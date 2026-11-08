<?php

namespace App\Repositories;

use App\Config\Database;
use PDO;

class PeminjamanRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(string $search = ''): array
    {
        $sql = "SELECT p.*, a.nama AS nama_anggota, b.judul AS judul_buku, b.kode_buku
                FROM peminjaman p
                INNER JOIN anggota a ON p.nim = a.nim
                INNER JOIN buku b ON p.id_buku = b.id";
        $params = [];
        if ($search !== '') {
            $sql .= " WHERE p.kode_peminjaman LIKE :s1 OR p.nim LIKE :s2 OR a.nama LIKE :s3
                      OR b.kode_buku LIKE :s4 OR b.judul LIKE :s5 OR p.status LIKE :s6";
            for ($i = 1; $i <= 6; $i++) {
                $params['s' . $i] = '%' . $search . '%';
            }
        }
        $sql .= ' ORDER BY p.id DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function aktif(string $search = ''): array
    {
        $sql = "SELECT p.*, a.nama AS nama_anggota, b.judul AS judul_buku, b.kode_buku
                FROM peminjaman p
                INNER JOIN anggota a ON p.nim = a.nim
                INNER JOIN buku b ON p.id_buku = b.id
                WHERE p.status = 'dipinjam'";
        $params = [];
        if ($search !== '') {
            $sql .= " AND (p.kode_peminjaman LIKE :s1 OR p.nim LIKE :s2 OR a.nama LIKE :s3
                      OR b.kode_buku LIKE :s4 OR b.judul LIKE :s5)";
            for ($i = 1; $i <= 5; $i++) {
                $params['s' . $i] = '%' . $search . '%';
            }
        }
        $sql .= ' ORDER BY p.id DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function riwayat(string $search = ''): array
    {
        $sql = "SELECT p.*, a.nama AS nama_anggota, b.judul AS judul_buku, b.kode_buku
                FROM peminjaman p
                INNER JOIN anggota a ON p.nim = a.nim
                INNER JOIN buku b ON p.id_buku = b.id
                WHERE p.status = 'kembali'";
        $params = [];
        if ($search !== '') {
            $sql .= " AND (p.kode_peminjaman LIKE :s1 OR p.nim LIKE :s2 OR a.nama LIKE :s3
                      OR b.kode_buku LIKE :s4 OR b.judul LIKE :s5)";
            for ($i = 1; $i <= 5; $i++) {
                $params['s' . $i] = '%' . $search . '%';
            }
        }
        $sql .= ' ORDER BY p.id DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Semua peminjaman milik satu anggota (berdasarkan NIM), lengkap dengan data buku. */
    public function byNim(string $nim, string $search = '', ?string $status = null): array
    {
        $sql = "SELECT p.*, b.judul AS judul_buku, b.kode_buku, b.penulis
                FROM peminjaman p
                INNER JOIN buku b ON p.id_buku = b.id
                WHERE p.nim = :nim";
        $params = ['nim' => $nim];
        if ($status !== null) {
            $sql .= ' AND p.status = :status';
            $params['status'] = $status;
        }
        if ($search !== '') {
            $sql .= " AND (p.kode_peminjaman LIKE :s1 OR b.kode_buku LIKE :s2 OR b.judul LIKE :s3 OR p.status LIKE :s4)";
            for ($i = 1; $i <= 4; $i++) {
                $params['s' . $i] = '%' . $search . '%';
            }
        }
        $sql .= ' ORDER BY p.id DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM peminjaman WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** ID buku yang sedang dipinjam (belum dikembalikan) oleh satu NIM. */
    public function idBukuAktifByNim(string $nim): array
    {
        $stmt = $this->db->prepare("SELECT id_buku FROM peminjaman WHERE nim = :nim AND status = 'dipinjam'");
        $stmt->execute(['nim' => $nim]);
        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }

    public function countAktifByNim(string $nim): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM peminjaman WHERE nim = :nim AND status = 'dipinjam'");
        $stmt->execute(['nim' => $nim]);
        return (int) $stmt->fetchColumn();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO peminjaman (kode_peminjaman, nim, id_buku, tanggal_pinjam, tanggal_kembali, status)
             VALUES (:kode_peminjaman, :nim, :id_buku, :tanggal_pinjam, :tanggal_kembali, 'dipinjam')"
        );
        $stmt->execute([
            'kode_peminjaman' => $data['kode_peminjaman'],
            'nim' => $data['nim'],
            'id_buku' => $data['id_buku'],
            'tanggal_pinjam' => $data['tanggal_pinjam'],
            'tanggal_kembali' => $data['tanggal_kembali'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updateKodePeminjaman(int $id, string $kodePeminjaman): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE peminjaman SET kode_peminjaman = :kode_peminjaman WHERE id = :id"
        );
        return $stmt->execute([
            'kode_peminjaman' => $kodePeminjaman,
            'id' => $id,
        ]);
    }

    public function tandaiKembali(int $id, string $tanggalKembaliAktual): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE peminjaman SET status = 'kembali', tanggal_kembali_aktual = :tgl
             WHERE id = :id AND status = 'dipinjam'"
        );
        $stmt->execute(['tgl' => $tanggalKembaliAktual, 'id' => $id]);
        return $stmt->rowCount() === 1;
    }

    public function getLastKode(): ?string
    {
        $stmt = $this->db->query(
            "SELECT kode_peminjaman FROM peminjaman 
            WHERE kode_peminjaman LIKE 'PJ%' 
            ORDER BY id DESC LIMIT 1"
        );
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row['kode_peminjaman'] ?? null;
    }
}
