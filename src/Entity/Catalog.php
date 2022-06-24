<?php

namespace App\Entity;

use App\Repository\CatalogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CatalogRepository::class)]
class Catalog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $filename;

    #[ORM\Column(type: 'string', length: 255)]
    private string $origin_filename;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: "manufacturer_id", referencedColumnName: "id")]
    private Manufacturer $manufacturer;

    #[ORM\ManyToOne(targetEntity: Language::class)]
    #[ORM\JoinColumn(name: "lang_id", referencedColumnName: "id")]
    private Language $lang;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getOriginFilename(): string
    {
        return $this->origin_filename;
    }

    public function setOriginFilename(string $origin_filename): self
    {
        $this->origin_filename = $origin_filename;

        return $this;
    }

    public function getManufacturerId(): Manufacturer
    {
        return $this->manufacturer;
    }

    public function setManufacturerId(Manufacturer $manufacturer): self
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    public function getLang(): Language
    {
        return $this->lang;
    }

    public function setLang(Language $lang): self
    {
        $this->lang = $lang;

        return $this;
    }
}
