<?php

namespace App\Service\Pdf;

use Exception;
use Smalot\PdfParser\Config;
use Smalot\PdfParser\Parser;

class CatalogParser
{

    private Parser $textParser;
    private array $badChars;

    public function __construct()
    {
        $this->registerPdfTextReader();
    }

    private function registerPdfTextReader(): void
    {
        $config = new Config();
        $config->setRetainImageContent(false);

        $this->textParser = new Parser([], $config);
    }

    /**
     * @throws Exception
     */
    public function textFromFile(string $filepath): string
    {
        $this->generateBadChars();

        $pdf_obj = $this->textParser->parseFile($filepath);
        $text = explode("\n", $pdf_obj->getText());

        $elastic_content_array = [];

        foreach ($text as $value) {
            if (json_encode($value) && !empty($value)) {
                $json = json_encode($value);
                $json = str_replace($this->badChars, "\u0020", $json);
                $elastic_content_array[] = json_decode($json);
            }
        }

        return mb_convert_encoding(implode(" ", $elastic_content_array), 'utf8', 'utf8');
    }

    /**
     * Generate and save chars that must be removed from PDF
     */
    private function generateBadChars(): void
    {
        $chars = [":", "\u2022", "\t", "\b"];

        for ($i = 0; $i < 20; $i++) {
            static $is = "\u0000";
            $chars[] = $is;
            $is++;
        }

        $this->badChars = $chars;
    }

}