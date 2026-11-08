<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Services\AuthService;
use App\Services\PeminjamanService;
use App\Services\BukuService;
use App\Exceptions\BukuTidakTersediaException;
use App\Exceptions\BatasPeminjamanException;
use App\Exceptions\BukuTidakDitemukanException;
use App\Helpers\Logger;

class PeminjamanUserController extends Controller
{
    public function index(): void
    {
        $this->tampil('dipinjam');
    }

    public function riwayat(): void
    {
        $this->tampil('kembali');
    }

    // POST /peminjaman-saya/pinjam -> user meminjam buku untuk dirinya sendiri
    public function pinjam(): void
    {
        if (Auth::isAdmin()) {
            $this->redirect('/peminjaman');
        }

        $user    = (new AuthService())->findUser((int) Auth::id());
        $nim     = $user['nim'] ?? null;
        $idBuku  = (int) ($_POST['id_buku'] ?? 0);
        $lama    = (int) ($_POST['lama_hari'] ?? 0);
        $kembali = $_POST['kembali'] ?? '/katalog';
        $kembali = in_array($kembali, ['/', '/katalog'], true) ? $kembali : '/katalog';

        if (!$nim) {
            $this->flash('warning', 'Akun Anda belum terhubung dengan NIM anggota. Hubungi admin perpustakaan.');
            $this->redirect($kembali);
        }

        try {
            (new PeminjamanService())->pinjamOlehUser($nim, $idBuku, $lama);
            $this->flash('success', 'Peminjaman berhasil! Lihat jatuh temponya di menu Peminjaman Saya.');
            $this->redirect('/peminjaman-saya');
        } catch (BukuTidakTersediaException|BatasPeminjamanException|BukuTidakDitemukanException|\RuntimeException $e) {
            $this->flash('danger', $e->getMessage());
        } catch (\PDOException $e) {
            Logger::error('Gagal pinjam (user): ' . $e->getMessage());
            $this->flash('danger', 'Peminjaman gagal diproses. Silakan coba lagi.');
        }
        $this->redirect($kembali);
    }

    // POST /peminjaman-saya/store -> form peminjaman user (boleh pilih sampai 3 buku sekaligus)
public function simpan(): void
{
    if (Auth::isAdmin()) {
        $this->redirect('/peminjaman');
    }

    $user = (new AuthService())->findUser((int) Auth::id());
    $nim  = $user['nim'] ?? null;

    if (!$nim) {
        $this->flash('warning', 'Akun Anda belum terhubung dengan NIM anggota. Hubungi admin perpustakaan.');
        $this->redirect('/peminjaman-saya');
    }

    // NIM TIDAK diambil dari form, hanya dari akun yang login
    $input = [
        'id_buku'         => (array) ($_POST['id_buku'] ?? []),
        'tanggal_kembali' => trim($_POST['tanggal_kembali'] ?? ''),
    ];

    $service = new PeminjamanService();
    $errors  = $service->validateUser($nim, $input);
    if ($errors) {
        $this->setErrors($errors);
        $this->setOld($input);
        $this->redirect('/peminjaman-saya');
    }

    try {
        $idBukuList = array_values(array_unique(array_filter(array_map('intval', $input['id_buku']))));
        $service->pinjamBanyakOlehUser($nim, $idBukuList, $input['tanggal_kembali']);
        $this->clearOld();
        $this->flash('success', 'Peminjaman berhasil disimpan. Jatuh tempo bisa dilihat pada tabel di bawah.');
    } catch (BukuTidakTersediaException|BatasPeminjamanException|BukuTidakDitemukanException|\RuntimeException $e) {
        $this->setOld($input);
        $this->flash('danger', $e->getMessage());
    } catch (\PDOException $e) {
        Logger::error('Gagal pinjam (form user): ' . $e->getMessage());
        $this->setOld($input);
        $this->flash('danger', 'Peminjaman gagal diproses. Silakan coba lagi.');
    }
    $this->clearErrors();
    $this->redirect('/peminjaman-saya');
}

        private function tampil(string $status): void
    {
        // Admin punya halaman peminjaman sendiri
        if (Auth::isAdmin()) {
            $this->redirect('/peminjaman');
        }

        $user    = (new AuthService())->findUser((int) Auth::id());
        $nim     = $user['nim'] ?? null;
        $search  = trim($_GET['q'] ?? '');
        $service = new PeminjamanService();

        $semua   = $nim ? $service->getByNim($nim) : [];
        $hariIni = date('Y-m-d');

        $this->view('peminjaman_saya/index', [
            'title'            => $status === 'kembali' ? 'Riwayat Peminjaman' : 'Peminjaman Saya',
            'mode'             => $status,
            'nim'              => $nim,
            'search'           => $search,
            'daftarPeminjaman' => $nim ? $service->getByNim($nim, $search, $status) : [],
            'totalDipinjam'    => count(array_filter($semua, fn ($p) => $p['status'] === 'dipinjam')),
            'totalTerlambat'   => count(array_filter($semua, fn ($p) => $p['status'] === 'dipinjam' && $p['tanggal_kembali'] < $hariIni)),
            'totalKembali'     => count(array_filter($semua, fn ($p) => $p['status'] === 'kembali')),
            'totalSemua'       => count($semua),
            'daftarBuku'       => $status === 'dipinjam' && $nim ? (new BukuService())->getAll() : [],
            'pinjam'           => $service->infoUser($nim),
        ]);
        $this->clearErrors();
        $this->clearOld();
    }
}
