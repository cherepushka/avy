<?php

namespace App\Mapper\FileList;

use App\Entity\File;
use App\Model\FileList\FileTypeGrouped\FileList;
use App\Model\FileList\FileTypeGrouped\FileListItem;
use App\Model\FileList\FileTypeGrouped\ItemFile;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FileTypeGroupedListMapper
{

    public function __construct(
        private readonly UrlGeneratorInterface $router,
    ){}

    /**
     * @param File[] $files
     * @return FileList
     */
    public function map(array $files): FileList
    {
        $items = [];

        foreach ($files as $file){
            $fileTypeEntity = $file->getFileType();
            $fileType = $fileTypeEntity->getType();

            if (isset($items[$fileType])){

                /** @var FileListItem $fileListItem */
                $fileListItem = $items[$fileType];

                $downloadLink = $this->router->generate('app_files_download', ['name' => $file->getFilename()],
                    UrlGeneratorInterface::ABSOLUTE_URL);

                $fileListItem->appendFile(new ItemFile(
                    $file->getOriginFilename(),
                    $file->getByteSize(),
                    $downloadLink
                ));
            } else {
                $items[$fileType] = new FileListItem($fileTypeEntity->getName());
            }
        }

        return new FileList($items);
    }

}