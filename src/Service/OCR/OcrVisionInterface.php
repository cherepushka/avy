<?php

namespace App\Service\OCR;

interface OcrVisionInterface
{

    /**
     * Return text from images
     *
     * @param string[] $imgArray - array of absolute paths to images, which must be parsed
     * @return string - parsed text from images
     */
    public function findImageAnnotations(array $imgArray): string;

}