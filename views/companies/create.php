<?php
$pageTitle   = 'Nouvelle entreprise — ' . APP_NAME;
$breadcrumbs = ['Entreprises' => 'companies', 'Nouvelle' => ''];
require __DIR__ . '/../layout/header.php';
?>

<div class="d-flex align-items-center gap-2 mb-4">
  <h4 class="fw-bold mb-0"><i class="bi bi-building-add me-2 text-primary"></i>Nouvelle entreprise</h4>
</div>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="card border-0 shadow-sm" style="max-width:720px;">
  <div class="card-body">
    <form method="POST" action="<?= APP_URL ?>/companies/store" novalidate>
      <?php require __DIR__ . '/partials/form_fields.php'; ?>
      <div class="d-flex justify-content-end gap-2 mt-3">
        <a href="<?= APP_URL ?>/companies" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-check-lg me-1"></i> Créer
        </button>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
