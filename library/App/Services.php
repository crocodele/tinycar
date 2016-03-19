<?php

	namespace Tinycar\App;
	
	use Tinycar\App\Manager;
	use Tinycar\Core\Http\Params;
	
	class Services
	{
		private $services = array();
		private $app; 
		
		
		/**
		 * Initiate class
		 * @param object $app Manager instance
		 */
		public function __construct(Manager $app)
		{
			$this->app = $app;
		}
		
		
		/**
		 * Call specified service
         * @param string $path target service path
         * @param array [$params] service parameters
         * @return mixed service response
		 */
		public function callService($name, array $params = array())
		{
			return $this->services[$name](new Params($params));
		}
		
		
		/**
		 * Check if specified service exists
		 * @param string $name target service name
		 * @return bool service exists
		 */
		public function hasService($name)
		{
			return array_key_exists($name, $this->services);
		}
		
		
		/**
		 * Set new specified service
		 * @param string $name target service name
		 * @param function $handler custom service handler
		 */
		public function setService($name, $handler)
		{
			$this->services[$name] = $handler;
		}
	}
	