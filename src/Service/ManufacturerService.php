<?php

namespace App\Service;

use App\Model\ManufacturerItem;
use App\Entity\Manufacturer;
use App\Model\ManufacturerList;
use App\Repository\ManufacturerRepository;
use Doctrine\ORM\NonUniqueResultException;

class ManufacturerService
{

    public function __construct(
        private readonly ManufacturerRepository $manufacturerRepository
    ) {}

    /**
     * @return ManufacturerList[]
     */
    public function getAll(): array
    {
        $manufacturers = $this->manufacturerRepository->findAll();

        $items = array_map(
            fn(Manufacturer $manufacturer) => (new ManufacturerItem())
                ->setId($manufacturer->getId())
                ->setName($manufacturer->getName()),
            $manufacturers
        );

        return (new ManufacturerList($items))->getItems();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getByName(string $name): ManufacturerItem
    {
        $manufacturer = $this->manufacturerRepository->findOneByName($name);

        return (new ManufacturerItem())
            ->setId($manufacturer->getId())
            ->setName($manufacturer->getName());
    }

}