<div class="card shadow-sm">
  <div class="card-body p-4">
    <h1 class="auth-title">Masuk</h1>
    <p class="text-muted mb-4">Silakan login untuk melanjutkan.</p>

    <?php if ($this->errors('login')): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($this->errors('login')) ?></div>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>/login" method="POST" novalidate>
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" autocomplete="username" autofocus
               class="form-control <?= $this->errors('username') ? 'is-invalid' : '' ?>"
               value="<?= htmlspecialchars($this->old('username')) ?>">
        <?php if ($this->errors('username')): ?><div class="invalid-feedback"><?= htmlspecialchars($this->errors('username')) ?></div><?php endif; ?>
      </div>
      <div class="mb-4">
        <label class="form-label">Password</label>
        <input type="password" name="password" autocomplete="current-password"
               class="form-control <?= $this->errors('password') ? 'is-invalid' : '' ?>">
        <?php if ($this->errors('password')): ?><div class="invalid-feedback"><?= htmlspecialchars($this->errors('password')) ?></div><?php endif; ?>
      </div>
      <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
    <p class="text-center text-muted mt-3 mb-0">Belum punya akun? <a href="<?= BASE_URL ?>/register">Daftar di sini</a></p>
  </div>
</div>
