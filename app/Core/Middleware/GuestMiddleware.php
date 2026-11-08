<?php

namespace App\Core\Middleware;

use App\Core\Auth;

/** Satpam 3: halaman login/register hanya untuk yang BELUM login. */
class GuestMiddleware
{
    public function handle(): void
    {
        if (Auth::check()) {
            header('Location: ' . BASE_URL . '/');
            exit;
        }
    }
}