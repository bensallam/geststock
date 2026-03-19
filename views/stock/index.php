<?php
$pageTitle   = 'Gestion du stock — ' . APP_NAME;
$breadcrumbs = ['Stock' => ''];
require __DIR__ . '/../layout/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
  <h4 class="fw-bold mb-0"><i class="bi bi-layers me-2 text-primary"></i>Gestion du stock</h4>
  <a href="<?= APP_URL ?>/stock/movements" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left-right me-1"></i> Historique des mouvements
  </a>
</div>

<!-- Filters -->
<form method="GET" action="<?= APP_URL ?>/stock" class="card border-0 shadow-sm mb-4">
  <div class="card-body">
    <div class="row g-2 align-items-end">
      <div class="col-md-5">
        <label class="form-label small">Recherche</label>
        <input type="text" name="search" class="form-control" placeholder="Nom ou SKU…"
               value="<?= e($filters['search']) ?>">
      </div>
      <div class="col-md-4">
        <div class="form-check mt-4">
          <input class="form-check-input" type="checkbox" name="low_stock" id="low_stock" value="1"
                 <?= !empty($filters['low_stock']) ? 'checked' : '' ?>>
          <label class="form-check-label" for="low_stock">
            <i class="bi bi-exclamation-triangle text-warning me-1"></i>
            Stock faible / rupture uniquement
          </label>
        </div>
      </div>
      <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-fill">
          <i class="bi bi-search me-1"></i> Filtrer
        </button>
        <a href="<?= APP_URL ?>/stock" class="btn btn-outline-secondary">
          <i class="bi bi-x"></i>
        </a>
      </div>
    </div>
  </div>
</form>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <?php if (empty($products)): ?>
      <div class="p-5 text-center text-muted">
        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
        Aucun produit.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Produit</th>
              <th>SKU</th>
              <th class="text-center">En stock</th>
              <th class="text-center">Stock min.</th>
              <th class="text-center">Statut</th>
              <th class="text-end">Valeur stock</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $p): ?>
              <?php $s = stockStatus((int)$p['quantity'], (int)$p['minimum_stock']); ?>
              <tr>
                <td>
                  <a href="<?= APP_URL ?>/products/show?id=<?= $p['id'] ?>"
                     class="fw-semibold text-decoration-none">
                    <?= e($p['name']) ?>
                  </a>
                </td>
                <td><code class="small"><?= e($p['sku']) ?></code></td>
                <td class="text-center">
                  <span class="fw-bold fs-5 text-<?= $s['class'] ?>"><?= $p['quantity'] ?></span>
                </td>
                <td class="text-center text-muted"><?= $p['minimum_stock'] ?></td>
                <td class="text-center">
                  <span class="badge bg-<?= $s['class'] ?>"><?= $s['label'] ?></span>
                </td>
                <td class="text-end text-muted">
                  <?= formatMoney($p['quantity'] * $p['cost_price']) ?>
                </td>
                <td class="text-end">
                  <a href="<?= APP_URL ?>/stock/adjust?id=<?= $p['id'] ?>"
                     class="btn btn-sm btn-outline-success">
                    <i class="bi bi-layers me-1"></i> Ajuster
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
  <div class="card-footer bg-transparent text-muted small">
    <?= count($products) ?> produit(s)
  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
