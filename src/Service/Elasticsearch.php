<?php

namespace App\Service;

use App\Entity\Category;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Exception\AuthenticationException;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ElasticsearchException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use GuzzleHttp\Client as GuzzleClient;

class Elasticsearch
{

    const PRE_TAG = '<highlight>';
    const POST_TAG = '</highlight>';
    const STD_INNER_HITS_SIZE = 5;
    const STD_LANG = 'rus';
    const STD_SEARCH_FIELDS = ['file-size', 'file-name', 'series', 'suggest-text'];

    private Client $client;

    /**
     * @throws AuthenticationException
     */
    public function __construct(
        string $elasticsearch_connection_type,
        string $elasticsearch_host,
        string $elasticsearch_user,
        string $elasticsearch_password,
        string $elasticsearch_cloud_id,
        string $elasticsearch_api_key
    ) {
        $Elasticsearch_client = ClientBuilder::create();
        $Elasticsearch_client->setHttpClient(new GuzzleClient());

        switch ($elasticsearch_connection_type){
            case 'PASSWORD':
                $Elasticsearch_client
                    ->setHosts([$elasticsearch_host])
                    ->setBasicAuthentication($elasticsearch_user, $elasticsearch_password);
                break;
            case 'API_KEY':
                $Elasticsearch_client
                    ->setElasticCloudId($elasticsearch_cloud_id)
                    ->setApiKey($elasticsearch_api_key);
                break;
            default:
                throw new AuthenticationException('Elasticsearch connection type don`t specified');
        }

        $this->client = $Elasticsearch_client->build();
    }

    /**
     * @param string $text
     * @param int $from
     * @return array
     *
     * @throws ClientResponseException
     * @throws MissingParameterException
     * @throws ServerResponseException
     */
    public function searchGlobal(string $text, int $from = 0): array
    {
        return $this->client->search([
            'index' => 'catalogs_alias',
            'body' => [
                '_source' => false,
                'from' => $from,
                'fields' => self::STD_SEARCH_FIELDS,
                'query' => [
                    "multi_match" => [
                        "query" => $text,
                        "fields" => [
                            "categories-full-text^1.5",
                            "text-content",
                            "text-content.tengram"
                        ]
                    ]
                ],
                'highlight' => [
                    'fields' => [
                        "text-content" => [
                            "pre_tags" => self::PRE_TAG,
                            "post_tags" => self::POST_TAG
                        ],
                        "categories-full-text" => [
                            "pre_tags" => self::PRE_TAG,
                            "post_tags" => self::POST_TAG
                        ]
                    ],
                ]
            ]
        ])->asArray();
    }

    /**
     * Search in 'catalogs' index and collapsing result on 'series' field
     *
     * @throws ElasticsearchException
     */
    public function searchCollapseBySeries(string $text, array $series_ids, int $series_size, int $from): array
    {
        $fields = self::STD_SEARCH_FIELDS;
        $fields[] = 'categories-full-text';

        return $this->client->search([
            'index' => 'catalogs-seria-_alias',
            'ignore_unavailable' => true,
            'body' => [
                '_source' => false,
                'from' => $from,
                'size' => $series_size,
                'query' => [
                    'bool' => [
                        "filter" => [
                            "terms" => [
                                "series" => $series_ids
                            ]
                        ],
                        "must"=> [
                            [
                                "multi_match" => [
                                    "query" => $text,
                                    "fields" => [
                                        "categories-full-text^1.5",
                                        "text-content",
                                        "text-content._tengram",
                                        "text-content._search-as-you-type._2gram",
                                        "text-content._search-as-you-type._3gram"
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'collapse' => [
                    'field' => 'series',
                    'inner_hits' => [
                        '_source' => false,
                        'fields' => $fields,
                        'name' => 'file-name',
                        'size' => self::STD_INNER_HITS_SIZE,
                        'highlight' => [
                            'fields' => [
                                'text-content' => [
                                    'pre_tags' => self::PRE_TAG,
                                    'post_tags' => self::POST_TAG
                                ],
                                "categories-full-text" => [
                                    "pre_tags" => self::PRE_TAG,
                                    "post_tags" => self::POST_TAG
                                ]
                            ]
                        ]
                    ],
                    'max_concurrent_group_searches' => 3
                ],
                'sort' => [
                    'exists-products' => 'desc'
                ],
                "aggs" => [
                    "total" => [
                        "cardinality" => [
                            "field" => "series"
                        ]
                    ]
                ]
            ]
        ])->asArray();
    }

    /**
     * Fetching search suggests in `catalogs` index
     *
     * @param string $text
     * @return array
     *
     * @throws ElasticsearchException
     */
    public function suggestsGlobal(string $text): array
    {
        return $this->client->search([
            'index' => 'catalogs_alias',
            'body' => [
                "_source" => false,
                "query" => [
                    "multi_match" => [
                        "query" => $text,
                        "type" => "bool_prefix",
                        "fields" => [
                            "text-content.suggest-completion",
                            "text-content.suggest-completion._index_prefix",
                            "text-content.trigram"
                        ]
                    ]
                ],
                "highlight" => [
                    "fields" => [
                        "text-content.trigram" => [
                            "number_of_fragments" => 1,
                            "fragment_size" => 100
                        ]
                    ]
                ]
            ]
        ])->asArray();
    }

    /**
     * @param string $filename
     * @param int $filesize
     * @param string $elastic_content
     * @param string $suggest_text
     * @param string $lang - lang alias
     * @param int[] $category_ids - ids of categories
     * @param Category[] $final_cats - Categories without child Categories (final categories)
     * @param string $categories_text - titles of all categories, imploded to string
     *
     * @throws ElasticsearchException
     */
    public function uploadDocument(
        string $filename,
        int $filesize,
        string $elastic_content,
        string $suggest_text,
        string $lang,
        array $category_ids,
        array $final_cats,
        array $categories_text,
    ): void
    {
        $series_ids = [];
        $global_is_product = false;
        foreach ($final_cats as $seria){

            $series_ids[] = $seria->getId();
            $global_is_product = $seria->isProductsExist() ? true : $global_is_product;
        }

        $this->client->create([
            'id' => uniqid(),
            'index' => 'catalogs',
            'body' => [
                'text-content' => $elastic_content,
                'suggest-text' => $suggest_text,
                'categories-full-text' => $categories_text,
                'file-name' => $filename,
                'file-size' => $filesize,
                'file-lang' => $lang,
                'exists-products' => $global_is_product,
                'categories' => $category_ids,
                'series' => $series_ids,
            ]
        ]);

        foreach ($final_cats as $seria) {

            $this->client->create([
                'id' => uniqid(),
                'index' => 'catalogs-seria-' . $seria->getId(),
                'body' => [
                    'text-content' => $elastic_content,
                    'suggest-text' => $suggest_text,
                    'categories-full-text' => $categories_text,
                    'file-name' => $filename,
                    'file-size' => $filesize,
                    'file-lang' => $lang,
                    'exists-products' => $global_is_product,
                    'categories' => $category_ids,
                    'series' => $seria->getId(),
                ]
            ]);
        }
    }

    public function productHints(string $text): array
    {
        return $this->client->search([
            'index' => 'product-suggests',
            'body' => [
                "_source" => false,
                "query" => [
                    "query_string" => [
                        "query" => $text,
                        "fields" => [
                            "value",
                            "value._search-as-you-type._2gram",
                            "value._search-as-you-type._3gram",
                            "value._concatenated-prefix"
                        ]
                    ]
                ],
                "collapse" => [
                    "field" => "type",
                    "inner_hits" => [
                        "_source"  => false,
                        "fields" => ["value"],
                        "name" => "value",
                        "size" => 2
                    ],
                    "max_concurrent_group_searches" => 4
                ]
            ]
        ])->asArray();
    }

    public function uploadProdustSuggest(string $text, string $type)
    {
        $this->client->create([
            'id' => uniqid(),
            'index' => 'product-suggests',
            'body' => [
                'value' => $text,
                'type' => $type,
            ]
        ]);
    }
}