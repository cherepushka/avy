<?php

namespace App\Controller;

use App\Service\Pdf\CatalogFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class PdfCatalogs extends AbstractController
{

    #[Route('/catalogs/pdf/show/{name}', name: 'app_catalogs_pdf_show', methods: ['GET'])]
    public function show(
        Request     $request,
        CatalogFile $fileHandler
    ): BinaryFileResponse
    {
        $catalogName = $request->attributes->get('name');

        $filepath = $fileHandler->getCatalogPath($catalogName);

        $response = new BinaryFileResponse($filepath);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $catalogName
        );

        return $response;
    }

}