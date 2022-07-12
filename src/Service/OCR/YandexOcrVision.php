<?php

namespace App\Service\OCR;

class YandexOcrVision implements OcrVisionInterface
{

    private string $url = "https://vision.api.cloud.yandex.net/vision/v1/batchAnalyze";

    public function __construct(
        private readonly string $ocr_project_id,
        private readonly string $ocr_yandex_token,
    ){}

    public function findImageAnnotations(array $imgArray): string
    {
        $text = "";

        foreach ($imgArray as $value) {
            $content = base64_encode(file_get_contents($value));

            $headers = [
                'Content-Type:application/json',
                "Authorization: Bearer ".$this->ocr_yandex_token
            ];
            $json_body = [
                "folderId" => $this->ocr_project_id,
                "analyze_specs" => [
                    "content" => $content,
                    "features" => [
                        "type" => "TEXT_DETECTION",
                        "text_detection_config" => [
                            "language_codes" => ["*"]
                        ]
                    ]
                ]
            ];

//            $text .= new RequestController($this->url, $headers, $json_body);
        }

        return $text;
    }
}