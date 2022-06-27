<?php

namespace App\Service\OCR;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Client\ClientInterface;

class GoogleOcrVision implements OcrVisionInterface
{

    private string $url = 'https://vision.googleapis.com/v1/images:annotate';
    private ClientInterface $httpClient;

    public function __construct(
        private readonly string $ocr_project_id,
        private readonly string $ocr_token,
    ){
        $this->httpClient = $this->setupHttpClient();
    }

    private function setupHttpClient(): Client
    {
        return new Client([
            RequestOptions::HEADERS => [
                "Content-Type" => "application/json; charset=utf-8",
                "Authorization" => "Bearer ". $this->ocr_token,
                "X-Goog-User-Project" => $this->ocr_project_id
            ]
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function findImageAnnotations(array $imgArray): string
    {
        $text = "";

        foreach ($imgArray as $value) {
            $content = base64_encode(file_get_contents($value));

            $json_body = [
                "requests" => [
                    [
                        "image" => ["content" => $content],
                        "features" => ["type" => "TEXT_DETECTION"]
                    ]
                ]
            ];

            $response = $this->httpClient->post($this->url, [RequestOptions::JSON => $json_body]);
            $json_response = json_decode($response->getBody()->getContents(), true);
            $text .= $json_response["responses"][0]["fullTextAnnotation"]["text"];
        }

        return $text;
    }
}