<?php 

	namespace Tinycar\System;
	
	use Tinycar\App\Config;
	use Tinycar\App\Manager;
	use Tinycar\Core\Exception;
	use Tinycar\Core\Storage\Query;
	
	class Storage
	{
		private $system;
		private $query;
		
		
		/**
		 * Initiate class
		 * @param object $system Tinycar\App\Manager instance
		 */
		public function __construct(Manager $system)
		{
			$this->system = $system;
		}
		
		
		/**
		 * Delete specified application
		 * @param object $app target Tinycar\System\Application instance
		 * @return bool operation outcome
		 */
		public function deleteApplication(Application $app)
		{
			// Get query to storage
			$db = $this->getQuery();
			
			// Bind variables
			$db->bind('id:string', $app->getId());
			
			// Delete from dateabase
			$db->query('
				DELETE 
				FROM apps 
				WHERE id=:id
			');
			
			// Failed to delete
			if ($db->getAffectedRows() === 0)
				return false;
			
			// Remove application storage
			$storage = $app->getStorage();
			$storage->delete();
		
			return true;
		}
		
		
		/**
		 * Get specified application from storage
		 * @param string $id target application id
		 * @return object Tinycar\System\Application instance 
		 * @throws Tinycar\Core\Exception
		 */
		public function getApplicationById($id)
		{
			// Get query to storage
			$db = $this->getQuery();
			
			// Bind variables
			$db->bind('id:string', $id);
				
			// Try to get data
			$data = $db->getOne('
				SELECT id, enabled, devmode, manifest
				FROM apps
				WHERE id=:id
			');

			// No application found
			if (is_null($data))
			{
				throw new Exception('app_id_invalid', array(
					'id' => $id,
				));
			}
				
			// Create new instance
			return Application::loadFromStorage(
				$this->system, $data
			);
		}
		
		
		/**
		 * Get applications from storage
		 * @param bool $all all applications, regardless of enabled state
		 * @return array list of Tinycar\System\Application instances
		 */
		public function getApplications($all = false)
		{
			// Get query to storage
			$db = $this->getQuery();
			
			// Get only enabled applications
			$sql = '
				SELECT id, enabled, devmode, manifest
				FROM apps
				WHERE enabled=1
				ORDER BY id
			';
			
			// Get all only applications
			if ($all === true)
			{
				$sql = '
					SELECT id, enabled, devmode, manifest
					FROM apps
					ORDER BY id
				';
			}
			
			// Try to get data
			$data = $db->getAll($sql);

			$result = array();
					
			// Create instances
			foreach ($data as $item)
			{
				$result[] = Application::loadFromStorage(
					$this->system, $item
				);
			}
				
			return $result;
		}
		
		
		/**
		 * Get query instance to application storage
		 * @return object Tinycar\Db\Query instance
		 */
		private function getQuery()
		{
			// Already resolved
			if (!is_null($this->query))
				return $this->query;
			
			// Create instance
			$instance = new Query(Config::getPath('STORAGE_FOLDER', 
				'/database/system.db'
			));
			
			// Remember
			$this->query = $instance;
			return $this->query;
		}
		
		
		/**
		 * Insert specified application to storage
		 * @param object $app target Tinycar\System\Application instance
		 * @return bool operation outcome
		 * @throws Tinycar\Core\Exception
		 */
		public function insertApplication(Application $app)
		{
			// Try to setup application storage
			$storage = $app->getStorage();
			$storage->setup();
			
			// Get query to storage
			$db = $this->getQuery();
			
			// Get manifest instance
			$manifest = $app->getManifest();
				
			// Bind variables
			$db->keys('id');
			$db->bind('id:string', $app->getId());
			$db->bind('enabled:bool', $app->isEnabled());
			$db->bind('devmode:bool', $app->isInDevmode());
			$db->bind('manifest:string', $manifest->getAsXml());

			// Try to insert
			$db->insert('apps');
				
			return true;
		}
		
		
		/**
		 * Check to see if system storage has been installed
		 * @return bool is installed 
		 */
		public function isInstalled()
		{
			// Check if database file exists
			return file_exists(Config::getPath('STORAGE_FOLDER',
				'/database/system.db'
			));
		}
		 
		
		/**
		 * Setup storage
		 * @return bool operation otucome
		 */
		public function setup()
		{
			// Get query to storage
			$db = $this->getQuery();
		
			// Create applications table
			$db->query('
				CREATE TABLE IF NOT EXISTS apps (
					id TEXT,
					enabled INTEGER DEFAULT 0,
					devmode INTEGER DEFAULT 0,
					manifest TEXT,
					PRIMARY KEY (id)
				)
			');
				
			return true;
		}
		
		
		/**
		 * Update specified application's properties
		 * @param object $app target Tinycar\System\Application instance
		 * @return bool operation outcome
		 * @throws Tinycar\Core\Exception
		 */
		public function updateApplication(Application $app)
		{
			// Get query to storage
			$db = $this->getQuery();
		
			// Get manifest instance
			$manifest = $app->getManifest();
		
			// Bind variables
			$db->keys('id');
			$db->bind('id:string', $app->getId());
			$db->bind('enabled:bool', $app->isEnabled());
			$db->bind('devmode:bool', $app->isInDevmode());
			$db->bind('manifest:string', $manifest->getAsXml());

			// Try to update
			$db->update('apps');
		
			return true;
		}
	}
