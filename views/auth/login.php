<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion — <?= APP_NAME ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/public/css/app.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">

<div class="card shadow-sm" style="width:100%;max-width:420px;">
  <div class="card-body p-4">
    <div class="text-center mb-4">
      <i class="bi bi-box-seam text-primary" style="font-size:2.5rem;"></i>
      <h4 class="fw-bold mt-2"><?= APP_NAME ?></h4>
      <p class="text-muted small">Gestion de stock & facturation</p>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <?php foreach ($errors as $e): ?>
          <div><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="<?= APP_URL ?>/login" novalidate>
      <div class="mb-3">
        <label class="form-label fw-semibold" for="email">Email</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-envelope"></i></span>
          <input type="email" id="email" name="email" class="form-control"
                 value="<?= e($_POST['email'] ?? '') ?>"
                 placeholder="admin@facturation.ma" required autofocus>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label fw-semibold" for="password">Mot de passe</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock"></i></span>
          <input type="password" id="password" name="password" class="form-control"
                 placeholder="••••••••" required>
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100 py-2">
        <i class="bi bi-box-arrow-in-right me-1"></i> Se connecter
      </button>
    </form>

    <div class="mt-3 text-center text-muted small">
      <strong>Démo :</strong> admin@facturation.ma / admin123
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
