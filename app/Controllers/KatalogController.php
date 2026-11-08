<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Services\AuthService;
use App\Services\PeminjamanService;
use App\Services\BukuService;
use App\Exceptions\BukuTidakDitemukanException;

/** Untuk USER: hanya MELIHAT & MENCARI buku (tanpa tambah/ubah/hapus). */
class KatalogController extends Controller
{
    private BukuService $service;

    public function __construct()
    {
        $this->service = new BukuService();
    }

    public function index(): void
    {
        $search = trim($_GET['q'] ?? '');

        $this->view('katalog/index', [
            'title'      => 'Data Buku',
            'daftarBuku' => $this->service->getAll($search),
            'search'     => $search,
            'pinjam'     => $this->infoPinjam(),
        ]);
    }

    public function detail(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        try {
            $buku = $this->service->getById($id);
        } catch (BukuTidakDitemukanException $e) {
            $this->flash('danger', $e->getMessage());
            $this->redirect('/katalog');
            return;
        }

        $this->view('katalog/detail', ['title' => 'Detail Buku', 'buku' => $buku, 'pinjam' => $this->infoPinjam()]);
    }

    /** Info tombol Pinjam untuk user yang login (admin tidak diberi tombol). */
    private function infoPinjam(): ?array
    {
        if (Auth::isAdmin()) {
            return null;
        }
        $user = (new AuthService())->findUser((int) Auth::id());
        return (new PeminjamanService())->infoUser($user['nim'] ?? null);
    }
}
