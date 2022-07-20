<?php

namespace App\Command\Dev;

use App\Entity\Category;
use App\Repository\CatalogRepository;
use App\Service\Elasticsearch;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'migrate:FromDbToElastic',
    description: 'Add a short description for your command',
)]
class MigrateFromDbToElasticCommand extends Command
{

    public function __construct(
        private readonly Elasticsearch $elasticsearch,
        private readonly CatalogRepository $catalogRepository
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {}

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach($this->catalogRepository->findAll() as $catalog){

            $series = $this->catalogRepository->findAllSeries($catalog->getId());

            $category_ids = [];
            foreach($catalog->getCategories() as $category){
                $category_ids[] = $category->getid();
            }

            $series_ids = [];
            foreach ($series as $seria){
                $series_ids[] = $seria->getId();
            }
            
            $this->elasticsearch->uploadDocument(
                $catalog->getId(),
                $catalog->getFilename(),
                $catalog->getByteSize(),
                $catalog->getText(),
                $catalog->getLang()->getAlias(),
                $category_ids,
                $series_ids
            );
        }
       
        return Command::SUCCESS;
    }
}
