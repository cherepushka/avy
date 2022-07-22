<?php

namespace App\Service\OCR\Google;

use App\Entity\Catalog;
use App\Model\File\CatalogFile;
use App\Model\File\GoogleCloud\File;
use App\Repository\CatalogRepository;
use App\Service\OCR\Google\Filters\ContentFilter;
use App\Service\OCR\OcrVisionInterface;
use App\Service\Pdf\Storage\GoogleCloud\CatalogStorageService;
use App\Service\Pdf\Storage\GoogleCloud\OcrResultStorageService;
use App\Service\Pdf\Storage\StorageServiceFacade;
use Exception;
use Google\ApiCore\ApiException;
use Google\ApiCore\ValidationException;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Vision\V1\AnnotateFileResponse;
use Google\Cloud\Vision\V1\AsyncAnnotateFileRequest;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Feature\Type;
use Google\Cloud\Vision\V1\GcsDestination;
use Google\Cloud\Vision\V1\GcsSource;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\InputConfig;
use Google\Cloud\Vision\V1\OutputConfig;

class DocumentOcrVision implements OcrVisionInterface
{

    private ImageAnnotatorClient $visionClient;
    private StorageClient $storageClient;
    private OcrResultStorageService $ocrResultStorageService;
    private readonly ContentFilter $contentFilter;

    private StorageServiceFacade $storageServiceFacade;
    private CatalogStorageService $gcCatalogStorageService;

    /**
     * @throws ValidationException
     */
    public function __construct(
        string                             $credentials_path,
        StorageServiceFacade               $storageServiceFacade,
        CatalogStorageService              $gcCatalogStorageService,
        OcrResultStorageService            $ocrResultStorageService,
        ContentFilter                      $contentFilter,
    )
    {
        $this->ocrResultStorageService = $ocrResultStorageService;
        $this->contentFilter = $contentFilter;

        $this->storageServiceFacade = $storageServiceFacade;
        $this->gcCatalogStorageService = $gcCatalogStorageService;

        $this->storageClient = new StorageClient([
            'keyFilePath' => $credentials_path
        ]);

        $this->visionClient = new ImageAnnotatorClient([
            'credentials' => $credentials_path
        ]);
    }

    public function catalogGetTextSync(CatalogFile $file): string
    {
        //Catalog must be stored on GoogleStorage for this type of OCR handling
        if (!$this->gcCatalogStorageService->exists($file->getName())){

        }

        $text = '';

//        $feature = (new Feature())->setType(Type::DOCUMENT_TEXT_DETECTION);
//        $inputConfig = $this->generateInputConf($file->getPath());
//        $outputConfig = $this->generateOutputConf($resultStorageDir);

        return $text;
    }

    /**
     * @throws ApiException
     * @throws ValidationException
     * @throws Exception
     */
    public function detectText(File $file): string
    {
        $resultStorageDir = $this->ocrResultStorageService->getStoragePath() . DIRECTORY_SEPARATOR . $file->getName() . DIRECTORY_SEPARATOR;

        $feature = (new Feature())->setType(Type::DOCUMENT_TEXT_DETECTION);
        $inputConfig = $this->generateInputConf($file->getPath());
        $outputConfig = $this->generateOutputConf($resultStorageDir);

        # prepare request using configs set above
        $request = (new AsyncAnnotateFileRequest())
            ->setFeatures([$feature])
            ->setInputConfig($inputConfig)
            ->setOutputConfig($outputConfig);
        $requests = [$request];

        # make request
        $operation = $this->visionClient->asyncBatchAnnotateFiles($requests);
        $operation->pollUntilComplete();

        return $this->handleResult($this->ocrResultStorageService->getStorageDir() .DIRECTORY_SEPARATOR . $file->getName() . DIRECTORY_SEPARATOR);
    }

    /**
     * @throws Exception
     */
    public function handleResult(string $gsResultsDir): string
    {
        $bucket = $this->storageClient->bucket($this->ocrResultStorageService->getStorageBucket());
        $objects = $bucket->objects([
            'prefix' => $gsResultsDir
        ]);

        $text = '';

        foreach ($objects as $object) {

            $jsonString = $object->downloadAsString();
            $file_response = new AnnotateFileResponse();
            $file_response->mergeFromJsonString($jsonString);

            $this->contentFilter->setResponse($file_response);
            $text .= $this->contentFilter->getFilteredText();
        }

        return $text;
    }

    private function generateInputConf(string $inputFilePath): InputConfig
    {
        // set $path (file to OCR) as source
        $gcsSource = (new GcsSource())
            ->setUri($inputFilePath);

        $mimeType = 'application/pdf';

        return (new InputConfig())
            ->setGcsSource($gcsSource)
            ->setMimeType($mimeType);
    }

    private function generateOutputConf(string $resultStorageDir): OutputConfig
    {
        // how many pages should be grouped into each json output file.
        $batchSize = 1;

        // set $output as destination
        $gcsDestination = (new GcsDestination())
            ->setUri($resultStorageDir);

        return (new OutputConfig())
            ->setGcsDestination($gcsDestination)
            ->setBatchSize($batchSize);
    }

}