<?php

namespace App\Model;

class LanguageList
{

    /** @var LanguageItem[] */
    private array $items;

    /** @param LanguageItem[] $languages */
    public function __construct(array $languages)
    {
        $this->items = $languages;
    }

    /** @return LanguageItem[] */
    public function getItems(): array
    {
        return $this->items;
    }

}