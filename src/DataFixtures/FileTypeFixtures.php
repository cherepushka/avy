<?php

namespace App\DataFixtures;

use App\Entity\FileType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class FileTypeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $fileTypes = [
            (new FileType())->setName('Каталог')
                ->setType('catalog')
                ->setDescription('Каталог серии'),
            (new FileType())->setName('Руководство по эксплуатации / инструкция')
                ->setType('manual')
                ->setDescription('Руководство по эксплуатации к серии'),
            (new FileType())->setName('Опросный лист')
                ->setType('questionnaire')
                ->setDescription('Опросный лист для клиентов'),
        ];

        foreach ($fileTypes as $entity){
            $manager->persist($entity);
        }

        $manager->flush();
    }
}
