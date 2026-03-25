<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Configuration initiale — <?= APP_NAME ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    body { background: #f0f4f8; min-height: 100vh; }

    .wizard-wrap { max-width: 680px; margin: 0 auto; padding: 40px 16px 60px; }

    /* Progress stepper */
    .stepper { display: flex; align-items: center; margin-bottom: 36px; }
    .step-item { display: flex; flex-direction: column; align-items: center; flex: 1; position: relative; }
    .step-item:not(:last-child)::after {
      content: '';
      position: absolute;
      top: 18px;
      left: 50%;
      width: 100%;
      height: 2px;
      background: #dee2e6;
      z-index: 0;
      transition: background .3s;
    }
    .step-item.done::after { background: #0d6efd; }
    .step-circle {
      width: 36px; height: 36px;
      border-radius: 50%;
      background: #dee2e6; color: #6c757d;
      display: flex; align-items: center; justify-content: center;
      font-weight: 700; font-size: 14px;
      position: relative; z-index: 1;
      transition: background .3s, color .3s;
    }
    .step-item.active .step-circle  { background: #0d6efd; color: #fff; box-shadow: 0 0 0 4px rgba(13,110,253,.2); }
    .step-item.done   .step-circle  { background: #0d6efd; color: #fff; }
    .step-label { font-size: 11px; margin-top: 6px; color: #6c757d; font-weight: 500; text-align: center; }
    .step-item.active .step-label,
    .step-item.done   .step-label { color: #0d6efd; }

    /* Cards */
    .wizard-card { background: #fff; border-radius: 12px; padding: 36px 40px; box-shadow: 0 2px 16px rgba(0,0,0,.07); }
    .wizard-card .step-panel { display: none; }
    .wizard-card .step-panel.active { display: block; }

    .step-icon { font-size: 2.2rem; color: #0d6efd; margin-bottom: 8px; }
    .step-title { font-size: 1.3rem; font-weight: 700; margin-bottom: 4px; }
    .step-subtitle { color: #6c757d; font-size: .9rem; margin-bottom: 28px; }

    /* Logo preview */
    #logoPreview { width: 120px; height: 120px; border-radius: 8px; object-fit: contain;
                   border: 2px solid #e9ecef; padding: 6px; display: none; }
    #logoPlaceholder { width: 120px; height: 120px; border-radius: 8px;
                        border: 2px dashed #dee2e6; background: #f8f9fa;
                        display: flex; align-items: center; justify-content: center;
                        color: #adb5bd; font-size: .8rem; text-align: center; cursor: pointer; }

    /* Nav buttons */
    .wizard-nav { display: flex; justify-content: space-between; margin-top: 32px; padding-top: 20px;
                  border-top: 1px solid #f0f0f0; }
  </style>
</head>
<body>

<div class="wizard-wrap">

  <div class="text-center mb-4">
    <i class="bi bi-box-seam text-primary" style="font-size:2rem;"></i>
    <h3 class="fw-bold mt-1"><?= APP_NAME ?></h3>
    <p class="text-muted small">Configurons votre espace en quelques étapes</p>
  </div>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger mb-3">
      <ul class="mb-0">
        <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <!-- Stepper -->
  <div class="stepper" id="stepper">
    <div class="step-item active" id="si-1">
      <div class="step-circle">1</div>
      <div class="step-label">Votre profil</div>
    </div>
    <div class="step-item" id="si-2">
      <div class="step-circle">2</div>
      <div class="step-label">Entreprise</div>
    </div>
    <div class="step-item" id="si-3">
      <div class="step-circle">3</div>
      <div class="step-label">Logo & options</div>
    </div>
  </div>

  <!-- Wizard form -->
  <form method="POST" action="<?= APP_URL ?>/onboarding/store"
        enctype="multipart/form-data" novalidate id="wizardForm">

    <div class="wizard-card">

      <!-- ── Step 1 : User profile ──────────────────────── -->
      <div class="step-panel active" id="panel-1">
        <div class="step-icon"><i class="bi bi-person-circle"></i></div>
        <div class="step-title">Votre profil</div>
        <div class="step-subtitle">Comment souhaitez-vous être identifié dans l'application ?</div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Nom complet <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control form-control-lg"
                 placeholder="Ex: Ahmed Benali"
                 value="<?= e($old['name'] ?? $_SESSION['user_name'] ?? '') ?>"
                 id="field-name">
          <div class="invalid-feedback">Le nom est obligatoire.</div>
        </div>
      </div>

      <!-- ── Step 2 : Company info ──────────────────────── -->
      <div class="step-panel" id="panel-2">
        <div class="step-icon"><i class="bi bi-building"></i></div>
        <div class="step-title">Votre entreprise</div>
        <div class="step-subtitle">Ces informations apparaîtront sur toutes vos factures.</div>

        <div class="row g-3">
          <div class="col-12">
            <label class="form-label fw-semibold">Nom de l'entreprise <span class="text-danger">*</span></label>
            <input type="text" name="company_name" class="form-control"
                   placeholder="Ex: SARL Achat & Vente"
                   value="<?= e($old['company_name'] ?? '') ?>"
                   id="field-company">
            <div class="invalid-feedback">Le nom de l'entreprise est obligatoire.</div>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Téléphone</label>
            <input type="text" name="phone" class="form-control"
                   placeholder="0522-123456"
                   value="<?= e($old['phone'] ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Email de contact</label>
            <input type="email" name="email" class="form-control"
                   placeholder="contact@entreprise.ma"
                   value="<?= e($old['email'] ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Identifiant fiscal (ICE / IF)</label>
            <input type="text" name="tax_id" class="form-control"
                   placeholder="001234567000012"
                   value="<?= e($old['tax_id'] ?? '') ?>">
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Adresse</label>
            <textarea name="address" class="form-control" rows="2"
                      placeholder="Rue, Ville, Code postal"><?= e($old['address'] ?? '') ?></textarea>
          </div>
        </div>
      </div>

      <!-- ── Step 3 : Logo & invoice options ───────────── -->
      <div class="step-panel" id="panel-3">
        <div class="step-icon"><i class="bi bi-image"></i></div>
        <div class="step-title">Logo & personnalisation</div>
        <div class="step-subtitle">Optionnel — vous pourrez modifier cela à tout moment depuis les Paramètres.</div>

        <div class="mb-4">
          <label class="form-label fw-semibold">Logo de l'entreprise</label>
          <div class="d-flex align-items-center gap-3">
            <div id="logoPlaceholder" onclick="document.getElementById('logoInput').click()">
              <div><i class="bi bi-cloud-upload fs-4 d-block mb-1"></i>Cliquez pour<br>choisir</div>
            </div>
            <img id="logoPreview" src="" alt="Aperçu logo">
            <div>
              <input type="file" name="logo" id="logoInput"
                     accept="image/jpeg,image/png,image/gif,image/webp"
                     class="form-control form-control-sm" style="max-width:260px;">
              <div class="form-text mt-1">JPEG, PNG, WebP ou GIF — max 2 Mo</div>
            </div>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">
            <i class="bi bi-sticky me-1 text-warning"></i>Notes par défaut sur les factures
          </label>
          <textarea name="invoice_notes" class="form-control" rows="2"
                    placeholder="Ex: Paiement à 30 jours, pénalités de retard…"><?= e($old['invoice_notes'] ?? '') ?></textarea>
          <div class="form-text">Pré-rempli dans le champ Notes de chaque nouvelle facture.</div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">
            <i class="bi bi-card-text me-1 text-secondary"></i>Pied de page des factures
          </label>
          <input type="text" name="invoice_footer" class="form-control"
                 placeholder="Ex: Merci pour votre confiance."
                 value="<?= e($old['invoice_footer'] ?? 'Merci pour votre confiance.') ?>">
        </div>
      </div>

      <!-- ── Navigation ─────────────────────────────────── -->
      <div class="wizard-nav">
        <button type="button" class="btn btn-outline-secondary" id="btnPrev" style="display:none;">
          <i class="bi bi-arrow-left me-1"></i> Précédent
        </button>
        <div></div>
        <button type="button" class="btn btn-primary px-4" id="btnNext">
          Suivant <i class="bi bi-arrow-right ms-1"></i>
        </button>
        <button type="submit" class="btn btn-success px-4" id="btnSubmit" style="display:none;">
          <i class="bi bi-check-lg me-1"></i> Terminer
        </button>
      </div>

    </div><!-- /.wizard-card -->
  </form>

</div><!-- /.wizard-wrap -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
  const TOTAL   = 3;
  let current   = 1;

  // If server returned errors, start on step 1 (already there by default)

  const panels    = id => document.getElementById('panel-' + id);
  const stepItems = id => document.getElementById('si-' + id);
  const btnPrev   = document.getElementById('btnPrev');
  const btnNext   = document.getElementById('btnNext');
  const btnSubmit = document.getElementById('btnSubmit');

  function goTo(step) {
    // Hide old panel
    panels(current).classList.remove('active');
    stepItems(current).classList.remove('active');

    // Mark previous steps done
    for (let i = 1; i < step; i++) stepItems(i).classList.add('done');
    for (let i = step; i <= TOTAL; i++) stepItems(i).classList.remove('done');

    current = step;

    // Show new panel
    panels(current).classList.add('active');
    stepItems(current).classList.add('active');

    btnPrev.style.display   = current > 1     ? 'inline-block' : 'none';
    btnNext.style.display   = current < TOTAL ? 'inline-block' : 'none';
    btnSubmit.style.display = current === TOTAL ? 'inline-block' : 'none';
  }

  function validateStep(step) {
    if (step === 1) {
      const name = document.getElementById('field-name');
      if (!name.value.trim()) {
        name.classList.add('is-invalid');
        name.focus();
        return false;
      }
      name.classList.remove('is-invalid');
    }
    if (step === 2) {
      const co = document.getElementById('field-company');
      if (!co.value.trim()) {
        co.classList.add('is-invalid');
        co.focus();
        return false;
      }
      co.classList.remove('is-invalid');
    }
    return true;
  }

  btnNext.addEventListener('click', function () {
    if (validateStep(current)) goTo(current + 1);
  });

  btnPrev.addEventListener('click', function () {
    goTo(current - 1);
  });

  // Logo preview
  const logoInput       = document.getElementById('logoInput');
  const logoPreview     = document.getElementById('logoPreview');
  const logoPlaceholder = document.getElementById('logoPlaceholder');

  logoInput.addEventListener('change', function () {
    if (!this.files || !this.files[0]) return;
    const reader = new FileReader();
    reader.onload = function (e) {
      logoPreview.src = e.target.result;
      logoPreview.style.display   = 'block';
      logoPlaceholder.style.display = 'none';
    };
    reader.readAsDataURL(this.files[0]);
  });

  // If there were server-side errors, jump to the right step
  <?php if (!empty($errors)): ?>
  const errText = <?= json_encode(implode(' ', $errors)) ?>;
  if (/nom est obligatoire/.test(errText)) goTo(1);
  else if (/entreprise/.test(errText))     goTo(2);
  else                                     goTo(3);
  <?php endif; ?>
}());
</script>
</body>
</html>
