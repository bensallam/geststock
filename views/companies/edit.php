<?php
$pageTitle   = 'Modifier entreprise — ' . APP_NAME;
$breadcrumbs = ['Entreprises' => 'companies', e($company['company_name']) => ''];
require __DIR__ . '/../layout/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-bold mb-0">
    <i class="bi bi-building me-2 text-primary"></i><?= e($company['company_name']) ?>
    <?php if ($company['is_active']): ?>
      <span class="badge bg-primary fs-6 ms-2">Active</span>
    <?php endif; ?>
  </h4>
  <?php if (!$company['is_active']): ?>
  <form method="POST" action="<?= APP_URL ?>/companies/set-active">
    <input type="hidden" name="id" value="<?= $company['id'] ?>">
    <button type="submit" class="btn btn-outline-primary btn-sm">
      <i class="bi bi-check-circle me-1"></i> Définir comme active
    </button>
  </form>
  <?php endif; ?>
</div>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="row g-4">

  <!-- ─── Company info ─────────────────────────────────────── -->
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white fw-semibold">
        <i class="bi bi-building me-2 text-primary"></i>Informations
      </div>
      <div class="card-body">
        <form method="POST" action="<?= APP_URL ?>/companies/update" novalidate>
          <input type="hidden" name="id" value="<?= $company['id'] ?>">
          <?php require __DIR__ . '/partials/form_fields.php'; ?>
          <div class="d-flex justify-content-end gap-2 mt-3">
            <a href="<?= APP_URL ?>/companies" class="btn btn-outline-secondary">Retour</a>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg me-1"></i> Enregistrer
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- ─── Logo + Watermark ─────────────────────────────────── -->
  <div class="col-lg-4">

    <!-- Logo -->
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-white fw-semibold">
        <i class="bi bi-image me-2 text-primary"></i>Logo
      </div>
      <div class="card-body text-center">
        <div class="mb-3">
          <?php if (!empty($company['logo_path'])): ?>
            <img src="<?= APP_URL ?>/companies/logo?id=<?= $company['id'] ?>&v=<?= time() ?>"
                 alt="Logo" class="img-fluid rounded border" style="max-height:100px;">
          <?php else: ?>
            <div class="border rounded d-flex align-items-center justify-content-center text-muted"
                 style="height:80px; background:#f8f9fa;">
              <div><i class="bi bi-image fs-2 d-block mb-1"></i><span class="small">Aucun logo</span></div>
            </div>
          <?php endif; ?>
        </div>
        <form method="POST" action="<?= APP_URL ?>/companies/upload-logo"
              enctype="multipart/form-data">
          <input type="hidden" name="id" value="<?= $company['id'] ?>">
          <input type="file" name="logo" class="form-control form-control-sm mb-2"
                 accept="image/jpeg,image/png,image/gif,image/webp">
          <button type="submit" class="btn btn-primary btn-sm w-100">
            <i class="bi bi-upload me-1"></i> Télécharger
          </button>
        </form>
        <?php if (!empty($company['logo_path'])): ?>
          <form method="POST" action="<?= APP_URL ?>/companies/delete-logo" class="mt-2">
            <input type="hidden" name="id" value="<?= $company['id'] ?>">
            <button type="submit" class="btn btn-outline-danger btn-sm w-100"
                    onclick="return confirm('Supprimer le logo ?')">
              <i class="bi bi-trash me-1"></i> Supprimer
            </button>
          </form>
        <?php endif; ?>
      </div>
    </div>

    <!-- Watermark -->
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white fw-semibold">
        <i class="bi bi-droplet-half me-2 text-secondary"></i>Filigrane / Signature
      </div>
      <div class="card-body text-center">
        <div class="mb-3">
          <?php if (!empty($company['watermark_path'])): ?>
            <img src="<?= APP_URL ?>/companies/watermark?id=<?= $company['id'] ?>&v=<?= time() ?>"
                 alt="Filigrane" class="img-fluid rounded border"
                 style="max-height:80px; opacity:<?= e($company['watermark_opacity']) ?>;">
          <?php else: ?>
            <div class="border rounded d-flex align-items-center justify-content-center text-muted"
                 style="height:64px; background:#f8f9fa;">
              <span class="small">Aucun filigrane</span>
            </div>
          <?php endif; ?>
        </div>

        <form method="POST" action="<?= APP_URL ?>/companies/upload-watermark"
              enctype="multipart/form-data">
          <input type="hidden" name="id" value="<?= $company['id'] ?>">
          <input type="file" name="watermark" class="form-control form-control-sm mb-2"
                 accept="image/jpeg,image/png,image/gif,image/webp">
          <button type="submit" class="btn btn-secondary btn-sm w-100">
            <i class="bi bi-upload me-1"></i> Télécharger
          </button>
        </form>

        <?php if (!empty($company['watermark_path'])): ?>
          <!-- Opacity -->
          <form method="POST" action="<?= APP_URL ?>/companies/update-opacity" class="mt-3">
            <input type="hidden" name="id" value="<?= $company['id'] ?>">
            <label class="form-label small text-muted mb-1">
              Opacité : <span id="opacityVal"><?= round($company['watermark_opacity'] * 100) ?>%</span>
            </label>
            <input type="range" name="watermark_opacity" class="form-range mb-2"
                   min="0.05" max="1" step="0.05"
                   value="<?= e($company['watermark_opacity']) ?>"
                   oninput="document.getElementById('opacityVal').textContent = Math.round(this.value*100)+'%'">
            <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
              Mettre à jour
            </button>
          </form>
          <form method="POST" action="<?= APP_URL ?>/companies/delete-watermark" class="mt-2">
            <input type="hidden" name="id" value="<?= $company['id'] ?>">
            <button type="submit" class="btn btn-outline-danger btn-sm w-100"
                    onclick="return confirm('Supprimer le filigrane ?')">
              <i class="bi bi-trash me-1"></i> Supprimer
            </button>
          </form>
        <?php endif; ?>

        <div class="form-text mt-2">
          Affiché en filigrane centré sur les documents imprimés (si activé par document).
        </div>
      </div>
    </div>

  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
