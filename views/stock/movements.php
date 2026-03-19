<?php
$pageTitle   = 'Historique des mouvements — ' . APP_NAME;
$breadcrumbs = ['Stock' => 'stock', 'Mouvements' => ''];
require __DIR__ . '/../layout/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
  <h4 class="fw-bold mb-0">
    <i class="bi bi-arrow-left-right me-2 text-primary"></i>Historique des mouvements
  </h4>
  <a href="<?= APP_URL ?>/stock" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i> Retour stock
  </a>
</div>

<!-- Filters -->
<form method="GET" action="<?= APP_URL ?>/stock/movements" class="card border-0 shadow-sm mb-4">
  <div class="card-body">
    <div class="row g-2 align-items-end">
      <div class="col-md-5">
        <label class="form-label small">Produit</label>
        <select name="product_id" class="form-select">
          <option value="">Tous les produits</option>
          <?php foreach ($products as $p): ?>
            <option value="<?= $p['id'] ?>"
              <?= (int)($filters['product_id'] ?? 0) === (int)$p['id'] ? 'selected' : '' ?>>
              <?= e($p['name']) ?> (<?= e($p['sku']) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small">Type</label>
        <select name="type" class="form-select">
          <option value="">Tous</option>
          <option value="IN"  <?= ($filters['type'] ?? '') === 'IN'  ? 'selected' : '' ?>>Entrée (IN)</option>
          <option value="OUT" <?= ($filters['type'] ?? '') === 'OUT' ? 'selected' : '' ?>>Sortie (OUT)</option>
        </select>
      </div>
      <div class="col-md-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-fill">
          <i class="bi bi-search me-1"></i> Filtrer
        </button>
        <a href="<?= APP_URL ?>/stock/movements" class="btn btn-outline-secondary">
          <i class="bi bi-x"></i>
        </a>
      </div>
    </div>
  </div>
</form>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <?php if (empty($movements)): ?>
      <div class="p-5 text-center text-muted">
        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
        Aucun mouvement.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Date</th>
              <th>Produit</th>
              <th>SKU</th>
              <th class="text-center">Type</th>
              <th class="text-center">Quantité</th>
              <th>Note</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($movements as $m): ?>
              <tr>
                <td class="text-muted small">
                  <?= date('d/m/Y', strtotime($m['created_at'])) ?>
                  <br><span class="text-muted" style="font-size:11px;">
                    <?= date('H:i', strtotime($m['created_at'])) ?>
                  </span>
                </td>
                <td>
                  <a href="<?= APP_URL ?>/products/show?id=<?= $m['product_id'] ?>"
                     class="text-decoration-none fw-semibold">
                    <?= e($m['product_name']) ?>
                  </a>
                </td>
                <td><code class="small"><?= e($m['sku']) ?></code></td>
                <td class="text-center">
                  <?php if ($m['type'] === 'IN'): ?>
                    <span class="badge bg-success-subtle text-success border border-success">
                      <i class="bi bi-arrow-down-circle me-1"></i>Entrée
                    </span>
                  <?php else: ?>
                    <span class="badge bg-danger-subtle text-danger border border-danger">
                      <i class="bi bi-arrow-up-circle me-1"></i>Sortie
                    </span>
                  <?php endif; ?>
                </td>
                <td class="text-center fw-bold <?= $m['type'] === 'IN' ? 'text-success' : 'text-danger' ?>">
                  <?= $m['type'] === 'IN' ? '+' : '−' ?><?= $m['quantity'] ?>
                </td>
                <td class="text-muted small"><?= e($m['note'] ?? '—') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
  <div class="card-footer bg-transparent text-muted small">
    <?= count($movements) ?> mouvement(s)
  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
