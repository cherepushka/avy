<?php

namespace App\Controller\Admin;

use App\Service\CatalogService;
use App\Service\CategoryTree;
use App\Service\OCR\OcrVisionInterface;
use App\Service\Pdf\ImageBuilder;
use App\Service\LanguageService;
use App\Service\ManufacturerService;
use App\Service\Pdf\CatalogFileService;
use App\Service\Pdf\TextParser;
use Doctrine\ORM\NonUniqueResultException;
use Elastic\Elasticsearch\Exception\ElasticsearchException;
use Exception;
use App\Service\Elasticsearch;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UploadController extends AbstractController
{

    public function __construct()
    {

    }

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
        TextParser          $textParser,
        CategoryTree        $categoryTree,
    ): JsonResponse|Response
    {
        ini_set('max_execution_time', 100);
        $document = $request->files->get('document');

        $new_document = [];
        $filepath = $fileHandler->saveUploadedFile($document);

        $new_document['text'] = $textParser->parseFromFile($filepath);
        $new_document['filename'] = (new File($filepath))->getBasename();
        $new_document['origin_filename'] = $document->getClientOriginalName();

        $manufacturers = $manufacturerService->getAll();
        $languages = $languageService->getAll();

        return $this->render('admin/pages/upload_confirm_form.html.twig', [
            'document'     => $new_document,
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
    ): RedirectResponse
    {
        //TODO сделать добавление в базу миграцией

        $file_data = $request->request->all();
        $categories_ids = explode(',', $file_data['category_ids']);

        if (empty($categories_ids)) {
            $this->addFlash('error_messages', 'Все каталоги были успешно загружены');
            return $this->redirectToRoute('admin_document_confirm_upload');
        }

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
            $this->addFlash('error_messages', 'Произшла ошибка при загрузке');
            return $this->redirectToRoute('admin_document_confirm_upload');
        }

        $this->addFlash('success_messages', 'Все каталоги были успешно загружены');
        return $this->redirectToRoute('admin_document_upload_form');
    }

    #[Route('/check/elastic/configuration/', name: 'admin_elastic_configuration')]
    public function check_elastic_connection(Elasticsearch $elasticsearch): JsonResponse
    {
        return new JsonResponse(var_dump($elasticsearch));
    }
}