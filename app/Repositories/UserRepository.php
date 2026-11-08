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

    /** Cari akun berdasarkan username ATAU email (tidak membedakan huruf besar/kecil). */
    public function findForLogin(string $identitas): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE LOWER(username) = LOWER(:u1) OR LOWER(email) = LOWER(:u2) LIMIT 1'
        );
        $stmt->execute(['u1' => $identitas, 'u2' => $identitas]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function updatePassword(int $id, string $hash): void
    {
        $stmt = $this->db->prepare('UPDATE users SET password = :p WHERE id = :id');
        $stmt->execute(['p' => $hash, 'id' => $id]);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, nama, username, email, nim, role, created_at FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByNim(string $nim): ?array
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE nim = :nim LIMIT 1');
        $stmt->execute(['nim' => $nim]);
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
            'INSERT INTO users (nama, username, email, nim, password, role)
             VALUES (:nama, :username, :email, :nim, :password, :role)'
        );
        $stmt->execute([
            'nama'     => $data['nama'],
            'username' => $data['username'],
            'email'    => $data['email'],
            'nim'      => $data['nim'] ?? null,
            'password' => $data['password'],   // sudah berupa hash
            'role'     => $data['role'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** Daftar pengguna (TANPA kolom password) untuk halaman admin. */
    public function all(string $search = ''): array
    {
        $sql    = 'SELECT id, nama, username, email, nim, role, created_at FROM users';
        $params = [];
        if ($search !== '') {
            $sql .= ' WHERE nama LIKE :s1 OR username LIKE :s2 OR email LIKE :s3 OR role LIKE :s4 OR nim LIKE :s5';
            $like   = '%' . $search . '%';
            $params = ['s1' => $like, 's2' => $like, 's3' => $like, 's4' => $like, 's5' => $like];
        }
        $sql .= ' ORDER BY id ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
