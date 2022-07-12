<?php

namespace App\Command;

use App\Repository\ParseQueueRepository;
use App\Service\CatalogService;
use App\Service\Elasticsearch;
use App\Service\ParseQueueService;
use App\Service\Pdf\CatalogFileService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'SaveFullyHandledCatalogs',
    description: 'Add a short description for your command',
)]
class SaveFullyHandledCatalogsCommand extends Command
{
    public function __construct(
        private readonly ParseQueueService $parseQueueService,
        private readonly ParseQueueRepository $parseQueueRepository,
        private readonly CatalogService $catalogService,
        private readonly Elasticsearch $elasticsearch,
        private readonly CatalogFileService $catalogFileService,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {}

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $parsedCatalogs = $this->parseQueueRepository->findAllSuccess();

        foreach ($parsedCatalogs as $parsedCatalog) {
            if ($parsedCatalog->getLanguage() !== null && $parsedCatalog->getManufacturer() !== null && !empty($parsedCatalog->getCategories())){

                $categories_ids = [];
                foreach ($parsedCatalog->getCategories()->getValues() as $category){
                    $categories_ids[] = $category->getId();
                }

                $filesize = filesize($this->catalogFileService->getTmpCatalogPath($parsedCatalog->getFilename()));
                $catalogID = $this->catalogService->insertCatalog(
                    $parsedCatalog->getFilename(),
                    $parsedCatalog->getOriginFilename(),
                    $parsedCatalog->getManufacturer()->getName(),
                    $categories_ids,
                    $parsedCatalog->getLanguage()->getName(),
                    $filesize,
                    $parsedCatalog->getText()
                );

                $this->elasticsearch->uploadDocument(
                    $catalogID,
                    $parsedCatalog->getFilename(),
                    $filesize,
                    $parsedCatalog->getText(),
                    $categories_ids
                );

                $this->parseQueueService->dequeueFile($parsedCatalog->getFilename());
            }
        }

        return Command::SUCCESS;
    }
}
