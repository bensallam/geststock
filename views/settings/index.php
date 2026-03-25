<?php
$pageTitle   = 'Paramètres — ' . APP_NAME;
$breadcrumbs = ['Paramètres' => ''];
require __DIR__ . '/../layout/header.php';
?>

<div class="d-flex align-items-center gap-2 mb-4">
  <h4 class="fw-bold mb-0"><i class="bi bi-gear me-2 text-primary"></i>Paramètres de l'entreprise</h4>
</div>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="row g-4">

  <!-- ─── Company info ──────────────────────────────────── -->
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white fw-semibold">
        <i class="bi bi-building me-2 text-primary"></i>Informations de l'entreprise
      </div>
      <div class="card-body">
        <form method="POST" action="<?= APP_URL ?>/settings/update" novalidate>
          <div class="row g-3">

            <div class="col-md-6">
              <label class="form-label fw-semibold">Nom de l'entreprise <span class="text-danger">*</span></label>
              <input type="text" name="company_name" class="form-control" required
                     value="<?= e($company['company_name'] ?? '') ?>">
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Identifiant fiscal (ICE / IF / RC)</label>
              <input type="text" name="tax_id" class="form-control"
                     placeholder="Ex: 001234567000012"
                     value="<?= e($company['tax_id'] ?? '') ?>">
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Téléphone</label>
              <input type="text" name="phone" class="form-control"
                     placeholder="0522-123456"
                     value="<?= e($company['phone'] ?? '') ?>">
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Email</label>
              <input type="email" name="email" class="form-control"
                     placeholder="contact@entreprise.ma"
                     value="<?= e($company['email'] ?? '') ?>">
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold">Adresse</label>
              <textarea name="address" class="form-control" rows="2"
                        placeholder="Rue, Ville, Code postal"><?= e($company['address'] ?? '') ?></textarea>
            </div>

            <div class="col-12"><hr class="my-1"></div>

            <div class="col-12">
              <label class="form-label fw-semibold">
                <i class="bi bi-sticky me-1 text-warning"></i>
                Notes par défaut sur les factures
              </label>
              <textarea name="invoice_notes" class="form-control" rows="3"
                        placeholder="Ex: Conditions de paiement, délai de livraison…"><?= e($company['invoice_notes'] ?? '') ?></textarea>
              <div class="form-text">Pré-rempli dans le champ Notes de chaque nouvelle facture.</div>
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold">
                <i class="bi bi-card-text me-1 text-secondary"></i>
                Message de pied de page
              </label>
              <input type="text" name="invoice_footer" class="form-control"
                     placeholder="Ex: Merci pour votre confiance."
                     value="<?= e($company['invoice_footer'] ?? '') ?>">
              <div class="form-text">Affiché en bas de chaque document imprimé / PDF.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">
                <i class="bi bi-credit-card me-1 text-primary"></i>
                Mode de règlement par défaut
              </label>
              <select name="default_payment_method" class="form-select">
                <option value="">— Non précisé —</option>
                <?php foreach (['cheque' => 'Chèque', 'espece' => 'Espèce', 'virement' => 'Virement bancaire'] as $val => $lbl): ?>
                  <option value="<?= $val ?>" <?= ($company['default_payment_method'] ?? '') === $val ? 'selected' : '' ?>>
                    <?= $lbl ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="form-text">Pré-sélectionné dans les nouveaux documents.</div>
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold">
                <i class="bi bi-shield me-1 text-success"></i>
                Conditions de garantie par défaut
              </label>
              <textarea name="default_warranty_terms" class="form-control" rows="5"
                        placeholder="Conditions pré-remplies dans les certificats de garantie…"><?= e($company['default_warranty_terms'] ?? '') ?></textarea>
              <div class="form-text">Pré-rempli dans le champ Conditions de chaque nouveau certificat de garantie.</div>
            </div>

            <div class="col-12 d-flex justify-content-end">
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i> Enregistrer
              </button>
            </div>

          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- ─── Logo ──────────────────────────────────────────── -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white fw-semibold">
        <i class="bi bi-image me-2 text-primary"></i>Logo de l'entreprise
      </div>
      <div class="card-body text-center">

        <!-- Current logo preview -->
        <div class="logo-preview mb-3">
          <?php if (!empty($company['logo_path'])): ?>
            <img src="<?= APP_URL ?>/settings/logo?v=<?= time() ?>"
                 alt="Logo" class="img-fluid rounded border"
                 style="max-height:120px; max-width:100%;">
          <?php else: ?>
            <div class="border rounded d-flex align-items-center justify-content-center text-muted"
                 style="height:120px; background:#f8f9fa;">
              <div>
                <i class="bi bi-image fs-1 d-block mb-1"></i>
                <span class="small">Aucun logo</span>
              </div>
            </div>
          <?php endif; ?>
        </div>

        <!-- Upload form -->
        <form method="POST" action="<?= APP_URL ?>/settings/upload-logo"
              enctype="multipart/form-data" id="logoForm">
          <div class="mb-3">
            <label class="form-label small text-muted">JPEG, PNG, WebP ou GIF — max 2 Mo</label>
            <input type="file" name="logo" id="logoInput" class="form-control form-control-sm"
                   accept="image/jpeg,image/png,image/gif,image/webp"
                   onchange="previewLogo(this)">
          </div>
          <button type="submit" class="btn btn-primary btn-sm w-100">
            <i class="bi bi-upload me-1"></i> Télécharger le logo
          </button>
        </form>

        <!-- Delete logo -->
        <?php if (!empty($company['logo_path'])): ?>
          <form method="POST" action="<?= APP_URL ?>/settings/delete-logo" class="mt-2">
            <button type="submit" class="btn btn-outline-danger btn-sm w-100"
                    onclick="return confirm('Supprimer le logo ?')">
              <i class="bi bi-trash me-1"></i> Supprimer le logo
            </button>
          </form>
        <?php endif; ?>

      </div>
    </div>

    <!-- Invoice preview card -->
    <div class="card border-0 shadow-sm mt-3">
      <div class="card-header bg-white fw-semibold">
        <i class="bi bi-eye me-2 text-primary"></i>Aperçu sur les factures
      </div>
      <div class="card-body p-3">
        <div class="d-flex align-items-start gap-3 p-2 border rounded bg-light">
          <?php if (!empty($company['logo_path'])): ?>
            <img src="<?= APP_URL ?>/settings/logo"
                 alt="Logo" style="height:48px; width:auto; object-fit:contain;">
          <?php else: ?>
            <div class="text-muted small fst-italic">Logo ici</div>
          <?php endif; ?>
          <div>
            <div class="fw-bold"><?= e($company['company_name'] ?: APP_NAME) ?></div>
            <?php if (!empty($company['address'])): ?>
              <div class="text-muted small"><?= nl2br(e($company['address'])) ?></div>
            <?php endif; ?>
            <?php if (!empty($company['phone'])): ?>
              <div class="text-muted small">Tél : <?= e($company['phone']) ?></div>
            <?php endif; ?>
            <?php if (!empty($company['email'])): ?>
              <div class="text-muted small"><?= e($company['email']) ?></div>
            <?php endif; ?>
          </div>
        </div>
        <?php if (!empty($company['invoice_footer'])): ?>
          <div class="mt-2 text-center text-muted small fst-italic border-top pt-2">
            <?= e($company['invoice_footer']) ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

<?php
$extraJs = <<<JS
<script>
function previewLogo(input) {
  if (!input.files || !input.files[0]) return;
  const reader = new FileReader();
  reader.onload = function(e) {
    const preview = document.querySelector('.logo-preview');
    preview.innerHTML = '<img src="' + e.target.result + '" class="img-fluid rounded border" style="max-height:120px;">';
  };
  reader.readAsDataURL(input.files[0]);
}
</script>
JS;
?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
