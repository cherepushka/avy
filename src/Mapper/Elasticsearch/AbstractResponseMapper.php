<?php

namespace App\Mapper\Elasticsearch;

use App\Repository\CatalogRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class AbstractResponseMapper
{

    public function __construct(
        protected readonly CatalogRepository $catalogRepository,
        protected readonly UrlGeneratorInterface $router,
    ){}

}