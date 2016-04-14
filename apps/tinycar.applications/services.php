<?php

	use Tinycar\Core\Http\Params;


	/**
	 * Update applications list
	 * @return bool operation outcome
	 */
	$api->setService('refresh', function(Params $params) use ($system)
	{
		return $system->callService('system.refresh');
	});


	/**
	 * Get properties for all applications
	 * @return array applications and their properties from system.applications
	 */
	$api->setService('storage.rows', function(Params $params) use ($system)
	{
		// Get system storage instance
		$storage = $system->getStorage();

		// Get application instances from storage
		$list = $storage->getApplications(true);

		$result = array();

		foreach ($list as $item)
		{
			// Get application manifest instance
			$manifest = $item->getManifest();

			$result[] = array(
				'id'           => $item->getId(),
				'label'        => $manifest->getName(),
				'description'  => $manifest->getProvider(),
				'image_color'  => $item->getColor(),
				'image_data'   => $manifest->getIconData(),
				'status_type'  => $item->getStatusType(),
				'status_label' => $item->getStatusLabel(),
			);
		}

		return $result;
	});


	/**
	 * Get single application's properties
	 * @param object $params Tinycar\Core\Http\Params instance
	 *               - string app       target application id
	 *               - string row       target row id
	 * @return array|null properties or null on failure
	 *               - string id       application id
	 *               - string name     application name
	 *               - string provider application provider name
	 *               - string description application description
	 *               - string status_label current status as a label
	 *               - bool   enabled      is application enabled
	 *               - bool   devmode      is application in development mode
	 *               - bool   has_storage  has application a local database
	 *               - bool   is_system    is application for system use
	 */
	$api->setService('storage.row', function(Params $params) use ($system)
	{
		// Get system storage instance
		$storage = $system->getStorage();

		// Get specified appliation instance from storage
		$item = $storage->getApplicationById(
			$params->get('row')
		);

		// Get application manifest instance
		$manifest = $item->getManifest();

		// Get properties
		return array(
			'id'           => $item->getId(),
			'name'         => $manifest->getName(),
			'provider'     => $manifest->getProvider(),
			'description'  => $manifest->getDescription(),
			'status_label' => $item->getStatusLabel(),
			'enabled'      => $item->isEnabled(),
			'devmode'      => $item->isInDevmode(),
			'has_storage'  => $manifest->hasStorageModel(),
			'is_system'    => $item->isSystemApplication(),
		);
	});


	/**
	 * Update application properties
	 * @param object $params Tinycar\Core\Http\Params instance
	 *               - string app   target application id
	 *               - int    row   target row id
	 *               - array  data  data for model
	 * @return bool operation outcome
	 */
	$api->setService('storage.update', function(Params $params) use ($system)
	{
		// Get system storage instance
		$storage = $system->getStorage();

		// Get specified appliation instance from storage
		$instance = $storage->getApplicationById($params->get('row'));

		// Get data properties
		$data = $params->getParams('data');

		// Update application properties
		$instance->setEnabled($data->getBool('enabled'));
		$instance->setDevmode($data->getBool('devmode'));

		// Refresh manifest file contents
		$instance->loadManifestFile();

		// Update application
		return $storage->updateApplication($instance);
	});
