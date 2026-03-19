<?php
$pageTitle   = 'Produits — ' . APP_NAME;
$breadcrumbs = ['Produits' => ''];
require __DIR__ . '/../layout/header.php';
?>

<!-- Toolbar -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
  <h4 class="fw-bold mb-0"><i class="bi bi-box me-2 text-primary"></i>Produits</h4>
  <a href="<?= APP_URL ?>/products/create" class="btn btn-primary">
    <i class="bi bi-plus-lg me-1"></i> Nouveau produit
  </a>
</div>

<!-- Filters -->
<form method="GET" action="<?= APP_URL ?>/products" class="card border-0 shadow-sm mb-4">
  <div class="card-body">
    <div class="row g-2 align-items-end">
      <div class="col-md-4">
        <label class="form-label small">Recherche</label>
        <input type="text" name="search" class="form-control" placeholder="Nom ou SKU…"
               value="<?= e($filters['search']) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label small">Catégorie</label>
        <select name="category_id" class="form-select">
          <option value="">Toutes</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>"
              <?= (int)($filters['category_id'] ?? 0) === (int)$cat['id'] ? 'selected' : '' ?>>
              <?= e($cat['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <div class="form-check mt-4">
          <input class="form-check-input" type="checkbox" name="low_stock" id="low_stock" value="1"
                 <?= !empty($filters['low_stock']) ? 'checked' : '' ?>>
          <label class="form-check-label" for="low_stock">Stock faible uniquement</label>
        </div>
      </div>
      <div class="col-md-2 d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-fill">
          <i class="bi bi-search"></i>
        </button>
        <a href="<?= APP_URL ?>/products" class="btn btn-outline-secondary">
          <i class="bi bi-x"></i>
        </a>
      </div>
    </div>
  </div>
</form>

<!-- Table -->
<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <?php if (empty($products)): ?>
      <div class="p-5 text-center text-muted">
        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
        Aucun produit trouvé.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Produit</th>
              <th>SKU</th>
              <th>Catégorie</th>
              <th class="text-end">Prix HT</th>
              <th class="text-center">Quantité</th>
              <th class="text-center">Statut</th>
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
                <td><?= e($p['category_name'] ?? '—') ?></td>
                <td class="text-end"><?= formatMoney((float)$p['unit_price']) ?></td>
                <td class="text-center fw-semibold"><?= $p['quantity'] ?></td>
                <td class="text-center">
                  <span class="badge bg-<?= $s['class'] ?>"><?= $s['label'] ?></span>
                </td>
                <td class="text-end">
                  <div class="btn-group btn-group-sm">
                    <a href="<?= APP_URL ?>/products/show?id=<?= $p['id'] ?>"
                       class="btn btn-outline-secondary" title="Détails">
                      <i class="bi bi-eye"></i>
                    </a>
                    <a href="<?= APP_URL ?>/products/edit?id=<?= $p['id'] ?>"
                       class="btn btn-outline-primary" title="Modifier">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <a href="<?= APP_URL ?>/stock/adjust?id=<?= $p['id'] ?>"
                       class="btn btn-outline-success" title="Ajuster stock">
                      <i class="bi bi-layers"></i>
                    </a>
                    <button type="button" class="btn btn-outline-danger btn-delete"
                            title="Supprimer"
                            data-id="<?= $p['id'] ?>"
                            data-name="<?= e($p['name']) ?>"
                            data-action="<?= APP_URL ?>/products/delete">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
  <div class="card-footer bg-transparent text-muted small">
    <?= count($products) ?> produit(s) affiché(s)
  </div>
</div>

<!-- Delete modal -->
<?php require __DIR__ . '/../layout/delete_modal.php'; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
