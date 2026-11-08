<div class="page-head">
  <div>
    <h1>Profil Saya</h1>
    <p>Informasi akun yang sedang digunakan.</p>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header bg-white p-3"><div class="section-title">Data Akun</div></div>
      <div class="card-body p-4">
        <dl class="row mb-0">
          <dt class="col-sm-4">Nama</dt>
          <dd class="col-sm-8"><?= htmlspecialchars($user['nama']) ?></dd>

          <dt class="col-sm-4">Username</dt>
          <dd class="col-sm-8"><?= htmlspecialchars($user['username']) ?></dd>

          <dt class="col-sm-4">Email</dt>
          <dd class="col-sm-8"><?= htmlspecialchars($user['email']) ?></dd>

          <dt class="col-sm-4">NIM</dt>
          <dd class="col-sm-8"><?= $user['nim'] ? htmlspecialchars($user['nim']) : '<span class="text-muted">Belum terhubung</span>' ?></dd>

          <dt class="col-sm-4">Role</dt>
          <dd class="col-sm-8"><span class="badge <?= $user['role'] === 'admin' ? 'bg-danger' : 'bg-primary' ?>"><?= $user['role'] === 'admin' ? 'Admin' : 'User' ?></span></dd>

          <dt class="col-sm-4">Terdaftar sejak</dt>
          <dd class="col-sm-8"><?= htmlspecialchars(date('d M Y', strtotime($user['created_at']))) ?></dd>
        </dl>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <?php if ($user['role'] !== 'admin'): ?>
    <div class="card mb-3">
      <div class="card-body">
        <div class="section-title mb-3">Ringkasan Peminjaman</div>
        <div class="d-flex justify-content-between mb-2"><span>Sedang dipinjam</span><strong><?= (int) $totalDipinjam ?></strong></div>
        <div class="d-flex justify-content-between mb-3"><span>Sudah dikembalikan</span><strong><?= (int) $totalKembali ?></strong></div>
        <a href="<?= BASE_URL ?>/riwayat-saya" class="btn btn-light border w-100">Lihat Riwayat</a>
      </div>
    </div>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>/logout" method="POST">
      <button type="submit" class="btn btn-danger w-100"><i class="bi bi-box-arrow-right me-1"></i>Logout</button>
    </form>
  </div>
</div>
