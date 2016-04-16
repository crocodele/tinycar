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
			$system->hasAuthentication() === false ||
			$system->hasAuthenticated() === true
		);
	});


	/**
	 * List application intances supported by the system
	 * @param object $params Tinycar\Core\Http\Params instance
	 * @return array applications and their properties
	 *         - string      id         application id
	 *         - string      name       application name
	 *         - string      provider   application provider
	 *         - string      color      application color
	 *         - bool        is_system  this is a system application
	 *         - string|null icon       application icon data, if any
	 */
	$api->setService('applications', function(Params $params) use ($system)
	{
		// Get system storage
		$storage = $system->getStorage();

		// Get stored application instances
		$list = $storage->getApplications();

		$result = array();

		foreach ($list as $item)
		{
			// Get application manifest instance
			$manifest = $item->getManifest();

			$result[] = array(
				'id'        => $item->getId(),
				'name'      => $manifest->getName(),
				'provider'  => $manifest->getProvider(),
				'color'     => $item->getColor(),
				'is_system' => $item->isSystemApplication(),
				'icon'      => $manifest->getIconData(),
			);
		}

		return $result;
	});


	/**
	 * Refresh current system state
     * @param object $params Tinycar\Core\Http\Params instance
	 * @return bool operation outcome
	 */
	$api->setService('refresh', function(Params $params) use ($system)
	{
		// Get system storage
		$storage = $system->getStorage();

		// Get all available applications in system
		$list = Application::loadAll($system);

		$instances = array();

		// Update or insert applicatoins
		foreach ($list as $item)
		{
			$existing = null;

			// Try to get existing application
			try
			{
				// Get existing application
				$existing = $storage->getApplicationById($item->getId());
			}
			catch (Exception $e)
			{
				// Insert as new application
				$storage->insertApplication($item);
				$applications[] = $item->getId();
			}

			// Update application properties
			if (is_object($existing))
			{
				$existing->loadManifestFile();
				$storage->updateApplication($existing);
				$applications[] = $existing->getId();
			}
		}

		// Remove unused applications
		foreach ($storage->getApplications(true) as $item)
		{
			if (!in_array($item->getId(), $applications))
				$storage->deleteApplication($item);
		}

		return true;
	});


	/**
	 * Install system for the first time
	 * @param object $params Tinycar\Core\Http\Params instance
	 * @return bool operation outcome
	 * @throws Tinycar\Core\Exception
	 */
	$api->setService('install', function(Params $params) use ($system)
	{
		// PHP version is invalid (< 5.2.7)
		if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50207)
			throw new Exception('install_php_version');

		// PDO does not contain SQLITE
		if (!in_array('sqlite', \PDO::getAvailableDrivers()))
			throw new Exception('install_sqlite_support');

		// Unable to write to database folder
		if (!is_writable(Config::getPath('STORAGE_FOLDER', '/database')))
			throw new Exception('install_database_privileges');

		// Unable to write to logsfolder
		if (!is_writable(Config::getPath('STORAGE_FOLDER', '/logs')))
			throw new Exception('install_logs_privileges');

		// Get system storage
		$storage = $system->getStorage();

		// Setup system tables
		if (!$storage->setup())
			throw new Exception('install_storage_failed');

		// Get all available applications in system
		$list = Application::loadAll($system);

		// Insert applications
		foreach ($list as $item)
		{
			// Insert as enabled application
			$item->setEnabled(true);
			$storage->insertApplication($item);
		}

		return true;
	});
