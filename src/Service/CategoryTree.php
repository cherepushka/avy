<?php

namespace App\Service;

class CategoryTree
{

    private string $remoteCategoryTreeUri = 'http://dev.fluidline.beget.tech/assets/snippets/product/treeOfProducts/treeData-react.json';

    public function __construct(){
    }

    public function getRemoteTree(): string|false
    {
        return file_get_contents($this->remoteCategoryTreeUri);
    }

}