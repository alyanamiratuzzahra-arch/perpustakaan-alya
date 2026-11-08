<?php

namespace App\Core\Middleware;

use App\Core\Auth;

/** Satpam 1: hanya yang SUDAH login boleh lewat. */
class AuthMiddleware
{
    public function handle(): void
    {
        if (!Auth::check()) {
            $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Silakan login terlebih dahulu.'];
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // Supaya tombol "Back" setelah logout tidak menampilkan halaman lama dari cache browser
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
    }
}
