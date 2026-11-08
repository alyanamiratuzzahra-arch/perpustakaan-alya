<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Services\AuthService;
use App\Services\BukuService;
use App\Services\PeminjamanService;

class HomeController extends Controller
{
    public function index(): void
    {
        // User biasa melihat dashboard pengguna; admin tetap dashboard yang lama
        if (!Auth::isAdmin()) {
            $this->dashboardUser();
            return;
        }

        $bukuService       = new BukuService();
        $peminjamanService = new PeminjamanService();

        $daftarBuku       = $bukuService->getAll();
        $daftarPeminjaman = $peminjamanService->getAll();

        $totalBuku      = count($daftarBuku);
        $totalStok      = array_sum(array_column($daftarBuku, 'stok'));
        $totalDipinjam  = count(array_filter($daftarPeminjaman, fn ($p) => $p['status'] === 'dipinjam'));
        $totalHabis     = count(array_filter($daftarBuku, fn ($b) => (int) $b['stok'] === 0));

        $this->view('dashboard/index', [
            'title'              => 'Dashboard',
            'totalBuku'          => $totalBuku,
            'totalStok'          => $totalStok,
            'totalDipinjam'      => $totalDipinjam,
            'totalHabis'         => $totalHabis,
            'peminjamanTerbaru'  => array_slice($daftarPeminjaman, 0, 5),
        ]);
    }

    private function dashboardUser(): void
    {
        $daftarBuku = (new BukuService())->getAll();

        // Peminjaman milik user ini (dicari lewat NIM yang tersimpan di akunnya)
        $user    = (new AuthService())->findUser((int) Auth::id());
        $nim     = $user['nim'] ?? null;
        $saya    = $nim ? (new PeminjamanService())->getByNim($nim) : [];
        $hariIni = date('Y-m-d');

        $this->view('dashboard/user', [
            'title'          => 'Dashboard',
            'nim'            => $nim,
            'totalBuku'      => count($daftarBuku),
            'totalTersedia'  => count(array_filter($daftarBuku, fn ($b) => $b['status'] === 'tersedia')),
            'totalHabis'     => count(array_filter($daftarBuku, fn ($b) => (int) $b['stok'] === 0)),
            'bukuTersedia'   => array_slice(array_values(array_filter($daftarBuku, fn ($b) => $b['status'] === 'tersedia')), 0, 5),
            'totalDipinjam'  => count(array_filter($saya, fn ($p) => $p['status'] === 'dipinjam')),
            'totalTerlambat' => count(array_filter($saya, fn ($p) => $p['status'] === 'dipinjam' && $p['tanggal_kembali'] < $hariIni)),
            'pinjamanTerbaru'=> array_slice($saya, 0, 5),
            'pinjam'         => (new PeminjamanService())->infoUser($nim),
        ]);
    }
}
