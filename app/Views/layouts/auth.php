<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($title ?? 'Masuk') ?> - Perpustakaan Kampus</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700;800&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/app.css?v=<?= @filemtime(__DIR__ . '/../../../public/assets/css/app.css') ?: 3 ?>" rel="stylesheet">
</head>
<body>
<div class="sky-decor" aria-hidden="true"></div>
<div class="auth-wrap">
  <div class="auth-box">
    <div class="auth-brand"><span class="brand-icon"><i class="bi bi-book-half"></i></span><span>Perpustakaan<br>Kampus</span></div>
    <?php require __DIR__ . '/../partials/alert.php'; ?>
    <?= $content ?>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
