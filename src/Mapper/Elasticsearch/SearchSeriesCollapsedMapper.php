<?php

namespace App\Mapper\Elasticsearch;

use App\Model\Elasticsearch\SearchResultItem;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SearchSeriesCollapsedMapper extends AbstractResponseMapper
{

    /**
     * @throws NonUniqueResultException
     */
    public function map(array $elastic_response): array
    {
        $items = [];

        foreach ($elastic_response['hits']['hits'] as $series){
            $item = [];
            $series_id = $series['fields']['series'][0];

            foreach ($series['inner_hits']['file-name']['hits']['hits'] as $inner_item) {
                $inner_fields = $inner_item['fields'];
                $catalog = $this->catalogRepository->findOneByFilename($inner_fields['file-name'][0]);

                $hit = (new SearchResultItem())
                    ->setSeries($series_id)
                    ->setLangAlias($catalog->getLang()->getAlias())
                    ->setByteSize($inner_fields['file-size'][0])
                    ->setDownloadLink($this->router->generate('app_catalogs_pdf_show', [
                        'name' => $catalog->getFilename()
                    ], UrlGeneratorInterface::ABSOLUTE_URL))
                    ->setOriginName($catalog->getOriginFilename())
                    ->setSuggestText(
                        implode("\n", $inner_item['highlight']['suggest-text-content'])
                    );

                $item[] = $hit;
            }

            $items[$series_id] = $item;
        }

        return $items;
    }

}