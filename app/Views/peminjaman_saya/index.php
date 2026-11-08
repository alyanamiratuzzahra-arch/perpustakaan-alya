<div class="page-head">
  <div>
    <?php $riwayat = ($mode ?? 'dipinjam') === 'kembali'; $rute = $riwayat ? '/riwayat-saya' : '/peminjaman-saya'; ?>
    <h1><?= $riwayat ? 'Riwayat Peminjaman' : 'Peminjaman Saya' ?></h1>
    <p><?= $riwayat ? 'Buku yang sudah Anda kembalikan, lengkap dengan tanggal pengembalian' : 'Buku yang sedang Anda pinjam beserta batas pengembaliannya' ?><?= $nim ? ' (NIM ' . htmlspecialchars($nim) . ')' : '' ?>.</p>
  </div>
</div>

<?php if (!$nim): ?>
  <div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-1"></i>
    Akun Anda belum terhubung dengan NIM anggota, jadi data peminjaman belum bisa ditampilkan.
    Hubungi admin perpustakaan untuk menghubungkan akun Anda.
  </div>
<?php else: ?>

<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="card card-stat text-white bg-warning shadow-sm"><div class="card-body">
    <div class="fs-2 fw-bold"><?= (int) $totalDipinjam ?></div><div>Sedang Dipinjam</div></div></div></div>
  <div class="col-md-3"><div class="card card-stat text-white bg-danger shadow-sm"><div class="card-body">
    <div class="fs-2 fw-bold"><?= (int) $totalTerlambat ?></div><div>Terlambat</div></div></div></div>
  <div class="col-md-3"><div class="card card-stat text-white bg-success shadow-sm"><div class="card-body">
    <div class="fs-2 fw-bold"><?= (int) $totalKembali ?></div><div>Sudah Dikembalikan</div></div></div></div>
  <div class="col-md-3"><div class="card card-stat text-white bg-primary shadow-sm"><div class="card-body">
    <div class="fs-2 fw-bold"><?= (int) $totalSemua ?></div><div>Total Buku Dipinjam</div></div></div></div>
</div>

<?php if (!$riwayat): ?>
<?php
  $aktifIds    = $pinjam['aktifIds'] ?? [];
  $sisaKuota   = (int) ($pinjam['sisaKuota'] ?? 0);
  $oldBuku     = array_map('strval', (array) $this->old('id_buku', []));
  $minKembali  = date('Y-m-d', strtotime('+1 day'));
  $maksKembali = date('Y-m-d', strtotime('+' . \App\Services\PeminjamanService::MAKS_HARI_PINJAM . ' days'));
?>
<div class="card mb-4">
  <div class="card-header bg-white p-3"><div class="section-title"><i class="bi bi-plus-circle me-2 text-primary"></i>Form Peminjaman</div></div>
  <div class="card-body p-4">
    <form action="<?= BASE_URL ?>/peminjaman-saya/store" method="POST" novalidate>
      <div class="row g-3">
        <div class="col-lg-6">
          <label class="form-label">Pilih Buku (sisa kuota Anda: <strong id="sisaKuota"><?= $sisaKuota ?></strong> dari maks. 3) <span class="required">*</span></label>
          <div id="daftarBukuCheckbox" class="border rounded p-2" style="max-height:170px;overflow-y:auto" data-sisa="<?= $sisaKuota ?>">
            <?php foreach ($daftarBuku as $b):
              $habis    = (int) $b['stok'] <= 0;
              $sedang   = in_array((int) $b['id'], $aktifIds, true);
              $nonaktif = $habis || $sedang || $sisaKuota <= 0; ?>
            <div class="form-check">
              <input class="form-check-input buku-checkbox" type="checkbox" name="id_buku[]"
                value="<?= (int) $b['id'] ?>" id="buku<?= (int) $b['id'] ?>"
                data-permanen="<?= ($habis || $sedang) ? '1' : '0' ?>"
                <?= in_array((string) $b['id'], $oldBuku, true) && !$nonaktif ? 'checked' : '' ?>
                <?= $nonaktif ? 'disabled' : '' ?>>
              <label class="form-check-label" for="buku<?= (int) $b['id'] ?>">
                <?= htmlspecialchars($b['kode_buku'] . ' - ' . $b['judul']) ?> (Stok: <?= (int) $b['stok'] ?>)
                <?php if ($sedang): ?><span class="badge bg-info text-dark">Sedang Anda pinjam</span><?php endif; ?>
              </label>
            </div>
            <?php endforeach; ?>
          </div>
          <?php if ($this->errors('id_buku')): ?><div class="text-danger small mt-1"><?= htmlspecialchars($this->errors('id_buku')) ?></div><?php endif; ?>
        </div>
        <div class="col-lg-3">
          <label class="form-label">Tanggal Pinjam</label>
          <input type="date" class="form-control" value="<?= date('Y-m-d') ?>" readonly>
          <div class="form-text">Otomatis hari ini.</div>
        </div>
        <div class="col-lg-3">
          <label class="form-label">Tanggal Kembali <span class="required">*</span></label>
          <input type="date" name="tanggal_kembali" min="<?= $minKembali ?>" max="<?= $maksKembali ?>"
                 class="form-control <?= $this->errors('tanggal_kembali') ? 'is-invalid' : '' ?>"
                 value="<?= htmlspecialchars($this->old('tanggal_kembali', date('Y-m-d', strtotime('+7 days')))) ?>">
          <?php if ($this->errors('tanggal_kembali')): ?><div class="text-danger small mt-1"><?= htmlspecialchars($this->errors('tanggal_kembali')) ?></div><?php endif; ?>
          <div class="form-text">Maksimal <?= \App\Services\PeminjamanService::MAKS_HARI_PINJAM ?> hari.</div>
        </div>
      </div>
      <div class="mt-3 p-3 rounded bg-light small text-muted"><i class="bi bi-info-circle me-1"></i>Aturan: stok harus &gt; 0 dan Anda maksimal memiliki 3 buku yang sedang dipinjam. Setelah buku dikembalikan, Anda bisa meminjam lagi. Peminjaman dicatat atas nama akun Anda (NIM <?= htmlspecialchars($nim) ?>).</div>
      <div class="text-end mt-3"><button class="btn btn-primary px-4" <?= $sisaKuota <= 0 ? 'disabled' : '' ?>><i class="bi bi-save me-1"></i>Simpan Peminjaman</button></div>
    </form>
  </div>
</div>
<script>
(function () {
  var box = document.getElementById('daftarBukuCheckbox');
  if (!box) return;
  var sisa = parseInt(box.dataset.sisa, 10) || 0;
  var cbs  = box.querySelectorAll('.buku-checkbox');
  function atur() {
    var dipilih = box.querySelectorAll('.buku-checkbox:checked').length;
    cbs.forEach(function (cb) {
      if (cb.dataset.permanen === '1' || sisa <= 0) return;
      if (!cb.checked) cb.disabled = dipilih >= sisa;
    });
  }
  cbs.forEach(function (cb) { cb.addEventListener('change', atur); });
  atur();
})();
</script>
<?php endif; ?>

<div class="search-box mb-3">
  <form method="GET" action="<?= BASE_URL ?><?= $rute ?>" class="row g-2 align-items-center">
    <div class="col"><div class="input-group"><span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span><input name="q" value="<?= htmlspecialchars($search ?? '') ?>" class="form-control border-start-0" placeholder="Cari ID pinjam, kode buku, judul, atau status..."></div></div>
    <div class="col-auto"><button class="btn btn-primary px-4">Cari</button></div>
    <?php if (($search ?? '') !== ''): ?><div class="col-auto"><a href="<?= BASE_URL ?><?= $rute ?>" class="btn btn-light border">Reset</a></div><?php endif; ?>
  </form>
</div>

<div class="card">
  <div class="card-header bg-white p-3"><div class="section-title"><?= $riwayat ? 'Riwayat Pengembalian' : 'Daftar Peminjaman Aktif' ?></div><small class="text-muted"><?= count($daftarPeminjaman) ?> baris buku ditemukan</small></div>
  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr>
        <th>No</th><th>ID Pinjam</th><th>ID Buku</th><th>Judul Buku</th>
        <th>Tgl Pinjam</th><th>Waktu Dicatat</th><th>Jatuh Tempo</th><th>Tgl Dikembalikan</th><th>Status</th>
      </tr></thead>
      <tbody>
      <?php
        // Kelompokkan per kode_peminjaman (satu transaksi bisa berisi beberapa buku), sama seperti halaman admin
        $kelompok = [];
        foreach ($daftarPeminjaman as $p) {
            $kelompok[$p['kode_peminjaman']][] = $p;
        }
      ?>
      <?php if (!$kelompok): ?>
        <tr><td colspan="9"><div class="empty-state"><i class="bi bi-journal-x"></i><?= $riwayat ? 'Belum ada riwayat pengembalian.' : 'Tidak ada buku yang sedang dipinjam.' ?></div></td></tr>
      <?php else: $no = 1; foreach ($kelompok as $kode => $items): $jumlah = count($items); ?>
        <?php foreach ($items as $i => $p): ?>
        <tr>
          <?php if ($i === 0): ?>
          <td rowspan="<?= $jumlah ?>"><?= $no++ ?></td>
          <td rowspan="<?= $jumlah ?>"><strong><?= htmlspecialchars($kode) ?></strong></td>
          <?php endif; ?>
          <td><?= htmlspecialchars($p['kode_buku']) ?></td>
          <td><?= htmlspecialchars($p['judul_buku']) ?></td>
          <?php if ($i === 0): ?>
          <td rowspan="<?= $jumlah ?>"><?= htmlspecialchars($p['tanggal_pinjam']) ?></td>
          <td rowspan="<?= $jumlah ?>"><?= htmlspecialchars(date('Y-m-d H:i', strtotime($p['created_at']))) ?></td>
          <td rowspan="<?= $jumlah ?>"><?= htmlspecialchars($p['tanggal_kembali']) ?></td>
          <?php endif; ?>
          <td><?= htmlspecialchars($p['tanggal_kembali_aktual'] ?? '-') ?></td>
          <td><?php include __DIR__ . '/../partials/status_peminjaman.php'; ?></td>
        </tr>
        <?php endforeach; ?>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php endif; ?>
