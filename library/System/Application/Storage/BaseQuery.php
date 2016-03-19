<?php

	namespace Tinycar\System\Application\Storage;
	
	use Tinycar\System\Application\Model;
	use Tinycar\System\Application\Storage;
	use Tinycar\System\Application\Storage\BaseQuery;
	
	
	class BaseQuery
	{
		protected $query;
		protected $model;
		protected $sql_filters = array();
		protected $sql_limit = 0;
		protected $sql_order;
		protected $sql_removed;
		protected $sql_sort;
		protected $storage;
		
		
		/**
		 * Initiate class
		 * @param object $storage Tinycar\System\Application\Storage instance
		 * @param object $model Tinycar\System\Application\Model instance 
		 */
		public function __construct(Storage $storage, Model $model)
		{
			$this->storage = $storage;
			$this->model = $model;
			$this->query = $storage->getQuery();
		}
		
		
		/**
		 * Set result limit
		 * @param int $limit new limit, 0 for none
		 */
		public function limit($limit)
		{
			$this->sql_limit = intval($limit);
		}
		
		
		/**
		 * Exclude or limit to only to removed rows
		 * @param bool $state new state
		 */
		public function removed($state)
		{
			$this->sql_removed = $state;
		}
		
		
		/**
		 * Get SQL string to use to limit results
		 * @return string limit string
		 */
		protected function getSqlForLimit()
		{
			// No limit
			if ($this->sql_limit === 0)
				return '';
		
			// Wrap limiting
			return 'LIMIT '.$this->sql_limit;
		}
	}