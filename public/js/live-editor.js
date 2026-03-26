/**
 * Live Document Editor — shared JS
 * Requires window.LE_CONFIG = { docId, saveUrl }
 */

/* ── State ─────────────────────────────────────────────────── */

var _dirty         = false;
var _autoSaveTimer = null;
var _rowIdx        = 5000; // avoid collision with any existing indices

/* ── Boot ──────────────────────────────────────────────────── */

document.addEventListener('DOMContentLoaded', function () {
  recalc();

  // Bind existing item rows
  document.querySelectorAll('.le-item-row').forEach(bindRowEvents);

  // Watch all named contenteditable/input/select fields
  document.querySelectorAll('[data-field]').forEach(function (el) {
    var evt = (el.tagName === 'SELECT' || el.tagName === 'INPUT' || el.tagName === 'TEXTAREA')
              ? 'input' : 'input';
    el.addEventListener(evt, function () {
      markDirty();
      if (el.classList.contains('le-recalc')) recalc();
    });
    if (el.tagName === 'SELECT') {
      el.addEventListener('change', function () { markDirty(); });
    }
  });

  // Ctrl/Cmd + S → save
  document.addEventListener('keydown', function (e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
      e.preventDefault();
      saveDoc();
    }
  });

  // Warn before leaving with unsaved changes
  window.addEventListener('beforeunload', function (e) {
    if (_dirty) {
      e.preventDefault();
      e.returnValue = '';
    }
  });
});

/* ── Dirty state ───────────────────────────────────────────── */

function markDirty() {
  _dirty = true;
  var el = document.getElementById('leDirty');
  if (el) el.style.display = 'inline';
  clearTimeout(_autoSaveTimer);
  // Auto-save after 60 s of inactivity
  _autoSaveTimer = setTimeout(function () { if (_dirty) saveDoc(); }, 60000);
}

function markClean() {
  _dirty = false;
  var el = document.getElementById('leDirty');
  if (el) el.style.display = 'none';
  clearTimeout(_autoSaveTimer);
}

/* ── Recalculate totals ─────────────────────────────────────── */

function recalc() {
  var rateEl  = document.getElementById('edTaxRate');
  var taxRate = rateEl ? (parseFloat(rateEl.value) || 0) : 0;

  var ht = 0;
  document.querySelectorAll('.le-item-row').forEach(function (row) {
    var qtyEl   = row.querySelector('.le-qty');
    var priceEl = row.querySelector('.le-price');
    var totEl   = row.querySelector('.le-row-total');
    var qty     = qtyEl   ? (parseFloat(qtyEl.value)   || 0) : 0;
    var price   = priceEl ? (parseFloat(priceEl.value)  || 0) : 0;
    var line    = Math.round(qty * price * 100) / 100;
    ht += line;
    if (totEl) totEl.textContent = formatMAD(line);
  });

  ht = Math.round(ht * 100) / 100;
  var tax = Math.round(ht * taxRate) / 100;
  var ttc = Math.round((ht + tax) * 100) / 100;

  setText('sumHT',  formatMAD(ht));
  setText('sumTax', formatMAD(tax));
  setText('sumTTC', formatMAD(ttc));
  setText('displayTaxRate', taxRate);
  setText('sumWords', amountInWords(ttc));
}

function setText(id, val) {
  var el = document.getElementById(id);
  if (el) el.textContent = val;
}

/* ── Collect data from DOM ──────────────────────────────────── */

function collectData() {
  var data = { id: LE_CONFIG.docId, items: [] };

  // All named fields (skip those inside item rows — handled below)
  document.querySelectorAll('[data-field]').forEach(function (el) {
    if (el.closest('.le-item-row')) return;
    var field = el.dataset.field;
    if (el.tagName === 'INPUT' || el.tagName === 'SELECT' || el.tagName === 'TEXTAREA') {
      data[field] = el.value;
    } else {
      data[field] = el.textContent.trim();
    }
  });

  // Item rows
  document.querySelectorAll('.le-item-row').forEach(function (row) {
    var labelEl = row.querySelector('.le-label');
    var qtyEl   = row.querySelector('.le-qty');
    var priceEl = row.querySelector('.le-price');

    var label = labelEl
      ? (labelEl.tagName === 'INPUT' ? labelEl.value : labelEl.textContent).trim()
      : '';
    if (!label) return;

    data.items.push({
      product_id: row.dataset.productId || null,
      label:      label,
      quantity:   qtyEl   ? (parseFloat(qtyEl.value)   || 1) : 1,
      unit_price: priceEl ? (parseFloat(priceEl.value)  || 0) : 0,
    });
  });

  return data;
}

/* ── AJAX save ──────────────────────────────────────────────── */

async function saveDoc() {
  var data = collectData();
  var btn  = document.getElementById('leSave');
  if (btn) { btn.disabled = true; btn.textContent = 'Enregistrement…'; }

  try {
    var resp = await fetch(LE_CONFIG.saveUrl, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body:    JSON.stringify(data),
    });
    var json = await resp.json();

    if (json.ok) {
      markClean();
      if (json.amount_words) setText('sumWords', json.amount_words);
      if (btn) {
        btn.textContent = '✓ Enregistré';
        setTimeout(function () { btn.textContent = 'Enregistrer'; btn.disabled = false; }, 2200);
      }
    } else {
      alert('Erreur : ' + (json.error || 'Impossible d\'enregistrer.'));
      if (btn) { btn.textContent = 'Enregistrer'; btn.disabled = false; }
    }
  } catch (err) {
    alert('Erreur réseau. Vérifiez votre connexion.');
    if (btn) { btn.textContent = 'Enregistrer'; btn.disabled = false; }
  }
}

/* ── Item row management ────────────────────────────────────── */

function addItemRow(tbodyId, hasPrices) {
  var tbody = document.getElementById(tbodyId || 'itemsTbody');
  if (!tbody) return;
  hasPrices = (hasPrices !== false);

  var tr = document.createElement('tr');
  tr.className       = 'le-item-row';
  tr.dataset.productId = '';

  var priceCol = hasPrices
    ? '<td class="r"><input type="number" class="le-input le-price le-recalc" value="0.00" min="0" step="0.01" style="width:80px"></td>'
    : '';
  var totalCol = hasPrices
    ? '<td class="r"><span class="le-row-total">0,00 MAD</span></td>'
    : '';

  tr.innerHTML =
    '<td>' +
      '<span class="le-label le-editable" contenteditable="true" ' +
           'placeholder="Désignation" style="display:block;min-width:100px;"></span>' +
    '</td>' +
    '<td class="no-print" style="width:28px;text-align:center;">' +
      '<button type="button" class="le-row-del" onclick="removeItemRow(this)" title="Supprimer">✕</button>' +
    '</td>' +
    '<td class="r"><input type="number" class="le-input le-qty le-recalc" value="1" min="0.01" step="0.01" style="width:60px"></td>' +
    priceCol + totalCol;

  tbody.appendChild(tr);
  bindRowEvents(tr);
  recalc();
}

function removeItemRow(btn) {
  var rows = document.querySelectorAll('.le-item-row');
  if (rows.length <= 1) return; // keep at least 1
  btn.closest('tr').remove();
  recalc();
  markDirty();
}

function bindRowEvents(tr) {
  tr.querySelectorAll('.le-recalc').forEach(function (el) {
    el.addEventListener('input', function () { recalc(); markDirty(); });
  });
  tr.querySelectorAll('[contenteditable]').forEach(function (el) {
    el.addEventListener('input', markDirty);
  });
}

/* ── Formatting helpers ─────────────────────────────────────── */

function formatMAD(n) {
  return n.toLocaleString('fr-MA', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' MAD';
}

/* ── Amount in words (French/MAD) ───────────────────────────── */

function amountInWords(amount) {
  var intPart  = Math.floor(amount);
  var cents    = Math.round((amount - intPart) * 100);
  var words    = _numToFr(intPart);
  words = words.charAt(0).toUpperCase() + words.slice(1);
  words += intPart > 1 ? ' dirhams' : ' dirham';
  if (cents > 0) {
    words += ' et ' + _numToFr(cents) + (cents > 1 ? ' centimes' : ' centime');
  }
  return words;
}

function _numToFr(n) {
  if (n === 0) return 'zéro';
  var ones = ['','un','deux','trois','quatre','cinq','six','sept','huit','neuf',
              'dix','onze','douze','treize','quatorze','quinze','seize',
              'dix-sept','dix-huit','dix-neuf'];
  var tns  = ['','','vingt','trente','quarante','cinquante','soixante'];
  var r    = '';

  if (n >= 1000000) { var m = Math.floor(n/1000000); r += _numToFr(m)+(m>1?' millions ':' million '); n %= 1000000; }
  if (n >= 1000)    { var t = Math.floor(n/1000);    r += t===1?'mille ':_numToFr(t)+' mille '; n %= 1000; }
  if (n >= 100)     { var h = Math.floor(n/100); var rem=n%100; r+=h===1?'cent ':ones[h]+(rem===0?' cents ':' cent '); n=rem; }

  if (n >= 20) {
    var t2 = Math.floor(n/10), u = n%10;
    if      (t2===7) r += u===1?'soixante-et-onze ':'soixante-'+ones[10+u]+' ';
    else if (t2===8) r += u===0?'quatre-vingts ':'quatre-vingt-'+ones[u]+' ';
    else if (t2===9) r += 'quatre-vingt-'+ones[10+u]+' ';
    else             r += u===0?tns[t2]+' ':u===1?tns[t2]+'-et-un ':tns[t2]+'-'+ones[u]+' ';
  } else if (n > 0) {
    r += ones[n] + ' ';
  }

  return r.trim();
}
