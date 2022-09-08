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
            (new FileType())->setType('Каталог')
                ->setAlias('catalog')
                ->setDescription('Каталог серии'),
            (new FileType())->setType('Руководство по эксплуатации / инструкция')
                ->setAlias('manual')
                ->setDescription('Руководство по эксплуатации к серии'),
            (new FileType())->setType('Опросный лист')
                ->setAlias('questionnaire')
                ->setDescription('Опросный лист для клиентов'),
        ];

        foreach ($fileTypes as $entity){
            $manager->persist($entity);
        }

        $manager->flush();
    }
}
