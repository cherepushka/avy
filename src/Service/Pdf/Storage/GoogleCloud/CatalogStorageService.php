<?php

namespace App\Service\Pdf\Storage\GoogleCloud;

use App\Service\Pdf\Storage\CatalogStorageServiceInterface;
use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;

class CatalogStorageService implements CatalogStorageServiceInterface
{

    private string $bucketName = 'avy-elastic-ocr';
    private Bucket $bucket;
    private string $catalogsDir = 'test';
    private readonly StorageClient $storageClient;

    public function __construct(
        string $credentials_path,
    )
    {
        $this->storageClient = new StorageClient([
            'keyFilePath' => $credentials_path
        ]);

        $this->bucket = $this->storageClient->bucket($this->bucketName);
    }

    public function uploadFromLocal(string $filepath, string $filename): string
    {
        $this->bucket->upload(fopen($filepath, 'r'), [
            'name' => $this->getBucketPathToCatalog($filename)
        ]);

        return $this->getFullPathToCatalog($filename);
    }

    public function delete(string $filename): void
    {
        $storageObject = $this->bucket->object($this->getBucketPathToCatalog($filename));

        $storageObject->delete();
    }

    public function exists(string $filename): bool
    {
        $storageObject = $this->bucket->object($this->getBucketPathToCatalog($filename));

        return $storageObject->exists();
    }

    public function getRawContentFromFile(string $filename): string
    {
        $storageObject = $this->bucket->object($this->getBucketPathToCatalog($filename));

        return $storageObject->downloadAsString();
    }

    public function downloadToFile(string $filename, string $pathToSave): void
    {
        $storageObject = $this->bucket->object($this->getBucketPathToCatalog($filename));
        $storageObject->downloadToFile($pathToSave);
    }

    private function getBucketPathToCatalog(string $filename): string
    {
        return sprintf('%s/%s', $this->catalogsDir, $filename);
    }

    public function getFullPathToCatalog(string $catalogName): string
    {
        return sprintf('gs://%s/%s/%s', $this->bucketName, $this->catalogsDir, $catalogName);
    }

}