<?php

require_once __DIR__ . '/../models/Company.php';

class CompanyController
{
    private Company $company;

    public function __construct()
    {
        $this->company = new Company();
    }

    public function index(): void
    {
        requireAuth();
        $companies = $this->company->all();
        require __DIR__ . '/../views/companies/index.php';
    }

    public function create(): void
    {
        requireAuth();
        $errors = [];
        $old    = [];
        require __DIR__ . '/../views/companies/create.php';
    }

    public function store(): void
    {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('companies'); }

        [$data, $errors] = $this->validateForm($_POST);

        if (!empty($errors)) {
            $old = $_POST;
            require __DIR__ . '/../views/companies/create.php';
            return;
        }

        $id = $this->company->create($data);
        setFlash('success', 'Entreprise « ' . e($data['company_name']) . ' » créée.');
        redirect('companies/edit?id=' . $id);
    }

    public function edit(): void
    {
        requireAuth();
        $id      = (int) ($_GET['id'] ?? 0);
        $company = $this->company->find($id);
        if (!$company) { $this->notFound(); return; }

        $errors = [];
        $old    = $company;
        require __DIR__ . '/../views/companies/edit.php';
    }

    public function update(): void
    {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('companies'); }

        $id      = (int) ($_POST['id'] ?? 0);
        $company = $this->company->find($id);
        if (!$company) { $this->notFound(); return; }

        [$data, $errors] = $this->validateForm($_POST);

        if (!empty($errors)) {
            $old = $_POST;
            require __DIR__ . '/../views/companies/edit.php';
            return;
        }

        $this->company->update($id, $data);
        setFlash('success', 'Entreprise mise à jour.');
        redirect('companies/edit?id=' . $id);
    }

    public function delete(): void
    {
        requireAuth();
        $id      = (int) ($_POST['id'] ?? 0);
        $company = $this->company->find($id);
        if (!$company) {
            setFlash('danger', 'Entreprise introuvable.');
            redirect('companies');
            return;
        }

        $this->company->delete($id);
        setFlash('success', 'Entreprise supprimée.');
        redirect('companies');
    }

    public function setActive(): void
    {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('companies'); }

        $id = (int) ($_POST['id'] ?? 0);
        if ($this->company->find($id)) {
            $this->company->setActive($id);
            setFlash('success', 'Entreprise active mise à jour.');
        }
        redirect('companies');
    }

    // ─── Logo ────────────────────────────────────────────────

    public function uploadLogo(): void
    {
        requireAuth();
        $id = (int) ($_POST['id'] ?? 0);
        if (!$this->company->find($id)) { redirect('companies'); }

        $error = $this->company->uploadLogo($id, $_FILES['logo'] ?? []);
        if ($error) {
            setFlash('danger', $error);
        } else {
            setFlash('success', 'Logo mis à jour.');
        }
        redirect('companies/edit?id=' . $id);
    }

    public function deleteLogo(): void
    {
        requireAuth();
        $id = (int) ($_POST['id'] ?? 0);
        $this->company->deleteLogo($id);
        setFlash('success', 'Logo supprimé.');
        redirect('companies/edit?id=' . $id);
    }

    /** Serve company logo image (browser). */
    public function logo(): void
    {
        requireAuth();
        $id      = (int) ($_GET['id'] ?? 0);
        $company = $this->company->find($id);
        if (!$company || empty($company['logo_path'])) {
            http_response_code(404); exit;
        }
        $this->serveImage(__DIR__ . '/../storage/logo/' . basename($company['logo_path']));
    }

    // ─── Watermark ───────────────────────────────────────────

    public function uploadWatermark(): void
    {
        requireAuth();
        $id = (int) ($_POST['id'] ?? 0);
        if (!$this->company->find($id)) { redirect('companies'); }

        $error = $this->company->uploadWatermark($id, $_FILES['watermark'] ?? []);
        if ($error) {
            setFlash('danger', $error);
        } else {
            setFlash('success', 'Filigrane mis à jour.');
        }
        redirect('companies/edit?id=' . $id);
    }

    public function deleteWatermark(): void
    {
        requireAuth();
        $id = (int) ($_POST['id'] ?? 0);
        $this->company->deleteWatermark($id);
        setFlash('success', 'Filigrane supprimé.');
        redirect('companies/edit?id=' . $id);
    }

    public function updateOpacity(): void
    {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('companies'); }

        $id      = (int) ($_POST['id'] ?? 0);
        $opacity = (float) ($_POST['watermark_opacity'] ?? 0.15);
        $this->company->updateOpacity($id, $opacity);
        setFlash('success', 'Opacité mise à jour.');
        redirect('companies/edit?id=' . $id);
    }

    /** Serve company watermark image (browser). */
    public function watermark(): void
    {
        requireAuth();
        $id      = (int) ($_GET['id'] ?? 0);
        $company = $this->company->find($id);
        if (!$company || empty($company['watermark_path'])) {
            http_response_code(404); exit;
        }
        $this->serveImage(__DIR__ . '/../storage/logo/' . basename($company['watermark_path']));
    }

    // ─── Helpers ────────────────────────────────────────────

    private function validateForm(array $post): array
    {
        $errors = [];
        $data   = [
            'company_name'           => trim($post['company_name']           ?? ''),
            'address'                => trim($post['address']                ?? ''),
            'phone'                  => trim($post['phone']                  ?? ''),
            'email'                  => trim($post['email']                  ?? ''),
            'tax_id'                 => trim($post['tax_id']                 ?? ''),
            'invoice_notes'          => trim($post['invoice_notes']          ?? ''),
            'invoice_footer'         => trim($post['invoice_footer']         ?? ''),
            'default_warranty_terms' => trim($post['default_warranty_terms'] ?? ''),
            'default_payment_method' => trim($post['default_payment_method'] ?? ''),
        ];

        if (empty($data['company_name'])) {
            $errors[] = 'Le nom de l\'entreprise est obligatoire.';
        }

        return [$data, $errors];
    }

    private function serveImage(string $path): void
    {
        if (!file_exists($path)) { http_response_code(404); exit; }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($path);
        header('Content-Type: ' . $mime);
        header('Cache-Control: private, max-age=3600');
        readfile($path);
        exit;
    }

    private function notFound(): void
    {
        http_response_code(404);
        require __DIR__ . '/../views/errors/404.php';
    }
}
