<?php

namespace App\Service\Pdf;

use App\Exception\FileCorruptedException;
use App\Service\OCR\OcrVisionInterface;
use ImagickException;

class TextParser
{

    public function __construct(
        private readonly OcrVisionInterface $OCR,
        private readonly ImageBuilder $imageBuilder
    ){}

    /**
     * @throws ImagickException|FileCorruptedException
     */
    public function parseFromFile(string $filepath): string
    {
        if ($this->imageBuilder->checkIsPdfCorrupted($filepath)){
            throw new FileCorruptedException($filepath);
        }

        $imageArray = $this->imageBuilder->generateImagickImages($filepath);

        try {
            $text = $this->OCR->findImageAnnotations($imageArray);
        } finally {
            $this->imageBuilder->deleteGeneratedImagesWithDir($imageArray);
        }

        return $text;
    }

}