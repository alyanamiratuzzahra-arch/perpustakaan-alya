<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\PeminjamanService;
use App\Exceptions\TransaksiTidakDitemukanException;
use App\Helpers\Logger;

class PengembalianController extends Controller
{
    private PeminjamanService $service;

    public function __construct()
    {
        $this->service = new PeminjamanService();
    }

    public function index(): void
    {
        $search = trim($_GET['q'] ?? '');
        $this->view('pengembalian/index', [
            'title' => 'Pengembalian Buku',
            'daftarAktif' => $this->service->getAktif($search),
            'riwayatPengembalian' => $this->service->getRiwayat($search),
            'search' => $search,
        ]);
    }

    public function kembalikan(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        try {
            $this->service->kembalikan($id);
            $this->flash('success', 'Buku berhasil dikembalikan dan stok telah ditambahkan kembali.');
        } catch (TransaksiTidakDitemukanException $e) {
            $this->flash('danger', $e->getMessage());
        } catch (\Throwable $e) {
            Logger::error('Gagal memproses pengembalian: ' . $e->getMessage());
            $this->flash('danger', $e->getMessage());
        }
        $this->redirect('/pengembalian');
    }
}
