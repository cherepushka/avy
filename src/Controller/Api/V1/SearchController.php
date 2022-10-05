<?php

namespace App\Controller\Api\V1;

use App\Attribute\RequestJson;
use App\Http\Request\Api\SearchBySeries;
use App\Http\Request\Api\SearchProductSuggests;
use App\Model\Elasticsearch\Default\SearchResultList;
use App\Model\Elasticsearch\ProductSuggestsList;
use App\Service\SearchService;
use Elastic\Elasticsearch\Exception\ElasticsearchException;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1', name: 'app_api_v1')]
class SearchController extends AbstractController
{
    public function __construct(
        private readonly SearchService $searchService,
    ) {
    }

    /**
     * @throws ElasticsearchException
     */
    #[Route('/search/by-series', name: '_search_by_series', methods: ['POST'])]
    #[OA\Response(
        response: 200,
        description: 'Возвращает PDF кактлоги, подходящие под текстовый запрос, сгруппированные по сериям',
        content: new OA\JsonContent(
            ref: new Model(type: SearchResultList::class)
        )
    )]
    #[OA\RequestBody(content: new OA\JsonContent(
        ref: new Model(type: SearchBySeries\Entity::class)
    ))]
    public function searchSeriesGrouping(#[RequestJson] SearchBySeries\Entity $requestEntity): JsonResponse
    {
        $search_text = $requestEntity->getSearch();
        $series = $requestEntity->getSeries();
        $page = $requestEntity->getPage();

        return $this->json(
            $this->searchService->searchSeriesCollapsed($search_text, $series, $page)
        );
    }

    /**
     * @throws ElasticsearchException
     */
    #[Route('/search/product-suggests', name: '_product_suggests', methods: ['POST'])]
    #[OA\Response(
        response: 200,
        description: 'Возвращает объект с неизвестным количеством свойств, 
                где название каждого свойства - название группы подсказок. А значение - массив из самих подсказок',
        content: new OA\JsonContent(ref: new Model(type: ProductSuggestsList::class))
    )]
    #[OA\RequestBody(content: new OA\JsonContent(
        ref: new Model(type: SearchProductSuggests\Entity::class)
    ))]
    public function productSuggests(#[RequestJson] SearchProductSuggests\Entity $requestEntity): JsonResponse
    {
        $searchText = $requestEntity->getSearch();

        $items = $this->searchService->productSuggests($searchText);

        return $this->json($items->getItems());
    }
}
