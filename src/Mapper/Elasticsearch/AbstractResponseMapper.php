<?php

namespace App\Mapper\Elasticsearch;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class AbstractResponseMapper
{
    public function __construct(
        protected readonly UrlGeneratorInterface $router,
    ) {
    }
}
