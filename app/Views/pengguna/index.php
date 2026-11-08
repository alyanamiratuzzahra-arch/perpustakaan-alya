<div class="page-head">
  <div><h1>Data Pengguna</h1><p>Daftar akun admin dan user yang terdaftar di sistem.</p></div>
</div>

<div class="search-box mb-3">
  <form method="GET" action="<?= BASE_URL ?>/pengguna" class="row g-2 align-items-center">
    <div class="col"><div class="input-group"><span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span><input name="q" value="<?= htmlspecialchars($search ?? '') ?>" class="form-control border-start-0" placeholder="Cari nama, username, email, NIM, atau role..."></div></div>
    <div class="col-auto"><button class="btn btn-primary px-4">Cari</button></div>
    <?php if (($search ?? '') !== ''): ?><div class="col-auto"><a href="<?= BASE_URL ?>/pengguna" class="btn btn-light border">Reset</a></div><?php endif; ?>
  </form>
</div>

<div class="card">
  <div class="card-header bg-white p-3"><div class="section-title">Daftar Pengguna</div><small class="text-muted"><?= count($daftarPengguna) ?> data ditemukan</small></div>
  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>No</th><th>Nama</th><th>Username</th><th>Email</th><th>NIM</th><th>Role</th><th>Terdaftar</th></tr></thead>
      <tbody>
      <?php if (!$daftarPengguna): ?>
        <tr><td colspan="7"><div class="empty-state"><i class="bi bi-people"></i>Tidak ada pengguna yang sesuai.</div></td></tr>
      <?php else: $no = 1; foreach ($daftarPengguna as $u): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($u['nama']) ?></td>
          <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><?= htmlspecialchars($u['nim'] ?? '-') ?></td>
          <td><?= $u['role'] === 'admin' ? '<span class="badge bg-primary">Admin</span>' : '<span class="badge bg-info text-white">User</span>' ?></td>
          <td><?= htmlspecialchars($u['created_at']) ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
