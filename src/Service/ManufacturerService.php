<?php

namespace App\Service;

use App\Model\Manufacturer as ManufacturerModel;
use App\Entity\Manufacturer as ManufacturerEntity;
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
            fn(ManufacturerEntity $manufacturer) => (new ManufacturerModel())
                ->setId($manufacturer->getId())
                ->setName($manufacturer->getName()),
            $manufacturers
        );

        return (new ManufacturerList($items))->getItems();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getByName(string $name): ManufacturerModel
    {
        $manufacturer = $this->manufacturerRepository->findOneByName($name);

        return (new ManufacturerModel())
            ->setId($manufacturer->getId())
            ->setName($manufacturer->getName());
    }

}