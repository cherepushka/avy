<?php

namespace App\Service\Pdf;

use RuntimeException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\RuntimeException as FileRuntimeException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class CatalogFileService
{

    private readonly string $catalogs_dir;
    private readonly string $tmp_catalogs_dir;

    public function __construct(
        string $upload_directory_path,
        string $tmp_directory_path,
        private readonly SluggerInterface $slugger
    ){
        $this->catalogs_dir = rtrim($upload_directory_path, '\\/');
        $this->tmp_catalogs_dir = rtrim($tmp_directory_path, "\\/") . DIRECTORY_SEPARATOR . "catalogs";

        if (!is_dir($this->catalogs_dir)){
            mkdir($this->catalogs_dir, 0777, true);
        }

        if (!is_dir($this->tmp_catalogs_dir)){
            mkdir($this->tmp_catalogs_dir, 0777, true);
        }
    }

    public function getCatalogsDir(): string
    {
        return $this->catalogs_dir;
    }

    public function getTmpCatalogsDir(): string
    {
        return $this->tmp_catalogs_dir;
    }

    /**
     * @param UploadedFile $file
     * @return string
     * @throws FileException
     */
    public function saveUploadedFileToTmp(UploadedFile $file): string
    {
        if ($file->getError()) {
            throw new UploadException($file->getErrorMessage());
        }

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = sprintf('%s-%s.%s', $safeFilename, uniqid(), $file->guessExtension());

        $file->move($this->getTmpCatalogsDir(), $fileName);

        return $this->getTmpCatalogsDir() . DIRECTORY_SEPARATOR . $fileName;
    }

    /** @throws FileNotFoundException */
    public function getCatalogPath(string $catalogName): string
    {
        $filepath = $this->getCatalogsDir() . DIRECTORY_SEPARATOR . $catalogName;

        if (!file_exists($filepath)){
            throw new FileNotFoundException('File not found by path ' . $filepath);
        }

        return $filepath;
    }

    /** @throws FileNotFoundException */
    public function getTmpCatalogPath(string $catalogName): string
    {
        $filepath = $this->getTmpCatalogsDir() . DIRECTORY_SEPARATOR . $catalogName;

        if (!file_exists($filepath)){
            throw new FileNotFoundException('File not found by path ' . $filepath);
        }

        return $filepath;
    }

    public function moveFromTmpToCatalogs(string $filename): string
    {
        $tmp_filepath = $this->getTmpCatalogPath($filename);
        $catalog_filepath = $this->getCatalogsDir() . DIRECTORY_SEPARATOR . $filename;

        if (!rename($tmp_filepath, $catalog_filepath)){
            throw new RuntimeException("Error occurred with moving file from '$tmp_filepath' to '$catalog_filepath'");
        }

        return $this->getCatalogPath($filename);
    }

    public function removeTmpCatalog(string $filename): void
    {
        $catalogPath = $this->getTmpCatalogPath($filename);

        if (!unlink($catalogPath)){
            throw new FileRuntimeException("File in path '$catalogPath' cannot be removed");
        }
    }

    public function removeCatalog(string $filename): void
    {
        $catalogPath = $this->getCatalogPath($filename);

        if (!unlink($catalogPath)){
            throw new FileRuntimeException("File in path '$catalogPath' cannot be removed");
        }
    }

    public function getTmpCatalogByteSize(string $filename): int
    {
        $filepath = $this->getTmpCatalogPath($filename);

        return filesize($filepath);
    }

    public function getTmpCatalogRawContent(string $filename): string
    {
        $filepath = $this->getTmpCatalogPath($filename);

        return file_get_contents($filepath);
    }

    public function getCatalogRawContent(string $filename): string
    {
        $filepath = $this->getCatalogPath($filename);

        return file_get_contents($filepath);
    }

}