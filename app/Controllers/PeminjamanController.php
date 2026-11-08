<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\PeminjamanService;
use App\Services\BukuService;
use App\Services\AnggotaService;
use App\Exceptions\BukuTidakTersediaException;
use App\Exceptions\BatasPeminjamanException;
use App\Exceptions\BukuTidakDitemukanException;
use App\Helpers\Logger;

class PeminjamanController extends Controller
{
    private PeminjamanService $service;

    public function __construct()
    {
        $this->service = new PeminjamanService();
    }

    public function index(): void
    {
        $search = trim($_GET['q'] ?? '');
        $bukuService = new BukuService();
        $anggotaService = new AnggotaService();

        $this->view('peminjaman/index', [
            'title' => 'Peminjaman Buku',
            'daftarPeminjaman' => $this->service->getAll($search),
            'daftarBuku' => $bukuService->getAll(),
            'daftarAnggota' => $anggotaService->getAll(),
            'search' => $search,
        ]);
        $this->clearErrors();
    }

    public function store(): void
    {
        $input = $_POST;
        $errors = $this->service->validate($input);
        if ($errors) {
            $this->setErrors($errors);
            $this->setOld($input);
            $this->redirect('/peminjaman');
        }

        $berhasil = false;
        try {
            $this->service->pinjam($input);
            $this->flash('success', 'Peminjaman buku berhasil disimpan.');
            $berhasil = true;
        } catch (BukuTidakTersediaException|BatasPeminjamanException|BukuTidakDitemukanException $e) {
            $this->flash('danger', $e->getMessage());
            $this->setOld($input);
        } catch (\Throwable $e) {
            Logger::error('Gagal memproses peminjaman: ' . $e->getMessage());
            $this->flash('danger', 'Peminjaman gagal diproses.');
            $this->setOld($input);
        }

        if ($berhasil) {
            $this->clearOld();
        }
        $this->clearErrors();
        $this->redirect('/peminjaman');
    }
}
