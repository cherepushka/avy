<?php

namespace App\Controller\Admin;

use App\Service\CatalogService;
use App\Service\CategoryTree;
use App\Service\LanguageService;
use App\Service\ManufacturerService;
use App\Service\Elasticsearch;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Http\Request\AdminCatalogUpload as AdminCatalogUploadForm;
use App\Repository\CategoryRepository;
use Throwable;

class UploadController extends AbstractController
{

    public function __construct(){}

    #[Route('/catalogs/upload', 'admin_document_upload_form', methods: ['GET'])]
    public function upload_form(
        ManufacturerService $manufacturerService,
        LanguageService     $languageService,
        CategoryTree        $categoryTree,
    ): Response
    {
        return $this->render('admin/pages/upload_form.html.twig', [
            'manufacturers' => $manufacturerService->getAll(),
            'languages'     => $languageService->getAll(),
            'category_tree' => $categoryTree->getRemoteTree()
        ]);
    }

    #[Route('/catalogs/confirm-upload', name: 'admin_document_confirm_upload', methods: ['POST'])]
    public function confirm_upload_document(
        Request $request,
        Elasticsearch $elasticsearch,
        CatalogService $catalogService,
        CategoryRepository $categoryRepository,
    ): RedirectResponse
    {
        ini_set('max_execution_time', 0);

        $formEntity = new AdminCatalogUploadForm\Entity();
        $form = $this->createForm(AdminCatalogUploadForm\FormType::class, $formEntity, ['csrf_protection' => false]);
        $form->submit([
            'lang' => $request->get('lang'),
            'text' => $request->get('text'),
            'originFilename' => $request->get('originFilename'),
            'manufacturer' => $request->get('manufacturer'),
            'categoryIds' => $request->get('categoryIds'),
            'file' => $request->files->get('file'),
        ]);

        if (!$form->isSubmitted() || !$form->isValid()) {
            foreach($form->getErrors(true) as $errorMessage){
                $this->addFlash('error_messages', $errorMessage->getMessage());
            }
            return $this->redirectToRoute('admin_document_upload_form');
        }

        try{
            $catalog = $catalogService->insertCatalog(
                $formEntity->getFile(),
                $formEntity->getOriginFilename() . '.pdf',
                $formEntity->getManufacturer(),
                $formEntity->getCategoryIds(),
                $formEntity->getLang(),
                $formEntity->getText()
            );

            $final_cats = $categoryRepository->findWithoutChildren($formEntity->getCategoryIds());
            $catalog_cats = $categoryRepository->findBy(['id' => $formEntity->getCategoryIds()]);

            $elasticsearch->uploadDocument(
                $catalog->getFilename(),
                $catalog->getOriginFilename(),
                $catalog->getByteSize(),
                $catalog->getLang()->getAlias(),
                $catalog->getText(),
                $catalog_cats,
                $final_cats,
            );
        } catch (Throwable $e) {

            $this->addFlash('error_messages', 'Произшла ошибка при загрузке: ' . $e->getMessage());
            return $this->redirectToRoute('admin_document_upload_form');
        }

        $this->addFlash('success_messages', 'Файл был успешно добавлен в очередь на загрузку');
        return $this->redirectToRoute('admin_document_upload_form');
    }

}