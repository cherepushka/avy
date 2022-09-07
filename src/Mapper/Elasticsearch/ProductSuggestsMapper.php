<?php

namespace App\Mapper\Elasticsearch;

use App\Model\Elasticsearch\ProductSuggestsList;

class ProductSuggestsMapper
{

    public function map(array $elastic_response): ProductSuggestsList
    {
        $result = [];

        foreach($elastic_response['hits']['hits'] as $hit){
            $inner_hits = [];

            foreach($hit['inner_hits']['value']['hits']['hits'] as $inner_hit) {
                $inner_hits[] = $inner_hit['fields']['value'][0];
            }

            $result[ $hit['fields']['type'][0] ] = $inner_hits;
        }

        return new ProductSuggestsList($result);
    }

}