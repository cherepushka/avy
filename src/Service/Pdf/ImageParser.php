<?php

namespace App\Service\Pdf;

use Imagick;
use ImagickException;
use RuntimeException;
use thiagoalessio\TesseractOCR\TesseractOCR;
use thiagoalessio\TesseractOCR\TesseractOcrException;

class ImageParser
{

    private readonly Imagick $im;
    private readonly TesseractOCR $tesseract;

    /**
     * @param string $filepath
     * @throws ImagickException
     */
    private function setupImagickWithDocPath(string $filepath): void
    {
        $this->im = new Imagick($filepath);
    }

    /**
     * @param string $filepath
     */
    private function setupTesseractWithDocPath(string $filepath): void
    {
        $this->tesseract = new TesseractOCR($filepath);
    }

    /**
     * @param string $imageBlob - BLOB image content
     * @param int $imgSize - size in bytes
     */
    private function setupTesseractWithDocBlob(string $imageBlob, int $imgSize): void
    {
        $this->tesseract = new TesseractOCR();
        $this->tesseract->imageData($imageBlob, $imgSize);
    }

    /**
     * @param string $filepath - path to file
     *
     * @throws RuntimeException
     * @throws ImagickException
     */
    public function pdfPageAsImageBlob( string $filepath )
    {
        if (!file_exists($filepath)){
            throw new RuntimeException("File not found on path $filepath");
        }

        $this->setupImagickWithDocPath($filepath);

        for ($i = 0; $i < $this->im->getNumberImages(); $i++){
            $this->im->previousImage();

            $this->im->setImageFormat('png');
            $this->im->writeImage("/var/www/html/avy/var/$i.png");
        }
    }

    /**
     * @throws TesseractOcrException
     */
    private function parseTextFromImageBlob(string $imageBlob, int $imageSize): string
    {
        $this->setupTesseractWithDocBlob($imageBlob, $imageSize);

        return $this->tesseract->run();
    }

}