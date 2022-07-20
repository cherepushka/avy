<?php

namespace App\Service\Pdf\Storage\Local;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\RuntimeException as FileRuntimeException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class StorageService
{

    private readonly string $catalogs_dir;

    public function __construct(
        string $upload_directory_path,
        private readonly SluggerInterface $slugger
    ){
        $this->catalogs_dir = rtrim($upload_directory_path, '\\/');

        if (!is_dir($this->catalogs_dir)){
            mkdir($this->catalogs_dir, 0777, true);
        }
    }

    public function getCatalogsDir(): string
    {
        return $this->catalogs_dir;
    }

    /**
     * @param UploadedFile $file
     * @return string
     * @throws FileException
     */
    public function saveUploaded(UploadedFile $file): string
    {
        if ($file->getError()) {
            throw new UploadException($file->getErrorMessage());
        }

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = sprintf('%s-%s.%s', $safeFilename, uniqid(), $file->guessExtension());

        $file->move($this->getCatalogsDir(), $fileName);

        return $this->getCatalogsDir() . DIRECTORY_SEPARATOR . $fileName;
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

    public function removeCatalog(string $filename): void
    {
        $catalogPath = $this->getCatalogPath($filename);

        if (!unlink($catalogPath)){
            throw new FileRuntimeException("File in path '$catalogPath' cannot be removed");
        }
    }

    public function getCatalogRawContent(string $filename): string
    {
        $filepath = $this->getCatalogPath($filename);

        return file_get_contents($filepath);
    }

}