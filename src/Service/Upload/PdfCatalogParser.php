<?php

namespace App\Service\Upload;

use Exception;
use Smalot\PdfParser\Parser;

class PdfCatalogParser extends Parser
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    public function filter_content_from_file(string $filepath ): string
    {
        $pdf_obj = $this->parseFile($filepath);
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