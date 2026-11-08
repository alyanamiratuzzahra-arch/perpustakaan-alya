<div class="page-head">
  <div><h1>Pengembalian Buku</h1><p>Proses pengembalian pada halaman terpisah dari transaksi peminjaman.</p></div>
</div>

<div class="search-box mb-3">
  <form method="GET" action="<?= BASE_URL ?>/pengembalian" class="row g-2 align-items-center">
    <div class="col">
      <div class="input-group">
        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
        <input name="q" value="<?= htmlspecialchars($search ?? '') ?>" class="form-control border-start-0" placeholder="Cari ID pinjam, NIM, nama anggota, ID buku, atau judul..."></div></div>
    <div class="col-auto"><button class="btn btn-primary px-4">Cari</button></div>
    <?php if(($search ?? '')!==''): ?><div class="col-auto"><a href="<?= BASE_URL ?>/pengembalian" class="btn btn-light border">Reset</a></div><?php endif; ?>
  </form>
</div>

<div class="card mb-4">
  <div class="card-header bg-white p-3">
    <div class="section-title"><i class="bi bi-arrow-down-left-square me-2 text-success">
    </i>Buku Sedang Dipinjam</div>
    <small class="text-muted">Hanya transaksi berstatus dipinjam yang dapat dikembalikan.</small></div>
  <div class="table-responsive">
    <table class="table align-middle"><thead>
      <tr>
        <th>No</th>
        <th>ID Pinjam</th>
        <th>NIM</th>
        <th>Nama</th>
        <th>ID Buku</th>
        <th>Daftar Buku</th>
        <th>Tgl Pinjam</th>
        <th>Jatuh Tempo</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
  <?php
    $kelompokAktif = [];
    foreach ($daftarAktif as $p) {
        $kelompokAktif[$p['kode_peminjaman']][] = $p;
    }
  ?>
  <?php if(!$kelompokAktif): ?>
    <tr>
      <td colspan="9">
        <div class="empty-state"><i class="bi bi-check2-circle">
        </i>Tidak ada buku yang sedang dipinjam.</div>
      </td>
    </tr>
  <?php else:$no=1;foreach($kelompokAktif as $kode => $items): $jumlahBuku = count($items); ?>
    <?php foreach($items as $i => $p): ?>
    <tr>
      <?php if ($i === 0): ?>
      <td rowspan="<?= $jumlahBuku ?>"><?= $no++ ?></td>
      <td rowspan="<?= $jumlahBuku ?>"><strong><?= htmlspecialchars($kode) ?></strong></td>
      <td rowspan="<?= $jumlahBuku ?>"><?= htmlspecialchars($p['nim']) ?></td>
      <td rowspan="<?= $jumlahBuku ?>"><?= htmlspecialchars($p['nama_anggota']) ?></td>
      <?php endif; ?>
      <td><?= htmlspecialchars($p['kode_buku']) ?></td>
      <td><?= htmlspecialchars($p['judul_buku']) ?></td>
      <?php if ($i === 0): ?>
      <td rowspan="<?= $jumlahBuku ?>"><?= htmlspecialchars($p['tanggal_pinjam']) ?></td>
      <td rowspan="<?= $jumlahBuku ?>"><?= htmlspecialchars($p['tanggal_kembali']) ?></td>
      <?php endif; ?>
      <td>
        <form method="POST" action="<?= BASE_URL ?>/pengembalian/kembalikan" onsubmit="return confirm('Proses pengembalian buku ini?');">
          <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
          <button class="btn btn-sm btn-success"><i class="bi bi-check2 me-1"></i>Kembalikan</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php endforeach;endif; ?>
  </tbody>
</table>
</div>
</div>

<div class="card">
  <div class="card-header bg-white p-3">
    <div class="section-title">Riwayat Pengembalian</div>
    <small class="text-muted"><?= count($riwayatPengembalian) ?> data ditemukan</small>
  </div>
  <div class="table-responsive">
    <table class="table align-middle"><thead>
      <tr>
        <th>No</th>
        <th>ID Pinjam</th>
        <th>NIM</th>
        <th>Nama</th>
        <th>ID Buku</th>
        <th>Daftar Buku</th>
        <th>Tgl Pinjam</th>
        <th>Tgl Rencana Kembali</th>
        <th>Tgl Aktual</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
  <?php
    $kelompokRiwayat = [];
    foreach ($riwayatPengembalian as $p) {
        $kelompokRiwayat[$p['kode_peminjaman']][] = $p;
    }
  ?>
  <?php if(!$kelompokRiwayat): ?>
    <tr>
      <td colspan="10">
        <div class="empty-state"><i class="bi bi-clock-history"></i>Belum ada riwayat pengembalian.</div>
      </td>
    </tr>
  <?php else:$no=1;foreach($kelompokRiwayat as $kode => $items): $jumlahBuku = count($items); ?>
  <?php foreach($items as $i => $p): ?>
  <tr>
    <?php if ($i === 0): ?>
    <td rowspan="<?= $jumlahBuku ?>"><?= $no++ ?></td>
    <td rowspan="<?= $jumlahBuku ?>">
      <strong><?= htmlspecialchars($kode) ?></strong>
    </td>
    <td rowspan="<?= $jumlahBuku ?>"><?= htmlspecialchars($p['nim']) ?></td>
    <td rowspan="<?= $jumlahBuku ?>"><?= htmlspecialchars($p['nama_anggota']) ?></td>
    <?php endif; ?>
    <td><?= htmlspecialchars($p['kode_buku']) ?></td>
    <td><?= htmlspecialchars($p['judul_buku']) ?></td>
    <?php if ($i === 0): ?>
    <td rowspan="<?= $jumlahBuku ?>"><?= htmlspecialchars($p['tanggal_pinjam']) ?></td>
    <td rowspan="<?= $jumlahBuku ?>"><?= htmlspecialchars($p['tanggal_kembali']) ?></td>
    <?php endif; ?>
    <td><?= htmlspecialchars($p['tanggal_kembali_aktual'] ?? '-') ?></td>
    <td><span class="badge bg-success">Kembali</span>
  </td>
  </tr>
  <?php endforeach; ?>
  <?php endforeach;endif; ?>
  </tbody>
</table>
</div>
</div>
