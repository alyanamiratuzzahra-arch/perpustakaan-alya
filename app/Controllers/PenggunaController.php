<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AuthService;

/** Khusus ADMIN: melihat data pengguna. */
class PenggunaController extends Controller
{
    public function index(): void
    {
        $search  = trim($_GET['q'] ?? '');
        $service = new AuthService();

        $this->view('pengguna/index', [
            'title'         => 'Data Pengguna',
            'daftarPengguna'=> $service->getAllUsers($search),
            'search'        => $search,
        ]);
    }
}
