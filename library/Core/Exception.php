<?php

namespace Tinycar\Core;

class Exception extends \Exception
{
    protected $custom_code;
    protected $custom_data;


    /**
     * Initiate class
     * @param string $code custom code
     * @param array [$data] custom data
     */
    public function __construct($code, array $data = array())
    {
        // Remember custom properties
        $this->custom_code = $code;
        $this->custom_data = $data;

        // Throw native exception
        parent::__construct($code);
    }


       /**
     * Get custom code
     * @return string custom code
     */
    public function getCustomCode()
    {
        return $this->custom_code;
    }


    /**
     * Get custom data
     * @return array custom data
     */
    public function getCustomData()
    {
        return $this->custom_data;
    }
}
