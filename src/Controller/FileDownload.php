<?php

namespace App\Controller;

use App\Service\Pdf\Storage\StorageServiceFacade;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class FileDownload extends AbstractController
{

    #[Route('/files/download/{name}', name: 'app_files_download', methods: ['GET'])]
    public function show(
        Request     $request,
        StorageServiceFacade $storageServiceFacade
    ): Response
    {
        $catalogName = $request->attributes->get('name');

        $fileContent = $storageServiceFacade->getRawContentFromCatalogFile($catalogName);

        $response = new Response($fileContent);
        //TODO отрефакторить это
        $response->headers->set('Content-Type', 'application/pdf');

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $catalogName
        );
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

}