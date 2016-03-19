<?php 

	namespace Tinycar\System\Application\Storage;

	use Tinycar\System\Application;
	use Tinycar\System\Application\Storage\BaseStorage;
	use Tinycar\System\Application\Storage\KeyQuery;
	
	class KeyStorage extends BaseStorage
	{
		
		/**
		 * Delete specified keys from database storage
		 * @param array $list list of target key names
		 * @return bool operation outcome
		 */
		public function delete(array $list)
		{
			// Get query to storage
			$db = $this->storage->getQuery();
				
			// Bind variables
			$db->bind('list:stringlist', $list);
			
			// Delete keys
			$db->query('
				DELETE 
				FROM data_keys
				WHERE name IN (:list)
			');
				
			return ($db->getAffectedRows() > 0);
		}
		
		
		/**
		 * Get query instance to search for model rows
		 * @return object Tinycar\System\Application\Storage\KeyQuery instance
		 */
		public function getQuery()
		{
			// Create new query instance
			$result = new KeyQuery(
				$this->storage, $this->model
			);
		
			// Defaults
			$result->removed(false);
		
			return $result;
		}
		
		
		/**
		 * Insert new row of model data to database storage
		 * @param array $data new model data
		 * @return int new row id 
		 */
		public function insert(array $data)
		{
			// Get model rows from data
			$rows = $this->model->getModelAsRows($data);
			
			// Get next row id
			$id = $this->getNextRowId();
			
			// Get query to storage
			$db = $this->storage->getQuery();
			
			// Insert row
			$db->bind('id:int', $id);
			$db->bind('created_time:int', time());
			$db->insert('data_rows');

			// Insert row properties
			foreach ($rows as $row)
			{
				// Bind variables
				$db->bind('row_id:int', $id);
				$db->bind('name:string', $row->getName());
				$db->bind('value:string', $row->getValue());
				$db->insert('data_row_properties');
			}
			
			return $id;
		}
		
		
		/**
		 * Remove existing keys of model from database storage
		 * @param array $list list of target key names
		 * @return bool operation outcome
		 */
		public function remove(array $list)
		{
			// Get query to storage
			$db = $this->storage->getQuery();
				
			// Bind variables
			$db->bind('list:stringlist', $list);
			$db->bind('time:int', time());
				
			// Update rows
			$db->query('
				UPDATE data_keys
				SET removed_time=:time
				WHERE name IN (:list) AND
					  removed_time IS NULL
			');
				
			return ($db->getAffectedRows() > 0);
		}
		
		
		/**
		 * Restore removed keys from database storage
		 * @param array $list list of target key names
		 * @return bool operation outcome
		 */
		public function restore(array $list)
		{
			// Get query to storage
			$db = $this->storage->getQuery();
		
			// Bind variables
			$db->bind('list:stringlist', $list);
		
			// Update rows
			$db->query('
				UPDATE data_keys
				SET removed_time=NULL
				WHERE name in (:list) AND
					  removed_time IS NOT NULL
			');
		
			return ($db->getAffectedRows() > 0);
		}
		
		
		/**
		 * Set a key into database storage
		 * @param string $name target key name
		 * @param string $value target key value
		 * @return bool operation outcome
		 */
		public function set($name, $value)
		{
			// Get query to storage
			$db = $this->storage->getQuery();
					
			// Bind variables
			$db->keys('name');
			$db->bind('name:string', $name);
			$db->bind('value:string', $value);
			$db->bind('modified_time:int', time());
		
			// Try to update first
			if (!$db->update('data_keys'))
			{
				// Bind variables
				$db->bind('name:string', $name);
				$db->bind('value:string', $value);
				$db->bind('created_time:int', time());
							
				if (!$db->insert('data_keys'))
					throw new Exception('key_update_failure');
			}
					
			return true;
		}
	}
	