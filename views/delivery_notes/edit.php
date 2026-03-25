<?php
$pageTitle   = 'Modifier bon de livraison — ' . APP_NAME;
$breadcrumbs = ['Bons de livraison' => 'delivery-notes', 'Modifier' => ''];
require __DIR__ . '/../layout/header.php';

$noteId = (int) ($old['id'] ?? $_GET['id'] ?? 0);

$renderItems = !empty($_POST['items']) ? $_POST['items'] : ($existingItems ?? []);

$productsJson = json_encode(array_map(fn($p) => [
    'id'         => $p['id'],
    'name'       => $p['name'],
    'sku'        => $p['sku'],
    'unit_price' => (float) $p['unit_price'],
], $products));

$itemsJson = json_encode(array_map(fn($i) => [
    'label'      => $i['label'],
    'product_id' => $i['product_id'] ?? null,
    'quantity'   => $i['quantity'],
    'unit_price' => $i['unit_price'],
], $renderItems));
?>

<div class="d-flex align-items-center gap-2 mb-4">
  <a href="<?= APP_URL ?>/delivery-notes/show?id=<?= $noteId ?>" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left"></i>
  </a>
  <h4 class="fw-bold mb-0"><i class="bi bi-truck me-2 text-primary"></i>Modifier le bon de livraison</h4>
</div>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
  </div>
<?php endif; ?>

<form method="POST" action="<?= APP_URL ?>/delivery-notes/update" novalidate>
  <input type="hidden" name="id" value="<?= $noteId ?>">
  <div class="row g-3">

    <!-- Header -->
    <div class="col-12">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">Informations générales</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-2">
              <label class="form-label fw-semibold">N° Bon <span class="text-danger">*</span></label>
              <input type="text" name="note_number" class="form-control" required
                     value="<?= e($old['note_number'] ?? '') ?>">
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">Date de livraison <span class="text-danger">*</span></label>
              <input type="date" name="delivery_date" class="form-control" required
                     value="<?= e($old['delivery_date'] ?? '') ?>">
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">Référence</label>
              <input type="text" name="reference" class="form-control"
                     placeholder="N° commande, facture…"
                     value="<?= e($old['reference'] ?? '') ?>">
            </div>
            <div class="col-md-3">
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
            <div class="col-md-3 d-flex align-items-end">
              <div class="form-check form-switch pb-1">
                <input class="form-check-input" type="checkbox" role="switch"
                       name="show_prices" id="showPrices" value="1"
                       <?= !empty($old['show_prices']) ? 'checked' : '' ?>>
                <label class="form-check-label fw-semibold" for="showPrices">
                  Afficher les prix sur le document
                </label>
              </div>
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

    <!-- Client -->
    <div class="col-12">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">Client / Destinataire</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Sélectionner un client</label>
              <select name="client_id" id="clientSelect" class="form-select">
                <option value="">— Saisie manuelle —</option>
                <?php foreach ($clients as $c): ?>
                  <option value="<?= $c['id'] ?>" data-name="<?= e($c['name']) ?>"
                    <?= (int)($old['client_id'] ?? 0) === (int)$c['id'] ? 'selected' : '' ?>>
                    <?= e($c['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Nom du destinataire <span class="text-danger">*</span></label>
              <input type="text" name="customer_name" id="customerName" class="form-control"
                     placeholder="Nom complet ou raison sociale"
                     value="<?= e($old['customer_name'] ?? '') ?>" required>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Items -->
    <div class="col-12">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold d-flex align-items-center justify-content-between">
          <span>Articles livrés <span class="text-danger">*</span></span>
          <button type="button" class="btn btn-sm btn-outline-primary" id="addLine">
            <i class="bi bi-plus-lg me-1"></i> Ajouter un article
          </button>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th style="width:40%">Désignation</th>
                  <th style="width:20%">Produit catalogue</th>
                  <th style="width:10%">Quantité</th>
                  <th style="width:20%">Prix unit. (optionnel)</th>
                  <th style="width:5%"></th>
                </tr>
              </thead>
              <tbody id="itemsBody"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Notes -->
    <div class="col-12">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">
          <i class="bi bi-sticky me-2 text-warning"></i>Notes / Observations
        </div>
        <div class="card-body">
          <textarea name="notes" class="form-control" rows="3"
                    placeholder="Instructions de livraison, remarques…"><?= e($old['notes'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <div class="col-12 d-flex justify-content-end gap-2">
      <a href="<?= APP_URL ?>/delivery-notes/show?id=<?= $noteId ?>" class="btn btn-outline-secondary">Annuler</a>
      <button type="submit" class="btn btn-primary">
        <i class="bi bi-check-lg me-1"></i> Enregistrer les modifications
      </button>
    </div>

  </div>
</form>

<?php
$extraJs = <<<JS
<script>
const PRODUCTS   = {$productsJson};
const INIT_ITEMS = {$itemsJson};

document.addEventListener('DOMContentLoaded', function () {
  document.getElementById('clientSelect').addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    if (opt.value && opt.dataset.name) document.getElementById('customerName').value = opt.dataset.name;
  });

  INIT_ITEMS.forEach(function (item) { addRow(item); });
  if (INIT_ITEMS.length === 0) addRow();
  document.getElementById('addLine').addEventListener('click', function () { addRow(); });
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
    '<td><input type="text" name="items[' + idx + '][label]" class="form-control form-control-sm item-label"' +
    ' placeholder="Désignation" value="' + escHtml(item.label || '') + '"></td>' +
    '<td><select class="form-select form-select-sm item-product"><option value="">— Libre —</option>' + opts + '</select>' +
    '<input type="hidden" name="items[' + idx + '][product_id]" class="item-product-id"></td>' +
    '<td><input type="number" name="items[' + idx + '][quantity]" class="form-control form-control-sm item-qty"' +
    ' min="0.01" step="0.01" value="' + (item.quantity || 1) + '"></td>' +
    '<td><input type="number" name="items[' + idx + '][unit_price]" class="form-control form-control-sm item-price"' +
    ' min="0" step="0.01" placeholder="—"' +
    ' value="' + (item.unit_price != null && item.unit_price !== '' ? parseFloat(item.unit_price).toFixed(2) : '') + '"></td>' +
    '<td><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="bi bi-x"></i></button></td>';

  tbody.appendChild(tr);

  if (item.product_id) tr.querySelector('.item-product').value = item.product_id;

  tr.querySelector('.item-product').addEventListener('change', function () {
    const pid = this.value;
    tr.querySelector('.item-product-id').value = pid;
    if (pid) {
      const prod = PRODUCTS.find(function (p) { return p.id == pid; });
      if (prod) { tr.querySelector('.item-label').value = prod.name; tr.querySelector('.item-price').value = prod.unit_price.toFixed(2); }
    }
  });

  tr.querySelector('.btn-remove-row').addEventListener('click', function () { tr.remove(); });
}

function escHtml(str) {
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
JS;
?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
