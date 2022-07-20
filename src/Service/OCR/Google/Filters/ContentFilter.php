<?php

namespace App\Service\OCR\Google\Filters;

use Exception;
use Google\Cloud\Vision\V1\AnnotateFileResponse;
use Google\Cloud\Vision\V1\AnnotateImageResponse;
use Google\Cloud\Vision\V1\Block;
use Google\Cloud\Vision\V1\Page;
use Google\Cloud\Vision\V1\Paragraph;
use Google\Cloud\Vision\V1\Symbol;
use Google\Cloud\Vision\V1\TextAnnotation\DetectedBreak\BreakType;
use Google\Cloud\Vision\V1\Word;

class ContentFilter
{

    private AnnotateFileResponse $response;

    private array $separators = [
        'UNKNOWN'           => "",
        'SPACE'             => " ",
        'SURE_SPACE'        => " ",
        'EOL_SURE_SPACE'    => " \n",
        'HYPHEN'            => ' - ',
        'LINE_BREAK'        => "\n",
    ];

    //if text block matching any of these regexps, then it doesn't be in result text
    private array $stop_paragraphs_regexps = [
        '#^[\d\W]+$#ui',
    ];

    public function setResponse(AnnotateFileResponse $response): void
    {
        $this->response = $response;
    }

    public function getFilteredText(): string
    {
        $text = '';

        /** @var AnnotateImageResponse $response */
        foreach ($this->response->getResponses() as $response) {

            $fullTextAnnotation = $response->getFullTextAnnotation();

            // skipping iteration if text content is empty
            if ($fullTextAnnotation === null){
                continue;
            }

            /** @var Page $page */
            foreach ($fullTextAnnotation->getPages()->getIterator() as $page) {
                $text .= $this->getFilteredTextFromPage($page);
            }
        }


        return $text;
    }

    private function getFilteredTextFromPage(Page $page): string
    {
        $text = '';

        /** @var Block $block */
        foreach ($page->getBlocks()->getIterator() as $block) {
            /** @var Paragraph $paragraph */
            foreach($block->getParagraphs()->getIterator() as $paragraph){
                $text .= $this->getFilteredTextFromParagraphs($paragraph);
            }
        }

        return $text;
    }

    private function getFilteredTextFromParagraphs(Paragraph $paragraph): string
    {
        $result_text = '';

        /** @var Word $word */
        foreach ($paragraph->getWords()->getIterator() as $word){

            /** @var Symbol $symbol */
            foreach($word->getSymbols()->getIterator() as $symbol){
                $result_text .= $symbol->getText();

                $separator = '';

                if($symbol->hasProperty()){
                    /** @var int $text_property */
                    $separator_type = $symbol->getProperty()->getDetectedBreak()->getType();

                    $separator_name = BreakType::name($separator_type);
                    $separator = $this->separators[$separator_name];
                }

                $result_text .= $separator;
            }
        }

        foreach ($this->stop_paragraphs_regexps as $regexp){
            if (preg_match($regexp, $result_text) === 1){
                $result_text = '';
            }
        }

        return $result_text;
    }

}