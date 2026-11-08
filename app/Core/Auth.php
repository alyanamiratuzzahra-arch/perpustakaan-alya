<?php

namespace App\Core;

/**
 * Pembantu untuk urusan "siapa yang sedang login".
 * Semua data login disimpan di $_SESSION (di sisi server).
 */
class Auth
{
    public static function login(array $user): void
    {
        session_regenerate_id(true);               // ganti ID session -> cegah session fixation

        $_SESSION['logged_in']     = true;
        $_SESSION['user_id']       = (int) $user['id'];
        $_SESSION['user_nama']     = $user['nama'];
        $_SESSION['user_username'] = $user['username'];
        $_SESSION['user_role']     = $user['role'];
    }

    public static function check(): bool
    {
        return !empty($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    public static function id(): ?int
    {
        return self::check() ? (int) $_SESSION['user_id'] : null;
    }

    public static function nama(): string
    {
        return $_SESSION['user_nama'] ?? '';
    }

    public static function role(): ?string
    {
        return self::check() ? ($_SESSION['user_role'] ?? null) : null;
    }

    public static function isAdmin(): bool
    {
        return self::role() === 'admin';
    }

    public static function logout(): void
    {
        $_SESSION = [];                            // 1) kosongkan isi session

        if (ini_get('session.use_cookies')) {      // 2) hapus cookie session di browser
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }

        session_destroy();                         // 3) hancurkan session di server

        session_start();                           // session baru yang bersih (untuk pesan "Anda telah logout")
        session_regenerate_id(true);
    }
}
