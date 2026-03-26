<?php
$pageTitle   = e($devis['devis_number']) . ' — ' . APP_NAME;
$breadcrumbs = ['Devis' => 'devis', e($devis['devis_number']) => ''];
require __DIR__ . '/../layout/header.php';

$statusMap = [
    'draft'    => ['label' => 'Brouillon', 'class' => 'secondary'],
    'sent'     => ['label' => 'Envoyé',    'class' => 'info'],
    'accepted' => ['label' => 'Accepté',   'class' => 'success'],
    'rejected' => ['label' => 'Refusé',    'class' => 'danger'],
];
$st = $statusMap[$devis['status']] ?? ['label' => $devis['status'], 'class' => 'secondary'];
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
  <div class="d-flex align-items-center gap-2">
    <a href="<?= APP_URL ?>/devis" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left"></i>
    </a>
    <h4 class="fw-bold mb-0"><?= e($devis['devis_number']) ?></h4>
    <span class="badge bg-<?= $st['class'] ?> fs-6"><?= $st['label'] ?></span>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= APP_URL ?>/devis/print?id=<?= $devis['id'] ?>"
       class="btn btn-outline-secondary" target="_blank">
      <i class="bi bi-printer me-1"></i> Imprimer
    </a>
    <a href="<?= APP_URL ?>/devis/pdf?id=<?= $devis['id'] ?>"
       class="btn btn-outline-danger">
      <i class="bi bi-file-earmark-pdf me-1"></i> PDF
    </a>
    <a href="<?= APP_URL ?>/devis/edit?id=<?= $devis['id'] ?>" class="btn btn-outline-primary">
      <i class="bi bi-pencil me-1"></i> Modifier
    </a>
    <button type="button" class="btn btn-outline-danger btn-delete"
            data-id="<?= $devis['id'] ?>"
            data-name="le devis <?= e($devis['devis_number']) ?>"
            data-action="<?= APP_URL ?>/devis/delete">
      <i class="bi bi-trash me-1"></i> Supprimer
    </button>
  </div>
</div>

<div class="row g-3 mb-4">
  <!-- Client -->
  <div class="col-md-5">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white fw-semibold">Client</div>
      <div class="card-body">
        <p class="fw-bold mb-1"><?= e($devis['client_name']) ?></p>
        <?php if ($devis['client_address']): ?>
          <p class="text-muted small mb-1"><?= nl2br(e($devis['client_address'])) ?></p>
        <?php endif; ?>
        <?php if ($devis['client_ice']): ?>
          <p class="text-muted small mb-1">ICE : <?= e($devis['client_ice']) ?></p>
        <?php endif; ?>
        <?php if ($devis['client_phone']): ?>
          <p class="text-muted small mb-1">Tél : <?= e($devis['client_phone']) ?></p>
        <?php endif; ?>
        <?php if ($devis['client_email']): ?>
          <p class="text-muted small mb-0">Email : <?= e($devis['client_email']) ?></p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Details -->
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white fw-semibold">Détails</div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-5 text-muted small">N° Devis</dt>
          <dd class="col-7 fw-semibold"><?= e($devis['devis_number']) ?></dd>
          <dt class="col-5 text-muted small">Date</dt>
          <dd class="col-7"><?= formatDate($devis['date']) ?></dd>
          <?php if ($devis['validity_date']): ?>
          <dt class="col-5 text-muted small">Validité</dt>
          <dd class="col-7"><?= formatDate($devis['validity_date']) ?></dd>
          <?php endif; ?>
          <dt class="col-5 text-muted small">Taux TVA</dt>
          <dd class="col-7"><?= $devis['tax_rate'] ?>%</dd>
          <dt class="col-5 text-muted small">Créé le</dt>
          <dd class="col-7 small"><?= date('d/m/Y H:i', strtotime($devis['created_at'])) ?></dd>
        </dl>
      </div>
    </div>
  </div>

  <!-- Totals -->
  <div class="col-md-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white fw-semibold">Montants</div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-6 text-muted small">Montant HT</dt>
          <dd class="col-6 text-end"><?= formatMoney((float)$devis['total_ht']) ?></dd>
          <dt class="col-6 text-muted small">TVA (<?= $devis['tax_rate'] ?>%)</dt>
          <dd class="col-6 text-end"><?= formatMoney((float)$devis['tax_amount']) ?></dd>
          <dt class="col-6 fw-bold">Total TTC</dt>
          <dd class="col-6 text-end fw-bold fs-5 text-primary"><?= formatMoney((float)$devis['total_ttc']) ?></dd>
        </dl>
      </div>
    </div>
  </div>
</div>

<!-- Line items -->
<div class="card border-0 shadow-sm mb-3">
  <div class="card-header bg-white fw-semibold">Lignes du devis</div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Désignation</th>
            <th class="text-center">Quantité</th>
            <th class="text-end">Prix unit. HT</th>
            <th class="text-end">Total HT</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $i => $item): ?>
            <tr>
              <td class="text-muted small"><?= $i + 1 ?></td>
              <td>
                <?= e($item['label']) ?>
                <?php if ($item['sku']): ?>
                  <br><code class="small text-muted"><?= e($item['sku']) ?></code>
                <?php endif; ?>
              </td>
              <td class="text-center"><?= number_format((float)$item['quantity'], 2) ?></td>
              <td class="text-end"><?= formatMoney((float)$item['unit_price']) ?></td>
              <td class="text-end fw-semibold"><?= formatMoney((float)$item['total']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot class="table-light">
          <tr>
            <td colspan="4" class="text-end text-muted">Montant HT</td>
            <td class="text-end fw-semibold"><?= formatMoney((float)$devis['total_ht']) ?></td>
          </tr>
          <tr>
            <td colspan="4" class="text-end text-muted">TVA (<?= $devis['tax_rate'] ?>%)</td>
            <td class="text-end"><?= formatMoney((float)$devis['tax_amount']) ?></td>
          </tr>
          <tr>
            <td colspan="4" class="text-end fw-bold fs-5">Total TTC</td>
            <td class="text-end fw-bold fs-5 text-primary"><?= formatMoney((float)$devis['total_ttc']) ?></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

<?php if ($devis['notes']): ?>
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">Notes</div>
    <div class="card-body"><?= nl2br(e($devis['notes'])) ?></div>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/delete_modal.php'; ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
