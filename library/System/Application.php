<?php

    namespace Tinycar\System;
    
    use Tinycar\App\Config;
    use Tinycar\Core\Exception;
    use Tinycar\App\Locale;
    use Tinycar\App\Manager;
    use Tinycar\App\Services;
    use Tinycar\Core\Http\Params;
    use Tinycar\Core\Xml\Data;
    use Tinycar\System\Application\Dialog;
    use Tinycar\System\Application\Manifest;
    use Tinycar\System\Application\Model;
    use Tinycar\System\Application\Model\Variable;
    use Tinycar\System\Application\View;
    use Tinycar\System\Application\View\Action;    
    use Tinycar\System\Application\Storage;
    
    
    class Application
    {
    	private $data = array();
    	private $dialogs;
    	private $id;
    	private $locale;
    	private $manifest;
    	private $model;
    	private $properties;
    	private $services;
    	private $storage;
    	private $system;
    	private $url_params;
    	private $views;
    	private $xdata;
        
        
		/**
         * Inititate class
         * @param object $system Tinycar\App\Manager instance
         * @param string $id target application id
         * @param array [$data] additional data
		 */
		public function __construct(Manager $system, $id, array $data = array())
        {
        	// Remember
        	$this->system = $system;
        	$this->id = $id;
        	$this->data = $data;
        }
        
        
        /**
         * Call specified service only in local scope
         * @param string $path target service path
         * @param array [$params] service parameters
         * @return mixed service response
         * @see Tinycar\App\Manager::callService()
         */
        public function callCustomService($path, array $params = array())
        {
        	// Get application services instance
        	$services = $this->getServices();
        	
        	// No such service
        	if (!$services->hasService($path))
        		throw new Exception('service_name_invalid');
        		
        	// Prefer application-specific service over system version
        	$result = $services->callService($path, $params);
        		
        	// Get application manifest
        	$manifest = $this->getManifest();
        		
        	// Add service name to parameters
        	$params = array_merge(array('service' => $path), $params);
        		
        	// Trigger any webhooks we might have
        	foreach ($manifest->getWebhooksByAction($path) as $item)
        		$item->callUrl($params);
        
        	return $result;
        }
        
        
        /**
         * Call specified service in local or system scope
         * @param string $path target service path
         * @param array [$params] service parameters
         * @return mixed service response
         * @see Tinycar\App\Manager::callService() 
         */
        public function callService($path, array $params = array())
        {
			// Get application services instance
			$services = $this->getServices();
			
			// Prefer application-specific service over system version
			$result = ($services->hasService($path) ?
				$services->callService($path, $params) :
				$this->system->callService($path, $params)
			);
			
			// Get application manifest
			$manifest = $this->getManifest();
			
			// Add service name to parameters
			$params = array_merge(array('service' => $path), $params);
				 
			// Trigger any webhooks we might have
			foreach ($manifest->getWebhooksByAction($path) as $item)
				$item->callUrl($params);
				
			return $result;
        }
        
        
        /**
         * Load all available instances from system
         * @param object Manager instance
         * @return array list of Tinycar\System\Application instances
         */
        public static function loadAll(Manager $system)
        {
        	// Get application folders
            $list = glob(
                Config::getPath('APPS_FOLDER', '/*'), 
                GLOB_ONLYDIR
            );
             
            $result = array();

            // Create application instances
            foreach ($list as $path)
            {
            	try
            	{
	            	// Try to load target application
	                $instance = self::loadById(
	                	$system, basename($path)
	                );
	                
	                // Add to list
	                if (is_object($instance))
	                	$result[] = $instance;
            	}
            	catch (Exception $e)
            	{
            	}
            }
             
            return $result;
		}
		
		
		
		/**
		 * Load application by data from storage
		 * @param object $system Manager instance
		 * @param array $storage target storage data
		 * @return object|null Tinycar\System\Application instance or null on failure
		 * @throws Tinycar\Core\Exception
		 */
		public static function loadFromStorage(Manager $system, array $data)
		{
			// Create new instance
			$result = new self($system, $data['id'], array(
				'enabled' => $data['enabled'],
				'devmode' => $data['devmode'],
			));
			
			// Load manifest from string
			$result->loadManifestString($data['manifest']);
			
			return $result;
		}
         
         
		/**
         * Load application by id
		 * @param object $system Manager instance
         * @param string $id target application id
         * @return object|null Tinycar\System\Application instance or null on failure
         * @throws Tinycar\Core\Exception
         */
		public static function loadById(Manager $system, $id)
        {
            // Invalid syntax
            if (!preg_match("'^([a-z]{1,})\.([a-z]{1,})$'m", $id, $m))
            	throw new Exception('app_id_invalid');
            
            // Create new instance
            $result = new self($system, $id);
            
            // Load manifest from local file
            $result->loadManifestFile();
           	
            return $result;
		}
		
		
		/**
		 * Get action instance by type
		 * @param object $view Tinycar\System\Application\View instance
		 * @param string $type target action type
		 * @return object|null Tinycar\System\Application\View\Action instance
		 *                     or null on failure
		 */
		public function getActionByType(View $view, $type)
		{
			foreach ($this->getActions($view) as $item)
			{
				if ($item->getType() === $type)
					return $item;
			}
				
			return null;
		}
		
		
		/**
		 * Get actions data for application and specified view
		 * @param object $view Tinycar\System\Application\View instance
		 * @return array list of Tinycar\System\Application\View\Action instances
		 */
		public function getActions(View $view)
		{
			$result = array();
			
			// Add view actions
			foreach ($view->getActions() as $item)
				$result[] = $item;
			
			// Default view actions
			if (!$this->isHomeApplication())
			{
				$result[] = new Action(array(
					'target' => 'system',
					'type'   => 'back',
					'label'  => $this->getLocaleText('action_back'),
					'link'   => array(
						'app'  => ($view->isDefault() ? Config::get('UI_APP_HOME') : $this->getId()),
						'view' => 'default',
					),
				));
			}
			
			// Home application
			if (!$this->isHomeApplication())
			{
				$result[] = new Action(array(
					'target' => 'system',
					'type'   => 'home',
					'label'  => $this->getLocaleText('action_home'),
					'link'   => array(
						'app'  => Config::get('UI_APP_HOME'), 
						'view' => 'default',
					),
				));
			}
			
			// We have an app for applications
			$app = Config::get('UI_APP_APPS');
			
			if (is_string($app))
			{
				$result[] = new Action(array(
					'target' => 'system',
					'type'   => 'apps',
					'label'  => $this->getLocaleText('action_apps'),
					'link'   => array(
						'app'  => $app, 
						'view' => 'default',
					),
				));
			}
			
			// Logout link
			if ($this->system->hasAuthentication() && $this->system->hasAuthenticated())
			{
				$result[] = new Action(array(
					'target'  => 'session',
					'type'    => 'user',
					'label'   => $this->getLocaleText('action_logout'),
					'service' => 'session.logout',
					'link'    => array(
						'app' => '$url.app',
					),
				));
			}
			
			return $result;
		}
		
		
		/**
		 * Get specified application property value
		 * @param string $name target property name
		 * @return mixed|null property value or null on failure
		 */
		public function getAppProperty($name)
		{
			// Resolve properties once
			if (!is_array($this->properties))
			{
				// Get application manifest instance
				$manifest = $this->getManifest();
				
				$this->properties = array(
					'id'       => $this->getId(),
					'name'     => $manifest->getName(),
					'provider' => $manifest->getProvider(),
				);
			}
			
			// Invalid property
			if (!array_key_exists($name, $this->properties))
				return null;
			
			return $this->properties[$name];
		}
		
		
		/**
		 * Get specified data property value
		 * @param string $name target property name
		 * @return mixed|null property value or null on failure
		 */
		public function getData($name)
		{
			return (array_key_exists($name, $this->data) ?
				$this->data[$name] : null
			);
		}
		
		
		/**
		 * Get specified date property value
		 * @param string $name target property name
		 * @return mixed|null property value or null on failure
		 */
		public function getDateProperty($name)
		{
			switch ($name)
			{
				case 'time':
					return time();
			}
				
			return null;
		}
		
		
    	/**
		 * Get applicatoin dialog by name
		 * @param string $name target dialog name
		 * @return object|null Tinycar\System\Application\Dialog 
		 *                     instance or null on failure
		 */
		public function getDialogByName($name)
		{
			// Get dialogs
			$dialogs = $this->getDialogs();
			
			// Invalid dialog
			if (!array_key_exists($name, $dialogs))
				return null;
			
			return $dialogs[$name];
		}
		
		
		/**
		 * Get list of dialogs for this application
		 * @return array map of Tinycar\System\Application\Dialog instances
		 */
		public function getDialogs()
		{
			// Already resolved
			if (is_array($this->dialogs))
				return $this->dialogs;
					
			// Defaults
			$result = array();
		
			// Create instances
			foreach ($this->xdata->getNodes('dialog') as $xdata)
			{
				$instance = new Dialog($this->system, $this, $xdata);
				$result[$instance->getName()] = $instance;
			}
					
			// Remember
			$this->dialogs = $result;
			return $this->dialogs;
		}
		
		
        /**
         * Get application id
         * @return string|null application id or null on failure
         */
        public function getId()
        {
        	return $this->id;
        }
        
        
        /**
         * Get new query instance to search for keys
         * @return object Tinycar\System\Application\Storage\KeyQuery instance
         */
        public function getKeyQuery()
        {
        	return $this->getKeyStorage()->getQuery();
        }
        
        
        /**
         * Get storage instance to handle key values
         * @return object Tinycar\System\Application\Storage\KeyStorage instance
         */
        public function getKeyStorage()
        {
        	return $this->getStorage()->getKeyStorage();
        }
        
        
        /**
         * Get current locale instance
         * @return object Tinycar\App\Locale instnace
         * @throws Tinycar\Core\Exception
         */
        public function getLocale()
        {
        	// Already resolved
        	if (!is_null($this->locale))
        		return $this->locale;
        			
        	// Get locale name from session
        	$session = $this->system->getSession();
        	$name = $session->getLocale();
        	
        	// Load instance from manifest
        	$instance = Locale::loadFromManifest(
        		$this->xdata, $name
        	);
        	
        	// Remember
        	$this->locale = $instance;
        	return $this->locale;
        }
         
         
		/**
         * Get specified string where variables have been localized
         * @param string|null $source target source string
         * @return string| altered source string or null on failure
		 */
        public function getLocaleString($source)
        {
        	// Not a valid string
        	if (!is_string($source))
        		return null;
        	
        	// Not localized
        	if (strpos($source, '$locale.') !== 0)
        		return $source;
        	
        	// Find translation
        	$text = $this->getLocaleText(
        		substr($source, 8)
        	);
        	
        	// Unable to find translation
        	if (is_null($text))
        		return $source;
        	
        	return $text;
		}
		
		
		/**
		 * Get specified format rule for current locale
		 * @param string $name target locale format rule name
		 * @param string|null locale format rule value or null on failure
		 */
		public function getLocaleFormat($name)
		{
			// Get from application's locale
			$locale = $this->getLocale();
			$value = $locale->getFormat($name);
				
			// Revert to system locale
			if (is_null($value))
			{
				$system = $this->system->getLocale();
				$value = $system->getFormat($name);
			}
				
			// Revert to name
			if (is_null($value))
				$value = $name;
					
			return $value;
		}
		
		
		/**
         * Get specified property value for current locale
         * @param string $name target locale property name
         * @return string locale property value or requested name on failure
		 * @see Tinycar\App\Manager::getLocaleText()
		 */
		public function getLocaleText($name)
		{
			// Get from application's locale
			$locale = $this->getLocale();
			$value = $locale->getText($name);
			 
			// Revert to system locale
			if (is_null($value))
				$value = $this->system->getLocaleText($name);
			 
			// Revert to name
			if (is_null($value))
				$value = $name;
			
			return $value;
		}
		
		
		/**
		 * Get application's manifest instance
		 * @return object Tinycar\System\Application\Manifest instnace
		 */
		public function getManifest()
		{
			// Already resolved
			if (!is_null($this->manifest))
				return $this->manifest;
					
			// Remember
			$this->manifest = new Manifest($this, $this->xdata);
			return $this->manifest;
		}
		
		
		/**
		 * Get application's storage model instance
		 * @return object Tinycar\System\Application\Model instnace
		 */
		public function getModel()
		{
			// Already resolved
			if (!is_null($this->model))
				return $this->model;
			
			// Remember
			$this->model = new Model($this, $this->xdata);
			return $this->model;
		}
		
		
		/**
		 * Get new query instance to search for rows
		 * @return object Tinycar\System\Application\Storage\RowQuery instance
		 */
		public function getRowQuery()
		{
			return $this->getRowStorage()->getQuery();
		}
		
		
		/**
		 * Get storage instance to handle rows
		 * @return object Tinycar\System\Application\Storage\RowStorage instance
		 */
		public function getRowStorage()
		{
			return $this->getStorage()->getRowStorage();
		}
		
		
		/**
		 * Get application services instance
		 * @param string $name target service name
		 * @return object Tinycar\App\Services instance
		 */
		public function getServices()
		{
			// Already resolved
			if (!is_null($this->services))
				return $this->services;
		
			// Create new instance
			$api = new Services($this->system);
		
			// System path to services file
			$file = Config::getPath('APPS_FOLDER', 
				'/'.$this->getId().'/services.php'
			);

			// Configure services
			if (file_exists($file))
			{
				// Environment variables
				$system = $this->system;
				$app = $this;
				$session = $this->system->getSession();
					
				// Get file
				include($file);
			}
	
			// Remember
			$this->services = $api;
			return $this->services;
		}
		
		
		/**
		 * Get application's current status label
		 * @return string status label
		 */
		public function getStatusLabel()
		{
			return $this->getLocaleText(
				'app_status_'.$this->getStatusType()
			);
		}
		
		
		/**
		 * Get current application system status name
		 * @return string status name
		 */
		public function getStatusType()
		{
			// Not currently enabled
			if ($this->isEnabled() === false)
				return 'disabled';
			
			// Currently in development mode
			if ($this->isInDevmode() === true)
				return 'devmode';
		
			// Everything is okay
			return 'installed';
		}
		
		
		/**
		 * Get application storage instance
		 * @return object Tinycar\Application\Storage instance
		 */
		public function getStorage()
		{
			// Already resolved
			if (!is_null($this->storage))
				return $this->storage;
			
			// Remember
			$this->storage = new Storage($this);
			return $this->storage;
		}
		
		
		/**
		 * Get specified system property value
		 * @param string $name target property name
		 * @return mixed|null property value or null on failure
		 */
		public function getSystemProperty($name)
		{
			return Config::get('SYSTEM_'.strtoupper($name));
		}
		
		
		/**
		 * Study specified string and see if we have reference
		 * to an internal variable and return the referenced item
		 * @param string $source target source string to study
		 * @return mixed referenced value source string on failure
		 */
		public function getStringValue($source)
		{
			// Try to load variable instnace
			$variable = Variable::loadByString($source);
			
			// No variable found, revert to source
			if (is_null($variable))
				return $source;
			
			switch ($variable->getType())
			{
				// Locale text
				case '$locale':
					return $variable->getAsValue(
						$this->getLocaleText($variable->getProperty())
					);
					
				// Locale format
				case '$format':
					return $variable->getAsValue(
						$this->getLocaleFormat($variable->getProperty())
					);
					
				// Model property instance
				case '$model':
					$model = $this->getModel();
					return $variable->getAsValue(
						$model->getPropertyByName($variable->getProperty())
					);
					
				// URL property
				case '$url':
					$url = $this->getUrlParams();
					return $variable->getAsValue(
						$url->get($variable->getProperty())
					);
					
				// Application property
				case '$app':
					return $variable->getAsValue(
						$this->getAppProperty($variable->getProperty())
					);
					
				// Date property
				case '$date':
					return $variable->getAsValue(
						$this->getDateProperty($variable->getProperty())
					);
					
				// System property
				case '$system':
					return $variable->getAsValue(
						$this->getSystemProperty($variable->getProperty())
					);
			}
			
			// Revert back to source
			return $source;
		}
		
		
		/**
		 * Get URL params instance
		 * @return object Tinycar\Core\Http\Params instance 
		 */
		public function getUrlParams()
		{
			// Already resolved
			if (is_object($this->url_params))
				return $this->url_params;
			
			// Remember empty instance
			$this->url_params = new Params(array());
			return $this->url_params;
		}
		
		
		/**
		 * Get application view by name
		 * @param string $name target view name
		 * @return object Tinycar\System\Application\View instance or null on failure
		 * @throws Tinycar\Core\Exception
		 */
		public function getViewByName($name)
		{
			// Get views
			$views = $this->getViews();
			
			// Invalid view name
			if (!array_key_exists($name, $views))
			{
				throw new Exception('app_view_invalid', array(
					'name' => $name,
				));
			}
			
			return $views[$name];
		}
		
		
		/**
		 * Get list of views for this application
		 * @return array map of Tinycar\System\Application\View instances
		 */
		public function getViews()
		{
			// Already resolved
			if (is_array($this->views))
				return $this->views;
			
			// Defaults
			$result = array(
				'default' => array(
					'name' => 'default',
				),
			);

			// Create instances
			foreach ($this->xdata->getNodes('view') as $xdata)
			{
				$instance = new View($this->system, $this, $xdata);
				$result[$instance->getName()] = $instance;
			}
			
			// Remember
			$this->views = $result;
			return $this->views;
		}
		
		
		/**
		 * Check if specified custom service exists
		 * @param string $path target service path
		 * @return bool custom service exists
		 */
		public function hasCustomService($path)
		{
			$services = $this->getServices();
			return $services->hasService($path);
		}
		
		
		/**
		 * Check if this application is enabled
		 * @return bool is enabled
		 */
		public function isEnabled()
		{
			// System application's can not be disabled
			return (
				$this->isSystemApplication() === true ||
				$this->getData('enabled') === '1'
			);
		}
		
		
		/**
		 * Check if this is the system home applicatoin
		 * @return bool is home application
		 */
		public function isHomeApplication()
		{
			return ($this->getId() === Config::get('UI_APP_HOME'));
		}
		
		
		/**
		 * Check if this is a system application
		 * @return bool is a system application
		 */
		public function isSystemApplication()
		{
			return in_array($this->getId(), array(
				Config::get('UI_APP_APPS'),
				Config::get('UI_APP_HOME'),
				Config::get('UI_APP_LOGIN'),
			));
		}
		
		
		/**
		 * Check if this application is in development mode
		 * @return bool is in development mode
		 */
		public function isInDevmode()
		{
			return ($this->getData('devmode') === '1');
		}
		
		
		/**
		 * Load manifest for this application from source file
		 * @return bool operation outcome
		 * @throws Tinycar\Core\Exception
		 */
		public function loadManifestFile()
		{
			// System path to manifes file
			$file = Config::getPath('APPS_FOLDER', 
				'/'.$this->getId().'/manifest.xml'
			);

			// Manifest file is missing
			if (!file_exists($file))
				throw new Exception('app_manifest_missing');
		
			// Create new XML document instance
			$xml = new \DOMDocument();
			$xml->preserveWhiteSpace = false;
		
			// Unable to read/parse XML
			if ($xml->load($file) === false)
			{
				throw new Exception('app_manifest_invalid', array(
					'id' => $this->getId(),
				));
			}
			
			// Update data
			$this->xdata = new Data($xml);
			
			// Reset internal properties
			$this->manifest = null;
			$this->views = null;
		
			return true;
		}
		
		
		/**
		 * Load manifest for this application from specified string
		 * @param string $source source string to study
		 * @return bool operation outcome
		 * @throws Tinycar\Core\Exception
		 */
		public function loadManifestString($source)
		{
			// Create new XML document instance
			$xml = new \DOMDocument();
			$xml->preserveWhiteSpace = false;
		
			// Unable to read/parse XML
			if ($xml->loadXml($source) === false)
			{
				throw new Exception('app_manifest_invalid', array(
					'id' => $this->getId(),
				));
			}
					
			// Update data
			$this->xdata = new Data($xml);
						
			// Reset internal properties
			$this->manifest = null;
			$this->views = null;
	
			return true;
		}
		
		
		/**
		 * Set application development mode
		 * @param bool $status new status
		 */
		public function setDevmode($status)
		{
			$this->data['devmode'] = ($status === true ? '1' : '0');
		}
		
		
		/**
		 * Set application state
		 * @param bool $status new status
		 */
		public function setEnabled($status)
		{
			$this->data['enabled'] = ($status === true ? '1' : '0');
		}
		
		
		/**
		 * Set URL parameters
		 * @param array $params new parameters
		 */
		public function setUrlParams(array $params)
		{
			$this->url_params = new Params($params);
		}
    }
    