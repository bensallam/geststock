<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Créer un compte — <?= APP_NAME ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/public/css/app.css">
  <style>
    .password-strength { height: 4px; border-radius: 2px; transition: all .3s; }
    .req-item { font-size: 12px; color: #6c757d; }
    .req-item.ok { color: #198754; }
    .req-item.ok::before { content: '✓ '; }
    .req-item:not(.ok)::before { content: '○ '; }
  </style>
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100 py-4">

<div class="card shadow-sm" style="width:100%;max-width:480px;">
  <div class="card-body p-4">

    <div class="text-center mb-4">
      <i class="bi bi-building text-primary" style="font-size:2.5rem;"></i>
      <h4 class="fw-bold mt-2">Créer un compte</h4>
      <p class="text-muted small">Inscrivez votre société sur <?= APP_NAME ?></p>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger py-2">
        <?php foreach ($errors as $err): ?>
          <div><i class="bi bi-exclamation-circle me-1"></i><?= e($err) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="<?= APP_URL ?>/register" novalidate id="regForm">

      <div class="mb-3">
        <label class="form-label fw-semibold" for="company_name">
          <i class="bi bi-building me-1 text-muted"></i>Nom de la société <span class="text-danger">*</span>
        </label>
        <input type="text" id="company_name" name="company_name" class="form-control"
               value="<?= e($old['company_name'] ?? '') ?>"
               placeholder="Ex : Ma Société SARL"
               required autofocus maxlength="255">
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold" for="email">
          <i class="bi bi-envelope me-1 text-muted"></i>Email <span class="text-danger">*</span>
        </label>
        <input type="email" id="email" name="email" class="form-control"
               value="<?= e($old['email'] ?? '') ?>"
               placeholder="contact@masociete.ma"
               required maxlength="255">
      </div>

      <div class="mb-1">
        <label class="form-label fw-semibold" for="password">
          <i class="bi bi-lock me-1 text-muted"></i>Mot de passe <span class="text-danger">*</span>
        </label>
        <div class="input-group">
          <input type="password" id="password" name="password" class="form-control"
                 placeholder="••••••••" required autocomplete="new-password">
          <button type="button" class="btn btn-outline-secondary" id="togglePwd" tabindex="-1">
            <i class="bi bi-eye" id="eyeIcon"></i>
          </button>
        </div>
      </div>

      <!-- Strength bar -->
      <div class="progress mb-2" style="height:4px;">
        <div class="progress-bar password-strength" id="strengthBar" style="width:0%;"></div>
      </div>

      <!-- Password requirements -->
      <div class="mb-3 d-flex gap-3 flex-wrap">
        <span class="req-item" id="req-len">8 caractères min.</span>
        <span class="req-item" id="req-upper">1 majuscule</span>
        <span class="req-item" id="req-num">1 chiffre</span>
      </div>

      <div class="mb-4">
        <label class="form-label fw-semibold" for="password_confirm">
          <i class="bi bi-lock-fill me-1 text-muted"></i>Confirmer le mot de passe <span class="text-danger">*</span>
        </label>
        <input type="password" id="password_confirm" name="password_confirm" class="form-control"
               placeholder="••••••••" required autocomplete="new-password">
        <div id="matchMsg" class="form-text" style="display:none;"></div>
      </div>

      <button type="submit" class="btn btn-primary w-100 py-2" id="submitBtn">
        <i class="bi bi-person-plus me-1"></i> Créer mon compte
      </button>
    </form>

    <hr class="my-3">
    <p class="text-center small text-muted mb-0">
      Vous avez déjà un compte ?
      <a href="<?= APP_URL ?>/login" class="fw-semibold">Se connecter</a>
    </p>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Toggle password visibility
document.getElementById('togglePwd').addEventListener('click', function () {
  var inp  = document.getElementById('password');
  var icon = document.getElementById('eyeIcon');
  if (inp.type === 'password') {
    inp.type = 'text';
    icon.className = 'bi bi-eye-slash';
  } else {
    inp.type = 'password';
    icon.className = 'bi bi-eye';
  }
});

// Live password strength + requirements
document.getElementById('password').addEventListener('input', function () {
  var val   = this.value;
  var len   = val.length >= 8;
  var upper = /[A-Z]/.test(val);
  var num   = /[0-9]/.test(val);
  var score = [len, upper, num].filter(Boolean).length;

  var colors = ['', 'bg-danger', 'bg-warning', 'bg-success'];
  var widths  = ['0%', '33%', '66%', '100%'];
  var bar     = document.getElementById('strengthBar');
  bar.className = 'progress-bar password-strength ' + (colors[score] || '');
  bar.style.width = widths[score];

  toggle('req-len',   len);
  toggle('req-upper', upper);
  toggle('req-num',   num);
  checkMatch();
});

function toggle(id, ok) {
  var el = document.getElementById(id);
  if (ok) el.classList.add('ok'); else el.classList.remove('ok');
}

// Confirm password match
document.getElementById('password_confirm').addEventListener('input', checkMatch);
function checkMatch() {
  var p1  = document.getElementById('password').value;
  var p2  = document.getElementById('password_confirm').value;
  var msg = document.getElementById('matchMsg');
  if (!p2) { msg.style.display = 'none'; return; }
  msg.style.display = 'block';
  if (p1 === p2) {
    msg.className = 'form-text text-success';
    msg.textContent = '✓ Les mots de passe correspondent.';
  } else {
    msg.className = 'form-text text-danger';
    msg.textContent = '✗ Les mots de passe ne correspondent pas.';
  }
}
</script>
</body>
</html>
