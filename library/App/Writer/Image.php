<?php

namespace Tinycar\App\Writer;

use Tinycar\App\Writer;

class Image extends Writer
{


    /**
     * Load from image data
     * @param string $mime target mime type
     * @param string $data image data
     */
    public function loadFromData($mime, $data)
    {
        // Set body contents
        $this->setBody($data);

        // Set custom headers
        $this->addHeaders(array(
            'Content-Type'   => $mime,
            'Content-Length' => $this->getBodyLength(),
        ));
    }


    /**
     * Load from local image file
     * @param string $path system path to image
     */
    public function loadFromPath($path)
    {
        // Image file is missing
        if (!file_exists($path))
            return $this->setStatusCode(404);

        // Read file properties
        $size = getimagesize($path);

        // Failed to read properties
        if (!is_array($size))
            return $this->setStatusCode(404);

        // Load image from data
        $this->loadFromData(
            $mime['type'], file_get_contents($path)
        );
    }
}
