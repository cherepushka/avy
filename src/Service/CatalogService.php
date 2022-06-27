<?php

namespace App\Service;

use App\Entity\Catalog;
use App\Entity\CatalogCategory;
use App\Exception\CategoryNotFoundByIdException;
use App\Repository\CatalogCategoryRepository;
use App\Repository\CatalogRepository;
use App\Repository\CategoryRepository;
use App\Repository\LanguageRepository;
use App\Repository\ManufacturerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;

class CatalogService
{

    public function __construct(
        private readonly CatalogRepository $catalogRepository,
        private readonly ManufacturerRepository $manufacturerRepository,
        private readonly LanguageRepository $languageRepository,
        private readonly CatalogCategoryRepository $catalogCategoryRepository,
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
    ): int
    {
        $manufacturer = $this->manufacturerRepository->findOneByName($manufacturer_name);
        $language = $this->languageRepository->findOneByName($language_name);

        $catalog = (new Catalog())
            ->setFilename($filename)
            ->setOriginFilename($origin_filename)
            ->setManufacturerId($manufacturer)
            ->setLang($language);

        $this->catalogRepository->add($catalog, true);

        foreach ($categories_ids as $category_id) {
            $category = $this->categoryRepository->find($category_id);

            if (!$category) {
                throw new CategoryNotFoundByIdException($category_id);
            }

            $catalogCategory = (new CatalogCategory())
                ->setCatalog($catalog)
                ->setCategory($category);

            $this->catalogCategoryRepository->add($catalogCategory, true);
        }

        return $catalog->getId();
    }

}