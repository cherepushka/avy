<?php

namespace App\DataFixtures;

use App\Entity\Manufacturer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ManufacturerFixtures extends Fixture
{

    public function load(ObjectManager $manager)
    {
        $manufacturers = [
            (new Manufacturer())->setName('Hy-Lock'),
            (new Manufacturer())->setName('A-Flow'),
        ];

        foreach ($manufacturers as $manufacturer){
            $manager->persist($manufacturer);
        }

        $manager->flush();
    }
}