<?php

namespace App\Service\Pdf\Storage\GoogleCloud;

use App\Service\Pdf\Storage\OcrResultStorageServiceInterface;
use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\ObjectIterator;
use Google\Cloud\Storage\StorageClient;

class OcrResultStorageService implements OcrResultStorageServiceInterface
{
    private Bucket $bucket;
    private StorageClient $storageClient;

    private string $bucketName = 'avy-elastic-ocr';
    private string $storageDir = 'ocr-parse-results';

    public function __construct(string $credentials_path)
    {
        $this->storageClient = new StorageClient([
            'keyFilePath' => $credentials_path,
        ]);

        $this->bucket = $this->storageClient->bucket($this->bucketName);
    }

    public function getBucketPathForResults(string $catalogName): string
    {
        return sprintf('%s/%s/', $this->storageDir, $catalogName);
    }

    public function getFullPathForResults(string $catalogName): string
    {
        return sprintf('gs://%s/%s/%s/', $this->bucketName, $this->storageDir, $catalogName);
    }

    public function getResultsOfCatalog(string $catalogName): ObjectIterator
    {
        $resultsDir = $this->getBucketPathForResults($catalogName);

        return $this->bucket->objects([
            'prefix' => $resultsDir,
        ]);
    }
}
