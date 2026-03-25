<?php
$pageTitle   = 'Certificats de garantie — ' . APP_NAME;
$breadcrumbs = ['Garanties' => ''];
require __DIR__ . '/../layout/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
  <h4 class="fw-bold mb-0"><i class="bi bi-shield-check me-2 text-primary"></i>Certificats de garantie</h4>
  <a href="<?= APP_URL ?>/guarantees/create" class="btn btn-primary">
    <i class="bi bi-plus-lg me-1"></i> Nouveau certificat
  </a>
</div>

<!-- Filters -->
<form method="GET" action="<?= APP_URL ?>/guarantees" class="card border-0 shadow-sm mb-4">
  <div class="card-body">
    <div class="row g-2 align-items-end">
      <div class="col-md-4">
        <label class="form-label small">Recherche</label>
        <input type="text" name="search" class="form-control"
               placeholder="N° certificat, client, produit…"
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
        <label class="form-label small">Début à partir du</label>
        <input type="date" name="date_from" class="form-control"
               value="<?= e($filters['date_from']) ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label small">Jusqu'au</label>
        <input type="date" name="date_to" class="form-control"
               value="<?= e($filters['date_to']) ?>">
      </div>
      <div class="col-md-2 d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-fill">
          <i class="bi bi-search"></i>
        </button>
        <a href="<?= APP_URL ?>/guarantees" class="btn btn-outline-secondary">
          <i class="bi bi-x"></i>
        </a>
      </div>
    </div>
  </div>
</form>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <?php if (empty($certs)): ?>
      <div class="p-5 text-center text-muted">
        <i class="bi bi-shield fs-1 d-block mb-2"></i>
        Aucun certificat de garantie trouvé.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>N° Certificat</th>
              <th>Client</th>
              <th>Produit / Service</th>
              <th>Début</th>
              <th>Fin</th>
              <th class="text-center">Statut</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($certs as $cert): ?>
              <?php
                $now      = date('Y-m-d');
                $expired  = $cert['end_date'] < $now;
                $expiring = !$expired && $cert['end_date'] <= date('Y-m-d', strtotime('+30 days'));
              ?>
              <tr>
                <td>
                  <a href="<?= APP_URL ?>/guarantees/show?id=<?= $cert['id'] ?>"
                     class="fw-semibold text-decoration-none">
                    <?= e($cert['certificate_number']) ?>
                  </a>
                </td>
                <td><?= e($cert['customer_name']) ?></td>
                <td class="text-truncate" style="max-width:220px;">
                  <?= e($cert['product_details']) ?>
                </td>
                <td><?= formatDate($cert['start_date']) ?></td>
                <td><?= formatDate($cert['end_date']) ?></td>
                <td class="text-center">
                  <?php if ($expired): ?>
                    <span class="badge bg-danger">Expirée</span>
                  <?php elseif ($expiring): ?>
                    <span class="badge bg-warning text-dark">Expire bientôt</span>
                  <?php else: ?>
                    <span class="badge bg-success">Active</span>
                  <?php endif; ?>
                </td>
                <td class="text-end">
                  <div class="btn-group btn-group-sm">
                    <a href="<?= APP_URL ?>/guarantees/show?id=<?= $cert['id'] ?>"
                       class="btn btn-outline-secondary" title="Voir">
                      <i class="bi bi-eye"></i>
                    </a>
                    <a href="<?= APP_URL ?>/guarantees/print?id=<?= $cert['id'] ?>"
                       class="btn btn-outline-secondary" title="Imprimer" target="_blank">
                      <i class="bi bi-printer"></i>
                    </a>
                    <a href="<?= APP_URL ?>/guarantees/edit?id=<?= $cert['id'] ?>"
                       class="btn btn-outline-primary" title="Modifier">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <button type="button" class="btn btn-outline-danger btn-delete"
                            title="Supprimer"
                            data-id="<?= $cert['id'] ?>"
                            data-name="le certificat <?= e($cert['certificate_number']) ?>"
                            data-action="<?= APP_URL ?>/guarantees/delete">
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
    <?= count($certs) ?> certificat(s)
  </div>
</div>

<?php require __DIR__ . '/../layout/delete_modal.php'; ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
