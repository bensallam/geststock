<?php

class DeliveryNote
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    // ─── Queries ────────────────────────────────────────────

    public function all(array $filters = []): array
    {
        $sql    = "SELECT d.*, c.name AS client_name_fk
                   FROM delivery_notes d
                   LEFT JOIN clients c ON c.id = d.client_id
                   WHERE 1=1";
        $params = [];

        if (!empty($filters['company_id'])) {
            $sql .= ' AND d.company_id = :company_id';
            $params[':company_id'] = (int) $filters['company_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= ' AND (d.note_number LIKE :s OR d.customer_name LIKE :s OR d.reference LIKE :s)';
            $params[':s'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['client_id'])) {
            $sql .= ' AND d.client_id = :cid';
            $params[':cid'] = (int) $filters['client_id'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= ' AND d.delivery_date >= :df';
            $params[':df'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= ' AND d.delivery_date <= :dt';
            $params[':dt'] = $filters['date_to'];
        }

        $sql .= ' ORDER BY d.delivery_date DESC, d.id DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT d.*,
                    c.name    AS client_name_fk,
                    c.address AS client_address,
                    c.phone   AS client_phone,
                    c.email   AS client_email
             FROM delivery_notes d
             LEFT JOIN clients c ON c.id = d.client_id
             WHERE d.id = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function items(int $noteId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM delivery_note_items WHERE note_id = :nid ORDER BY id'
        );
        $stmt->execute([':nid' => $noteId]);
        return $stmt->fetchAll();
    }

    public function numberExists(string $number, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM delivery_notes WHERE note_number = :n AND id <> :eid'
        );
        $stmt->execute([':n' => $number, ':eid' => $excludeId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM delivery_notes')->fetchColumn();
    }

    // ─── Mutations ──────────────────────────────────────────

    public function create(array $data, array $items = []): int
    {
        $this->db->beginTransaction();
        try {
            $this->db->prepare(
                "INSERT INTO delivery_notes
                   (company_id, use_watermark, note_number, client_id, customer_name,
                    delivery_date, reference, show_prices, payment_method, notes)
                 VALUES (:coid, :wm, :num, :cid, :cname, :date, :ref, :prices, :pmeth, :notes)"
            )->execute($this->bind($data));

            $id = (int) $this->db->lastInsertId();
            $this->insertItems($id, $items);
            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $data, array $items = []): void
    {
        $this->db->beginTransaction();
        try {
            $params        = $this->bind($data);
            $params[':id'] = $id;
            $this->db->prepare(
                "UPDATE delivery_notes
                 SET company_id     = :coid,  use_watermark  = :wm,
                     note_number    = :num,   client_id      = :cid,
                     customer_name  = :cname, delivery_date  = :date,
                     reference      = :ref,   show_prices    = :prices,
                     payment_method = :pmeth, notes          = :notes
                 WHERE id = :id"
            )->execute($params);

            $this->db->prepare('DELETE FROM delivery_note_items WHERE note_id = :id')
                     ->execute([':id' => $id]);
            $this->insertItems($id, $items);
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete(int $id): void
    {
        $this->db->prepare('DELETE FROM delivery_notes WHERE id = :id')
                 ->execute([':id' => $id]);
    }

    // ─── Private ────────────────────────────────────────────

    private function bind(array $d): array
    {
        return [
            ':coid'   => !empty($d['company_id'])   ? (int) $d['company_id'] : null,
            ':wm'     => !empty($d['use_watermark']) ? 1 : 0,
            ':num'    => $d['note_number'],
            ':cid'    => !empty($d['client_id'])     ? (int) $d['client_id'] : null,
            ':cname'  => $d['customer_name'],
            ':date'   => $d['delivery_date'],
            ':ref'    => $d['reference']             ?: null,
            ':prices' => !empty($d['show_prices'])   ? 1 : 0,
            ':pmeth'  => $d['payment_method']        ?: null,
            ':notes'  => $d['notes']                 ?: null,
        ];
    }

    private function insertItems(int $noteId, array $items): void
    {
        if (empty($items)) return;
        $stmt = $this->db->prepare(
            'INSERT INTO delivery_note_items (note_id, label, quantity, unit_price, total)
             VALUES (:nid, :label, :qty, :price, :total)'
        );
        foreach ($items as $item) {
            $qty   = (float) ($item['quantity']   ?? 1);
            $price = (isset($item['unit_price']) && $item['unit_price'] !== '')
                        ? (float) $item['unit_price'] : null;
            $total = $price !== null ? round($qty * $price, 2) : null;
            $stmt->execute([
                ':nid'   => $noteId,
                ':label' => $item['label'],
                ':qty'   => $qty,
                ':price' => $price,
                ':total' => $total,
            ]);
        }
    }
}
