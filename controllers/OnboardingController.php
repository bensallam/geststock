<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/CompanySettings.php';

class OnboardingController
{
    private User $user;
    private CompanySettings $cs;

    public function __construct()
    {
        $this->user = new User();
        $this->cs   = new CompanySettings();
    }

    public function show(): void
    {
        $this->requireLogin();
        if (!empty($_SESSION['onboarding_completed'])) { redirect('dashboard'); }

        $errors = [];
        $old    = [];
        require __DIR__ . '/../views/onboarding/index.php';
    }

    public function store(): void
    {
        $this->requireLogin();
        if (!empty($_SESSION['onboarding_completed'])) { redirect('dashboard'); }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('onboarding'); }

        $errors = [];
        $old    = $_POST;
        $userId = (int) $_SESSION['user_id'];

        // ── Step 1: user name ──────────────────────────────────
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $errors[] = 'Le nom est obligatoire.';
        }

        // ── Step 2: company ────────────────────────────────────
        $companyName = trim($_POST['company_name'] ?? '');
        if ($companyName === '') {
            $errors[] = 'Le nom de l\'entreprise est obligatoire.';
        }

        if (!empty($errors)) {
            require __DIR__ . '/../views/onboarding/index.php';
            return;
        }

        // ── Persist user name ──────────────────────────────────
        $this->user->updateProfile($userId, $name);
        $_SESSION['user_name'] = $name;

        // ── Persist company settings ───────────────────────────
        $this->cs->save([
            'company_name'   => $companyName,
            'address'        => trim($_POST['address']        ?? ''),
            'phone'          => trim($_POST['phone']          ?? ''),
            'email'          => trim($_POST['email']          ?? ''),
            'tax_id'         => trim($_POST['tax_id']         ?? ''),
            'invoice_notes'  => trim($_POST['invoice_notes']  ?? ''),
            'invoice_footer' => trim($_POST['invoice_footer'] ?? ''),
        ]);

        // ── Logo upload (optional) ─────────────────────────────
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $logoErr = $this->cs->uploadLogo($_FILES['logo']);
            if ($logoErr) {
                $errors[] = $logoErr;
                require __DIR__ . '/../views/onboarding/index.php';
                return;
            }
        }

        // ── Mark onboarding complete ───────────────────────────
        $this->user->completeOnboarding($userId);
        $_SESSION['onboarding_completed'] = 1;

        setFlash('success', 'Bienvenue ! Votre espace est prêt.');
        redirect('dashboard');
    }

    private function requireLogin(): void
    {
        if (empty($_SESSION['user_id'])) {
            redirect('login');
        }
    }
}
