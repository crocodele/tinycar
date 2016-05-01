<?php

    namespace Tinycar\App\Writer;

    use Tinycar\App\Writer;


    class JsonRpc extends Writer
    {
        private $error;
        private $request_id;
        private $result;

        // JSON-RPC 1.2 status codes
        private $status_codes = array(
            -32700             => 500,   // Parse error
            -32600             => 400,   // Invalid Request
            -32601             => 404,   // Method not found
            -32602             => 500,   // Invalid params
            -32700             => 500,   // Internal error
            // -32099.. -32000 => 500    // Server error
        );


        /**
         * Set response error
         * @param string $id error id
         * @param string $code error code
         * @param array $data error data
         */
        public function setError($id, $code, array $data)
        {
            // Remember error response
            $this->error = array(
                'id'      => $id,
                'code'    => $code,
                'message' => $data,
            );

            // Response error status code
            $code = (array_key_exists($id, $this->status_codes) ?
                $this->status_codes[$id] : 500
            );

            // Set custom status code
            $this->setStatusCode($code);
        }


        /**
         * Set request id
         * @param int|null $id request id
         */
        public function setRequestId($id)
        {
            $this->request_id = $id;
        }


        /**
         * Set result data
         * @param mixed $data new data
         */
        public function setResult($data)
        {
            $this->result = $data;
        }


        /**
         * @see Tinycar\App\Writer::output()
         */
        public function output()
        {
            // We have an error
            if (is_array($this->error))
            {
                $response = array(
                    'id'    => $this->request_id,
                    'error' => $this->error,
                );
            }
            // We have a result
            else
            {
                $response = array(
                    'id'     => $this->request_id,
                    'result' => $this->result,
                );
            }

            // Set response body
            $this->setBody((defined('JSON_PRETTY_PRINT') ?
                json_encode($response, JSON_PRETTY_PRINT) :
                json_encode($response)
            ));

            // Add custom headers
            $this->addHeaders(array(
                'Content-Type'   => 'application/json; charset=UTF8',
                'Content-Length' => $this->getBodyLength(),
            ));

            // Output writer contents
            parent::output();
        }
    }