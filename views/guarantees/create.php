<?php
$pageTitle   = 'Nouveau certificat — ' . APP_NAME;
$breadcrumbs = ['Garanties' => 'guarantees', 'Nouveau certificat' => ''];
require __DIR__ . '/../layout/header.php';

$productsJson = json_encode(array_map(fn($p) => [
    'id'         => $p['id'],
    'name'       => $p['name'],
    'sku'        => $p['sku'],
    'unit_price' => (float) $p['unit_price'],
], $products));

$submittedItemsJson = json_encode(
    !empty($_POST['items']) ? array_values($_POST['items']) : []
);
?>

<div class="d-flex align-items-center gap-2 mb-4">
  <a href="<?= APP_URL ?>/guarantees" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left"></i>
  </a>
  <h4 class="fw-bold mb-0"><i class="bi bi-shield-plus me-2 text-primary"></i>Nouveau certificat de garantie</h4>
</div>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
  </div>
<?php endif; ?>

<form method="POST" action="<?= APP_URL ?>/guarantees/store" novalidate>
  <div class="row g-3">

    <!-- ── Header card ───────────────────────────────────────── -->
    <div class="col-12">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">Informations générales</div>
        <div class="card-body">
          <div class="row g-3">

            <div class="col-md-3">
              <label class="form-label fw-semibold">N° Certificat <span class="text-danger">*</span></label>
              <input type="text" name="certificate_number" class="form-control" required
                     value="<?= e($old['certificate_number'] ?? $nextNum) ?>">
            </div>

            <div class="col-md-3">
              <label class="form-label fw-semibold">Date d'émission</label>
              <input type="date" name="delivery_date" class="form-control"
                     value="<?= e($old['delivery_date'] ?? date('Y-m-d')) ?>">
            </div>

            <div class="col-md-3">
              <label class="form-label fw-semibold">Référence / Bon de livraison</label>
              <input type="text" name="reference" class="form-control"
                     placeholder="BL-2026-001"
                     value="<?= e($old['reference'] ?? '') ?>">
            </div>

            <div class="col-md-3">
              <label class="form-label fw-semibold">Facture liée</label>
              <select name="invoice_id" class="form-select">
                <option value="">— Aucune —</option>
                <?php foreach ($invoices as $inv): ?>
                  <option value="<?= $inv['id'] ?>"
                    <?= (int)($old['invoice_id'] ?? 0) === (int)$inv['id'] ? 'selected' : '' ?>>
                    <?= e($inv['invoice_number']) ?> — <?= e($inv['client_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <?php if (!empty($companies)): ?>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Entreprise</label>
              <select name="company_id" class="form-select">
                <option value="">— Paramètres généraux —</option>
                <?php foreach ($companies as $co): ?>
                  <option value="<?= $co['id'] ?>"
                    <?= (int)($old['company_id'] ?? 0) === (int)$co['id'] ? 'selected' : '' ?>>
                    <?= e($co['company_name']) ?><?= $co['is_active'] ? ' ★' : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
              <div class="form-check form-switch pb-1">
                <input class="form-check-input" type="checkbox" role="switch"
                       name="use_watermark" id="useWatermark" value="1"
                       <?= !empty($old['use_watermark']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="useWatermark">Filigrane</label>
              </div>
            </div>
            <?php endif; ?>

          </div>
        </div>
      </div>
    </div>

    <!-- ── Client card ───────────────────────────────────────── -->
    <div class="col-12">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">Client / Bénéficiaire</div>
        <div class="card-body">
          <div class="row g-3">

            <div class="col-md-6">
              <label class="form-label fw-semibold">Sélectionner un client</label>
              <select name="client_id" id="clientSelect" class="form-select">
                <option value="">— Saisie manuelle —</option>
                <?php foreach ($clients as $c): ?>
                  <option value="<?= $c['id'] ?>"
                          data-name="<?= e($c['name']) ?>"
                    <?= (int)($old['client_id'] ?? 0) === (int)$c['id'] ? 'selected' : '' ?>>
                    <?= e($c['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Nom du bénéficiaire <span class="text-danger">*</span></label>
              <input type="text" name="customer_name" id="customerName" class="form-control"
                     placeholder="Nom complet du bénéficiaire"
                     value="<?= e($old['customer_name'] ?? '') ?>" required>
            </div>

          </div>
        </div>
      </div>
    </div>

    <!-- ── Line items ─────────────────────────────────────────── -->
    <div class="col-12">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold d-flex align-items-center justify-content-between">
          <span>Articles couverts par la garantie <span class="text-muted fw-normal small">(optionnel si description ci-dessous)</span></span>
          <button type="button" class="btn btn-sm btn-outline-primary" id="addLine">
            <i class="bi bi-plus-lg me-1"></i> Ajouter un article
          </button>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table align-middle mb-0" id="itemsTable">
              <thead class="table-light">
                <tr>
                  <th style="width:35%">Désignation</th>
                  <th style="width:20%">Produit catalogue</th>
                  <th style="width:10%">Qté</th>
                  <th style="width:17%">Prix unit. (optionnel)</th>
                  <th style="width:13%">Total</th>
                  <th style="width:5%"></th>
                </tr>
              </thead>
              <tbody id="itemsBody"></tbody>
            </table>
          </div>
        </div>
        <div class="card-footer bg-transparent d-flex justify-content-end">
          <div id="totalBlock" style="display:none;">
            <span class="text-muted me-3">Total :</span>
            <span class="fw-bold fs-5 text-primary" id="displayTotal">0,00 MAD</span>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Description générale ──────────────────────────────── -->
    <div class="col-12">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">
          Description générale <span class="text-muted fw-normal small">(affichée si aucun article ci-dessus)</span>
        </div>
        <div class="card-body">
          <textarea name="product_details" class="form-control" rows="3"
                    placeholder="Décrivez brièvement le produit ou service couvert…"><?= e($old['product_details'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <!-- ── Warranty period ────────────────────────────────────── -->
    <div class="col-md-5">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white fw-semibold">
          <i class="bi bi-calendar-range me-2 text-primary"></i>Période de garantie
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-6">
              <label class="form-label fw-semibold">Début <span class="text-danger">*</span></label>
              <input type="date" name="start_date" id="startDate" class="form-control" required
                     value="<?= e($old['start_date'] ?? date('Y-m-d')) ?>">
            </div>
            <div class="col-6">
              <label class="form-label fw-semibold">Fin <span class="text-danger">*</span></label>
              <input type="date" name="end_date" id="endDate" class="form-control" required
                     value="<?= e($old['end_date'] ?? date('Y-m-d', strtotime('+1 year'))) ?>">
            </div>
          </div>
          <div id="durationBadge" class="alert alert-info py-2 small mt-3 mb-0"></div>

          <div class="mt-3">
            <div class="text-muted small mb-2">Durées rapides :</div>
            <div class="d-flex gap-2 flex-wrap">
              <?php foreach ([6 => '6 mois', 12 => '1 an', 24 => '2 ans', 36 => '3 ans'] as $m => $l): ?>
                <button type="button" class="btn btn-outline-secondary btn-sm duration-btn" data-months="<?= $m ?>"><?= $l ?></button>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Terms ─────────────────────────────────────────────── -->
    <div class="col-md-7">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white fw-semibold">
          <i class="bi bi-file-text me-2 text-primary"></i>Conditions de garantie
        </div>
        <div class="card-body">
          <textarea name="terms" class="form-control" rows="8"><?= e($old['terms'] ?? $defaultTerms) ?></textarea>
          <div class="form-text">Vous pouvez personnaliser les conditions avant d'enregistrer.</div>
        </div>
      </div>
    </div>

    <!-- ── Notes ─────────────────────────────────────────────── -->
    <div class="col-12">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">
          <i class="bi bi-sticky me-2 text-warning"></i>Notes internes
        </div>
        <div class="card-body">
          <textarea name="notes" class="form-control" rows="2"
                    placeholder="Remarques internes (non affichées sur le certificat)"><?= e($old['notes'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <!-- ── Actions ────────────────────────────────────────────── -->
    <div class="col-12 d-flex justify-content-end gap-2">
      <a href="<?= APP_URL ?>/guarantees" class="btn btn-outline-secondary">Annuler</a>
      <button type="submit" class="btn btn-primary">
        <i class="bi bi-check-lg me-1"></i> Créer le certificat
      </button>
    </div>

  </div>
</form>

<?php
$extraJs = <<<JS
<script>
const PRODUCTS  = {$productsJson};
const SUBMITTED = {$submittedItemsJson};

document.addEventListener('DOMContentLoaded', function () {
  // Auto-fill client name from dropdown
  document.getElementById('clientSelect').addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    if (opt.value && opt.dataset.name) {
      document.getElementById('customerName').value = opt.dataset.name;
    }
  });

  // Restore submitted items or start with one empty row
  if (SUBMITTED.length > 0) {
    SUBMITTED.forEach(function (item) { addRow(item); });
  }

  document.getElementById('addLine').addEventListener('click', function () { addRow(); });

  // Duration
  document.getElementById('startDate').addEventListener('change', updateDuration);
  document.getElementById('endDate').addEventListener('change', updateDuration);
  updateDuration();

  document.querySelectorAll('.duration-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const s = document.getElementById('startDate');
      if (!s.value) s.value = new Date().toISOString().slice(0,10);
      const start = new Date(s.value);
      start.setMonth(start.getMonth() + parseInt(this.dataset.months, 10));
      document.getElementById('endDate').value = start.toISOString().slice(0,10);
      updateDuration();
    });
  });
});

let rowIndex = 0;

function addRow(item) {
  item = item || {};
  const idx   = rowIndex++;
  const tbody = document.getElementById('itemsBody');

  const opts = PRODUCTS.map(function (p) {
    return '<option value="' + p.id + '" data-price="' + p.unit_price + '">' + escHtml(p.name) + ' (' + escHtml(p.sku) + ')</option>';
  }).join('');

  const tr = document.createElement('tr');
  tr.innerHTML =
    '<td>' +
      '<input type="text" name="items[' + idx + '][label]" class="form-control form-control-sm item-label"' +
      ' placeholder="Désignation du produit / service"' +
      ' value="' + escHtml(item.label || '') + '">' +
    '</td>' +
    '<td>' +
      '<select class="form-select form-select-sm item-product">' +
        '<option value="">— Libre —</option>' + opts +
      '</select>' +
      '<input type="hidden" name="items[' + idx + '][product_id]" class="item-product-id" value="' + (item.product_id || '') + '">' +
    '</td>' +
    '<td>' +
      '<input type="number" name="items[' + idx + '][quantity]" class="form-control form-control-sm item-qty"' +
      ' min="0.01" step="0.01" value="' + (item.quantity || 1) + '">' +
    '</td>' +
    '<td>' +
      '<input type="number" name="items[' + idx + '][unit_price]" class="form-control form-control-sm item-price"' +
      ' min="0" step="0.01" placeholder="—"' +
      ' value="' + (item.unit_price != null && item.unit_price !== '' ? parseFloat(item.unit_price).toFixed(2) : '') + '">' +
    '</td>' +
    '<td><span class="item-total text-muted small">—</span></td>' +
    '<td>' +
      '<button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="bi bi-x"></i></button>' +
    '</td>';

  tbody.appendChild(tr);

  if (item.product_id) tr.querySelector('.item-product').value = item.product_id;

  tr.querySelector('.item-product').addEventListener('change', function () {
    const pid = this.value;
    tr.querySelector('.item-product-id').value = pid;
    if (pid) {
      const prod = PRODUCTS.find(function (p) { return p.id == pid; });
      if (prod) {
        tr.querySelector('.item-label').value = prod.name;
        tr.querySelector('.item-price').value = prod.unit_price.toFixed(2);
      }
    }
    recalc();
  });

  tr.querySelector('.item-qty').addEventListener('input', recalc);
  tr.querySelector('.item-price').addEventListener('input', recalc);
  tr.querySelector('.btn-remove-row').addEventListener('click', function () { tr.remove(); recalc(); });

  recalc();
}

function recalc() {
  let total = 0;
  let hasPrice = false;

  document.querySelectorAll('#itemsBody tr').forEach(function (tr) {
    const qty   = parseFloat(tr.querySelector('.item-qty')?.value)   || 0;
    const price = parseFloat(tr.querySelector('.item-price')?.value);
    const span  = tr.querySelector('.item-total');
    if (!isNaN(price) && tr.querySelector('.item-price').value !== '') {
      const line = qty * price;
      total += line;
      hasPrice = true;
      if (span) span.textContent = formatMAD(line);
    } else {
      if (span) { span.textContent = '—'; span.className = 'item-total text-muted small'; }
    }
  });

  const block = document.getElementById('totalBlock');
  if (hasPrice) {
    block.style.display = '';
    document.getElementById('displayTotal').textContent = formatMAD(total);
  } else {
    block.style.display = 'none';
  }
}

function updateDuration() {
  const s = new Date(document.getElementById('startDate').value);
  const e = new Date(document.getElementById('endDate').value);
  const badge = document.getElementById('durationBadge');
  if (!document.getElementById('startDate').value || !document.getElementById('endDate').value || e <= s) {
    badge.textContent = '';
    return;
  }
  const days   = Math.round((e - s) / 86400000);
  const months = Math.round(days / 30.44);
  badge.textContent = 'Durée : ' + (months >= 12
    ? Math.floor(months / 12) + ' an(s)' + (months % 12 ? ' ' + (months % 12) + ' mois' : '')
    : months + ' mois') + ' (' + days + ' jours)';
}

function formatMAD(n) {
  return n.toLocaleString('fr-MA', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' MAD';
}

function escHtml(str) {
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
JS;
?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
