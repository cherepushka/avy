<?php

namespace App\Controller\Admin;

use App\Service\Elasticsearch;
use App\Service\Upload\PdfCatalogParser;
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
        Request             $request,
        PdfCatalogSaver     $fileSaver,
        PdfCatalogParser    $fileParser,
        Elasticsearch       $elasticsearch
    ): JsonResponse
    {
        //TODO сделать валидацию расширения файла

        $file = $request->files->get('download');
        $filepath = $fileSaver->save($file);

        $clean_text = $fileParser->filter_content_from_file($filepath);

        $elastic_response = $elasticsearch->uploadDocument(
            explode(".pdf", $file->getClientOriginalName())[0],
            filesize($filepath),
            $clean_text
        );
        $elastic_response_code = $elastic_response->getStatusCode();

        if ($elastic_response_code >= 200 && $elastic_response_code <= 299){
            return new JsonResponse(['file' => $filepath, 'message' => 'upload successful!']);
        } else {
            return new JsonResponse(['file' => $filepath, 'message' => 'upload error.']);
        }
    }

}