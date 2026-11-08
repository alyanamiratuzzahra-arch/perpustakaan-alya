<?php

namespace App\Services;

use App\Config\Database;
use App\Repositories\PeminjamanRepository;
use App\Repositories\BukuRepository;
use App\Repositories\AnggotaRepository;
use App\Exceptions\BukuTidakTersediaException;
use App\Exceptions\BatasPeminjamanException;
use App\Exceptions\BukuTidakDitemukanException;
use App\Exceptions\TransaksiTidakDitemukanException;
use App\Helpers\Logger;
use PDO;

class PeminjamanService
{
    private const MAKS_PINJAM_PER_MAHASISWA = 3;

    private PeminjamanRepository $repo;
    private BukuRepository $bukuRepo;
    private AnggotaRepository $anggotaRepo;
    private PDO $db;

    public function __construct()
    {
        $this->repo = new PeminjamanRepository();
        $this->bukuRepo = new BukuRepository();
        $this->anggotaRepo = new AnggotaRepository();
        $this->db = Database::getInstance();
    }

    public function getAll(string $search = ''): array
    {
        return $this->repo->all(trim($search));
    }

    public function getAktif(string $search = ''): array
    {
        return $this->repo->aktif(trim($search));
    }

    public function getRiwayat(string $search = ''): array
    {
        return $this->repo->riwayat(trim($search));
    }

    public function getByNim(string $nim, string $search = '', ?string $status = null): array
    {
        return $this->repo->byNim($nim, trim($search), $status);
    }

    /** Pilihan lama pinjam (hari) yang boleh dipilih user. */
    public const LAMA_PINJAM = [7, 14];

    /** Info untuk tombol "Pinjam" milik user: buku apa yang sedang dipinjam dan sisa kuota (maks 3 buku aktif). */
    public function infoUser(?string $nim): array
    {
        if (!$nim) {
            return ['nim' => null, 'aktifIds' => [], 'sisaKuota' => 0];
        }
        $aktif = $this->repo->idBukuAktifByNim($nim);
        return [
            'nim'       => $nim,
            'aktifIds'  => $aktif,
            'sisaKuota' => max(0, self::MAKS_PINJAM_PER_MAHASISWA - count($aktif)),
        ];
    }

    /**
     * User meminjam SATU buku untuk dirinya sendiri.
     * NIM diambil dari akun yang login (bukan dari form), tanggal dihitung server, jadi tidak bisa dimanipulasi.
     */
    public function pinjamOlehUser(string $nim, int $idBuku, int $lamaHari): int
    {
        if (!in_array($lamaHari, self::LAMA_PINJAM, true)) {
            throw new \RuntimeException('Lama peminjaman tidak valid.');
        }
        if (in_array($idBuku, $this->repo->idBukuAktifByNim($nim), true)) {
            throw new \RuntimeException('Anda sedang meminjam buku ini dan belum mengembalikannya.');
        }

        $hariIni = date('Y-m-d');
        $ids = $this->pinjam([
            'nim'             => $nim,
            'id_buku'         => [$idBuku],
            'tanggal_pinjam'  => $hariIni,
            'tanggal_kembali' => date('Y-m-d', strtotime("+{$lamaHari} days")),
        ]);
        return $ids[0];
    }

    /** Batas lama pinjam (hari) untuk form milik user. */
public const MAKS_HARI_PINJAM = 14;

/**
 * Validasi form peminjaman milik USER.
 * NIM berasal dari akun login, tanggal pinjam selalu hari ini (dihitung server).
 */
public function validateUser(string $nim, array $input): array
{
    $errors     = [];
    $idBukuList = array_values(array_unique(array_filter(array_map('intval', (array) ($input['id_buku'] ?? [])))));
    $tglKembali = trim($input['tanggal_kembali'] ?? '');

    $aktifIds = $this->repo->idBukuAktifByNim($nim);
    $sisa     = max(0, self::MAKS_PINJAM_PER_MAHASISWA - count($aktifIds));

    if (empty($idBukuList)) {
        $errors['id_buku'] = 'Pilih minimal satu buku.';
    } elseif ($sisa <= 0) {
        $errors['id_buku'] = 'Kuota Anda penuh (maksimal 3 buku). Kembalikan buku terlebih dahulu.';
    } elseif (count($idBukuList) > $sisa) {
        $errors['id_buku'] = "Sisa kuota Anda {$sisa} buku (maksimal 3 buku sedang dipinjam).";
    } else {
        foreach ($idBukuList as $idBuku) {
            $buku = $this->bukuRepo->find($idBuku);
            if (!$buku) {
                $errors['id_buku'] = 'Salah satu buku yang dipilih tidak ditemukan.';
                break;
            }
            if (in_array($idBuku, $aktifIds, true)) {
                $errors['id_buku'] = "Buku \"{$buku['judul']}\" sedang Anda pinjam dan belum dikembalikan.";
                break;
            }
            if ((int) $buku['stok'] <= 0) {
                $errors['id_buku'] = "Stok buku \"{$buku['judul']}\" habis.";
                break;
            }
        }
    }

    $besok = strtotime('+1 day', strtotime(date('Y-m-d')));
    $batas = strtotime('+' . self::MAKS_HARI_PINJAM . ' days', strtotime(date('Y-m-d')));
    if ($tglKembali === '' || !$this->isTanggalValid($tglKembali)) {
        $errors['tanggal_kembali'] = 'Tanggal pengembalian tidak valid.';
    } elseif (strtotime($tglKembali) < $besok) {
        $errors['tanggal_kembali'] = 'Tanggal pengembalian minimal besok.';
    } elseif (strtotime($tglKembali) > $batas) {
        $errors['tanggal_kembali'] = 'Lama peminjaman maksimal ' . self::MAKS_HARI_PINJAM . ' hari.';
    }

    return $errors;
}

/** User meminjam beberapa buku sekaligus (1 kode peminjaman). Tanggal pinjam = hari ini. */
public function pinjamBanyakOlehUser(string $nim, array $idBukuList, string $tglKembali): array
{
    return $this->pinjam([
        'nim'             => $nim,
        'id_buku'         => $idBukuList,
        'tanggal_pinjam'  => date('Y-m-d'),
        'tanggal_kembali' => $tglKembali,
    ]);
}

    public function validate(array $input): array
    {
        $errors = [];
        $nim = trim($input['nim'] ?? '');
        $idBukuList = array_values(array_unique(array_filter(array_map('intval', (array) ($input['id_buku'] ?? [])))));
        $tglPinjam = trim($input['tanggal_pinjam'] ?? '');
        $tglKembali = trim($input['tanggal_kembali'] ?? '');

        if ($nim === '') {
            $errors['nim'] = 'NIM wajib diisi.';
        } elseif (!ctype_digit($nim)) {
            $errors['nim'] = 'NIM harus berupa angka.';
        } elseif (!$this->anggotaRepo->findByNim($nim)) {
            $errors['nim'] = 'NIM tidak terdaftar sebagai anggota perpustakaan.';
        }

        if (empty($idBukuList)) {
            $errors['id_buku'] = 'Pilih minimal satu buku.';
        } elseif (count($idBukuList) > self::MAKS_PINJAM_PER_MAHASISWA) {
            $errors['id_buku'] = 'Maksimal 3 buku dapat dipinjam dalam satu kali transaksi.';
        } else {
            foreach ($idBukuList as $idBuku) {
                if (!$this->bukuRepo->find($idBuku)) {
                    $errors['id_buku'] = 'Salah satu buku yang dipilih tidak ditemukan.';
                    break;
                }
            }
        }

        if ($tglPinjam === '' || !$this->isTanggalValid($tglPinjam)) {
            $errors['tanggal_pinjam'] = 'Tanggal peminjaman tidak valid.';
        }
        if ($tglKembali === '' || !$this->isTanggalValid($tglKembali)) {
            $errors['tanggal_kembali'] = 'Tanggal pengembalian tidak valid.';
        }
        if (!$this->hasError($errors, 'tanggal_pinjam') && !$this->hasError($errors, 'tanggal_kembali')
            && strtotime($tglKembali) < strtotime($tglPinjam)) {
            $errors['tanggal_kembali'] = 'Tanggal pengembalian tidak boleh sebelum tanggal peminjaman.';
        }

        return $errors;
    }

    private function hasError(array $errors, string $key): bool
    {
        return isset($errors[$key]);
    }

    private function isTanggalValid(string $tanggal): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $tanggal);
        return $d !== false && $d->format('Y-m-d') === $tanggal;
    }

    public function pinjam(array $input): array
    {
        $nim = trim($input['nim']);
        $idBukuList = array_values(array_unique(array_filter(array_map('intval', (array) ($input['id_buku'] ?? [])))));

        $anggota = $this->anggotaRepo->findByNim($nim);
        if (!$anggota) {
            throw new \RuntimeException('NIM tidak terdaftar sebagai anggota perpustakaan.');
        }

        if (empty($idBukuList)) {
            throw new \RuntimeException('Pilih minimal satu buku.');
        }

        // Total buku yang sedang dipinjam (lama) + buku baru yang mau dipinjam sekaligus, tidak boleh melebihi batas.
        $sudahAktif = $this->repo->countAktifByNim($nim);
        if ($sudahAktif + count($idBukuList) > self::MAKS_PINJAM_PER_MAHASISWA) {
            throw new BatasPeminjamanException();
        }

        foreach ($idBukuList as $idBuku) {
            $buku = $this->bukuRepo->find($idBuku);
            if (!$buku) {
                throw new BukuTidakDitemukanException();
            }
            if ((int) $buku['stok'] <= 0) {
                throw new BukuTidakTersediaException();
            }
        }

        $this->db->beginTransaction();
        try {
            $ids = [];
            $kodePeminjamanBersama = null;

            foreach ($idBukuList as $idBuku) {
                if (!$this->bukuRepo->decrementStok($idBuku)) {
                    throw new BukuTidakTersediaException();
                }

                // Buku pertama dalam transaksi ini dapat kode sementara lalu diganti berbasis ID auto-increment-nya
                // (supaya tidak bentrok saat ada transaksi bersamaan). Buku berikutnya dalam transaksi yang sama
                // langsung memakai kode yang sama, sehingga satu kali pinjam banyak buku tetap tercatat sebagai satu kode.
                $kodeUntukBarisIni = $kodePeminjamanBersama ?? $this->buatKodePeminjaman();

                $id = $this->repo->create([
                    'kode_peminjaman' => $kodeUntukBarisIni,
                    'nim' => $nim,
                    'id_buku' => $idBuku,
                    'tanggal_pinjam' => $input['tanggal_pinjam'],
                    'tanggal_kembali' => $input['tanggal_kembali'],
                ]);

                if ($kodePeminjamanBersama === null) {
                    $kodePeminjamanBersama = 'PJ' . str_pad((string) $id, 3, '0', STR_PAD_LEFT);
                    $this->repo->updateKodePeminjaman($id, $kodePeminjamanBersama);
                }

                $ids[] = $id;
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }

        Logger::info("Peminjaman berhasil: Kode={$kodePeminjamanBersama}, NIM={$nim}, IDBuku=" . implode(',', $idBukuList) . ", TglPinjam={$input['tanggal_pinjam']}, TglKembaliRencana={$input['tanggal_kembali']}");
        return $ids;
    }

    public function kembalikan(int $id): void
    {
        $peminjaman = $this->repo->find($id);
        if (!$peminjaman) {
            throw new TransaksiTidakDitemukanException();
        }
        if ($peminjaman['status'] === 'kembali') {
            throw new \RuntimeException('Transaksi ini sudah tercatat sebagai dikembalikan.');
        }

        $tanggalKembaliAktual = date('Y-m-d');
        $this->db->beginTransaction();
        try {
            if (!$this->repo->tandaiKembali($id, $tanggalKembaliAktual)) {
                throw new TransaksiTidakDitemukanException();
            }
            if (!$this->bukuRepo->incrementStok((int) $peminjaman['id_buku'])) {
                throw new BukuTidakDitemukanException();
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }

        Logger::info("Pengembalian berhasil: IDPeminjaman={$id}, Kode={$peminjaman['kode_peminjaman']}, IDBuku={$peminjaman['id_buku']}, TglKembaliAktual={$tanggalKembaliAktual}");
    }

    private function buatKodePeminjaman(): string
    {
        $last = $this->repo->getLastKode(); // misal "PJ005"
        $lastNumber = $last ? (int) substr($last, 2) : 0;
        $next = $lastNumber + 1;
        return 'PJ' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}
