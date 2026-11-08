<?php

namespace App\Services;

use App\Repositories\AnggotaRepository;
use App\Helpers\Logger;

class AnggotaService
{
    private AnggotaRepository $repo;

    public function __construct()
    {
        $this->repo = new AnggotaRepository();
    }

    public function getAll(string $search = ''): array
    {
        return $this->repo->all(trim($search));
    }

    public function getById(int $id): array
    {
        $anggota = $this->repo->find($id);
        if (!$anggota) {
            throw new \RuntimeException('Data anggota tidak ditemukan.');
        }
        return $anggota;
    }

    public function validate(array $input, ?int $ignoreId = null): array
    {
        $errors = [];
        $nim = trim($input['nim'] ?? '');
        $nama = trim($input['nama'] ?? '');

        if ($nim === '') {
            $errors['nim'] = 'NIM wajib diisi.';
        } elseif (!ctype_digit($nim)) {
            $errors['nim'] = 'NIM harus berupa angka.';
        } elseif (strlen($nim) < 5 || strlen($nim) > 9) {
            $errors['nim'] = 'NIM harus terdiri dari 5-9 digit.';
        } else {
            $existing = $this->repo->findByNim($nim);
            if ($existing && (int) $existing['id'] !== (int) $ignoreId) {
                $errors['nim'] = 'NIM sudah terdaftar.';
            }
        }

        if ($nama === '') {
            $errors['nama'] = 'Nama anggota wajib diisi.';
        } elseif (mb_strlen($nama) < 2) {
            $errors['nama'] = 'Nama anggota terlalu pendek.';
        }

        return $errors;
    }

    public function create(array $input): int
    {
        $id = $this->repo->create(['nim' => trim($input['nim']), 'nama' => trim($input['nama'])]);
        Logger::info("Anggota ditambahkan: ID={$id}, NIM={$input['nim']}, Nama={$input['nama']}");
        return $id;
    }

    public function update(int $id, array $input): void
    {
        $this->getById($id);
        $this->repo->update($id, ['nim' => trim($input['nim']), 'nama' => trim($input['nama'])]);
        Logger::info("Anggota diperbarui: ID={$id}");
    }

    public function delete(int $id): void
    {
        $this->getById($id);
        if ($this->repo->adaPeminjaman($id)) {
            throw new \RuntimeException('Anggota yang memiliki riwayat peminjaman tidak boleh dihapus.');
        }
        $this->repo->delete($id);
        Logger::info("Anggota dihapus: ID={$id}");
    }
}
