<?php

namespace App\Exception;

use Exception;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;

class FileAlreadyLoadedException extends Exception
{

    public function __construct(string $filepath = "")
    {
        parent::__construct("File '$filepath' is already loaded to system", 500);
    }
}