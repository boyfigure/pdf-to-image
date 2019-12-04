<?php

namespace Offspring\FileToThumbnail;

use Imagick;
use Offspring\FileToThumbnail\Exceptions\InvalidFormat;
use Offspring\FileToThumbnail\Exceptions\FileDoesNotExist;
use Offspring\FileToThumbnail\Exceptions\PageDoesNotExist;
use Offspring\FileToThumbnail\Exceptions\InvalidLayerMethod;

class FileToThumbnail
{
    protected $file;

    protected $resolution = 144;

    protected $outputFormat = 'jpg';

    protected $page = 1;

    public $imagick;

    protected $numberOfPages;

    protected $validOutputFormats = ['jpg', 'jpeg', 'png'];

    protected $layerMethod = Imagick::LAYERMETHOD_FLATTEN;

    protected $colorspace;

    protected $compressionQuality;

    protected $thumbnail_height = 0;

    protected $thumbnail_width = 0;

    public function __construct($file)
    {
        if (!filter_var($file, FILTER_VALIDATE_URL) && !file_exists($file)) {
            throw new FileDoesNotExist("File `{$file}` does not exist");
        }
        // Imagick::setRegistry('temporary-path', sys_get_temp_dir());

        $this->imagick = new Imagick();
        $this->imagick->setResourceLimit(Imagick::RESOURCETYPE_THREAD, 1);
        $this->imagick->setResourceLimit(Imagick::RESOURCETYPE_AREA, 10000000);
        $this->imagick->setResourceLimit(Imagick::RESOURCETYPE_MEMORY, 1024000000);
        $this->imagick->setResourceLimit(Imagick::RESOURCETYPE_MAP, 1024000000);

        $this->imagick->setResourceLimit(Imagick::RESOURCETYPE_DISK, -1);

        $this->file = $file;
    }

    /**
     * Set the raster resolution.
     *
     * @param int $resolution
     *
     * @return $this
     */
    public function setResolution($resolution)
    {
        $this->resolution = $resolution;

        return $this;
    }


    public function setOutputFormat($outputFormat)
    {
        if (!$this->isValidOutputFormat($outputFormat)) {
            throw new InvalidFormat("Format {$outputFormat} is not supported");
        }

        $this->outputFormat = $outputFormat;

        return $this;
    }

    /**
     * Get the output format.
     *
     * @return string
     */
    public function getOutputFormat()
    {
        return $this->outputFormat;
    }


    public function setLayerMethod($layerMethod)
    {
        if (
            is_int($layerMethod) === false &&
            is_null($layerMethod) === false
        ) {
            throw new InvalidLayerMethod('LayerMethod must be an integer or null');
        }

        $this->layerMethod = $layerMethod;

        return $this;
    }

    public function setThumbnail($with, $height)
    {
        $this->thumbnail_height = $height;

        $this->thumbnail_width = $with;

        return $this;
    }

    /**
     * Determine if the given format is a valid output format.
     *
     * @param $outputFormat
     *
     * @return bool
     */
    public function isValidOutputFormat($outputFormat)
    {
        return in_array($outputFormat, $this->validOutputFormats);
    }


    public function setPage($page)
    {
        if ($page > $this->getNumberOfPages() || $page < 1) {
            throw new PageDoesNotExist("Page {$page} does not exist");
        }

        $this->page = $page;

        return $this;
    }

    /**
     * Get the number of pages in the file.
     *
     * @return int
     */
    public function getNumberOfPages()
    {
        return $this->numberOfPages;
    }

    /**
     * Save the image to the given path.
     *
     * @param string $pathToImage
     *
     * @return bool
     */
    public function saveImage($pathToImage)
    {
        if (is_dir($pathToImage)) {
            $pathToImage = rtrim($pathToImage, '\/') . DIRECTORY_SEPARATOR . $this->page . '.' . $this->outputFormat;
        }
        $imageData = $this->getImageData($pathToImage);


        return file_put_contents($pathToImage, $imageData) !== false;
    }

    public function getImageBlob($pathToImage)
    {
        $imageData = $this->getImageData($pathToImage);
        return $imageData->getimageblob();

    }


    /**
     * Save the file as images to the given directory.
     *
     * @param string $directory
     * @param string $prefix
     *
     * @return array $files the paths to the created images
     */
    public function saveAllPagesAsImages($directory, $prefix = '')
    {
        $numberOfPages = $this->getNumberOfPages();

        if ($numberOfPages === 0) {
            return [];
        }

        return array_map(function ($pageNumber) use ($directory, $prefix) {
            $this->setPage($pageNumber);

            $destination = "{$directory}/{$prefix}{$pageNumber}.{$this->outputFormat}";

            $this->saveImage($destination);

            return $destination;
        }, range(1, $numberOfPages));
    }

    /**
     * Return raw image data.
     *
     * @param string $pathToImage
     *
     * @return \Imagick
     */
    public function getImageData($pathToImage)
    {
        $this->imagick->setResolution($this->resolution, $this->resolution);

        if ($this->colorspace !== null) {
            $this->imagick->setColorspace($this->colorspace);
        }

        if ($this->compressionQuality !== null) {
            $this->imagick->setCompressionQuality($this->compressionQuality);
        }

        if (filter_var($this->file, FILTER_VALIDATE_URL)) {
            return $this->getRemoteImageData($pathToImage);
        }


        $this->imagick->readImage(sprintf('%s[%s]', $this->file, $this->page - 1));

        if ($this->thumbnail_width != 0 && $this->thumbnail_height != 0) {
            $this->imagick->thumbnailImage($this->thumbnail_width, $this->thumbnail_height, true, true);
        }

        if (is_int($this->layerMethod)) {
            $this->imagick = $this->imagick->mergeImageLayers($this->layerMethod);
        }

        $this->imagick->setFormat($this->determineOutputFormat($pathToImage));

        return $this->imagick;
    }


    /**
     * @param int $colorspace
     *
     * @return $this
     */
    public function setColorspace(int $colorspace)
    {
        $this->colorspace = $colorspace;

        return $this;
    }

    /**
     * @param int $compressionQuality
     *
     * @return $this
     */
    public function setCompressionQuality(int $compressionQuality)
    {
        $this->compressionQuality = $compressionQuality;

        return $this;
    }


    protected function getRemoteImageData($pathToImage)
    {
        $this->imagick->readImage($this->file);

        $this->imagick->setIteratorIndex($this->page - 1);

        if ($this->thumbnail_width != 0 && $this->thumbnail_height != 0) {
//            $this->imagick->thumbnailImage($this->thumbnail_width, $this->thumbnail_height, true, true);
//            $this->imagick->cropThumbnailImage($this->thumbnail_width, $this->thumbnail_height, true);
            $this->imagick->resizeImage($this->thumbnail_width, null, Imagick::FILTER_LANCZOS, 1);
        }

        if (is_int($this->layerMethod)) {
            $this->imagick = $this->imagick->mergeImageLayers($this->layerMethod);
        }

        $this->imagick->setFormat($this->determineOutputFormat($pathToImage));

        return $this->imagick;
    }

    /**
     * Determine in which format the image must be rendered.
     *
     * @param $pathToImage
     *
     * @return string
     */
    protected function determineOutputFormat($pathToImage)
    {
        $outputFormat = pathinfo($pathToImage, PATHINFO_EXTENSION);

        if ($this->outputFormat != '') {
            $outputFormat = $this->outputFormat;
        }

        $outputFormat = strtolower($outputFormat);

        if (!$this->isValidOutputFormat($outputFormat)) {
            $outputFormat = 'jpg';
        }

        return $outputFormat;
    }

    public function clear()
    {
        $this->imagick->clear();
        return true;
    }
}
