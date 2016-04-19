<?php

    namespace Tinycar\App\Writer;

    use Tinycar\App\Writer;


    class JsonRpc extends Writer
    {
        private $error;
        private $request_id;
        private $result;


        /**
         * Set response error
         * @param string $id error id
         * @param string $code error code
         * @param array $data error data
         */
        public function setError($id, $code, array $data)
        {
            $this->error = array(
                'id'      => $id,
                'code'    => $code,
                'message' => $data,
            );
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