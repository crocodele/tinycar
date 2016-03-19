<?php

	namespace Tinycar\System\Application\Storage;
	
	
	class RecordMap 
	{
		private $data = array();
		
		
		/**
		 * Initiate class
		 * @param array [$data] initial data
		 */
		public function __construct(array $data = array())
		{
			$this->data = $data;
		}
		
		
		/**
		 * Get number of instances in list
		 * @return int amount
		 */
		public function count()
		{
			return count($this->data);
		}
		
		
		/**
		 * Get specified key value or null on failure
		 * @param string $name target key name
		 * @return mixed|null key value or null on failure
		 */
		public function get($name)
		{
			return (array_key_exists($name, $this->data) ? 
				$this->data[$name] : null
			);
		}
		
		
		/**
		 * Get all available source data
		 * @return array data
		 */
		public function getAllData()
		{
			return $this->data;
		}
	}
