<?php

namespace App\Controller\Api\V1;

use App\Model\FileList\FileTypeGrouped\FileList;
use App\Service\FileService;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1', name: 'app_api_v1')]
class FileListController extends AbstractController
{
    #[OA\Response(
        response: 200,
        description: 'Фозвращает список файлов в определенной категории, сгруппированный по сериям',
        content: new OA\JsonContent(
            ref: new Model(type: FileList::class)
        )
    )]
    #[Route('/files-list/category/{category_id}', name: '_file_list', methods: ['GET'])]
    public function byCategoryId(
        int $category_id,
        FileService $fileService,
    ): Response {
        $result = $fileService->getFilesInCategoryGroupedByType($category_id);

        return $this->json($result->getItems());
    }
}
