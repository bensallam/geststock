<?php

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    /**
     * Find a user by email.
     */
    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    /**
     * Find a user by ID.
     */
    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Verify password against stored hash.
     */
    public function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    /**
     * Hash a plain password.
     */
    public function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Update the user's display name.
     */
    public function updateProfile(int $id, string $name): void
    {
        $stmt = $this->db->prepare('UPDATE users SET name = :name WHERE id = :id');
        $stmt->execute([':name' => $name, ':id' => $id]);
    }

    /**
     * Mark onboarding as completed for a user.
     */
    public function completeOnboarding(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE users SET onboarding_completed = 1 WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }
}
