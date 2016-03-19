<?php 

	namespace Tinycar\System\Application;

	use Tinycar\App\Config;
	use Tinycar\Core\Storage\Query;
	use Tinycar\System\Application;
	use Tinycar\System\Application\Storage\KeyStorage;
	use Tinycar\System\Application\Storage\RowStorage;
	
	class Storage
	{
		private $app;
		private $key_storage;
		private $model;
		private $query;
		private $row_storage;
		
		
		/**
		 * Initiate class
		 * @param object $app Tinycar\System\Application instance
		 */
		public function __construct(Application $app)
		{
			$this->app = $app;
			$this->model = $app->getModel();
		}
		
		
		/**
		 * Delete application storage
		 * @return bool operation outcome
		 * @throws Tinycar\Core\Exception
		 */
		public function delete()
		{
			// System path to target file
			$file = $this->getStorageFile();
			
			// No such file
			if (!file_exists($file))
				return false;
			
			// Failed to remove file
			if (!unlink($file))
			{
				throw new Exception('storage_delete_failed', array(
					'id' => $this->app->getId(),
				));
			}
			
			return false;
		}
		
		
		/**
		 * Get storage instance to handle key values
		 * @return object Tinycar\System\Application\Storage\KeyStorage instance
		 */
		public function getKeyStorage()
		{
			// Already resolved
			if (is_object($this->key_storage))
				return $this->key_storage;
					
			// Initiate once
			$result = new KeyStorage($this->app);
					
			// Remember
			$this->key_storage = $result;
			return $this->key_storage;
		}
		
		
		/**
		 * Get query instance to application storage
		 * @return object Tinycar\Db\Query instance
		 */
		public function getQuery()
		{
			// Already resolved
			if (!is_null($this->query))
				return $this->query;
					
			// Create instance
			$instance = new Query($this->getStorageFile());
					
			// Remember
			$this->query = $instance;
			return $this->query;
		}
		
		
		/**
		 * Get storage instance to handle rows
		 * @return object Tinycar\System\Application\Storage\RowStorage instance
		 */
		public function getRowStorage()
		{
			// Already resolved
			if (is_object($this->row_storage))
				return $this->row_storage;
			
			// Initiate once
			$result = new RowStorage($this->app);
			
			// Remember
			$this->row_storage = $result;
			return $this->row_storage;
		}
		
		
		/**
		 * Get system path to this application's storage file
		 * @return string system path to file
		 */
		private function getStorageFile()
		{
			return Config::getPath('STORAGE_FOLDER',
				'/database/'.$this->app->getId().'.db'
			);
		}
		
		
		/**
		 * Check to see if application storage has been installed
		 * @return bool is installed
		 */
		public function isInstalled()
		{
			// Check if database file exists
			return file_exists($this->getStorageFile());
		}
		
		
		/**
		 * Setup storage
		 * @return bool operation otucome
		 */
		public function setup()
		{
			// Get query to storage
			$db = $this->getQuery();
		
			// Create tables
			$db->query('
				CREATE TABLE IF NOT EXISTS app_keys (
					name TEXT,
					value TEXT,
					PRIMARY KEY (name)
				)
			');
				
			$db->query('
				CREATE TABLE IF NOT EXISTS data_keys (
					created_time INTEGER,
					modified_time INTEGER,
					removed_time INTEGER,
					name TEXT,
					value TEXT,
					PRIMARY KEY (name)
				)
			');
		
			$db->query('
				CREATE TABLE IF NOT EXISTS data_rows (
					id INTEGER,
					created_time INTEGER,
					modified_time INTEGER,
					removed_time INTEGER,
					PRIMARY KEY (id)
				)
			');
				
			$db->query('
				CREATE TABLE IF NOT EXISTS data_row_properties (
					row_id INTEGER,
					name TEXT,
					value TEXT,
					PRIMARY KEY (row_id, name)
				)
			');
				
			return true;
		}
	}
	