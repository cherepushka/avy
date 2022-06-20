<?php

namespace App\Mapper\Elasticsearch;

use App\Model\Elasticsearch\Default\SearchResultList;
use App\Model\Elasticsearch\SearchResultItem;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SearchDefaultMapper extends AbstractResponseMapper
{

    /**
     * @throws NonUniqueResultException
     */
    public function map(array $elastic_response): SearchResultList
    {
        $items = [];
        foreach ($elastic_response['hits']['hits'] as $hit){
            $fields = $hit['fields'];
            $catalog = $this->catalogRepository->findOneByFilename($fields['file-name'][0]);

            $items[] = (new SearchResultItem())
                ->setSuggestText(implode("\n", $hit['highlight']['suggest-text-content']))
                ->setOriginName($catalog->getOriginFilename())
                ->setDownloadLink($this->router->generate('app_catalogs_pdf_show', [
                    'name' => $catalog->getFilename()
                ], UrlGeneratorInterface::ABSOLUTE_URL))
                ->setByteSize($fields['file-size'][0])
                ->setLangAlias($catalog->getLang()->getAlias());
        }

        return new SearchResultList($items);
    }

}