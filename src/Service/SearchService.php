<?php

namespace App\Service;

use App\Mapper\Elasticsearch\SearchDefaultSuggestsMapper;
use App\Model\Elasticsearch\Default\SearchResultList;
use App\Mapper\Elasticsearch\SearchDefaultMapper;
use App\Mapper\Elasticsearch\SearchSeriesCollapsedMapper;
use Doctrine\ORM\NonUniqueResultException;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ElasticsearchException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;

class SearchService
{

    public function __construct(
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
        $elastic_response = $this->elasticsearch->suggestsDefault($text);

        return $this->searchDefaultSuggestsMapper->map($elastic_response);
    }

    /**
     * @throws ElasticsearchException|NonUniqueResultException
     */
    public function searchDefault(string $text): SearchResultList
    {
        $elastic_response = $this->elasticsearch->search($text);

        return $this->defaultResultMapper->map($elastic_response);
    }

    /**
     * @throws ElasticsearchException|NonUniqueResultException
     */
    public function searchSeriesCollapsed(string $text): array
    {
        $elastic_response = $this->elasticsearch->searchCollapseBySeries($text);

        return $this->searchSeriesCollapsedMapper->map($elastic_response);
    }

}