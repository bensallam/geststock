<?php

class StockMovement
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    /**
     * Log a stock movement and update the product quantity.
     */
    public function log(int $productId, string $type, int $qty, string $note = ''): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO stock_movements (product_id, type, quantity, note)
             VALUES (:pid, :type, :qty, :note)"
        );
        $stmt->execute([
            ':pid'  => $productId,
            ':type' => $type,
            ':qty'  => abs($qty),
            ':note' => $note ?: null,
        ]);
    }

    /**
     * All movements, optionally filtered by product.
     */
    public function all(array $filters = []): array
    {
        $sql = "SELECT sm.*, p.name AS product_name, p.sku
                FROM stock_movements sm
                JOIN products p ON p.id = sm.product_id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['product_id'])) {
            $sql .= ' AND sm.product_id = :pid';
            $params[':pid'] = (int) $filters['product_id'];
        }

        if (!empty($filters['type'])) {
            $sql .= ' AND sm.type = :type';
            $params[':type'] = $filters['type'];
        }

        $sql .= ' ORDER BY sm.created_at DESC LIMIT 200';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Movements for a specific product.
     */
    public function forProduct(int $productId): array
    {
        return $this->all(['product_id' => $productId]);
    }
}
