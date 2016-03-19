<?php

	namespace Tinycar\System\Application\Storage;
	
	use Tinycar\Core\Exception;
	use Tinycar\System\Application\Storage;
	use Tinycar\System\Application\Storage\BaseQuery;
	
	
	class KeyQuery extends BaseQuery
	{
		protected $sql_order = 'key';
		protected $sql_sort = 'asc';
		
		
		/**
		 * Add filters for results
		 * @param array $filters map of filters
		 * @throws Tinycar\Core\Exception
		 */
		public function filter(array $filters)
		{
			// Pick valid filters
			foreach ($filters as $name => $value)
			{
				// Invalid filter property
				if ($name !== 'key' && $name !== 'value')
				{
					throw new Exception(
						'query_filter_unknown', 
						array('filter' => $name)
					);
				}
				
				// Add to list
				$this->sql_filters[] = array(
					'name'  => $name,
					'value' => $value,
				);
			}
		}
		
		
		/**
		 * Search for requested results
		 * @return object Tinycar\System\Application\Storage\RecordMap instance
		 */
		public function find()
		{
			// Resolve SQL to use
			$sql = '
				SELECT name AS key, value 
				FROM data_keys '.
				$this->getSqlForWhere().' '.
				$this->getSqlForOrder().' '.
				$this->getSqlForLimit();

			// Get new instance from row data
			return new RecordMap(
				$this->query->getKeyPairs($sql)
			);
		}
		
		
		/**
		 * Get SQL string to use for ordering
		 * @return string order string
		 */
		protected function getSqlForOrder()
		{
			// Do not order when limited to one
			return ($this->sql_limit === 1 ? 
				'' : 'ORDER BY '.$this->sql_order.' '.$this->sql_sort
			);
		}
		
		
		/**
		 * Get SQL string to use to filter results
		 * @return string where conditions string
		 */
		protected function getSqlForWhere()
		{
			$result = array();
			
			// Show only removed
			if ($this->sql_removed === true)
				$result[] = 'removed_time IS NOT NULL';
			
			// Excelude removed
			else if ($this->sql_removed === false)
				$result[] = 'removed_time IS NULL';
			
			// Add filters for name or value 
			foreach ($this->sql_filters as $item)
			{
				$this->query->bind('v'.$item['name'].':string', $item['value']);
				$result[] = sprintf('%s=:v%s', $item['name'], $item['name']);
			}
			
			// No conditions at all
			if (count($result) === 0)
				return '';
			
			// Wrap conditions
			return 'WHERE '.implode(' AND ', $result);
		}
		
		
		/**
		 * Set result order
		 * @param string $order target property to sort by
		 * @param string $sort sorting deiction (asc|desc)
		 * @throws Tinycar\Core\Exception
		 */
		public function order($order, $sort)
		{
			// Invalid value
			if ($order !== 'key' && $order !== 'value')
				throw new Exception('query_order_unknown');
			
			// Invalid sorting value
			if ($sort !== 'asc' && $sort !== 'desc')
				throw new Exception('query_sort_unknown');

			// Remember
			$this->sql_order = $order;
			$this->sql_sort = $sort;
		}
	}