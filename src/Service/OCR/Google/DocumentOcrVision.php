<?php

namespace App\Service\OCR\Google;

use App\Model\File\GoogleCloud\File;
use App\Service\OCR\OcrVisionInterface;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;

class DocumentOcrVision implements OcrVisionInterface
{

    private ImageAnnotatorClient $visionClient;

    public function __construct(string $credentials_path)
    {
        $this->visionClient = new ImageAnnotatorClient([
            'credentials' => $credentials_path
        ]);
    }

    public function detectText(File $file)
    {
        # select ocr feature
        $feature = (new Feature())
            ->setType(Type::DOCUMENT_TEXT_DETECTION);

        # set $path (file to OCR) as source
        $gcsSource = (new GcsSource())
            ->setUri($path);
        # supported mime_types are: 'application/pdf' and 'image/tiff'
        $mimeType = 'application/pdf';
        $inputConfig = (new InputConfig())
            ->setGcsSource($gcsSource)
            ->setMimeType($mimeType);

        # set $output as destination
        $gcsDestination = (new GcsDestination())
            ->setUri($output);
        # how many pages should be grouped into each json output file.
        $batchSize = 2;
        $outputConfig = (new OutputConfig())
            ->setGcsDestination($gcsDestination)
            ->setBatchSize($batchSize);

        # prepare request using configs set above
        $request = (new AsyncAnnotateFileRequest())
            ->setFeatures([$feature])
            ->setInputConfig($inputConfig)
            ->setOutputConfig($outputConfig);
        $requests = [$request];

        # make request
        $imageAnnotator = new ImageAnnotatorClient();
        $operation = $imageAnnotator->asyncBatchAnnotateFiles($requests);
        print('Waiting for operation to finish.' . PHP_EOL);
        $operation->pollUntilComplete();

        # once the request has completed and the output has been
        # written to GCS, we can list all the output files.
        preg_match('/^gs:\/\/([a-zA-Z0-9\._\-]+)\/?(\S+)?$/', $output, $match);
        $bucketName = $match[1];
        $prefix = isset($match[2]) ? $match[2] : '';

        $storage = new StorageClient();
        $bucket = $storage->bucket($bucketName);
        $options = ['prefix' => $prefix];
        $objects = $bucket->objects($options);

        # save first object for sample below
        $objects->next();
        $firstObject = $objects->current();

        # list objects with the given prefix.
        print('Output files:' . PHP_EOL);
        foreach ($objects as $object) {
            print($object->name() . PHP_EOL);
        }

        # process the first output file from GCS.
        # since we specified batch_size=2, the first response contains
        # the first two pages of the input file.
        $jsonString = $firstObject->downloadAsString();
        $firstBatch = new AnnotateFileResponse();
        $firstBatch->mergeFromJsonString($jsonString);

        # get annotation and print text
        foreach ($firstBatch->getResponses() as $response) {
            $annotation = $response->getFullTextAnnotation();
            print($annotation->getText());
        }

        $imageAnnotator->close();
    }

    public function findImageAnnotations(array $imgArray): string
    {
        // TODO: Implement findImageAnnotations() method.
    }
}