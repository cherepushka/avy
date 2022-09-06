<?php

namespace App\Controller\Api\V1;

use App\Attribute\RequestJson;
use App\Service\SearchService;
use Doctrine\ORM\NonUniqueResultException;
use Elastic\Elasticsearch\Exception\ElasticsearchException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Http\Request\Api\SearchBySeries as SearchBySeries;

#[Route('/api/v1', name: 'app_api')]
class SearchController extends AbstractController
{

    public function __construct(
        private readonly SearchService $searchService,
    ){}

    /** @throws ElasticsearchException|NonUniqueResultException */
    #[Route('/search/by-series', name: '_search', methods: ['POST'])]
    public function searchSeriesGrouping(
        #[RequestJson] SearchBySeries\Entity $requestEntity
    ): JsonResponse
    {
        $search_text = $requestEntity->getSearch();
        $series = $requestEntity->getSeries();
        $page = isset($request_arr['page']) && is_int($request_arr['page']) ? $request_arr['page'] : 1;

        return $this->json(
            $this->searchService->searchSeriesCollapsed($search_text, $series, $page)
        );
    }

    #[Route('/search/product-suggests', name: '_product_suggests', methods: ['POST'])]
    public function productSuggests(Request $request): JsonResponse
    {
        $request_arr = $request->toArray();

        if (empty($request_arr) || !isset($request_arr['search'])){
            return $this->json([]);
        }

        $items = $this->searchService->productSuggests($request_arr['search']);
        return $this->json($items);
    }

}
