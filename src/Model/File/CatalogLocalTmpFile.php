<?php

namespace App\Model\File;

class CatalogLocalTmpFile
{

    private string $fullPath;
    private string $originName;
    private string $extension;
    private int $byteSize;

    public function getFullPath(): string
    {
        return $this->fullPath;
    }

    public function setFullPath(string $fullPath): self
    {
        $this->fullPath = $fullPath;
        return $this;
    }

    public function getOriginName(): string
    {
        return $this->originName;
    }

    public function setOriginName(string $originName): self
    {
        $this->originName = $originName;
        return $this;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): self
    {
        $this->extension = $extension;
        return $this;
    }

    public function getByteSize(): int
    {
        return $this->byteSize;
    }

    public function setByteSize(int $byteSize): self
    {
        $this->byteSize = $byteSize;

        return $this;
    }

}