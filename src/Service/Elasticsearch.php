<?php

namespace App\Service;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Exception\AuthenticationException;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Elasticsearch\Response\Elasticsearch as Elasticsearch_Response;
use GuzzleHttp\Client as GuzzleClient;
use Http\Promise\Promise;
use RuntimeException;

class Elasticsearch
{

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
                throw new RuntimeException('Elasticsearch connection type don`t specified');
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
                '_source' => [
                    'suggest-hints',
                    'file-name',
                    'file-url',
                    'file-size'
                ],
                'query' => [
                    'match' => [
                        "suggest-text-content" => $text
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
    public function suggests(string $text)
    {
        $response = $this->client->search([
            'index' => 'catalogs',
            'body' => [
                "suggest" => [
                    "highlight-suggest" => [
                        "prefix" => $text,
                        "phrase" => [
                            "field" => "suggest-hints",
                            "highlight" => [
                                "pre_tag" => "<highlight>",
                                "post_tag" => "<highlight>"
                            ]
                        ]
                    ]
                ]
            ]
        ])->asArray();

        $items = [];
        foreach ($response['suggest']['highlight-suggest'] as $item) {
            foreach ($item['options'] as $option) {
                $items[] = $option['highlighted'];
            }
        }
        return $items;
    }

    /**
     * @throws ClientResponseException
     * @throws ServerResponseException
     * @throws MissingParameterException
     */
    public function uploadDocument(string $filename, int $filesize, string $elastic_content): Elasticsearch_Response|Promise
    {
        return $this->client->create([
            'id' => uniqid(),
            'index' => 'catalogs',
            'body' => [
                'suggest-completion' => explode(" ", $elastic_content),
                'suggest-hints' => $elastic_content,
                'suggest-text-content' => $elastic_content,
                'file-name' => $filename,
                'file-size' => $filesize
            ]
        ]);
    }

}