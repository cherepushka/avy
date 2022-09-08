<?php

namespace App\Service\Pdf\Storage;

use App\Model\File\CatalogFile;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class StorageServiceFacade
{

    public function __construct(
        private readonly CatalogStorageServiceInterface $catalogStorageService,
        private readonly SluggerInterface $slugger
    ){}

    public function saveUploadedCatalog(UploadedFile $file): CatalogFile
    {
        if ($file->getError()) {
            throw new UploadException($file->getErrorMessage());
        }

        $trimmedExtFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        $safeFilename = $this->slugger->slug($trimmedExtFileName);

        $fileName = sprintf('%s-%s.%s', $safeFilename, uniqid(), $extension);

        $catalogFile = (new CatalogFile())
            ->setOriginName($file->getClientOriginalName())
            ->setByteSize($file->getSize())
            ->setExtension($extension)
            ->setName($fileName)
            ->setMimeType($file->getMimeType());

        $newFilepath = $this->catalogStorageService->uploadFromLocal($file->getRealPath(), $fileName);
        $catalogFile->setFullPath($newFilepath);

        return $catalogFile;
    }

    public function getCatalogFullPath(string $catalogName): string
    {
        return $this->catalogStorageService->getFullPathToCatalog($catalogName);
    }

    public function deleteCatalog(string $filename): void
    {
        $this->catalogStorageService->delete($filename);
    }

    public function getRawContentFromCatalogFile(string $filename): string
    {
        return $this->catalogStorageService->getRawContentFromFile($filename);
    }

    /**
     * @param string $filename
     * @return resource
     */
    public function saveCatalogTmpCopy(string $filename)
    {
        $tmpFile = tmpfile();
        $tmpFilePath = $this->getPathToTmpFile($tmpFile);

        $this->catalogStorageService->downloadToFile($filename, $tmpFilePath);

        return $tmpFile;
    }

    /**
     * @param resource $tmpFile
     * @return string - absolute path to file
     */
    public function getPathToTmpFile($tmpFile): string
    {
        $tmpFileMetadata = stream_get_meta_data($tmpFile);
        return $tmpFileMetadata['uri'];
    }

}