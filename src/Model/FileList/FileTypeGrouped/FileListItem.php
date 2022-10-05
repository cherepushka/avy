<?php

namespace App\Model\FileList\FileTypeGrouped;

class FileListItem
{
    private string $typeName;

    /**
     * @var ItemFile[]
     */
    private array $files;

    /**
     * @param ItemFile[] $files
     */
    public function __construct(string $typeName, array $files = [])
    {
        $this->typeName = $typeName;
        $this->files = $files;
    }

    public function getTypeName(): string
    {
        return $this->typeName;
    }

    /**
     * @return ItemFile[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    public function appendFile(ItemFile $itemFile): void
    {
        $this->files[] = $itemFile;
    }
}
