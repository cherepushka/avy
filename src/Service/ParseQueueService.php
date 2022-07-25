<?php

namespace App\Service;

use App\Entity\Language;
use App\Entity\Manufacturer;
use App\Entity\ParseQueue;
use App\Exception\FileAlreadyLoadedException;
use App\Model\File\CatalogFile;
use App\Model\ParseQueueItem;
use App\Model\ParseQueueList;
use App\Repository\CatalogRepository;
use App\Repository\ParseQueueRepository;
use App\Service\Pdf\Storage\StorageServiceFacade;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ParseQueueService
{

    public function __construct(
        private readonly ParseQueueRepository $queueRepository,
        private readonly CatalogRepository $catalogRepository,
        private readonly StorageServiceFacade $storageService,
    ){}

    /**
     * @throws FileAlreadyLoadedException
     */
    public function enqueueFile(UploadedFile $uploadedFile, Manufacturer $manufacturer = null, Language $lang = null, ArrayCollection $category_ids = null): void
    {
        $file = $this->storageService->saveUploadedCatalog($uploadedFile);

        if ($this->isDocumentAlreadyLoaded($file)){
            $this->storageService->deleteCatalog($file->getName());
            throw new FileAlreadyLoadedException($file->getOriginName());
        }

        $new_queue_item = (new ParseQueue())
            ->setFilename($file->getName())
            ->setStatus(ParseQueue::STATUS_NEW)
            ->setOriginFilename($file->getOriginName())
            ->setByteSize($file->getByteSize());

        if ($manufacturer !== null && $lang !== null && $category_ids !== null){
            $new_queue_item->setCategories($category_ids)
                ->setLanguage($lang)
                ->setManufacturer($manufacturer);
        }

        $this->queueRepository->add($new_queue_item, true);
    }

    public function dequeueFile(string $filename): string
    {
        $queueItem = $this->queueRepository->findOneBy(['filename' => $filename]);

        $this->queueRepository->remove($queueItem, true);

        return $this->storageService->getCatalogFullPath($filename);
    }

    public function getAllParsed(): ParseQueueList
    {
        $catalogs = $this->queueRepository->findAllSuccess();

        $catalogs = array_map(
            fn($catalog) => (new ParseQueueItem())
                ->setId($catalog->getId())
                ->setFilename($catalog->getFilename())
                ->setOriginFilename($catalog->getOriginFilename())
                ->setText($catalog->getText())
                ->setStatus($catalog->getStatus())
                ->setByteSize($catalog->getByteSize()),
            $catalogs
        );

        return new ParseQueueList($catalogs);
    }

    /**
     * Checking if document is already in queue or was uploaded
     */
    private function isDocumentAlreadyLoaded(CatalogFile $file): bool
    {
        $byte_size = $file->getByteSize();
        $raw_content = $this->storageService->getRawContentFromCatalogFile($file->getName());

        foreach ($this->catalogRepository->findAllByByteSize($byte_size) as $item){

            if ($this->storageService->getRawContentFromCatalogFile($item->getFilename()) === $raw_content){
                return true;
            }
        }

        foreach ($this->queueRepository->findAllByByteSize($byte_size) as $item){
            if ($this->storageService->getRawContentFromCatalogFile($item->getFilename()) === $raw_content){
                return true;
            }
        }

        return false;
    }

}