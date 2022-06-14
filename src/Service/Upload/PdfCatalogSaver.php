<?php

namespace App\Service\Upload;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class PdfCatalogSaver
{

    public function __construct(
        private readonly string  $upload_directory_path,
        private readonly SluggerInterface $slugger
    ){}

    public function save(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            // ... handle exception if something happens during file upload
        }

        return $this->getTargetDirectory() . '/' . $fileName;
    }

    public function getTargetDirectory(): string
    {
        return $this->upload_directory_path;
    }
}