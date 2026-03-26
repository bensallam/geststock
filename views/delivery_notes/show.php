<?php
$pageTitle   = $note['note_number'] . ' — ' . APP_NAME;
$breadcrumbs = ['Bons de livraison' => 'delivery-notes', $note['note_number'] => ''];
require __DIR__ . '/../layout/header.php';

$hasPrice   = $note['show_prices'] && !empty(array_filter(array_column($items, 'unit_price'), fn($v) => $v !== null));
$grandTotal = $hasPrice ? array_sum(array_column($items, 'total')) : 0;
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
  <h4 class="fw-bold mb-0">
    <i class="bi bi-truck me-2 text-primary"></i><?= e($note['note_number']) ?>
  </h4>
  <div class="d-flex gap-2 flex-wrap">
    <a href="<?= APP_URL ?>/delivery-notes/live-edit?id=<?= $note['id'] ?>"
       class="btn btn-outline-info">
      <i class="bi bi-pencil-square me-1"></i> Éditeur live
    </a>
    <a href="<?= APP_URL ?>/delivery-notes/print?id=<?= $note['id'] ?>"
       class="btn btn-outline-secondary" target="_blank">
      <i class="bi bi-printer me-1"></i> Imprimer
    </a>
    <a href="<?= APP_URL ?>/delivery-notes/pdf?id=<?= $note['id'] ?>"
       class="btn btn-outline-danger">
      <i class="bi bi-file-earmark-pdf me-1"></i> Télécharger PDF
    </a>
    <a href="<?= APP_URL ?>/delivery-notes/edit?id=<?= $note['id'] ?>"
       class="btn btn-primary">
      <i class="bi bi-pencil me-1"></i> Modifier
    </a>
    <button type="button" class="btn btn-outline-danger btn-delete"
            data-id="<?= $note['id'] ?>"
            data-name="le bon <?= e($note['note_number']) ?>"
            data-action="<?= APP_URL ?>/delivery-notes/delete">
      <i class="bi bi-trash me-1"></i> Supprimer
    </button>
  </div>
</div>

<div class="row g-4">

  <!-- Left: items + notes -->
  <div class="col-lg-8">

    <!-- Items table -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white fw-semibold">
        <i class="bi bi-box-seam me-2 text-primary"></i>Articles livrés
      </div>
      <div class="card-body p-0">
        <table class="table table-sm align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Désignation</th>
              <th class="text-center">Qté</th>
              <?php if ($hasPrice): ?>
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
              <?php if ($hasPrice): ?>
              <td class="text-end"><?= $item['unit_price'] !== null ? formatMoney((float)$item['unit_price']) : '—' ?></td>
              <td class="text-end"><?= $item['total'] !== null ? formatMoney((float)$item['total']) : '—' ?></td>
              <?php endif; ?>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <?php if ($hasPrice && $grandTotal > 0): ?>
          <tfoot>
            <tr class="table-active">
              <td colspan="<?= $hasPrice ? 4 : 3 ?>" class="text-end fw-semibold">Total :</td>
              <td class="text-end fw-bold text-primary fs-6"><?= formatMoney($grandTotal) ?></td>
            </tr>
          </tfoot>
          <?php endif; ?>
        </table>
      </div>
    </div>

    <?php if ($note['notes']): ?>
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white fw-semibold">
        <i class="bi bi-sticky me-2 text-warning"></i>Notes / Observations
      </div>
      <div class="card-body text-muted" style="white-space:pre-line;"><?= e($note['notes']) ?></div>
    </div>
    <?php endif; ?>

  </div>

  <!-- Right: sidebar -->
  <div class="col-lg-4">

    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white fw-semibold">
        <i class="bi bi-person me-2 text-primary"></i>Destinataire
      </div>
      <div class="card-body">
        <div class="fw-bold"><?= e($note['customer_name']) ?></div>
        <?php if (!empty($note['client_address'])): ?>
          <div class="text-muted small mt-1"><?= nl2br(e($note['client_address'])) ?></div>
        <?php endif; ?>
        <?php if (!empty($note['client_phone'])): ?>
          <div class="text-muted small">Tél : <?= e($note['client_phone']) ?></div>
        <?php endif; ?>
        <?php if (!empty($note['client_email'])): ?>
          <div class="text-muted small"><?= e($note['client_email']) ?></div>
        <?php endif; ?>
      </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white fw-semibold">
        <i class="bi bi-info-circle me-2"></i>Détails
      </div>
      <div class="card-body small">
        <div class="d-flex justify-content-between mb-2">
          <span class="text-muted">N° Bon</span>
          <span class="fw-semibold"><?= e($note['note_number']) ?></span>
        </div>
        <div class="d-flex justify-content-between mb-2">
          <span class="text-muted">Date livraison</span>
          <span><?= formatDate($note['delivery_date']) ?></span>
        </div>
        <?php if ($note['reference']): ?>
        <div class="d-flex justify-content-between mb-2">
          <span class="text-muted">Référence</span>
          <span><?= e($note['reference']) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($note['payment_method']): ?>
        <div class="d-flex justify-content-between mb-2">
          <span class="text-muted">Règlement</span>
          <span><?= e(paymentMethodLabel($note['payment_method'])) ?></span>
        </div>
        <?php endif; ?>
        <div class="d-flex justify-content-between mb-2">
          <span class="text-muted">Prix affichés</span>
          <?php if ($note['show_prices']): ?>
            <span class="badge bg-success-subtle text-success border border-success-subtle">Oui</span>
          <?php else: ?>
            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Non</span>
          <?php endif; ?>
        </div>
        <div class="d-flex justify-content-between mb-2">
          <span class="text-muted">Créé le</span>
          <span><?= formatDate(substr($note['created_at'], 0, 10)) ?></span>
        </div>
        <div class="d-flex justify-content-between">
          <span class="text-muted">Modifié le</span>
          <span><?= formatDate(substr($note['updated_at'], 0, 10)) ?></span>
        </div>
      </div>
    </div>

  </div>
</div>

<?php require __DIR__ . '/../layout/delete_modal.php'; ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
