<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Édition — BL <?= e($note['note_number']) ?></title>
  <link rel="stylesheet" href="<?= APP_URL ?>/public/css/live-editor.css">
  <style>
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif; font-size: 12px; color: #111; background: #f8fafc; line-height: 1.5; }
    .page { max-width: 960px; margin: 24px auto; padding: 36px 48px; background: #fff; box-shadow: 0 4px 20px rgba(0,0,0,.08); border-radius: 4px; }
    .cf::after { content: ''; display: table; clear: both; }
    .r { text-align: right; }
    .doc-header { display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 16px; border-bottom: 2px solid #111; margin-bottom: 24px; gap: 20px; }
    .company-block { flex: 1; }
    .company-block img { display: block; max-height: 56px; max-width: 160px; object-fit: contain; margin-bottom: 8px; }
    .company-name { font-size: 14px; font-weight: 700; }
    .company-sub  { font-size: 10px; color: #555; line-height: 1.7; margin-top: 3px; }
    .doc-title-block { text-align: right; flex-shrink: 0; }
    .doc-type { font-size: 22px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; }
    .doc-ref  { font-size: 13px; font-weight: 600; margin-top: 6px; }
    .doc-meta { font-size: 10px; color: #555; margin-top: 3px; line-height: 1.6; }
    .client-block { float: right; border: 1px solid #bbb; padding: 12px 16px; min-width: 220px; max-width: 280px; margin-bottom: 24px; }
    .client-label  { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #888; margin-bottom: 5px; }
    .client-name   { font-size: 13px; font-weight: 700; }
    .client-detail { font-size: 10px; color: #444; line-height: 1.65; margin-top: 4px; }
    table.items { width: 100%; border-collapse: collapse; margin-bottom: 4px; font-size: 11px; }
    table.items thead th { background: #f2f2f2; border-top: 1px solid #bbb; border-bottom: 1px solid #bbb; padding: 8px 10px; font-size: 9px; text-transform: uppercase; letter-spacing: .5px; font-weight: 700; text-align: left; }
    table.items thead th.r { text-align: right; }
    table.items tbody td { padding: 7px 10px; border-bottom: 1px solid #eee; vertical-align: middle; }
    table.items tbody td.r { text-align: right; }
    .totals-wrap { overflow: hidden; margin: 12px 0; }
    .totals-table { float: right; width: 280px; border-collapse: collapse; font-size: 11px; }
    .totals-table td { padding: 4px 0; }
    .totals-table td:last-child { text-align: right; font-weight: 600; padding-left: 16px; }
    .totals-table tr.total-net td { font-size: 13px; font-weight: 700; border-top: 2px solid #111; padding-top: 8px; }
    .amount-words { clear: both; font-size: 10px; color: #333; background: #f9f9f9; border: 1px solid #ddd; padding: 8px 12px; margin: 12px 0; }
    .notes-block { border-left: 3px solid #ddd; padding: 6px 12px; font-size: 10px; color: #444; line-height: 1.65; margin-top: 12px; }
    .sig-row { display: flex; gap: 48px; margin-top: 36px; }
    .sig-col  { flex: 1; }
    .sig-lbl  { font-size: 9px; text-transform: uppercase; color: #888; margin-bottom: 4px; }
    .sig-place { height: 72px; display: flex; align-items: flex-end; justify-content: center; }
    .sig-place img { max-height: 72px; max-width: 100%; object-fit: contain; }
    .sig-line { border-top: 1px solid #555; padding-top: 5px; font-size: 9px; color: #888; text-align: center; }
    .doc-footer { margin-top: 24px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 9px; color: #888; text-align: center; line-height: 1.8; }
    @media print {
      body { background: #fff; }
      .page { max-width: 100%; margin: 0; padding: 0 14mm; box-shadow: none; border-radius: 0; }
      body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
  </style>
</head>
<body>

<?php $hasPrice = !empty(array_filter(array_column($items, 'unit_price'), fn($v) => $v !== null)); ?>

<div class="le-toolbar no-print">
  <div class="le-toolbar-left">
    <a href="<?= APP_URL ?>/delivery-notes/show?id=<?= $note['id'] ?>" class="le-btn">← Retour</a>
    <span class="le-title">Édition — <strong>BL <?= e($note['note_number']) ?></strong></span>
  </div>
  <div class="le-toolbar-right">
    <span id="leDirty" class="le-dirty">● Non enregistré</span>
    <button id="leSave" class="le-btn le-btn-primary" onclick="saveDoc()">Enregistrer</button>
    <button class="le-btn" onclick="window.print()">🖨 Imprimer</button>
    <a class="le-btn" href="<?= APP_URL ?>/delivery-notes/pdf?id=<?= $note['id'] ?>">PDF</a>
  </div>
</div>
<div class="le-spacer no-print"></div>

<div class="page">

  <div class="doc-header">
    <div class="company-block">
      <?php if (!empty($company['logo_data_uri'])): ?>
        <img src="<?= $company['logo_data_uri'] ?>" alt="Logo">
      <?php endif; ?>
      <div class="company-name"><?= e($company['company_name'] ?: APP_NAME) ?></div>
      <div class="company-sub">
        <?php if (!empty($company['address'])): ?><?= e(trim(strtok($company['address'],"\n"))) ?><?php strtok('',''); ?><br><?php endif; ?>
        <?php if (!empty($company['phone'])): ?>Tél&nbsp;: <?= e($company['phone']) ?><?php endif; ?>
        <?php if (!empty($company['phone']) && !empty($company['email'])): ?> &nbsp;·&nbsp; <?php endif; ?>
        <?php if (!empty($company['email'])): ?><?= e($company['email']) ?><?php endif; ?>
        <?php if (!empty($company['tax_id'])): ?><br>ICE&nbsp;: <?= e($company['tax_id']) ?><?php endif; ?>
      </div>
    </div>
    <div class="doc-title-block">
      <div class="doc-type">Bon de livraison</div>
      <div class="doc-ref">
        <span class="le-editable" contenteditable="true" data-field="note_number"
              style="font-size:13px;font-weight:600;"><?= e($note['note_number']) ?></span>
      </div>
      <div class="doc-meta">
        <input type="date" class="le-input le-input-date" data-field="delivery_date" value="<?= e($note['delivery_date']) ?>">
        <?php if ($note['reference']): ?>
          <br>Réf&nbsp;:
          <span class="le-editable" contenteditable="true" data-field="reference"><?= e($note['reference']) ?></span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="client-block">
    <div class="client-label">Adressé à :</div>
    <div class="client-name">
      <span class="le-editable" contenteditable="true" data-field="customer_name"><?= e($note['customer_name']) ?></span>
    </div>
    <div class="client-detail">
      <?php if (!empty($note['client_address'])): ?>
        <span class="le-editable-block" contenteditable="true" data-field="client_address"><?= e($note['client_address']) ?></span><br>
      <?php endif; ?>
      <?php if (!empty($note['client_phone'])): ?>Tél&nbsp;: <?= e($note['client_phone']) ?><?php endif; ?>
    </div>
  </div>
  <div class="cf"></div>

  <table class="items">
    <thead>
      <tr>
        <th style="width:<?= $hasPrice ? '50%' : '70%' ?>">Désignation</th>
        <th class="no-print" style="width:28px;"></th>
        <th class="r" style="width:12%">Quantité</th>
        <?php if ($hasPrice): ?>
        <th class="r" style="width:18%">P.U. HT</th>
        <th class="r" style="width:15%">Total HT</th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody id="itemsTbody">
      <?php foreach ($items as $item): ?>
      <tr class="le-item-row">
        <td><span class="le-label le-editable" contenteditable="true" style="display:block;min-width:80px;"><?= e($item['label']) ?></span></td>
        <td class="no-print" style="text-align:center;"><button type="button" class="le-row-del" onclick="removeItemRow(this)">✕</button></td>
        <td class="r"><input type="number" class="le-input le-qty le-recalc" value="<?= (float)$item['quantity'] ?>" min="0.01" step="0.01" style="width:56px"></td>
        <?php if ($hasPrice): ?>
        <td class="r"><input type="number" class="le-input le-price le-recalc" value="<?= $item['unit_price'] !== null ? (float)$item['unit_price'] : '' ?>" min="0" step="0.01" style="width:80px"></td>
        <td class="r"><span class="le-row-total"><?= $item['unit_price'] !== null ? formatMoney((float)$item['total']) : '—' ?></span></td>
        <?php endif; ?>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <button type="button" class="le-add-row-btn no-print" onclick="addItemRow('itemsTbody', <?= $hasPrice ? 'true' : 'false' ?>)">+ Ajouter une ligne</button>

  <?php if ($hasPrice): ?>
  <div class="totals-wrap">
    <table class="totals-table">
      <tr class="total-net"><td>Total Net à payer</td><td id="sumTTC">—</td></tr>
    </table>
  </div>
  <div class="amount-words">
    <strong>Arrêté à la somme de :</strong>
    <span id="sumWords">—</span>
  </div>
  <?php endif; ?>

  <div class="notes-block" style="min-height:24px;">
    <div class="le-editable-block" contenteditable="true" data-field="notes" placeholder="Notes…"><?= e($note['notes']) ?></div>
  </div>

  <?php $dp=$note['delivery_date']?explode('-',$note['delivery_date']):null; $df=$dp?$dp[2].'/'.$dp[1].'/'.$dp[0]:'___/___/______'; ?>
  <div class="sig-row">
    <div class="sig-col">
      <div class="sig-lbl">Fait à _____________________, le <?= $df ?></div>
      <div class="sig-place">
        <?php if (!empty($note['use_watermark']) && !empty($company['watermark_data_uri'])): ?>
          <img src="<?= $company['watermark_data_uri'] ?>" style="opacity:<?= e($company['watermark_opacity']??0.15) ?>;" alt="">
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

  <div class="doc-footer">
    <?php $fp=[];
      if (!empty($company['company_name'])) $fp[]=e($company['company_name']);
      if (!empty($company['address']))      { $fp[]=e(trim(strtok($company['address'],"\n"))); strtok('',''); }
      if (!empty($company['phone']))        $fp[]='Tél&nbsp;: '.e($company['phone']);
      if (!empty($company['email']))        $fp[]=e($company['email']);
      if (!empty($company['tax_id']))       $fp[]='ICE&nbsp;: '.e($company['tax_id']);
      echo implode(' &nbsp;·&nbsp; ',$fp);
    ?>
    <?php if (!empty($company['invoice_footer'])): ?><br><?= e($company['invoice_footer']) ?><?php endif; ?>
  </div>

</div>

<script src="<?= APP_URL ?>/public/js/live-editor.js"></script>
<script>
var LE_CONFIG = { docId: <?= $note['id'] ?>, saveUrl: '<?= APP_URL ?>/delivery-notes/live-update' };
// For delivery notes without prices, disable totals display
<?php if (!$hasPrice): ?>
document.getElementById = (function(orig){ return function(id){ if(['sumHT','sumTax','sumTTC','sumWords','displayTaxRate'].includes(id)) return null; return orig.call(document,id); }; })(document.getElementById);
<?php endif; ?>
</script>
</body>
</html>
