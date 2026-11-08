# Sistem Perpustakaan Kampus

Aplikasi PHP Native dengan MVC + Repository + Service Layer. Versi ini melanjutkan project awal dan menambahkan **halaman Pengembalian terpisah**, **Data Anggota**, serta **pencarian di setiap halaman data**.

## Fitur sesuai PDF

- CRUD buku.
- Transaksi peminjaman.
- Halaman pengembalian terpisah dari peminjaman.
- Data anggota: NIM dan nama.
- Pada form peminjaman, NIM dicari dari data anggota.
- Tabel peminjaman menampilkan: ID Pinjam, NIM, Nama, ID Buku, Judul Buku, Tanggal Pinjam, Tanggal Kembali, Status.
- Pencarian per halaman: Buku, Peminjaman, Pengembalian, Anggota.
- Business Logic seluruhnya berada di Service Layer:
  1. Buku hanya dapat dipinjam jika stok > 0.
  2. Satu mahasiswa maksimal memiliki 3 buku aktif.
  3. Peminjaman mengurangi stok 1.
  4. Pengembalian menambah stok 1.
  5. Stok 0 tidak dapat dipinjam.
  6. Buku yang sedang dipinjam tidak boleh dihapus.
  7. Tidak ada fitur hapus transaksi peminjaman.
- Validasi NIM, ID buku, tanggal pinjam, tanggal kembali, dan ketersediaan buku.
- Exception sesuai studi kasus.
- Logging transaksi peminjaman dan pengembalian ke `storage/logs/app.log`.
- PRG (POST -> Redirect -> GET) untuk tambah/ubah/hapus buku, tambah/ubah/hapus anggota, peminjaman, dan pengembalian.
- Peminjaman dan pengembalian memakai database transaction agar perubahan stok dan status transaksi tetap konsisten.

## Struktur penting

```text
app/
├── Config/Database.php
├── Controllers/
│   ├── HomeController.php
│   ├── BukuController.php
│   ├── PeminjamanController.php
│   ├── PengembalianController.php
│   └── AnggotaController.php
├── Exceptions/
├── Helpers/Logger.php
├── Repositories/
│   ├── BukuRepository.php
│   ├── AnggotaRepository.php
│   └── PeminjamanRepository.php
├── Services/
│   ├── BukuService.php
│   ├── PeminjamanService.php
│   └── AnggotaService.php
└── Views/
    ├── buku/
    ├── peminjaman/
    ├── pengembalian/
    ├── anggota/
    ├── dashboard/
    ├── layouts/
    └── partials/

database/perpustakaan.sql
routes/web.php
public/index.php
public/.htaccess
```

## URL

- `/` Dashboard
- `/buku` Data Buku
- `/buku/create` Tambah Buku
- `/buku/edit?id=1` Edit Buku
- `/buku/detail?id=1` Detail Buku
- `/peminjaman` Peminjaman + form tambah peminjaman
- `/pengembalian` Pengembalian + riwayat
- `/anggota` Data Anggota
- `/anggota/create` Tambah Anggota
- `/anggota/edit?id=1` Edit Anggota

## Instalasi XAMPP/Laragon

1. Extract folder `perpustakaan` ke `htdocs`.
2. Jalankan Apache dan MySQL.
3. Import `database/perpustakaan.sql` melalui phpMyAdmin.
4. Jika username/password MySQL berbeda, ubah `config/database.php`.
5. Buka `http://localhost/perpustakaan/public/`.

## Catatan aturan

File SQL menggunakan foreign key `RESTRICT` pada buku dan anggota agar histori transaksi tidak rusak. Tidak tersedia tombol hapus transaksi peminjaman, karena sesuai ketentuan PDF data peminjaman tidak boleh dihapus sembarangan.


## Tambahan: Authentication & Multi-User (Admin / User)

1. Import `database/auth.sql` di phpMyAdmin (membuat tabel `users` + kolom `kategori` pada `buku`).
2. Buat akun awal: `php database/seed_users.php`
   - Admin: `admin` / `admin123`
   - User : `mahasiswa` / `user123`
3. Buka `http://localhost/perpustakaan/public/login`.

| URL | Hak akses |
|---|---|
| `/login`, `/register` | Belum login |
| `/`, `/katalog`, `/katalog/detail` | Admin & User |
| `/buku/*`, `/peminjaman`, `/pengembalian`, `/anggota/*`, `/pengguna` | Admin saja |
