<?php

namespace App\Service;

use App\Model\LanguageItem;
use App\Model\LanguageList;
use App\Repository\LanguageRepository;

class LanguageService
{
    public function __construct(
        private readonly LanguageRepository $languageRepository
    ) {
    }

    public function getAll(): LanguageList
    {
        $languages = $this->languageRepository->findAll();

        $items = array_map(
            fn ($language) => (new LanguageItem())
                ->setId($language->getId())
                ->setName($language->getName())
                ->setAlias($language->getAlias()),
            $languages
        );

        return new LanguageList($items);
    }
}
