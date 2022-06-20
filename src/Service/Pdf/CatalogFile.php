<?php

namespace App\Service\Pdf;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class CatalogFile
{

    private readonly string $catalogs_dir;

    public function __construct(
        string  $upload_directory_path,
        private readonly SluggerInterface $slugger
    ){
        $this->catalogs_dir = rtrim($upload_directory_path, '\\/');
        if (!is_dir($this->catalogs_dir)){
            mkdir($this->catalogs_dir, 0777);
        }
    }

    public function getCatalogsDirectory(): string
    {
        return $this->catalogs_dir;
    }

    /**
     * @param UploadedFile $file
     * @return string
     * @throws FileException
     */
    public function saveUploadedFile(UploadedFile $file): string
    {
        if ($file->getError()) {
            throw new UploadException($file->getErrorMessage());
        }

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = sprintf('%s-%s.%s', $safeFilename, uniqid(), $file->guessExtension());

        $file->move($this->getCatalogsDirectory(), $fileName);

        return $this->getCatalogsDirectory() . '/' . $fileName;
    }

    /** @throws FileNotFoundException $catalogName */
    public function getCatalogPath(string $catalogName): string
    {
        $filepath = $this->getCatalogsDirectory() . '/' . $catalogName;

        if (!file_exists($filepath)){
            throw new FileNotFoundException('File not found by path ' . $filepath);
        }

        return $filepath;
    }

}