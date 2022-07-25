<?php

namespace App\Controller;

use App\Service\Pdf\Storage\StorageServiceFacade;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class PdfCatalogs extends AbstractController
{

    #[Route('/catalogs/pdf/show/{name}', name: 'app_catalogs_pdf_show', methods: ['GET'])]
    public function show(
        Request     $request,
        StorageServiceFacade $storageServiceFacade
    ): Response
    {
        $catalogName = $request->attributes->get('name');

        $fileContent = $storageServiceFacade->getRawContentFromCatalogFile($catalogName);

        $response = new Response($fileContent);
        $response->headers->set('Content-Type', 'application/pdf');

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $catalogName
        );
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

}