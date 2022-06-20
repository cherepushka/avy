<?php

namespace App\Service;

use App\Entity\Catalog;
use App\Repository\CatalogRepository;
use App\Repository\LanguageRepository;
use App\Repository\ManufacturerRepository;
use Doctrine\ORM\NonUniqueResultException;

class CatalogService
{

    public function __construct(
        private readonly CatalogRepository $catalogRepository,
        private readonly ManufacturerRepository $manufacturerRepository,
        private readonly LanguageRepository $languageRepository,
    ){}

    /**
     * @throws NonUniqueResultException
     */
    public function insertCatalog(
        string  $filename,
        string  $origin_filename,
        string  $manufacturer_name,
        int     $series,
        string  $language_name,
    ): int
    {
        $manufacturer = $this->manufacturerRepository->findOneByName($manufacturer_name);
        $language = $this->languageRepository->findOneByName($language_name);

        $catalog = (new Catalog())
            ->setFilename($filename)
            ->setOriginFilename($origin_filename)
            ->setManufacturerId($manufacturer)
            ->setSeries($series)
            ->setLang($language);

        $this->catalogRepository->add($catalog, true);

        return $catalog->getId();
    }

}