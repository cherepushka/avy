<?php

namespace App\Model\Elasticsearch;

use OpenApi\Attributes as OA;

class ProductSuggestsList
{
    /**
     * @var string[] $items
     */
    #[OA\Property(
        property: 'suggestTypeName',
        type: 'array',
        items: new OA\Items(
            type: 'string'
        )
    )]
    private readonly array $items;

    public function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * @return string[]
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
