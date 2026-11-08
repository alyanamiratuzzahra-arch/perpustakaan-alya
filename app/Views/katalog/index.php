<div class="page-head">
  <div>
    <h1>Katalog Buku</h1>
    <p>Cari buku berdasarkan judul, penulis, kategori, atau kode buku.</p>
  </div>
</div>

<div class="search-box mb-3">
  <form method="GET" action="<?= BASE_URL ?>/katalog" class="row g-2 align-items-center">
    <div class="col">
      <div class="input-group">
        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
        <input type="text" name="q" value="<?= htmlspecialchars($search ?? '') ?>" class="form-control border-start-0" placeholder="Cari judul, penulis, kategori, atau kode buku...">
      </div>
    </div>
    <div class="col-auto"><button class="btn btn-primary px-4">Cari</button></div>
    <?php if (($search ?? '') !== ''): ?><div class="col-auto"><a href="<?= BASE_URL ?>/katalog" class="btn btn-light border">Reset</a></div><?php endif; ?>
  </form>
</div>

<div class="card">
  <div class="card-header bg-white p-3">
    <div class="section-title">Daftar Buku</div>
    <small class="text-muted"><?= count($daftarBuku) ?> data ditemukan</small>
  </div>
  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr><th>No</th><th>Kode</th><th>Judul</th><th>Penulis</th><th>Kategori</th><th>Stok</th><th>Status</th><th>Detail</th><?php if (!empty($pinjam)): ?><th>Pinjam</th><?php endif; ?></tr>
      </thead>
      <tbody>
      <?php if (!$daftarBuku): ?>
        <tr><td colspan="9"><div class="empty-state"><i class="bi bi-book"></i>Tidak ada buku yang sesuai.</div></td></tr>
      <?php else: $no = 1; foreach ($daftarBuku as $buku): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><strong><?= htmlspecialchars($buku['kode_buku']) ?></strong></td>
          <td><?= htmlspecialchars($buku['judul']) ?></td>
          <td><?= htmlspecialchars($buku['penulis']) ?></td>
          <td><?= htmlspecialchars($buku['kategori'] ?? 'Umum') ?></td>
          <td><strong><?= (int) $buku['stok'] ?></strong></td>
          <td><?= $buku['status'] === 'tersedia' ? '<span class="badge bg-success">Tersedia</span>' : '<span class="badge bg-danger">Habis</span>' ?></td>
          <td><a href="<?= BASE_URL ?>/katalog/detail?id=<?= (int) $buku['id'] ?>" class="btn btn-sm btn-info text-white"><i class="bi bi-eye"></i></a></td>
          <?php if (!empty($pinjam)): ?><td><?php $b = $buku; include __DIR__ . '/../partials/tombol_pinjam.php'; ?></td><?php endif; ?>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if (!empty($pinjam)) { $kembaliKe = '/katalog'; include __DIR__ . '/../partials/modal_pinjam.php'; } ?>
