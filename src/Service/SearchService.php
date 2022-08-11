<?php

namespace App\Service;

use App\Entity\Category;
use App\Mapper\Elasticsearch\SearchDefaultSuggestsMapper;
use App\Model\Elasticsearch\Default\SearchResultList;
use App\Mapper\Elasticsearch\SearchDefaultMapper;
use App\Mapper\Elasticsearch\SearchSeriesCollapsedMapper;
use App\Repository\CategoryRepository;
use Doctrine\ORM\NonUniqueResultException;
use Elastic\Elasticsearch\Exception\ElasticsearchException;

class SearchService
{

    public function __construct(
        private readonly CategoryRepository             $categoryRepository,
        private readonly Elasticsearch                  $elasticsearch,
        private readonly SearchDefaultMapper            $defaultResultMapper,
        private readonly SearchSeriesCollapsedMapper    $searchSeriesCollapsedMapper,
        private readonly SearchDefaultSuggestsMapper    $searchDefaultSuggestsMapper
    ){}

    /**
     * @throws ElasticsearchException
     */
    public function suggestsDefault(string $text): array
    {
        $elastic_response = $this->elasticsearch->suggestsGlobal($text);

        return $this->searchDefaultSuggestsMapper->map($elastic_response);
    }

    /**
     * @throws ElasticsearchException|NonUniqueResultException
     */
    public function searchDefault(string $text, int $page = 1): SearchResultList
    {
        $page_size = 10;
        $from = ($page - 1) * $page_size;

        $elastic_response = $this->elasticsearch->searchGlobal($text, $from);

        return $this->defaultResultMapper->map($elastic_response, $page_size, $page);
    }

    /**
     * @param string $text - text for search
     * @param int[] $series - array of series for filtering
     * 
     * @throws ElasticsearchException|NonUniqueResultException
     */
    public function searchSeriesCollapsed(
        string $text,
        array $series,
        int $page = 1
    ): SearchResultList
    {
        $series_size = 3;
        $from = ($page - 1) * $series_size;

        $elastic_response = $this->elasticsearch->searchCollapseBySeries($text, $series, $series_size, $from);
        return $this->searchSeriesCollapsedMapper->map($elastic_response, $series_size, $page);
    }

}