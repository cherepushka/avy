<?php

namespace App\DataFixtures;

use App\Entity\Language;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LanguageFixtures extends Fixture
{

    public function load(ObjectManager $manager)
    {
        $languages = [
            (new Language())->setName('Русский')->setAlias('rus'),
            (new Language())->setName('Английский')->setAlias('eng'),
        ];

        foreach ($languages as $language){
            $manager->persist($language);
        }

        $manager->flush();
    }
}