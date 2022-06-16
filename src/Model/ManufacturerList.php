<?php

namespace App\Model;

class ManufacturerList
{

    /**
     * @var Manufacturer[]
     */
    private array $manufacturers;

    /**
     * @param Manufacturer[] $manufacturers
     */
    public function __construct(array $manufacturers)
    {
        $this->manufacturers = $manufacturers;
    }

    public function getItems(): array
    {
        return $this->manufacturers;
    }

}