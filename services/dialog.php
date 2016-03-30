<?php

	use Tinycar\App\Config;
	use Tinycar\Core\Exception;
	use Tinycar\Core\Http\Params;


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
     *               - string dialog  target dialog name
     *               - string component target component id
     *               - string action    target action type
     *               - array  data      current component data
	 * @return bool operation outcome
	 * @throws Tinycar\Core\Exception
	 */
	$api->setService('action', function(Params $params) use ($system)
	{
		// Get target application
		$instance = $system->getApplicationById($params->get('app'));

		// Set URL parameters to application request
		$instance->setUrlParams($params->getArray('url'));

		// Get target dialog
		$dialog = $instance->getDialogByName($params->get('dialog'));

		// Invalid dialog name
		if (is_null($dialog))
			throw new Exception('app_dialog_invalid');

		// Get target action instance
		$action = $instance->getActionByType(
			$dialog, $params->get('action')
		);

		// Invalid action
		if (is_null($action))
			throw new Exception('dialog_action_invalid');

		// Get URL parameters instance
		$url = $instance->getUrlParams();

		// Call action service
		return $instance->callService($action->getService(), array(
			'app'  => $instance->getId(),
			'row'  => $url->get('id'),
			'rows' => array($url->get('id')),
			'data' => $dialog->getAsModelData($params->getArray('data')),
		));
	});


	/**
	 * Call specified component action from an applications view
     * @param object $params Tinycar\Core\Http\Params instance
     *               - string app       target application id
     *               - string [view]    target view name
     *               - string [dialog]  target dialog name
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

		// Get target dialog
		$dialog = $instance->getDialogByName($params->get('dialog'));

		// Invalid dialog name
		if (is_null($dialog))
			throw new Exception('app_dialog_invalid');

		// Get target component instance
		$component = $dialog->getComponentById($params->get('component'));

		// Invalid component id
		if (is_null($component))
			throw new Exception('view_component_invalid');

		// Try to get action response
		return $component->callAction(
			$params->get('action'), $params->getArray('data')
		);
	});


	/**
	 * Get specified application dialog
	 * @param object $params Tinycar\Core\Http\Params instance
	 *               - string app    target application id
	 *               - string dialog target dialog name
	 *               - array  [url] source URL parameters
	 * @return array dialog properties
	 * @throws Tinycar\Core\Exception
	 */
	$api->setService('view', function(Params $params) use ($system)
	{
		// Get target application
		$instance = $system->getApplicationById($params->get('app'));

		// Set URL parameters to application request
		$instance->setUrlParams($params->getArray('url'));

		// Get target dialog
		$dialog = $instance->getDialogByName($params->get('dialog'));

		// Invalid dialog name
		if (is_null($dialog))
		{
			throw new Exception('app_dialog_invalid', array(
				'name' => $params->get('dialog'),
			));
		}

		// Get dialog properties
		$result = array(
			'name'       => $dialog->getName(),
			'heading'    => $dialog->getHeading(),
			'tabs'       => array(),
			'actions'    => array(),
			'components' => array(),
		);

		// Add tabs
		foreach ($dialog->getTabs() as $item)
		{
			$result['tabs'][] = array(
				'name'  => $item->getName(),
				'label' => $item->getLabel(),
			);
		}

		// Add actions
		foreach ($dialog->getActions() as $item)
			$result['actions'][] = $item->getAll();

		// Add components
		foreach ($dialog->getComponents() as $item)
			$result['components'][] = $item->callAction('model');

		return $result;
	});
