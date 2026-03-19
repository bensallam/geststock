<?php

class Product
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    /**
     * Return all products with their category name.
     */
    public function all(array $filters = []): array
    {
        $sql = "SELECT p.*, c.name AS category_name
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE :search OR p.sku LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['low_stock'])) {
            $sql .= " AND p.quantity <= p.minimum_stock";
        }

        if (!empty($filters['category_id'])) {
            $sql .= " AND p.category_id = :cat";
            $params[':cat'] = (int) $filters['category_id'];
        }

        $sql .= " ORDER BY p.name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Find a single product by ID.
     */
    public function find(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, c.name AS category_name
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.id = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Create a new product. Returns the new ID.
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO products (name, sku, category_id, unit_price, cost_price, quantity, minimum_stock, description)
             VALUES (:name, :sku, :cat, :unit, :cost, :qty, :min, :desc)"
        );
        $stmt->execute([
            ':name' => $data['name'],
            ':sku'  => $data['sku'],
            ':cat'  => $data['category_id'] ?: null,
            ':unit' => $data['unit_price'],
            ':cost' => $data['cost_price'],
            ':qty'  => (int) $data['quantity'],
            ':min'  => (int) $data['minimum_stock'],
            ':desc' => $data['description'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update a product.
     */
    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare(
            "UPDATE products
             SET name = :name, sku = :sku, category_id = :cat,
                 unit_price = :unit, cost_price = :cost,
                 quantity = :qty, minimum_stock = :min,
                 description = :desc
             WHERE id = :id"
        );
        $stmt->execute([
            ':name' => $data['name'],
            ':sku'  => $data['sku'],
            ':cat'  => $data['category_id'] ?: null,
            ':unit' => $data['unit_price'],
            ':cost' => $data['cost_price'],
            ':qty'  => (int) $data['quantity'],
            ':min'  => (int) $data['minimum_stock'],
            ':desc' => $data['description'] ?? null,
            ':id'   => $id,
        ]);
    }

    /**
     * Delete a product by ID.
     */
    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM products WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    /**
     * Adjust quantity (positive = IN, negative = OUT).
     * Returns false if quantity would go below zero.
     */
    public function adjustQuantity(int $id, int $delta): bool
    {
        $this->db->beginTransaction();
        try {
            // Lock row
            $stmt = $this->db->prepare(
                'SELECT quantity FROM products WHERE id = :id FOR UPDATE'
            );
            $stmt->execute([':id' => $id]);
            $current = (int) $stmt->fetchColumn();

            $newQty = $current + $delta;
            if ($newQty < 0) {
                $this->db->rollBack();
                return false;
            }

            $upd = $this->db->prepare(
                'UPDATE products SET quantity = :qty WHERE id = :id'
            );
            $upd->execute([':qty' => $newQty, ':id' => $id]);
            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Get low-stock products.
     */
    public function lowStock(): array
    {
        $stmt = $this->db->query(
            "SELECT p.*, c.name AS category_name
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.quantity <= p.minimum_stock
             ORDER BY p.quantity ASC
             LIMIT 10"
        );
        return $stmt->fetchAll();
    }

    /**
     * Count total products.
     */
    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM products')->fetchColumn();
    }

    /**
     * Check if a SKU already exists (excluding a given ID for edit).
     */
    public function skuExists(string $sku, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM products WHERE sku = :sku AND id <> :id'
        );
        $stmt->execute([':sku' => $sku, ':id' => $excludeId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * All products for select dropdowns.
     */
    public function forSelect(): array
    {
        return $this->db->query(
            "SELECT id, name, sku, unit_price, quantity FROM products ORDER BY name"
        )->fetchAll();
    }
}
