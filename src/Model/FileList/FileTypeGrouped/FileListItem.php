<?php

namespace App\Model\FileList\FileTypeGrouped;

class FileListItem
{

    private string $type;
    private string $typeAlias;

    /**
     * @var ItemFile[]
     */
    private array $files;

    /**
     * @param string $type
     * @param string $typeAlias
     * @param ItemFile[] $files
     */
    public function __construct(string $type, string $typeAlias, array $files = [])
    {
        $this->type = $type;
        $this->typeAlias = $typeAlias;
        $this->files = $files;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTypeAlias(): string
    {
        return $this->typeAlias;
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