<div class="page-head">
  <div>
    <h1>Tambah Anggota</h1>
    <p>Tambahkan NIM dan nama mahasiswa ke data anggota.</p>
  </div>
</div>

<div class="card" style="max-width:720px">
  <div class="card-body p-4">
    <form action="<?= BASE_URL ?>/anggota/store" method="POST" novalidate>
    <div class="mb-3">
      <label class="form-label">NIM <span class="required">*</span></label>
      <input type="text" name="nim" class="form-control 
        <?= $this->errors('nim')?'is-invalid':'' ?>" value="
        <?= htmlspecialchars($this->old('nim')) ?>" placeholder="Contoh: 20241001">
        <?php if($this->errors('nim')):?>
          <div class="invalid-feedback"><?= htmlspecialchars($this->errors('nim')) ?></div>
        <?php endif;?>
    </div>
    <div class="mb-4">
      <label class="form-label">Nama Anggota <span class="required">*</span></label>
        <input type="text" name="nama" class="form-control 
          <?= $this->errors('nama')?'is-invalid':'' ?>" value="
          <?= htmlspecialchars($this->old('nama')) ?>" placeholder="Nama lengkap mahasiswa">
        <?php if($this->errors('nama')):?>
          <div class="invalid-feedback">
            <?= htmlspecialchars($this->errors('nama')) ?>
          </div>
        <?php endif;?>
    </div>

    <button class="btn btn-primary">
      <i class="bi bi-save me-1"></i>Simpan
    </button> 
      <a href="<?= BASE_URL ?>/anggota" class="btn btn-light border">Batal</a>
    </form>
  </div>
</div>
