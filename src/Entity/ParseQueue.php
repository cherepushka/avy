<?php

namespace App\Entity;

use App\Repository\ParseQueueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

#[ORM\Entity(repositoryClass: ParseQueueRepository::class)]
class ParseQueue
{

    public const STATUS_NEW = 'new';
    public const STATUS_PARSING = 'parsing';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $origin_filename;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $filename;

    #[ORM\Column(type: 'text', nullable: true)]
    private string $text;

    #[ORM\Column(type: 'string', nullable: false, columnDefinition: "ENUM('new', 'parsing', 'success', 'failed')")]
    private string $status;

    #[ORM\ManyToOne(targetEntity: Language::class)]
    #[ORM\JoinColumn(name: 'lang_id', referencedColumnName: 'id', nullable: true)]
    private Language $language;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: 'manufacturer_id', referencedColumnName: 'id', nullable: true)]
    private Manufacturer $manufacturer;

    #[ORM\ManyToMany(targetEntity: Category::class)]
    private Collection $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setStatus(string $status): self
    {
        if (!in_array($status, [self::STATUS_NEW, self::STATUS_PARSING, self::STATUS_FAILED, self::STATUS_SUCCESS])){
            throw new InvalidArgumentException('$status must value of existing in this entity class constant');
        }

        $this->status = $status;

        return $this;
    }

    public function getLanguage(): Language
    {
        return $this->language;
    }

    public function setLanguage(Language $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getManufacturer(): Manufacturer
    {
        return $this->manufacturer;
    }

    public function setManufacturer(Manufacturer $manufacturer): self
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function setCategories(Collection $categories): self
    {
        $this->categories = $categories;

        return $this;
    }

}
