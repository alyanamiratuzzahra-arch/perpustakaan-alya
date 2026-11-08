<div class="page-head">
  <div><h1>Data Anggota</h1><p>Kelola NIM dan nama mahasiswa yang dapat melakukan peminjaman.</p></div>
  <a href="<?= BASE_URL ?>/anggota/create" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>Tambah Anggota</a>
</div>

<div class="search-box mb-3">
  <form method="GET" action="<?= BASE_URL ?>/anggota" class="row g-2 align-items-center">
    <div class="col"><div class="input-group"><span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span><input name="q" value="<?= htmlspecialchars($search ?? '') ?>" class="form-control border-start-0" placeholder="Cari NIM atau nama anggota..."></div></div>
    <div class="col-auto"><button class="btn btn-primary px-4">Cari</button></div>
    <?php if(($search ?? '')!==''): ?><div class="col-auto"><a href="<?= BASE_URL ?>/anggota" class="btn btn-light border">Reset</a></div><?php endif; ?>
  </form>
</div>

<div class="card">
  <div class="card-header bg-white p-3"><div class="section-title">Daftar Anggota</div><small class="text-muted"><?= count($daftarAnggota) ?> data ditemukan</small></div>
  <div class="table-responsive"><table class="table align-middle"><thead><tr><th>No</th><th>NIM</th><th>Nama Anggota</th><th>Sedang Dipinjam</th><th>Aksi</th></tr></thead><tbody>
  <?php if(!$daftarAnggota): ?><tr><td colspan="5"><div class="empty-state"><i class="bi bi-people"></i>Belum ada data anggota.</div></td></tr>
  <?php else:$no=1;foreach($daftarAnggota as $a): ?><tr><td><?= $no++ ?></td><td><strong><?= htmlspecialchars($a['nim']) ?></strong></td><td><?= htmlspecialchars($a['nama']) ?></td><td><span class="badge <?= (int)$a['jumlah_dipinjam']>=3?'bg-danger':'bg-primary' ?>"><?= (int)$a['jumlah_dipinjam'] ?> / 3</span></td><td class="text-nowrap"><a href="<?= BASE_URL ?>/anggota/edit?id=<?= (int)$a['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a><form action="<?= BASE_URL ?>/anggota/delete" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus anggota ini?');"><input type="hidden" name="id" value="<?= (int)$a['id'] ?>"><button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button></form></td></tr><?php endforeach;endif; ?>
  </tbody></table></div>
</div>
