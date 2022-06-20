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
        foreach ($elastic_response['suggest']['highlight-suggest'] as $item) {
            foreach ($item['options'] as $option) {
                $items[] = $option['text'];
            }
        }
        return $items;
    }

}