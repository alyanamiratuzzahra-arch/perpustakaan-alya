<?php

namespace App\Exceptions;

use Exception;

class BatasPeminjamanException extends Exception
{
    public function __construct(string $message = 'Peminjaman buku maksimal berjumlah 3 buku.')
    {
        parent::__construct($message);
    }
}
