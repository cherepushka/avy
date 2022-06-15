<?php

namespace App\Service\Pdf;

use Exception;
use Smalot\PdfParser\Parser as PdfParser;

class Parser
{

    public function __construct(
        private PdfParser $parser
    ){}

    /**
     * @throws Exception
     */
    public function textFromFile(string $filepath): string
    {
        $pdf_obj = $this->parser->parseFile($filepath);
        $text = explode("\n", $pdf_obj->getText());

        $elastic_content_array = [];
        $rep = [":", "\u2022", "\t"];

        for ($i = 0; $i < 20; $i++) {
            static $is = "\u0000";
            $rep[] = $is;
            $is++;
        }

        foreach ($text as $value) {
            if (json_encode($value) && !empty($value)) {
                $json = json_encode($value);
                $json = str_replace($rep, "\u0020", $json);
                $elastic_content_array[] = json_decode($json);
            }
        }

        return implode(" ", $elastic_content_array);
    }

}