<?php

namespace App\Command;

use App\Repository\CategoryRepository;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

#[AsCommand(
    name: 'set:isProductsExistStatus',
    description: 'Migrate from txt file ids of series with existing products',
)]
class SetIsProductsExistStatus extends Command
{
    public function __construct(
        private readonly string $projectDir,
        private readonly CategoryRepository $categoryRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('seriesPath', InputArgument::REQUIRED, 'Path to series TXT file relative to the project root');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $uploaded_series = $this->loadSeriesAsArr($input->getArgument('seriesPath'));

        foreach ($uploaded_series as $series) {
            $new_series = $this->categoryRepository->find($series)
                ->setProductsExist(true);

            $this->categoryRepository->add($new_series, true);
        }

        return Command::SUCCESS;
    }

    private function loadSeriesAsArr(string $path): array
    {
        $path = ltrim($path, '\\/');

        if (!is_file($this->projectDir.\DIRECTORY_SEPARATOR.$path)) {
            throw new FileNotFoundException("Cannot load tree with path '$path'");
        }

        $content = file_get_contents($path);
        if (!$content) {
            throw new RuntimeException('Error with getting body of file');
        }

        return explode(',', $content);
    }
}
