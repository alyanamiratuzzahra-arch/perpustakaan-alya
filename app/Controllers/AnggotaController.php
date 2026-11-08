<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AnggotaService;
use App\Helpers\Logger;

class AnggotaController extends Controller
{
    private AnggotaService $service;

    public function __construct()
    {
        $this->service = new AnggotaService();
    }

    public function index(): void
    {
        $search = trim($_GET['q'] ?? '');
        $this->view('anggota/index', [
            'title' => 'Data Anggota',
            'daftarAnggota' => $this->service->getAll($search),
            'search' => $search,
        ]);
    }

    public function create(): void
    {
        $this->view('anggota/create', ['title' => 'Tambah Anggota']);
        $this->clearErrors();
    }

    public function store(): void
    {
        $input = $_POST;
        $errors = $this->service->validate($input);
        if ($errors) {
            $this->setErrors($errors);
            $this->setOld($input);
            $this->redirect('/anggota/create');
        }

        try {
            $this->service->create($input);
            $this->flash('success', 'Data anggota berhasil ditambahkan.');
        } catch (\Throwable $e) {
            Logger::error('Gagal menambah anggota: ' . $e->getMessage());
            $this->flash('danger', 'Data anggota gagal disimpan.');
        }
        $this->clearOld();
        $this->clearErrors();
        $this->redirect('/anggota');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        try {
            $anggota = $this->service->getById($id);
        } catch (\Throwable $e) {
            $this->flash('danger', $e->getMessage());
            $this->redirect('/anggota');
            return;
        }
        $this->view('anggota/edit', ['title' => 'Edit Anggota', 'anggota' => $anggota]);
        $this->clearErrors();
    }

    public function update(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $input = $_POST;
        $errors = $this->service->validate($input, $id);
        if ($errors) {
            $this->setErrors($errors);
            $this->setOld($input);
            $this->redirect('/anggota/edit?id=' . $id);
        }

        try {
            $this->service->update($id, $input);
            $this->flash('success', 'Data anggota berhasil diubah.');
        } catch (\Throwable $e) {
            Logger::error('Gagal mengubah anggota: ' . $e->getMessage());
            $this->flash('danger', $e->getMessage());
        }
        $this->clearOld();
        $this->clearErrors();
        $this->redirect('/anggota');
    }

    public function destroy(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        try {
            $this->service->delete($id);
            $this->flash('success', 'Data anggota berhasil dihapus.');
        } catch (\Throwable $e) {
            Logger::error('Gagal menghapus anggota: ' . $e->getMessage());
            $this->flash('danger', $e->getMessage());
        }
        $this->redirect('/anggota');
    }
}
