<?php

namespace App\Model;

class ManufacturerList
{

    /** @var ManufacturerItem[] */
    private array $manufacturers;

    /** @param ManufacturerItem[] $manufacturers */
    public function __construct(array $manufacturers)
    {
        $this->manufacturers = $manufacturers;
    }

    /** @return ManufacturerItem[] */
    public function getItems(): array
    {
        return $this->manufacturers;
    }

}