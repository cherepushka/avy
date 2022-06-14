<?php

namespace App\Controller;

use App\Service\Elasticsearch;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class SearchController extends AbstractController
{

    public function __construct(
        private readonly Elasticsearch $elasticsearch
    ){}

    /**
     * @throws ClientResponseException
     * @throws ServerResponseException
     * @throws MissingParameterException
     */
    #[Route('/search/result', name: 'app_search_result')]
    public function index(Request $request): Response
    {
        $search_text = ($request->request->all())['search_text'];

        $items = $this->elasticsearch->search($search_text);

        $catalogs = $items['hits']['hits'];

        return $this->render('shared/pages/search_result.html.twig', [
            'catalog_items' => $catalogs,
        ]);
    }
}
