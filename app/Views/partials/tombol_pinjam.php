<?php
/**
 * Tombol "Pinjam" untuk satu buku. Butuh: $b (baris buku) dan $pinjam (hasil PeminjamanService::infoUser).
 * Hanya tampil untuk user (bukan admin); admin tidak punya $pinjam.
 */
if (empty($pinjam) && !is_array($pinjam ?? null)) { return; }

$sedangDipinjam = in_array((int) $b['id'], $pinjam['aktifIds'], true);

if (!$pinjam['nim']): ?>
  <button class="btn btn-sm btn-secondary" disabled title="Akun belum terhubung dengan NIM">Pinjam</button>
<?php elseif ($sedangDipinjam): ?>
  <span class="badge bg-info text-dark">Sedang Anda pinjam</span>
<?php elseif ((int) $b['stok'] <= 0): ?>
  <button class="btn btn-sm btn-secondary" disabled>Stok habis</button>
<?php elseif ($pinjam['sisaKuota'] <= 0): ?>
  <button class="btn btn-sm btn-secondary" disabled title="Batas maksimal 3 buku">Kuota penuh</button>
<?php else: ?>
  <button type="button" class="btn btn-sm btn-primary btn-pinjam"
          data-bs-toggle="modal" data-bs-target="#modalPinjam"
          data-id="<?= (int) $b['id'] ?>" data-judul="<?= htmlspecialchars($b['judul'], ENT_QUOTES) ?>">
    <i class="bi bi-bookmark-plus me-1"></i>Pinjam
  </button>
<?php endif; ?>
