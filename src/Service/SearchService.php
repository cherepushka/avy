<?php

namespace App\Service;

use App\Model\SearchResultItem;
use App\Model\SearchResultList;
use App\Repository\CatalogRepository;
use Doctrine\ORM\NonUniqueResultException;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ElasticsearchException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SearchService
{

    public function __construct(
        private readonly Elasticsearch $elasticsearch,
        private readonly CatalogRepository $catalogRepository,
        private readonly UrlGeneratorInterface $router,
    ){}

    /**
     * @throws ElasticsearchException|NonUniqueResultException
     */
    public function search(string $text): SearchResultList
    {
        $elastic_response = $this->elasticsearch->search($text);

        return $this->mapElasticHits($elastic_response['hits']['hits']);
    }

    /**
     * @throws ElasticsearchException
     */
    public function searchSeriesCollapse(string $text): array
    {
        $elastic_response = $this->elasticsearch->searchCollapseBySeries($text);

        $items = [];

        foreach ($elastic_response['hits']['hits'] as $series){
            $item = [];

            foreach ($series['inner_hits']['file-name']['hits']['hits'] as $inner_item) {
                $item[] = $inner_item['fields']['file-name'][0];
            }

            $items[$series['fields']['series'][0]] = $item;
        }

        return $items;
    }

    /**
     * @throws NonUniqueResultException
     */
    private function mapElasticHits(array $hits): SearchResultList
    {
        $items = [];
        foreach ($hits as $hit){
            $source = $hit['_source'];
            $catalog = $this->catalogRepository->findOneByFilename($source['file-name']);

            $items[] = (new SearchResultItem())
                ->setSuggestText($source['suggest-hints'])
                ->setOriginName($catalog->getOriginFilename())
                ->setDownloadLink($this->router->generate('app_catalogs_pdf_show', [
                    'name' => $catalog->getFilename()
                ]))
                ->setByteSize($source['file-size'])
                ->setLangAlias($catalog->getLang()->getAlias());
        }

        return new SearchResultList($items);
    }

}