<?php

namespace App\Controller\Api\V1;

use App\Service\Elasticsearch;
use App\Service\SearchService;
use Doctrine\ORM\NonUniqueResultException;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ElasticsearchException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/v1', name: 'app_api')]
class SearchController extends AbstractController
{

    public function __construct(
        private readonly Elasticsearch $elasticsearch,
        private readonly SearchService $searchService,
    ){}

    /**
     * @throws ElasticsearchException
     */
    #[Route('/search/highlight', name: '_search_highlight', methods: ['POST'])]
    public function highlights(Request $request): JsonResponse
    {
        if (empty($request->toArray())){
            return $this->json([]);
        }
        $search_text = $request->toArray()['search'];

        $items = $this->elasticsearch->suggests($search_text);

        return $this->json($items);
    }

    /**
     * @throws ElasticsearchException
     */
    #[Route('/search/by-series', name: '_search', methods: ['POST'])]
    public function searchSeriesGrouping(Request $request): JsonResponse
    {
        if (empty($request->toArray()) || !isset($request->toArray()['search'])){
            return $this->json([
                'items' => []
            ]);
        }

        $search_text = $request->toArray()['search'];
        $items = $this->searchService->searchSeriesCollapse($search_text);

        return $this->json($items);
    }

}
