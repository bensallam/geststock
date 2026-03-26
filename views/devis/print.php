<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Devis <?= e($devis['devis_number']) ?></title>
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
    .doc-type { font-size: 24px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; }
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
    .totals-table tr.tva-note td { color: #777; font-size: 10px; }
    .totals-table tr.total-net td {
      font-size: 13px; font-weight: 700; border-top: 2px solid #111; padding-top: 8px;
    }

    /* ── Amount in words ── */
    .amount-words {
      clear: both; font-size: 10px; color: #333;
      background: #f9f9f9; border: 1px solid #ddd; padding: 8px 12px; margin: 12px 0;
    }

    /* ── Validity notice ── */
    .validity-notice {
      clear: both; font-size: 10px; color: #555;
      border: 1px solid #ddd; padding: 6px 12px; margin: 8px 0;
      background: #fffbf0;
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
    .sig-place { height: 72px; display: flex; align-items: flex-end; justify-content: center; }
    .sig-place img { max-height: 72px; max-width: 100%; object-fit: contain; }
    .sig-line { border-top: 1px solid #555; padding-top: 5px; font-size: 9px; color: #888; text-align: center; }

    /* ── Footer ── */
    .doc-footer {
      margin-top: 24px; padding-top: 10px; border-top: 1px solid #ddd;
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

  <!-- Action bar -->
  <div class="action-bar no-print">
    <button class="btn-print" onclick="window.print()">Imprimer</button>
    <a class="btn-pdf" href="<?= APP_URL ?>/devis/pdf?id=<?= $devis['id'] ?>">Télécharger PDF</a>
    <a class="btn-back" href="<?= APP_URL ?>/devis/show?id=<?= $devis['id'] ?>">← Retour</a>
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
      <div class="doc-type">Devis</div>
      <div class="doc-ref"><?= e($devis['devis_number']) ?></div>
      <div class="doc-meta">
        <?= formatDate($devis['date']) ?>
        <?php if ($devis['validity_date']): ?>
          <br>Valable jusqu'au : <?= formatDate($devis['validity_date']) ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Client block -->
  <div class="client-block">
    <div class="client-label">Adressé à :</div>
    <div class="client-name"><?= e($devis['client_name']) ?></div>
    <div class="client-detail">
      <?php if (!empty($devis['client_address'])): ?>
        <?= e(trim(strtok($devis['client_address'], "\n"))) ?>
        <?php strtok('', ''); ?>
        <br>
      <?php endif; ?>
      <?php if (!empty($devis['client_ice'])): ?>ICE&nbsp;: <?= e($devis['client_ice']) ?><br><?php endif; ?>
      <?php if (!empty($devis['client_phone'])): ?>Tél&nbsp;: <?= e($devis['client_phone']) ?><?php endif; ?>
    </div>
  </div>
  <div class="cf"></div>

  <!-- Items -->
  <table class="items">
    <thead>
      <tr>
        <th style="width:55%">Désignation</th>
        <th class="r" style="width:10%">Qté</th>
        <th class="r" style="width:18%">P.U. HT</th>
        <th class="r" style="width:17%">Total HT</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $item): ?>
      <tr>
        <td><?= e($item['label']) ?></td>
        <td class="r"><?= rtrim(rtrim(number_format((float)$item['quantity'], 2, ',', ' '), '0'), ',') ?></td>
        <td class="r"><?= formatMoney((float)$item['unit_price']) ?></td>
        <td class="r"><?= formatMoney((float)$item['total']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Totals -->
  <div class="totals-wrap">
    <table class="totals-table">
      <tr>
        <td>Total HT</td>
        <td><?= formatMoney((float)$devis['total_ht']) ?></td>
      </tr>
      <?php if ((float)$devis['tax_rate'] > 0): ?>
      <tr>
        <td>TVA (<?= $devis['tax_rate'] ?>%)</td>
        <td><?= formatMoney((float)$devis['tax_amount']) ?></td>
      </tr>
      <?php else: ?>
      <tr class="tva-note">
        <td colspan="2">TVA non applicable — art. 293 B du CGI</td>
      </tr>
      <?php endif; ?>
      <tr class="total-net">
        <td>Total Net</td>
        <td><?= formatMoney((float)$devis['total_ttc']) ?></td>
      </tr>
    </table>
  </div>

  <!-- Amount in words -->
  <div class="amount-words">
    <strong>Arrêté à la somme de :</strong> <?= e(amountInWords((float)$devis['total_ttc'])) ?>
  </div>

  <?php if ($devis['validity_date']): ?>
  <div class="validity-notice">
    Ce devis est valable jusqu'au <strong><?= formatDate($devis['validity_date']) ?></strong>.
    Passé ce délai, il ne pourra plus être accepté sans accord préalable.
  </div>
  <?php endif; ?>

  <!-- Info row -->
  <?php $hasInfo = !empty($devis['payment_method']); ?>
  <?php if ($hasInfo): ?>
  <div class="info-row">
    <?php if (!empty($devis['payment_method'])): ?>
    <div class="info-item">
      <div class="ii-label">Mode de règlement</div>
      <div class="ii-value"><?= e(paymentMethodLabel($devis['payment_method'])) ?></div>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- Notes -->
  <?php if ($devis['notes']): ?>
  <div class="notes-block"><?= nl2br(e($devis['notes'])) ?></div>
  <?php endif; ?>

  <!-- Signature -->
  <?php
    $docDateParts = $devis['date'] ? explode('-', $devis['date']) : null;
    $docDateFmt   = $docDateParts ? $docDateParts[2] . ' / ' . $docDateParts[1] . ' / ' . $docDateParts[0] : '___ / ___ / ______';
  ?>
  <div class="sig-row">
    <div class="sig-col">
      <div class="sig-lbl">Fait à _____________________, le <?= $docDateFmt ?></div>
      <div class="sig-place">
        <?php if (!empty($devis['use_watermark']) && !empty($company['watermark_data_uri'])): ?>
          <img src="<?= $company['watermark_data_uri'] ?>"
               style="opacity:<?= e($company['watermark_opacity'] ?? 0.15) ?>;" alt="">
        <?php endif; ?>
      </div>
      <div class="sig-line">Cachet et signature du prestataire</div>
    </div>
    <div class="sig-col">
      <div class="sig-lbl">Bon pour accord</div>
      <div class="sig-place"></div>
      <div class="sig-line">Signature du client</div>
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
