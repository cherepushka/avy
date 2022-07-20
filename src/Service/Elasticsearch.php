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
use Elastic\Elasticsearch\Response\Elasticsearch as Elasticsearch_Response;
use GuzzleHttp\Client as GuzzleClient;
use Http\Promise\Promise;

class Elasticsearch
{

    const PRE_TAG = '<highlight>';
    const POST_TAG = '</highlight>';
    const STD_INNER_HITS_SIZE = 5;
    const STD_LANG = 'rus';
    const STD_SEARCH_FIELDS = ['file-size', 'file-name', 'series'];

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
     * @return array
     *
     * @throws ClientResponseException
     * @throws MissingParameterException
     * @throws ServerResponseException
     */
    public function search(string $text, int $from = 0): array
    {
        return $this->client->search([
            'index' => 'catalogs',
            'body' => [
                '_source' => false,
                'from' => $from,
                'fields' => self::STD_SEARCH_FIELDS,
                'query' => [
                    'match' => [
                        "suggest-text-content" => $text
                    ]
                ],
                'highlight' => [
                    'fields' => [
                        'suggest-text-content' => [
                            'pre_tags' => self::PRE_TAG,
                            'post_tags' => self::POST_TAG
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
        return $this->client->search([
            'index' => 'catalogs',
            'size' => 0,
            'body' => [
                '_source' => false,
                'query' => [
                    'bool' => [
                        'filter' => [
                            'terms' => [
                                'series' => $series_ids
                            ]
                        ],
                        'must' => [
                            'match' => [
                                "suggest-text-content" => $text
                            ]
                        ]
                    ]
                ],
                "aggs" => [
                    "series_grouping" => [
                        "terms" => [
                            "field" => "series",
                        ],
                        "aggs" => [
                            "items" => [
                                "top_hits" => [
                                    "size" => self::STD_INNER_HITS_SIZE,
                                    "fields" => self::STD_SEARCH_FIELDS,
                                    "_source" => false,
                                    'highlight' => [
                                         'fields' => [
                                            'suggest-text-content' => [
                                                'pre_tags' => self::PRE_TAG,
                                                'post_tags' => self::POST_TAG
                                            ]
                                        ]
                                    ],
                                    'sort' => [
                                        'exists-products' => 'desc'
                                    ],
                                ]
                            ]
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
     * @throws ServerResponseException
     * @throws ClientResponseException
     * @throws MissingParameterException
     */
    public function suggestsDefault(string $text): array
    {
        return $this->client->search([
            'index' => 'catalogs',
            'body' => [
                "suggest" => [
                    "highlight-suggest" => [
                        "prefix" => $text,
                        "completion" => [
                            "field" => "suggest-completion",
//                            "highlight" => [
//                                "pre_tag" => self::PRE_TAG,
//                                "post_tag" => self::POST_TAG
//                            ]
                        ]
                    ]
                ]
            ]
        ])->asArray();
    }

    /**
     * @param int $id
     * @param string $filename
     * @param int $filesize
     * @param string $elastic_content
     * @param int[] $categories - ids of categories
     * @param string $lang - lang alias
     * @param Category[] $series - Categories without child Categories
     *
     * @throws ElasticsearchException
     */
    public function uploadDocument(
        int $id,
        string $filename,
        int $filesize,
        string $elastic_content,
        string $lang,
        array $category_ids,
        array $series_ids
    ): void
    {
        $this->client->create([
            'id' => $id,
            'index' => 'catalogs',
            'body' => [
                'suggest-completion' => $elastic_content,
                'suggest-text-content' => $elastic_content,
                'file-name' => $filename,
                'file-size' => $filesize,
                'file-lang' => $lang,
                'exists-products' => true,
                'categories' => $category_ids,
                'series' => $series_ids,
            ]
        ]);
    }

}