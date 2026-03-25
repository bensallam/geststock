<?php
$pageTitle   = 'Bons de livraison — ' . APP_NAME;
$breadcrumbs = ['Bons de livraison' => ''];
require __DIR__ . '/../layout/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
  <h4 class="fw-bold mb-0"><i class="bi bi-truck me-2 text-primary"></i>Bons de livraison</h4>
  <a href="<?= APP_URL ?>/delivery-notes/create" class="btn btn-primary">
    <i class="bi bi-plus-lg me-1"></i> Nouveau bon
  </a>
</div>

<form method="GET" action="<?= APP_URL ?>/delivery-notes" class="card border-0 shadow-sm mb-4">
  <div class="card-body">
    <div class="row g-2 align-items-end">
      <div class="col-md-4">
        <label class="form-label small">Recherche</label>
        <input type="text" name="search" class="form-control"
               placeholder="N° bon, client, référence…"
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
        <input type="date" name="date_from" class="form-control" value="<?= e($filters['date_from']) ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label small">Au</label>
        <input type="date" name="date_to" class="form-control" value="<?= e($filters['date_to']) ?>">
      </div>
      <div class="col-md-2 d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search"></i></button>
        <a href="<?= APP_URL ?>/delivery-notes" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
      </div>
    </div>
  </div>
</form>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <?php if (empty($notes)): ?>
      <div class="p-5 text-center text-muted">
        <i class="bi bi-truck fs-1 d-block mb-2"></i>
        Aucun bon de livraison trouvé.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>N° Bon</th>
              <th>Client</th>
              <th>Date livraison</th>
              <th>Référence</th>
              <th class="text-center">Prix affichés</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($notes as $n): ?>
            <tr>
              <td>
                <a href="<?= APP_URL ?>/delivery-notes/show?id=<?= $n['id'] ?>"
                   class="fw-semibold text-decoration-none">
                  <?= e($n['note_number']) ?>
                </a>
              </td>
              <td><?= e($n['customer_name']) ?></td>
              <td><?= formatDate($n['delivery_date']) ?></td>
              <td><?= $n['reference'] ? e($n['reference']) : '<span class="text-muted">—</span>' ?></td>
              <td class="text-center">
                <?php if ($n['show_prices']): ?>
                  <span class="badge bg-success-subtle text-success border border-success-subtle">Oui</span>
                <?php else: ?>
                  <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Non</span>
                <?php endif; ?>
              </td>
              <td class="text-end">
                <div class="btn-group btn-group-sm">
                  <a href="<?= APP_URL ?>/delivery-notes/show?id=<?= $n['id'] ?>"
                     class="btn btn-outline-secondary" title="Voir"><i class="bi bi-eye"></i></a>
                  <a href="<?= APP_URL ?>/delivery-notes/print?id=<?= $n['id'] ?>"
                     class="btn btn-outline-secondary" title="Imprimer" target="_blank">
                     <i class="bi bi-printer"></i></a>
                  <a href="<?= APP_URL ?>/delivery-notes/edit?id=<?= $n['id'] ?>"
                     class="btn btn-outline-primary" title="Modifier"><i class="bi bi-pencil"></i></a>
                  <button type="button" class="btn btn-outline-danger btn-delete"
                          title="Supprimer"
                          data-id="<?= $n['id'] ?>"
                          data-name="le bon <?= e($n['note_number']) ?>"
                          data-action="<?= APP_URL ?>/delivery-notes/delete">
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
  <div class="card-footer bg-transparent text-muted small"><?= count($notes) ?> bon(s)</div>
</div>

<?php require __DIR__ . '/../layout/delete_modal.php'; ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
