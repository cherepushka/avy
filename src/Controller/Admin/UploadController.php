<?php

namespace App\Controller\Admin;

use App\Service\Elasticsearch;
use App\Service\Pdf\Parser;
use App\Service\Upload\PdfCatalogSaver;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UploadController extends AbstractController
{

    #[Route('/upload', name: 'admin_document_upload_form', methods: ['GET'])]
    public function upload_form(): Response
    {
        return $this->render('admin/pages/upload_form.html.twig');
    }

    /**
     * @throws Exception
     */
    #[Route('/upload', name: 'admin_document_upload', methods: ['POST'])]
    public function upload_document(
        Request         $request,
        PdfCatalogSaver $fileSaver,
        Parser          $pdfParser,
        Elasticsearch   $elasticsearch
    ): JsonResponse
    {
        foreach ($request->files->get('documents') as $document) {
            $filepath = $fileSaver->save($document);
            $text = $pdfParser->textFromFile($filepath);

            $elastic_response = $elasticsearch->uploadDocument(
                explode(".pdf", $document->getClientOriginalName())[0],
                filesize($filepath),
                $text
            );
            $elastic_response_code = $elastic_response->getStatusCode();

            if ($elastic_response_code < 200 || $elastic_response_code > 299){
                unlink($filepath);
                return new JsonResponse(['file' => $filepath, 'message' => 'upload error.']);
            }
        }

        return new JsonResponse(['message' => 'upload successful!']);
    }

}