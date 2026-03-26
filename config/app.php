<?php
/**
 * Application-wide configuration.
 */

define('APP_NAME',     'GestStock');
define('APP_VERSION',  '1.0.0');
define('APP_URL',      'https://fc.wegamachine.ma');
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

// ─── Proxy / HTTPS detection ────────────────────────────────
// Behind Varnish → Nginx, the backend (port 8080) receives HTTP.
// Nginx sets fastcgi_param HTTPS "on" and SERVER_PORT 443, so
// $_SERVER['HTTPS'] is already "on" inside PHP — no extra detection needed.
// For outgoing redirects we always use APP_URL which is https://.

// ─── Secure session (HTTPS only) ────────────────────────────
ini_set('session.cookie_secure',   '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', '1');

// ─── Real client IP (behind Varnish proxy) ──────────────────
if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    // Take the first IP in the chain (the real client)
    $realIp = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    $_SERVER['REMOTE_ADDR'] = filter_var($realIp, FILTER_VALIDATE_IP)
        ? $realIp
        : $_SERVER['REMOTE_ADDR'];
}

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
 * Also redirects to onboarding if not yet completed.
 */
function requireAuth(): void
{
    if (empty($_SESSION['user_id'])) {
        redirect('login');
    }
    if (empty($_SESSION['onboarding_completed'])) {
        redirect('onboarding');
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
 * Generate the next delivery note number.
 */
function nextDeliveryNoteNumber(): string
{
    $year = date('Y');
    $stmt = db()->prepare(
        "SELECT note_number FROM delivery_notes
         WHERE note_number LIKE :prefix ORDER BY id DESC LIMIT 1"
    );
    $stmt->execute([':prefix' => "BL-{$year}-%"]);
    $last = $stmt->fetchColumn();

    if ($last) {
        $n = (int) substr($last, strrpos($last, '-') + 1);
        return sprintf('BL-%s-%03d', $year, $n + 1);
    }

    return "BL-{$year}-001";
}

/**
 * Return the French label for a payment method code.
 */
function paymentMethodLabel(string $method): string
{
    return match ($method) {
        'cheque'   => 'Chèque',
        'espece'   => 'Espèce',
        'virement' => 'Virement bancaire',
        default    => $method,
    };
}

/**
 * Generate the next devis number.
 */
function nextDevisNumber(): string
{
    $year = date('Y');
    $stmt = db()->prepare(
        "SELECT devis_number FROM devis
         WHERE devis_number LIKE :prefix
         ORDER BY id DESC LIMIT 1"
    );
    $stmt->execute([':prefix' => "DEVIS-{$year}-%"]);
    $last = $stmt->fetchColumn();

    if ($last) {
        $n = (int) substr($last, strrpos($last, '-') + 1);
        return sprintf('DEVIS-%s-%03d', $year, $n + 1);
    }

    return "DEVIS-{$year}-001";
}

/**
 * Generate the next guarantee certificate number.
 */
function nextCertificateNumber(): string
{
    $year = date('Y');
    $stmt = db()->prepare(
        "SELECT certificate_number FROM guarantee_certificates
         WHERE certificate_number LIKE :prefix
         ORDER BY id DESC LIMIT 1"
    );
    $stmt->execute([':prefix' => "GAR-{$year}-%"]);
    $last = $stmt->fetchColumn();

    if ($last) {
        $n = (int) substr($last, strrpos($last, '-') + 1);
        return sprintf('GAR-%s-%03d', $year, $n + 1);
    }

    return "GAR-{$year}-001";
}

/**
 * Convert an amount to French words for MAD currency.
 * e.g. 15000.00 → "Quinze mille dirhams"
 */
function amountInWords(float $amount): string
{
    $int    = (int) floor($amount);
    $cents  = (int) round(($amount - $int) * 100);

    $words = _numToFr($int);
    $words = mb_strtoupper(mb_substr($words, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($words, 1, null, 'UTF-8');
    $words .= $int > 1 ? ' dirhams' : ' dirham';

    if ($cents > 0) {
        $words .= ' et ' . _numToFr($cents) . ($cents > 1 ? ' centimes' : ' centime');
    }

    return $words;
}

/**
 * Internal helper: integer to lowercase French words.
 */
function _numToFr(int $n): string
{
    if ($n === 0) return 'zéro';

    $ones = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf',
             'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize',
             'dix-sept', 'dix-huit', 'dix-neuf'];
    $tenlabels = ['', '', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante'];

    $r = '';

    if ($n >= 1000000) {
        $m  = (int)($n / 1000000);
        $r .= _numToFr($m) . ($m > 1 ? ' millions ' : ' million ');
        $n %= 1000000;
    }

    if ($n >= 1000) {
        $t  = (int)($n / 1000);
        $r .= $t === 1 ? 'mille ' : _numToFr($t) . ' mille ';
        $n %= 1000;
    }

    if ($n >= 100) {
        $h   = (int)($n / 100);
        $rem = $n % 100;
        $r  .= $h === 1 ? 'cent ' : $ones[$h] . ($rem === 0 ? ' cents ' : ' cent ');
        $n   = $rem;
    }

    if ($n >= 20) {
        $t = (int)($n / 10);
        $u = $n % 10;

        if ($t === 7) {
            $r .= $u === 1 ? 'soixante-et-onze ' : 'soixante-' . $ones[10 + $u] . ' ';
        } elseif ($t === 8) {
            $r .= $u === 0 ? 'quatre-vingts ' : 'quatre-vingt-' . $ones[$u] . ' ';
        } elseif ($t === 9) {
            $r .= 'quatre-vingt-' . $ones[10 + $u] . ' ';
        } else {
            if ($u === 0)      $r .= $tenlabels[$t] . ' ';
            elseif ($u === 1)  $r .= $tenlabels[$t] . '-et-un ';
            else               $r .= $tenlabels[$t] . '-' . $ones[$u] . ' ';
        }
    } elseif ($n > 0) {
        $r .= $ones[$n] . ' ';
    }

    return rtrim($r);
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
