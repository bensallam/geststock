<?php
/**
 * Front Controller – routes all requests.
 */

declare(strict_types=1);

// ─── Bootstrap ──────────────────────────────────────────────
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';

session_name(SESSION_NAME);
session_start();

// ─── Autoload controllers & models ──────────────────────────
spl_autoload_register(function (string $class): void {
    $dirs = [__DIR__ . '/controllers/', __DIR__ . '/models/'];
    foreach ($dirs as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// ─── Router ─────────────────────────────────────────────────
// Nginx uses try_files $uri $uri/ /index.php?$args — route comes from REQUEST_URI,
// not from a ?route= rewrite parameter (Apache style).
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$route       = trim($requestPath ?? '', '/');
$route       = $route === '' ? 'dashboard' : $route;
$method      = $_SERVER['REQUEST_METHOD'];

// Map routes → [controller, action]
$routes = [
    // Auth
    'login'                    => ['AuthController',      'login'],
    'logout'                   => ['AuthController',      'logout'],

    // Dashboard
    'dashboard'                => ['DashboardController', 'index'],
    ''                         => ['DashboardController', 'index'],

    // Products
    'products'                 => ['ProductController',   'index'],
    'products/create'          => ['ProductController',   'create'],
    'products/store'           => ['ProductController',   'store'],
    'products/show'            => ['ProductController',   'show'],
    'products/edit'            => ['ProductController',   'edit'],
    'products/update'          => ['ProductController',   'update'],
    'products/delete'          => ['ProductController',   'delete'],

    // Clients
    'clients'                  => ['ClientController',    'index'],
    'clients/create'           => ['ClientController',    'create'],
    'clients/store'            => ['ClientController',    'store'],
    'clients/edit'             => ['ClientController',    'edit'],
    'clients/update'           => ['ClientController',    'update'],
    'clients/delete'           => ['ClientController',    'delete'],

    // Invoices
    'invoices'                 => ['InvoiceController',   'index'],
    'invoices/create'          => ['InvoiceController',   'create'],
    'invoices/store'           => ['InvoiceController',   'store'],
    'invoices/show'            => ['InvoiceController',   'show'],
    'invoices/edit'            => ['InvoiceController',   'edit'],
    'invoices/update'          => ['InvoiceController',   'update'],
    'invoices/delete'          => ['InvoiceController',   'delete'],
    'invoices/print'           => ['InvoiceController',   'printView'],
    'invoices/pdf'             => ['InvoiceController',   'pdf'],

    // Stock
    'stock'                    => ['StockController',     'index'],
    'stock/adjust'             => ['StockController',     'adjust'],
    'stock/store'              => ['StockController',     'store'],
    'stock/movements'          => ['StockController',     'movements'],

    // Categories (AJAX)
    'categories/store'         => ['CategoryController',  'store'],

    // Delivery notes
    'delivery-notes'           => ['DeliveryNoteController', 'index'],
    'delivery-notes/create'    => ['DeliveryNoteController', 'create'],
    'delivery-notes/store'     => ['DeliveryNoteController', 'store'],
    'delivery-notes/show'      => ['DeliveryNoteController', 'show'],
    'delivery-notes/edit'      => ['DeliveryNoteController', 'edit'],
    'delivery-notes/update'    => ['DeliveryNoteController', 'update'],
    'delivery-notes/delete'    => ['DeliveryNoteController', 'delete'],
    'delivery-notes/print'     => ['DeliveryNoteController', 'printView'],
    'delivery-notes/pdf'       => ['DeliveryNoteController', 'pdf'],

    // Guarantees
    'guarantees'               => ['GuaranteeCertificateController', 'index'],
    'guarantees/create'        => ['GuaranteeCertificateController', 'create'],
    'guarantees/store'         => ['GuaranteeCertificateController', 'store'],
    'guarantees/show'          => ['GuaranteeCertificateController', 'show'],
    'guarantees/edit'          => ['GuaranteeCertificateController', 'edit'],
    'guarantees/update'        => ['GuaranteeCertificateController', 'update'],
    'guarantees/delete'        => ['GuaranteeCertificateController', 'delete'],
    'guarantees/print'         => ['GuaranteeCertificateController', 'printView'],
    'guarantees/pdf'           => ['GuaranteeCertificateController', 'pdf'],

    // Onboarding
    'onboarding'               => ['OnboardingController', 'show'],
    'onboarding/store'         => ['OnboardingController', 'store'],

    // Companies
    'companies'                    => ['CompanyController',   'index'],
    'companies/create'             => ['CompanyController',   'create'],
    'companies/store'              => ['CompanyController',   'store'],
    'companies/edit'               => ['CompanyController',   'edit'],
    'companies/update'             => ['CompanyController',   'update'],
    'companies/delete'             => ['CompanyController',   'delete'],
    'companies/set-active'         => ['CompanyController',   'setActive'],
    'companies/upload-logo'        => ['CompanyController',   'uploadLogo'],
    'companies/delete-logo'        => ['CompanyController',   'deleteLogo'],
    'companies/logo'               => ['CompanyController',   'logo'],
    'companies/upload-watermark'   => ['CompanyController',   'uploadWatermark'],
    'companies/delete-watermark'   => ['CompanyController',   'deleteWatermark'],
    'companies/update-opacity'     => ['CompanyController',   'updateOpacity'],
    'companies/watermark'          => ['CompanyController',   'watermark'],

    // Settings
    'settings'                 => ['SettingsController',  'index'],
    'settings/update'          => ['SettingsController',  'update'],
    'settings/upload-logo'     => ['SettingsController',  'uploadLogo'],
    'settings/delete-logo'     => ['SettingsController',  'deleteLogo'],
    'settings/logo'            => ['SettingsController',  'logo'],
];

if (isset($routes[$route])) {
    [$controllerClass, $action] = $routes[$route];

    $controllerFile = __DIR__ . '/controllers/' . $controllerClass . '.php';
    if (!file_exists($controllerFile)) {
        http_response_code(500);
        die("Controller file not found: {$controllerClass}");
    }

    require_once $controllerFile;
    $controller = new $controllerClass();

    if (!method_exists($controller, $action)) {
        http_response_code(500);
        die("Action {$action} not found in {$controllerClass}");
    }

    $controller->$action();
} else {
    // 404
    http_response_code(404);
    require __DIR__ . '/views/errors/404.php';
}
