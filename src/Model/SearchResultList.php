<?php

namespace App\Model;

class SearchResultList
{

    /** @var SearchResultItem[] $items */
    private array $items;

    /** @param SearchResultItem[] */
    public function __construct(array $searchResultItems)
    {
        $this->items = $searchResultItems;
    }

    /** @return SearchResultItem[] */
    public function getItems(): array
    {
        return $this->items;
    }

}