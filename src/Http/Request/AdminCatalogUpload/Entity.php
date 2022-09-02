<?php

namespace App\Http\Request\AdminCatalogUpload;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class Entity{

    #[Assert\NotNull(message: 'Файл должен быть загружен')]
    #[Assert\File(
        mimeTypes: ['application/pdf', 'application/x-pdf'],
        mimeTypesMessage: 'Пожалуйста, загрузите валидный PDF',
    )]
    private UploadedFile $file;

    #[Assert\NotBlank(message: 'Имя файла не должно быть пустым')]
    private string $originFilename;

    #[Assert\NotBlank(message: 'Производитель не должен быть пустым')]
    private string $manufacturer;

    #[Assert\NotBlank(message: 'Язык должен быть указан')]
    private string $lang;

    #[Assert\NotBlank(message: 'Текст не должен быть пустым')]
    private string $text;

    #[Assert\NotBlank(message: 'Вы должны указать минимум одну категорию')]
    #[Assert\Regex(
        pattern: '#^(?:\d+,)+\d+$#',
        message: 'Категории должны быть указаны в корректном формате'
    )]
    private string $categoryIds;

    public function getFile(): UploadedFile
    {
        return $this->file;
    }

    public function setFile(UploadedFile $file): void
    {
        $this->file = $file;
    }

    public function getOriginFilename(): string
    {
        return $this->originFilename;
    }

    public function setOriginFilename(string $originFilename): void
    {
        $this->originFilename = $originFilename;
    }

    public function getManufacturer(): string
    {
        return $this->manufacturer;
    }

    public function setManufacturer(string $manufacturer): void
    {
        $this->manufacturer = $manufacturer;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function setLang(string $lang): void
    {
        $this->lang = $lang;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function getCategoryIds(): array
    {
        return explode(',', $this->categoryIds);
    }

    public function setCategoryIds(string $categoryIds): void
    {
        $this->categoryIds = $categoryIds;
    }

}