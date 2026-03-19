<div class="row g-3">
  <div class="col-md-6">
    <label class="form-label fw-semibold">Nom / Raison sociale <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control" required
           value="<?= e($old['name'] ?? '') ?>">
  </div>

  <div class="col-md-3">
    <label class="form-label fw-semibold">ICE</label>
    <input type="text" name="ice" class="form-control" maxlength="50"
           value="<?= e($old['ice'] ?? '') ?>"
           placeholder="001234567000012">
  </div>

  <div class="col-md-3">
    <label class="form-label fw-semibold">Téléphone</label>
    <input type="text" name="phone" class="form-control"
           value="<?= e($old['phone'] ?? '') ?>"
           placeholder="0522-123456">
  </div>

  <div class="col-md-6">
    <label class="form-label fw-semibold">Email</label>
    <input type="email" name="email" class="form-control"
           value="<?= e($old['email'] ?? '') ?>"
           placeholder="contact@entreprise.ma">
  </div>

  <div class="col-12">
    <label class="form-label fw-semibold">Adresse</label>
    <textarea name="address" class="form-control" rows="2"><?= e($old['address'] ?? '') ?></textarea>
  </div>
</div>
