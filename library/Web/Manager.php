<?php

namespace Tinycar\Web;

use Tinycar\App\Config;
use Tinycar\Core\Exception;

class Manager extends \Tinycar\App\Manager
{
    private $install_error;


    /**
     * Get installation error message
     * @return string|null error message or null on failure
     */
    public function getInstallError()
    {
        return $this->install_error;
    }


    /**
     * Get configuration for requireJS
     * @return array configuration properties
     *         - array paths map of paths
     */
    public function getRequireConfig()
    {
        return array(
            'paths' => array_merge(
                array('app' => 'assets/scripts/common.min'),
                Config::get('UI_VENDOR_SCRIPTS')
            ),
        );
    }


    /**
     * Get system configuration
     * @return array configuration properties
     *         - array config  configuration map
     *         - array params  URL parameters map
     */
    public function getSystemConfig()
    {
        // Get current session instance
        $session = $this->getSession();

        // Get current locale instance
        $locale = $this->getLocale();

        // Get target user
        $user = $this->getUser();

        $result = array();

        // Configuration properties
        $result['config'] = array(
               'APP_HOME'      => Config::get('UI_APP_HOME'),
            'APP_LOGIN'     => Config::get('UI_APP_LOGIN'),
            'API_PATH'      => Config::get('UI_API_PATH'),
               'PATH_PARAM'    => Config::get('UI_PATH_PARAM'),
               'SYSTEM_TITLE'  => Config::get('SYSTEM_TITLE'),
            'UI_LOGIN'      => $this->hasAuthentication(),
            'VENDOR_STYLES' => Config::get('UI_VENDOR_STYLES'),
           );

        // User properties
        $result['user'] = array(
            'is_empty' => $user->isEmpty(),
        );

           // Calendar properties
        $result['calendar'] = $locale->getCalendarConfig();

        // URL parameters
        $result['params'] = (object) $this->getUrlParams();

        // Locale translations
        $result['text'] = $locale->getTextsByPattern(
              "'^(action|colorpicker|commentslist|datagrid|info|toast|calendar|view)_'m"
        );

        return $result;
    }


    /**
     * Get system title
     * @return string|null title or null on failure
     */
    public function getSystemTitle()
    {
        return Config::get('SYSTEM_TITLE');
    }


    /**
     * Get named URL parameters
     * @return array map of parameters
     */
    private function getUrlParams()
    {
        $result = array();

        // Target URL parameter name
        $name = Config::get('UI_PATH_PARAM');

        // No parameter exists
        if (!$this->hasParameter($name))
            return $result;

        // Get parts
        $parts = $this->getParameter($name);
        $parts = trim($parts, '/');
        $parts = explode('/', $parts);

        // Find parts
        foreach ($parts as $part)
        {
            // Invalid syntax
            if (strpos($part, ':') === false)
                continue;

            // Separate name and value
            list($name, $value) = explode(':', $part, 2);

            // Add to  list
            $result[$name] = $value;
        }

        return $result;
    }


    /**
     * Check if the system is installed and try
     * to install when necessary
     * @return bool operation outcome
     */
    public function install()
    {
        // Already installed
        if ($this->getStorage()->isInstalled())
            return true;

           // Call system service for setup
           try
           {
               $this->callService('system.install');
           }
           catch (Exception $e)
           {
               $this->install_error = $this->getLocaleText(
                   'toast_'.$e->getMessage()
               );
           }
           catch (\Exception $e)
           {
               $this->install_error = sprintf(
                   'PHP error: %s', $e->getMessage()
               );
           }

           // Must not have an installation error
           return is_null($this->install_error);
    }
}
