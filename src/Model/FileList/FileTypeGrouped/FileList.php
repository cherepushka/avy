<?php

namespace App\Model\FileList\FileTypeGrouped;

class FileList
{

    private array $items;

    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function getItems(): array
    {
        return $this->items;
    }

}