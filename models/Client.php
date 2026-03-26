<?php

class Client
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    public function all(array $filters = []): array
    {
        $sql    = 'SELECT * FROM clients WHERE 1=1';
        $params = [];

        if (!empty($filters['company_id'])) {
            $sql .= ' AND company_id = :company_id';
            $params[':company_id'] = (int) $filters['company_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= ' AND (name LIKE :s OR email LIKE :s OR ice LIKE :s OR phone LIKE :s)';
            $params[':s'] = '%' . $filters['search'] . '%';
        }

        $sql .= ' ORDER BY name ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM clients WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function create(array $d): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO clients (company_id, name, address, ice, phone, email)
             VALUES (:coid, :name, :addr, :ice, :phone, :email)'
        );
        $stmt->execute([
            ':coid'  => $d['company_id'] ?? null,
            ':name'  => $d['name'],
            ':addr'  => $d['address']    ?? null,
            ':ice'   => $d['ice']        ?? null,
            ':phone' => $d['phone']      ?? null,
            ':email' => $d['email']      ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $d): void
    {
        $stmt = $this->db->prepare(
            'UPDATE clients
             SET name = :name, address = :addr, ice = :ice, phone = :phone, email = :email
             WHERE id = :id'
        );
        $stmt->execute([
            ':name'  => $d['name'],
            ':addr'  => $d['address']  ?? null,
            ':ice'   => $d['ice']      ?? null,
            ':phone' => $d['phone']    ?? null,
            ':email' => $d['email']    ?? null,
            ':id'    => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM clients WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM clients')->fetchColumn();
    }

    /**
     * Check if client has invoices (prevent delete).
     */
    public function hasInvoices(int $id): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM invoices WHERE client_id = :id'
        );
        $stmt->execute([':id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function forSelect(?int $companyId = null): array
    {
        if ($companyId) {
            $stmt = $this->db->prepare(
                'SELECT id, name FROM clients WHERE company_id = :coid ORDER BY name'
            );
            $stmt->execute([':coid' => $companyId]);
            return $stmt->fetchAll();
        }
        return $this->db->query(
            'SELECT id, name FROM clients ORDER BY name'
        )->fetchAll();
    }
}
