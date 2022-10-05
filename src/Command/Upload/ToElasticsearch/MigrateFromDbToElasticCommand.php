<?php

namespace App\Command\Upload\ToElasticsearch;

use App\Repository\FileRepository;
use App\Service\Elasticsearch;
use Elastic\Elasticsearch\Exception\ElasticsearchException;
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
        private readonly FileRepository $fileRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    /**
     * @throws ElasticsearchException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $files = $this->fileRepository->findAllPdfsWithText();

        foreach ($files as $catalog) {
            $this->elasticsearch->uploadDocument(
                $catalog->getFilename(),
                $catalog->getOriginFilename(),
                $catalog->getByteSize(),
                $catalog->getLang()->getAlias(),
                $catalog->getText(),
                $catalog->getFileType()->getType(),
                $catalog->getCategories()->toArray(),
                $this->fileRepository->findAllSeries($catalog->getId()),
            );
        }

        return Command::SUCCESS;
    }
}
