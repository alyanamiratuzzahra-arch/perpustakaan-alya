<div class="page-head">
  <div>
    <h1>Data Buku</h1>
    <p>Kelola koleksi buku, stok, dan status ketersediaan.</p>
  </div>
  <a href="<?= BASE_URL ?>/buku/create" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah Buku</a>
</div>

<div class="search-box mb-3">
  <form method="GET" action="<?= BASE_URL ?>/buku" class="row g-2 align-items-center">
    <div class="col">
      <div class="input-group">
        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
        <input type="text" name="q" value="<?= htmlspecialchars($search ?? '') ?>" class="form-control border-start-0" placeholder="Cari ID buku, judul, penulis, atau penerbit...">
      </div>
    </div>
    <div class="col-auto"><button class="btn btn-primary px-4">Cari</button></div>
    <?php if (($search ?? '') !== ''): ?><div class="col-auto"><a href="<?= BASE_URL ?>/buku" class="btn btn-light border">Reset</a></div><?php endif; ?>
  </form>
</div>

<div class="card">
  <div class="card-header bg-white p-3 d-flex justify-content-between align-items-center">
    <div>
      <div class="section-title">Daftar Buku</div>
      <small class="text-muted"><?= count($daftarBuku) ?> data ditemukan</small>
    </div>
  </div>
  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr>
          <th>No</th>
          <th>ID Buku</th>
          <th>Judul</th>
          <th>Penulis</th>
          <th>Penerbit</th>
          <th>Tahun</th>
          <th>Stok</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!$daftarBuku): ?>
        <tr>
          <td colspan="9">
            <div class="empty-state"><i class="bi bi-book"></i>Tidak ada data buku yang sesuai.</div>
          </td>
        </tr>
      <?php else: $no=1; foreach($daftarBuku as $buku): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><strong><?= htmlspecialchars($buku['kode_buku']) ?></strong></td>
          <td><?= htmlspecialchars($buku['judul']) ?></td>
          <td><?= htmlspecialchars($buku['penulis']) ?></td>
          <td><?= htmlspecialchars($buku['penerbit']) ?></td>
          <td><?= htmlspecialchars($buku['tahun_terbit']) ?></td>
          <td><strong><?= (int)$buku['stok'] ?></strong></td>
          <td><?= $buku['status']==='tersedia' ? '<span class="badge bg-success">Tersedia</span>' : '<span class="badge bg-danger">Habis</span>' ?></td>
          <td class="text-nowrap">
            <a href="<?= BASE_URL ?>/buku/detail?id=<?= (int)$buku['id'] ?>" class="btn btn-sm btn-info text-white"><i class="bi bi-eye"></i></a>
            <a href="<?= BASE_URL ?>/buku/edit?id=<?= (int)$buku['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
            <form action="<?= BASE_URL ?>/buku/delete" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus buku ini?');">
              <input type="hidden" name="id" value="<?= (int)$buku['id'] ?>"><button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
