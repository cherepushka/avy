<?php

namespace App\Service\Pdf\Storage\Local;

use App\Service\Pdf\Storage\CatalogStorageServiceInterface;
use RuntimeException;

class CatalogStorageService implements CatalogStorageServiceInterface
{
    private readonly string $catalogs_dir;

    public function __construct(
        string $upload_directory_path,
    ) {
        $this->catalogs_dir = rtrim($upload_directory_path, '\\/');

        if (!is_dir($this->catalogs_dir)) {
            mkdir($this->catalogs_dir, 0777, true);
        }
    }

    public function uploadFromLocal(string $filepath, string $filename): string
    {
        $newCatalogPath = $this->getFullPathToCatalog($filename);

        if (false === copy($filepath, $newCatalogPath)) {
            throw new RuntimeException("Unable to save file from '$filepath' to '$newCatalogPath'");
        }

        return $newCatalogPath;
    }

    public function delete(string $filename): void
    {
        $catalogPath = $this->getFullPathToCatalog($filename);

        if (!unlink($catalogPath)) {
            throw new RuntimeException("File in path '$catalogPath' cannot be removed");
        }
    }

    public function exists(string $filename): bool
    {
        return is_file($this->getFullPathToCatalog($filename));
    }

    public function getRawContentFromFile(string $filename): string
    {
        return file_get_contents($this->getFullPathToCatalog($filename));
    }

    public function downloadToFile(string $filename, string $pathToSave): void
    {
        $catalogPath = $this->getFullPathToCatalog($filename);
        file_put_contents($pathToSave, file_get_contents($catalogPath));
    }

    public function getFullPathToCatalog(string $catalogName): string
    {
        return $this->catalogs_dir.DIRECTORY_SEPARATOR.$catalogName;
    }
}
