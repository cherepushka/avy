<?php

namespace App\Controller\Api;

use App\Service\Elasticsearch;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api', name: 'app_api')]
class SearchController extends AbstractController
{

    public function __construct(
        private readonly Elasticsearch $elasticsearch
    ){}

    /**
     * @throws ServerResponseException
     * @throws ClientResponseException
     * @throws MissingParameterException
     */
    #[Route('/search/highlight', name: '_search_highlight')]
    public function search(Request $request): JsonResponse
    {
        if (empty($request->toArray())){
            return $this->json([]);
        }
        $search_text = $request->toArray()['search'];

        $items = $this->elasticsearch->suggests($search_text);

        return $this->json($items);
    }

}
