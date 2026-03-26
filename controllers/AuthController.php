<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Company.php';

class AuthController
{
    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }

    // ─── Login ──────────────────────────────────────────────

    public function login(): void
    {
        if (!empty($_SESSION['user_id'])) {
            redirect('dashboard');
        }

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email']    ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($email) || empty($password)) {
                $errors[] = 'Veuillez remplir tous les champs.';
            } else {
                $user = $this->user->findByEmail($email);

                if ($user && ($user['is_active'] ?? 1) && $this->user->verifyPassword($password, $user['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['user_id']             = $user['id'];
                    $_SESSION['user_name']            = $user['name'] ?? $user['full_name'] ?? 'Utilisateur';
                    $_SESSION['company_id']           = $user['company_id'] ?? null;
                    $_SESSION['onboarding_completed'] = (int) ($user['onboarding_completed'] ?? 1);

                    if (empty($user['onboarding_completed'])) {
                        redirect('onboarding');
                    }
                    redirect('dashboard');
                } else {
                    $errors[] = 'Email ou mot de passe incorrect.';
                }
            }
        }

        require __DIR__ . '/../views/auth/login.php';
    }

    // ─── Register ───────────────────────────────────────────

    public function register(): void
    {
        if (!empty($_SESSION['user_id'])) {
            redirect('dashboard');
        }

        $errors = [];
        $old    = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $companyName = trim($_POST['company_name'] ?? '');
            $email       = trim($_POST['email']        ?? '');
            $password    =      $_POST['password']     ?? '';
            $confirm     =      $_POST['password_confirm'] ?? '';
            $old         = $_POST;

            // ── Validation ──────────────────────────────────
            if ($companyName === '') {
                $errors[] = 'Le nom de la société est obligatoire.';
            }
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Adresse email invalide.';
            }
            if (strlen($password) < 8) {
                $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
            } elseif (!preg_match('/[A-Z]/', $password)) {
                $errors[] = 'Le mot de passe doit contenir au moins une majuscule.';
            } elseif (!preg_match('/[0-9]/', $password)) {
                $errors[] = 'Le mot de passe doit contenir au moins un chiffre.';
            }
            if ($password !== $confirm) {
                $errors[] = 'Les mots de passe ne correspondent pas.';
            }
            if (empty($errors) && $this->user->emailExists($email)) {
                $errors[] = 'Cette adresse email est déjà utilisée.';
            }

            // ── Create account ──────────────────────────────
            if (empty($errors)) {
                $pdo = db();
                $pdo->beginTransaction();
                try {
                    // 1. Create company
                    $cm        = new Company();
                    $companyId = $cm->create(['company_name' => $companyName]);
                    $cm->setActive($companyId);

                    // 2. Create admin user linked to that company
                    $userId = $this->user->create([
                        'company_id' => $companyId,
                        'email'      => $email,
                        'password'   => $password,
                        'name'       => $companyName,
                    ]);

                    // 3. Link company back to its owner user
                    $pdo->prepare('UPDATE companies SET user_id = :uid WHERE id = :id')
                        ->execute([':uid' => $userId, ':id' => $companyId]);

                    $pdo->commit();

                    // Auto-login
                    session_regenerate_id(true);
                    $_SESSION['user_id']             = $userId;
                    $_SESSION['user_name']            = $companyName;
                    $_SESSION['company_id']           = $companyId;
                    $_SESSION['onboarding_completed'] = 1;

                    setFlash('success', 'Bienvenue ! Votre compte a été créé avec succès.');
                    redirect('dashboard');
                } catch (Throwable $e) {
                    $pdo->rollBack();
                    $errors[] = 'Erreur lors de la création du compte. Veuillez réessayer.';
                }
            }
        }

        require __DIR__ . '/../views/auth/register.php';
    }

    // ─── Logout ─────────────────────────────────────────────

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        redirect('login');
    }
}
