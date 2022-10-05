<?php

namespace App\Command;

use App\Model\File\CatalogFile;
use App\Repository\FileRepository;
use App\Service\OCR\OcrVisionInterface;
use App\Service\Pdf\Storage\StorageServiceFacade;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'SetPdfFilesOcrText',
    description: 'Add a short description for your command',
)]
class SetPdfFilesOcrTextCommand extends Command
{
    public function __construct(
        private readonly StorageServiceFacade $storageServiceFacade,
        private readonly OcrVisionInterface $ocrVision,
        private readonly FileRepository $fileRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pdfs = $this->fileRepository->findBy([
            'mimeType' => 'application/pdf',
            'text' => null,
        ]);

        foreach ($pdfs as $pdf) {
            $fileExtension = pathinfo($pdf->getOriginFilename(), PATHINFO_EXTENSION);
            $fileFullPath = $this->storageServiceFacade->getCatalogFullPath($pdf->getFilename());
            $file = (new CatalogFile())
                ->setMimeType($pdf->getMimeType())
                ->setByteSize($pdf->getByteSize())
                ->setExtension($fileExtension)
                ->setFullPath($fileFullPath)
                ->setName($pdf->getFilename())
                ->setOriginName($pdf->getOriginFilename());

            $text = $this->ocrVision->catalogGetTextSync($file);

            $pdf->setText($text);

            $this->fileRepository->add($pdf, true);
        }

        return Command::SUCCESS;
    }
}
