<?php
$pageTitle   = 'Nouveau produit — ' . APP_NAME;
$breadcrumbs = ['Produits' => 'products', 'Nouveau produit' => ''];
require __DIR__ . '/../layout/header.php';
?>

<div class="d-flex align-items-center gap-2 mb-4">
  <a href="<?= APP_URL ?>/products" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left"></i>
  </a>
  <h4 class="fw-bold mb-0">Nouveau produit</h4>
</div>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($errors as $err): ?>
        <li><?= e($err) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
  <div class="card-body">
    <form method="POST" action="<?= APP_URL ?>/products/store" novalidate>
      <div class="row g-3">

        <div class="col-md-6">
          <label class="form-label fw-semibold">Nom du produit <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control" required
                 value="<?= e($old['name'] ?? '') ?>">
        </div>

        <div class="col-md-3">
          <label class="form-label fw-semibold">SKU / Référence <span class="text-danger">*</span></label>
          <input type="text" name="sku" class="form-control" required
                 value="<?= e($old['sku'] ?? '') ?>">
        </div>

        <div class="col-md-3">
          <label class="form-label fw-semibold">Catégorie</label>
          <div class="input-group">
            <select name="category_id" class="form-select">
              <option value="">— Aucune —</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"
                  <?= (int)($old['category_id'] ?? 0) === (int)$cat['id'] ? 'selected' : '' ?>>
                  <?= e($cat['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="col-md-3">
          <label class="form-label fw-semibold">Prix de vente HT (MAD) <span class="text-danger">*</span></label>
          <input type="number" name="unit_price" class="form-control" min="0" step="0.01" required
                 value="<?= e($old['unit_price'] ?? '0.00') ?>">
        </div>

        <div class="col-md-3">
          <label class="form-label fw-semibold">Prix d'achat (MAD)</label>
          <input type="number" name="cost_price" class="form-control" min="0" step="0.01"
                 value="<?= e($old['cost_price'] ?? '0.00') ?>">
        </div>

        <div class="col-md-3">
          <label class="form-label fw-semibold">Quantité initiale</label>
          <input type="number" name="quantity" class="form-control" min="0" step="1"
                 value="<?= e($old['quantity'] ?? '0') ?>">
        </div>

        <div class="col-md-3">
          <label class="form-label fw-semibold">Stock minimum</label>
          <input type="number" name="minimum_stock" class="form-control" min="0" step="1"
                 value="<?= e($old['minimum_stock'] ?? '5') ?>">
        </div>

        <div class="col-12">
          <label class="form-label fw-semibold">Description</label>
          <textarea name="description" class="form-control" rows="3"><?= e($old['description'] ?? '') ?></textarea>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
          <a href="<?= APP_URL ?>/products" class="btn btn-outline-secondary">Annuler</a>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i> Enregistrer
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
