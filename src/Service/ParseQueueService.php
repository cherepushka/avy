<?php

namespace App\Service;

use App\Entity\Language;
use App\Entity\Manufacturer;
use App\Entity\ParseQueue;
use App\Exception\FileAlreadyLoadedException;
use App\Model\ParseQueueItem;
use App\Model\ParseQueueList;
use App\Repository\CatalogRepository;
use App\Repository\ParseQueueRepository;
use App\Service\Pdf\CatalogFileService;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ParseQueueService
{

    public function __construct(
        private readonly CatalogFileService $catalogFileService,
        private readonly ParseQueueRepository $queueRepository,
        private readonly CatalogRepository $catalogRepository
    ){}

    /**
     * @throws FileAlreadyLoadedException
     */
    public function enqueueFile(UploadedFile $file, Manufacturer $manufacturer = null, Language $lang = null, ArrayCollection $category_ids = null): void
    {
        $catalogPath = $this->catalogFileService->saveUploadedFileToTmp($file);
        $filename = (new File($catalogPath))->getBasename();

        if ($this->isTmpDocumentAlreadyLoaded($filename)){
            $this->catalogFileService->removeTmpCatalog($filename);
            throw new FileAlreadyLoadedException($filename);
        }

        $new_queue_item = (new ParseQueue())
            ->setFilename($filename)
            ->setStatus(ParseQueue::STATUS_NEW)
            ->setOriginFilename($file->getClientOriginalName())
            ->setByteSize($this->catalogFileService->getTmpCatalogByteSize($filename));

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

        $catalogPath = $this->catalogFileService->moveFromTmpToCatalogs($filename);

        $this->queueRepository->remove($queueItem, true);

        return $catalogPath;
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
                ->setStatus($catalog->getStatus()),
            $catalogs
        );

        return new ParseQueueList($catalogs);
    }

    /**
     * Checking if document is already in queue or was uploaded
     */
    private function isTmpDocumentAlreadyLoaded(string $filepath): bool
    {
        $byte_size = $this->catalogFileService->getTmpCatalogByteSize($filepath);
        $raw_content = $this->catalogFileService->getTmpCatalogRawContent($filepath);

        foreach ($this->catalogRepository->findAllByByteSize($byte_size) as $item){
            if ($this->catalogFileService->getCatalogRawContent($item->getFilename()) === $raw_content){
                return true;
            }
        }

        foreach ($this->queueRepository->findAllByByteSize($byte_size) as $item){
            if ($this->catalogFileService->getTmpCatalogRawContent($item->getFilename()) === $raw_content){
                return true;
            }
        }

        return false;
    }

}