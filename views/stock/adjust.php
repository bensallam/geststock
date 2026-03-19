<?php
$pageTitle   = 'Ajustement stock — ' . APP_NAME;
$breadcrumbs = ['Stock' => 'stock', 'Ajustement' => ''];
require __DIR__ . '/../layout/header.php';
$s = stockStatus((int)$product['quantity'], (int)$product['minimum_stock']);
?>

<div class="d-flex align-items-center gap-2 mb-4">
  <a href="<?= APP_URL ?>/stock" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left"></i>
  </a>
  <h4 class="fw-bold mb-0">Ajustement de stock</h4>
</div>

<div class="row g-3">
  <!-- Product info -->
  <div class="col-md-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white fw-semibold">Produit</div>
      <div class="card-body text-center">
        <p class="fw-bold fs-5 mb-1"><?= e($product['name']) ?></p>
        <p class="text-muted small mb-3"><code><?= e($product['sku']) ?></code></p>
        <div class="display-4 fw-bold text-<?= $s['class'] ?>"><?= $product['quantity'] ?></div>
        <div class="text-muted mt-1">unités actuellement en stock</div>
        <hr>
        <span class="badge bg-<?= $s['class'] ?> fs-6"><?= $s['label'] ?></span>
        <div class="text-muted small mt-2">Stock min : <?= $product['minimum_stock'] ?></div>
      </div>
    </div>
  </div>

  <!-- Adjustment form -->
  <div class="col-md-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white fw-semibold">Enregistrer un mouvement</div>
      <div class="card-body">

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="POST" action="<?= APP_URL ?>/stock/store" novalidate>
          <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

          <div class="mb-3">
            <label class="form-label fw-semibold">Type de mouvement</label>
            <div class="d-flex gap-3">
              <div class="form-check form-check-inline flex-fill">
                <input class="form-check-input" type="radio" name="type" id="typeIN" value="IN"
                       <?= ($_POST['type'] ?? 'IN') === 'IN' ? 'checked' : '' ?>>
                <label class="form-check-label" for="typeIN">
                  <span class="badge bg-success-subtle text-success border border-success fs-6 px-3 py-2">
                    <i class="bi bi-arrow-down-circle me-1"></i> Entrée (IN)
                  </span>
                </label>
              </div>
              <div class="form-check form-check-inline flex-fill">
                <input class="form-check-input" type="radio" name="type" id="typeOUT" value="OUT"
                       <?= ($_POST['type'] ?? '') === 'OUT' ? 'checked' : '' ?>>
                <label class="form-check-label" for="typeOUT">
                  <span class="badge bg-danger-subtle text-danger border border-danger fs-6 px-3 py-2">
                    <i class="bi bi-arrow-up-circle me-1"></i> Sortie (OUT)
                  </span>
                </label>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold" for="quantity">Quantité <span class="text-danger">*</span></label>
            <input type="number" id="quantity" name="quantity" class="form-control"
                   min="1" step="1" required
                   value="<?= e($_POST['quantity'] ?? '1') ?>">
          </div>

          <div class="mb-4">
            <label class="form-label fw-semibold" for="note">Note / Motif</label>
            <input type="text" id="note" name="note" class="form-control"
                   placeholder="Ex: livraison fournisseur, retour client…"
                   value="<?= e($_POST['note'] ?? '') ?>">
          </div>

          <div class="d-flex gap-2 justify-content-end">
            <a href="<?= APP_URL ?>/products/show?id=<?= $product['id'] ?>" class="btn btn-outline-secondary">
              Annuler
            </a>
            <button type="submit" class="btn btn-success">
              <i class="bi bi-check-lg me-1"></i> Enregistrer le mouvement
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
