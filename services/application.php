<?php

	use Tinycar\App\Config;
	use Tinycar\Core\Exception;
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
	 * Execute specified action from an application view
     * @param object $params Tinycar\Core\Http\Params instance
     *               - string app       target application id
     *               - string vie  ]    target view name
     *               - string component target component id
     *               - string action    target action type
	 * @return bool operation outcome
	 * @throws Tinycar\Core\Exception 
	 */
	$api->setService('action', function(Params $params) use ($system)
	{
		// Get target application
		$instance = $system->getApplicationById($params->get('app'));
			
		// Get target view
		$view = $instance->getViewByName($params->get('view'));
				
		// Get target action instance
		$action = $instance->getActionByType(
			$view, $params->get('action')
		);
		
		// Invalid action
		if (is_null($action))
			throw new Exception('view_action_invalid');
		
		// Map component data to model
		$data = $view->getAsModelData($params->getArray('data'));

		// Call action service
		return $instance->callService($action->getService(), array(
			'data' => $data,
		));
	});
	
	
	/**
	 * Call specified component action from an applications view
     * @param object $params Tinycar\Core\Http\Params instance
     *               - string app       target application id
     *               - string view      target view name
     *               - string component target component id
     *               - string action    target action name
     *               - array  data      custom component data
	 * @return mixed component action response
	 * @throws Tinycar\Core\Exception
     */
	$api->setService('component', function(Params $params) use ($system)
	{
		// Get target application
		$instance = $system->getApplicationById($params->get('app'));
			
		// Get target view
		$view = $instance->getViewByName($params->get('view'));
				
		// Get target component instance
		$component = $view->getComponentById($params->get('component'));
		
		// Invalid component id
		if (is_null($component))
			throw new Exception('view_component_invalid');
	
		// Try to get action response
		return $component->callAction(
			$params->get('action'), $params->getArray('data')
		);
	});
	
	
	/**
	 * Remove specified application view
	 * @param object $params Tinycar\Core\Http\Params instance
	 *               - string app   target application id
	 *               - string view  target view name
	 *               - array  url   source URL parameters
	 * @return bool operation outcome
	 * @throws Tinycar\Core\Exception
	 */
	$api->setService('remove', function(Params $params) use ($system)
	{
		// Get target application
		$instance = $system->getApplicationById($params->get('app'));
	
		// Get target view
		$view = $instance->getViewByName($params->get('view'));
	
		// Set URL parameters to application request
		$instance->setUrlParams($params->getArray('url'));
	
		// Get view's target record instance
		$record = $view->getDataRecord();
	
		// Let's try to remove the record
		return $instance->callService('storage.remove', array(
			'app'  => $params->get('app'),
			'rows' => array($record->get('id')),
		));
	});
	
	
	/**
	 * Call specified application's service
     * @param object $params Tinycar\Core\Http\Params instance
     *               - string app  target application id
     *               - string service target service name
	 * @return mixed application service result
	 * @throws Tinycar\Core\Exception  
	 */
	$api->setService('service', function(Params $params) use ($system)
	{
		// Get target application
		$instance = $system->getApplicationById($params->get('app'));
			
		// GEt service result
		return $instance->callService(
			$params->get('service'), $params->getAll()
		);
	});
	
	
	/**
	 * Install application and setup storage
	 * @param object $params Tinycar\Core\Http\Params instance
	 *               - string app  target application id
	 * @return bool operation outcome
	 */
	$api->setService('setup', function(Params $params) use ($system)
	{
		// Get target application
		$instance = $system->getApplicationById($params->get('app'));
	
		// Get storage instance
		$storage = $instance->getStorage();
	
		// Try to setup storage
		return $storage->setup();
	});
	
	
	/**
	 * Save specified application view
     * @param object $params Tinycar\Core\Http\Params instance
     *               - string app   target application id
     *               - string view  target view name
     *               - array  url   source URL parameters
     *               - array  data  target data
	 * @return bool operation outcome
	 * @throws Tinycar\Core\Exception
	 */
	$api->setService('save', function(Params $params) use ($system)
	{
		// Get target application
		$instance = $system->getApplicationById($params->get('app'));
	
		// Get target view
		$view = $instance->getViewByName($params->get('view'));
		
		// Set URL parameters to application request
		$instance->setUrlParams($params->getArray('url'));
		
		// Get view's target record instance
		$record = $view->getDataRecord();
		
		// Map component data to model
		$data = $view->getAsModelData($params->getArray('data'));

		// We have an existing id, let's update the record
		if ($record->has('id'))
		{
			return $instance->callService('storage.update', array(
				'app'  => $params->get('app'),
				'row'  => $record->get('id'),
				'data' => $data,
			));
		}

		// Let's try to insert the record
		return $instance->callService('storage.insert', array(
			'app'  => $params->get('app'),
			'data' => $data,
		));
	});
	

	/**
	 * Get specified application's view
     * @param object $params Tinycar\Core\Http\Params instance
     *               - string app   target application id
     *               - string view  target view name
     *               - array  [url] source URL parameters
	 * @return array properties
	 *         - array app  application properties
	 *         - array text application-specific translations
	 *         - array view view properties
	 * @throws Tinycar\Core\Exception
     */
	$api->setService('view', function(Params $params) use ($system)
	{
		// Get target application
		$instance = $system->getApplicationById($params->get('app'));
		
		// Set URL parameters to application request
		$instance->setUrlParams($params->getArray('url'));
			
		// Get target view
		$view = $instance->getViewByName($params->get('view'));
			
		$result = array();
		
		// Get application manifest instance
		$manifest = $instance->getManifest();
		
		// Get applicatoion locale instance
		$locale = $instance->getLocale();
			
		// Application properties 
		$result['app'] = array(
			'id'          => $instance->getId(),
			'layout_name' => $manifest->getLayoutName(),
			'name'        => $manifest->getName(),
			'provider'    => $manifest->getProvider(),
			'colors'      => $manifest->getColorMap(),
			'icon'        => $manifest->getIconData(),
		);
			
		// Get view properties
		$result['view'] = array(
			'name'        => $view->getName(),
			'layout_type' => $view->getLayoutType(),
			'heading'     => $view->getHeading(),
			'tabs'        => array(),
			'actions'     => array(),
			'components'  => array(),
			'text'        => array(),
		);
		
		// Translations
		$result['text'] = $locale->getTextsByPattern(
			"'^(datagrid|info|toast|calendar)_'m"
		);

		// Add tabs
		foreach ($view->getTabs() as $tab)
		{
			$result['view']['tabs'][] = array(
				'name'  => $tab->getName(),
				'label' => $tab->getLabel(),
			);
		}
		
		// Add actions
		foreach ($instance->getActions($view) as $item)
			$result['view']['actions'][] = $item->getAll();
			
		// Add components
		foreach ($view->getComponents() as $item)
			$result['view']['components'][] = $item->callAction('model');
             
		return $result;
	});