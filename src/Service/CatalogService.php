<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Entity\Catalog;
use App\Exception\CategoryNotFoundByIdException;
use App\Repository\CatalogRepository;
use App\Repository\CategoryRepository;
use App\Repository\LanguageRepository;
use App\Repository\ManufacturerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use App\Model\File\CatalogFile;
use App\Exception\FileAlreadyLoadedException;
use App\Service\Pdf\Storage\StorageServiceFacade;

class CatalogService
{

    public function __construct(
        private readonly CatalogRepository $catalogRepository,
        private readonly ManufacturerRepository $manufacturerRepository,
        private readonly LanguageRepository $languageRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly StorageServiceFacade $storageService,
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
        string          $text
    ): Catalog
    {
        $file = $this->storageService->saveUploadedCatalog($uploadedFile);

        if ($this->isDocumentAlreadyLoaded($file)){
            $this->storageService->deleteCatalog($file->getName());
            throw new FileAlreadyLoadedException($file->getOriginName());
        }

        $manufacturer = $this->manufacturerRepository->findOneByName($manufacturer_name);
        $language = $this->languageRepository->findOneByName($language_name);

        $catalog = (new Catalog())
            ->setFilename($file->getName())
            ->setOriginFilename($origin_filename)
            ->setManufacturer($manufacturer)
            ->setLang($language)
            ->setByteSize($file->getByteSize())
            ->setText($text);

        $categories = new ArrayCollection();
        foreach ($categories_ids as $category_id) {
            $category = $this->categoryRepository->find($category_id);

            if (!$category) {
                throw new CategoryNotFoundByIdException($category_id);
            }

            $categories->add($category);
        }

        $catalog->setCategories($categories);
        $this->catalogRepository->add($catalog, true);

        return $catalog;
    }

    /**
     * Checking if document is already in queue or was uploaded
     */
    private function isDocumentAlreadyLoaded(CatalogFile $file): bool
    {
        $byte_size = $file->getByteSize();
        $raw_content = $this->storageService->getRawContentFromCatalogFile($file->getName());

        foreach ($this->catalogRepository->findAllByByteSize($byte_size) as $item){

            if ($this->storageService->getRawContentFromCatalogFile($item->getFilename()) === $raw_content){
                return true;
            }
        }

        return false;
    }

}