<?php

namespace App\Service\Pdf\Storage;

use App\Model\File\CatalogFile;
use App\Model\File\CatalogTmpFile;

interface CatalogStorageServiceInterface
{

    /**
     * Save catalog from local filesystem to catalogs storage
     */
    public function save(CatalogTmpFile $file): CatalogFile;

    public function delete(string $filename): void;

    public function exists(string $filename): bool;

    public function getRawContentFromFile(string $filename): string;

}