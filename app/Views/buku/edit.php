<div class="card shadow-sm" style="max-width:700px;">
  <div class="card-header bg-white fw-semibold">Edit Buku</div>
  <div class="card-body">
    <form action="<?= BASE_URL ?>/buku/update?id=<?= (int) $buku['id'] ?>" method="POST" novalidate>
      <div class="mb-3">
        <label class="form-label">ID Buku</label>
        <input type="text" name="kode_buku" class="form-control <?= $this->errors('kode_buku') ? 'is-invalid' : '' ?>"
               value="<?= htmlspecialchars($this->old('kode_buku', $buku['kode_buku'])) ?>">
        <?php if ($this->errors('kode_buku')): ?><div class="invalid-feedback"><?= htmlspecialchars($this->errors('kode_buku')) ?></div><?php endif; ?>
      </div>
      <div class="mb-3">
        <label class="form-label">Judul</label>
        <input type="text" name="judul" class="form-control <?= $this->errors('judul') ? 'is-invalid' : '' ?>"
               value="<?= htmlspecialchars($this->old('judul', $buku['judul'])) ?>">
        <?php if ($this->errors('judul')): ?><div class="invalid-feedback"><?= htmlspecialchars($this->errors('judul')) ?></div><?php endif; ?>
      </div>
      <div class="mb-3">
        <label class="form-label">Penulis</label>
        <input type="text" name="penulis" class="form-control <?= $this->errors('penulis') ? 'is-invalid' : '' ?>"
               value="<?= htmlspecialchars($this->old('penulis', $buku['penulis'])) ?>">
        <?php if ($this->errors('penulis')): ?><div class="invalid-feedback"><?= htmlspecialchars($this->errors('penulis')) ?></div><?php endif; ?>
      </div>
      <div class="mb-3">
        <label class="form-label">Penerbit</label>
        <input type="text" name="penerbit" class="form-control <?= $this->errors('penerbit') ? 'is-invalid' : '' ?>"
               value="<?= htmlspecialchars($this->old('penerbit', $buku['penerbit'])) ?>">
        <?php if ($this->errors('penerbit')): ?><div class="invalid-feedback"><?= htmlspecialchars($this->errors('penerbit')) ?></div><?php endif; ?>
      </div>
      <div class="mb-3">
        <label class="form-label">Kategori</label>
        <input type="text" name="kategori" class="form-control <?= $this->errors('kategori') ? 'is-invalid' : '' ?>"
               value="<?= htmlspecialchars($this->old('kategori', $buku['kategori'] ?? '')) ?>" placeholder="Contoh: Teknologi (kosong = Umum)">
        <?php if ($this->errors('kategori')): ?><div class="invalid-feedback"><?= htmlspecialchars($this->errors('kategori')) ?></div><?php endif; ?>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Tahun Terbit</label>
          <input type="number" name="tahun_terbit" class="form-control <?= $this->errors('tahun_terbit') ? 'is-invalid' : '' ?>"
                 value="<?= htmlspecialchars($this->old('tahun_terbit', $buku['tahun_terbit'])) ?>">
          <?php if ($this->errors('tahun_terbit')): ?><div class="invalid-feedback"><?= htmlspecialchars($this->errors('tahun_terbit')) ?></div><?php endif; ?>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Stok</label>
          <input type="number" name="stok" min="0" class="form-control <?= $this->errors('stok') ? 'is-invalid' : '' ?>"
                 value="<?= htmlspecialchars($this->old('stok', $buku['stok'])) ?>">
          <?php if ($this->errors('stok')): ?><div class="invalid-feedback"><?= htmlspecialchars($this->errors('stok')) ?></div><?php endif; ?>
        </div>
      </div>
      <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
      <a href="<?= BASE_URL ?>/buku" class="btn btn-secondary">Batal</a>
    </form>
  </div>
</div>
