<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle ?? APP_NAME) ?></title>
  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- Custom styles -->
  <link rel="stylesheet" href="<?= APP_URL ?>/public/css/app.css">
</head>
<body>
<div class="d-flex" id="wrapper">

  <!-- ─── Sidebar ─────────────────────────────────────────── -->
  <nav id="sidebar" class="bg-dark text-white d-flex flex-column">
    <div class="sidebar-header px-3 py-4">
      <a href="<?= APP_URL ?>/dashboard" class="text-white text-decoration-none d-flex align-items-center gap-2">
        <i class="bi bi-box-seam fs-4 text-primary"></i>
        <span class="fw-bold fs-5"><?= APP_NAME ?></span>
      </a>
    </div>

    <?php $route = trim($_GET['route'] ?? 'dashboard', '/'); ?>

    <ul class="nav flex-column px-2 flex-grow-1">
      <li class="nav-item">
        <a href="<?= APP_URL ?>/dashboard"
           class="nav-link text-white <?= str_starts_with($route,'dashboard') || $route==='' ? 'active' : '' ?>">
          <i class="bi bi-speedometer2 me-2"></i> Tableau de bord
        </a>
      </li>

      <li class="sidebar-label px-3 pt-3 pb-1 text-uppercase text-secondary small">Catalogue</li>

      <li class="nav-item">
        <a href="<?= APP_URL ?>/products"
           class="nav-link text-white <?= str_starts_with($route,'products') ? 'active' : '' ?>">
          <i class="bi bi-box me-2"></i> Produits
        </a>
      </li>

      <li class="nav-item">
        <a href="<?= APP_URL ?>/stock"
           class="nav-link text-white <?= str_starts_with($route,'stock') ? 'active' : '' ?>">
          <i class="bi bi-layers me-2"></i> Stock
        </a>
      </li>

      <li class="sidebar-label px-3 pt-3 pb-1 text-uppercase text-secondary small">Ventes</li>

      <li class="nav-item">
        <a href="<?= APP_URL ?>/clients"
           class="nav-link text-white <?= str_starts_with($route,'clients') ? 'active' : '' ?>">
          <i class="bi bi-people me-2"></i> Clients
        </a>
      </li>

      <li class="nav-item">
        <a href="<?= APP_URL ?>/invoices"
           class="nav-link text-white <?= str_starts_with($route,'invoices') ? 'active' : '' ?>">
          <i class="bi bi-receipt me-2"></i> Factures
        </a>
      </li>
    </ul>

    <div class="px-3 py-3 border-top border-secondary">
      <div class="d-flex align-items-center gap-2 mb-2">
        <i class="bi bi-person-circle fs-5"></i>
        <span class="small"><?= e($_SESSION['user_name'] ?? 'Utilisateur') ?></span>
      </div>
      <a href="<?= APP_URL ?>/logout" class="btn btn-sm btn-outline-secondary w-100">
        <i class="bi bi-box-arrow-right me-1"></i> Déconnexion
      </a>
    </div>
  </nav>

  <!-- ─── Main Content ─────────────────────────────────────── -->
  <div id="page-content" class="flex-grow-1 d-flex flex-column min-vh-100">

    <!-- Topbar -->
    <header class="topbar bg-white border-bottom px-4 py-2 d-flex align-items-center gap-3">
      <button id="sidebarToggle" class="btn btn-sm btn-light border">
        <i class="bi bi-list fs-5"></i>
      </button>
      <nav aria-label="breadcrumb" class="mb-0">
        <ol class="breadcrumb mb-0">
          <?php foreach ($breadcrumbs ?? [] as $label => $url): ?>
            <?php if ($url): ?>
              <li class="breadcrumb-item">
                <a href="<?= APP_URL . '/' . $url ?>"><?= e($label) ?></a>
              </li>
            <?php else: ?>
              <li class="breadcrumb-item active"><?= e($label) ?></li>
            <?php endif; ?>
          <?php endforeach; ?>
        </ol>
      </nav>
    </header>

    <!-- Flash messages -->
    <?php $flash = getFlash(); ?>
    <?php if ($flash): ?>
    <div class="px-4 pt-3">
      <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    </div>
    <?php endif; ?>

    <main class="flex-grow-1 p-4">
