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
        $current_page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? $_GET['page'] : 1;
        $search_text = trim($request->query->get('search_text'));

        $catalogs = $this->searchService->searchDefault($search_text, $current_page);

        return $this->render('shared/pages/search_results.html.twig', [
            'catalog_items' => $catalogs->getItems(),
            'max_pages' => $catalogs->getMaxPage()
        ]);
    }
}
