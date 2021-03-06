<?php

namespace App\Exception;

use RuntimeException;

class CategoryNotFoundByIdException extends RuntimeException
{

    public function __construct($category_id)
    {
        parent::__construct("Category not found by id $category_id");
    }

}