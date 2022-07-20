<?php

namespace App\Service\Pdf\Storage\GoogleCloud;

use Google\Cloud\Storage\StorageClient;
use SplFileInfo;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class QueueStorageService
{

    private readonly string $storageDirPrefix;

    public function __construct(
        private readonly StorageClient $storageClient,
        private readonly string $bucketName,
    ){
        $this->storageDirPrefix = 'tmp-catalogs';
    }

    public function uploadFromLocal(SplFileInfo $fileInfo): void
    {
        $fullPath = $fileInfo->getRealPath();

        if (!file_exists($fullPath)){
            throw new FileNotFoundException("File not found by path '$fullPath'");
        }


    }

    public function remove(string $filename): void
    {

    }

    public function moveToParsedCatalogsDir(): void
    {

    }

}