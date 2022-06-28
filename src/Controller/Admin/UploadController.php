<?php

namespace App\Controller\Admin;

use App\Service\CatalogService;
use App\Service\CategoryTree;
use App\Service\OCR\OcrVisionInterface;
use App\Service\Pdf\ImageBuilder;
use App\Service\LanguageService;
use App\Service\ManufacturerService;
use App\Service\OCR\OCRVision;
use App\Service\Pdf\CatalogFileService;
use Doctrine\ORM\NonUniqueResultException;
use Elastic\Elasticsearch\Exception\ElasticsearchException;
use Exception;
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
        return $this->render('admin/pages/upload_form.html.twig');
    }

    /**
     * @throws Exception
     */
    #[Route('/catalogs/upload', name: 'admin_document_upload', methods: ['POST'])]
    public function upload_document(
        Request             $request,
        CatalogFileService  $fileHandler,
        ManufacturerService $manufacturerService,
        LanguageService     $languageService,
        CategoryTree        $categoryTree,
        ImageBuilder        $imageBuilder,
        OcrVisionInterface  $OCR
    ): JsonResponse|Response
    {
        $documents = [];

        /** @var UploadedFile $document */
        foreach ($request->files->get('documents') as $document) {
            $new_document = [];
            $filepath = $fileHandler->saveUploadedFile($document);

            $imageArray = $imageBuilder->generateImagickImages($filepath);
            $new_document['text'] = $OCR->findImageAnnotations($imageArray);
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
            'category_tree' => $categoryTree->getRemoteTree()
        ]);
    }

    /**
     * @throws NonUniqueResultException
     */
    #[Route('/catalogs/confirm-upload', name: 'admin_document_confirm_upload', methods: ['POST'])]
    public function confirm_upload_document(
        Request             $request,
        Elasticsearch       $elasticsearch,
        CatalogService      $catalogService,
        CatalogFileService  $catalogFile
    ): Response
    {
        //TODO сделать добавление в базу миграцией

        $files_data = $request->request->all();

        foreach ($files_data as $file_data) {
            $categories_ids = explode(',', $file_data['category_ids']);

            $catalogID = $catalogService->insertCatalog(
                $file_data['filename'],
                $file_data['origin_filename'],
                $file_data['manufacturer'],
                $categories_ids,
                $file_data['lang']
            );

            $catalog_path = $catalogFile->getCatalogPath($file_data['filename']);

            try {
                $elasticsearch->uploadDocument(
                    $catalogID,
                    $file_data['filename'],
                    filesize($catalog_path),
                    $file_data['text'],
                    $categories_ids
                );
            } catch (ElasticsearchException) {
                unlink($catalog_path);
                return new JsonResponse(['file' => $catalog_path, 'message' => 'upload error.']);
            }
        }

        return $this->redirectToRoute('admin_document_upload_form');
    }

}