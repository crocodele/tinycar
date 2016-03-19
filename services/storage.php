<?php

	use Tinycar\App\Config;
	use Tinycar\Core\Http\Params;
	use Tinycar\System\Application;
	
	
	/**
	 * Verify that active user has access to these services
	 * @param object $params Tinycar\Core\Http\Params instance
	 * @return boolean has access
	 */
	$api->setService('access', function(Params $params) use ($system)
	{
		return (
			$params->get('app') === Config::get('UI_APP_LOGIN') ||
			$system->hasAuthentication() === false ||
			$system->hasAuthenticated() === true
		);
	});
	
	
	/**
	 * Permanently delete row from specified application's storage
	 * @param object $params Tinycar\Core\Http\Params instance
	 *               - string app   target application id
	 *               - array  data  data for model
	 *               - array  [rows] list of target row id's
	 *               - array  [keys] list of target key names
	 * @return bool operation outcome
	 */
	$api->setService('delete', function(Params $params) use ($system)
	{
		// Get target application
		$instance = Application::loadById($system, $params->get('app'));
	
		// Try to delete rows
		if ($params->has('rows'))
		{
			$storage = $instance->getRowStorage();
			$storage->delete($params->getArray('rows'));
		}
		
		// Try to delete keys
		if ($params->has('keys'))
		{
			$storage = $instance->getKeyStorage();
			$storage->delete($params->getArray('keys'));
		}
		
		return true;
	});
	
	
	/**
	 * Get keys from specified application's storage
	 * @param object $params Tinycar\Core\Http\Params instance
	 *               - string app       target application id
	 *               - array  [filter]  map of property values to must match
	 *               - string [order]   property name to sort by
	 *               - string [sort]    sorting direction (asc|desc)
	 *               - int    [limit]   maximum amount of results
	 *               - bool   [removed] return only removed keys
	 * @return array row data
	 */
	$api->setService('keys', function(Params $params) use ($system)
	{
		// Get target application
		$instance = Application::loadById($system, $params->get('app'));
	
		// Get query instance
		$query = $instance->getKeyQuery();
	
		// Set filters
		if ($params->has('filter'))
			$query->filter($params->getArray('filter'));
	
		// Set custom order
		if ($params->has('order') && $params->has('sort'))
			$query->order($params->get('order'), $params->get('sort'));
	
		// Get only removed
		if ($params->has('removed'))
			$query->removed($params->getBool('removed'));

		// Set custom limit
		if ($params->has('limit'))
			$query->limit($params->getInt('limit'));
	
		// Get query results as as a native array
		return $query->find()->getAllData();
	});
	
	
	/**
	 * Remove existing row from specified application's storage
	 * @param object $params Tinycar\Core\Http\Params instance
	 *               - string app   target application id
	 *               - array  [rows] list of target row id's
	 *               - array  [keys] list of target key names
	 * @return bool operation outcome
	 */
	$api->setService('remove', function(Params $params) use ($system)
	{
		// Get target application
		$instance = Application::loadById($system, $params->get('app'));
	
		// Try to remove rows
		if ($params->has('rows'))
		{
			$storage = $instance->getRowStorage();
			$storage->remove($params->getArray('rows'));
		}
		
		// Try to remove keys
		if ($params->has('keys'))
		{
			$storage = $instance->getKeyStorage();
			$storage->remove($params->getArray('keys'));
		}
		
		return true;
	});
	
	
	/**
	 * Restore removed row from specified application's storage
	 * @param object $params Tinycar\Core\Http\Params instance
	 *               - string app   target application id
	 *               - array  [rows] list of target row id's
	 *               - array  [keys] list of target key names
	 * @return int row id
	 */
	$api->setService('restore', function(Params $params) use ($system)
	{
		// Get target application
		$instance = Application::loadById($system, $params->get('app'));
	
		// Try to restore rows
		if ($params->has('rows'))
		{
			$storage = $instance->getRowStorage();
			$storage->restore($params->getArray('rows'));
		}
		
		// Try to restore keys
		if ($params->has('keys'))
		{
			$storage = $instance->getKeyStorage();
			$storage->restore($params->getArray('keys'));
		}
		
		return true;
	});
	
	
	/**
	 * Get a row from specified application's storage
	 * @param object $params Tinycar\Core\Http\Params instance
	 *               - string app       target application id
	 *               - int    row       target row id
	 * @return array|null row data or null on failure
	 */
	$api->setService('row', function(Params $params) use ($system)
	{
		// Get target application
		$instance = Application::loadById($system, $params->get('app'));
	
		// Get query instance
		$query = $instance->getRowQuery();
	
		// Find target row by id
		$result = $query->id($params->get('row'));
		
		// Failed to find target row
		if (!is_object($result))
			return null;
		
		// Get data as native array
		return $result->getAllData();
	});
	
	
	/**
	 * Get rows from specified application's storage
	 * @param object $params Tinycar\Core\Http\Params instance
	 *               - string app       target application id
	 *               - array  [filter]  map of property values to must match
	 *               - string [order]   property name to sort by
	 *               - string [sort]    sorting direction (asc|desc)
	 *               - int    [limit]   maximum amount of results
	 *               - bool   [removed] return only removed rows
	 * @return array rows data
	 */
	$api->setService('rows', function(Params $params) use ($system)
	{
		// Get target application
		$instance = Application::loadById($system, $params->get('app'));
	
		// Get query instance
		$query = $instance->getRowQuery();
	
		// Set filters
		if ($params->has('filter'))
			$query->filter($params->getArray('filter'));
	
		// Set custom order
		if ($params->has('order') && $params->has('sort'))
			$query->order($params->get('order'), $params->get('sort'));
	
		// Get only removed
		if ($params->has('removed'))
			$query->removed($params->getBool('removed'));
	
		// Set custom limit
		if ($params->has('limit'))
			$query->limit($params->getInt('limit'));
		
		// Get query results as as a native array
		return $query->find()->getAllData();
	});
	
	
	/**
	 * Set new key to specified application's storage
	 * @param object $params Tinycar\Core\Http\Params instance
	 *               - string app   target application id
	 *               - array  key   target key
	 *               - mixed  value target value
	 * @return bool operation outcome
	 */
	$api->setService('set', function(Params $params) use ($system)
	{
		// Get target application
		$instance = Application::loadById($system, $params->get('app'));
	
		// Get application storage instance
		$storage = $instance->getKeyStorage();
	
		// Try to set key
		return $storage->set(
			$params->get('key'), $params->get('value')
		);
	});
	
	
	/**
	 * Insert new row to specified application's storage
     * @param object $params Tinycar\Core\Http\Params instance
     *               - string app   target application id
     *               - array  data  data for model
     * @return int row id
	 */
	$api->setService('insert', function(Params $params) use ($system)
	{
		// Get target application
		$instance = Application::loadById($system, $params->get('app'));
		
		// Get application storage instance
		$storage = $instance->getRowStorage();
		
		// Try to add new item
		return $storage->insert(
			$params->getArray('data')
		);
	});
	

	/**
	 * Update existing row in specified application's storage
	 * @param object $params Tinycar\Core\Http\Params instance
	 *               - string app   target application id
	 *               - int    row   target row id
	 *               - array  data  data for model
	 * @return bool operation outcome
	 */
	$api->setService('update', function(Params $params) use ($system)
	{
		// Get target application
		$instance = Application::loadById($system, $params->get('app'));
	
		// Get application storage instance
		$storage = $instance->getRowStorage();
	
		// Try to add new item
		return $storage->update(
			$params->getInt('row'), $params->getArray('data')
		);
	});
	