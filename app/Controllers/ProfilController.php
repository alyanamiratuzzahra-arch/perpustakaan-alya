<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Repositories\AnggotaRepository;
use App\Services\AuthService;
use App\Services\PeminjamanService;

/** Halaman profil: data akun milik user yang sedang login (admin & user). */
class ProfilController extends Controller
{
    public function index(): void
    {
        $user = (new AuthService())->findUser((int) Auth::id());

        if (!$user) {
            Auth::logout();
            $this->flash('danger', 'Akun tidak ditemukan. Silakan login ulang.');
            $this->redirect('/login');
        }

        $nim     = $user['nim'] ?? null;
        $anggota = $nim ? (new AnggotaRepository())->findByNim($nim) : null;
        $saya    = $nim ? (new PeminjamanService())->getByNim($nim) : [];

        $this->view('profil/index', [
            'title'          => 'Profil Saya',
            'user'           => $user,
            'anggota'        => $anggota,
            'totalDipinjam'  => count(array_filter($saya, fn ($p) => $p['status'] === 'dipinjam')),
            'totalKembali'   => count(array_filter($saya, fn ($p) => $p['status'] === 'kembali')),
        ]);
    }
}
