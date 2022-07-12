<?php

namespace App\Controller\Api\V1;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1', name: 'app_api')]
class SeriesController extends AbstractController{

    public function __construct(

    ){}

    #[Route('/series/upload-existing', name: '_series_upload-existing', methods: ['PUT'])]
    public function uploadSeries(Request $request)
    {
        $data = $request->toArray();
    }

}