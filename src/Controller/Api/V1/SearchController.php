<?php

namespace App\Controller\Api\V1;

use App\Service\SearchService;
use Doctrine\ORM\NonUniqueResultException;
use Elastic\Elasticsearch\Exception\ElasticsearchException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/v1', name: 'app_api')]
class SearchController extends AbstractController
{

    public function __construct(
        private readonly SearchService $searchService,
    ){}

    /** @throws ElasticsearchException */
    #[Route('/search/highlight', name: '_search_highlight', methods: ['POST'])]
    public function highlights(Request $request): JsonResponse
    {
        if (empty($request->toArray())){
            return $this->json([]);
        }
        $search_text = $request->toArray()['search'];

        $items = $this->searchService->suggestsDefault($search_text);

        return $this->json($items);
    }

    /** @throws ElasticsearchException|NonUniqueResultException */
    #[Route('/search/by-series', name: '_search', methods: ['POST'])]
    public function searchSeriesGrouping(Request $request): JsonResponse
    {
        $request_arr = $request->toArray();

        if (empty($request_arr) || !isset($request_arr['search']) || !isset($request_arr['series'])){
            return $this->json([
                'items' => []
            ]);
        }

        $search_text = $request_arr['search'];
        $series = explode(',', $request_arr['series']);
        $page = isset($request_arr['page']) && is_int($request_arr['page']) ? $request_arr['page'] : 1;
        $items = $this->searchService->searchSeriesCollapsed($search_text, $series, $page);

        return $this->json($items);
    }

}
