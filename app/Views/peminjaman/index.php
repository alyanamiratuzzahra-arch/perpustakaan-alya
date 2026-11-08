<div class="page-head">
  <div>
    <h1>Peminjaman Buku</h1>
    <p>Catat transaksi peminjaman buku oleh anggota perpustakaan.</p>
  </div>
</div>

<div class="card mb-4">
  <div class="card-header bg-white p-3"><div class="section-title"><i class="bi bi-plus-circle me-2 text-primary"></i>Form Peminjaman</div></div>
  <div class="card-body p-4">
    <form action="<?= BASE_URL ?>/peminjaman/store" method="POST" novalidate>
      <div class="row g-3">
        <div class="col-lg-3">
          <label class="form-label">NIM Anggota <span class="required">*</span></label>
          <div class="input-group">
            <input id="nim" type="text" name="nim" list="daftar-nim" class="form-control <?= $this->errors('nim')?'is-invalid':'' ?>" value="<?= htmlspecialchars($this->old('nim')) ?>" placeholder="Cari/tulis NIM">
            <button type="button" class="btn btn-primary" id="btnCariNim"><i class="bi bi-search"></i></button>
          </div>
          <datalist id="daftar-nim">
            <?php foreach($daftarAnggota as $a): ?><option value="<?= htmlspecialchars($a['nim']) ?>"><?= htmlspecialchars($a['nama']) ?></option><?php endforeach; ?>
          </datalist>
          <div id="namaAnggota" class="form-text">Pilih atau ketik NIM anggota.</div>
          <?php if($this->errors('nim')): ?><div class="text-danger small mt-1"><?= htmlspecialchars($this->errors('nim')) ?></div><?php endif; ?>
        </div>
        <div class="col-lg-4">
          <label class="form-label">Pilih Buku (maks. 3 sekaligus) <span class="required">*</span></label>
          <?php $oldBuku = array_map('strval', (array) $this->old('id_buku', [])); ?>
          <div id="daftarBukuCheckbox" class="border rounded p-2" style="max-height:170px;overflow-y:auto">
            <?php foreach($daftarBuku as $b): $habis = (int)$b['stok'] <= 0; ?>
            <div class="form-check">
              <input class="form-check-input buku-checkbox" type="checkbox" name="id_buku[]"
                value="<?= (int)$b['id'] ?>" id="buku<?= (int)$b['id'] ?>"
                data-habis="<?= $habis ? '1' : '0' ?>"
                <?= in_array((string)$b['id'], $oldBuku, true) ? 'checked' : '' ?>
                <?= $habis ? 'disabled' : '' ?>>
              <label class="form-check-label" for="buku<?= (int)$b['id'] ?>"><?= htmlspecialchars($b['kode_buku'].' - '.$b['judul']) ?> (Stok: <?= (int)$b['stok'] ?>)</label>
            </div>
            <?php endforeach; ?>
          </div>
          <?php if($this->errors('id_buku')): ?><div class="text-danger small mt-1"><?= htmlspecialchars($this->errors('id_buku')) ?></div><?php endif; ?>
        </div>
        <div class="col-lg-2">
          <label class="form-label">Tanggal Pinjam <span class="required">*</span></label>
          <input type="date" name="tanggal_pinjam" class="form-control <?= $this->errors('tanggal_pinjam')?'is-invalid':'' ?>" value="<?= htmlspecialchars($this->old('tanggal_pinjam', date('Y-m-d'))) ?>">
          <?php if($this->errors('tanggal_pinjam')): ?><div class="text-danger small mt-1"><?= htmlspecialchars($this->errors('tanggal_pinjam')) ?></div><?php endif; ?>
        </div>
        <div class="col-lg-2">
          <label class="form-label">Tanggal Kembali <span class="required">*</span></label>
          <input type="date" name="tanggal_kembali" class="form-control <?= $this->errors('tanggal_kembali')?'is-invalid':'' ?>" value="<?= htmlspecialchars($this->old('tanggal_kembali', date('Y-m-d', strtotime('+7 days')))) ?>">
          <?php if($this->errors('tanggal_kembali')): ?><div class="text-danger small mt-1"><?= htmlspecialchars($this->errors('tanggal_kembali')) ?></div><?php endif; ?>
        </div>
      </div>
      <div class="mt-3 p-3 rounded bg-light small text-muted"><i class="bi bi-info-circle me-1"></i>Aturan: stok harus &gt; 0 dan satu mahasiswa maksimal memiliki 3 buku yang sedang dipinjam.</div>
      <div class="text-end mt-3"><button class="btn btn-primary px-4"><i class="bi bi-save me-1"></i>Simpan Peminjaman</button></div>
    </form>
  </div>
</div>

<div class="search-box mb-3">
  <form method="GET" action="<?= BASE_URL ?>/peminjaman" class="row g-2 align-items-center">
    <div class="col"><div class="input-group"><span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span><input name="q" value="<?= htmlspecialchars($search ?? '') ?>" class="form-control border-start-0" placeholder="Cari ID pinjam, NIM, nama anggota, ID buku, atau judul..."></div></div>
    <div class="col-auto"><button class="btn btn-primary px-4">Cari</button></div>
    <?php if(($search ?? '')!==''): ?><div class="col-auto"><a href="<?= BASE_URL ?>/peminjaman" class="btn btn-light border">Reset</a></div><?php endif; ?>
  </form>
</div>

<div class="card">
  <div class="card-header bg-white p-3"><div class="section-title">Daftar Peminjaman</div><small class="text-muted"><?= count($daftarPeminjaman) ?> baris buku ditemukan</small></div>
  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>No</th><th>ID Pinjam</th><th>NIM</th><th>Nama</th><th>ID Buku</th><th>Daftar Buku</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Status</th></tr></thead>
      <tbody>
      <?php
        // Kelompokkan per kode_peminjaman: satu kali transaksi bisa berisi beberapa buku sekaligus (maks. 3),
        // supaya NIM/Nama/tanggal cukup tampil sekali dan hanya daftar bukunya yang diulang.
        $kelompokPeminjaman = [];
        foreach ($daftarPeminjaman as $p) {
            $kelompokPeminjaman[$p['kode_peminjaman']][] = $p;
        }
      ?>
      <?php if(!$kelompokPeminjaman): ?><tr><td colspan="9"><div class="empty-state"><i class="bi bi-arrow-up-right-square"></i>Belum ada data peminjaman.</div></td></tr>
      <?php else:$no=1;foreach($kelompokPeminjaman as $kode => $items): $jumlahBuku = count($items); ?>
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
          <td><?= $p['status']==='dipinjam'?'<span class="badge bg-warning text-dark">Dipinjam</span>':'<span class="badge bg-success">Kembali</span>' ?></td>
        </tr>
        <?php endforeach; ?>
      <?php endforeach;endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
const anggotaData = <?= json_encode(array_map(fn($a)=>['nim'=>$a['nim'],'nama'=>$a['nama']], $daftarAnggota), JSON_UNESCAPED_UNICODE) ?>;
const nimInput = document.getElementById('nim');
const namaBox = document.getElementById('namaAnggota');
function cariAnggota(){
  const nim=(nimInput.value||'').trim(); const a=anggotaData.find(x=>x.nim===nim);
  if(a){ namaBox.innerHTML='<span class="text-success"><i class="bi bi-check-circle me-1"></i>'+a.nama+'</span>'; }
  else if(nim){ namaBox.innerHTML='<span class="text-danger"><i class="bi bi-exclamation-circle me-1"></i>NIM tidak ditemukan.</span>'; }
  else { namaBox.textContent='Pilih atau ketik NIM anggota.'; }
}
nimInput?.addEventListener('input',cariAnggota); document.getElementById('btnCariNim')?.addEventListener('click',cariAnggota); cariAnggota();

// Batasi pemilihan checkbox buku maksimal 3 sekaligus dalam satu form (validasi akhir tetap di server).
const bukuCheckboxes = document.querySelectorAll('.buku-checkbox');
function updateBukuCheckboxState(){
  const jumlahDipilih = document.querySelectorAll('.buku-checkbox:checked').length;
  bukuCheckboxes.forEach(cb=>{
    if (cb.dataset.habis === '1') { cb.disabled = true; return; }
    cb.disabled = !cb.checked && jumlahDipilih >= 3;
  });
}
bukuCheckboxes.forEach(cb=>cb.addEventListener('change', updateBukuCheckboxState));
updateBukuCheckboxState();
</script>
