<?php
namespace App\Service\OCR;

use Exception;
use RuntimeException;

class OCRVision
{

	public function __construct(
		private readonly string $ocr_type,
        private readonly string $ocr_project_id,
        private readonly string $ocr_yandex_token,
        private readonly string $ocr_google_token
	){}

    /**
     * @throws RuntimeException
     */
    public function getOCR(): OcrVisionInterface
    {
        return match ($this->ocr_type) {
            "GOOGLE" => new GoogleOcrVision($this->ocr_project_id, $this->ocr_google_token),
            "YANDEX" => new YandexOcrVision($this->ocr_project_id, $this->ocr_yandex_token),
            default => throw new RuntimeException("OCR TYPE INVALID"),
        };
    }

    /**
     * @throws Exception
     */
//    public function findImageAnnotations(array $imgArray): string
//    {
//		$text = "";
//		foreach ($imgArray as $value) {
//			$content = base64_encode(file_get_contents($value));
//
//			switch ($this->ocr_type) {
//				case "GOOGLE":
//					$url = "https://vision.googleapis.com/v1/images:annotate";
//					$headers = [
//						'Content-Type:application/json; charset=utf-8',
//						"Authorization: Bearer ".$this->ocr_google_token,
//						"X-Goog-User-Project: ".$this->ocr_project_id
//					];
//					$json_body = [
//						"requests" => [
//							[
//								"image" => [
//									"content" => $content
//								],
//								"features" => [
//									"type" => "TEXT_DETECTION"
//								]
//							]
//						]
//					];
//
//					$text .= new RequestController($url, $headers, $json_body);
//					break;
//
//				case "YANDEX":
//					$url = "https://vision.api.cloud.yandex.net/vision/v1/batchAnalyze";
//					$headers = [
//						'Content-Type:application/json',
//						"Authorization: Bearer ".$this->ocr_yandex_token
//					];
//					$json_body = [
//						"folderId" => $this->ocr_project_id,
//						"analyze_specs" => [
//							"content" => $content,
//							"features" => [
//								"type" => "TEXT_DETECTION",
//					            "text_detection_config" => [
//					                "language_codes" => ["*"]
//					            ]
//							]
//						]
//					];
//
//					$text .= new RequestController($url, $headers, $json_body);
//					break;
//
//				default:
//					throw new Exception("OCR TYPE INVALID", 1337);
//			}
//		}
//
//		if (!is_file($imgArray[0].".json"))
//			touch($imgArray[0].".json");
//
//		file_put_contents($imgArray[0].".json", $text);
//
//		return $text;
//	}

}