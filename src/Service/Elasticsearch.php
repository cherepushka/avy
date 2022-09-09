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
    const STD_SEARCH_FIELDS = ['file-size', 'file-name', 'origin-file-name', 'series', 'lang'];

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
            'index' => 'files_index_alias',
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

    public function searchCollapseBySeriesEmptySeries(string $text, int $series_size, int $from): array
    {
        $fields = self::STD_SEARCH_FIELDS;
        $fields[] = 'categories-full-text';

        return $this->client->search([
            'index' => 'files-seria-_index_alias',
            'ignore_unavailable' => true,
            'body' => [
                '_source' => false,
                'from' => $from,
                'size' => $series_size,
                'query' => [
                    "multi_match" => [
                        "query" => $text,
                        "fields" => [
                            "categories-full-text^1.5",
                            "text-content",
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
     * Search in 'catalogs' index and collapsing result on 'series' field
     *
     * @throws ElasticsearchException
     */
    public function searchCollapseBySeries(string $text, array $series_ids, int $series_size, int $from): array
    {
        $fields = self::STD_SEARCH_FIELDS;
        $fields[] = 'categories-full-text';

        return $this->client->search([
            'index' => 'files-seria-_index_alias',
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
     * @param string $filename
     * @param string $originFilename
     * @param int $byteSize - byte size of file
     * @param string $langAlias - language alias of catalog
     * @param string $catalogText
     * @param string $fileTypeAlias
     * @param Category[] $categories - ids of categories
     * @param Category[] $finalCats
     * @param string $indexPrefix - prefix on index in which new catalog will be uploaded
     *
     * @throws ElasticsearchException
     */
    public function uploadDocument(
        string $filename,
        string $originFilename,
        int $byteSize,
        string $langAlias,
        string $catalogText,
        string $fileTypeAlias,
        array $categories,
        array $finalCats,
        string $indexPrefix = '',
    ): void
    {
        $series_ids = [];
        $global_is_product = false;
        foreach ($finalCats as $seria){
            $series_ids[] = $seria->getId();
            $global_is_product = $seria->isProductsExist() ? true : $global_is_product;
        }

        $categories_ids = [];
        $categories_titles = [];
        foreach($categories as $category) {
            $categories_ids[] = $category->getId();
            $categories_titles[] = $category->getTitle();
        }

        $this->client->create([
            'id' => uniqid(),
            'index' => $indexPrefix . 'files',
            'body' => [
                'text-content' => $catalogText,
                'categories-full-text' => $categories_titles,
                'file-name' => $filename,
                'origin-file-name' => $originFilename,
                'lang' => $langAlias,
                'file-size' => $byteSize,
                'file-type' => $fileTypeAlias,
                'exists-products' => $global_is_product,
                'categories' => $categories_ids,
                'series' => $series_ids,
            ]
        ]);

        foreach ($finalCats as $seria) {

            $this->client->create([
                'id' => uniqid(),
                'index' => $indexPrefix . 'files-seria-' . $seria->getId(),
                'body' => [
                    'text-content' => $catalogText,
                    'categories-full-text' => $categories_titles,
                    'file-name' => $filename,
                    'origin-file-name' => $originFilename,
                    'lang' => $langAlias,
                    'file-size' => $byteSize,
                    'file-type' => $fileTypeAlias,
                    'exists-products' => $global_is_product,
                    'categories' => $categories_ids,
                    'series' => $seria->getId(),
                ]
            ]);
        }
    }

    /**
     * @param string $text - search text
     *
     * @throws ElasticsearchException
     */
    public function productSuggests(string $text): array
    {
        return $this->client->search([
            'index' => 'product-suggests_alias',
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

    /**
     * @throws ElasticsearchException
     */
    public function uploadProductSuggest(string $text, string $type, string $indexPrefix = ''): void
    {
        $this->client->create([
            'id' => uniqid(),
            'index' => $indexPrefix . 'product-suggests',
            'body' => [
                'value' => $text,
                'type' => $type,
            ]
        ]);
    }
}