<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Certificat de Garantie <?= e($cert['certificate_number']) ?></title>
  <style>
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
      font-size: 11px;
      color: #111;
      background: #fff;
      line-height: 1.4;
    }

    .page { max-width: 960px; margin: 0 auto; padding: 28px 40px; }

    .cf::after { content: ''; display: table; clear: both; }
    .r { text-align: right; }

    /* ── Header ── */
    .doc-header {
      display: flex; justify-content: space-between; align-items: flex-start;
      padding-bottom: 12px; border-bottom: 2px solid #111; margin-bottom: 16px; gap: 16px;
    }
    .company-block { flex: 1; }
    .company-block img { display: block; max-height: 48px; max-width: 140px; object-fit: contain; margin-bottom: 6px; }
    .company-name { font-size: 13px; font-weight: 700; }
    .company-sub  { font-size: 9px; color: #555; line-height: 1.6; margin-top: 2px; }

    .doc-title-block { text-align: right; flex-shrink: 0; }
    .doc-type { font-size: 18px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; }
    .doc-ref  { font-size: 12px; font-weight: 600; margin-top: 4px; }
    .doc-meta { font-size: 9px; color: #555; margin-top: 2px; line-height: 1.5; }

    /* ── Client block ── */
    .client-block {
      float: right; border: 1px solid #bbb; padding: 8px 14px;
      min-width: 200px; max-width: 260px; margin-bottom: 14px;
    }
    .client-label  { font-size: 8px; text-transform: uppercase; letter-spacing: 1px; color: #888; margin-bottom: 4px; }
    .client-name   { font-size: 12px; font-weight: 700; }
    .client-detail { font-size: 9px; color: #444; line-height: 1.5; margin-top: 3px; }

    /* ── Items table ── */
    table.items { width: 100%; border-collapse: collapse; margin-bottom: 4px; font-size: 10px; }
    table.items thead th {
      background: #f2f2f2; border-top: 1px solid #bbb; border-bottom: 1px solid #bbb;
      padding: 5px 8px; font-size: 8px; text-transform: uppercase;
      letter-spacing: 0.5px; font-weight: 700; text-align: left;
    }
    table.items thead th.r { text-align: right; }
    table.items tbody td { padding: 5px 8px; border-bottom: 1px solid #eee; }
    table.items tbody td.r { text-align: right; }

    /* ── Totals ── */
    .totals-wrap { overflow: hidden; margin: 8px 0; }
    .totals-table { float: right; width: 260px; border-collapse: collapse; font-size: 10px; }
    .totals-table td { padding: 3px 0; }
    .totals-table td:last-child { text-align: right; font-weight: 600; padding-left: 12px; }
    .totals-table tr.total-net td {
      font-size: 12px; font-weight: 700; border-top: 2px solid #111; padding-top: 6px;
    }

    /* ── Amount in words ── */
    .amount-words {
      clear: both; font-size: 9px; color: #333;
      background: #f9f9f9; border: 1px solid #ddd; padding: 6px 10px; margin: 8px 0;
    }

    /* ── Description fallback ── */
    .desc-box {
      background: #f9f9f9; border: 1px solid #ddd; border-radius: 3px;
      padding: 7px 12px; font-size: 10px; line-height: 1.5;
      white-space: pre-line; margin-bottom: 10px;
    }

    /* ── Warranty period ── */
    .warranty-period {
      display: flex; border: 1px solid #bbb; border-radius: 3px;
      overflow: hidden; margin: 10px 0; text-align: center;
    }
    .w-col { flex: 1; padding: 8px 12px; }
    .w-col + .w-col { border-left: 1px solid #bbb; }
    .w-lbl { font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; color: #888; margin-bottom: 4px; }
    .w-val { font-size: 11px; font-weight: 700; }
    .w-val.dur { color: #1a5276; }

    /* ── Terms ── */
    .section-label { font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; color: #555; margin-bottom: 4px; margin-top: 10px; }
    .terms-body {
      font-size: 9px; color: #444; line-height: 1.55; white-space: pre-line;
      border: 1px solid #ddd; padding: 7px 12px; margin-bottom: 10px;
    }

    /* ── Info row ── */
    .info-row {
      display: flex; gap: 24px; flex-wrap: wrap;
      border-top: 1px solid #ddd; padding-top: 8px; margin-top: 8px;
    }
    .info-item .ii-label { font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; color: #888; }
    .info-item .ii-value { font-size: 10px; font-weight: 600; margin-top: 2px; }

    /* ── Notes ── */
    .notes-block {
      border-left: 3px solid #ddd; padding: 4px 10px;
      font-size: 9px; color: #444; line-height: 1.5; margin-top: 8px;
    }

    /* ── Signature ── */
    .sig-row { display: flex; gap: 40px; margin-top: 16px; }
    .sig-col  { flex: 1; }
    .sig-lbl  { font-size: 8px; text-transform: uppercase; color: #888; margin-bottom: 3px; }
    .sig-place { height: 64px; display: flex; align-items: flex-end; justify-content: center; }
    .sig-place img { max-height: 64px; max-width: 100%; object-fit: contain; }
    .sig-line { border-top: 1px solid #555; padding-top: 4px; font-size: 8px; color: #888; text-align: center; }

    /* ── Footer ── */
    .doc-footer {
      margin-top: 14px; padding-top: 8px; border-top: 1px solid #ddd;
      font-size: 8px; color: #888; text-align: center; line-height: 1.7;
    }

    /* ── Responsive ── */
    @media screen and (max-width: 640px) {
      .page { padding: 16px; }
      .doc-header { flex-direction: column; }
      .doc-title-block { text-align: left; }
      .client-block { float: none; max-width: 100%; min-width: 0; width: 100%; margin-bottom: 12px; }
      .warranty-period { flex-direction: column; }
      .w-col + .w-col { border-left: none; border-top: 1px solid #bbb; }
      .sig-row { flex-direction: column; gap: 20px; }
      .totals-table { float: none; width: 100%; }
      .info-row { gap: 12px; }
    }

    @media print {
      body { font-size: 10px; }
      .page { max-width: 100%; padding: 0 12mm; }
      body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
  </style>
</head>
<body>
<div class="page">

  <?php
    $startTs    = strtotime($cert['start_date']);
    $endTs      = strtotime($cert['end_date']);
    $days       = (int) round(($endTs - $startTs) / 86400);
    $months     = round($days / 30.44);
    $durLabel   = $months >= 12
        ? floor($months / 12) . ' an(s)' . ($months % 12 ? ' et ' . ($months % 12) . ' mois' : '')
        : $months . ' mois';
    $isExpired  = $cert['end_date'] < date('Y-m-d');
    $hasPrice   = !empty(array_filter(array_column($items, 'unit_price'), fn($v) => $v !== null));
    $grandTotal = (float) array_sum(array_column($items, 'total'));
  ?>

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
      <div class="doc-type">Certificat de Garantie</div>
      <div class="doc-ref"><?= e($cert['certificate_number']) ?></div>
      <div class="doc-meta">
        Émis le : <?= formatDate($cert['delivery_date'] ?: date('Y-m-d')) ?>
        <?php if ($cert['reference']): ?><br>Réf&nbsp;: <?= e($cert['reference']) ?><?php endif; ?>
        <?php if ($cert['invoice_number']): ?><br>Facture&nbsp;: <?= e($cert['invoice_number']) ?><?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Client block -->
  <div class="client-block">
    <div class="client-label">Bénéficiaire :</div>
    <div class="client-name"><?= e($cert['customer_name']) ?></div>
    <div class="client-detail">
      <?php if (!empty($cert['client_address'])): ?>
        <?= e(trim(strtok($cert['client_address'], "\n"))) ?>
        <?php strtok('', ''); ?>
        <br>
      <?php endif; ?>
      <?php if (!empty($cert['client_phone'])): ?>Tél&nbsp;: <?= e($cert['client_phone']) ?><?php endif; ?>
    </div>
  </div>
  <div class="cf"></div>

  <!-- Items table -->
  <?php if (!empty($items)): ?>
  <table class="items">
    <thead>
      <tr>
        <th style="width:<?= $hasPrice ? '55%' : '80%' ?>">Désignation</th>
        <th class="r" style="width:10%">Qté</th>
        <?php if ($hasPrice): ?>
        <th class="r" style="width:18%">P.U. HT</th>
        <th class="r" style="width:17%">Total HT</th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $item): ?>
      <tr>
        <td><?= e($item['label']) ?></td>
        <td class="r"><?= rtrim(rtrim(number_format((float)$item['quantity'], 2, ',', ' '), '0'), ',') ?></td>
        <?php if ($hasPrice): ?>
        <td class="r"><?= $item['unit_price'] !== null ? formatMoney((float)$item['unit_price']) : '—' ?></td>
        <td class="r"><?= $item['total']      !== null ? formatMoney((float)$item['total'])      : '—' ?></td>
        <?php endif; ?>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

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

  <?php elseif (!empty($cert['product_details'])): ?>
  <div class="desc-box"><?= e($cert['product_details']) ?></div>
  <?php endif; ?>

  <!-- Warranty period -->
  <div class="warranty-period">
    <div class="w-col">
      <div class="w-lbl">Début de garantie</div>
      <div class="w-val"><?= formatDate($cert['start_date']) ?></div>
    </div>
    <div class="w-col">
      <div class="w-lbl">Durée</div>
      <div class="w-val dur"><?= e($durLabel) ?></div>
    </div>
    <div class="w-col">
      <div class="w-lbl">Fin de garantie</div>
      <div class="w-val" <?= $isExpired ? 'style="color:#c41c1c;"' : '' ?>>
        <?= formatDate($cert['end_date']) ?>
      </div>
    </div>
  </div>

  <!-- Terms -->
  <?php if (!empty($cert['terms'])): ?>
  <div class="section-label">Conditions de garantie</div>
  <div class="terms-body"><?= e($cert['terms']) ?></div>
  <?php endif; ?>

  <!-- Info row -->
  <?php if (!empty($cert['payment_method'])): ?>
  <div class="info-row">
    <div class="info-item">
      <div class="ii-label">Mode de règlement</div>
      <div class="ii-value"><?= e(paymentMethodLabel($cert['payment_method'])) ?></div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Notes -->
  <?php if (!empty($cert['notes'])): ?>
  <div class="notes-block"><?= nl2br(e($cert['notes'])) ?></div>
  <?php endif; ?>

  <!-- Signature -->
  <?php
    $sigDate      = $cert['delivery_date'] ?: $cert['start_date'];
    $docDateParts = $sigDate ? explode('-', $sigDate) : null;
    $docDateFmt   = $docDateParts ? $docDateParts[2] . ' / ' . $docDateParts[1] . ' / ' . $docDateParts[0] : '___ / ___ / ______';
  ?>
  <div class="sig-row">
    <div class="sig-col">
      <div class="sig-lbl">Fait à _____________________, le <?= $docDateFmt ?></div>
      <div class="sig-place">
        <?php if (!empty($cert['use_watermark']) && !empty($company['watermark_data_uri'])): ?>
          <img src="<?= $company['watermark_data_uri'] ?>"
               style="opacity:<?= e($company['watermark_opacity'] ?? 0.15) ?>;" alt="">
        <?php endif; ?>
      </div>
      <div class="sig-line">Cachet et signature du vendeur</div>
    </div>
    <div class="sig-col">
      <div class="sig-lbl">Lu et approuvé</div>
      <div class="sig-place"></div>
      <div class="sig-line">Signature du bénéficiaire</div>
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
