<?php

class Invoice
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    // ─── Queries ────────────────────────────────────────────

    public function all(array $filters = []): array
    {
        $sql = "SELECT i.*, c.name AS client_name
                FROM invoices i
                JOIN clients c ON c.id = i.client_id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= ' AND (i.invoice_number LIKE :s OR c.name LIKE :s)';
            $params[':s'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['client_id'])) {
            $sql .= ' AND i.client_id = :cid';
            $params[':cid'] = (int) $filters['client_id'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= ' AND i.date >= :df';
            $params[':df'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= ' AND i.date <= :dt';
            $params[':dt'] = $filters['date_to'];
        }

        if (!empty($filters['status'])) {
            $sql .= ' AND i.status = :status';
            $params[':status'] = $filters['status'];
        }

        $sql .= ' ORDER BY i.date DESC, i.id DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT i.*, c.name AS client_name, c.address AS client_address,
                    c.ice AS client_ice, c.phone AS client_phone, c.email AS client_email
             FROM invoices i
             JOIN clients c ON c.id = i.client_id
             WHERE i.id = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function items(int $invoiceId): array
    {
        $stmt = $this->db->prepare(
            "SELECT ii.*, p.sku
             FROM invoice_items ii
             LEFT JOIN products p ON p.id = ii.product_id
             WHERE ii.invoice_id = :iid
             ORDER BY ii.id"
        );
        $stmt->execute([':iid' => $invoiceId]);
        return $stmt->fetchAll();
    }

    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM invoices')->fetchColumn();
    }

    public function totalRevenue(): float
    {
        return (float) $this->db->query(
            "SELECT COALESCE(SUM(total_ttc),0) FROM invoices WHERE status IN ('sent','paid')"
        )->fetchColumn();
    }

    public function recent(int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT i.*, c.name AS client_name
             FROM invoices i
             JOIN clients c ON c.id = i.client_id
             ORDER BY i.date DESC, i.id DESC
             LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ─── Mutations ──────────────────────────────────────────

    /**
     * Create an invoice + items; deduct stock.
     * Returns the new invoice ID.
     */
    public function create(array $data, array $items): int
    {
        $this->db->beginTransaction();
        try {
            // Insert header
            $stmt = $this->db->prepare(
                "INSERT INTO invoices
                    (invoice_number, client_id, date, total_ht, tax_rate, tax_amount, total_ttc, notes, status)
                 VALUES
                    (:num, :cid, :date, :ht, :rate, :tax, :ttc, :notes, :status)"
            );
            $stmt->execute([
                ':num'    => $data['invoice_number'],
                ':cid'    => $data['client_id'],
                ':date'   => $data['date'],
                ':ht'     => $data['total_ht'],
                ':rate'   => $data['tax_rate'],
                ':tax'    => $data['tax_amount'],
                ':ttc'    => $data['total_ttc'],
                ':notes'  => $data['notes'] ?? null,
                ':status' => $data['status'] ?? 'draft',
            ]);
            $invoiceId = (int) $this->db->lastInsertId();

            // Insert items + deduct stock
            $this->insertItems($invoiceId, $items, 'create');

            $this->db->commit();
            return $invoiceId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Update invoice header + items; correctly adjusts stock differences.
     */
    public function update(int $id, array $data, array $newItems): void
    {
        $this->db->beginTransaction();
        try {
            // Restore stock from old items
            $oldItems = $this->items($id);
            foreach ($oldItems as $old) {
                if ($old['product_id']) {
                    $this->adjustStock(
                        (int) $old['product_id'],
                        (int) $old['quantity'],   // positive = restore
                        'IN',
                        'Modification facture #' . $id . ' — annulation ligne'
                    );
                }
            }

            // Delete old items
            $this->db->prepare('DELETE FROM invoice_items WHERE invoice_id = :id')
                     ->execute([':id' => $id]);

            // Update header
            $stmt = $this->db->prepare(
                "UPDATE invoices
                 SET invoice_number = :num, client_id = :cid, date = :date,
                     total_ht = :ht, tax_rate = :rate, tax_amount = :tax,
                     total_ttc = :ttc, notes = :notes, status = :status
                 WHERE id = :id"
            );
            $stmt->execute([
                ':num'    => $data['invoice_number'],
                ':cid'    => $data['client_id'],
                ':date'   => $data['date'],
                ':ht'     => $data['total_ht'],
                ':rate'   => $data['tax_rate'],
                ':tax'    => $data['tax_amount'],
                ':ttc'    => $data['total_ttc'],
                ':notes'  => $data['notes'] ?? null,
                ':status' => $data['status'] ?? 'draft',
                ':id'     => $id,
            ]);

            // Insert new items + deduct stock
            $this->insertItems($id, $newItems, 'update');

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Delete invoice; restore stock.
     */
    public function delete(int $id): void
    {
        $this->db->beginTransaction();
        try {
            $inv = $this->find($id);
            if (!$inv) {
                $this->db->rollBack();
                return;
            }

            // Restore stock
            foreach ($this->items($id) as $item) {
                if ($item['product_id']) {
                    $this->adjustStock(
                        (int) $item['product_id'],
                        (int) $item['quantity'],
                        'IN',
                        'Suppression facture ' . $inv['invoice_number']
                    );
                }
            }

            // Cascade deletes items automatically (FK ON DELETE CASCADE)
            $this->db->prepare('DELETE FROM invoices WHERE id = :id')
                     ->execute([':id' => $id]);

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ─── Internals ──────────────────────────────────────────

    private function insertItems(int $invoiceId, array $items, string $context): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO invoice_items (invoice_id, product_id, label, quantity, unit_price, total)
             VALUES (:iid, :pid, :label, :qty, :unit, :total)"
        );

        foreach ($items as $item) {
            $qty       = (float) ($item['quantity']   ?? 1);
            $unitPrice = (float) ($item['unit_price']  ?? 0);
            $total     = round($qty * $unitPrice, 2);
            $productId = !empty($item['product_id']) ? (int) $item['product_id'] : null;

            $stmt->execute([
                ':iid'   => $invoiceId,
                ':pid'   => $productId,
                ':label' => $item['label'],
                ':qty'   => $qty,
                ':unit'  => $unitPrice,
                ':total' => $total,
            ]);

            // Deduct stock for linked products
            if ($productId && $qty > 0) {
                $note = ($context === 'update' ? 'Modification' : 'Création') . ' facture #' . $invoiceId;
                $this->adjustStock($productId, (int) $qty, 'OUT', $note);
            }
        }
    }

    private function adjustStock(int $productId, int $qty, string $type, string $note): void
    {
        // Update product quantity
        $delta = $type === 'IN' ? $qty : -$qty;
        $this->db->prepare(
            "UPDATE products
             SET quantity = GREATEST(0, quantity + :delta)
             WHERE id = :id"
        )->execute([':delta' => $delta, ':id' => $productId]);

        // Log movement
        $this->db->prepare(
            "INSERT INTO stock_movements (product_id, type, quantity, note)
             VALUES (:pid, :type, :qty, :note)"
        )->execute([
            ':pid'  => $productId,
            ':type' => $type,
            ':qty'  => abs($qty),
            ':note' => $note,
        ]);
    }
}
