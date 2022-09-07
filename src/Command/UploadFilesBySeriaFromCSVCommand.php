<?php

namespace App\Command;

use App\Service\CatalogService;
use Generator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[AsCommand(
    name: 'UploadFilesBySeriaFromCSV',
    description: 'Add a short description for your command',
)]
class UploadFilesBySeriaFromCSVCommand extends Command
{

    private string $fluidLineUrl = 'https://fluid-line.ru/';

    public function __construct(
        private readonly CatalogService $catalogService,
        private readonly string $projectDir
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('path', InputArgument::OPTIONAL, 'new catalogs path relative project dir');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $inputPath = rtrim($input->getArgument('path'), '\\/');
        $catalogsPath = $this->projectDir . DIRECTORY_SEPARATOR . $inputPath;

        foreach ($this->sourceCsvFileIterator($catalogsPath) as $row){
            $this->handleRow($row[0], $row[1]);
        }

        $io->success('Успех');
        return Command::SUCCESS;
    }

    private function handleRow(string $seria, string $html)
    {
        $fileUrls = $this->parseAbsoluteFileUrls($html);

        foreach ($fileUrls as $fileUrl){

            $originFileName = basename($fileUrl);
            $tmpFile = $this->saveFileAsTmp($fileUrl);
            $tmpCatalogPath = stream_get_meta_data($tmpFile)['uri'];

            $this->upload($tmpCatalogPath, $originFileName, $seria);
        }
    }

    private function sourceCsvFileIterator(string $absoluteFilepath): Generator
    {
        if (!file_exists($absoluteFilepath)) {
            throw new FileNotFoundException("Файл не найден по пути `$absoluteFilepath`");
        }

        $stream = fopen($absoluteFilepath, 'r');

        while (($row = fgetcsv($stream, 0, ';', '"')) !== false) {
            yield $row;
        }
    }

    private function parseAbsoluteFileUrls(string $html): array
    {
        $result = [];
        preg_match_all('#href=\"(?:https?://fluid-line\.ru/)?([\w\/\-\']+\.\w+)\"#', $html, $matches);

        foreach ($matches[1] as $path){
            $path = ltrim($path, '/');
            $result[] = $this->fluidLineUrl . $path;
        }

        return $result;
    }

    /**
     * @param string $url
     * @return false|resource
     */
    private function saveFileAsTmp(string $url)
    {
        $temp = tmpfile();
        $temp_meta = stream_get_meta_data($temp);
        $temp_path = $temp_meta['uri'];

        file_put_contents($temp_path, file_get_contents($url));

        return $temp;
    }

    private function upload(string $filePath, string $originFileName, string $seria)
    {
        $fileMimeType = mime_content_type($filePath);
        if (false === $fileMimeType) {
            throw new \RuntimeException('Не удалось получить mime-type файла');
        }
        $file = new UploadedFile($filePath, $originFileName, $fileMimeType, null, true);

        $this->catalogService->insertCatalog();
    }

}
