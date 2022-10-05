<?php

namespace App\Exception;

use Exception;

class FileAlreadyLoadedException extends Exception
{
    public function __construct(string $filepath = '')
    {
        parent::__construct("Файл '$filepath' уже загружен в систему", 500);
    }
}
