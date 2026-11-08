<?php

namespace App\Exceptions;

use Exception;

/**
 * Dilempar ketika transaksi peminjaman dengan ID tertentu tidak ditemukan.
 */
class TransaksiTidakDitemukanException extends Exception
{
    public function __construct(string $message = 'Transaksi peminjaman tidak ditemukan.')
    {
        parent::__construct($message);
    }
}
