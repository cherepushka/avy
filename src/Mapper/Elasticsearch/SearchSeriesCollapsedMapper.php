<?php

namespace App\Mapper\Elasticsearch;

use App\Model\Elasticsearch\Default\SearchResultList;
use App\Model\Elasticsearch\SearchResultItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SearchSeriesCollapsedMapper extends AbstractResponseMapper
{
    public function map(array $elastic_response, int $page_size, int $page): SearchResultList
    {
        $items = [];
        $total = $elastic_response['aggregations']['total']['value'];

        foreach ($elastic_response['hits']['hits'] as $series) {
            $item = [];
            $series_id = $series['fields']['series'][0];

            foreach ($series['inner_hits']['file-name']['hits']['hits'] as $inner_item) {
                $inner_fields = $inner_item['fields'];
                $suggestText = isset($inner_item['highlight']['text-content']) ? implode("\n", $inner_item['highlight']['text-content']) : '';

                $hit = (new SearchResultItem())
                    ->setSeries($series_id)
                    ->setLangAlias($inner_fields['lang'][0])
                    ->setByteSize($inner_fields['file-size'][0])
                    ->setDownloadLink($this->router->generate('app_files_download', [
                        'name' => $inner_fields['file-name'][0],
                    ], UrlGeneratorInterface::ABSOLUTE_URL))
                    ->setOriginName($inner_fields['origin-file-name'][0])
                    ->setSuggestText($suggestText);

                $item[] = $hit;
            }

            $items[$series_id] = $item;
        }

        return new SearchResultList($items, $page_size, $total, $page);
    }
}
