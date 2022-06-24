<?php

namespace App\Entity;

use App\Repository\CatalogCategoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CatalogCategoryRepository::class)]
class CatalogCategory
{

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Catalog::class, inversedBy: Catalog::class)]
    #[ORM\JoinColumn(name: "catalog_id", referencedColumnName: "id")]
    private Catalog $catalog_id;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: Catalog::class)]
    #[ORM\JoinColumn(name: "category_id", referencedColumnName: "id")]
    private Category $category_id;

    public function getCatalog(): Catalog
    {
        return $this->catalog_id;
    }

    public function setCatalog(Catalog $catalog_id): self
    {
        $this->catalog_id = $catalog_id;
        return $this;
    }

    public function getCategory(): Category
    {
        return $this->category_id;
    }

    public function setCategory(Category $category_id): self
    {
        $this->category_id = $category_id;
        return $this;
    }


}
