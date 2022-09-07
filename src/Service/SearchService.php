<?php

namespace App\Service;

use App\Mapper\Elasticsearch\ProductSuggestsMapper;
use App\Model\Elasticsearch\Default\SearchResultList;
use App\Mapper\Elasticsearch\SearchDefaultMapper;
use App\Mapper\Elasticsearch\SearchSeriesCollapsedMapper;
use App\Model\Elasticsearch\ProductSuggestsList;
use Elastic\Elasticsearch\Exception\ElasticsearchException;

class SearchService
{

    public function __construct(
        private readonly Elasticsearch                  $elasticsearch,
        private readonly SearchDefaultMapper            $defaultResultMapper,
        private readonly SearchSeriesCollapsedMapper    $searchSeriesCollapsedMapper,
        private readonly ProductSuggestsMapper          $productSuggestsMapper
    ){}

    /**
     * @throws ElasticsearchException
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
     * @throws ElasticsearchException
     */
    public function searchSeriesCollapsed(
        string $text,
        ?array $series,
        int $page = 1
    ): SearchResultList
    {
        $series_size = 3;
        $from = ($page - 1) * $series_size;

        if ($series) {
            $elastic_response = $this->elasticsearch->searchCollapseBySeries($text, $series, $series_size, $from);
        } else {
            $elastic_response = $this->elasticsearch->searchCollapseBySeriesEmptySeries($text, $series_size, $from);
        }

        return $this->searchSeriesCollapsedMapper->map($elastic_response, $series_size, $page);
    }

    /**
     * @throws ElasticsearchException
     */
    public function productSuggests(string $text): ProductSuggestsList
    {
        // Add slashes to Elastic reserved query characters
        // https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-query-string-query.html#_reserved_characters
        $text = addcslashes($text, '+-=&|><!(){}[]^"~*?:\/');

        $text_words = explode(" ", $text);
        foreach ($text_words as $key => $word){
            $text_words[$key] = $word . '^' . $key + 1;
        }

        $text_words = array_reverse($text_words);
        $text = implode(' ', $text_words);
        
        $elastic_response = $this->elasticsearch->productSuggests($text);
        return $this->productSuggestsMapper->map($elastic_response);
    }

}