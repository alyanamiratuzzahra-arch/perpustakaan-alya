<div class="card shadow-sm">
  <div class="card-body p-4">
    <h1 class="auth-title">Daftar Akun</h1>
    <p class="text-muted mb-4">Buat akun pengguna perpustakaan.</p>

    <form action="<?= BASE_URL ?>/register" method="POST" novalidate>
      <?php
      $fields = [
          ['nama', 'Nama Lengkap', 'text', 'name'],
          ['username', 'Username', 'text', 'username'],
          ['nim', 'NIM (Nomor Induk Mahasiswa)', 'text', 'off'],
          ['email', 'Email', 'email', 'email'],
      ];
      foreach ($fields as [$name, $label, $type, $auto]): ?>
        <div class="mb-3">
          <label class="form-label"><?= $label ?></label>
          <input type="<?= $type ?>" name="<?= $name ?>" autocomplete="<?= $auto ?>"
                 class="form-control <?= $this->errors($name) ? 'is-invalid' : '' ?>"
                 value="<?= htmlspecialchars($this->old($name)) ?>">
          <?php if ($this->errors($name)): ?><div class="invalid-feedback"><?= htmlspecialchars($this->errors($name)) ?></div><?php endif; ?>
          <?php if ($name === 'nim'): ?><div class="form-text">NIM harus sudah didaftarkan admin sebagai anggota perpustakaan.</div><?php endif; ?>
        </div>
      <?php endforeach; ?>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" autocomplete="new-password"
               class="form-control <?= $this->errors('password') ? 'is-invalid' : '' ?>">
        <?php if ($this->errors('password')): ?><div class="invalid-feedback"><?= htmlspecialchars($this->errors('password')) ?></div><?php endif; ?>
      </div>
      <div class="mb-4">
        <label class="form-label">Ulangi Password</label>
        <input type="password" name="password_confirmation" autocomplete="new-password"
               class="form-control <?= $this->errors('password_confirmation') ? 'is-invalid' : '' ?>">
        <?php if ($this->errors('password_confirmation')): ?><div class="invalid-feedback"><?= htmlspecialchars($this->errors('password_confirmation')) ?></div><?php endif; ?>
      </div>
      <button type="submit" class="btn btn-primary w-100">Daftar</button>
    </form>
    <p class="text-center text-muted mt-3 mb-0">Sudah punya akun? <a href="<?= BASE_URL ?>/login">Login</a></p>
  </div>
</div>
