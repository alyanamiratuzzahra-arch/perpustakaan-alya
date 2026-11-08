<?php

namespace App\Exceptions;

use Exception;

/**
 * Dilempar ketika data buku dengan ID tertentu tidak ditemukan di database.
 */
class BukuTidakDitemukanException extends Exception
{
    public function __construct(string $message = 'Data buku tidak ditemukan.')
    {
        parent::__construct($message);
    }
}
