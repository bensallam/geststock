<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bon de livraison <?= e($note['note_number']) ?></title>
  <style>
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
      font-size: 12px;
      color: #111;
      background: #fff;
      line-height: 1.5;
    }

    .page { max-width: 960px; margin: 0 auto; padding: 36px 48px; }

    .no-print { display: block; }
    .cf::after { content: ''; display: table; clear: both; }
    .r { text-align: right; }

    /* ── Action bar ── */
    .action-bar { display: flex; gap: 8px; justify-content: flex-end; margin-bottom: 24px; }
    .action-bar a, .action-bar button {
      display: inline-block; padding: 8px 18px; border-radius: 4px;
      font-size: 12px; font-family: inherit; cursor: pointer;
      text-decoration: none; border: 1px solid transparent;
    }
    .btn-print { background: #111; color: #fff; border-color: #111; }
    .btn-pdf   { background: #fff; color: #111; border-color: #111; }
    .btn-back  { background: #fff; color: #666; border-color: #ccc; }

    /* ── Header ── */
    .doc-header {
      display: flex; justify-content: space-between; align-items: flex-start;
      padding-bottom: 16px; border-bottom: 2px solid #111; margin-bottom: 24px; gap: 20px;
    }
    .company-block { flex: 1; }
    .company-block img { display: block; max-height: 56px; max-width: 160px; object-fit: contain; margin-bottom: 8px; }
    .company-name { font-size: 14px; font-weight: 700; }
    .company-sub  { font-size: 10px; color: #555; line-height: 1.7; margin-top: 3px; }

    .doc-title-block { text-align: right; flex-shrink: 0; }
    .doc-type { font-size: 22px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; }
    .doc-ref  { font-size: 13px; font-weight: 600; margin-top: 6px; }
    .doc-meta { font-size: 10px; color: #555; margin-top: 3px; line-height: 1.6; }

    /* ── Client block ── */
    .client-block {
      float: right; border: 1px solid #bbb; padding: 12px 16px;
      min-width: 220px; max-width: 280px; margin-bottom: 24px;
    }
    .client-label  { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #888; margin-bottom: 5px; }
    .client-name   { font-size: 13px; font-weight: 700; }
    .client-detail { font-size: 10px; color: #444; line-height: 1.65; margin-top: 4px; }

    /* ── Items table ── */
    table.items { width: 100%; border-collapse: collapse; margin-bottom: 4px; font-size: 11px; }
    table.items thead th {
      background: #f2f2f2; border-top: 1px solid #bbb; border-bottom: 1px solid #bbb;
      padding: 8px 10px; font-size: 9px; text-transform: uppercase;
      letter-spacing: 0.5px; font-weight: 700; text-align: left;
    }
    table.items thead th.r { text-align: right; }
    table.items tbody td { padding: 8px 10px; border-bottom: 1px solid #eee; }
    table.items tbody td.r { text-align: right; }

    /* ── Totals ── */
    .totals-wrap { overflow: hidden; margin: 12px 0; }
    .totals-table { float: right; width: 280px; border-collapse: collapse; font-size: 11px; }
    .totals-table td { padding: 4px 0; }
    .totals-table td:last-child { text-align: right; font-weight: 600; padding-left: 16px; }
    .totals-table tr.total-net td {
      font-size: 13px; font-weight: 700; border-top: 2px solid #111; padding-top: 8px;
    }

    /* ── Amount in words ── */
    .amount-words {
      clear: both; font-size: 10px; color: #333;
      background: #f9f9f9; border: 1px solid #ddd; padding: 8px 12px; margin: 12px 0;
    }

    /* ── Info row ── */
    .info-row {
      display: flex; gap: 32px; flex-wrap: wrap;
      border-top: 1px solid #ddd; padding-top: 12px; margin-top: 12px;
    }
    .info-item .ii-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #888; }
    .info-item .ii-value { font-size: 11px; font-weight: 600; margin-top: 2px; }

    /* ── Notes ── */
    .notes-block {
      border-left: 3px solid #ddd; padding: 6px 12px;
      font-size: 10px; color: #444; line-height: 1.65; margin-top: 12px;
    }

    /* ── Signature ── */
    .sig-row { display: flex; gap: 48px; margin-top: 36px; }
    .sig-col  { flex: 1; }
    .sig-lbl  { font-size: 9px; text-transform: uppercase; color: #888; margin-bottom: 4px; }
    .sig-place { height: 48px; }
    .sig-line { border-top: 1px solid #555; padding-top: 5px; font-size: 9px; color: #888; text-align: center; }

    /* ── Footer ── */
    .doc-footer {
      margin-top: 36px; padding-top: 10px; border-top: 1px solid #ddd;
      font-size: 9px; color: #888; text-align: center; line-height: 1.8;
    }

    /* ── Responsive ── */
    @media screen and (max-width: 640px) {
      .page { padding: 20px; }
      .doc-header { flex-direction: column; }
      .doc-title-block { text-align: left; }
      .client-block { float: none; max-width: 100%; min-width: 0; width: 100%; margin-bottom: 16px; }
      .totals-table { float: none; width: 100%; }
      .sig-row { flex-direction: column; gap: 24px; }
      .info-row { gap: 16px; }
    }

    /* ── Signature (override height for cachet image) ── */
    .sig-place { height: 72px; display: flex; align-items: flex-end; justify-content: center; }
    .sig-place img { max-height: 72px; max-width: 100%; object-fit: contain; }

    @media print {
      .no-print { display: none !important; }
      body { font-size: 11px; }
      .page { max-width: 100%; padding: 0 14mm; }
      body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
  </style>
</head>
<body>
<div class="page">

  <?php
    $hasPrice   = $note['show_prices'] && !empty(array_filter(array_column($items, 'unit_price'), fn($v) => $v !== null));
    $grandTotal = $hasPrice ? (float) array_sum(array_column($items, 'total')) : 0;
  ?>

  <!-- Action bar -->
  <div class="action-bar no-print">
    <button class="btn-print" onclick="window.print()">Imprimer</button>
    <a class="btn-pdf" href="<?= APP_URL ?>/delivery-notes/pdf?id=<?= $note['id'] ?>">Télécharger PDF</a>
    <a class="btn-back" href="<?= APP_URL ?>/delivery-notes/show?id=<?= $note['id'] ?>">← Retour</a>
  </div>

  <!-- Header -->
  <div class="doc-header">
    <div class="company-block">
      <?php if (!empty($company['logo_data_uri'])): ?>
        <img src="<?= $company['logo_data_uri'] ?>" alt="Logo">
      <?php endif; ?>
      <div class="company-name"><?= e($company['company_name'] ?: APP_NAME) ?></div>
      <div class="company-sub">
        <?php if (!empty($company['address'])): ?>
          <?= e(trim(strtok($company['address'], "\n"))) ?>
          <?php strtok('', ''); ?>
          <br>
        <?php endif; ?>
        <?php if (!empty($company['phone'])): ?>Tél&nbsp;: <?= e($company['phone']) ?><?php endif; ?>
        <?php if (!empty($company['phone']) && !empty($company['email'])): ?> &nbsp;·&nbsp; <?php endif; ?>
        <?php if (!empty($company['email'])): ?><?= e($company['email']) ?><?php endif; ?>
        <?php if (!empty($company['tax_id'])): ?><br>ICE&nbsp;: <?= e($company['tax_id']) ?><?php endif; ?>
      </div>
    </div>
    <div class="doc-title-block">
      <div class="doc-type">Bon de livraison</div>
      <div class="doc-ref"><?= e($note['note_number']) ?></div>
      <div class="doc-meta">
        <?= formatDate($note['delivery_date']) ?>
        <?php if ($note['reference']): ?><br>Réf&nbsp;: <?= e($note['reference']) ?><?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Client block -->
  <div class="client-block">
    <div class="client-label">Adressé à :</div>
    <div class="client-name"><?= e($note['customer_name']) ?></div>
    <div class="client-detail">
      <?php if (!empty($note['client_address'])): ?>
        <?= e(trim(strtok($note['client_address'], "\n"))) ?>
        <?php strtok('', ''); ?>
        <br>
      <?php endif; ?>
      <?php if (!empty($note['client_phone'])): ?>Tél&nbsp;: <?= e($note['client_phone']) ?><?php endif; ?>
    </div>
  </div>
  <div class="cf"></div>

  <!-- Items table -->
  <table class="items">
    <thead>
      <tr>
        <?php if ($hasPrice): ?>
        <th style="width:55%">Désignation</th>
        <th class="r" style="width:10%">Qté</th>
        <th class="r" style="width:18%">P.U. HT</th>
        <th class="r" style="width:17%">Total HT</th>
        <?php else: ?>
        <th style="width:75%">Désignation</th>
        <th class="r" style="width:25%">Quantité</th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $item): ?>
      <tr>
        <td><?= e($item['label']) ?></td>
        <?php if ($hasPrice): ?>
        <td class="r"><?= rtrim(rtrim(number_format((float)$item['quantity'], 2, ',', ' '), '0'), ',') ?></td>
        <td class="r"><?= $item['unit_price'] !== null ? formatMoney((float)$item['unit_price']) : '—' ?></td>
        <td class="r"><?= $item['total'] !== null ? formatMoney((float)$item['total']) : '—' ?></td>
        <?php else: ?>
        <td class="r"><?= rtrim(rtrim(number_format((float)$item['quantity'], 2, ',', ' '), '0'), ',') ?></td>
        <?php endif; ?>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Totals (only when prices are shown) -->
  <?php if ($hasPrice && $grandTotal > 0): ?>
  <div class="totals-wrap">
    <table class="totals-table">
      <tr class="total-net">
        <td>Total Net à payer</td>
        <td><?= formatMoney($grandTotal) ?></td>
      </tr>
    </table>
  </div>
  <div class="amount-words">
    <strong>Arrêté à la somme de :</strong> <?= e(amountInWords($grandTotal)) ?>
  </div>
  <?php endif; ?>

  <!-- Info row -->
  <?php $hasInfo = !empty($note['delivery_date']) || !empty($note['payment_method']); ?>
  <?php if ($hasInfo): ?>
  <div class="info-row">
    <?php if (!empty($note['delivery_date'])): ?>
    <div class="info-item">
      <div class="ii-label">Date de livraison</div>
      <div class="ii-value"><?= formatDate($note['delivery_date']) ?></div>
    </div>
    <?php endif; ?>
    <?php if (!empty($note['payment_method'])): ?>
    <div class="info-item">
      <div class="ii-label">Mode de règlement</div>
      <div class="ii-value"><?= e(paymentMethodLabel($note['payment_method'])) ?></div>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- Notes -->
  <?php if ($note['notes']): ?>
  <div class="notes-block"><?= nl2br(e($note['notes'])) ?></div>
  <?php endif; ?>

  <!-- Signature -->
  <?php
    $docDateParts = $note['delivery_date'] ? explode('-', $note['delivery_date']) : null;
    $docDateFmt   = $docDateParts ? $docDateParts[2] . ' / ' . $docDateParts[1] . ' / ' . $docDateParts[0] : '___ / ___ / ______';
  ?>
  <div class="sig-row">
    <div class="sig-col">
      <div class="sig-lbl">Fait à _____________________, le <?= $docDateFmt ?></div>
      <div class="sig-place">
        <?php if (!empty($note['use_watermark']) && !empty($company['watermark_data_uri'])): ?>
          <img src="<?= $company['watermark_data_uri'] ?>"
               style="opacity:<?= e($company['watermark_opacity'] ?? 0.15) ?>;" alt="">
        <?php endif; ?>
      </div>
      <div class="sig-line">Cachet et signature de l'expéditeur</div>
    </div>
    <div class="sig-col">
      <div class="sig-lbl">Reçu en bon état</div>
      <div class="sig-place"></div>
      <div class="sig-line">Signature du destinataire</div>
    </div>
  </div>

  <!-- Footer -->
  <div class="doc-footer">
    <?php
      $fp = [];
      if (!empty($company['company_name'])) $fp[] = e($company['company_name']);
      if (!empty($company['address']))      { $fp[] = e(trim(strtok($company['address'], "\n"))); strtok('', ''); }
      if (!empty($company['phone']))        $fp[] = 'Tél&nbsp;: ' . e($company['phone']);
      if (!empty($company['email']))        $fp[] = e($company['email']);
      if (!empty($company['tax_id']))       $fp[] = 'ICE&nbsp;: ' . e($company['tax_id']);
      echo implode(' &nbsp;·&nbsp; ', $fp);
    ?>
    <?php if (!empty($company['invoice_footer'])): ?><br><?= e($company['invoice_footer']) ?><?php endif; ?>
  </div>

</div>
</body>
</html>
