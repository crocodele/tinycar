<?php

	namespace Tinycar\Core\Storage;

	use Tinycar\Core\Exception;
	
	
	class Query
	{
		private $connection;
		private $last_query;
		private $param_key = array();
		private $param_list = array();
		private $param_var = array();
		private $path;
		
		
		/**
		 * Initiate class
		 * @param string system path to file
		 */
		public function __construct($path)
		{
			$this->path = $path;
		}
		
		
		/**
		 * Bind a variable
		 * @param string $name target variable name
		 * @param mixed $value new variable value
		 * @throws Tinycar\Core\Exception
		 */
		public function bind($name, $value)
		{
			// Split type from string
			list($name, $type) = explode(':', $name, 2);
			
			switch ($type)
			{
				// Strings
				case 'string':
					$this->bindAsString($name, $value);
				break;
					
				// Booleans and integers
				case 'bool':
				case 'int':
					$this->bindAsInt($name, $value);
				break;
					
				// List of integers
				case 'intlist':
					$this->bindAsIntList($name, $value);
				break;
				
				// List of strings
				case 'stringlist':
					$this->bindAsStringList($name, $value);
				break;
			}
		}
		
		
		/**
		 * Bind variable as an integer
		 * @param string $name target variable name
		 * @param mixed $value new variable value
		 */
		private function bindAsInt($name, $value)
		{
			// Register parameter
			$this->param_var[$name] = array(
				'type'  => \PDO::PARAM_INT,
				'name'  => ':'.$name,
				'value' => (int) $value,
			);
		}
		
		
		/**
		 * Bind variable as an intlist
		 * @param string $name target variable name
		 * @param mixed $value new variable value
		 * @throws Tinycar\Core\Exception
		 */
		private function bindAsIntList($name, $value)
		{
			// Ensure we have only integers
			$value = array_map('intval', $value);
			$value = array_unique($value);
		
			// No values to insert
			if (count($value) === 0 || $value[0] === 0)
				throw new Exception('param_intlist_empty');
	
			// Register parameter
			$this->param_list[$name] = array(
				'type'  => 'integer',
				'name'  => ':'.$name,
				'value' => implode(',', $value),
			);
		}
		
		
		/**
		 * Bind variable as a string
		 * @param string $name target variable name
		 * @param mixed $value new variable value
		 */
		private function bindAsString($name, $value)
		{
			// Register parameter
			$this->param_var[$name] = array(
				'type'  => \PDO::PARAM_STR,
				'name'  => ':'.$name,
				'value' => $value,
			);
		
		}
		
		
		/**
		 * Bind variable as a stringlist
		 * @param string $name target variable name
		 * @param mixed $value new variable value
		 * @throws Tinycar\Core\Exception
		 */
		private function bindAsStringList($name, $value)
		{
			// Ensure we have only strings
			$value = array_map('strval', $value);
			$value = array_map('addslashes', $value);
			$value = array_unique($value);
				
			// No values to insert
			if (count($value) === 0 || $value[0] === '')
				throw new Exception('param_stringlist_empty');
					
			// Register parameter
			$this->param_list[$name] = array(
				'type'  => 'string',
				'name'  => ':'.$name,
				'value' => "'".implode("','", $value)."'",
			);
		}
		
		
		/**
		 * Connect to the database
		 * @returns object \PDO instance
		 * @throws Tinycar\Core\Exception 
		 */
		private function connect()
		{
			// Already resolved
			if (!is_null($this->connection))
				return $this->connection;
			
			// Create new instance
			try
			{
				$instance = new \PDO('sqlite:'.$this->path);
			}
			catch (\Exception $e)
			{
				throw new Exception(
					'db_connection_failed', 
					array('path' => $this->path)
				);
			}
			
			// Remember
			$this->connection = $instance;
			return $this->connection;
		}
		
		
		/**
		 * Get all specified rows
		 * @param string $sql target SQL
		 * @return array list of rows and named properties
		 */
		public function getAll($sql)
		{
			$query = $this->query($sql);
			return $query->fetchAll(\PDO::FETCH_ASSOC);
		}
		
		
		/**
		 * Get number of rows affected by last query
		 * @return int amount of rows
		 */
		public function getAffectedRows()
		{
			return (is_object($this->last_query) ? 
				$this->last_query->rowCount() : 0
			);
		}
		
		
		/**
		 * Get all specified rows grouped by first column
		 * @param string $sql target SQL
		 * @return array map of rows and named properties 
		 */
		public function getGrouped($sql)
		{
			$query = $this->query($sql);
			return $query->fetchAll(\PDO::FETCH_ASSOC|\PDO::FETCH_GROUP);
		}
		
		
		/**
		 * Get all specified rows in key-value pairs
		 * @param string $sql target SQL
		 * @return array map of key value pairs
		 */
		public function getKeyPairs($sql)
		{
			$query = $this->query($sql);
			return $query->fetchAll(\PDO::FETCH_KEY_PAIR);
		}
		
		
		/**
		 * Get list of values from specified columns first column
		 * @param string $sql target SQL
		 * @return array list of values
		 */
		public function getList($sql)
		{
			$query = $this->query($sql);
			return $query->fetchAll(\PDO::FETCH_COLUMN);
		}
		
		
		/**
		 * Get one specified row
		 * @param string $sql target SQL
		 * @return array|null row properties or null on failure
		 */
		public function getOne($sql)
		{
			$result = $this->getAll($sql);
			return (count($result) > 0 ? $result[0] : null);
		}
		
		
		/**
		 * Get a single value for query result
		 * @param string $sql target SQL
		 * @return mixed|null query result or null on failure
		 */
		public function getValue($sql)
		{
			$result = $this->getOne($sql);
			return (count($result) > 0 ? array_shift($result) : null);
		}
		
		
		/**
		 * Insert new row
		 * @param string $table table name
		 * @return bool operation outcome
		 */
		public function insert($table)
		{
			// Get parameter keys
			$keys = array_keys($this->param_var);
			
			// Create SQL
			$result = $this->query(sprintf('
				INSERT INTO %s (%s)
				VALUES (%s)', 
				$table, 
				implode(',', $keys),
				':'.implode(',:', $keys)
			));

			/// Try to insert
			return (bool) $result;
		}
		
		
		/**
		 * Set primary keys for upcoming update
		 * @param string $arg1 key 1
		 * @param string [$arg2] key #2
		 * ...
		 */
		public function keys()
		{
			$this->param_key = func_get_args();
		}
		
		
		/**
		 * Execute specified query
		 * @param string $sql target SQL to exceute
		 * @return object \PDOStatement instance
		 * @throws Tinycar\Core\Exception
		 */
		public function query($sql)
		{
			// Get connection
			$connection = $this->connect();
			
			// Add lists to SQL
			foreach ($this->param_list as $item)
				$sql = str_replace($item['name'], $item['value'], $sql);
			
			// Try to prepare query
			$query = $connection->prepare($sql);
			
			// Preparation failed
			if (!is_object($query))
			{
				throw new Exception(
					'query_prepare_failed', $connection->errorInfo()
				);
			}
			
			// Bind parameters
			foreach ($this->param_var as $item)
			{
				$query->bindParam(
					$item['name'], $item['value'],  $item['type']
				);
			}
			
			// Reset parameters
			$this->last_query = null;
			$this->param_key = array();
			$this->param_list = array();
			$this->param_var = array();

			// Execute query
			$result = $query->execute();
			
			// Execution failed
			if ($result === false)
			{
				throw new Exception(
					'query_exec_failed', $connection->errorInfo()
				);
			}
			
			// Remember
			$this->last_query = $query;
			return $this->last_query;
		}
		
		
		/**
		 * Update current row
		 * @param string $table table name
		 * @return bool operation outcome
		 */
		public function update($table)
		{
			$columns = array();
			
			// Default value
			if (count($this->param_key) === 0)
				$this->param_key = array('id');

			// Create column updates
			foreach ($this->param_var as $name => $item)
			{
				if (!in_array($name, $this->param_key))
					$columns[] = $name.'='.$item['name'];
			}
			
			$where = array();
			
			// Create where conditions
			foreach ($this->param_key as $name)
				$where[] = $name.'=:'.$name;
				
			// Try to update
			$result = $this->query('
				UPDATE '.$table.' 
				SET '.implode(', ', $columns).'
				WHERE '.implode(' AND ', $where)
			);
			
			return ($this->getAffectedRows() > 0);
		}
	}