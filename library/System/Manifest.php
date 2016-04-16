<?php

	namespace Tinycar\System;

	use Tinycar\App\Locale;
	use Tinycar\App\Manager;
	use Tinycar\Core\Xml\Data;
	use Tinycar\System\Application;
	use Tinycar\System\Application\SideBar;

	class Manifest
	{
	    private $locale;
		private $system;
		private $xdata;


		/**
		 * Initiate class
		 * @param object $system Tinycar\App\Manager instance
		 * @param object $xdata Tinycar\Core\Xml\Data instance
		 */
		public function __construct(Manager $system, Data $xdata)
		{
			$this->system = $system;
			$this->xdata = $xdata;
		}


		/**
		 * Load new manifest instance from specified file path
		 * @param string $path system path to target file
		 * @return Tinycar\System\Application\Xml\Manifest instance
		 * @throws Tinycar\Core\Exception
		 */
		public static function loadFromFile(Manager $system, $path)
		{
			// Try to load XML from given path
			try
			{
				$xml = Data::loadFromFile($path);
			}
			// Manifest is invalid
			catch (Exception $e)
			{
				throw new Exception('system_manifest_invalid');
			}

			// Get new instance
			return new self($system, $xml);
		}


		/**
		 * Get default application color
		 * @return string|null color or null on failure
		 */
		public function getAppColor()
		{
			return $this->xdata->getString('app/color');
		}


		/**
		 * Get current locale instance
		 * @return object Tinycar\App\Locale instance
		 * @throws Tinycar\Core\Exception
		 */
		public function getLocale()
		{
		    // Already resolved
		    if (!is_null($this->locale))
		        return $this->locale;

	        // Try to load target locale
		    $instance = Locale::loadFromManifest(
		        $this->xdata, $this->system->getLocaleName()
		    );

		    // Remember
		    $this->locale = $instance;
		    return $this->locale;
		}


		/**
		 * Get sidebar instance for specified applicatoin
		 * @param object $app Tinycar\System\Application instance
		 * @return object Tinycar\System\Application\SideBar
		 */
		public function getSideBar(Application $app)
		{
			// Get sidebar node
			$node = $this->xdata->getNode('bar');

			// Use a dummy node when none exists
			if (is_null($node))
				$node = $this->xdata->getAsNode(array());

			// Create new instance
			return new SideBar($this->system, $app, $node);
		}
	}