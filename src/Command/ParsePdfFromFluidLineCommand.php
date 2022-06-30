<?php

namespace App\Command;

use App\Repository\CategoryRepository;
use App\Service\ParseQueueService;
use PDO;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'ParsePdfFromFluidLine',
    description: 'Parse pdf catalogs from fluid-line.ru',
)]
class ParsePdfFromFluidLineCommand extends Command
{

    private readonly PDO $fluid_db;

    public function __construct(
        private readonly ParseQueueService $parseQueueService,
        private readonly CategoryRepository $categoryRepository
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('host', InputArgument::REQUIRED, 'Argument description')
            ->addArgument('port', InputArgument::REQUIRED, 'Argument description')
            ->addArgument('database', InputArgument::REQUIRED, 'Argument description')
            ->addArgument('user', InputArgument::REQUIRED, 'Argument description')
            ->addArgument('password', InputArgument::REQUIRED, 'Argument description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->connectToFluidDB(
            $input->getArgument('host'),
            $input->getArgument('port'),
            $input->getArgument('database'),
            $input->getArgument('user'),
            $input->getArgument('password'),
        );

        //PDF files exist only in final categories without subcategories
        $categories = $this->categoryRepository->findAllWithoutChildren();

        return Command::SUCCESS;
    }

    private function connectToFluidDB(string $host, int $port, string $database, string $user, string $password)
    {
        $dsn = sprintf("mysql:host=%s;port=%d;dbname=%s;charset=UTF8;", $host, $port, $database);
        $this->fluid_db = new PDO($dsn, $user, $password, [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
        ]);
    }

}
