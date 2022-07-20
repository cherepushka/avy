<?php

namespace App\Service\Pdf\Storage\GoogleCloud;

use Google\Cloud\Storage\StorageClient;

class StorageServiceFacade
{

    private string $storagePath = 'gs://avy-elastic-ocr/catalogs';

    public function __construct(
        private readonly OcrResultStorageService $ocrResultStorageService,
        private readonly QueueStorageService $queueStorageService,
        private readonly CatalogStorageService $catalogStorageService,
    ){

    }

    public function getCatalogsDir(): string
    {
        return $this->storagePath;
    }

    public function getTmpCatalogsDir()
    {

    }

}