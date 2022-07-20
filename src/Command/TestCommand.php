<?php

namespace App\Command;

use App\Service\OCR\Google\DocumentOcrVision;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'TestCommand',
    description: 'Add a short description for your command',
)]
class TestCommand extends Command
{

    public function __construct(
        private readonly DocumentOcrVision $documentOcrVision
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->documentOcrVision->handleResultText();
//        $files = [
//            'gs://avy-elastic-ocr/tmp-catalogs/105-62c2b06c04239.pdf',
//            'gs://avy-elastic-ocr/tmp-catalogs/10-62c2b0c055430.pdf',
//            'gs://avy-elastic-ocr/tmp-catalogs/20-62c2b0d6b4871.pdf',
//            'gs://avy-elastic-ocr/tmp-catalogs/21-62c2b0d705fe1.pdf',
//            'gs://avy-elastic-ocr/tmp-catalogs/22-62c2b0d7728b7.pdf',
//            'gs://avy-elastic-ocr/tmp-catalogs/3-111-62c2b119b7c93.pdf',
//            'gs://avy-elastic-ocr/tmp-catalogs/30KVSBI-62c2b105f3538.pdf',
//        ];
//
//        foreach ($files as $file){
//            $fileObj = (new File())
//                ->setPath($file)
//                ->setName(basename($file));
//
//            $this->documentOcrVision->handleResult('ocr-parse-results/105-62c2b06c04239.pdf');
//            dd(123);
//        }

        return Command::SUCCESS;
    }
}
