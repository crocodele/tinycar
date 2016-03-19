<?php

	namespace Tinycar\System\Application;

	use Tinycar\Core\Exception;
	use Tinycar\Core\Xml\Data;
	use Tinycar\Core\Http\Request;
	use Tinycar\System\Application;
	
	class Webhook
	{
		private $app;
		private $xdata;
		
		
		/**
		 * Initiate class
		 * @param object $app Tinycar\System\Application instance
		 * @param object $xdata Tinycar\Core\Xml\Data instance
		 */
		public function __construct(Application $app, Data $xdata)
		{
			$this->app = $app;
			$this->xdata = $xdata;
		}
		
		
		/**
		 * Call this webhook with custom data
		 * @param array [$data] custom data
		 * @return bool operation outcome
		 * @throws Tinycar\Core\Exception
		 */
		public function callUrl(array $params = array())
		{
			// Create request
			$request = new Request();
			$request->setUrl($this->getUrl());
			$request->setMethod('POST');
			$request->setFormData($params);
			
			// Send request
			$response = $request->send();
			
			// Request failure
			if ($response->getCode() !== 200)
			{
				throw new Exception('webhook_failure', array(
					'action' => $this->getAction(),
					'error'  => $response->getError(),
				));
			}
			
			return true;
		}
		
		
		/**
		 * Get webhook action name
		 * @return string|null action name or null on failure
		 */
		public function getAction()
		{
			return $this->xdata->getString('@action');			
		}
		
		
		/**
		 * Get webhook target URL
		 * @return string|null URL stringor null on failure
		 */
		public function getUrl()
		{
			return $this->xdata->getString('url');
		}
	}