<?php
$pageTitle   = 'Clients — ' . APP_NAME;
$breadcrumbs = ['Clients' => ''];
require __DIR__ . '/../layout/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
  <h4 class="fw-bold mb-0"><i class="bi bi-people me-2 text-primary"></i>Clients</h4>
  <a href="<?= APP_URL ?>/clients/create" class="btn btn-primary">
    <i class="bi bi-plus-lg me-1"></i> Nouveau client
  </a>
</div>

<!-- Search -->
<form method="GET" action="<?= APP_URL ?>/clients" class="card border-0 shadow-sm mb-4">
  <div class="card-body">
    <div class="row g-2 align-items-end">
      <div class="col-md-8">
        <label class="form-label small">Recherche</label>
        <input type="text" name="search" class="form-control" placeholder="Nom, email, ICE, téléphone…"
               value="<?= e($filters['search']) ?>">
      </div>
      <div class="col-md-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-fill">
          <i class="bi bi-search me-1"></i> Rechercher
        </button>
        <a href="<?= APP_URL ?>/clients" class="btn btn-outline-secondary">
          <i class="bi bi-x"></i>
        </a>
      </div>
    </div>
  </div>
</form>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <?php if (empty($clients)): ?>
      <div class="p-5 text-center text-muted">
        <i class="bi bi-people fs-1 d-block mb-2"></i>
        Aucun client trouvé.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Nom</th>
              <th>ICE</th>
              <th>Téléphone</th>
              <th>Email</th>
              <th>Adresse</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($clients as $c): ?>
              <tr>
                <td class="fw-semibold"><?= e($c['name']) ?></td>
                <td><code class="small"><?= e($c['ice'] ?? '—') ?></code></td>
                <td><?= e($c['phone'] ?? '—') ?></td>
                <td><?= e($c['email'] ?? '—') ?></td>
                <td class="text-muted small"><?= e($c['address'] ?? '—') ?></td>
                <td class="text-end">
                  <div class="btn-group btn-group-sm">
                    <a href="<?= APP_URL ?>/clients/edit?id=<?= $c['id'] ?>"
                       class="btn btn-outline-primary" title="Modifier">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <button type="button" class="btn btn-outline-danger btn-delete"
                            title="Supprimer"
                            data-id="<?= $c['id'] ?>"
                            data-name="<?= e($c['name']) ?>"
                            data-action="<?= APP_URL ?>/clients/delete">
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
    <?= count($clients) ?> client(s)
  </div>
</div>

<?php require __DIR__ . '/../layout/delete_modal.php'; ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
