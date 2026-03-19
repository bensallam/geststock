<?php
$pageTitle   = 'Tableau de bord — ' . APP_NAME;
$breadcrumbs = ['Tableau de bord' => ''];
require __DIR__ . '/../layout/header.php';
?>

<!-- Stats row -->
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-primary-subtle text-primary rounded-3 p-3">
          <i class="bi bi-box fs-3"></i>
        </div>
        <div>
          <div class="text-muted small">Produits</div>
          <div class="fs-3 fw-bold"><?= $totalProducts ?></div>
        </div>
      </div>
      <div class="card-footer bg-transparent border-0">
        <a href="<?= APP_URL ?>/products" class="text-decoration-none small">Voir les produits →</a>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-success-subtle text-success rounded-3 p-3">
          <i class="bi bi-people fs-3"></i>
        </div>
        <div>
          <div class="text-muted small">Clients</div>
          <div class="fs-3 fw-bold"><?= $totalClients ?></div>
        </div>
      </div>
      <div class="card-footer bg-transparent border-0">
        <a href="<?= APP_URL ?>/clients" class="text-decoration-none small">Voir les clients →</a>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-warning-subtle text-warning rounded-3 p-3">
          <i class="bi bi-receipt fs-3"></i>
        </div>
        <div>
          <div class="text-muted small">Factures</div>
          <div class="fs-3 fw-bold"><?= $totalInvoices ?></div>
        </div>
      </div>
      <div class="card-footer bg-transparent border-0">
        <a href="<?= APP_URL ?>/invoices" class="text-decoration-none small">Voir les factures →</a>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-info-subtle text-info rounded-3 p-3">
          <i class="bi bi-cash-stack fs-3"></i>
        </div>
        <div>
          <div class="text-muted small">Chiffre d'affaires</div>
          <div class="fs-4 fw-bold"><?= formatMoney($totalRevenue) ?></div>
        </div>
      </div>
      <div class="card-footer bg-transparent border-0">
        <span class="text-muted small">Factures envoyées &amp; payées</span>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <!-- Low stock alerts -->
  <div class="col-lg-5">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle text-warning"></i>
        Alertes stock faible
        <?php if (count($lowStock) > 0): ?>
          <span class="badge bg-warning text-dark ms-auto"><?= count($lowStock) ?></span>
        <?php endif; ?>
      </div>
      <div class="card-body p-0">
        <?php if (empty($lowStock)): ?>
          <div class="p-4 text-center text-muted">
            <i class="bi bi-check-circle text-success fs-2 d-block mb-2"></i>
            Tous les stocks sont suffisants.
          </div>
        <?php else: ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($lowStock as $p): ?>
              <?php $s = stockStatus((int)$p['quantity'], (int)$p['minimum_stock']); ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <a href="<?= APP_URL ?>/products/show?id=<?= $p['id'] ?>" class="text-decoration-none fw-semibold">
                    <?= e($p['name']) ?>
                  </a>
                  <div class="text-muted small"><?= e($p['sku']) ?></div>
                </div>
                <div class="text-end">
                  <span class="badge bg-<?= $s['class'] ?>"><?= $s['label'] ?></span>
                  <div class="text-muted small mt-1">Qté : <?= $p['quantity'] ?> / Min : <?= $p['minimum_stock'] ?></div>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
      <div class="card-footer bg-transparent border-0">
        <a href="<?= APP_URL ?>/stock?low_stock=1" class="text-decoration-none small">
          Gérer le stock →
        </a>
      </div>
    </div>
  </div>

  <!-- Recent invoices -->
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
        <i class="bi bi-clock-history text-primary"></i>
        Dernières factures
      </div>
      <div class="card-body p-0">
        <?php if (empty($recentInvoices)): ?>
          <div class="p-4 text-center text-muted">Aucune facture.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>N°</th>
                  <th>Client</th>
                  <th>Date</th>
                  <th>Total TTC</th>
                  <th>Statut</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentInvoices as $inv): ?>
                  <?php
                  $statusMap = [
                      'draft'     => ['label' => 'Brouillon',  'class' => 'secondary'],
                      'sent'      => ['label' => 'Envoyée',    'class' => 'info'],
                      'paid'      => ['label' => 'Payée',      'class' => 'success'],
                      'cancelled' => ['label' => 'Annulée',    'class' => 'danger'],
                  ];
                  $st = $statusMap[$inv['status']] ?? ['label' => $inv['status'], 'class' => 'secondary'];
                  ?>
                  <tr>
                    <td>
                      <a href="<?= APP_URL ?>/invoices/show?id=<?= $inv['id'] ?>"
                         class="text-decoration-none fw-semibold">
                        <?= e($inv['invoice_number']) ?>
                      </a>
                    </td>
                    <td><?= e($inv['client_name']) ?></td>
                    <td><?= formatDate($inv['date']) ?></td>
                    <td class="fw-semibold"><?= formatMoney((float)$inv['total_ttc']) ?></td>
                    <td><span class="badge bg-<?= $st['class'] ?>"><?= $st['label'] ?></span></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
      <div class="card-footer bg-transparent border-0">
        <a href="<?= APP_URL ?>/invoices" class="text-decoration-none small">Toutes les factures →</a>
        <a href="<?= APP_URL ?>/invoices/create" class="btn btn-sm btn-primary float-end">
          <i class="bi bi-plus"></i> Nouvelle facture
        </a>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
