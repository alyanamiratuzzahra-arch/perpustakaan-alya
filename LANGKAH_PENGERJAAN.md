# Panduan Langkah Demi Langkah: CRUD + Authentication + Multi-User

Project: **Sistem Perpustakaan Kampus** (PHP Native, MVC + Repository + Service)
Mata kuliah: Workshop SI Web Server (TIF330805) — Studi Kasus *CRUD Authentication Multi-User*

> **Aturan yang kupegang:** tampilan lama **tidak diubah**. Semua halaman lama (Dashboard admin, Data Buku, Peminjaman, Pengembalian, Anggota) tetap sama persis. Yang ada hanyalah **tambahan**: halaman login/register, menu Logout, halaman untuk User, dan halaman Data Pengguna.

---

## 0. Cerita besarnya dulu (versi bayi 🍼)

Bayangkan perpustakaan itu **sebuah gedung**.

- **Dulu:** pintu gedung tidak dikunci. Siapa saja boleh masuk, boleh ubah dan buang buku. 😱
- **Sekarang:** kita pasang **satpam** di pintu.
  1. Orang harus **tunjukkan kartu** (username + password) → ini namanya **Authentication / Login**.
  2. Satpam **mengingat** siapa yang sudah masuk → ini namanya **Session**.
  3. Di dalam gedung ada ruangan **khusus petugas**. Satpam hanya mengizinkan **Admin** masuk ke sana → ini namanya **Authorization / Role**.
  4. Saat keluar, satpam **membuang catatan** orang itu → ini namanya **Logout (hancurkan session)**.

Ada dua jenis orang:

| Siapa | Boleh ngapain |
|---|---|
| 👩‍💼 **Admin** (petugas) | Semuanya: lihat dashboard, tambah/ubah/hapus buku, ubah stok, lihat data pengguna, peminjaman, pengembalian, anggota |
| 🎓 **User** (mahasiswa) | **Hanya melihat**: dashboard, daftar buku, detail buku, cari buku, lihat stok & status (tersedia/habis) |

Password **tidak boleh disimpan apa adanya**. Kalau password-nya `admin123`, di database disimpan seperti `$2y$10$Xk3...` (acak, tidak bisa dibalik). Itu namanya **hash**.

### Peta file: apa yang baru?

```text
perpustakaan/
├── database/
│   ├── auth.sql                     ← BARU  (tabel users + kolom kategori)
│   └── seed_users.php               ← BARU  (bikin akun admin & user awal)
├── app/
│   ├── Core/
│   │   ├── Auth.php                 ← BARU  (catatan "siapa yang login")
│   │   ├── Router.php               ← DIUBAH (bisa pasang satpam di route)
│   │   ├── Controller.php           ← DIUBAH (boleh pilih layout, 1 baris)
│   │   └── Middleware/              ← BARU  (3 satpam)
│   │       ├── AuthMiddleware.php
│   │       ├── AdminMiddleware.php
│   │       └── GuestMiddleware.php
│   ├── Repositories/UserRepository.php   ← BARU
│   ├── Services/AuthService.php          ← BARU
│   ├── Controllers/
│   │   ├── AuthController.php            ← BARU (login, register, logout)
│   │   ├── KatalogController.php         ← BARU (halaman buku untuk User)
│   │   ├── PenggunaController.php        ← BARU (data pengguna, Admin)
│   │   └── HomeController.php            ← DIUBAH (dashboard beda per role)
│   └── Views/
│       ├── layouts/auth.php              ← BARU
│       ├── auth/login.php, register.php  ← BARU
│       ├── katalog/index.php, detail.php ← BARU
│       ├── pengguna/index.php            ← BARU
│       ├── dashboard/user.php            ← BARU
│       ├── partials/sidebar.php          ← DIUBAH (menu per role + Logout)
│       ├── layouts/main.php              ← DIUBAH (2 baris kecil)
│       └── buku/create.php, edit.php, detail.php ← DIUBAH (+ kolom Kategori)
├── routes/web.php                        ← DIUBAH (pasang satpam)
├── public/index.php                      ← DIUBAH (pengaman cookie session)
└── public/assets/css/app.css             ← DITAMBAH di paling bawah
```

---

## Langkah 1 — Siapkan "buku daftar tamu" di database

**Bahasa bayi:** Satpam butuh buku catatan berisi daftar orang yang boleh masuk. Buku catatan itu = tabel `users`. Di tiap baris ada nama, username, email, password (yang sudah diacak), dan `role` (admin atau user).

Selain itu soal minta pencarian **berdasarkan kategori**, sedangkan tabel `buku` belum punya kolom kategori. Jadi kita tambahkan kolom `kategori` (isi bawaan: `Umum`).

**Yang dikerjakan:**
1. Buka phpMyAdmin → pilih database `perpustakaan` → tab **SQL**.
2. Copy-paste isi file `database/auth.sql` → klik **Kirim/Go**.

```sql
// database/auth.sql
-- =====================================================
-- LANGKAH 1: Tambahan database untuk Authentication
-- Jalankan SATU KALI di phpMyAdmin (pilih database "perpustakaan" -> tab SQL)
-- =====================================================

-- 1) Tabel users: tempat menyimpan akun admin & user
CREATE TABLE IF NOT EXISTS users (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nama       VARCHAR(100) NOT NULL,
    username   VARCHAR(30)  NOT NULL UNIQUE,
    email      VARCHAR(100) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,                 -- isinya HASH, bukan password asli
    role       ENUM('admin','user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2) Kolom kategori pada tabel buku (dibutuhkan untuk pencarian berdasarkan kategori)
--    Jalankan baris ini HANYA SEKALI. Jika muncul "Duplicate column name 'kategori'", berarti sudah pernah.
ALTER TABLE buku
    ADD COLUMN kategori VARCHAR(50) NOT NULL DEFAULT 'Umum' AFTER penerbit;
```


> ⚠️ Perintah `ALTER TABLE` cukup dijalankan **sekali**. Kalau muncul *Duplicate column name 'kategori'*, artinya sudah pernah — aman, abaikan.

**Hasil yang seharusnya:** di phpMyAdmin muncul tabel baru **`users`** (kosong), dan tabel **`buku`** punya kolom baru **`kategori`** (semua buku lama berisi `Umum`).

### Langkah 1b — Bikin akun admin & user awal

**Bahasa bayi:** Tabel `users` masih kosong. Kita butuh 1 admin dan 1 user supaya bisa mencoba. Password harus diacak dulu pakai `password_hash()`, jadi tidak bisa kita ketik langsung di SQL. Kita pakai skrip kecil.

```php
// database/seed_users.php
<?php
/**
 * Membuat 2 akun awal (admin & user) dengan password yang DI-HASH.
 * Cara menjalankan (dari folder project):
 *   php database/seed_users.php
 * Di XAMPP Windows:
 *   C:\xampp\php\php.exe database\seed_users.php
 */
$config = require __DIR__ . '/../config/database.php';
$dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
$db  = new PDO($dsn, $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$akun = [
    ['Administrator Perpustakaan', 'admin',     'admin@perpustakaan.test',     'admin123', 'admin'],
    ['Mahasiswa Contoh',           'mahasiswa', 'mahasiswa@perpustakaan.test', 'user123',  'user'],
];

$cek    = $db->prepare('SELECT COUNT(*) FROM users WHERE username = :u');
$insert = $db->prepare('INSERT INTO users (nama, username, email, password, role) VALUES (:n, :u, :e, :p, :r)');

foreach ($akun as [$nama, $username, $email, $passwordAsli, $role]) {
    $cek->execute(['u' => $username]);
    if ((int) $cek->fetchColumn() > 0) {
        echo "Lewati  : {$username} sudah ada\n";
        continue;
    }
    $insert->execute([
        'n' => $nama, 'u' => $username, 'e' => $email,
        'p' => password_hash($passwordAsli, PASSWORD_DEFAULT),   // <-- di sinilah password diacak (hash)
        'r' => $role,
    ]);
    echo "Dibuat  : {$username} ({$role})\n";
}
echo "Selesai.\n";
```


**Cara menjalankan** (buka Terminal/CMD di folder project):

```bash
php database/seed_users.php
# XAMPP Windows (kalau 'php' tidak dikenali):
C:\xampp\php\php.exe database\seed_users.php
```

**Hasil yang seharusnya** di terminal:

```text
Dibuat  : admin (admin)
Dibuat  : mahasiswa (user)
Selesai.
```

Kalau dijalankan lagi: `Lewati  : admin sudah ada` (tidak dobel).

| Akun | Username | Password | Role |
|---|---|---|---|
| Admin | `admin` | `admin123` | admin |
| User | `mahasiswa` | `user123` | user |

> Cara lain kalau tidak bisa menjalankan PHP lewat terminal: daftar akun lewat halaman `/register` (Langkah 7), lalu di phpMyAdmin jalankan  
> `UPDATE users SET role='admin' WHERE username='namakamu';`

---

## Langkah 2 — Nyalakan "ingatan" satpam (Session) dengan aman

**Bahasa bayi:** Session itu seperti **gelang tamu**. Begitu kamu login, kamu dikasih gelang. Setiap pindah halaman, satpam cukup lihat gelangmu — tidak perlu minta password lagi. `session_start()` sudah ada di project, kita cuma tambah pengaman:

- `httponly` → gelang tidak bisa dibaca oleh JavaScript jahat.
- `use_strict_mode` → server menolak nomor gelang palsu buatan orang lain.

```diff
--- public/index.php (lama)
+++ public/index.php (baru)
@@ -1,3 +1,7 @@
 <?php
+// Pengaman cookie session (Langkah 2)
+ini_set('session.use_strict_mode', '1');
+ini_set('session.cookie_httponly', '1');
+session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
 session_start();
 
```
> Baris berawalan `+` = **tambahkan**, `-` = **hapus/ganti**, tanpa tanda = hanya penunjuk letak.


**Hasil yang seharusnya:** tampilan tidak berubah sama sekali. Ini hanya pengaman di belakang layar.

---

## Langkah 3 — Buat "buku catatan siapa yang login" (`Auth`)

**Bahasa bayi:** Kita bikin 1 kelas kecil bernama `Auth` supaya tidak berulang-ulang menulis `$_SESSION[...]`. Isinya:

| Fungsi | Artinya |
|---|---|
| `Auth::login($user)` | catat "orang ini sudah masuk" + **ganti nomor gelang** (cegah pencurian session) |
| `Auth::check()` | sudah login atau belum? |
| `Auth::isAdmin()` | dia admin atau bukan? |
| `Auth::nama()` | namanya siapa? (buat sapaan di dashboard) |
| `Auth::logout()` | kosongkan catatan, hapus cookie, **hancurkan session** |

```php
// app/Core/Auth.php
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
```


**Hasil yang seharusnya:** belum terlihat di browser — ini "alat" yang dipakai langkah berikutnya.

---

## Langkah 4 — Pasang 3 satpam (Middleware)

**Bahasa bayi:** Middleware = satpam yang berdiri **sebelum** pintu ruangan (Controller). Persis seperti contoh di materi BKPM Acara 6 (`AuthMiddleware`). Kita punya 3 satpam:

1. **`auth`** — "Sudah login? Kalau belum, balik ke halaman login."
2. **`admin`** — "Kamu admin? Kalau bukan, saya antar balik ke dashboard dengan pesan *tidak punya hak akses*."
3. **`guest`** — "Kamu belum login? Baru boleh lihat halaman login. Kalau sudah login, ngapain login lagi?"

```php
// app/Core/Middleware/AuthMiddleware.php
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
```

```php
// app/Core/Middleware/AdminMiddleware.php
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
```

```php
// app/Core/Middleware/GuestMiddleware.php
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
```


> `Cache-Control: no-store` di `AuthMiddleware` penting: setelah logout, tombol **Back** di browser tidak akan menampilkan halaman lama dari cache.

### Langkah 4b — Ajari Router memakai satpam

**Bahasa bayi:** Router itu "resepsionis" yang mengantar tamu ke ruangan yang benar. Dulu daftarnya cuma `[Controller, 'method']`. Sekarang boleh ada pilihan ketiga: daftar satpam yang harus dilewati. Kalau tidak ditulis, tidak ada satpam (jadi kode lama tetap jalan).

```php
// app/Core/Router.php
<?php

namespace App\Core;

use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\AdminMiddleware;
use App\Core\Middleware\GuestMiddleware;

class Router
{
    private array $routes;

    /** Nama pendek middleware -> class aslinya */
    private const MIDDLEWARE = [
        'auth'  => AuthMiddleware::class,
        'admin' => AdminMiddleware::class,
        'guest' => GuestMiddleware::class,
    ];

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function dispatch(): void
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if (BASE_URL !== '' && str_starts_with($uri, BASE_URL)) {
            $uri = substr($uri, strlen(BASE_URL));
        }

        $uri = '/' . ltrim($uri, '/');
        if ($uri !== '/') {
            $uri = rtrim($uri, '/');
        }

        $method = $_SERVER['REQUEST_METHOD'];

        if (isset($this->routes[$method][$uri])) {
            $route           = $this->routes[$method][$uri];
            $controllerClass = $route[0];
            $action          = $route[1];
            $middlewares     = $route[2] ?? [];      // daftar satpam (boleh kosong)

            // Jalankan satpam satu per satu SEBELUM controller dipanggil
            foreach ($middlewares as $nama) {
                if (!isset(self::MIDDLEWARE[$nama])) {
                    throw new \RuntimeException("Middleware '{$nama}' tidak dikenal.");
                }
                $class = self::MIDDLEWARE[$nama];
                (new $class())->handle();
            }

            $controller = new $controllerClass();
            $controller->$action();
            return;
        }

        http_response_code(404);
        echo '404 - Halaman tidak ditemukan: ' . htmlspecialchars($uri);
    }
}
```


Satu baris kecil di `Controller` supaya halaman login boleh memakai tampilan polos (tanpa sidebar). Default-nya tetap `main`, jadi halaman lama **tidak berubah**:

```diff
--- app/Core/Controller.php (lama)
+++ app/Core/Controller.php (baru)
@@ -5,5 +5,5 @@
 abstract class Controller
 {
-    protected function view(string $view, array $data = []): void
+    protected function view(string $view, array $data = [], string $layout = 'main'): void
     {
         extract($data);
@@ -19,5 +19,5 @@
         $content = ob_get_clean();
 
-        require __DIR__ . '/../Views/layouts/main.php';
+        require __DIR__ . '/../Views/layouts/' . $layout . '.php';
     }
 
```
> Baris berawalan `+` = **tambahkan**, `-` = **hapus/ganti**, tanpa tanda = hanya penunjuk letak.


**Hasil yang seharusnya:** belum ada perubahan tampilan. Rute baru baru dipasang di Langkah 9.

---

## Langkah 5 — Urusan data pengguna (Repository & Service)

Ikut pola project: **Repository** = bagian yang bicara dengan database, **Service** = bagian yang berisi aturan/logika.

**Bahasa bayi:**
- `UserRepository` = petugas arsip: "cari user dengan username ini", "simpan user baru", "ambil semua user".
- `AuthService` = otak aturan: cek username/password cocok atau tidak (`password_verify`), cek isian register benar atau tidak, dan **acak password** sebelum disimpan (`password_hash`).

Aturan penting di `AuthService`:
- Pendaftaran lewat form **selalu** jadi role `user`. Role tidak diambil dari form, jadi orang iseng tidak bisa mendaftar sebagai admin.
- Pesan gagal login sengaja **sama** ("Username atau password salah") supaya penyerang tidak tahu username mana yang benar.
- Query memakai *prepared statement* (`:u`, `:e`) → aman dari SQL Injection.
- Daftar pengguna untuk admin **tidak** mengambil kolom password.

```php
// app/Repositories/UserRepository.php
<?php

namespace App\Repositories;

use App\Config\Database;
use PDO;

class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = :u LIMIT 1');
        $stmt->execute(['u' => $username]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :e LIMIT 1');
        $stmt->execute(['e' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (nama, username, email, password, role)
             VALUES (:nama, :username, :email, :password, :role)'
        );
        $stmt->execute([
            'nama'     => $data['nama'],
            'username' => $data['username'],
            'email'    => $data['email'],
            'password' => $data['password'],   // sudah berupa hash
            'role'     => $data['role'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** Daftar pengguna (TANPA kolom password) untuk halaman admin. */
    public function all(string $search = ''): array
    {
        $sql    = 'SELECT id, nama, username, email, role, created_at FROM users';
        $params = [];
        if ($search !== '') {
            $sql .= ' WHERE nama LIKE :s1 OR username LIKE :s2 OR email LIKE :s3 OR role LIKE :s4';
            $like   = '%' . $search . '%';
            $params = ['s1' => $like, 's2' => $like, 's3' => $like, 's4' => $like];
        }
        $sql .= ' ORDER BY id ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
```

```php
// app/Services/AuthService.php
<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Helpers\Logger;

class AuthService
{
    private UserRepository $repo;

    public function __construct()
    {
        $this->repo = new UserRepository();
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

    /** Mengembalikan data user jika username & password cocok, selain itu null. */
    public function attempt(string $username, string $password): ?array
    {
        $user = $this->repo->findByUsername($username);

        // password_verify membandingkan password yang diketik dengan HASH di database
        if (!$user || !password_verify($password, $user['password'])) {
            return null;
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
```


**Hasil yang seharusnya:** belum terlihat di browser.

---

## Langkah 6 — Controller untuk login, register, logout

**Bahasa bayi:** Controller = "kasir" yang menerima permintaan dari browser.

- `loginForm()` → tampilkan formulir login.
- `login()` → ambil isian, cek ke Service. Salah → kembali ke form + pesan merah. Benar → `Auth::login()` lalu masuk ke dashboard + pesan "Selamat datang".
- `registerForm()` / `register()` → daftar akun baru (role otomatis `user`).
- `logout()` → hancurkan session, lalu ke halaman login + pesan "Anda telah logout."

Pakai pola **PRG** (POST → Redirect → GET) seperti controller lain, jadi kalau halaman di-refresh form tidak terkirim dua kali. Password **tidak pernah** dikembalikan ke form (hanya username/nama/email yang diingat).

```php
// app/Controllers/AuthController.php
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
            'email'                 => trim($_POST['email'] ?? ''),
            'password'              => (string) ($_POST['password'] ?? ''),
            'password_confirmation' => (string) ($_POST['password_confirmation'] ?? ''),
        ];

        $errors = $this->service->validateRegister($input);
        if (!empty($errors)) {
            $this->setErrors($errors);
            $this->setOld([
                'nama' => $input['nama'], 'username' => $input['username'], 'email' => $input['email'],
            ]);
            $this->redirect('/register');
        }

        try {
            $this->service->register($input);
            $this->clearErrors();
            $this->clearOld();
            $this->flash('success', 'Registrasi berhasil. Silakan login.');
            $this->redirect('/login');
        } catch (\PDOException $e) {
            Logger::error('Gagal register: ' . $e->getMessage());
            $this->setOld(['nama' => $input['nama'], 'username' => $input['username'], 'email' => $input['email']]);
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
```


**Hasil yang seharusnya:** belum terlihat sampai halamannya dibuat (Langkah 7) dan rutenya dipasang (Langkah 9).

---

## Langkah 7 — Halaman Login & Register

**Bahasa bayi:** Ini formulirnya. Karena halaman login tidak punya sidebar, kita buat tampilan polos `layouts/auth.php` yang memakai **CSS, font, dan warna pastel yang sama** dengan aplikasi.

```php
// app/Views/layouts/auth.php
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($title ?? 'Masuk') ?> - Perpustakaan Kampus</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700;800&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/app.css?v=<?= @filemtime(__DIR__ . '/../../../public/assets/css/app.css') ?: 3 ?>" rel="stylesheet">
</head>
<body>
<div class="sky-decor" aria-hidden="true"></div>
<div class="auth-wrap">
  <div class="auth-box">
    <div class="auth-brand"><span class="brand-icon"><i class="bi bi-book-half"></i></span><span>Perpustakaan<br>Kampus</span></div>
    <?php require __DIR__ . '/../partials/alert.php'; ?>
    <?= $content ?>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

```php
// app/Views/auth/login.php
<div class="card shadow-sm">
  <div class="card-body p-4">
    <h1 class="auth-title">Masuk</h1>
    <p class="text-muted mb-4">Silakan login untuk melanjutkan.</p>

    <?php if ($this->errors('login')): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($this->errors('login')) ?></div>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>/login" method="POST" novalidate>
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" autocomplete="username" autofocus
               class="form-control <?= $this->errors('username') ? 'is-invalid' : '' ?>"
               value="<?= htmlspecialchars($this->old('username')) ?>">
        <?php if ($this->errors('username')): ?><div class="invalid-feedback"><?= htmlspecialchars($this->errors('username')) ?></div><?php endif; ?>
      </div>
      <div class="mb-4">
        <label class="form-label">Password</label>
        <input type="password" name="password" autocomplete="current-password"
               class="form-control <?= $this->errors('password') ? 'is-invalid' : '' ?>">
        <?php if ($this->errors('password')): ?><div class="invalid-feedback"><?= htmlspecialchars($this->errors('password')) ?></div><?php endif; ?>
      </div>
      <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
    <p class="text-center text-muted mt-3 mb-0">Belum punya akun? <a href="<?= BASE_URL ?>/register">Daftar di sini</a></p>
  </div>
</div>
```

```php
// app/Views/auth/register.php
<div class="card shadow-sm">
  <div class="card-body p-4">
    <h1 class="auth-title">Daftar Akun</h1>
    <p class="text-muted mb-4">Buat akun pengguna perpustakaan.</p>

    <form action="<?= BASE_URL ?>/register" method="POST" novalidate>
      <?php
      $fields = [
          ['nama', 'Nama Lengkap', 'text', 'name'],
          ['username', 'Username', 'text', 'username'],
          ['email', 'Email', 'email', 'email'],
      ];
      foreach ($fields as [$name, $label, $type, $auto]): ?>
        <div class="mb-3">
          <label class="form-label"><?= $label ?></label>
          <input type="<?= $type ?>" name="<?= $name ?>" autocomplete="<?= $auto ?>"
                 class="form-control <?= $this->errors($name) ? 'is-invalid' : '' ?>"
                 value="<?= htmlspecialchars($this->old($name)) ?>">
          <?php if ($this->errors($name)): ?><div class="invalid-feedback"><?= htmlspecialchars($this->errors($name)) ?></div><?php endif; ?>
        </div>
      <?php endforeach; ?>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" autocomplete="new-password"
               class="form-control <?= $this->errors('password') ? 'is-invalid' : '' ?>">
        <?php if ($this->errors('password')): ?><div class="invalid-feedback"><?= htmlspecialchars($this->errors('password')) ?></div><?php endif; ?>
      </div>
      <div class="mb-4">
        <label class="form-label">Ulangi Password</label>
        <input type="password" name="password_confirmation" autocomplete="new-password"
               class="form-control <?= $this->errors('password_confirmation') ? 'is-invalid' : '' ?>">
        <?php if ($this->errors('password_confirmation')): ?><div class="invalid-feedback"><?= htmlspecialchars($this->errors('password_confirmation')) ?></div><?php endif; ?>
      </div>
      <button type="submit" class="btn btn-primary w-100">Daftar</button>
    </form>
    <p class="text-center text-muted mt-3 mb-0">Sudah punya akun? <a href="<?= BASE_URL ?>/login">Login</a></p>
  </div>
</div>
```


CSS tambahan untuk kotak login & menu logout — **ditaruh di paling bawah `app.css`**, jadi aturan lama tidak ikut berubah:

```css
/* ===== Tambahan Authentication (tidak mengubah gaya lama) ===== */
.nav-item:nth-of-type(6) i{background:linear-gradient(135deg,#9AA7E8,#C1C9F4)}
button.nav-item{border:0;background:transparent;text-align:left;font-family:inherit;font-size:inherit;cursor:pointer}
.nav-logout i{background:linear-gradient(135deg,#E48A8A,#F3B1B1) !important}
.user-chip{margin:18px 10px 6px;padding:10px 12px;border-radius:16px;background:rgba(255,255,255,.55)}
.user-chip-name{font-weight:800;color:var(--purple-dark);line-height:1.2;word-break:break-word}
.user-chip-role{font-size:12px;color:var(--muted);font-weight:700}
.auth-wrap{position:relative;z-index:1;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
.auth-box{width:100%;max-width:430px}
.auth-brand{display:flex;align-items:center;justify-content:center;gap:12px;font-family:'Baloo 2',sans-serif;font-weight:700;font-size:18px;color:var(--purple-dark);line-height:1.15;margin-bottom:18px}
.auth-title{font-family:'Baloo 2',sans-serif;font-size:25px;font-weight:700;margin:0 0 4px;color:var(--ink)}
```

**Hasil yang seharusnya** (setelah Langkah 9):

- `/login` → kartu putih di tengah langit pastel: logo "Perpustakaan Kampus", judul **Masuk**, kolom *Username*, *Password*, tombol ungu **Login**, tautan "Belum punya akun? Daftar di sini".
- Login salah → kotak merah **"Username atau password salah."**
- Kolom kosong → tulisan merah "Username wajib diisi." / "Password wajib diisi."
- `/register` → 5 kolom (Nama, Username, Email, Password, Ulangi Password) + tombol **Daftar**. Berhasil → pindah ke login dengan pesan hijau **"Registrasi berhasil. Silakan login."**

---

## Langkah 8 — Halaman untuk User & Admin

### 8a. Halaman User: Dashboard + Katalog + Detail

**Bahasa bayi:** User hanya boleh **melihat**. Jadi kita bikin halaman sendiri untuk user yang **tidak punya tombol tambah/ubah/hapus sama sekali** (bukan sekadar disembunyikan). Dan karena rutenya `/katalog` (bukan `/buku`), user memang tidak punya jalan ke halaman admin.

- **Dashboard User** — sapaan "Halo, nama", 3 kartu (Judul Buku, Buku Tersedia, Stok Habis), tabel buku yang bisa dipinjam.
- **Katalog Buku** — daftar semua buku + kolom pencarian (judul, penulis, kategori, kode buku) + badge Tersedia/Habis + jumlah stok.
- **Detail Buku** — info lengkap, stok, status.

Dashboard memilih tampilan sesuai role; **dashboard admin tetap yang lama**:

```diff
--- app/Controllers/HomeController.php (lama)
+++ app/Controllers/HomeController.php (baru)
@@ -3,4 +3,5 @@
 namespace App\Controllers;
 
+use App\Core\Auth;
 use App\Core\Controller;
 use App\Services\BukuService;
@@ -11,4 +12,10 @@
     public function index(): void
     {
+        // User biasa melihat dashboard pengguna; admin tetap dashboard yang lama
+        if (!Auth::isAdmin()) {
+            $this->dashboardUser();
+            return;
+        }
+
         $bukuService       = new BukuService();
         $peminjamanService = new PeminjamanService();
@@ -31,3 +38,16 @@
         ]);
     }
+
+    private function dashboardUser(): void
+    {
+        $daftarBuku = (new BukuService())->getAll();
+
+        $this->view('dashboard/user', [
+            'title'         => 'Dashboard',
+            'totalBuku'     => count($daftarBuku),
+            'totalTersedia' => count(array_filter($daftarBuku, fn ($b) => $b['status'] === 'tersedia')),
+            'totalHabis'    => count(array_filter($daftarBuku, fn ($b) => (int) $b['stok'] === 0)),
+            'bukuTersedia'  => array_slice(array_values(array_filter($daftarBuku, fn ($b) => $b['status'] === 'tersedia')), 0, 5),
+        ]);
+    }
 }
```
> Baris berawalan `+` = **tambahkan**, `-` = **hapus/ganti**, tanpa tanda = hanya penunjuk letak.

```php
// app/Controllers/KatalogController.php
<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\BukuService;
use App\Exceptions\BukuTidakDitemukanException;

/** Untuk USER: hanya MELIHAT & MENCARI buku (tanpa tambah/ubah/hapus). */
class KatalogController extends Controller
{
    private BukuService $service;

    public function __construct()
    {
        $this->service = new BukuService();
    }

    public function index(): void
    {
        $search = trim($_GET['q'] ?? '');

        $this->view('katalog/index', [
            'title'      => 'Katalog Buku',
            'daftarBuku' => $this->service->getAll($search),
            'search'     => $search,
        ]);
    }

    public function detail(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        try {
            $buku = $this->service->getById($id);
        } catch (BukuTidakDitemukanException $e) {
            $this->flash('danger', $e->getMessage());
            $this->redirect('/katalog');
            return;
        }

        $this->view('katalog/detail', ['title' => 'Detail Buku', 'buku' => $buku]);
    }
}
```

```php
// app/Views/dashboard/user.php
<div class="page-head">
  <div>
    <h1>Halo, <?= htmlspecialchars(\App\Core\Auth::nama()) ?> <span aria-hidden="true">🌸</span></h1>
    <p>Cari buku dan cek stoknya sebelum datang ke perpustakaan.</p>
  </div>
  <a href="<?= BASE_URL ?>/katalog" class="btn btn-primary"><i class="bi bi-search me-1"></i>Cari Buku</a>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card card-stat text-white bg-primary shadow-sm"><div class="card-body">
      <div class="fs-2 fw-bold"><?= (int) $totalBuku ?></div><div>Judul Buku</div>
    </div></div>
  </div>
  <div class="col-md-4">
    <div class="card card-stat text-white bg-success shadow-sm"><div class="card-body">
      <div class="fs-2 fw-bold"><?= (int) $totalTersedia ?></div><div>Buku Tersedia</div>
    </div></div>
  </div>
  <div class="col-md-4">
    <div class="card card-stat text-white bg-danger shadow-sm"><div class="card-body">
      <div class="fs-2 fw-bold"><?= (int) $totalHabis ?></div><div>Buku Stok Habis</div>
    </div></div>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-header bg-white fw-semibold">Buku yang Bisa Dipinjam</div>
  <div class="table-responsive">
    <table class="table table-striped mb-0 align-middle">
      <thead class="table-dark">
        <tr><th>ID Buku</th><th>Judul</th><th>Penulis</th><th>Kategori</th><th>Stok</th><th>Status</th></tr>
      </thead>
      <tbody>
      <?php if (empty($bukuTersedia)): ?>
        <tr><td colspan="6" class="text-center text-muted py-3">Belum ada buku yang tersedia.</td></tr>
      <?php else: foreach ($bukuTersedia as $b): ?>
        <tr>
          <td><?= htmlspecialchars($b['kode_buku']) ?></td>
          <td><?= htmlspecialchars($b['judul']) ?></td>
          <td><?= htmlspecialchars($b['penulis']) ?></td>
          <td><?= htmlspecialchars($b['kategori'] ?? 'Umum') ?></td>
          <td><strong><?= (int) $b['stok'] ?></strong></td>
          <td><span class="badge bg-success">Tersedia</span></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
```

```php
// app/Views/katalog/index.php
<div class="page-head">
  <div>
    <h1>Katalog Buku</h1>
    <p>Cari buku berdasarkan judul, penulis, kategori, atau kode buku.</p>
  </div>
</div>

<div class="search-box mb-3">
  <form method="GET" action="<?= BASE_URL ?>/katalog" class="row g-2 align-items-center">
    <div class="col">
      <div class="input-group">
        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
        <input type="text" name="q" value="<?= htmlspecialchars($search ?? '') ?>" class="form-control border-start-0" placeholder="Cari judul, penulis, kategori, atau kode buku...">
      </div>
    </div>
    <div class="col-auto"><button class="btn btn-primary px-4">Cari</button></div>
    <?php if (($search ?? '') !== ''): ?><div class="col-auto"><a href="<?= BASE_URL ?>/katalog" class="btn btn-light border">Reset</a></div><?php endif; ?>
  </form>
</div>

<div class="card">
  <div class="card-header bg-white p-3">
    <div class="section-title">Daftar Buku</div>
    <small class="text-muted"><?= count($daftarBuku) ?> data ditemukan</small>
  </div>
  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr><th>No</th><th>Kode</th><th>Judul</th><th>Penulis</th><th>Kategori</th><th>Stok</th><th>Status</th><th>Detail</th></tr>
      </thead>
      <tbody>
      <?php if (!$daftarBuku): ?>
        <tr><td colspan="8"><div class="empty-state"><i class="bi bi-book"></i>Tidak ada buku yang sesuai.</div></td></tr>
      <?php else: $no = 1; foreach ($daftarBuku as $buku): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><strong><?= htmlspecialchars($buku['kode_buku']) ?></strong></td>
          <td><?= htmlspecialchars($buku['judul']) ?></td>
          <td><?= htmlspecialchars($buku['penulis']) ?></td>
          <td><?= htmlspecialchars($buku['kategori'] ?? 'Umum') ?></td>
          <td><strong><?= (int) $buku['stok'] ?></strong></td>
          <td><?= $buku['status'] === 'tersedia' ? '<span class="badge bg-success">Tersedia</span>' : '<span class="badge bg-danger">Habis</span>' ?></td>
          <td><a href="<?= BASE_URL ?>/katalog/detail?id=<?= (int) $buku['id'] ?>" class="btn btn-sm btn-info text-white"><i class="bi bi-eye"></i></a></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
```

```php
// app/Views/katalog/detail.php
<div class="card shadow-sm" style="max-width:600px;">
  <div class="card-header bg-white fw-semibold">Detail Buku</div>
  <div class="card-body">
    <table class="table table-borderless mb-0">
      <tr><th style="width:180px;">Kode Buku</th><td><?= htmlspecialchars($buku['kode_buku']) ?></td></tr>
      <tr><th>Judul</th><td><?= htmlspecialchars($buku['judul']) ?></td></tr>
      <tr><th>Penulis</th><td><?= htmlspecialchars($buku['penulis']) ?></td></tr>
      <tr><th>Penerbit</th><td><?= htmlspecialchars($buku['penerbit']) ?></td></tr>
      <tr><th>Kategori</th><td><?= htmlspecialchars($buku['kategori'] ?? 'Umum') ?></td></tr>
      <tr><th>Tahun Terbit</th><td><?= htmlspecialchars($buku['tahun_terbit']) ?></td></tr>
      <tr><th>Stok</th><td><?= (int) $buku['stok'] ?></td></tr>
      <tr>
        <th>Status</th>
        <td><?= $buku['status'] === 'tersedia' ? '<span class="badge bg-success">Tersedia</span>' : '<span class="badge bg-danger">Habis</span>' ?></td>
      </tr>
    </table>
  </div>
  <div class="card-footer bg-white">
    <a href="<?= BASE_URL ?>/katalog" class="btn btn-secondary">Kembali</a>
  </div>
</div>
```


### 8b. Halaman Admin: Data Pengguna

**Bahasa bayi:** Admin boleh melihat siapa saja yang punya akun. Tidak ada kolom password di tabelnya.

```php
// app/Controllers/PenggunaController.php
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
```

```php
// app/Views/pengguna/index.php
<div class="page-head">
  <div><h1>Data Pengguna</h1><p>Daftar akun admin dan user yang terdaftar di sistem.</p></div>
</div>

<div class="search-box mb-3">
  <form method="GET" action="<?= BASE_URL ?>/pengguna" class="row g-2 align-items-center">
    <div class="col"><div class="input-group"><span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span><input name="q" value="<?= htmlspecialchars($search ?? '') ?>" class="form-control border-start-0" placeholder="Cari nama, username, email, atau role..."></div></div>
    <div class="col-auto"><button class="btn btn-primary px-4">Cari</button></div>
    <?php if (($search ?? '') !== ''): ?><div class="col-auto"><a href="<?= BASE_URL ?>/pengguna" class="btn btn-light border">Reset</a></div><?php endif; ?>
  </form>
</div>

<div class="card">
  <div class="card-header bg-white p-3"><div class="section-title">Daftar Pengguna</div><small class="text-muted"><?= count($daftarPengguna) ?> data ditemukan</small></div>
  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>No</th><th>Nama</th><th>Username</th><th>Email</th><th>Role</th><th>Terdaftar</th></tr></thead>
      <tbody>
      <?php if (!$daftarPengguna): ?>
        <tr><td colspan="6"><div class="empty-state"><i class="bi bi-people"></i>Tidak ada pengguna yang sesuai.</div></td></tr>
      <?php else: $no = 1; foreach ($daftarPengguna as $u): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($u['nama']) ?></td>
          <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><?= $u['role'] === 'admin' ? '<span class="badge bg-primary">Admin</span>' : '<span class="badge bg-info text-white">User</span>' ?></td>
          <td><?= htmlspecialchars($u['created_at']) ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
```


### 8c. CRUD Buku & Manajemen Stok (Admin) — sudah ada, tinggal dikunci

CRUD buku dari tugas sebelumnya (tambah, lihat, ubah, hapus) **tidak ditulis ulang**. Yang berubah hanya: sekarang rutenya dijaga satpam `auth` + `admin` (Langkah 9). **Mengubah stok** dilakukan lewat tombol ✏️ **Edit** pada Data Buku (kolom *Stok*); status **Tersedia/Habis** otomatis menyesuaikan stok (stok 0 = Habis).

### 8d. Kolom Kategori (untuk pencarian User)

Form tambah/edit buku mendapat satu isian baru **Kategori** (boleh kosong → otomatis `Umum`), dan pencarian ikut mencari di kategori.

```diff
--- app/Repositories/BukuRepository.php (lama)
+++ app/Repositories/BukuRepository.php (baru)
@@ -20,5 +20,5 @@
         $params = [];
         if ($search !== '') {
-            $sql .= ' WHERE kode_buku LIKE :search OR judul LIKE :search2 OR penulis LIKE :search3 OR penerbit LIKE :search4';
+            $sql .= ' WHERE kode_buku LIKE :search OR judul LIKE :search2 OR penulis LIKE :search3 OR penerbit LIKE :search4 OR kategori LIKE :search5';
             $params = [
                 'search' => '%' . $search . '%',
@@ -26,4 +26,5 @@
                 'search3' => '%' . $search . '%',
                 'search4' => '%' . $search . '%',
+                'search5' => '%' . $search . '%',
             ];
         }
@@ -53,6 +54,6 @@
     {
         $stmt = $this->db->prepare(
-            'INSERT INTO buku (kode_buku, judul, penulis, penerbit, tahun_terbit, stok, status)
-             VALUES (:kode_buku, :judul, :penulis, :penerbit, :tahun_terbit, :stok, :status)'
+            'INSERT INTO buku (kode_buku, judul, penulis, penerbit, kategori, tahun_terbit, stok, status)
+             VALUES (:kode_buku, :judul, :penulis, :penerbit, :kategori, :tahun_terbit, :stok, :status)'
         );
         $stmt->execute([
@@ -61,4 +62,5 @@
             'penulis' => $data['penulis'],
             'penerbit' => $data['penerbit'],
+            'kategori' => $data['kategori'],
             'tahun_terbit' => $data['tahun_terbit'],
             'stok' => $data['stok'],
@@ -72,5 +74,5 @@
         $stmt = $this->db->prepare(
             'UPDATE buku SET kode_buku = :kode_buku, judul = :judul, penulis = :penulis,
-             penerbit = :penerbit, tahun_terbit = :tahun_terbit, stok = :stok,
+             penerbit = :penerbit, kategori = :kategori, tahun_terbit = :tahun_terbit, stok = :stok,
              status = :status WHERE id = :id'
         );
@@ -80,4 +82,5 @@
             'penulis' => $data['penulis'],
             'penerbit' => $data['penerbit'],
+            'kategori' => $data['kategori'],
             'tahun_terbit' => $data['tahun_terbit'],
             'stok' => $data['stok'],
```
> Baris berawalan `+` = **tambahkan**, `-` = **hapus/ganti**, tanpa tanda = hanya penunjuk letak.

```diff
--- app/Services/BukuService.php (lama)
+++ app/Services/BukuService.php (baru)
@@ -58,4 +58,8 @@
         }
 
+        if (mb_strlen(trim($input['kategori'] ?? '')) > 50) {
+            $errors['kategori'] = 'Kategori maksimal 50 karakter.';
+        }
+
         $tahun = (string) ($input['tahun_terbit'] ?? '');
         if ($tahun === '' || !ctype_digit($tahun) || (int) $tahun < 1900 || (int) $tahun > (int) date('Y') + 1) {
@@ -78,4 +82,5 @@
             'penulis'      => trim($input['penulis']),
             'penerbit'     => trim($input['penerbit']),
+            'kategori'     => trim($input['kategori'] ?? '') ?: 'Umum',
             'tahun_terbit' => (int) $input['tahun_terbit'],
             'stok'         => (int) $input['stok'],
@@ -96,4 +101,5 @@
             'penulis'      => trim($input['penulis']),
             'penerbit'     => trim($input['penerbit']),
+            'kategori'     => trim($input['kategori'] ?? '') ?: 'Umum',
             'tahun_terbit' => (int) $input['tahun_terbit'],
             'stok'         => (int) $input['stok'],
```
> Baris berawalan `+` = **tambahkan**, `-` = **hapus/ganti**, tanpa tanda = hanya penunjuk letak.

```diff
--- app/Views/buku/create.php (lama)
+++ app/Views/buku/create.php (baru)
@@ -27,4 +27,10 @@
         <?php if ($this->errors('penerbit')): ?><div class="invalid-feedback"><?= htmlspecialchars($this->errors('penerbit')) ?></div><?php endif; ?>
       </div>
+      <div class="mb-3">
+        <label class="form-label">Kategori</label>
+        <input type="text" name="kategori" class="form-control <?= $this->errors('kategori') ? 'is-invalid' : '' ?>"
+               value="<?= htmlspecialchars($this->old('kategori')) ?>" placeholder="Contoh: Teknologi (kosong = Umum)">
+        <?php if ($this->errors('kategori')): ?><div class="invalid-feedback"><?= htmlspecialchars($this->errors('kategori')) ?></div><?php endif; ?>
+      </div>
       <div class="row">
         <div class="col-md-6 mb-3">
```
> Baris berawalan `+` = **tambahkan**, `-` = **hapus/ganti**, tanpa tanda = hanya penunjuk letak.

```diff
--- app/Views/buku/edit.php (lama)
+++ app/Views/buku/edit.php (baru)
@@ -27,4 +27,10 @@
         <?php if ($this->errors('penerbit')): ?><div class="invalid-feedback"><?= htmlspecialchars($this->errors('penerbit')) ?></div><?php endif; ?>
       </div>
+      <div class="mb-3">
+        <label class="form-label">Kategori</label>
+        <input type="text" name="kategori" class="form-control <?= $this->errors('kategori') ? 'is-invalid' : '' ?>"
+               value="<?= htmlspecialchars($this->old('kategori', $buku['kategori'] ?? '')) ?>" placeholder="Contoh: Teknologi (kosong = Umum)">
+        <?php if ($this->errors('kategori')): ?><div class="invalid-feedback"><?= htmlspecialchars($this->errors('kategori')) ?></div><?php endif; ?>
+      </div>
       <div class="row">
         <div class="col-md-6 mb-3">
```
> Baris berawalan `+` = **tambahkan**, `-` = **hapus/ganti**, tanpa tanda = hanya penunjuk letak.

```diff
--- app/Views/buku/detail.php (lama)
+++ app/Views/buku/detail.php (baru)
@@ -18,4 +18,8 @@
         <th>Penerbit</th>
         <td><?= htmlspecialchars($buku['penerbit']) ?></td>
+      </tr>
+      <tr>
+        <th>Kategori</th>
+        <td><?= htmlspecialchars($buku['kategori'] ?? 'Umum') ?></td>
       </tr>
       <tr>
```
> Baris berawalan `+` = **tambahkan**, `-` = **hapus/ganti**, tanpa tanda = hanya penunjuk letak.


---

## Langkah 9 — Pasang satpam di semua rute & menu per role

**Bahasa bayi:** Sekarang kita pasang satpam di tiap pintu. Baca sebagai kalimat: `'/buku' => [..., ['auth', 'admin']]` artinya *"ke /buku: harus sudah login, DAN harus admin."*

```php
// routes/web.php
<?php

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\BukuController;
use App\Controllers\KatalogController;
use App\Controllers\PenggunaController;
use App\Controllers\PeminjamanController;
use App\Controllers\PengembalianController;
use App\Controllers\AnggotaController;

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
```


Sidebar ikut menyesuaikan role — menu admin yang lama **tetap utuh**; user melihat menu lebih pendek; keduanya punya tombol **Logout** (memakai POST agar tidak bisa dipicu sembarang link):

```diff
--- app/Views/partials/sidebar.php (lama)
+++ app/Views/partials/sidebar.php (baru)
@@ -1,3 +1,5 @@
 <?php
+use App\Core\Auth;
+
 $current = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
 if (BASE_URL !== '' && str_starts_with($current, BASE_URL)) {
@@ -14,4 +16,5 @@
 ?>
 <div class="menu-label">Menu utama</div>
+<?php if (Auth::isAdmin()): ?>
 <a href="<?= BASE_URL ?>/" class="nav-item <?= isActive('/', $current) ?>"><i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span></a>
 <a href="<?= BASE_URL ?>/buku" class="nav-item <?= isActive('/buku', $current) ?>"><i class="bi bi-journal-bookmark-fill"></i><span>Data Buku</span></a>
@@ -19,2 +22,15 @@
 <a href="<?= BASE_URL ?>/pengembalian" class="nav-item <?= isActive('/pengembalian', $current) ?>"><i class="bi bi-arrow-down-left-square-fill"></i><span>Pengembalian</span></a>
 <a href="<?= BASE_URL ?>/anggota" class="nav-item <?= isActive('/anggota', $current) ?>"><i class="bi bi-people-fill"></i><span>Data Anggota</span></a>
+<a href="<?= BASE_URL ?>/pengguna" class="nav-item <?= isActive('/pengguna', $current) ?>"><i class="bi bi-person-badge-fill"></i><span>Data Pengguna</span></a>
+<?php else: ?>
+<a href="<?= BASE_URL ?>/" class="nav-item <?= isActive('/', $current) ?>"><i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span></a>
+<a href="<?= BASE_URL ?>/katalog" class="nav-item <?= isActive('/katalog', $current) ?>"><i class="bi bi-journal-bookmark-fill"></i><span>Katalog Buku</span></a>
+<?php endif; ?>
+
+<div class="user-chip">
+  <div class="user-chip-name"><?= htmlspecialchars(Auth::nama()) ?></div>
+  <div class="user-chip-role"><?= Auth::isAdmin() ? 'Admin' : 'User' ?></div>
+</div>
+<form action="<?= BASE_URL ?>/logout" method="POST" class="m-0">
+  <button type="submit" class="nav-item nav-logout w-100"><i class="bi bi-box-arrow-right"></i><span>Logout</span></button>
+</form>
```
> Baris berawalan `+` = **tambahkan**, `-` = **hapus/ganti**, tanpa tanda = hanya penunjuk letak.


Dua baris kecil di layout supaya kotak pencarian di atas mengarah ke halaman yang benar untuk User:

```diff
--- app/Views/layouts/main.php (lama)
+++ app/Views/layouts/main.php (baru)
@@ -42,8 +42,10 @@
 if(gs){
   const path=window.location.pathname;
-  let target='/buku';
+  let target=<?= \App\Core\Auth::isAdmin() ? "'/buku'" : "'/katalog'" ?>;
   if(path.includes('/peminjaman')) target='/peminjaman';
   else if(path.includes('/pengembalian')) target='/pengembalian';
   else if(path.includes('/anggota')) target='/anggota';
+  else if(path.includes('/pengguna')) target='/pengguna';
+  else if(path.includes('/katalog')) target='/katalog';
   gs.action=<?= json_encode(BASE_URL) ?>+target;
 }
```
> Baris berawalan `+` = **tambahkan**, `-` = **hapus/ganti**, tanpa tanda = hanya penunjuk letak.


**Hasil yang seharusnya:**

*Sebagai Admin* — sidebar: Dashboard, Data Buku, Peminjaman, Pengembalian, Data Anggota, **Data Pengguna** (baru), lalu kotak nama "Administrator Perpustakaan / Admin" dan tombol **Logout**. Halaman-halamannya tampil sama seperti sebelumnya.

*Sebagai User* — sidebar: Dashboard, **Katalog Buku**, kotak nama "Mahasiswa Contoh / User", dan tombol **Logout**.

---

## Langkah 10 — Cara memasang & menguji (checklist hasil yang seharusnya)

**Pasang:**
1. Extract folder `perpustakaan` ke `htdocs` (XAMPP) lalu nyalakan Apache + MySQL.
2. Jalankan `database/auth.sql` (Langkah 1) dan `php database/seed_users.php` (Langkah 1b).
3. Buka `http://localhost/perpustakaan/public/login`.

**Uji — kalau hasil kamu sama dengan kolom kanan, berarti benar ✅**

| # | Yang dicoba | Hasil yang seharusnya |
|---|---|---|
| 1 | Buka `/` sebelum login | Otomatis pindah ke `/login`, ada pesan kuning "Silakan login terlebih dahulu." |
| 2 | Login `admin` + password salah | Tetap di login, kotak merah "Username atau password salah." |
| 3 | Login kosong | Pesan "Username wajib diisi." & "Password wajib diisi." |
| 4 | Login `admin` / `admin123` | Masuk dashboard admin (tampilan lama) + pesan hijau "Selamat datang, Administrator Perpustakaan!" |
| 5 | Admin: tambah, ubah, hapus buku, ubah stok | Berfungsi seperti sebelumnya; ada isian Kategori baru |
| 6 | Admin: klik **Data Pengguna** | Tabel berisi `admin` (Admin) dan `mahasiswa` (User), tanpa kolom password |
| 7 | Klik **Logout** | Pindah ke login, pesan "Anda telah logout." |
| 8 | Setelah logout, ketik manual `/buku` | Dilempar ke `/login` (halaman tidak bisa dibuka) |
| 9 | Setelah logout, tekan tombol **Back** browser | Tidak menampilkan halaman admin lama; diarahkan ke login |
| 10 | Login `mahasiswa` / `user123` | Dashboard user: "Halo, Mahasiswa Contoh", 3 kartu, tabel buku tersedia |
| 11 | User: buka **Katalog Buku**, cari "teknologi", cari kode buku, cari nama penulis | Daftar tersaring; ada Stok & badge Tersedia (hijau) / Habis (merah); **tidak ada** tombol edit/hapus |
| 12 | User: ketik manual `/buku`, `/buku/create`, `/pengguna`, `/anggota`, `/peminjaman` | Dikembalikan ke dashboard dengan pesan merah "Anda tidak memiliki hak akses ke halaman tersebut." |
| 13 | Buka `/register`, daftar akun baru | Pesan hijau, lalu login; akunnya otomatis **User** (cek di Data Pengguna admin) |
| 14 | Daftar dengan username yang sudah ada / email salah / password < 6 | Pesan error merah di kolom yang bersangkutan |
| 15 | Cek tabel `users` di phpMyAdmin | Kolom `password` berisi teks acak diawali `$2y$`, **bukan** `admin123` |
| 16 | Sudah login lalu buka `/login` | Langsung diarahkan ke dashboard |

---

## Langkah 11 — Pemetaan ke soal (untuk laporan)

| Tuntutan soal | Dipenuhi oleh |
|---|---|
| Login, Logout, Register | `AuthController` + `views/auth/*` |
| Validasi username/password, validasi input | `AuthService::validateLogin/validateRegister`, `BukuService::validate` |
| Password hashing | `password_hash()` saat daftar/seed, `password_verify()` saat login |
| Session / login state | `Auth::login()`, `$_SESSION`, `session_regenerate_id` |
| Proteksi halaman berdasarkan session | `AuthMiddleware` (`auth`) |
| Proteksi berdasarkan role | `AdminMiddleware` (`admin`) |
| User tidak bisa akses halaman Admin | Rute admin dipasangi `['auth','admin']`; User hanya punya `/katalog` |
| Logout menghancurkan session | `Auth::logout()` (kosongkan `$_SESSION`, hapus cookie, `session_destroy`) |
| Tabel `users` (id, nama, username, email, password, role) | `database/auth.sql` |
| Admin: dashboard, CRUD buku, stok, data pengguna | `HomeController`, `BukuController`, `PenggunaController` |
| User: dashboard, daftar, pencarian, detail, stok, status | `HomeController::dashboardUser`, `KatalogController` |
| Pencarian: judul, penulis, kategori, kode buku | `BukuRepository::all()` |

**Catatan keamanan lain yang sudah diterapkan:** semua output memakai `htmlspecialchars()` (anti XSS), semua query memakai prepared statement (anti SQL Injection), cookie session `HttpOnly` + `SameSite=Lax`, ID session diganti saat login, dan peminjaman/pengembalian/anggota ikut dikunci hanya untuk admin karena itu termasuk pengelolaan perpustakaan.

## Kalau ada masalah

| Gejala | Penyebab & solusi |
|---|---|
| Admin: error saat tambah/ubah buku ("gagal disimpan") | Kolom `kategori` belum ada → jalankan `database/auth.sql` |
| Login selalu "salah" | `seed_users.php` belum dijalankan / tabel `users` kosong |
| `php` tidak dikenali di terminal | Pakai path lengkap `C:\xampp\php\php.exe`, atau pakai cara daftar-lewat-register lalu ubah role di phpMyAdmin |
| Halaman 404 / CSS hilang | Buka lewat `.../perpustakaan/public/` (bukan folder luar), pastikan `mod_rewrite` Apache aktif |
| Lupa password admin | Jalankan di phpMyAdmin `DELETE FROM users WHERE username='admin';` lalu `php database/seed_users.php` lagi |
