<?php
use App\Core\Auth;

$current = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (BASE_URL !== '' && str_starts_with($current, BASE_URL)) {
    $current = substr($current, strlen(BASE_URL));
}
$current = '/' . ltrim($current, '/');
if ($current !== '/') {
    $current = rtrim($current, '/');
}
function isActive(string $path, string $current): string
{
    return $current === $path ? 'active' : '';
}
?>
<div class="menu-label">Menu utama</div>
<?php if (Auth::isAdmin()): ?>
<a href="<?= BASE_URL ?>/" class="nav-item <?= isActive('/', $current) ?>"><i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span></a>
<a href="<?= BASE_URL ?>/buku" class="nav-item <?= isActive('/buku', $current) ?>"><i class="bi bi-journal-bookmark-fill"></i><span>Data Buku</span></a>
<a href="<?= BASE_URL ?>/peminjaman" class="nav-item <?= isActive('/peminjaman', $current) ?>"><i class="bi bi-arrow-up-right-square-fill"></i><span>Peminjaman</span></a>
<a href="<?= BASE_URL ?>/pengembalian" class="nav-item <?= isActive('/pengembalian', $current) ?>"><i class="bi bi-arrow-down-left-square-fill"></i><span>Pengembalian</span></a>
<a href="<?= BASE_URL ?>/anggota" class="nav-item <?= isActive('/anggota', $current) ?>"><i class="bi bi-people-fill"></i><span>Data Anggota</span></a>
<a href="<?= BASE_URL ?>/pengguna" class="nav-item <?= isActive('/pengguna', $current) ?>"><i class="bi bi-person-badge-fill"></i><span>Data Pengguna</span></a>
<?php else: ?>
<a href="<?= BASE_URL ?>/" class="nav-item <?= isActive('/', $current) ?>"><i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span></a>
<a href="<?= BASE_URL ?>/katalog" class="nav-item <?= isActive('/katalog', $current) ?>"><i class="bi bi-journal-bookmark-fill"></i><span>Data Buku</span></a>
<a href="<?= BASE_URL ?>/peminjaman-saya" class="nav-item <?= isActive('/peminjaman-saya', $current) ?>"><i class="bi bi-clock-history"></i><span>Peminjaman Saya</span></a>
<a href="<?= BASE_URL ?>/riwayat-saya" class="nav-item <?= isActive('/riwayat-saya', $current) ?>"><i class="bi bi-calendar-check-fill"></i><span>Riwayat Peminjaman</span></a>
<?php endif; ?>
<a href="<?= BASE_URL ?>/profil" class="nav-item <?= isActive('/profil', $current) ?>"><i class="bi bi-person-circle"></i><span>Profil Saya</span></a>

<div class="user-chip">
  <div class="user-chip-name"><?= htmlspecialchars(Auth::nama()) ?></div>
  <div class="user-chip-role"><?= Auth::isAdmin() ? 'Admin' : 'User' ?></div>
</div>
<form action="<?= BASE_URL ?>/logout" method="POST" class="m-0">
  <button type="submit" class="nav-item nav-logout w-100"><i class="bi bi-box-arrow-right"></i><span>Logout</span></button>
</form>
