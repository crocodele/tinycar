<?php

namespace Tinycar\App\Writer;

use Tinycar\App\Writer;

class Html extends Writer
{


    /**
     * Set HTML for body
     * @param string $html new HTML
     */
    public function setHtml($html)
    {
        $this->setBody($html);
    }


    /**
     * @see Tinycar\App\Writer::output()
     */
    public function output()
    {
        // Add custom headers
        $this->addHeaders(array(
            'Content-Type'   => 'text/html; charset=UTF8',
            'Content-Length' => $this->getBodyLength(),
        ));

       // Output writer contents
        parent::output();
    }
}
