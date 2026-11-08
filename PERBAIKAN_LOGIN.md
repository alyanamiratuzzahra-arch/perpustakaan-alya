# Perbaikan Login (5 Okt 2026)

## Penyebab login gagal
Aplikasi memeriksa password dengan `password_verify()`, yang hanya cocok kalau kolom `users.password`
berisi HASH (`$2y$10$...`). Kalau password diketik langsung (mis. `admin123`) lewat phpMyAdmin,
username terlihat "sama persis" tetapi login tetap ditolak.

## Yang diubah
- `app/Services/AuthService.php` -> `attempt()`: login boleh pakai username ATAU email (huruf besar/kecil bebas);
  password plaintext lama otomatis di-hash saat login pertama.
- `app/Repositories/UserRepository.php` -> `findForLogin()`, `updatePassword()`.
- `database/reset_akun.php` (BARU) -> membuat/reset akun admin & user dengan hash yang benar.
- Pinjam buku oleh User: tombol "Pinjam" di Data Buku, Detail Buku, dan Dashboard (pilih 7 atau 14 hari, maks 3 buku aktif, NIM dari akun, tanggal dihitung server).
  Perbaikan bug stok: status buku tidak lagi salah menjadi "habis" saat stok masih tersisa 1.
- Menu User: Data Buku, Peminjaman Saya (jatuh tempo), Riwayat Peminjaman (tgl dikembalikan), Profil Saya, Logout.
  File baru/ubah: `ProfilController.php`, `Views/profil/index.php`, `PeminjamanSayaController.php`,
  `Views/peminjaman_saya/index.php`, `Views/partials/sidebar.php`, `routes/web.php`.

## Cara pakai
1. Pastikan Apache + MySQL (XAMPP) jalan dan database `perpustakaan` sudah ada tabel `users`.
2. Jalankan:  C:\xampp\php\php.exe database\reset_akun.php
3. Buka http://localhost/perpustakaan/public/login
   - Admin : admin / admin123
   - User  : mahasiswa / user123

## Fitur: User bisa meminjam buku sendiri
- Tombol **Pinjam** muncul di Dashboard, Data Buku, dan Detail Buku (khusus role user; admin tidak punya).
- Klik Pinjam -> pilih lama pinjam (7 / 14 hari) -> jatuh tempo dihitung otomatis oleh server.
- **Maksimal 3 buku aktif per user.** Kalau sudah 3, tombol berubah jadi "Kuota penuh".
- Setelah admin mencatat pengembalian (menu Pengembalian), kuota bertambah lagi dan user bisa pinjam lagi.
- Buku yang sedang dipinjam tidak bisa dipinjam dua kali; stok habis -> tombol nonaktif.
- NIM diambil dari akun yang login, bukan dari form, jadi user tidak bisa meminjam atas nama orang lain.
- Route baru: `POST /peminjaman-saya/pinjam`.
- Catatan: akun user harus punya NIM yang terdaftar di tabel `anggota` (kolom `users.nim`).
