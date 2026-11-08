<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Services\AuthService;
use App\Helpers\Logger;

class AuthController extends Controller
{
    private AuthService $service;

    public function __construct()
    {
        $this->service = new AuthService();
    }

    // GET /login -> tampilkan form login
    public function loginForm(): void
    {
        $this->view('auth/login', ['title' => 'Login'], 'auth');
        $this->clearErrors();
        $this->clearOld();
    }

    // POST /login -> proses login
    public function login(): void
    {
        $input = [
            'username' => trim($_POST['username'] ?? ''),
            'password' => (string) ($_POST['password'] ?? ''),
        ];

        $errors = $this->service->validateLogin($input);
        if (!empty($errors)) {
            $this->setErrors($errors);
            $this->setOld(['username' => $input['username']]);   // password TIDAK disimpan ulang
            $this->redirect('/login');
        }

        $user = $this->service->attempt($input['username'], $input['password']);

        if ($user === null) {
            Logger::info('Login gagal untuk username: ' . $input['username']);
            $this->setErrors(['login' => 'Username atau password salah.']);
            $this->setOld(['username' => $input['username']]);
            $this->redirect('/login');
        }

        Auth::login($user);
        Logger::info("Login berhasil: {$user['username']} ({$user['role']})");

        $this->clearErrors();
        $this->clearOld();
        $this->flash('success', 'Selamat datang, ' . $user['nama'] . '!');
        $this->redirect('/');
    }

    // GET /register -> form daftar
    public function registerForm(): void
    {
        $this->view('auth/register', ['title' => 'Daftar Akun'], 'auth');
        $this->clearErrors();
        $this->clearOld();
    }

    // POST /register -> proses daftar
    public function register(): void
    {
        $input = [
            'nama'                  => trim($_POST['nama'] ?? ''),
            'username'              => trim($_POST['username'] ?? ''),
            'nim'                   => trim($_POST['nim'] ?? ''),
            'email'                 => trim($_POST['email'] ?? ''),
            'password'              => (string) ($_POST['password'] ?? ''),
            'password_confirmation' => (string) ($_POST['password_confirmation'] ?? ''),
        ];

        $old = [
            'nama' => $input['nama'], 'username' => $input['username'],
            'nim'  => $input['nim'],  'email'    => $input['email'],
        ];

        try {
            $errors = $this->service->validateRegister($input);
            if (!empty($errors)) {
                $this->setErrors($errors);
                $this->setOld($old);
                $this->redirect('/register');
            }

            $this->service->register($input);
            $this->clearErrors();
            $this->clearOld();
            $this->flash('success', 'Registrasi berhasil. Silakan login.');
            $this->redirect('/login');
        } catch (\PDOException $e) {
            Logger::error('Gagal register: ' . $e->getMessage());
            $this->setOld($old);
            $this->flash('danger', 'Registrasi gagal. Silakan coba lagi.');
            $this->redirect('/register');
        }
    }

    // POST /logout -> hancurkan session
    public function logout(): void
    {
        Logger::info('Logout: user_id=' . (Auth::id() ?? 0));
        Auth::logout();
        $this->flash('success', 'Anda telah logout.');
        $this->redirect('/login');
    }
}
