<?php
$pageTitle   = 'Nouveau client — ' . APP_NAME;
$breadcrumbs = ['Clients' => 'clients', 'Nouveau client' => ''];
require __DIR__ . '/../layout/header.php';
?>

<div class="d-flex align-items-center gap-2 mb-4">
  <a href="<?= APP_URL ?>/clients" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left"></i>
  </a>
  <h4 class="fw-bold mb-0">Nouveau client</h4>
</div>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
  <div class="card-body">
    <form method="POST" action="<?= APP_URL ?>/clients/store" novalidate>
      <?php require __DIR__ . '/form_fields.php'; ?>
      <div class="d-flex justify-content-end gap-2 mt-3">
        <a href="<?= APP_URL ?>/clients" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-check-lg me-1"></i> Enregistrer
        </button>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
