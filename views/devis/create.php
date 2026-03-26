<?php
$pageTitle   = 'Nouveau devis — ' . APP_NAME;
$breadcrumbs = ['Devis' => 'devis', 'Nouveau devis' => ''];
require __DIR__ . '/../layout/header.php';

$productsJson = json_encode(array_map(fn($p) => [
    'id'         => $p['id'],
    'name'       => $p['name'],
    'sku'        => $p['sku'],
    'unit_price' => (float) $p['unit_price'],
    'quantity'   => (int)   $p['quantity'],
], $products));

$submittedItems = [];
if (!empty($_POST['items'])) {
    $submittedItems = $_POST['items'];
}
?>

<div class="d-flex align-items-center gap-2 mb-4">
  <a href="<?= APP_URL ?>/devis" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left"></i>
  </a>
  <h4 class="fw-bold mb-0">Nouveau devis</h4>
</div>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="POST" action="<?= APP_URL ?>/devis/store" id="devisForm" novalidate>
  <div class="row g-3">

    <!-- Header card -->
    <div class="col-12">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">Informations générales</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label fw-semibold">N° Devis <span class="text-danger">*</span></label>
              <input type="text" name="devis_number" class="form-control" required
                     value="<?= e($old['devis_number'] ?? $nextNum) ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Client <span class="text-danger">*</span></label>
              <select name="client_id" class="form-select" required>
                <option value="">— Sélectionner —</option>
                <?php foreach ($clients as $c): ?>
                  <option value="<?= $c['id'] ?>"
                    <?= (int)($old['client_id'] ?? 0) === (int)$c['id'] ? 'selected' : '' ?>>
                    <?= e($c['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
              <input type="date" name="date" class="form-control" required
                     value="<?= e($old['date'] ?? date('Y-m-d')) ?>">
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">Valable jusqu'au</label>
              <input type="date" name="validity_date" class="form-control"
                     value="<?= e($old['validity_date'] ?? '') ?>">
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">TVA (%)</label>
              <input type="number" name="tax_rate" id="taxRate" class="form-control"
                     min="0" max="100" step="0.01"
                     value="<?= e($old['tax_rate'] ?? TAX_RATE) ?>">
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">Statut</label>
              <select name="status" class="form-select">
                <option value="draft"    <?= ($old['status'] ?? 'draft') === 'draft'    ? 'selected' : '' ?>>Brouillon</option>
                <option value="sent"     <?= ($old['status'] ?? '') === 'sent'     ? 'selected' : '' ?>>Envoyé</option>
                <option value="accepted" <?= ($old['status'] ?? '') === 'accepted' ? 'selected' : '' ?>>Accepté</option>
                <option value="rejected" <?= ($old['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Refusé</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">Mode de règlement</label>
              <select name="payment_method" class="form-select">
                <option value="">— Non précisé —</option>
                <?php foreach (['cheque' => 'Chèque', 'espece' => 'Espèce', 'virement' => 'Virement bancaire'] as $val => $lbl): ?>
                  <option value="<?= $val ?>" <?= ($old['payment_method'] ?? '') === $val ? 'selected' : '' ?>>
                    <?= $lbl ?>
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

    <!-- Line items card -->
    <div class="col-12">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold d-flex align-items-center justify-content-between">
          <span>Lignes du devis</span>
          <button type="button" class="btn btn-sm btn-outline-primary" id="addLine">
            <i class="bi bi-plus-lg me-1"></i> Ajouter une ligne
          </button>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table align-middle mb-0" id="itemsTable">
              <thead class="table-light">
                <tr>
                  <th style="width:35%">Désignation</th>
                  <th style="width:20%">Produit (optionnel)</th>
                  <th style="width:10%">Qté</th>
                  <th style="width:15%">Prix unit. HT</th>
                  <th style="width:15%">Total HT</th>
                  <th style="width:5%"></th>
                </tr>
              </thead>
              <tbody id="itemsBody"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Notes + totals -->
    <div class="col-md-7">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white fw-semibold">Notes</div>
        <div class="card-body">
          <textarea name="notes" class="form-control" rows="5"
                    placeholder="Conditions, remarques…"><?= e($old['notes'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <div class="col-md-5">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">Récapitulatif</div>
        <div class="card-body">
          <table class="table table-sm mb-0">
            <tr>
              <td class="text-muted">Montant HT</td>
              <td class="text-end fw-semibold" id="displayHT">0,00 MAD</td>
            </tr>
            <tr>
              <td class="text-muted">TVA (<span id="displayRate"><?= TAX_RATE ?></span>%)</td>
              <td class="text-end" id="displayTax">0,00 MAD</td>
            </tr>
            <tr class="table-active fw-bold">
              <td>Total TTC</td>
              <td class="text-end fs-5" id="displayTTC">0,00 MAD</td>
            </tr>
          </table>
        </div>
      </div>
    </div>

    <div class="col-12 d-flex justify-content-end gap-2">
      <a href="<?= APP_URL ?>/devis" class="btn btn-outline-secondary">Annuler</a>
      <button type="submit" class="btn btn-primary">
        <i class="bi bi-check-lg me-1"></i> Créer le devis
      </button>
    </div>

  </div>
</form>

<?php
$submittedItemsJson = json_encode($submittedItems);
$extraJs = <<<JS
<script>
const PRODUCTS  = {$productsJson};
const SUBMITTED = {$submittedItemsJson};

document.addEventListener('DOMContentLoaded', () => {
  if (SUBMITTED.length > 0) {
    SUBMITTED.forEach(item => addRow(item));
  } else {
    addRow();
  }
  recalc();
  document.getElementById('addLine').addEventListener('click', () => addRow());
  document.getElementById('taxRate').addEventListener('input', recalc);
});

let rowIndex = 0;

function addRow(item = {}) {
  const idx   = rowIndex++;
  const tbody = document.getElementById('itemsBody');
  const opts  = PRODUCTS.map(p =>
    `<option value="\${p.id}" data-price="\${p.unit_price}">\${p.name} (\${p.sku})</option>`
  ).join('');

  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td>
      <input type="text" name="items[\${idx}][label]" class="form-control form-control-sm item-label"
             placeholder="Description du produit / service" required
             value="\${escHtml(item.label || '')}">
    </td>
    <td>
      <select class="form-select form-select-sm item-product">
        <option value="">— Libre —</option>
        \${opts}
      </select>
      <input type="hidden" name="items[\${idx}][product_id]" class="item-product-id" value="\${item.product_id || ''}">
    </td>
    <td>
      <input type="number" name="items[\${idx}][quantity]" class="form-control form-control-sm item-qty"
             min="0.01" step="0.01" value="\${item.quantity || 1}" required>
    </td>
    <td>
      <input type="number" name="items[\${idx}][unit_price]" class="form-control form-control-sm item-price"
             min="0" step="0.01" value="\${item.unit_price || '0.00'}" required>
    </td>
    <td><span class="item-total fw-semibold">0,00 MAD</span></td>
    <td>
      <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row">
        <i class="bi bi-x"></i>
      </button>
    </td>
  `;
  tbody.appendChild(tr);

  if (item.product_id) {
    tr.querySelector('.item-product').value = item.product_id;
  }

  tr.querySelector('.item-product').addEventListener('change', function () {
    const pid = this.value;
    tr.querySelector('.item-product-id').value = pid;
    if (pid) {
      const prod = PRODUCTS.find(p => p.id == pid);
      if (prod) {
        tr.querySelector('.item-label').value = prod.name;
        tr.querySelector('.item-price').value = prod.unit_price.toFixed(2);
      }
    }
    recalc();
  });

  tr.querySelector('.item-qty').addEventListener('input', recalc);
  tr.querySelector('.item-price').addEventListener('input', recalc);
  tr.querySelector('.btn-remove-row').addEventListener('click', () => { tr.remove(); recalc(); });
  recalc();
}

function recalc() {
  const taxRate = parseFloat(document.getElementById('taxRate').value) || 0;
  document.getElementById('displayRate').textContent = taxRate;
  let ht = 0;
  document.querySelectorAll('#itemsBody tr').forEach(tr => {
    const q = parseFloat(tr.querySelector('.item-qty')?.value)   || 0;
    const p = parseFloat(tr.querySelector('.item-price')?.value) || 0;
    const l = q * p;
    ht += l;
    const s = tr.querySelector('.item-total');
    if (s) s.textContent = formatMAD(l);
  });
  const tax = ht * taxRate / 100;
  document.getElementById('displayHT').textContent  = formatMAD(ht);
  document.getElementById('displayTax').textContent = formatMAD(tax);
  document.getElementById('displayTTC').textContent = formatMAD(ht + tax);
}

function formatMAD(n) {
  return n.toLocaleString('fr-MA', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' MAD';
}

function escHtml(str) {
  return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
JS;
?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
