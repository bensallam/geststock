<?php
/**
 * Database configuration and PDO connection singleton.
 */

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'facturation');
define('DB_USER', 'root');
define('DB_PASS', 'password');
define('DB_CHARSET', 'utf8mb4');

/**
 * Returns a shared PDO instance (singleton pattern).
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Show a user-friendly error (never expose credentials in production)
            die('<div style="font-family:sans-serif;color:#c0392b;padding:2rem;">
                    <h2>Erreur de connexion à la base de données</h2>
                    <p>' . htmlspecialchars($e->getMessage()) . '</p>
                 </div>');
        }
    }

    return $pdo;
}
