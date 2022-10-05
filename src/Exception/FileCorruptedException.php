<?php

namespace App\Exception;

use Exception;

class FileCorruptedException extends Exception
{
    public function __construct(string $filepath = '')
    {
        parent::__construct("File by path '$filepath' is corrupted and unable to read");
    }
}
