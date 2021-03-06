<?php

namespace App\Command;

use App\Entity\ParseQueue;
use App\Exception\FileCorruptedException;
use App\Model\File\CatalogFile;
use App\Repository\ParseQueueRepository;
use App\Service\CatalogService;
use App\Service\OCR\OcrVisionInterface;
use App\Service\ParseQueueService;
use App\Service\Pdf\CatalogFileService;
use App\Service\Pdf\ImageBuilder;
use App\Service\Pdf\Storage\StorageServiceFacade;
use App\Service\Pdf\TextParser;
use Exception;
use ImagickException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

#[AsCommand(
    name: 'parse:queue',
    description: 'Start parsing catalogs in queue',
)]
class ParseQueueCommand extends Command
{

    public function __construct(
        private readonly OcrVisionInterface $ocrVision,
        private readonly ParseQueueRepository $parseQueueRepository,
        private readonly StorageServiceFacade $storageService,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {}

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $unhandledCatalogs = $this->parseQueueRepository->findAllNew();

        foreach ($unhandledCatalogs as $queueItem){
            try {
                $this->handleCatalog($queueItem);
            } catch (Exception $exception){
                $queueItem->setStatus(ParseQueue::STATUS_FAILED);
                $queueItem->setExceptionText($exception->getMessage() . "\n" . $exception->getTraceAsString());

                $this->parseQueueRepository->add($queueItem, true);
                continue;
            }
        }

        return Command::SUCCESS;
    }

    /**
     * @throws FileNotFoundException
     */
    private function handleCatalog(ParseQueue $queueItem): void
    {
        $queueItem->setStatus(ParseQueue::STATUS_PARSING);
        $this->parseQueueRepository->add($queueItem, true);

        $catalogPath = $this->storageService->getCatalogFullPath($queueItem->getFilename());

        $catalogFile = (new CatalogFile())
            ->setFullPath($catalogPath)
            ->setOriginName($queueItem->getOriginFilename())
            ->setName($queueItem->getFilename())
            ->setExtension('pdf')
            ->setByteSize($queueItem->getByteSize());

        $queueItem->setText($this->ocrVision->catalogGetTextSync($catalogFile));
        $queueItem->setStatus(ParseQueue::STATUS_SUCCESS);

        $this->parseQueueRepository->add($queueItem, true);
    }
}
