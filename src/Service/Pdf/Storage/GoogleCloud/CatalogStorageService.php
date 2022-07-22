<?php

namespace App\Service\Pdf\Storage\GoogleCloud;

use App\Model\File\CatalogFile;
use App\Model\File\CatalogTmpFile;
use App\Service\Pdf\Storage\CatalogStorageServiceInterface;
use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;
use Symfony\Component\String\Slugger\SluggerInterface;

class CatalogStorageService implements CatalogStorageServiceInterface
{

    private string $bucketName = 'avy-elastic-ocr';
    private Bucket $bucket;
    private string $catalogsDir = 'test';
    private readonly StorageClient $storageClient;

    public function __construct(
        string $credentials_path,
        private readonly SluggerInterface $slugger,
    )
    {
        $this->storageClient = new StorageClient([
            'keyFilePath' => $credentials_path
        ]);

        $this->bucket = $this->storageClient->bucket($this->bucketName);
    }

    public function save(CatalogTmpFile $file): CatalogFile
    {
        $trimmedExtFileName = pathinfo($file->getOriginName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($trimmedExtFileName);
        $fileName = sprintf('%s-%s.%s', $safeFilename, uniqid(), $file->getExtension());

        $catalogFile = (new CatalogFile())
            ->setName($fileName)
            ->setOriginName($file->getOriginName())
            ->setFullPath(sprintf('gs://%s/%s/%s', $this->bucketName, $this->catalogsDir, $fileName))
            ->setExtension($file->getExtension())
            ->setByteSize($file->getByteSize());

        $this->bucket->upload(fopen($file->getFullPath(), 'r'), [
            'name' => $this->getPathToCatalog($fileName)
        ]);

        return $catalogFile;
    }

    public function delete(string $filename): void
    {
        $storageObject = $this->bucket->object($this->getPathToCatalog($filename));

        $storageObject->delete();
    }

    public function exists(string $filename): bool
    {
        $storageObject = $this->bucket->object($this->getPathToCatalog($filename));

        return $storageObject->exists();
    }

    public function getRawContentFromFile(string $filename): string
    {
        $storageObject = $this->bucket->object($this->getPathToCatalog($filename));

        return $storageObject->downloadAsString();
    }

    private function getPathToCatalog(string $filename): string
    {
        return sprintf('%s/%s', $this->catalogsDir, $filename);
    }

}