<?php 

	namespace Tinycar\System\Application\Storage;

	use Tinycar\System\Application;
	use Tinycar\System\Application\Storage\BaseStorage;
	use Tinycar\System\Application\Storage\RowQuery;
	
	class RowStorage extends BaseStorage
	{
		
		
		/**
		 * Delete specified rows from database storage
		 * @param array $list list of target row id's
		 * @return bool operation outcome
		 */
		public function delete(array $list)
		{
			// Get query to storage
			$db = $this->storage->getQuery();
				
			// Bind variables
			$db->bind('list:intlist', $list);
			
			// Delete properties first
			$db->query('
				DELETE
				FROM data_row_properties
				WHERE row_id IN (:list)
			');

			// Bind variables
			$db->bind('list:intlist', $list);
				
			// Delete rows
			$db->query('
				DELETE 
				FROM data_rows
				WHERE id IN (:list)
			');
				
			return ($db->getAffectedRows() > 0);
		}
		
		
		/**
		 * Get query instance to search for model rows
		 * @return object Tinycar\System\Application\Storage\RowQuery instance
		 */
		public function getQuery()
		{
			// Create new query instance
			$result = new RowQuery(
				$this->storage, $this->model
			);
				
			// Defaults
			$result->removed(false);
				
			return $result;
		}
		
		
		/**
		 * Get next storage row id
		 * @return int next row id
		 */
		private function getNextRowId()
		{
			// Get query to storage
			$db = $this->storage->getQuery();
					
			// Get last inserted id
			$id = $db->getValue('
				SELECT MAX(id)
				FROM data_rows
			');
			
			// Increment value
			return intval($id) + 1;
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
		 * Remove existing rows of model from database storage
		 * @param array $list list of target row id's
		 * @return bool operation outcome 
		 */
		public function remove(array $list)
		{
			// Get query to storage
			$db = $this->storage->getQuery();
			
			// Bind variables
			$db->bind('list:intlist', $list);
			$db->bind('time:int', time());
			
			// Update rows
			$db->query('
				UPDATE data_rows
				SET removed_time=:time
				WHERE id IN (:list) AND
					  removed_time IS NULL
			');
			
			return ($db->getAffectedRows() > 0);
		}
		
		
		/**
		 * Restore removed rows from database storage
		 * @param array $list list of target row id's
		 * @return bool operation outcome
		 */
		public function restore(array $list)
		{
			// Get query to storage
			$db = $this->storage->getQuery();
				
			// Bind variables
			$db->bind('list:intlist', $list);
				
			// Update rows
			$db->query('
				UPDATE data_rows
				SET removed_time=NULL
				WHERE id IN (:list) AND
					  removed_time IS NOT NULL
			');
				
			return ($db->getAffectedRows() > 0);
		}
		
		
		/**
		 * Update existing row om database storage
		 * @param int $id target row id
		 * @param array $data new model data
		 * @return bool operation outcome
		 */
		public function update($id, array $data)
		{
			// Find existing row
			$instance = $this->getQuery()->id($id);
			
			// Not found
			if (is_null($instance))
				throw new Exception('row_not_found', array('id' => $id));
			
			// Get query to storage
			$db = $this->storage->getQuery();
			
			// Old complex values must be removed first
			foreach ($data as $name => $value)
			{
				// Not an array
				if (!is_array($value))
					continue;
			
				// Bind variables
				$db->bind('row_id:int', $id);
				$db->bind('name:string', $name.'.%');
						
				// Remove properties
				$db->query('
					DELETE
					FROM data_row_properties
					WHERE row_id=:row_id AND
					      name LIKE :name
				');
			}
		
			// Update row
			$db->bind('id:int', $id);
			$db->bind('modified_time:int', time());
			$db->update('data_rows');
			
			// Get properties as rows
			$rows = $this->model->getDataAsRows($data);

			// Update (or insert) rows
			foreach ($rows as $row)
			{
				// Bind variables
				$db->keys('row_id', 'name');
				$db->bind('row_id:int', $id);
				$db->bind('name:string', $row->getName());
				$db->bind('value:string', $row->getValue());
				
				// Try to update
				if (!$db->update('data_row_properties'))
				{
					// Bind variables
					$db->bind('row_id:int', $id);
					$db->bind('name:string', $row->getName());
					$db->bind('value:string', $row->getValue());
					
					if (!$db->insert('data_row_properties'))
						throw new Exception('row_update_failure');
				}
			}
			
			return true;
		}
	}
	