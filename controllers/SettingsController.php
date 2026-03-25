<?php

require_once __DIR__ . '/../models/CompanySettings.php';

class SettingsController
{
    private CompanySettings $settings;

    public function __construct()
    {
        $this->settings = new CompanySettings();
    }

    /**
     * Show the settings form.
     */
    public function index(): void
    {
        requireAuth();
        $company = $this->settings->get();
        $errors  = [];
        require __DIR__ . '/../views/settings/index.php';
    }

    /**
     * Save text settings.
     */
    public function update(): void
    {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('settings'); }

        $errors = [];

        // Validate email if provided
        $email = trim($_POST['email'] ?? '');
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'adresse email n\'est pas valide.';
        }

        if (empty($errors)) {
            $this->settings->save($_POST);
            setFlash('success', 'Paramètres enregistrés.');
            redirect('settings');
        }

        $company = array_merge($this->settings->get(), $_POST);
        require __DIR__ . '/../views/settings/index.php';
    }

    /**
     * Handle logo upload.
     */
    public function uploadLogo(): void
    {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('settings'); }

        if (empty($_FILES['logo']) || $_FILES['logo']['error'] === UPLOAD_ERR_NO_FILE) {
            setFlash('danger', 'Aucun fichier sélectionné.');
            redirect('settings');
            return;
        }

        $error = $this->settings->uploadLogo($_FILES['logo']);

        if ($error) {
            setFlash('danger', $error);
        } else {
            setFlash('success', 'Logo mis à jour avec succès.');
        }

        redirect('settings');
    }

    /**
     * Delete logo.
     */
    public function deleteLogo(): void
    {
        requireAuth();
        $this->settings->deleteLogo();
        setFlash('success', 'Logo supprimé.');
        redirect('settings');
    }

    /**
     * Serve the logo image file (so it stays outside public/).
     */
    public function logo(): void
    {
        requireAuth();
        $settings = $this->settings->get();

        if (empty($settings['logo_path'])) {
            http_response_code(404);
            exit;
        }

        $file = __DIR__ . '/../storage/logo/' . basename($settings['logo_path']);

        if (!file_exists($file)) {
            http_response_code(404);
            exit;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file);

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($file));
        header('Cache-Control: private, max-age=86400');
        readfile($file);
        exit;
    }
}
