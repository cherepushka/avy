<?php

namespace App\Service;

use App\Entity\Catalog;
use App\Exception\CategoryNotFoundByIdException;
use App\Repository\CatalogRepository;
use App\Repository\CategoryRepository;
use App\Repository\LanguageRepository;
use App\Repository\ManufacturerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;

class CatalogService
{

    public function __construct(
        private readonly CatalogRepository $catalogRepository,
        private readonly ManufacturerRepository $manufacturerRepository,
        private readonly LanguageRepository $languageRepository,
        private readonly CategoryRepository $categoryRepository,
    ){}

    /**
     * @throws NonUniqueResultException
     */
    public function insertCatalog(
        string  $filename,
        string  $origin_filename,
        string  $manufacturer_name,
        array   $categories_ids,
        string  $language_name,
        int     $byteSize
    ): int
    {
        $manufacturer = $this->manufacturerRepository->findOneByName($manufacturer_name);
        $language = $this->languageRepository->findOneByName($language_name);

        $catalog = (new Catalog())
            ->setFilename($filename)
            ->setOriginFilename($origin_filename)
            ->setManufacturer($manufacturer)
            ->setLang($language)
            ->setByteSize($byteSize);

        $this->catalogRepository->add($catalog, true);

        $categories = new ArrayCollection();
        foreach ($categories_ids as $category_id) {
            $category = $this->categoryRepository->find($category_id);

            if (!$category) {
                throw new CategoryNotFoundByIdException($category_id);
            }

            $categories->add($category);
        }

        $catalog->setCategories($categories);

        return $catalog->getId();
    }

    public function removeCatalog()
    {

    }

}