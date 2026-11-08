<div class="page-head">
  <div>
    <h1>Selamat datang <span aria-hidden="true">🌸</span></h1>
    <p>Ringkasan aktivitas perpustakaan hari ini.</p>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="card card-stat text-white bg-primary shadow-sm">
      <div class="card-body">
        <div class="fs-2 fw-bold"><?= (int) $totalBuku ?></div>
        <div>Judul Buku</div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card card-stat text-white bg-success shadow-sm">
      <div class="card-body">
        <div class="fs-2 fw-bold"><?= (int) $totalStok ?></div>
        <div>Total Stok Buku</div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card card-stat text-white bg-warning shadow-sm">
      <div class="card-body">
        <div class="fs-2 fw-bold"><?= (int) $totalDipinjam ?></div>
        <div>Sedang Dipinjam</div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card card-stat text-white bg-danger shadow-sm">
      <div class="card-body">
        <div class="fs-2 fw-bold"><?= (int) $totalHabis ?></div>
        <div>Buku Stok Habis</div>
      </div>
    </div>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-header bg-white fw-semibold">Peminjaman Terbaru</div>
  <div class="table-responsive">
    <table class="table table-striped mb-0 align-middle">
      <thead class="table-dark">
        <tr>
          <th>Kode</th><th>NIM</th><th>Nama Anggota</th><th>Judul Buku</th>
          <th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($peminjamanTerbaru)): ?>
          <tr><td colspan="7" class="text-center text-muted py-3">Belum ada data peminjaman.</td></tr>
        <?php else: foreach ($peminjamanTerbaru as $p): ?>
          <tr>
            <td><?= htmlspecialchars($p['kode_peminjaman']) ?></td>
            <td><?= htmlspecialchars($p['nim']) ?></td>
            <td><?= htmlspecialchars($p['nama_anggota']) ?></td>
            <td><?= htmlspecialchars($p['judul_buku']) ?></td>
            <td><?= htmlspecialchars($p['tanggal_pinjam']) ?></td>
            <td><?= htmlspecialchars($p['tanggal_kembali']) ?></td>
            <td>
              <?php if ($p['status'] === 'dipinjam'): ?>
                <span class="badge bg-warning text-dark">Dipinjam</span>
              <?php else: ?>
                <span class="badge bg-success">Kembali</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
