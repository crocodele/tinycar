<?php

	namespace Tinycar\System\Application\Storage;
	
	use Tinycar\System\Application\Storage\Record;
	
	
	class RecordList
	{
		private $list = array();
		
		
		/**
		 * Add a new record instance to list
		 * @param object $record target Tinycar\System\Application\Storage\Record instance 
		 */
		public function add(Record $record)
		{
			$this->list[] = $record;
		}
		
		
		/**
		 * Get number of instances in list
		 * @return int amount
		 */
		public function count()
		{
			return count($this->list);
		}
		
		
		/**
		 * Get first item from list
		 * @return object|null Tinycar\System\Application\Storage\Record instance
		 *                     or null on failure
		 */
		public function first()
		{
			return (count($this->list) > 0 ? 
				$this->list[0] : null
			);
		}
		
		
		/**
		 * Get all available source data
		 * @return array data
		 */
		public function getAllData()
		{
			$result = array();
			
			foreach ($this->list as $item)
				$result[] = $item->getAllData();
			
			return $result;
		}
		
		
		/**
		 * Sort record instances by property
		 * @param object $property Tinycar\System\Application\Model\Property instance
		 * @param string $sort sorting order (asc|desc)
		 */
		public function sortByProperty($property, $sort)
		{
			// Comparison variables
			$name = $property->getName();
			$type = $property->getNativeType();
			$sort = ($sort === 'asc');
			
			// Custom comparison
			usort($this->list, function(Record $a, Record $b) use ($name, $type, $sort) 
			{
				$v1 = $sort ? $a->getData($name) : $b->getData($name);
				$v2 = $sort ? $b->getData($name) : $a->getData($name);
				
				// Compare as numbers
				if ($type === 'int')
					return $v1 - $v2;
				
				// Compare as strings
				return strcasecmp($v1, $v2);
			});
		}
		
	}
