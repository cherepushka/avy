<?php

namespace App\Command\Upload\ToElasticsearch;

use App\Service\Elasticsearch;
use Elastic\Elasticsearch\Exception\ElasticsearchException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'TestAddProductSuggestions',
    description: 'Add a short description for your command',
)]
class TestAddProductSuggestionsCommand extends Command
{
    public function __construct(
        private readonly string $projectDir,
        private readonly Elasticsearch $elasticsearch
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
        foreach (glob($this->projectDir.'/.dev-data/hints/*') as $filepath) {
            $type = pathinfo($filepath)['filename'];
            $csv_stream = fopen($filepath, 'r');

            while (($row = fgetcsv($csv_stream, 0, "\n")) !== false) {
                $value = $row[0];
                $this->elasticsearch->uploadProductSuggest($value, $type);
            }
            fclose($csv_stream);
        }

        return Command::SUCCESS;
    }
}
