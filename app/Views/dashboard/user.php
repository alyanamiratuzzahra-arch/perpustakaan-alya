<div class="page-head">
  <div>
    <h1>Halo, <?= htmlspecialchars(\App\Core\Auth::nama()) ?> <span aria-hidden="true">🌸</span></h1>
    <p>Cari buku dan cek stoknya sebelum datang ke perpustakaan.</p>
  </div>
  <a href="<?= BASE_URL ?>/katalog" class="btn btn-primary"><i class="bi bi-search me-1"></i>Cari Buku</a>
</div>

<?php if (!$nim): ?>
<div class="alert alert-warning">
  <i class="bi bi-exclamation-triangle me-1"></i>
  Akun Anda belum terhubung dengan NIM anggota, sehingga data peminjaman belum tampil. Hubungi admin perpustakaan.
</div>
<?php endif; ?>

<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="card card-stat text-white bg-warning shadow-sm"><div class="card-body">
    <div class="fs-2 fw-bold"><?= (int) $totalDipinjam ?></div><div>Sedang Saya Pinjam</div></div></div></div>
  <div class="col-md-3"><div class="card card-stat text-white bg-danger shadow-sm"><div class="card-body">
    <div class="fs-2 fw-bold"><?= (int) $totalTerlambat ?></div><div>Terlambat Dikembalikan</div></div></div></div>
  <div class="col-md-3"><div class="card card-stat text-white bg-success shadow-sm"><div class="card-body">
    <div class="fs-2 fw-bold"><?= (int) $totalTersedia ?></div><div>Buku Tersedia</div></div></div></div>
  <div class="col-md-3"><div class="card card-stat text-white bg-primary shadow-sm"><div class="card-body">
    <div class="fs-2 fw-bold"><?= (int) $totalBuku ?></div><div>Judul Buku</div></div></div></div>
</div>

<?php if ($nim): ?>
<div class="card shadow-sm mb-4">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <span class="fw-semibold">Peminjaman Saya Terbaru</span>
    <a href="<?= BASE_URL ?>/peminjaman-saya" class="small">Lihat semua</a>
  </div>
  <div class="table-responsive">
    <table class="table table-striped mb-0 align-middle">
      <thead class="table-dark">
        <tr><th>ID Pinjam</th><th>Judul Buku</th><th>Tgl Pinjam</th><th>Jatuh Tempo</th><th>Tgl Dikembalikan</th><th>Status</th></tr>
      </thead>
      <tbody>
      <?php if (empty($pinjamanTerbaru)): ?>
        <tr><td colspan="6" class="text-center text-muted py-3">Anda belum pernah meminjam buku.</td></tr>
      <?php else: foreach ($pinjamanTerbaru as $p): ?>
        <tr>
          <td><?= htmlspecialchars($p['kode_peminjaman']) ?></td>
          <td><?= htmlspecialchars($p['judul_buku']) ?></td>
          <td><?= htmlspecialchars($p['tanggal_pinjam']) ?></td>
          <td><?= htmlspecialchars($p['tanggal_kembali']) ?></td>
          <td><?= htmlspecialchars($p['tanggal_kembali_aktual'] ?? '-') ?></td>
          <td><?php include __DIR__ . '/../partials/status_peminjaman.php'; ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<div class="card shadow-sm">
  <div class="card-header bg-white fw-semibold">Buku yang Bisa Dipinjam</div>
  <div class="table-responsive">
    <table class="table table-striped mb-0 align-middle">
      <thead class="table-dark">
        <tr><th>ID Buku</th><th>Judul</th><th>Penulis</th><th>Kategori</th><th>Stok</th><th>Status</th><th>Aksi</th></tr>
      </thead>
      <tbody>
      <?php if (empty($bukuTersedia)): ?>
        <tr><td colspan="7" class="text-center text-muted py-3">Belum ada buku yang tersedia.</td></tr>
      <?php else: foreach ($bukuTersedia as $b): ?>
        <tr>
          <td><?= htmlspecialchars($b['kode_buku']) ?></td>
          <td><?= htmlspecialchars($b['judul']) ?></td>
          <td><?= htmlspecialchars($b['penulis']) ?></td>
          <td><?= htmlspecialchars($b['kategori'] ?? 'Umum') ?></td>
          <td><strong><?= (int) $b['stok'] ?></strong></td>
          <td><span class="badge bg-success">Tersedia</span></td>
          <td><?php $b = $b; include __DIR__ . '/../partials/tombol_pinjam.php'; ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php $kembaliKe = '/'; include __DIR__ . '/../partials/modal_pinjam.php'; ?>
