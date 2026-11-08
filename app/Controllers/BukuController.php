<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\BukuService;
use App\Exceptions\BukuTidakDitemukanException;
use App\Helpers\Logger;

class BukuController extends Controller
{
    private BukuService $service;

    public function __construct()
    {
        $this->service = new BukuService();
    }

    public function index(): void
    {
        $search = trim($_GET['q'] ?? '');
        $this->view('buku/index', [
            'title' => 'Data Buku',
            'daftarBuku' => $this->service->getAll($search),
            'search' => $search,
        ]);
    }

    public function create(): void
    {
        $this->view('buku/create', ['title' => 'Tambah Buku']);
        $this->clearErrors();
    }

    public function store(): void
    {
        $input  = $_POST;
        $errors = $this->service->validate($input);

        if (!empty($errors)) {
            $this->setErrors($errors);
            $this->setOld($input);
            $this->redirect('/buku/create');
        }

        try {
            $this->service->create($input);
            $this->flash('success', 'Data buku berhasil ditambahkan.');
        } catch (\Throwable $e) {
            Logger::error('Gagal menambah buku: ' . $e->getMessage());
            $this->flash('danger', 'Data buku gagal disimpan.');
        }

        $this->clearOld();
        $this->clearErrors();
        $this->redirect('/buku');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        try {
            $buku = $this->service->getById($id);
        } catch (BukuTidakDitemukanException $e) {
            $this->flash('danger', $e->getMessage());
            $this->redirect('/buku');
            return;
        }

        $this->view('buku/edit', [
            'title' => 'Edit Buku',
            'buku'  => $buku,
        ]);
        $this->clearErrors();
    }

    public function update(): void
    {
        $id    = (int) ($_GET['id'] ?? 0);
        $input = $_POST;
        $errors = $this->service->validate($input, $id);

        if (!empty($errors)) {
            $this->setErrors($errors);
            $this->setOld($input);
            $this->redirect('/buku/edit?id=' . $id);
        }

        try {
            $this->service->update($id, $input);
            $this->flash('success', 'Data buku berhasil diubah.');
        } catch (BukuTidakDitemukanException $e) {
            $this->flash('danger', $e->getMessage());
        } catch (\Throwable $e) {
            Logger::error('Gagal mengubah buku: ' . $e->getMessage());
            $this->flash('danger', 'Data buku gagal disimpan.');
        }

        $this->clearOld();
        $this->clearErrors();
        $this->redirect('/buku');
    }

    public function destroy(): void
    {
        $id = (int) ($_POST['id'] ?? 0);

        try {
            $this->service->delete($id);
            $this->flash('success', 'Data buku berhasil dihapus.');
        } catch (BukuTidakDitemukanException $e) {
            $this->flash('danger', $e->getMessage());
        } catch (\RuntimeException $e) {
            // Pesan validasi yang sengaja dibuat ramah, aman ditampilkan ke user
            // (mis. "buku sedang dipinjam" / "masih ada riwayat peminjaman").
            $this->flash('danger', $e->getMessage());
        } catch (\Throwable $e) {
            // Error tak terduga (mis. dari database) — jangan tampilkan pesan mentahnya ke user.
            Logger::error('Gagal menghapus buku: ' . $e->getMessage());
            $this->flash('danger', 'Buku gagal dihapus. Buku ini kemungkinan masih terkait dengan data lain.');
        }

        $this->redirect('/buku');
    }

    public function detail(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        try {
            $buku = $this->service->getById($id);
        } catch (BukuTidakDitemukanException $e) {
            $this->flash('danger', $e->getMessage());
            $this->redirect('/buku');
            return;
        }

        $this->view('buku/detail', [
            'title' => 'Detail Buku',
            'buku'  => $buku,
        ]);
    }
}
