<?php

class GuaranteeCertificate
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    // ─── Queries ────────────────────────────────────────────

    public function all(array $filters = []): array
    {
        $sql    = "SELECT g.*, c.name AS client_name_fk
                   FROM guarantee_certificates g
                   LEFT JOIN clients c ON c.id = g.client_id
                   WHERE 1=1";
        $params = [];

        if (!empty($filters['company_id'])) {
            $sql .= ' AND g.company_id = :company_id';
            $params[':company_id'] = (int) $filters['company_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= ' AND (g.certificate_number LIKE :s OR g.customer_name LIKE :s OR g.reference LIKE :s)';
            $params[':s'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['client_id'])) {
            $sql .= ' AND g.client_id = :cid';
            $params[':cid'] = (int) $filters['client_id'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= ' AND g.start_date >= :df';
            $params[':df'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= ' AND g.start_date <= :dt';
            $params[':dt'] = $filters['date_to'];
        }

        $sql .= ' ORDER BY g.created_at DESC, g.id DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT g.*,
                    c.name    AS client_name_fk,
                    c.address AS client_address,
                    c.phone   AS client_phone,
                    c.email   AS client_email,
                    i.invoice_number
             FROM guarantee_certificates g
             LEFT JOIN clients  c ON c.id = g.client_id
             LEFT JOIN invoices i ON i.id = g.invoice_id
             WHERE g.id = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function items(int $certId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM guarantee_items WHERE certificate_id = :cid ORDER BY id'
        );
        $stmt->execute([':cid' => $certId]);
        return $stmt->fetchAll();
    }

    public function numberExists(string $number, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM guarantee_certificates WHERE certificate_number = :n AND id <> :eid'
        );
        $stmt->execute([':n' => $number, ':eid' => $excludeId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM guarantee_certificates')->fetchColumn();
    }

    public function recent(int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT g.*, c.name AS client_name_fk
             FROM guarantee_certificates g
             LEFT JOIN clients c ON c.id = g.client_id
             ORDER BY g.created_at DESC, g.id DESC LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ─── Mutations ──────────────────────────────────────────

    public function create(array $data, array $items = []): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO guarantee_certificates
                   (company_id, use_watermark, certificate_number, client_id, invoice_id,
                    reference, customer_name, product_details, start_date, end_date,
                    delivery_date, terms, notes, payment_method)
                 VALUES
                   (:coid, :wm, :num, :cid, :iid,
                    :ref, :cname, :details, :sdate, :edate, :ddate, :terms, :notes, :pmeth)"
            );
            $stmt->execute($this->bind($data));
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
                "UPDATE guarantee_certificates
                 SET company_id         = :coid,   use_watermark   = :wm,
                     certificate_number = :num,    client_id       = :cid,   invoice_id  = :iid,
                     reference          = :ref,    customer_name   = :cname,
                     product_details    = :details, start_date     = :sdate, end_date    = :edate,
                     delivery_date      = :ddate,  terms           = :terms, notes       = :notes,
                     payment_method     = :pmeth
                 WHERE id = :id"
            )->execute($params);

            $this->db->prepare('DELETE FROM guarantee_items WHERE certificate_id = :id')
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
        // guarantee_items cascade-deleted by FK
        $this->db->prepare('DELETE FROM guarantee_certificates WHERE id = :id')
                 ->execute([':id' => $id]);
    }

    // ─── Private ────────────────────────────────────────────

    private function bind(array $d): array
    {
        return [
            ':coid'    => !empty($d['company_id'])    ? (int) $d['company_id'] : null,
            ':wm'      => !empty($d['use_watermark'])  ? 1 : 0,
            ':num'     => $d['certificate_number'],
            ':cid'     => !empty($d['client_id'])     ? (int) $d['client_id']  : null,
            ':iid'     => !empty($d['invoice_id'])    ? (int) $d['invoice_id'] : null,
            ':ref'     => $d['reference']      ?: null,
            ':cname'   => $d['customer_name'],
            ':details' => $d['product_details'] ?: null,
            ':sdate'   => $d['start_date'],
            ':edate'   => $d['end_date'],
            ':ddate'   => $d['delivery_date']   ?: null,
            ':terms'   => $d['terms']           ?: null,
            ':notes'   => $d['notes']           ?: null,
            ':pmeth'   => $d['payment_method']  ?: null,
        ];
    }

    private function insertItems(int $certId, array $items): void
    {
        if (empty($items)) return;
        $stmt = $this->db->prepare(
            'INSERT INTO guarantee_items (certificate_id, label, quantity, unit_price, total)
             VALUES (:cid, :label, :qty, :price, :total)'
        );
        foreach ($items as $item) {
            $qty   = (float) ($item['quantity']   ?? 1);
            $price = (isset($item['unit_price']) && $item['unit_price'] !== '')
                        ? (float) $item['unit_price'] : null;
            $total = $price !== null ? round($qty * $price, 2) : null;
            $stmt->execute([
                ':cid'   => $certId,
                ':label' => $item['label'],
                ':qty'   => $qty,
                ':price' => $price,
                ':total' => $total,
            ]);
        }
    }
}
