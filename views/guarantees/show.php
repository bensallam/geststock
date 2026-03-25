<?php
$pageTitle   = 'Certificat ' . $cert['certificate_number'] . ' — ' . APP_NAME;
$breadcrumbs = ['Garanties' => 'guarantees', $cert['certificate_number'] => ''];
require __DIR__ . '/../layout/header.php';

$now      = date('Y-m-d');
$expired  = $cert['end_date'] < $now;
$expiring = !$expired && $cert['end_date'] <= date('Y-m-d', strtotime('+30 days'));
$days     = (int) round((strtotime($cert['end_date']) - strtotime($cert['start_date'])) / 86400);
$months   = round($days / 30.44);
$hasTotal = array_sum(array_column($items, 'total')) > 0;
$grandTotal = array_sum(array_column($items, 'total'));
?>

<!-- Action bar -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
  <h4 class="fw-bold mb-0">
    <i class="bi bi-shield-check me-2 text-primary"></i>
    <?= e($cert['certificate_number']) ?>
    <?php if ($expired): ?>
      <span class="badge bg-danger fs-6 ms-2">Expirée</span>
    <?php elseif ($expiring): ?>
      <span class="badge bg-warning text-dark fs-6 ms-2">Expire bientôt</span>
    <?php else: ?>
      <span class="badge bg-success fs-6 ms-2">Active</span>
    <?php endif; ?>
  </h4>
  <div class="d-flex gap-2 flex-wrap">
    <a href="<?= APP_URL ?>/guarantees/print?id=<?= $cert['id'] ?>"
       class="btn btn-outline-secondary" target="_blank">
      <i class="bi bi-printer me-1"></i> Imprimer
    </a>
    <a href="<?= APP_URL ?>/guarantees/pdf?id=<?= $cert['id'] ?>"
       class="btn btn-outline-danger">
      <i class="bi bi-file-earmark-pdf me-1"></i> Télécharger PDF
    </a>
    <a href="<?= APP_URL ?>/guarantees/edit?id=<?= $cert['id'] ?>"
       class="btn btn-primary">
      <i class="bi bi-pencil me-1"></i> Modifier
    </a>
    <button type="button" class="btn btn-outline-danger btn-delete"
            data-id="<?= $cert['id'] ?>"
            data-name="le certificat <?= e($cert['certificate_number']) ?>"
            data-action="<?= APP_URL ?>/guarantees/delete">
      <i class="bi bi-trash me-1"></i> Supprimer
    </button>
  </div>
</div>

<div class="row g-4">

  <!-- Left: main content -->
  <div class="col-lg-8">

    <!-- Period summary -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body p-0">
        <div class="row text-center g-0">
          <div class="col-4 p-3 border-end">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Début de garantie</div>
            <div class="fw-bold fs-6"><?= formatDate($cert['start_date']) ?></div>
          </div>
          <div class="col-4 p-3 border-end">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Durée</div>
            <div class="fw-bold fs-6 text-primary">
              <?= $months >= 12
                    ? floor($months / 12) . ' an(s)' . ($months % 12 ? ' · ' . ($months % 12) . ' mois' : '')
                    : $months . ' mois' ?>
            </div>
          </div>
          <div class="col-4 p-3">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Fin de garantie</div>
            <div class="fw-bold fs-6 <?= $expired ? 'text-danger' : ($expiring ? 'text-warning' : '') ?>">
              <?= formatDate($cert['end_date']) ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Items table -->
    <?php if (!empty($items)): ?>
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white fw-semibold">
        <i class="bi bi-box-seam me-2 text-primary"></i>Articles couverts
      </div>
      <div class="card-body p-0">
        <table class="table table-sm align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Désignation</th>
              <th class="text-center">Qté</th>
              <?php if ($hasTotal): ?>
              <th class="text-end">Prix unit.</th>
              <th class="text-end">Total</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $i => $item): ?>
            <tr>
              <td class="text-muted"><?= $i + 1 ?></td>
              <td><?= e($item['label']) ?></td>
              <td class="text-center"><?= number_format((float)$item['quantity'], 2) ?></td>
              <?php if ($hasTotal): ?>
              <td class="text-end"><?= $item['unit_price'] !== null ? formatMoney((float)$item['unit_price']) : '—' ?></td>
              <td class="text-end"><?= $item['total']      !== null ? formatMoney((float)$item['total'])      : '—' ?></td>
              <?php endif; ?>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <?php if ($hasTotal): ?>
          <tfoot>
            <tr class="table-active">
              <td colspan="<?= $hasTotal ? 4 : 3 ?>" class="text-end fw-semibold">Total :</td>
              <td class="text-end fw-bold text-primary fs-6"><?= formatMoney($grandTotal) ?></td>
            </tr>
          </tfoot>
          <?php endif; ?>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <!-- General description (if any and no items or items exist) -->
    <?php if (!empty($cert['product_details'])): ?>
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white fw-semibold">
        <i class="bi bi-file-text me-2 text-primary"></i>Description
      </div>
      <div class="card-body" style="white-space:pre-line;"><?= e($cert['product_details']) ?></div>
    </div>
    <?php endif; ?>

    <!-- Terms -->
    <?php if ($cert['terms']): ?>
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white fw-semibold">
        <i class="bi bi-shield me-2 text-primary"></i>Conditions de garantie
      </div>
      <div class="card-body" style="white-space:pre-line; font-size:.9rem; color:#444;"><?= e($cert['terms']) ?></div>
    </div>
    <?php endif; ?>

    <?php if ($cert['notes']): ?>
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white fw-semibold">
        <i class="bi bi-sticky me-2 text-warning"></i>Notes internes
      </div>
      <div class="card-body text-muted" style="white-space:pre-line;"><?= e($cert['notes']) ?></div>
    </div>
    <?php endif; ?>

  </div>

  <!-- Right: sidebar -->
  <div class="col-lg-4">

    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white fw-semibold">
        <i class="bi bi-person me-2 text-primary"></i>Bénéficiaire
      </div>
      <div class="card-body">
        <div class="fw-bold"><?= e($cert['customer_name']) ?></div>
        <?php if ($cert['client_address']): ?>
          <div class="text-muted small mt-1"><?= nl2br(e($cert['client_address'])) ?></div>
        <?php endif; ?>
        <?php if ($cert['client_phone']): ?>
          <div class="text-muted small">Tél : <?= e($cert['client_phone']) ?></div>
        <?php endif; ?>
        <?php if ($cert['client_email']): ?>
          <div class="text-muted small"><?= e($cert['client_email']) ?></div>
        <?php endif; ?>
      </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white fw-semibold">
        <i class="bi bi-info-circle me-2"></i>Détails
      </div>
      <div class="card-body small">
        <div class="d-flex justify-content-between mb-2">
          <span class="text-muted">N° Certificat</span>
          <span class="fw-semibold"><?= e($cert['certificate_number']) ?></span>
        </div>
        <?php if ($cert['reference']): ?>
        <div class="d-flex justify-content-between mb-2">
          <span class="text-muted">Référence</span>
          <span><?= e($cert['reference']) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($cert['delivery_date']): ?>
        <div class="d-flex justify-content-between mb-2">
          <span class="text-muted">Date d'émission</span>
          <span><?= formatDate($cert['delivery_date']) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($cert['invoice_number']): ?>
        <div class="d-flex justify-content-between mb-2">
          <span class="text-muted">Facture liée</span>
          <a href="<?= APP_URL ?>/invoices/show?id=<?= $cert['invoice_id'] ?>" class="fw-semibold">
            <?= e($cert['invoice_number']) ?>
          </a>
        </div>
        <?php endif; ?>
        <div class="d-flex justify-content-between mb-2">
          <span class="text-muted">Créé le</span>
          <span><?= formatDate(substr($cert['created_at'], 0, 10)) ?></span>
        </div>
        <div class="d-flex justify-content-between">
          <span class="text-muted">Modifié le</span>
          <span><?= formatDate(substr($cert['updated_at'], 0, 10)) ?></span>
        </div>
      </div>
    </div>

  </div>
</div>

<?php require __DIR__ . '/../layout/delete_modal.php'; ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
