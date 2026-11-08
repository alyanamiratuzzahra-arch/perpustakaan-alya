<?php

namespace App\Exceptions;

use Exception;

class BukuTidakTersediaException extends Exception
{
    public function __construct(string $message = 'Buku tidak tersedia.')
    {
        parent::__construct($message);
    }
}
