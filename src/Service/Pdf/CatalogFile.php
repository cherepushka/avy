<?php

namespace App\Service\Pdf;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class CatalogFile
{

    private string $catalogs_dir;

    public function __construct(
        string  $upload_directory_path,
        private readonly SluggerInterface $slugger
    ){
        $this->catalogs_dir = rtrim($upload_directory_path, '\\/');
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
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        $file->move($this->getCatalogsDirectory(), $fileName);

        return $this->getCatalogsDirectory() . '/' . $fileName;
    }

    public function getCatalogPath(string $catalogName): string
    {
        $filepath = $this->getCatalogsDirectory() . '/' . $catalogName;

        if (!file_exists($filepath)){
            throw new FileNotFoundException('File not found by path ' . $filepath);
        }

        return $filepath;
    }

}