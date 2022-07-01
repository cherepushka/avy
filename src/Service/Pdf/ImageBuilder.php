<?php
namespace App\Service\Pdf;

use Imagick;
use ImagickException;
use ImagickPixel;

class ImageBuilder 
{
	private Imagick $imagick;

    /**
     * @throws ImagickException
     */
    public function __construct() {
		$this->imagick = new Imagick();
		$this->imagick->setResolution(300, 300);
	}

    /**
     * Generates images from pdf file pages
     *
     * @param string $filepath - path to source pdf file
     * @return string[] - array of absolute paths to images
     * @throws ImagickException
     */
    public function generateImagickImages(string $filepath): array
    {
		$imgArray = [];

		$this->imagick->readImage($filepath);

		for ($i = 0; $i < $this->imagick->getNumberImages(); $i++) {
			$this->imagick->previousImage();

			$upload = explode(".", $filepath)[0];
			if (!is_dir($upload)) {
                mkdir($upload);
            }

			$this->imagickSettings();

			$upload_file = $upload."/$i.png";

	        $this->imagick->writeImage($upload_file);

	        $imgArray[] = $upload_file;
		}
			
		$this->imagick->clear();

		return array_reverse($imgArray);
	}

    /**
     * @throws ImagickException
     */
    private function imagickSettings(): void
    {
		$this->imagick->setImageFormat('png'); // Объявляется после чтения файла.
		$this->imagick->setBackgroundColor(new ImagickPixel('#ffffff')); // Объявляется после чтения файла.
	    $this->imagick->setImageAlphaChannel($this->imagick::ALPHACHANNEL_REMOVE); // Объявляется после чтения файла.

	    $this->imagick->setImageCompressionQuality(95); // Объявляется перед записью файла.
	    $this->imagick->resizeImage(
            $this->imagick->getImageWidth() / 1.25,
            $this->imagick->getImageHeight() / 1.25,
            null,
            0
        ); // Объявляется после чтения файла.
	}

    /**
     * Deleting images with directory
     *
     * @param string[] $imgArray - array of absolute paths of images
     * @return void
     */
    public function deleteGeneratedImagesWithDir(array $imgArray): void
    {
        $tmpDir = dirname($imgArray[0]);

        foreach ($imgArray as $imagePath) {
            unlink($imagePath);
        }

        rmdir($tmpDir);
    }

    /**
     * Return `true` if provided PDF file is unable to open and parse further
     *
     * @param string $filepath - absolute path to PDF
     * @return bool - true - corrupted, false - is OK
     */
    public function checkIsPdfCorrupted(string $filepath): bool
    {
        try {
            $this->imagick->readImage($filepath);
            $isCorrupted = !$this->imagick->valid();
            $this->imagick->clear();

            return $isCorrupted;
        } catch (ImagickException){
            return true;
        }
    }

}