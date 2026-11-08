<div class="card shadow-sm" style="max-width:600px;">
  <div class="card-header bg-white fw-semibold">Detail Buku</div>
  <div class="card-body">
    <table class="table table-borderless mb-0">
      <tr><th style="width:180px;">Kode Buku</th><td><?= htmlspecialchars($buku['kode_buku']) ?></td></tr>
      <tr><th>Judul</th><td><?= htmlspecialchars($buku['judul']) ?></td></tr>
      <tr><th>Penulis</th><td><?= htmlspecialchars($buku['penulis']) ?></td></tr>
      <tr><th>Penerbit</th><td><?= htmlspecialchars($buku['penerbit']) ?></td></tr>
      <tr><th>Kategori</th><td><?= htmlspecialchars($buku['kategori'] ?? 'Umum') ?></td></tr>
      <tr><th>Tahun Terbit</th><td><?= htmlspecialchars($buku['tahun_terbit']) ?></td></tr>
      <tr><th>Stok</th><td><?= (int) $buku['stok'] ?></td></tr>
      <tr>
        <th>Status</th>
        <td><?= $buku['status'] === 'tersedia' ? '<span class="badge bg-success">Tersedia</span>' : '<span class="badge bg-danger">Habis</span>' ?></td>
      </tr>
    </table>
  </div>
  <div class="card-footer bg-white">
    <a href="<?= BASE_URL ?>/katalog" class="btn btn-secondary">Kembali</a>
    <?php if (!empty($pinjam)): ?><span class="ms-2"><?php $b = $buku; include __DIR__ . '/../partials/tombol_pinjam.php'; ?></span><?php endif; ?>
  </div>
</div>

<?php if (!empty($pinjam)) { $kembaliKe = '/katalog'; include __DIR__ . '/../partials/modal_pinjam.php'; } ?>
