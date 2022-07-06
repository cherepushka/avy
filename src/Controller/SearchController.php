<?php

namespace App\Controller;

use App\Service\SearchService;
use Doctrine\ORM\NonUniqueResultException;
use Elastic\Elasticsearch\Exception\ElasticsearchException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class SearchController extends AbstractController
{

    public function __construct(
        private readonly SearchService $searchService
    ){}

    /**
     * @throws ElasticsearchException
     * @throws NonUniqueResultException
     */
    #[Route('/search/result', name: 'app_search_result', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $search_text = trim($request->query->get('search_text'));

        $catalogs = $this->searchService->searchDefault($search_text);

        return $this->render('shared/pages/search_results.html.twig', [
            'current_page' => 1,
            'pages' => 20,
            'search_text' => $search_text,
            'catalog_items' => $catalogs->getItems(),
        ]);
    }

    #[Route('/search/result/{page}', name: 'app_search_result_pages', requirements: ['page' => '\d+'], methods: ['GET'])]
    public function pages(Request $request, int $page): Response
    {
        if ($page < 1)
            $page = 1;
        elseif ($page > 20)
            $page = 20;

        $search_text = trim($request->query->get('search_text'));

        $catalogs = $this->searchService->searchDefault($search_text);

        return $this->render('shared/pages/search_results.html.twig', [
            'current_page' => $page,
            'pages' => 20,
            'search_text' => $search_text,
            'catalog_items' => $catalogs->getItems(),
        ]);
    }
}
