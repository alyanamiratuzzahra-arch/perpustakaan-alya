<?php

namespace App\Repositories;

use App\Config\Database;
use PDO;

class BukuRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(string $search = ''): array
    {
        $sql = 'SELECT * FROM buku WHERE deleted_at IS NULL';
        $params = [];
        if ($search !== '') {
            $sql .= ' AND (kode_buku LIKE :search OR judul LIKE :search2 OR penulis LIKE :search3 OR penerbit LIKE :search4 OR kategori LIKE :search5)';
            $params = [
                'search' => '%' . $search . '%',
                'search2' => '%' . $search . '%',
                'search3' => '%' . $search . '%',
                'search4' => '%' . $search . '%',
                'search5' => '%' . $search . '%',
            ];
        }
        $sql .= ' ORDER BY id DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM buku WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByKode(string $kodeBuku): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM buku WHERE kode_buku = :kode');
        $stmt->execute(['kode' => $kodeBuku]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO buku (kode_buku, judul, penulis, penerbit, kategori, tahun_terbit, stok, status)
             VALUES (:kode_buku, :judul, :penulis, :penerbit, :kategori, :tahun_terbit, :stok, :status)'
        );
        $stmt->execute([
            'kode_buku' => $data['kode_buku'],
            'judul' => $data['judul'],
            'penulis' => $data['penulis'],
            'penerbit' => $data['penerbit'],
            'kategori' => $data['kategori'],
            'tahun_terbit' => $data['tahun_terbit'],
            'stok' => $data['stok'],
            'status' => $data['stok'] > 0 ? 'tersedia' : 'habis',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE buku SET kode_buku = :kode_buku, judul = :judul, penulis = :penulis,
             penerbit = :penerbit, kategori = :kategori, tahun_terbit = :tahun_terbit, stok = :stok,
             status = :status WHERE id = :id'
        );
        $stmt->execute([
            'kode_buku' => $data['kode_buku'],
            'judul' => $data['judul'],
            'penulis' => $data['penulis'],
            'penerbit' => $data['penerbit'],
            'kategori' => $data['kategori'],
            'tahun_terbit' => $data['tahun_terbit'],
            'stok' => $data['stok'],
            'status' => $data['stok'] > 0 ? 'tersedia' : 'habis',
            'id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM buku WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /** Soft delete: buku disembunyikan, baris tetap ada agar riwayat peminjaman utuh. */
    public function softDelete(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE buku SET deleted_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function decrementStok(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE buku SET status = IF(stok - 1 > 0, 'tersedia', 'habis'), stok = stok - 1
             WHERE id = :id AND stok > 0"  // status dihitung SEBELUM stok berubah (MySQL memproses SET dari kiri ke kanan)
        );
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() === 1;
    }

    public function incrementStok(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE buku SET stok = stok + 1, status = 'tersedia' WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() === 1;
    }

    public function adaPeminjamanAktif(int $id): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM peminjaman WHERE id_buku = :id AND status = 'dipinjam'");
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function adaRiwayatPeminjaman(int $id): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM peminjaman WHERE id_buku = :id");
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
