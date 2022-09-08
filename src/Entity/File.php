<?php

namespace App\Entity;

use App\Repository\FileRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FileRepository::class)]
#[ORM\HasLifecycleCallbacks]
class File
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: false)]
    private string $filename;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $origin_filename;

    #[ORM\Column(type: 'integer', length: 100, nullable: false)]
    private int $byte_size;

    #[ORM\Column(type: 'text', nullable: true)]
    private string $text;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: "manufacturer_id", referencedColumnName: "id", nullable: false)]
    private Manufacturer $manufacturer;

    #[ORM\ManyToOne(targetEntity: Language::class)]
    #[ORM\JoinColumn(name: "lang_id", referencedColumnName: "id", nullable: false)]
    private Language $lang;

    #[ORM\ManyToOne(targetEntity: FileType::class)]
    #[ORM\JoinColumn(name: "fileType_id", referencedColumnName: "id", nullable: false)]
    private FileType $fileType;

    #[ORM\Column(type: 'string', enumType: FileStatus::class, options: ['default' => FileStatus::NEW])]
    private FileStatus $fileStatus;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $mimeType;

    #[ORM\ManyToMany(targetEntity: Category::class)]
    private Collection $categories;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?DateTimeInterface $created_at;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?DateTimeInterface $updated_at;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

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

    public function getByteSize(): int
    {
        return $this->byte_size;
    }

    public function setByteSize(int $byte_size): self
    {
        $this->byte_size = $byte_size;

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

    public function getManufacturer(): Manufacturer
    {
        return $this->manufacturer;
    }

    public function setManufacturer(Manufacturer $manufacturer): self
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

    public function getFileType(): FileType
    {
        return $this->fileType;
    }

    public function setFileType(FileType $fileType): self
    {
        $this->fileType = $fileType;

        return $this;
    }

    public function getFileStatus(): FileStatus
    {
        return $this->fileStatus;
    }

    public function setFileStatus(FileStatus $fileStatus): self
    {
        $this->fileStatus = $fileStatus;

        return $this;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): self
    {
        $this->mimeType = $mimeType;

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

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->created_at;
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): self
    {
        if (!isset($this->created_at) || $this->getCreatedAt() === null){
            $this->created_at = new DateTimeImmutable();
        }

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updated_at;
    }

    #[ORM\PrePersist]
    public function setUpdatedAt(): self
    {
        $this->updated_at = new DateTimeImmutable();

        return $this;
    }
}
