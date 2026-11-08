<div class="card shadow-sm" style="max-width:600px;">
  <div class="card-header bg-white fw-semibold">Detail Buku</div>
  <div class="card-body">
    <table class="table table-borderless mb-0">
      <tr>
        <th style="width:180px;">ID Buku</th>
        <td><?= htmlspecialchars($buku['kode_buku']) ?></td>
      </tr>
      <tr>
        <th>Judul</th>
        <td><?= htmlspecialchars($buku['judul']) ?></td>
      </tr>
      <tr>
        <th>Penulis</th>
        <td><?= htmlspecialchars($buku['penulis']) ?></td>
      </tr>
      <tr>
        <th>Penerbit</th>
        <td><?= htmlspecialchars($buku['penerbit']) ?></td>
      </tr>
      <tr>
        <th>Kategori</th>
        <td><?= htmlspecialchars($buku['kategori'] ?? 'Umum') ?></td>
      </tr>
      <tr>
        <th>Tahun Terbit</th>
        <td><?= htmlspecialchars($buku['tahun_terbit']) ?></td>
      </tr>
      <tr>
        <th>Stok</th>
        <td><?= htmlspecialchars($buku['stok']) ?></td>
      </tr>
      <tr>
        <th>Status</th>
        <td>
          <?php if ($buku['status'] === 'tersedia'): ?>
            <span class="badge bg-success">Tersedia</span>
          <?php else: ?>
            <span class="badge bg-danger">Habis</span>
          <?php endif; ?>
        </td>
      </tr>
    </table>
  </div>
  <div class="card-footer bg-white">
    <a href="<?= BASE_URL ?>/buku" class="btn btn-secondary">Kembali</a>
  </div>
</div>
