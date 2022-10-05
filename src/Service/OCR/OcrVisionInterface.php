<?php

namespace App\Service\OCR;

use App\Model\File\CatalogFile;

interface OcrVisionInterface
{
    /**
     * Return text from pdf catalog by OCR handling.
     *
     * @param CatalogFile $file - file that you want to parse
     *
     * @return string - parsed text from images
     */
    public function catalogGetTextSync(CatalogFile $file): string;

    public function getTextFromOcrResult(string $catalogName): string;
}
