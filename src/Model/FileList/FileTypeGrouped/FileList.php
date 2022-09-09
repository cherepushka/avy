<?php

namespace App\Model\FileList\FileTypeGrouped;

use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;

class FileList
{

    /** @var FileListItem[] $items */
    #[OA\Property(
        property: 'fileType',
        type: 'array',
        items: new OA\Items(
            ref: new Model(type: FileListItem::class)
        )
    )]
    private array $items;

    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function getItems(): array
    {
        return $this->items;
    }

}