<?php

namespace App\Core\Middleware;

use App\Core\Auth;
use App\Helpers\Logger;

/** Satpam 2: hanya role ADMIN yang boleh lewat. (Selalu dipasang setelah 'auth'.) */
class AdminMiddleware
{
    public function handle(): void
    {
        if (!Auth::isAdmin()) {
            Logger::info('Akses ditolak (bukan admin): user_id=' . (Auth::id() ?? 0) . ', url=' . ($_SERVER['REQUEST_URI'] ?? ''));
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Anda tidak memiliki hak akses ke halaman tersebut.'];
            header('Location: ' . BASE_URL . '/');
            exit;
        }
    }
}
