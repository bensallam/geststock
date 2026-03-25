<div class="row g-3">
  <div class="col-md-6">
    <label class="form-label fw-semibold">Nom de l'entreprise <span class="text-danger">*</span></label>
    <input type="text" name="company_name" class="form-control" required
           value="<?= e($old['company_name'] ?? '') ?>">
  </div>
  <div class="col-md-6">
    <label class="form-label fw-semibold">ICE / IF / RC</label>
    <input type="text" name="tax_id" class="form-control"
           placeholder="Ex: 001234567000012"
           value="<?= e($old['tax_id'] ?? '') ?>">
  </div>
  <div class="col-md-6">
    <label class="form-label fw-semibold">Téléphone</label>
    <input type="text" name="phone" class="form-control"
           value="<?= e($old['phone'] ?? '') ?>">
  </div>
  <div class="col-md-6">
    <label class="form-label fw-semibold">Email</label>
    <input type="email" name="email" class="form-control"
           value="<?= e($old['email'] ?? '') ?>">
  </div>
  <div class="col-12">
    <label class="form-label fw-semibold">Adresse</label>
    <textarea name="address" class="form-control" rows="2"><?= e($old['address'] ?? '') ?></textarea>
  </div>
  <div class="col-12"><hr class="my-1"></div>
  <div class="col-12">
    <label class="form-label fw-semibold">Notes par défaut sur les factures</label>
    <textarea name="invoice_notes" class="form-control" rows="2"><?= e($old['invoice_notes'] ?? '') ?></textarea>
  </div>
  <div class="col-12">
    <label class="form-label fw-semibold">Message de pied de page</label>
    <input type="text" name="invoice_footer" class="form-control"
           placeholder="Ex: Merci pour votre confiance."
           value="<?= e($old['invoice_footer'] ?? '') ?>">
  </div>
  <div class="col-md-6">
    <label class="form-label fw-semibold">Mode de règlement par défaut</label>
    <select name="default_payment_method" class="form-select">
      <option value="">— Non précisé —</option>
      <?php foreach (['cheque' => 'Chèque', 'espece' => 'Espèce', 'virement' => 'Virement bancaire'] as $val => $lbl): ?>
        <option value="<?= $val ?>" <?= ($old['default_payment_method'] ?? '') === $val ? 'selected' : '' ?>>
          <?= $lbl ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-12">
    <label class="form-label fw-semibold">Conditions de garantie par défaut</label>
    <textarea name="default_warranty_terms" class="form-control" rows="4"><?= e($old['default_warranty_terms'] ?? '') ?></textarea>
  </div>
</div>
