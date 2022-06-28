<?php

namespace App\Service\Pdf;

use App\Service\OCR\OcrVisionInterface;
use ImagickException;

class TextParser
{

    public function __construct(
        private readonly OcrVisionInterface $OCR,
        private readonly ImageBuilder $imageBuilder
    ){}

    /**
     * @throws ImagickException
     */
    public function parseFromFile(string $filepath): string
    {
        $imageArray = $this->imageBuilder->generateImagickImages($filepath);

        try {
            $text = $this->OCR->findImageAnnotations($imageArray);
        } finally {
            $this->imageBuilder->deleteGeneratedImagesWithDir($imageArray);
        }

        return $text;
    }

}