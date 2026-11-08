<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($title ?? 'Perpustakaan Kampus') ?> - Perpustakaan Kampus</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700;800&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/app.css?v=<?= @filemtime(__DIR__ . '/../../../public/assets/css/app.css') ?: 3 ?>" rel="stylesheet">
</head>
<body>
<div class="sky-decor" aria-hidden="true"></div>
<div class="app-shell">
  <aside class="sidebar">
    <div class="brand"><span class="brand-icon"><i class="bi bi-book-half"></i></span><span>Perpustakaan<br>Kampus</span></div>
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>
    <button class="settings-dot" type="button" aria-label="Pengaturan"><i class="bi bi-gear-fill"></i></button>
  </aside>

  <main class="main-area">
    <nav class="topbar">
      <div class="top-search-wrap">
        <form id="globalSearch" class="top-search" method="GET">
          <i class="bi bi-search"></i>
          <input name="q" type="text" placeholder="Cari buku, judul, penulis..." aria-label="Pencarian cepat">
        </form>
      </div>
    </nav>

    <div class="content-area">
      <?php require __DIR__ . '/../partials/alert.php'; ?>
      <?= $content ?>
    </div>
  </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const gs=document.getElementById('globalSearch');
if(gs){
  const path=window.location.pathname;
  let target=<?= \App\Core\Auth::isAdmin() ? "'/buku'" : "'/katalog'" ?>;
  if(path.includes('/peminjaman')) target='/peminjaman';
  else if(path.includes('/pengembalian')) target='/pengembalian';
  else if(path.includes('/anggota')) target='/anggota';
  else if(path.includes('/pengguna')) target='/pengguna';
  else if(path.includes('/katalog')) target='/katalog';
  gs.action=<?= json_encode(BASE_URL) ?>+target;
}
</script>
</body>
</html>
