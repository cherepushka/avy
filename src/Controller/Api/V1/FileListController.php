<?php

namespace App\Controller\Api\V1;

use App\Service\FileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1', name: 'app_api_v1')]
class FileListController extends AbstractController
{
    #[Route('/files-list/category/{category_id}', name: '_file_list', methods: ['GET'])]
    public function byCategoryId(
        int $category_id,
        FileService $fileService,
    ): Response
    {
        $result = $fileService->getFilesInCategoryGroupedByType($category_id);

        return $this->json($result->getItems());
    }
}
