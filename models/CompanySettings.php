<?php

class CompanySettings
{
    private PDO $db;

    /** Allowed logo MIME types */
    private const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    /** Max logo file size: 2 MB */
    private const MAX_LOGO_BYTES = 2 * 1024 * 1024;

    public function __construct()
    {
        $this->db = db();
    }

    /**
     * Return the single settings row, always with all keys present.
     */
    public function get(): array
    {
        $stmt = $this->db->query('SELECT * FROM company_settings WHERE id = 1');
        $row  = $stmt->fetch();
        return $row ?: $this->defaults();
    }

    /**
     * Persist text settings (not the logo).
     */
    public function save(array $data): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO company_settings
               (id, company_name, address, phone, email, tax_id,
                invoice_notes, invoice_footer,
                default_warranty_terms, default_payment_method)
             VALUES (1, :name, :addr, :phone, :email, :tax, :notes, :footer, :warranty, :pmeth)
             ON DUPLICATE KEY UPDATE
               company_name           = VALUES(company_name),
               address                = VALUES(address),
               phone                  = VALUES(phone),
               email                  = VALUES(email),
               tax_id                 = VALUES(tax_id),
               invoice_notes          = VALUES(invoice_notes),
               invoice_footer         = VALUES(invoice_footer),
               default_warranty_terms = VALUES(default_warranty_terms),
               default_payment_method = VALUES(default_payment_method)"
        );
        $stmt->execute([
            ':name'     => trim($data['company_name']           ?? ''),
            ':addr'     => trim($data['address']                ?? ''),
            ':phone'    => trim($data['phone']                  ?? ''),
            ':email'    => trim($data['email']                  ?? ''),
            ':tax'      => trim($data['tax_id']                 ?? ''),
            ':notes'    => trim($data['invoice_notes']          ?? ''),
            ':footer'   => trim($data['invoice_footer']         ?? ''),
            ':warranty' => trim($data['default_warranty_terms'] ?? '') ?: null,
            ':pmeth'    => trim($data['default_payment_method'] ?? '') ?: null,
        ]);
    }

    /**
     * Validate and store a logo upload.
     * Returns null on success or an error message string.
     */
    public function uploadLogo(array $file): ?string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return 'Erreur lors du téléchargement du logo (code ' . $file['error'] . ').';
        }

        if ($file['size'] > self::MAX_LOGO_BYTES) {
            return 'Le logo ne doit pas dépasser 2 Mo.';
        }

        // Verify actual MIME type using fileinfo (ignores extension)
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);

        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            return 'Format non autorisé. Utilisez JPEG, PNG, GIF ou WebP.';
        }

        // Map mime → extension
        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        };

        $logoDir = __DIR__ . '/../storage/logo/';

        // Remove old logo if present
        $current = $this->get();
        if (!empty($current['logo_path']) && file_exists($logoDir . basename($current['logo_path']))) {
            unlink($logoDir . basename($current['logo_path']));
        }

        $filename = 'logo_' . bin2hex(random_bytes(8)) . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $logoDir . $filename)) {
            return 'Impossible de sauvegarder le logo.';
        }

        // Persist path
        $this->db->prepare(
            "INSERT INTO company_settings (id, logo_path)
             VALUES (1, :path)
             ON DUPLICATE KEY UPDATE logo_path = VALUES(logo_path)"
        )->execute([':path' => $filename]);

        return null; // success
    }

    /**
     * Delete the stored logo.
     */
    public function deleteLogo(): void
    {
        $current = $this->get();
        $logoDir = __DIR__ . '/../storage/logo/';

        if (!empty($current['logo_path'])) {
            $file = $logoDir . basename($current['logo_path']);
            if (file_exists($file)) {
                unlink($file);
            }
        }

        $this->db->exec(
            "UPDATE company_settings SET logo_path = NULL WHERE id = 1"
        );
    }

    /**
     * Return logo as a base64 data-URI (for Dompdf / inline embedding).
     * Returns null if no logo is set.
     */
    public function logoDataUri(): ?string
    {
        $current = $this->get();
        if (empty($current['logo_path'])) return null;

        $path = __DIR__ . '/../storage/logo/' . basename($current['logo_path']);
        if (!file_exists($path)) return null;

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($path);
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }

    // ─── Private ────────────────────────────────────────────

    private function defaults(): array
    {
        return [
            'id'                      => 1,
            'company_name'            => APP_NAME,
            'address'                 => '',
            'phone'                   => '',
            'email'                   => '',
            'tax_id'                  => '',
            'logo_path'               => null,
            'invoice_notes'           => '',
            'invoice_footer'          => 'Merci pour votre confiance.',
            'default_warranty_terms'  => null,
            'default_payment_method'  => null,
            'updated_at'              => null,
        ];
    }
}
