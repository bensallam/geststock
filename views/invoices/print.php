<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Facture <?= e($invoice['invoice_number']) ?></title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 13px; color: #333; background:#fff; }
    .page { max-width: 800px; margin: 0 auto; padding: 40px; }

    /* Header */
    .inv-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; }
    .company-name { font-size: 28px; font-weight: 700; color: #0d6efd; }
    .company-sub  { font-size: 11px; color: #666; margin-top: 4px; }
    .inv-badge    { text-align: right; }
    .inv-badge h2 { font-size: 22px; color: #0d6efd; font-weight: 700; letter-spacing: 1px; }
    .inv-badge .inv-num { font-size: 15px; color: #555; margin-top: 4px; }

    /* Parties */
    .parties { display: flex; gap: 40px; margin-bottom: 32px; }
    .party    { flex: 1; }
    .party-label { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #888; margin-bottom: 6px; }
    .party-name  { font-size: 15px; font-weight: 700; margin-bottom: 4px; }
    .party p     { font-size: 12px; color: #555; line-height: 1.5; }

    /* Details row */
    .details-row { display: flex; gap: 20px; background: #f8f9fa; padding: 12px 16px; border-radius: 6px; margin-bottom: 28px; }
    .detail-item { flex: 1; }
    .detail-item .label { font-size: 10px; text-transform: uppercase; color: #888; }
    .detail-item .value { font-size: 13px; font-weight: 600; margin-top: 2px; }

    /* Table */
    table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
    thead tr { background: #0d6efd; color: #fff; }
    thead th { padding: 10px 12px; text-align: left; font-size: 12px; }
    thead th.right { text-align: right; }
    tbody tr { border-bottom: 1px solid #e9ecef; }
    tbody tr:nth-child(even) { background: #f8f9fa; }
    tbody td { padding: 10px 12px; font-size: 12px; }
    tbody td.right { text-align: right; }
    tfoot td { padding: 8px 12px; font-size: 12px; }
    tfoot tr.total-row td { font-size: 15px; font-weight: 700; color: #0d6efd; border-top: 2px solid #0d6efd; }

    /* Totals block */
    .totals-block { float: right; width: 280px; border: 1px solid #e9ecef; border-radius: 6px; overflow: hidden; }
    .totals-block table { margin: 0; }
    .totals-block tbody td { padding: 8px 14px; }
    .totals-block .total-ttc { background: #0d6efd; color: #fff; font-size: 15px; font-weight: 700; }
    .clearfix::after { content: ''; display: block; clear: both; }

    /* Notes */
    .notes { margin-top: 32px; padding: 14px; background: #fffbe6; border-left: 4px solid #ffc107; border-radius: 4px; }
    .notes-label { font-size: 10px; text-transform: uppercase; color: #888; margin-bottom: 4px; }

    /* Footer */
    .print-footer { margin-top: 48px; padding-top: 16px; border-top: 1px solid #e9ecef; font-size: 11px; color: #aaa; text-align: center; }

    /* Status stamp */
    .stamp { position: absolute; top: 80px; right: 60px; transform: rotate(-20deg); border: 3px solid; border-radius: 4px; padding: 4px 10px; font-size: 18px; font-weight: 700; opacity: 0.35; pointer-events: none; }
    .stamp-paid      { color: #198754; border-color: #198754; }
    .stamp-cancelled { color: #dc3545; border-color: #dc3545; }

    @media print {
      body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .no-print { display: none !important; }
    }
  </style>
</head>
<body>
<div class="page" style="position:relative;">

  <?php if ($invoice['status'] === 'paid'): ?>
    <div class="stamp stamp-paid">PAYÉE</div>
  <?php elseif ($invoice['status'] === 'cancelled'): ?>
    <div class="stamp stamp-cancelled">ANNULÉE</div>
  <?php endif; ?>

  <!-- Print actions (hidden on print) -->
  <div class="no-print" style="text-align:right;margin-bottom:20px;">
    <button onclick="window.print()" style="padding:8px 20px;background:#0d6efd;color:#fff;border:none;border-radius:5px;cursor:pointer;margin-right:8px;">
      🖨 Imprimer
    </button>
    <a href="<?= APP_URL ?>/invoices/show?id=<?= $invoice['id'] ?>"
       style="padding:8px 20px;background:#6c757d;color:#fff;text-decoration:none;border-radius:5px;">
      ← Retour
    </a>
  </div>

  <!-- Invoice header -->
  <div class="inv-header">
    <div>
      <div class="company-name"><?= APP_NAME ?></div>
      <div class="company-sub">Gestion de stock &amp; facturation</div>
    </div>
    <div class="inv-badge">
      <h2>FACTURE</h2>
      <div class="inv-num"><?= e($invoice['invoice_number']) ?></div>
    </div>
  </div>

  <!-- Parties -->
  <div class="parties">
    <div class="party">
      <div class="party-label">Émetteur</div>
      <div class="party-name"><?= APP_NAME ?></div>
      <p>Maroc<br>facturation@geststock.ma</p>
    </div>
    <div class="party">
      <div class="party-label">Facturé à</div>
      <div class="party-name"><?= e($invoice['client_name']) ?></div>
      <p>
        <?php if ($invoice['client_address']): ?>
          <?= nl2br(e($invoice['client_address'])) ?><br>
        <?php endif; ?>
        <?php if ($invoice['client_ice']): ?>ICE : <?= e($invoice['client_ice']) ?><br><?php endif; ?>
        <?php if ($invoice['client_phone']): ?>Tél : <?= e($invoice['client_phone']) ?><br><?php endif; ?>
        <?php if ($invoice['client_email']): ?><?= e($invoice['client_email']) ?><?php endif; ?>
      </p>
    </div>
  </div>

  <!-- Details row -->
  <div class="details-row">
    <div class="detail-item">
      <div class="label">Date</div>
      <div class="value"><?= formatDate($invoice['date']) ?></div>
    </div>
    <div class="detail-item">
      <div class="label">N° Facture</div>
      <div class="value"><?= e($invoice['invoice_number']) ?></div>
    </div>
    <div class="detail-item">
      <div class="label">TVA</div>
      <div class="value"><?= $invoice['tax_rate'] ?>%</div>
    </div>
    <div class="detail-item">
      <div class="label">Statut</div>
      <div class="value"><?= match($invoice['status']) {
        'draft'     => 'Brouillon',
        'sent'      => 'Envoyée',
        'paid'      => 'Payée',
        'cancelled' => 'Annulée',
        default     => $invoice['status']
      } ?></div>
    </div>
  </div>

  <!-- Line items -->
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Désignation</th>
        <th class="right">Quantité</th>
        <th class="right">Prix unit. HT</th>
        <th class="right">Total HT</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $i => $item): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td><?= e($item['label']) ?></td>
          <td class="right"><?= number_format((float)$item['quantity'], 2) ?></td>
          <td class="right"><?= formatMoney((float)$item['unit_price']) ?></td>
          <td class="right"><?= formatMoney((float)$item['total']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Totals -->
  <div class="clearfix">
    <div class="totals-block">
      <table>
        <tbody>
          <tr>
            <td>Montant HT</td>
            <td style="text-align:right;"><?= formatMoney((float)$invoice['total_ht']) ?></td>
          </tr>
          <tr>
            <td>TVA (<?= $invoice['tax_rate'] ?>%)</td>
            <td style="text-align:right;"><?= formatMoney((float)$invoice['tax_amount']) ?></td>
          </tr>
          <tr class="total-ttc">
            <td>Total TTC</td>
            <td style="text-align:right;"><?= formatMoney((float)$invoice['total_ttc']) ?></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <?php if ($invoice['notes']): ?>
    <div class="notes">
      <div class="notes-label">Notes</div>
      <div><?= nl2br(e($invoice['notes'])) ?></div>
    </div>
  <?php endif; ?>

  <div class="print-footer">
    Document généré par <?= APP_NAME ?> — <?= date('d/m/Y à H:i') ?>
  </div>
</div>
</body>
</html>
