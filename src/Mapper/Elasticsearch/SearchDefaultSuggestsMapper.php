<?php

namespace App\Mapper\Elasticsearch;

class SearchDefaultSuggestsMapper extends AbstractResponseMapper
{

    /**
     * @return array<string>
     */
    public function map(array $elastic_response): array
    {
        $items = [];

        foreach ($elastic_response['hits']['hits'] as $hit) {
            foreach ($hit['highlight']['text-content.trigram'] as $suggest) {
                $items[] = $suggest;
            }
        }
        return $items;
    }

}