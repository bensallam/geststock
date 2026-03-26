<?php

class Company
{
    private PDO $db;

    private const ALLOWED_MIME   = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private const MAX_FILE_BYTES = 2 * 1024 * 1024; // 2 MB

    public function __construct()
    {
        $this->db = db();
    }

    // ─── Queries ────────────────────────────────────────────

    public function all(): array
    {
        return $this->db->query(
            'SELECT * FROM companies ORDER BY is_active DESC, company_name ASC'
        )->fetchAll();
    }

    public function find(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM companies WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /** Returns the currently active company, or false if none set. */
    public function getActive(): array|false
    {
        $stmt = $this->db->query('SELECT * FROM companies WHERE is_active = 1 LIMIT 1');
        return $stmt->fetch();
    }

    public function forSelect(): array
    {
        return $this->db->query(
            'SELECT id, company_name, is_active FROM companies ORDER BY is_active DESC, company_name ASC'
        )->fetchAll();
    }

    // ─── Mutations ──────────────────────────────────────────

    public function create(array $data): int
    {
        $params           = $this->params($data);
        $params[':lname'] = $params[':name']; // keep live DB `name` column in sync
        $stmt = $this->db->prepare(
            "INSERT INTO companies
               (company_name, name, address, phone, email, tax_id,
                invoice_notes, invoice_footer,
                default_warranty_terms, default_payment_method)
             VALUES (:name, :lname, :addr, :phone, :email, :tax, :notes, :footer, :warranty, :pmeth)"
        );
        $stmt->execute($params);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $params         = $this->params($data);
        $params[':id']  = $id;
        $this->db->prepare(
            "UPDATE companies
             SET company_name           = :name,
                 address                = :addr,
                 phone                  = :phone,
                 email                  = :email,
                 tax_id                 = :tax,
                 invoice_notes          = :notes,
                 invoice_footer         = :footer,
                 default_warranty_terms = :warranty,
                 default_payment_method = :pmeth
             WHERE id = :id"
        )->execute($params);
    }

    public function delete(int $id): void
    {
        $company = $this->find($id);
        if (!$company) return;

        $dir = __DIR__ . '/../storage/logo/';
        foreach (['logo_path', 'watermark_path'] as $col) {
            if (!empty($company[$col]) && file_exists($dir . basename($company[$col]))) {
                unlink($dir . basename($company[$col]));
            }
        }

        $this->db->prepare('DELETE FROM companies WHERE id = :id')
                 ->execute([':id' => $id]);
    }

    /** Mark one company as active, deactivate all others. */
    public function setActive(int $id): void
    {
        $this->db->exec('UPDATE companies SET is_active = 0');
        $this->db->prepare('UPDATE companies SET is_active = 1 WHERE id = :id')
                 ->execute([':id' => $id]);
    }

    // ─── Logo ────────────────────────────────────────────────

    /** Returns null on success, error message on failure. */
    public function uploadLogo(int $id, array $file): ?string
    {
        return $this->uploadImage($id, $file, 'logo_path');
    }

    public function deleteLogo(int $id): void
    {
        $this->deleteImage($id, 'logo_path');
    }

    public function logoDataUri(int $id): ?string
    {
        $company = $this->find($id);
        if (!$company || empty($company['logo_path'])) return null;
        return $this->fileDataUri(__DIR__ . '/../storage/logo/' . basename($company['logo_path']));
    }

    // ─── Watermark ───────────────────────────────────────────

    public function uploadWatermark(int $id, array $file): ?string
    {
        return $this->uploadImage($id, $file, 'watermark_path');
    }

    public function deleteWatermark(int $id): void
    {
        $this->deleteImage($id, 'watermark_path');
    }

    public function updateOpacity(int $id, float $opacity): void
    {
        $opacity = max(0.05, min(1.0, $opacity));
        $this->db->prepare('UPDATE companies SET watermark_opacity = :op WHERE id = :id')
                 ->execute([':op' => $opacity, ':id' => $id]);
    }

    public function watermarkDataUri(int $id): ?string
    {
        $company = $this->find($id);
        if (!$company || empty($company['watermark_path'])) return null;
        return $this->fileDataUri(__DIR__ . '/../storage/logo/' . basename($company['watermark_path']));
    }

    // ─── Private helpers ────────────────────────────────────

    private function params(array $data): array
    {
        return [
            ':name'     => trim($data['company_name']           ?? ''),
            ':addr'     => trim($data['address']                ?? '') ?: null,
            ':phone'    => trim($data['phone']                  ?? '') ?: null,
            ':email'    => trim($data['email']                  ?? '') ?: null,
            ':tax'      => trim($data['tax_id']                 ?? '') ?: null,
            ':notes'    => trim($data['invoice_notes']          ?? '') ?: null,
            ':footer'   => trim($data['invoice_footer']         ?? '') ?: null,
            ':warranty' => trim($data['default_warranty_terms'] ?? '') ?: null,
            ':pmeth'    => trim($data['default_payment_method'] ?? '') ?: null,
        ];
    }

    private function uploadImage(int $id, array $file, string $column): ?string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return 'Erreur lors du téléchargement (code ' . $file['error'] . ').';
        }
        if ($file['size'] > self::MAX_FILE_BYTES) {
            return 'Le fichier ne doit pas dépasser 2 Mo.';
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);

        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            return 'Format non autorisé. Utilisez JPEG, PNG, GIF ou WebP.';
        }

        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        };

        $dir = __DIR__ . '/../storage/logo/';

        // Delete old file
        $company = $this->find($id);
        if ($company && !empty($company[$column])) {
            $old = $dir . basename($company[$column]);
            if (file_exists($old)) unlink($old);
        }

        $filename = $column . '_' . $id . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
            return 'Impossible de sauvegarder le fichier.';
        }

        $this->db->prepare("UPDATE companies SET {$column} = :path WHERE id = :id")
                 ->execute([':path' => $filename, ':id' => $id]);

        return null;
    }

    private function deleteImage(int $id, string $column): void
    {
        $company = $this->find($id);
        if (!$company || empty($company[$column])) return;

        $file = __DIR__ . '/../storage/logo/' . basename($company[$column]);
        if (file_exists($file)) unlink($file);

        $this->db->prepare("UPDATE companies SET {$column} = NULL WHERE id = :id")
                 ->execute([':id' => $id]);
    }

    private function fileDataUri(string $path): ?string
    {
        if (!file_exists($path)) return null;
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($path);
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }
}
