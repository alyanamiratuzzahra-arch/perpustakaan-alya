<?php

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\BukuController;
use App\Controllers\KatalogController;
use App\Controllers\PenggunaController;
use App\Controllers\PeminjamanUserController;
use App\Controllers\PeminjamanController;
use App\Controllers\PengembalianController;
use App\Controllers\AnggotaController;
use App\Controllers\ProfilController;

/*
 * Format:  'url' => [Controller, 'method', ['satpam1', 'satpam2']]
 *   guest = hanya yang belum login
 *   auth  = harus sudah login
 *   admin = harus role admin (dipasang setelah 'auth')
 */
return [
    'GET' => [
        // --- Publik (belum login) ---
        '/login'    => [AuthController::class, 'loginForm',    ['guest']],
        '/register' => [AuthController::class, 'registerForm', ['guest']],

        // --- Semua yang sudah login (admin & user) ---
        '/'               => [HomeController::class,    'index',  ['auth']],
        '/katalog'        => [KatalogController::class, 'index',  ['auth']],
        '/katalog/detail' => [KatalogController::class, 'detail', ['auth']],

        '/peminjaman-saya' => [PeminjamanUserController::class, 'index',   ['auth']],
        '/riwayat-saya'    => [PeminjamanUserController::class, 'riwayat', ['auth']],
        '/profil'          => [ProfilController::class,         'index',   ['auth']],

        // --- Khusus ADMIN ---
        '/buku'          => [BukuController::class, 'index',  ['auth', 'admin']],
        '/buku/create'   => [BukuController::class, 'create', ['auth', 'admin']],
        '/buku/edit'     => [BukuController::class, 'edit',   ['auth', 'admin']],
        '/buku/detail'   => [BukuController::class, 'detail', ['auth', 'admin']],
        '/peminjaman'    => [PeminjamanController::class,   'index', ['auth', 'admin']],
        '/pengembalian'  => [PengembalianController::class, 'index', ['auth', 'admin']],
        '/anggota'        => [AnggotaController::class, 'index',  ['auth', 'admin']],
        '/anggota/create' => [AnggotaController::class, 'create', ['auth', 'admin']],
        '/anggota/edit'   => [AnggotaController::class, 'edit',   ['auth', 'admin']],
        '/pengguna'       => [PenggunaController::class, 'index', ['auth', 'admin']],
    ],
    'POST' => [
        '/login'    => [AuthController::class, 'login',    ['guest']],
        '/register' => [AuthController::class, 'register', ['guest']],
        '/logout'   => [AuthController::class, 'logout',   ['auth']],
        '/peminjaman-saya/pinjam' => [PeminjamanUserController::class, 'pinjam', ['auth']],
        '/peminjaman-saya/store'  => [PeminjamanUserController::class, 'simpan', ['auth']],

        '/buku/store'  => [BukuController::class, 'store',   ['auth', 'admin']],
        '/buku/update' => [BukuController::class, 'update',  ['auth', 'admin']],
        '/buku/delete' => [BukuController::class, 'destroy', ['auth', 'admin']],
        '/peminjaman/store'        => [PeminjamanController::class,   'store',     ['auth', 'admin']],
        '/pengembalian/kembalikan' => [PengembalianController::class, 'kembalikan',['auth', 'admin']],
        '/anggota/store'  => [AnggotaController::class, 'store',   ['auth', 'admin']],
        '/anggota/update' => [AnggotaController::class, 'update',  ['auth', 'admin']],
        '/anggota/delete' => [AnggotaController::class, 'destroy', ['auth', 'admin']],
    ],
];
