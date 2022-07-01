<?php

namespace App\Command;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\RuntimeException;

#[AsCommand(
    name: 'MigrateTreeFromJson',
    description: 'Upload category tree structure from json to category_tree table',
)]
class MigrateTreeFromJsonCommand extends Command
{

    public function __construct(
        private readonly string $projectDir,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('treePath', InputArgument::REQUIRED, 'Path to JSON with TreeData relative to the project root');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $JSON = $this->loadTreeFromJson($input->getArgument('treePath'));

        $this->saveItems($JSON);

        return Command::SUCCESS;
    }

    private function saveItems(array $items, ?Category $categoryParentItem = null)
    {
        foreach ($items as $item) {
            $categoryNewItem = (new Category())
                ->setId($item['id'])
                ->setTitle($item['title'])
                ->setLink($item['link']);

            if ($categoryParentItem){
                $categoryNewItem->setParent($categoryParentItem);
                $categoryParentItem->addChild($categoryNewItem);

                $this->em->persist($categoryParentItem);
            }

            $this->em->persist($categoryNewItem);
            $this->em->flush();

            if( isset($item['children']) ) {
                $this->saveItems($item['children'], $categoryNewItem);
            }
        }
    }

    private function loadTreeFromJson(string $path): array
    {
        $path = ltrim($path, "\\/");

        if ( !is_file($this->projectDir . \DIRECTORY_SEPARATOR . $path) ){
            throw new FileNotFoundException("Cannot load tree with path '$path'");
        }

        $JSON = file_get_contents($path);
        if (!$JSON){
            throw new RuntimeException("Error with getting body of file");
        }

        return json_decode($JSON, true);
    }
}
