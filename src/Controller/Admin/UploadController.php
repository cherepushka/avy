<?php

namespace App\Controller\Admin;

use App\Service\CatalogService;
use App\Service\LanguageService;
use App\Service\ManufacturerService;
use App\Service\Pdf\CatalogFile;
use Doctrine\ORM\NonUniqueResultException;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Exception;
use App\Service\Pdf\CatalogParser;
use App\Service\Elasticsearch;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UploadController extends AbstractController
{

    #[Route('/catalogs/upload', name: 'admin_document_upload_form', methods: ['GET'])]
    public function upload_form(): Response
    {
        return $this->render('admin/pages/upload_form.html.twig', [
            'max_file_uploads' => ini_get('max_file_uploads'),
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/catalogs/upload', name: 'admin_document_upload', methods: ['POST'])]
    public function upload_document(
        Request             $request,
        CatalogFile         $fileHandler,
        CatalogParser       $pdfParser,
        ManufacturerService $manufacturerService,
        LanguageService     $languageService
    ): JsonResponse|Response
    {
        $documents = [];

        /** @var UploadedFile $document */
        foreach ($request->files->get('documents') as $document) {
            $new_document = [];
            $filepath = $fileHandler->saveUploadedFile($document);

            $new_document['text'] = $pdfParser->textFromFile($filepath);
            $new_document['filename'] = (new File($filepath))->getBasename();
            $new_document['origin_filename'] = $document->getClientOriginalName();
            $documents[] = $new_document;
        }

        $manufacturers = $manufacturerService->getAll();
        $languages = $languageService->getAll();

        return $this->render('admin/pages/upload_confirm_form.html.twig', [
            'documents'     => $documents,
            'manufacturers' => $manufacturers,
            'languages'     => $languages,
        ]);
    }

    /**
     * @throws ClientResponseException
     * @throws NonUniqueResultException
     * @throws ServerResponseException
     * @throws MissingParameterException
     */
    #[Route('/catalogs/confirm-upload', name: 'admin_document_confirm_upload', methods: ['POST'])]
    public function confirm_upload_document(
        Request             $request,
        Elasticsearch       $elasticsearch,
        CatalogService      $catalogService,
        CatalogFile         $catalogFile
    ): Response
    {
        //TODO сделать добавление в базу миграцией

        $files_data = $request->request->all();

        foreach ($files_data as $file_data) {

            $catalogService->insertCatalog(
                filename: $file_data['filename'],
                origin_filename: $file_data['origin_filename'],
                manufacturer_name: $file_data['manufacturer'],
                series: $file_data['series'],
                language_name: $file_data['lang']
            );

            $catalog_path = $catalogFile->getCatalogPath($file_data['filename']);

            $elastic_response = $elasticsearch->uploadDocument(
                $file_data['filename'],
                filesize($catalog_path),
                mb_convert_encoding($file_data['text'], 'UTF-8', 'UTF-8')
            );
            $elastic_response_code = $elastic_response->getStatusCode();

            if ($elastic_response_code < 200 || $elastic_response_code > 299){
                unlink($catalog_path);
                return new JsonResponse(['file' => $catalog_path, 'message' => 'upload error.']);
            }
        }

        return $this->render('admin/pages/upload_form.html.twig', [
            'max_file_uploads' => ini_get('max_file_uploads'),
        ]);
    }

}