<?php

namespace App\Model\Elasticsearch\Default;

use App\Model\Elasticsearch\SearchResultItem;

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