<?php

namespace App\Service\Pdf\Storage;

interface CatalogStorageServiceInterface
{

    /**
     * Save catalog from local filesystem to catalogs storage
     *
     * @param string $filepath - absolute path to local file
     * @param string $filename - name for file in storage system
     * @return string - storage system path to file
     */
    public function uploadFromLocal(string $filepath, string $filename): string;

    public function delete(string $filename): void;

    public function exists(string $filename): bool;

    public function getRawContentFromFile(string $filename): string;

    public function downloadToFile(string $filename, string $pathToSave): void;

    public function getFullPathToCatalog(string $catalogName): string;

}