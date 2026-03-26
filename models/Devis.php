<?php

class Devis
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    // ─── Queries ────────────────────────────────────────────

    public function all(array $filters = []): array
    {
        $sql = "SELECT d.*, c.name AS client_name
                FROM devis d
                JOIN clients c ON c.id = d.client_id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['company_id'])) {
            $sql .= ' AND d.company_id = :company_id';
            $params[':company_id'] = (int) $filters['company_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= ' AND (d.devis_number LIKE :s OR c.name LIKE :s)';
            $params[':s'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['client_id'])) {
            $sql .= ' AND d.client_id = :cid';
            $params[':cid'] = (int) $filters['client_id'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= ' AND d.date >= :df';
            $params[':df'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= ' AND d.date <= :dt';
            $params[':dt'] = $filters['date_to'];
        }

        if (!empty($filters['status'])) {
            $sql .= ' AND d.status = :status';
            $params[':status'] = $filters['status'];
        }

        $sql .= ' ORDER BY d.date DESC, d.id DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT d.*, c.name AS client_name, c.address AS client_address,
                    c.ice AS client_ice, c.phone AS client_phone, c.email AS client_email
             FROM devis d
             JOIN clients c ON c.id = d.client_id
             WHERE d.id = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function items(int $devisId): array
    {
        $stmt = $this->db->prepare(
            "SELECT di.*, p.sku
             FROM devis_items di
             LEFT JOIN products p ON p.id = di.product_id
             WHERE di.devis_id = :did
             ORDER BY di.id"
        );
        $stmt->execute([':did' => $devisId]);
        return $stmt->fetchAll();
    }

    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM devis')->fetchColumn();
    }

    // ─── Mutations ──────────────────────────────────────────

    public function create(array $data, array $items): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO devis
                (company_id, use_watermark, devis_number, client_id, date, validity_date,
                 total_ht, tax_rate, tax_amount, total_ttc, notes, status, payment_method)
             VALUES
                (:coid, :wm, :num, :cid, :date, :vdate,
                 :ht, :rate, :tax, :ttc, :notes, :status, :pmeth)"
        );
        $stmt->execute([
            ':coid'   => $data['company_id']     ?: null,
            ':wm'     => !empty($data['use_watermark']) ? 1 : 0,
            ':num'    => $data['devis_number'],
            ':cid'    => $data['client_id'],
            ':date'   => $data['date'],
            ':vdate'  => $data['validity_date']   ?: null,
            ':ht'     => $data['total_ht'],
            ':rate'   => $data['tax_rate'],
            ':tax'    => $data['tax_amount'],
            ':ttc'    => $data['total_ttc'],
            ':notes'  => $data['notes']           ?? null,
            ':status' => $data['status']          ?? 'draft',
            ':pmeth'  => $data['payment_method']  ?: null,
        ]);
        $devisId = (int) $this->db->lastInsertId();
        $this->insertItems($devisId, $items);
        return $devisId;
    }

    public function update(int $id, array $data, array $newItems): void
    {
        $this->db->prepare('DELETE FROM devis_items WHERE devis_id = :id')
                 ->execute([':id' => $id]);

        $stmt = $this->db->prepare(
            "UPDATE devis
             SET company_id    = :coid, use_watermark  = :wm,
                 devis_number  = :num,  client_id      = :cid,   date          = :date,
                 validity_date = :vdate,
                 total_ht      = :ht,   tax_rate       = :rate,  tax_amount    = :tax,
                 total_ttc     = :ttc,  notes          = :notes, status        = :status,
                 payment_method = :pmeth
             WHERE id = :id"
        );
        $stmt->execute([
            ':coid'   => $data['company_id']     ?: null,
            ':wm'     => !empty($data['use_watermark']) ? 1 : 0,
            ':num'    => $data['devis_number'],
            ':cid'    => $data['client_id'],
            ':date'   => $data['date'],
            ':vdate'  => $data['validity_date']   ?: null,
            ':ht'     => $data['total_ht'],
            ':rate'   => $data['tax_rate'],
            ':tax'    => $data['tax_amount'],
            ':ttc'    => $data['total_ttc'],
            ':notes'  => $data['notes']           ?? null,
            ':status' => $data['status']          ?? 'draft',
            ':pmeth'  => $data['payment_method']  ?: null,
            ':id'     => $id,
        ]);

        $this->insertItems($id, $newItems);
    }

    public function delete(int $id): void
    {
        $this->db->prepare('DELETE FROM devis WHERE id = :id')
                 ->execute([':id' => $id]);
    }

    // ─── Internal ────────────────────────────────────────────

    private function insertItems(int $devisId, array $items): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO devis_items (devis_id, product_id, label, quantity, unit_price, total)
             VALUES (:did, :pid, :label, :qty, :unit, :total)"
        );

        foreach ($items as $item) {
            $qty       = (float) ($item['quantity']   ?? 1);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $total     = round($qty * $unitPrice, 2);
            $productId = !empty($item['product_id']) ? (int) $item['product_id'] : null;

            $stmt->execute([
                ':did'   => $devisId,
                ':pid'   => $productId,
                ':label' => $item['label'],
                ':qty'   => $qty,
                ':unit'  => $unitPrice,
                ':total' => $total,
            ]);
        }
    }
}
