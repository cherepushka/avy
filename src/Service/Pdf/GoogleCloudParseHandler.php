<?php

namespace App\Service\Pdf;

use App\Service\Pdf\Storage\GoogleCloud\StorageServiceFacade;

class GoogleCloudParseHandler
{

    public function __construct(
        private readonly StorageServiceFacade $storageService
    ){}

    public function parseUploadedFile()
    {

    }

}