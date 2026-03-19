<?php
/**
 * Application-wide configuration.
 */

define('APP_NAME',     'GestStock');
define('APP_VERSION',  '1.0.0');
define('APP_URL',      'http://localhost:8080');
define('APP_TIMEZONE', 'Africa/Casablanca');
define('APP_LOCALE',   'fr_MA');

// Currency
define('CURRENCY',     'MAD');
define('TAX_RATE',     20.00);  // TVA default %

// Session
define('SESSION_NAME', 'facturation_sess');

// PDF output directory (writable)
define('PDF_DIR', __DIR__ . '/../storage/pdf/');

date_default_timezone_set(APP_TIMEZONE);

// ─── Helpers ────────────────────────────────────────────────

/**
 * Format a number as Moroccan currency (MAD).
 */
function formatMoney(float $amount): string
{
    return number_format($amount, 2, ',', ' ') . ' MAD';
}

/**
 * Format a date to French locale.
 */
function formatDate(string $date): string
{
    if (empty($date)) return '—';
    $months = [
        1=>'janvier',2=>'février',3=>'mars',4=>'avril',
        5=>'mai',6=>'juin',7=>'juillet',8=>'août',
        9=>'septembre',10=>'octobre',11=>'novembre',12=>'décembre'
    ];
    $ts = strtotime($date);
    return date('d', $ts) . ' ' . $months[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

/**
 * Escape output for HTML.
 */
function e(mixed $val): string
{
    return htmlspecialchars((string)$val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Redirect to a URL and exit.
 */
function redirect(string $path): never
{
    header('Location: ' . APP_URL . '/' . ltrim($path, '/'));
    exit;
}

/**
 * Set a flash message in session.
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear the flash message.
 */
function getFlash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Require authenticated session or redirect to login.
 */
function requireAuth(): void
{
    if (empty($_SESSION['user_id'])) {
        redirect('login');
    }
}

/**
 * Generate the next invoice number.
 */
function nextInvoiceNumber(): string
{
    $year = date('Y');
    $stmt = db()->prepare(
        "SELECT invoice_number FROM invoices
         WHERE invoice_number LIKE :prefix
         ORDER BY id DESC LIMIT 1"
    );
    $stmt->execute([':prefix' => "FAC-{$year}-%"]);
    $last = $stmt->fetchColumn();

    if ($last) {
        $n = (int) substr($last, strrpos($last, '-') + 1);
        return sprintf('FAC-%s-%03d', $year, $n + 1);
    }

    return "FAC-{$year}-001";
}

/**
 * Stock status label/badge.
 */
function stockStatus(int $qty, int $min): array
{
    if ($qty <= 0) {
        return ['label' => 'Rupture de stock', 'class' => 'danger'];
    }
    if ($qty <= $min) {
        return ['label' => 'Stock faible',     'class' => 'warning'];
    }
    return ['label' => 'En stock', 'class' => 'success'];
}
