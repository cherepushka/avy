<?php

namespace App\Http\Request\Api\SearchProductSuggests;

use Symfony\Component\Validator\Constraints as Assert;

class Entity
{
    #[Assert\NotBlank(message: 'Поле `search` не должно быть пустым')]
    #[Assert\Length(min: 1, minMessage: 'Поле `search` должно содержать хотя бы один символ')]
    private string $search;

    public function getSearch(): string
    {
        return $this->search;
    }

    public function setSearch(string $search): self
    {
        $this->search = $search;

        return $this;
    }
}
