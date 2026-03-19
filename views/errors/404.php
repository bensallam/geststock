<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>404 — Page introuvable</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100 text-center">
  <div>
    <div class="display-1 fw-bold text-muted">404</div>
    <h3 class="mb-3">Page introuvable</h3>
    <p class="text-muted mb-4">La page que vous cherchez n'existe pas ou a été déplacée.</p>
    <a href="<?= defined('APP_URL') ? APP_URL : '/' ?>/dashboard" class="btn btn-primary">
      ← Retour au tableau de bord
    </a>
  </div>
</body>
</html>
