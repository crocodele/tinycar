<?php

namespace Tinycar\Core\Http;

use Tinycar\Core\Http\Response;

class Request
{
    private $data;
    private $method = 'GET';
    private $timeout = 10;
    private $url;


    /**
     * Try to send current request
     * @return object Tinycar\Core\Http\Response instance
     */
    public function send()
    {
        // Create new instance
        $request = curl_init();

        // Set options
        curl_setopt($request, CURLOPT_URL, $this->url);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 0);
        curl_setopt($request, CURLOPT_TIMEOUT, $this->timeout);

        // Post method
        if ($this->method === 'POST')
            curl_setopt($request, CURLOPT_POST, 1);

        // Post form data
        if ($this->method === 'POST' && is_array($this->data))
        {
            curl_setopt($request, CURLOPT_POSTFIELDS,
                http_build_query($this->data)
            );
        }

        // Try to execute
        $response = curl_exec($request);

        // Create response instance
        $result = Response::loadFromRequest($request, $response);

        // Close connection
        if ($response !== false)
            curl_close($request);

        return $result;
    }


    /**
     * Set form data
     * @param array $data new form data
     */
    public function setFormData(array $data)
    {
        $this->data = $data;
    }


    /**
     * Set target method
     * @param string $type new type
     */
    public function setMethod($type)
    {
        $this->method = $type;
    }

    /**
     * Set target URL
     * @param string $url target URL
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }
}
