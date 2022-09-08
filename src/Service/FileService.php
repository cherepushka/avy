<?php

namespace App\Service;

use App\Entity\FileStatus;
use App\Mapper\FileList\FileTypeGroupedListMapper;
use App\Model\FileList\FileTypeGrouped\FileList;
use App\Repository\FileTypeRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Entity\File;
use App\Exception\CategoryNotFoundByIdException;
use App\Repository\FileRepository;
use App\Repository\CategoryRepository;
use App\Repository\LanguageRepository;
use App\Repository\ManufacturerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use App\Model\File\CatalogFile;
use App\Exception\FileAlreadyLoadedException;
use App\Service\Pdf\Storage\StorageServiceFacade;

class FileService
{

    public function __construct(
        private readonly FileRepository $fileRepository,
        private readonly ManufacturerRepository $manufacturerRepository,
        private readonly LanguageRepository $languageRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly StorageServiceFacade $storageService,
        private readonly FileTypeRepository $fileTypeRepository,
        private readonly FileTypeGroupedListMapper $fileTypeGroupedListMapper,
    ){}

    /**
     * @throws NonUniqueResultException
     * @throws FileAlreadyLoadedException
     */
    public function insertCatalog(
        UploadedFile    $uploadedFile,
        string          $origin_filename,
        string          $manufacturer_name,
        array           $categories_ids,
        string          $language_name,
        string          $fileType,
        ?string         $text = null
    ): File
    {
        $file = $this->storageService->saveUploadedCatalog($uploadedFile);

        if ($this->isDocumentAlreadyLoaded($file)){
            $this->storageService->deleteCatalog($file->getName());
            throw new FileAlreadyLoadedException($file->getOriginName());
        }

        $manufacturer = $this->manufacturerRepository->findOneByName($manufacturer_name);
        $language = $this->languageRepository->findOneByName($language_name);
        $fileType = $this->fileTypeRepository->findOneBy(['type' => $fileType]);

        $catalog = (new File())
            ->setFilename($file->getName())
            ->setOriginFilename($origin_filename)
            ->setManufacturer($manufacturer)
            ->setLang($language)
            ->setByteSize($file->getByteSize())
            ->setFileStatus(FileStatus::NEW)
            ->setFileType($fileType)
            ->setMimeType($file->getMimeType());

        if ($text !== null) {
            $catalog->setText($text);
        }

        $categories = new ArrayCollection();
        foreach ($categories_ids as $category_id) {
            $category = $this->categoryRepository->find($category_id);

            if (!$category) {
                throw new CategoryNotFoundByIdException($category_id);
            }

            $categories->add($category);
        }

        $catalog->setCategories($categories);
        $this->fileRepository->add($catalog, true);

        return $catalog;
    }

    /**
     * Checking if document is already in queue or was uploaded
     */
    private function isDocumentAlreadyLoaded(CatalogFile $file): bool
    {
        $byte_size = $file->getByteSize();
        $raw_content = $this->storageService->getRawContentFromCatalogFile($file->getName());

        foreach ($this->fileRepository->findAllByByteSize($byte_size) as $item){

            if ($this->storageService->getRawContentFromCatalogFile($item->getFilename()) === $raw_content){
                return true;
            }
        }

        return false;
    }

    public function getFilesInCategoryGroupedByType(int $category_id): FileList
    {
        $category = $this->categoryRepository->find($category_id);

        if (null === $category){
            throw new CategoryNotFoundByIdException($category_id);
        }

        $files = $this->fileRepository->findAllByCategory($category_id);

        return $this->fileTypeGroupedListMapper->map($files);
    }

}