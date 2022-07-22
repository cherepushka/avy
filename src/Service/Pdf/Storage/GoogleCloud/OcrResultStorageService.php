<?php

namespace App\Service\Pdf\Storage\GoogleCloud;

use App\Service\Pdf\Storage\OcrResultStorageServiceInterface;

class OcrResultStorageService implements OcrResultStorageServiceInterface
{

    private string $storageBucket = 'avy-elastic-ocr';
    private string $storageDir = 'ocr-parse-results';
    private string $storagePath = 'gs://avy-elastic-ocr/ocr-parse-results';

    public function __construct(string $credentials_path)
    {

    }

    public function getStoragePath(): string
    {
        return $this->storagePath;
    }

    public function getStorageBucket(): string
    {
        return $this->storageBucket;
    }

    public function getStorageDir(): string
    {
        return $this->storageDir;
    }

}