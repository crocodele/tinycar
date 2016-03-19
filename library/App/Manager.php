<?php

    namespace Tinycar\App;

    use Tinycar\Core\Exception;
    use Tinycar\App\Locale;
    use Tinycar\App\Session;
    use Tinycar\System\Storage;
    
    class Manager
    {
    	private $locale;
        private $parameters = array();
        private $services = array();
        private $session;
        private $storage;
        
        
         /**
          * Initiate class
          */
         public function __construct()
         {
         }
         
         
         /**
          * Add new parameters
          * @param array $parameters new parameters
          */
         public function addParameters(array $parameters)
         {
             $this->parameters = array_merge(
                 $this->parameters, $parameters
             );
         }
         
         
         /**
          * Add raw request data
          * @param string $data raw data to parse
          */
         public function addRawData($data)
         {
         	// We have raw data
         	if (strlen($data) > 0)
         	{
         		// Try to decode from JSON
         		$data = json_decode($data, true);
         
         		// Add data as parameters
         		if (is_array($data))
         			$this->addParameters($data);
         	}
         }
         
         
         /**
          * Call specified service
          * @param string $path target service path
          * @param array [$params] service parameters
          * @param bool [$authenticate] verify authentication, false by default
          * @return mixed service response
          * @throws Tinycar\Core\Exception
          */
         public function callService($path, array $params = array(), $authenticate = false)
         {
         	// Invalid path syntax
         	if (!preg_match("'^([a-z\.]{1,})\.([a-z\.]{1,})$'m", $path, $m))
         		throw new Exception('service_syntax_invalid');
         		 
         	// Try to get target services
         	$services = $this->getServices($m[1]);
         	
         	// Verify access to the service with custom service
         	if ($authenticate && $services->hasService('access'))
         	{
         		// Access denied
       			if ($services->callService('access', $params) === false)
         			throw new Exception('service_access_denied'); 
         	}
         	
         	// No such service
         	if (!$services->hasService($m[2]))
         	{
         		throw new Exception('service_name_invalid', array(
         			'name' => $m[2],
         		));
         	}
         	
         	// Get service result
         	return $services->callService($m[2], $params);
         }
         
         
         /**
          * Get application instance by id
          * @param string $id target application id
          * @return object Tinycar\System\Application
          * @throws Tinycar\Core\Exception
          */
         public function getApplicationById($id)
         {
         	// Get system stroage
         	$storage = $this->getStorage();
         	
         	// Try to get application from storage
         	$result = $storage->getApplicationById($id);
         	
         	// This application is not in use
         	if (!$result->isEnabled())
         	{
         		throw new Exception('app_disabled', array(
         			'id' => $id,
         		));
         	}
         	
         	// This application is in development mode
         	if ($result->isInDevmode())
         	{
         		$result->loadManifestFile();
         		$storage->updateApplication($result);
         	}
         	
         	return $result;
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
         	
         	// Get current locale name from session
         	$session = $this->getSession();
         	$name = $session->getLocale();
         	
         	// Try to load target locale
         	$instance = Locale::loadFromSystem($name);
         	
         	// Failed, revert to default
         	if (is_null($instance))
         		$instance = Locale::loadFromSystem('default');
         	
         	// Still failed, we have no locales
         	if (is_null($instance))
         		throw new Exception('locale_files_missing');
         	
			// Remember
			$this->locale = $instance;
			return $this->locale;
         }
         
         
        /**
         * Get specified property value for current locale
         * @param string $name target locale property name
         * @return string locale property value or requested name on failure
         */
		public function getLocaleText($name)
		{
        	// Get from application's locale
         	$locale = $this->getLocale();
         	$value = $locale->getText($name);
         		
         	// Revert to name
         	if (is_null($value))
         		$value = $name;
         			
         	return $value;
		}
         
         
         /**
          * Get specified parameter value
          * @param string $name target parameter name
          * @return mixed|null parameter value or null on failure
          */
         public function getParameter($name)
         {
             return (array_key_exists($name, $this->parameters) ?
                 $this->parameters[$name] : null
             );
         }
         
         
         /**
          * Get specified services instance
          * @param string $name target service name
          * @return object Tinycar\App\Services instance
          */
         private function getServices($name)
         {
         	// Already resolved
         	if (array_key_exists($name, $this->services))
         		return $this->services[$name];
         
         	// Create new instance
         	$api = new Services($this);
         
         	// System path to services file
         	$file = Config::getPath('SERVICES_FOLDER', '/'.$name.'.php');
         
         	// Configure services
         	if (file_exists($file))
         	{
         		// Environment variables
         		$system = $this;
         		$session = $this->getSession();
         		$user = $this->getUser();
         		 
         		// Get file
         		include($file);
         	}
         	
            // Remember
         	$this->services[$name] = $api;
         	return $this->services[$name];
         }
         
         
         /**
          * Get system storage instance
          * @return object Tinycar\System\Storage instance
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
          * Get all parameters
          * @return array parameters
          */
         public function getParameters()
         {
             return $this->parameters;
         }
         
         
         /**
          * Get session instance
          * @return object Tinycar\App\Session instance
          */
         public function getSession()
         {
         	// Already resolved
         	if (!is_null($this->session))
         		return $this->session;
         	
         	// Remember
         	$this->session = new Session();
        	return $this->session;
		}
		
		
		/**
		 * Get session user instance
		 * @return object Tinycar\App\User instance
		 */
		public function getUser()
		{
			return $this->getSession()->getUser();
		}
		
		
		/**
		 * Check if user has authenticated
		 * @return bool user has authenticated
		 */
		public function hasAuthenticated()
		{
			return ($this->getUser()->isEmpty() === false);
		}
		
         
        /**
		 * Check if authentication is required
		 * @return bool authentication is required 
         */
		public function hasAuthentication()
		{
			return is_string(Config::get('UI_APP_LOGIN'));
		}
         
         
		/**
         * Check if specified parameter has been defiend
         * @param string $name target parameter name
         * @return bool has parameter
         */
        public function hasParameter($name)
        {
        	return array_key_exists($name, $this->parameters);
		}
		
		
        /**
         *  Show output
         */
        public function show()
        {
		}
    }