<?php

namespace App\Mapper\Elasticsearch;

use App\Model\Elasticsearch\Default\SearchResultList;
use App\Model\Elasticsearch\SearchResultItem;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SearchDefaultMapper extends AbstractResponseMapper
{

    public function map(array $elastic_response, int $page_size, int $page): SearchResultList
    {
        $total = $elastic_response['hits']['total']['value'];

        $items = [];
        foreach ($elastic_response['hits']['hits'] as $hit){
            $fields = $hit['fields'];

            $items[] = (new SearchResultItem())
                ->setSuggestText(implode("\n", $hit['highlight']['text-content']))
                ->setOriginName($fields['origin-file-name'][0])
                ->setDownloadLink($this->router->generate('app_catalogs_pdf_show', [
                    'name' => $fields['file-name'][0]
                ], UrlGeneratorInterface::ABSOLUTE_URL))
                ->setByteSize($fields['file-size'][0])
                ->setLangAlias($fields['lang'][0]);
        }

        return new SearchResultList($items, $page_size, $total, $page);
    }

}