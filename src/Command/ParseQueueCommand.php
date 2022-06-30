<?php

namespace App\Command;

use App\Entity\ParseQueue;
use App\Repository\ParseQueueRepository;
use App\Service\OCR\OcrVisionInterface;
use App\Service\Pdf\CatalogFileService;
use App\Service\Pdf\ImageBuilder;
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
        private readonly ParseQueueRepository $parseQueueRepository,
        private readonly CatalogFileService $catalogFileService,
        private readonly ImageBuilder $imageBuilder,
        private readonly OcrVisionInterface $OCR,
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
                dump($exception->getMessage());
                $this->parseQueueRepository->add($queueItem->setStatus(ParseQueue::STATUS_FAILED), true);
                continue;
            }

            $this->parseQueueRepository->add($queueItem->setStatus(ParseQueue::STATUS_SUCCESS), true);
        }

        return Command::SUCCESS;
    }

    /**
     * @throws ImagickException|FileNotFoundException
     */
    private function handleCatalog(ParseQueue $queueItem)
    {
        $queueItem->setStatus(ParseQueue::STATUS_PARSING);
        $this->parseQueueRepository->add($queueItem, true);

        $filepath = $this->catalogFileService->getTmpCatalogPath($queueItem->getFilename());
        $imagesPaths = $this->imageBuilder->generateImagickImages($filepath);

        try {
            $queueItem->setText($this->OCR->findImageAnnotations($imagesPaths));
        } finally {
            $this->imageBuilder->deleteGeneratedImagesWithDir($imagesPaths);
        }

        $this->parseQueueRepository->add($queueItem, true);
    }
}
