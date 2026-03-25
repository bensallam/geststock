<?php
$pageTitle   = 'Entreprises — ' . APP_NAME;
$breadcrumbs = ['Entreprises' => ''];
require __DIR__ . '/../layout/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-bold mb-0"><i class="bi bi-buildings me-2 text-primary"></i>Entreprises</h4>
  <a href="<?= APP_URL ?>/companies/create" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i> Nouvelle entreprise
  </a>
</div>

<?php if (empty($companies)): ?>
  <div class="alert alert-info">
    Aucune entreprise configurée.
    <a href="<?= APP_URL ?>/companies/create">Créer la première entreprise</a>.
  </div>
<?php else: ?>
<div class="row g-3">
  <?php foreach ($companies as $co): ?>
  <div class="col-md-6 col-lg-4">
    <div class="card border-0 shadow-sm h-100 <?= $co['is_active'] ? 'border-primary border' : '' ?>">
      <div class="card-body">
        <div class="d-flex align-items-start gap-3 mb-3">
          <?php if (!empty($co['logo_path'])): ?>
            <img src="<?= APP_URL ?>/companies/logo?id=<?= $co['id'] ?>"
                 alt="Logo" style="height:48px; width:auto; object-fit:contain; flex-shrink:0;">
          <?php else: ?>
            <div class="d-flex align-items-center justify-content-center bg-light rounded"
                 style="width:48px; height:48px; flex-shrink:0;">
              <i class="bi bi-building text-muted fs-5"></i>
            </div>
          <?php endif; ?>
          <div class="flex-grow-1 min-width-0">
            <div class="fw-bold text-truncate"><?= e($co['company_name']) ?></div>
            <?php if (!empty($co['tax_id'])): ?>
              <div class="text-muted small">ICE : <?= e($co['tax_id']) ?></div>
            <?php endif; ?>
            <?php if (!empty($co['phone'])): ?>
              <div class="text-muted small"><?= e($co['phone']) ?></div>
            <?php endif; ?>
          </div>
          <?php if ($co['is_active']): ?>
            <span class="badge bg-primary">Active</span>
          <?php endif; ?>
        </div>

        <div class="d-flex gap-2 flex-wrap">
          <a href="<?= APP_URL ?>/companies/edit?id=<?= $co['id'] ?>"
             class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-pencil me-1"></i> Modifier
          </a>

          <?php if (!$co['is_active']): ?>
          <form method="POST" action="<?= APP_URL ?>/companies/set-active" class="d-inline">
            <input type="hidden" name="id" value="<?= $co['id'] ?>">
            <button type="submit" class="btn btn-sm btn-outline-primary">
              <i class="bi bi-check-circle me-1"></i> Définir active
            </button>
          </form>
          <?php endif; ?>

          <form method="POST" action="<?= APP_URL ?>/companies/delete" class="d-inline ms-auto"
                onsubmit="return confirm('Supprimer cette entreprise ? Les documents liés conserveront les données actuelles.')">
            <input type="hidden" name="id" value="<?= $co['id'] ?>">
            <button type="submit" class="btn btn-sm btn-outline-danger">
              <i class="bi bi-trash"></i>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
