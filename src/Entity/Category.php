<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\Column(name: "id", type: 'integer')]
    private int $id;

    #[ORM\Column(name: "title", type: "string", length: 255, nullable: false)]
    private string $title;

    #[ORM\Column(name: "link", type: "string", length: 255, nullable: false)]
    private string $link;

    #[ORM\OneToMany(mappedBy: "parent", targetEntity: self::class, fetch: "EXTRA_LAZY")]
    private Collection $children;

    #[ORM\ManyToOne(targetEntity: self::class, fetch: "EXTRA_LAZY", inversedBy: "children")]
    #[ORM\JoinColumn(name: "parent", referencedColumnName: "id")]
    private ?self $parent;

    #[ORM\Column(name: 'productsExist', type: 'boolean', options: ["default" => false])]
    private bool $productsExist;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function setLink(string $link): self
    {
        $this->link = $link;

        return $this;
    }

    /** @return Collection<Category> */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * @param Collection<Category> $children
     * @return Category
     */
    public function setChildren(Collection $children): self
    {
        $this->children = $children;

        return $this;
    }

    public function addChild(Category $child): self
    {
        $this->children->add($child);

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent ?? null;
    }

    public function setParent(Category $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function isProductsExist(): bool
    {
        return $this->productsExist;
    }

    public function setProductsExist(bool $productsExist): self
    {
        $this->productsExist = $productsExist;

        return $this;
    }

}
