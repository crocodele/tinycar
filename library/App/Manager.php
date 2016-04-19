<?php

    namespace Tinycar\App;

    use Tinycar\App\Config;
    use Tinycar\App\Locale;
    use Tinycar\App\Session;
    use Tinycar\App\Writer;
    use Tinycar\Core\Exception;
    use Tinycar\System\Manifest;
    use Tinycar\System\Storage;

    class Manager
    {
        private $last_writer;
    	private $locale;
    	private $manifest;
        private $parameters = array();
        private $services = array();
        private $session;
        private $storage;
        private $writers = array();


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
         	// Get system storage
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
          * Get last writer instance
          * @return object|null Tinycar\App\Writer instance or null on failure
          */
         public function getLastWriter()
         {
             // No last writer
             if (!is_string($this->last_writer))
                 return null;

             // Invalid type
             if (!array_key_exists($this->last_writer, $this->writers))
                 return null;

             // Get writer instance
             return $this->writers[$this->last_writer];
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
          * Get current local name
          * @return string locale name
          */
         public function getLocaleName()
         {
             return $this->getSession()->getLocale();
         }


        /**
         * Get specified property value for current locale
         * @param string $name target locale property name
		 * @param array [$vars] custom variables to add in key-value pairs
         * @return string locale property value or requested name on failure
         */
		public function getLocaleText($name, array $vars = array())
		{
        	// Get from system locale
         	$locale = $this->getLocale();
         	$value = $locale->getText($name, $vars);

         	// Check from system manifests' locale
         	if (is_null($value))
         	{
             	// Get from manifest file
             	$locale = $this->getManifest()->getLocale();
             	$value = $locale->getText($name, $vars);
         	}

         	// Revert to name
         	if (is_null($value))
         		$value = $name;

         	// Revert to name
         	return $value;
		}


		/**
		 * Get system manifest instance
		 * @return object Tinycar\System\Manifest instance
		 */
		public function getManifest()
		{
			// Already resolved
			if (!is_null($this->manifest))
				return $this->manifest;

			// Try to load from file
			$instance = Manifest::loadFromFile($this, Config::getPath(
				'SYSTEM_PATH', '/config/manifest.xml'
			));

			// Remember
			$this->manifest = $instance;
			return $this->manifest;
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
		 * Get specified writer instance
		 * @param string $name target writer type
		 * @return object Tinycar\App\Writer instance
		 */
		public function getWriter($type)
		{
		    // Already resolved
		    if (array_key_exists($type, $this->writers))
		        return $this->writers[$type];

		    // Try to load writer instance by type
		    $instance = Writer::loadByType($type);

		    // Remembers last writer type
		    $this->last_writer = $type;

		    // Remember
		    $this->writers[$type] = $instance;
		    return $this->writers[$type];
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