<?php

namespace App\Model\Elasticsearch;

class SearchResultItem
{
    private string $suggestText;
    private int $byteSize;
    private string $originName;
    private string $downloadLink;
    private string $langAlias;
    private int $series;

    public function getSuggestText(): string
    {
        return $this->suggestText;
    }

    public function setSuggestText(string $suggestText): self
    {
        $this->suggestText = $suggestText;

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

    public function getOriginName(): string
    {
        return $this->originName;
    }

    public function setOriginName(string $originName): self
    {
        $this->originName = $originName;

        return $this;
    }

    public function getDownloadLink(): string
    {
        return $this->downloadLink;
    }

    public function setDownloadLink(string $downloadLink): self
    {
        $this->downloadLink = $downloadLink;

        return $this;
    }

    public function getLangAlias(): string
    {
        return $this->langAlias;
    }

    public function setLangAlias(string $langAlias): self
    {
        $this->langAlias = $langAlias;

        return $this;
    }

    public function getSeries(): int
    {
        return $this->series;
    }

    public function setSeries(int $series): self
    {
        $this->series = $series;

        return $this;
    }
}
