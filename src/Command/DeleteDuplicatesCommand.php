<?php

namespace App\Command;

use App\Entity\Catalog;
use App\Repository\CatalogRepository;
use App\Service\Pdf\CatalogFileService;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'DeleteDuplicates',
    description: 'Add a short description for your command',
)]
class DeleteDuplicatesCommand extends Command
{

    public function __construct(
        private readonly CatalogRepository $catalogRepository,
        private readonly CatalogFileService $catalogFileService
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {}

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        /** @var Catalog $catalog */
        foreach($this->catalogRepository->findAll() as $catalog){

            $sameCatalogs = $this->catalogRepository->findAllByOriginFilename($catalog->getOriginFilename());
            if(count($sameCatalogs) > 1){

                $categories = new ArrayCollection();

                foreach($sameCatalogs as $sameCatalog) foreach($sameCatalog->getCategories() as $sameCategory){
                    $categories->set($sameCategory->getId(), $sameCategory);
                }

                foreach($sameCatalogs as $key => $sameCatalog){
                    if($sameCatalog->getId() === $catalog->getId()){
                        unset($sameCatalogs[$key]);
                    }
                }

                $this->deleteCatalogs($sameCatalogs);

                $catalog->setCategories($categories);
                $this->catalogRepository->add($catalog, true);
            }
        }

        return Command::SUCCESS;
    }

    private function deleteCatalogs(array $catalogs)
    {
        foreach($catalogs as $catalog){
            $this->catalogFileService->removeCatalog($catalog->getFilename());
            $this->catalogRepository->remove($catalog, true);
        }
    }
}
