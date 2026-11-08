<?php
/** Badge status satu baris peminjaman. Butuh variabel $p (baris dari tabel peminjaman). */
if ($p['status'] === 'kembali'): ?>
  <span class="badge bg-success">Kembali</span>
<?php elseif ($p['tanggal_kembali'] < date('Y-m-d')): ?>
  <span class="badge bg-danger">Terlambat</span>
<?php else: ?>
  <span class="badge bg-warning text-dark">Dipinjam</span>
<?php endif; ?>
