<?php

namespace App\Service\Pdf\Storage;

use App\Model\File\CatalogFile;
use App\Model\File\CatalogTmpFile;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class StorageServiceFacade
{

    public function __construct(
        private readonly CatalogStorageServiceInterface $catalogStorageService,
        private readonly OcrResultStorageServiceInterface $ocrResultStorageService
    ){}

    public function saveUploadedCatalog(UploadedFile $file): CatalogFile
    {
        if ($file->getError()) {
            throw new UploadException($file->getErrorMessage());
        }

        $tmpCatalog = (new CatalogTmpFile())
            ->setFullPath($file->getRealPath())
            ->setOriginName($file->getClientOriginalName())
            ->setExtension('pdf')
            ->setByteSize($file->getSize());

        return $this->catalogStorageService->save($tmpCatalog);
    }

    public function deleteCatalog(string $filename): void
    {
        $this->catalogStorageService->delete($filename);
    }

    public function getRawContentFromCatalogFile(string $filename): string
    {
        return $this->catalogStorageService->getRawContentFromFile($filename);
    }

    public function isCatalogSaved(string $filename): bool
    {
        return $this->catalogStorageService->exists($filename);
    }

}