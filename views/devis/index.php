<?php
$pageTitle   = 'Devis — ' . APP_NAME;
$breadcrumbs = ['Devis' => ''];
require __DIR__ . '/../layout/header.php';

$statusMap = [
    'draft'    => ['label' => 'Brouillon', 'class' => 'secondary'],
    'sent'     => ['label' => 'Envoyé',    'class' => 'info'],
    'accepted' => ['label' => 'Accepté',   'class' => 'success'],
    'rejected' => ['label' => 'Refusé',    'class' => 'danger'],
];
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
  <h4 class="fw-bold mb-0"><i class="bi bi-file-text me-2 text-primary"></i>Devis</h4>
  <a href="<?= APP_URL ?>/devis/create" class="btn btn-primary">
    <i class="bi bi-plus-lg me-1"></i> Nouveau devis
  </a>
</div>

<!-- Filters -->
<form method="GET" action="<?= APP_URL ?>/devis" class="card border-0 shadow-sm mb-4">
  <div class="card-body">
    <div class="row g-2 align-items-end">
      <div class="col-md-3">
        <label class="form-label small">Recherche</label>
        <input type="text" name="search" class="form-control" placeholder="N° devis, client…"
               value="<?= e($filters['search']) ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label small">Client</label>
        <select name="client_id" class="form-select">
          <option value="">Tous</option>
          <?php foreach ($clients as $c): ?>
            <option value="<?= $c['id'] ?>"
              <?= (int)($filters['client_id'] ?? 0) === (int)$c['id'] ? 'selected' : '' ?>>
              <?= e($c['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small">Du</label>
        <input type="date" name="date_from" class="form-control"
               value="<?= e($filters['date_from']) ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label small">Au</label>
        <input type="date" name="date_to" class="form-control"
               value="<?= e($filters['date_to']) ?>">
      </div>
      <div class="col-md-1">
        <label class="form-label small">Statut</label>
        <select name="status" class="form-select">
          <option value="">Tous</option>
          <?php foreach ($statusMap as $key => $s): ?>
            <option value="<?= $key ?>"
              <?= ($filters['status'] ?? '') === $key ? 'selected' : '' ?>>
              <?= $s['label'] ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2 d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-fill">
          <i class="bi bi-search"></i>
        </button>
        <a href="<?= APP_URL ?>/devis" class="btn btn-outline-secondary">
          <i class="bi bi-x"></i>
        </a>
      </div>
    </div>
  </div>
</form>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <?php if (empty($devisList)): ?>
      <div class="p-5 text-center text-muted">
        <i class="bi bi-file-text fs-1 d-block mb-2"></i>
        Aucun devis trouvé.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>N° Devis</th>
              <th>Client</th>
              <th>Date</th>
              <th>Validité</th>
              <th class="text-end">Montant HT</th>
              <th class="text-end">Total TTC</th>
              <th class="text-center">Statut</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($devisList as $dv): ?>
              <?php $st = $statusMap[$dv['status']] ?? ['label' => $dv['status'], 'class' => 'secondary']; ?>
              <tr>
                <td>
                  <a href="<?= APP_URL ?>/devis/show?id=<?= $dv['id'] ?>"
                     class="fw-semibold text-decoration-none">
                    <?= e($dv['devis_number']) ?>
                  </a>
                </td>
                <td><?= e($dv['client_name']) ?></td>
                <td><?= formatDate($dv['date']) ?></td>
                <td><?= $dv['validity_date'] ? formatDate($dv['validity_date']) : '<span class="text-muted">—</span>' ?></td>
                <td class="text-end"><?= formatMoney((float)$dv['total_ht']) ?></td>
                <td class="text-end fw-semibold"><?= formatMoney((float)$dv['total_ttc']) ?></td>
                <td class="text-center">
                  <span class="badge bg-<?= $st['class'] ?>"><?= $st['label'] ?></span>
                </td>
                <td class="text-end">
                  <div class="btn-group btn-group-sm">
                    <a href="<?= APP_URL ?>/devis/show?id=<?= $dv['id'] ?>"
                       class="btn btn-outline-secondary" title="Voir">
                      <i class="bi bi-eye"></i>
                    </a>
                    <a href="<?= APP_URL ?>/devis/print?id=<?= $dv['id'] ?>"
                       class="btn btn-outline-secondary" title="Imprimer" target="_blank">
                      <i class="bi bi-printer"></i>
                    </a>
                    <a href="<?= APP_URL ?>/devis/edit?id=<?= $dv['id'] ?>"
                       class="btn btn-outline-primary" title="Modifier">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <button type="button" class="btn btn-outline-danger btn-delete"
                            title="Supprimer"
                            data-id="<?= $dv['id'] ?>"
                            data-name="le devis <?= e($dv['devis_number']) ?>"
                            data-action="<?= APP_URL ?>/devis/delete">
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
    <?= count($devisList) ?> devis
  </div>
</div>

<?php require __DIR__ . '/../layout/delete_modal.php'; ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
