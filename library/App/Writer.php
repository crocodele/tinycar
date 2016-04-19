<?php

    namespace Tinycar\App;

    use Tinycar\Core\Exception;


    class Writer
    {
        private $body = '';
        private $headers = array();


        /**
         * Load writer by type
         * @param string $type target type
         * @return object Tinycar\App\Writer derived instance
         * @throws Tinycar\Core\Exception
         */
        public static function loadByType($type)
        {
			// Try create new class instance
			try
			{
				$reflect = new \ReflectionClass(
					'\\Tinycar\\App\\Writer\\'.$type
				);
	    	}
			// Invalid writer type
			catch (\ReflectionException $Exception)
	    	{
				throw new Exception('invalid_writer_type', array(
					'type' => $type
				));
			}

			// Create new module instance
			$result = $reflect->newInstanceArgs(array());
			$result->init();

			return $result;
        }


        /**
         * Initiate writer type
         */
        public function init()
        {
        }


        /**
         * Add new headers to writer
         * @param array $map headers in key-value pairs
         */
        protected function addHeaders(array $map)
        {
            $this->headers = array_merge($this->headers, $map);
        }


        /**
         * Get length for custom response body
         * @return int body length
         */
        protected function getBodyLength()
        {
            return mb_strlen($this->body);
        }


        /**
         * Set custom response body
         * @param string $body new body
         */
        protected function setBody($body)
        {
            $this->body = $body;
        }


        /**
         * Output writer contents to browser
         */
        public function output()
        {
            // Send custom headers
            foreach ($this->headers as $name => $value)
                header($name.': '.$value);

            // Output writer contents
            echo $this->body;
        }
    }