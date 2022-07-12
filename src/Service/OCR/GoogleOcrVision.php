<?php

namespace App\Service\OCR;

use Google\ApiCore\ApiException;
use Google\ApiCore\ValidationException;
use Google\Cloud\Vision\V1\Feature\Type;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;

class GoogleOcrVision implements OcrVisionInterface
{

    private ImageAnnotatorClient $visionClient;

    /**
     * @throws ValidationException
     */
    public function __construct(string $credentials_path){
        $this->visionClient = new ImageAnnotatorClient([
            'credentials' => $credentials_path
        ]);
    }

    /**
     * @throws ApiException
     */
    public function findImageAnnotations(array $imgArray): string
    {
        $text = "";

        foreach ($imgArray as $value) {
            $imageStream = $this->getImageResource($value);

            $response = $this->visionClient->annotateImage($imageStream, [
                Type::TEXT_DETECTION
            ]);

            $this->closeImageResource($imageStream);

            if ($response->getFullTextAnnotation() !== null){
                $response_text = $response->getFullTextAnnotation()->getText();
                $text .= trim($response_text);
            }
        }

        return $text;
    }

    /**
     * @param string $imagePath
     * @return false|resource
     */
    private function getImageResource(string $imagePath)
    {
        return fopen($imagePath, 'r');
    }

    /**
     * @param resource $resource
     * @return bool
     */
    private function closeImageResource($resource): bool
    {
        return fclose($resource);
    }

}