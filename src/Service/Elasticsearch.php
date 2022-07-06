<?php

namespace App\Service;

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
    const INNER_HITS_SIZE = 5;
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
    public function search(string $text): array
    {
        return $this->client->search([
            'index' => 'catalogs',
            'body' => [
                '_source' => false,
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
                    ]
                ]
            ]
        ])->asArray();
    }

    public function elastic_index_request(): array
    {
        return $this->client->search([
            'index' => 'catalogs',
            'body' => []
        ])->asArray();
    }

    /**
     * Search in 'catalogs' index and collapsing result on 'series' field
     *
     * @throws ElasticsearchException
     */
    public function searchCollapseBySeries(string $text, int $inner_hits_size = null): array
    {
        return $this->client->search([
            'index' => 'catalogs',
            'body' => [
                '_source' => false,
                'query' => [
                    'match' => [
                        "suggest-text-content" => $text
                    ]
                ],
                'collapse' => [
                    'field' => 'series',
                    'inner_hits' => [
                        '_source' => false,
                        'fields' => self::STD_SEARCH_FIELDS,
                        'name' => 'file-name',
                        'size' => $inner_hits_size ?? self::INNER_HITS_SIZE,
                        'highlight' => [
                            'fields' => [
                                'suggest-text-content' => [
                                    'pre_tags' => self::PRE_TAG,
                                    'post_tags' => self::POST_TAG
                                ]
                            ]
                        ]
                    ],
                    'max_concurrent_group_searches' => 3
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
     * @throws ElasticsearchException
     */
    public function uploadDocument(
        int $id,
        string $filename,
        int $filesize,
        string $elastic_content,
        array $series
    ): Elasticsearch_Response|Promise
    {
        return $this->client->create([
            'id' => $id,
            'index' => 'catalogs',
            'body' => [
                'suggest-completion' => $elastic_content,
                'suggest-text-content' => $elastic_content,
                'file-name' => $filename,
                'file-size' => $filesize,
                'series' => $series
            ]
        ]);
    }

}