<?php

namespace App\Service;

use App\Entity\Catalog;
use App\Repository\CatalogRepository;
use App\Repository\ManufacturerRepository;
use Doctrine\ORM\NonUniqueResultException;

class CatalogService
{

    public function __construct(
        private readonly CatalogRepository $catalogRepository,
        private readonly ManufacturerRepository $manufacturerRepository
    ){}

    /**
     * @throws NonUniqueResultException
     */
    public function insertCatalog(
        string  $filename,
        string  $origin_filename,
        string  $manufacturer_name,
        string  $series,
    ): void
    {
        $manufacturer = $this->manufacturerRepository->findOneByName($manufacturer_name);

        $catalog = (new Catalog())
            ->setFilename($filename)
            ->setOriginFilename($origin_filename)
            ->setManufacturerId($manufacturer)
            ->setSeries($series);

        $this->catalogRepository->add($catalog, true);
    }

}