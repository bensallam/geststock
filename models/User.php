<?php

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    // ─── Queries ────────────────────────────────────────────

    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        return (int) $stmt->fetchColumn() > 0;
    }

    // ─── Mutations ──────────────────────────────────────────

    /**
     * Create a new admin user linked to a company.
     * Returns the new user ID.
     */
    public function create(array $data): int
    {
        // Derive a unique username from the company name
        $base     = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $data['name'] ?? 'user'));
        $username = $base ?: 'user';
        $n = 1;
        $try = $username;
        while ($this->usernameExists($try)) {
            $try = $username . $n++;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO users
               (company_id, username, email, password, full_name, name, role, is_active, onboarding_completed)
             VALUES
               (:cid, :username, :email, :password, :full_name, :name, 'admin', 1, 1)"
        );
        $stmt->execute([
            ':cid'       => $data['company_id'],
            ':username'  => $try,
            ':email'     => $data['email'],
            ':password'  => $this->hashPassword($data['password']),
            ':full_name' => $data['name'],
            ':name'      => $data['name'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updateProfile(int $id, string $name): void
    {
        $this->db->prepare('UPDATE users SET name = :name, full_name = :name WHERE id = :id')
                 ->execute([':name' => $name, ':id' => $id]);
    }

    public function completeOnboarding(int $id): void
    {
        $this->db->prepare('UPDATE users SET onboarding_completed = 1 WHERE id = :id')
                 ->execute([':id' => $id]);
    }

    // ─── Password ───────────────────────────────────────────

    public function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    public function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    // ─── Private ────────────────────────────────────────────

    private function usernameExists(string $username): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE username = :u');
        $stmt->execute([':u' => $username]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
