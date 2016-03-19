<?php

	namespace Tinycar\Core\Http;
	
	class Response
	{
		private $code = 0;
		private $error;
		
		
		/**
		 * Initiate class
		 * @param array $params initial properties
		 */
		public function __construct(array $params)
		{
			foreach ($params as $name => $value)
				$this->$name = $value;
		}
		
		
		/**
		 * Load from CURL response
		 * @param resource $request CURL request
		 * @param mixed $response target response
		 * @return object Tinycar\Core\Http\Response instance  
		 */
		public static function loadFromRequest($request, $response)
		{
			// Request failed
			if ($response === false)
			{
				return new self(array(
					'error' => curl_error($request),
				));
			}
					
			// Resolve properties
			return new self(array(
				'code' => intval(curl_getinfo($request, CURLINFO_HTTP_CODE)),
			));
		}
		
		
		/**
		 * Get HTTP response code
		 * @return int response code
		 */
		public function getCode()
		{
			return $this->code;
		}
		
		
		/**
		 * Get occured error 
		 * @return string|null error string or null on failure 
		 */
		public function getError()
		{
			return $this->error;	
		}
	}