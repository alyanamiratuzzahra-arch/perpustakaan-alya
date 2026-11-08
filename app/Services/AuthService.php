<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Repositories\AnggotaRepository;
use App\Helpers\Logger;

class AuthService
{
    private UserRepository $repo;
    private AnggotaRepository $anggotaRepo;

    public function __construct()
    {
        $this->repo        = new UserRepository();
        $this->anggotaRepo = new AnggotaRepository();
    }

    /** Data user (tanpa password) berdasarkan ID; dipakai untuk mengambil NIM user yang sedang login. */
    public function findUser(int $id): ?array
    {
        return $this->repo->find($id);
    }

    public function validateLogin(array $input): array
    {
        $errors = [];
        if ($input['username'] === '') {
            $errors['username'] = 'Username wajib diisi.';
        }
        if ($input['password'] === '') {
            $errors['password'] = 'Password wajib diisi.';
        }
        return $errors;
    }

    /**
     * Mengembalikan data user jika username/email & password cocok, selain itu null.
     *
     * Normalnya password di database berupa HASH (dibuat password_hash) dan dicek dengan password_verify.
     * Kalau ternyata password di database MASIH TEKS BIASA (misalnya akun diisi manual lewat phpMyAdmin),
     * password_verify pasti gagal. Untuk kasus itu: cocokkan teks biasa dengan aman (hash_equals),
     * lalu LANGSUNG ubah menjadi hash di database, jadi hanya sekali terjadi.
     */
    public function attempt(string $username, string $password): ?array
    {
        $username = trim($username);
        $user     = $this->repo->findForLogin($username);

        if (!$user) {
            return null;
        }

        $tersimpan = trim((string) $user['password']);
        $info      = password_get_info($tersimpan);
        $cocok     = false;

        if ($info['algo'] !== null && $info['algo'] !== 0) {
            // Password di database sudah berupa hash -> cara yang benar
            $cocok = password_verify($password, $tersimpan);
        } elseif ($tersimpan !== '' && hash_equals($tersimpan, $password)) {
            // Masih plaintext -> terima sekali, lalu amankan jadi hash
            $cocok = true;
            $this->repo->updatePassword((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));
            Logger::info("Password akun {$user['username']} masih plaintext, otomatis di-hash.");
        }

        if (!$cocok) {
            return null;
        }

        // Perbarui hash kalau algoritma bawaan PHP sudah berganti
        if ($info['algo'] && password_needs_rehash($tersimpan, PASSWORD_DEFAULT)) {
            $this->repo->updatePassword((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));
        }
        return $user;
    }

    public function validateRegister(array $input): array
    {
        $errors = [];

        if ($input['nama'] === '' || mb_strlen($input['nama']) > 100) {
            $errors['nama'] = 'Nama wajib diisi (maksimal 100 karakter).';
        }

        if (!preg_match('/^[A-Za-z0-9_]{4,30}$/', $input['username'])) {
            $errors['username'] = 'Username 4-30 karakter, hanya huruf, angka, dan underscore.';
        } elseif ($this->repo->findByUsername($input['username'])) {
            $errors['username'] = 'Username sudah dipakai.';
        }

        $nim = $input['nim'] ?? '';
        if ($nim === '') {
            $errors['nim'] = 'NIM wajib diisi.';
        } elseif (!ctype_digit($nim) || strlen($nim) > 9) {
            $errors['nim'] = 'NIM harus berupa angka (maksimal 9 digit).';
        } elseif (!$this->anggotaRepo->findByNim($nim)) {
            $errors['nim'] = 'NIM belum terdaftar sebagai anggota. Hubungi admin perpustakaan.';
        } elseif ($this->repo->findByNim($nim)) {
            $errors['nim'] = 'NIM ini sudah terhubung dengan akun lain.';
        }

        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($input['email']) > 100) {
            $errors['email'] = 'Format email tidak valid.';
        } elseif ($this->repo->findByEmail($input['email'])) {
            $errors['email'] = 'Email sudah terdaftar.';
        }

        if (strlen($input['password']) < 6) {
            $errors['password'] = 'Password minimal 6 karakter.';
        }
        if ($input['password'] !== $input['password_confirmation']) {
            $errors['password_confirmation'] = 'Konfirmasi password tidak sama.';
        }

        return $errors;
    }

    /** Pendaftaran publik SELALU menjadi role 'user' (admin tidak bisa dibuat dari form). */
    public function register(array $input): int
    {
        $id = $this->repo->create([
            'nama'     => $input['nama'],
            'username' => $input['username'],
            'email'    => strtolower($input['email']),
            'nim'      => $input['nim'],
            'password' => password_hash($input['password'], PASSWORD_DEFAULT),
            'role'     => 'user',
        ]);

        Logger::info("User baru terdaftar: ID={$id}, username={$input['username']}");
        return $id;
    }

    public function getAllUsers(string $search = ''): array
    {
        return $this->repo->all(trim($search));
    }
}
