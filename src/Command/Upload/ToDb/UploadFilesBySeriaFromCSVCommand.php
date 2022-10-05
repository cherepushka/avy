<?php

namespace App\Command\Upload\ToDb;

use App\Entity\Category;
use App\Exception\FileAlreadyLoadedException;
use App\Repository\CategoryRepository;
use App\Repository\FileTypeRepository;
use App\Repository\LanguageRepository;
use App\Repository\ManufacturerRepository;
use App\Service\FileService;
use Doctrine\ORM\NonUniqueResultException;
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
        private readonly FileService $catalogService,
        private readonly CategoryRepository $categoryRepository,
        private readonly ManufacturerRepository $manufacturerRepository,
        private readonly LanguageRepository $languageRepository,
        private readonly FileTypeRepository $fileTypeRepository,
        private readonly string $projectDir
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('path', InputArgument::OPTIONAL, 'new catalogs path relative project dir');
    }

    /**
     * @throws NonUniqueResultException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $inputPath = rtrim($input->getArgument('path'), '\\/');
        $catalogsPath = $this->projectDir.DIRECTORY_SEPARATOR.$inputPath;

        foreach ($this->sourceCsvFileIterator($catalogsPath) as $row) {
            $this->handleRow($row[0], $row[1]);
        }

        $io->success('Успех');

        return Command::SUCCESS;
    }

    /**
     * @throws NonUniqueResultException
     */
    private function handleRow(string $seria, string $html)
    {
        $fileUrls = $this->parseAbsoluteFileUrls($html);

        foreach ($fileUrls as $fileUrl) {
            $originFileName = basename($fileUrl);

            $tmpFile = tmpfile();
            $tmpCatalogPath = stream_get_meta_data($tmpFile)['uri'];

            if (false === $this->uploadFileToTmp($tmpCatalogPath, $fileUrl)) {
                dump("Не удалось загрузить файл по url `$fileUrl`");
                continue;
            }

            sleep(1);

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

        foreach ($matches[1] as $path) {
            $path = ltrim($path, '/');
            $result[] = $this->fluidLineUrl.$path;
        }

        return $result;
    }

    public function uploadFileToTmp(string $TmpFilePath, string $fileUrl): false|int
    {
        return file_put_contents($TmpFilePath, file_get_contents($fileUrl));
    }

    /**
     * @throws NonUniqueResultException
     */
    private function upload(string $filePath, string $originFileName, string $seria)
    {
        $fileMimeType = mime_content_type($filePath);
        if (false === $fileMimeType) {
            throw new \RuntimeException('Не удалось получить mime-type файла');
        }

        $file = new UploadedFile($filePath, $originFileName, $fileMimeType, null, true);

        $categories = $this->categoryRepository->findAllParentsList($seria);
        $categories_ids = array_map(
            fn (Category $category) => $category->getId(),
            $categories
        );

        $defaultManufacturer = $this->manufacturerRepository->find(1);
        $defaultLanguage = $this->languageRepository->find(1);
        $defaultFileType = $this->fileTypeRepository->find(1);

        try {
            $this->catalogService->insertCatalog(
                $file,
                $originFileName,
                $defaultManufacturer->getName(),
                $categories_ids,
                $defaultLanguage->getName(),
                $defaultFileType->getType(),
            );
        } catch (FileAlreadyLoadedException $alreadyLoaded) {
            return;
        }
    }
}
