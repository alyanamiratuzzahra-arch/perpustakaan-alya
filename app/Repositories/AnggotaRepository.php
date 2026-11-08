<?php

namespace App\Repositories;

use App\Config\Database;
use PDO;

class AnggotaRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(string $search = ''): array
    {
        $sql = "SELECT a.*, COALESCE(SUM(CASE WHEN p.status = 'dipinjam' THEN 1 ELSE 0 END), 0) AS jumlah_dipinjam
                FROM anggota a
                LEFT JOIN peminjaman p ON p.nim = a.nim";

        $params = [];
        if ($search !== '') {
            $sql .= " WHERE a.nim LIKE :search OR a.nama LIKE :search2";
            $params['search'] = '%' . $search . '%';
            $params['search2'] = '%' . $search . '%';
        }

        $sql .= " GROUP BY a.id, a.nim, a.nama ORDER BY a.nama ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM anggota WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByNim(string $nim): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM anggota WHERE nim = :nim');
        $stmt->execute(['nim' => $nim]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO anggota (nim, nama) VALUES (:nim, :nama)');
        $stmt->execute(['nim' => $data['nim'], 'nama' => $data['nama']]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare('UPDATE anggota SET nim = :nim, nama = :nama WHERE id = :id');
        $stmt->execute(['nim' => $data['nim'], 'nama' => $data['nama'], 'id' => $id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM anggota WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function adaPeminjaman(int $id): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM peminjaman p INNER JOIN anggota a ON a.nim = p.nim WHERE a.id = :id'
        );
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
