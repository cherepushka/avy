<?php

namespace App\Model\Elasticsearch\Default;

use App\Model\Elasticsearch\SearchResultItem;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;

class SearchResultList
{

    /** @var SearchResultItem[] $items */
    #[OA\Property(
        property: 'items',
        properties: [new OA\Property(
            property: 'seriaName',
            type: 'array',
            items: new OA\Items(
                ref: new Model(type: SearchResultItem::class)
            )
        )],
        type: 'object'
    )]
    private array $items;

    private int $totalHits;

    private int $currentPage;

    private int $maxPage;

    private int $pageSize;

    /**
     * @param SearchResultItem[] $searchResultItems
     * @param int $total - count if all result hits
     */
    public function __construct(array $searchResultItems, int $page_size, int $total, int $page)
    {
        $this->items = $searchResultItems;
        $this->totalHits = $total;
        $this->pageSize = $page_size;
        $this->currentPage = $page;

        $this->setMaxPage();
    }

    private function setMaxPage(): void
    {
        if ($this->totalHits === 0){
            $this->maxPage = 0;
            return;
        }

        $this->maxPage = (int)ceil($this->totalHits / $this->pageSize);
    }

    /** @return SearchResultItem[] */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getTotalHits(): int
    {
        return $this->totalHits;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getMaxPage(): int
    {
        return $this->maxPage;
    }

}