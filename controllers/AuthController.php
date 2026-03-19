<?php

require_once __DIR__ . '/../models/User.php';

class AuthController
{
    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }

    public function login(): void
    {
        // Already logged in
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
                if ($user && $this->user->verifyPassword($password, $user['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['user_id']   = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    redirect('dashboard');
                } else {
                    $errors[] = 'Email ou mot de passe incorrect.';
                }
            }
        }

        require __DIR__ . '/../views/auth/login.php';
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        redirect('login');
    }
}
