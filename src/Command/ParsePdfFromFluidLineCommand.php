<?php

namespace App\Command;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\LanguageRepository;
use App\Repository\ManufacturerRepository;
use App\Service\ParseQueueService;
use App\Service\Pdf\ImageBuilder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Http\Client\Exception;
use PDO;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Throwable;

#[AsCommand(
    name: 'ParsePdfFromFluidLine',
    description: 'Parse pdf catalogs from fluid-line.ru',
)]
class ParsePdfFromFluidLineCommand extends Command
{

    private string $fluid_address = "https://fluid-line.ru";
    private readonly PDO $fluid_db;

    //list of pdfs, that`s should not be saved
    private array $except_pdfs = [
        'opros_needles.pdf',
        'opros-drastar.pdf'
    ];

    public function __construct(
        private readonly ParseQueueService $parseQueueService,
        private readonly CategoryRepository $categoryRepository,
        private readonly ManufacturerRepository $manufacturerRepository,
        private readonly LanguageRepository $languageRepository,
        private readonly ImageBuilder $imageBuilder,
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

        foreach ($categories as $category){
            try {
                $this->handleCategoryPdfs($category);
            } catch (Throwable $e){
                // TODO бработать исключения
            }
        }

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

    /**
     * @throws RuntimeException
     * @throws Throwable
     */
    private function handleCategoryPdfs(Category $category)
    {
        $category_id = $category->getId();

        $query = $this->fluid_db->query(
            "SELECT `value` FROM modx_site_tmplvar_contentvalues WHERE contentid = $category_id"
        );
        $template_vars = $query->fetchAll();

        if (empty($template_vars)){
            return;
        }

        foreach ($template_vars as $template_var) {
            $var_value = $template_var['value'];

            preg_match_all('#(?:http(?:s)?\:\/\/fluid-line\.ru\/)?((?:[\w\/\-\'\.])+\.pdf)#', $var_value, $matches);
            foreach ($matches[1] as $match){
                try {
                    $this->handleMatch($match, $category_id);
                } catch (Throwable $e){
                    dump($e->getMessage());
                    continue;
                }
            }
        }
    }

    /**
     * @throws NonUniqueResultException
     */
    private function handleMatch(string $match, int $category_id)
    {
        if (in_array(basename($match), $this->except_pdfs)){
            return;
        }

        $catalogLink = $this->fluid_address . '/' . $match;
        $tmpCatalog = $this->saveRemotePdfAsTmp($catalogLink);
        $tmpCatalogPath = stream_get_meta_data($tmpCatalog)['uri'];

        $categories = new ArrayCollection($this->categoryRepository->findAllParentsList($category_id));

        if ($this->imageBuilder->checkIsPdfCorrupted($tmpCatalogPath)){
            fclose($tmpCatalog);
            throw new RuntimeException('Downloaded PDF file is corrupted');
        }

        $file = new UploadedFile($tmpCatalogPath, basename($match), "application/pdf", null, true);

        $this->parseQueueService->enqueueFile(
            $file,
            $this->manufacturerRepository->findOneByName('Hy-Lock'),
            $this->languageRepository->findOneByName('Русский'),
            $categories
        );

        fclose($tmpCatalog);
    }

    /**
     * @param string $url
     * @return resource
     */
    private function saveRemotePdfAsTmp(string $url)
    {
        $temp = tmpfile();
        $temp_meta = stream_get_meta_data($temp);
        $temp_path = $temp_meta['uri'];

        file_put_contents($temp_path, file_get_contents($url));

        return $temp;
    }

}
