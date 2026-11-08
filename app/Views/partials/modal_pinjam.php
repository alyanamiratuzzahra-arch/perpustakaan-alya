<?php
/** Modal konfirmasi pinjam (dipakai bersama oleh semua halaman user). Butuh $kembaliKe ('/' atau '/katalog'). */
use App\Services\PeminjamanService;
?>
<div class="modal fade" id="modalPinjam" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" action="<?= BASE_URL ?>/peminjaman-saya/pinjam" method="POST">
      <div class="modal-header">
        <h5 class="modal-title">Pinjam Buku</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_buku" id="pinjamIdBuku">
        <input type="hidden" name="kembali" value="<?= htmlspecialchars($kembaliKe ?? '/katalog') ?>">
        <p class="mb-3">Anda akan meminjam: <strong id="pinjamJudul"></strong></p>
        <label class="form-label" for="pinjamLama">Lama peminjaman</label>
        <select name="lama_hari" id="pinjamLama" class="form-select">
          <?php foreach (PeminjamanService::LAMA_PINJAM as $hari): ?>
            <option value="<?= $hari ?>"><?= $hari ?> hari (jatuh tempo <?= date('d M Y', strtotime("+{$hari} days")) ?>)</option>
          <?php endforeach; ?>
        </select>
        <div class="form-text">Maksimal 3 buku dapat dipinjam pada saat yang sama.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Ya, Pinjam</button>
      </div>
    </form>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.btn-pinjam').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.getElementById('pinjamIdBuku').value = btn.dataset.id;
      document.getElementById('pinjamJudul').textContent = btn.dataset.judul;
    });
  });
});
</script>
