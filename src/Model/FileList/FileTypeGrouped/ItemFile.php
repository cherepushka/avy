<?php

namespace App\Model\FileList\FileTypeGrouped;

class ItemFile
{

    private string $originName;
    private int $byteSize;
    private string $downloadLink;

    public function __construct(string $originName, int $byteSize, string $downloadLink)
    {
        $this->originName = $originName;
        $this->byteSize = $byteSize;
        $this->downloadLink = $downloadLink;
    }

    public function getOriginName(): string
    {
        return $this->originName;
    }

    public function getByteSize(): int
    {
        return $this->byteSize;
    }

    public function getDownloadLink(): string
    {
        return $this->downloadLink;
    }

}