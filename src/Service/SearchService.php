<?php

namespace App\Service;

use App\Mapper\Elasticsearch\SearchDefaultSuggestsMapper;
use App\Model\Elasticsearch\Default\SearchResultList;
use App\Mapper\Elasticsearch\SearchDefaultMapper;
use App\Mapper\Elasticsearch\SearchSeriesCollapsedMapper;
use App\Repository\CategoryRepository;
use Doctrine\ORM\NonUniqueResultException;
use Elastic\Elasticsearch\Exception\ElasticsearchException;
use App\Service\Elasticsearch;

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

    public function productSuggests(string $text)
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
        
        $elastic_response = $this->elasticsearch->productHints($text);
        
        $result = [];
        foreach($elastic_response['hits']['hits'] as $hit){
            $inner_hits = [];

            foreach($hit['inner_hits']['value']['hits']['hits'] as $inner_hit) {
                $inner_hits[] = $inner_hit['fields']['value'][0];
            }

            $result[ $hit['fields']['type'][0] ] = $inner_hits;
        }

        return $result;
    }

}