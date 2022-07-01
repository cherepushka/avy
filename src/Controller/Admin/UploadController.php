<?php

namespace App\Controller\Admin;

use App\Service\CatalogService;
use App\Service\CategoryTree;
use App\Service\LanguageService;
use App\Service\ManufacturerService;
use App\Service\ParseQueueService;
use App\Service\Pdf\CatalogFileService;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use App\Service\Elasticsearch;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Throwable;

class UploadController extends AbstractController
{

    public function __construct(
        private readonly ParseQueueService $parseQueueService
    ){}

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
    ): JsonResponse|Response
    {
        $document = $request->files->get('document');

        $this->parseQueueService->enqueueFile($document);

        $this->addFlash('success_messages', 'Файл успешно добавлен в очередь на загрузку');
        return $this->redirectToRoute('admin_document_upload_form');
    }

    #[Route('/catalogs/parsed', 'admin_parsed_documents_list', methods: ['GET'])]
    public function parsed_documents_list(
        ManufacturerService $manufacturerService,
        LanguageService     $languageService,
        CategoryTree        $categoryTree,
    ): Response
    {
        return $this->render('admin/pages/upload_confirm_form.html.twig', [
            'documents'     => $this->parseQueueService->getAllParsed(),
            'manufacturers' => $manufacturerService->getAll(),
            'languages'     => $languageService->getAll(),
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
        $files_data = $request->request->all();

        foreach ($files_data as $file_data) {
            $categories_ids = explode(',', $file_data['category_ids']);

            if (empty($categories_ids)) {
                $this->addFlash('error_messages', 'Нужно указать хотя бы 1 категорию');
                return $this->redirectToRoute('admin_document_confirm_upload');
            }

            $catalog_path = $catalogFile->getTmpCatalogPath($file_data['filename']);;
            $byte_size = filesize($catalog_path);

            $catalogID = $catalogService->insertCatalog(
                $file_data['filename'],
                $file_data['origin_filename'],
                $file_data['manufacturer'],
                $categories_ids,
                $file_data['lang'],
                $byte_size
            );

            try {
                $elasticsearch->uploadDocument(
                    $catalogID,
                    $file_data['filename'],
                    $byte_size,
                    $file_data['text'],
                    $categories_ids
                );

                $this->parseQueueService->dequeueFile($file_data['filename']);
            } catch (Throwable) {
                $this->addFlash('error_messages', 'Произшла ошибка при загрузке');
                return $this->redirectToRoute('admin_document_confirm_upload');
            }
        }

        $this->addFlash('success_messages', 'Все каталоги были успешно загружены');
        return $this->redirectToRoute('admin_document_upload_form');
    }

}