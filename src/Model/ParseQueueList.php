<?php

namespace App\Model;

class ParseQueueList
{

    private array $items;

    /** @param ParseQueueItem[] $catalogs */
    public function __construct(array $catalogs)
    {
        $this->items = $catalogs;
    }

    /** @return ParseQueueItem[] */
    public function getItems(): array
    {
        return $this->items;
    }

}