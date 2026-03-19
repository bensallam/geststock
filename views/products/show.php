<?php
$pageTitle   = e($product['name']) . ' — ' . APP_NAME;
$breadcrumbs = ['Produits' => 'products', e($product['name']) => ''];
require __DIR__ . '/../layout/header.php';

$status = stockStatus((int)$product['quantity'], (int)$product['minimum_stock']);
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
  <div class="d-flex align-items-center gap-2">
    <a href="<?= APP_URL ?>/products" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left"></i>
    </a>
    <h4 class="fw-bold mb-0"><?= e($product['name']) ?></h4>
    <span class="badge bg-<?= $status['class'] ?>"><?= $status['label'] ?></span>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= APP_URL ?>/stock/adjust?id=<?= $product['id'] ?>" class="btn btn-outline-success">
      <i class="bi bi-layers me-1"></i> Ajuster stock
    </a>
    <a href="<?= APP_URL ?>/products/edit?id=<?= $product['id'] ?>" class="btn btn-outline-primary">
      <i class="bi bi-pencil me-1"></i> Modifier
    </a>
  </div>
</div>

<div class="row g-3 mb-4">
  <!-- Info card -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white fw-semibold">Informations</div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-5 text-muted small">SKU</dt>
          <dd class="col-7"><code><?= e($product['sku']) ?></code></dd>

          <dt class="col-5 text-muted small">Catégorie</dt>
          <dd class="col-7"><?= e($product['category_name'] ?? '—') ?></dd>

          <dt class="col-5 text-muted small">Prix vente HT</dt>
          <dd class="col-7 fw-semibold"><?= formatMoney((float)$product['unit_price']) ?></dd>

          <dt class="col-5 text-muted small">Prix achat</dt>
          <dd class="col-7"><?= formatMoney((float)$product['cost_price']) ?></dd>

          <dt class="col-5 text-muted small">Marge</dt>
          <?php $margin = $product['unit_price'] - $product['cost_price']; ?>
          <dd class="col-7 <?= $margin >= 0 ? 'text-success' : 'text-danger' ?>">
            <?= formatMoney($margin) ?>
          </dd>

          <dt class="col-5 text-muted small">Créé le</dt>
          <dd class="col-7"><?= formatDate($product['created_at']) ?></dd>
        </dl>
      </div>
    </div>
  </div>

  <!-- Stock card -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white fw-semibold">Stock</div>
      <div class="card-body text-center py-4">
        <div class="display-4 fw-bold text-<?= $status['class'] ?>">
          <?= $product['quantity'] ?>
        </div>
        <div class="text-muted mt-1">unités en stock</div>
        <hr>
        <div class="row text-center">
          <div class="col">
            <div class="small text-muted">Stock minimum</div>
            <div class="fw-bold"><?= $product['minimum_stock'] ?></div>
          </div>
          <div class="col">
            <div class="small text-muted">Valeur stock</div>
            <div class="fw-bold"><?= formatMoney($product['quantity'] * $product['cost_price']) ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Description -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white fw-semibold">Description</div>
      <div class="card-body">
        <?php if (!empty($product['description'])): ?>
          <p class="mb-0"><?= nl2br(e($product['description'])) ?></p>
        <?php else: ?>
          <p class="text-muted mb-0">Aucune description.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Movement history -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
    <i class="bi bi-arrow-left-right text-primary"></i>
    Historique des mouvements
  </div>
  <div class="card-body p-0">
    <?php if (empty($movements)): ?>
      <div class="p-4 text-center text-muted">Aucun mouvement enregistré.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Date</th>
              <th class="text-center">Type</th>
              <th class="text-center">Quantité</th>
              <th>Note</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($movements as $m): ?>
              <tr>
                <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($m['created_at'])) ?></td>
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
                <td class="text-center fw-semibold <?= $m['type'] === 'IN' ? 'text-success' : 'text-danger' ?>">
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
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
