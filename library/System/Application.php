<?php

namespace Tinycar\System;

use Tinycar\App\Config;
use Tinycar\Core\Exception;
use Tinycar\Core\Format;
use Tinycar\App\Locale;
use Tinycar\App\Manager;
use Tinycar\App\Services;
use Tinycar\App\Url;
use Tinycar\Core\Http\Params;
use Tinycar\Core\Xml\Data;
use Tinycar\System\Application\Component;
use Tinycar\System\Application\Dialog;
use Tinycar\System\Application\Manifest;
use Tinycar\System\Application\Model;
use Tinycar\System\Application\Model\Variable;
use Tinycar\System\Application\View;
use Tinycar\System\Application\SideBar;
use Tinycar\System\Application\SideList;
use Tinycar\System\Application\Storage;
use Tinycar\System\Application\Xml\Action;
use Tinycar\System\Application\Xml\Section;

class Application
{
    private $data = array();
    private $component_index = array();
    private $component_list = array();
    private $dialogs;
    private $id;
    private $locale;
    private $manifest;
    private $model;
    private $properties;
    private $sidebar;
    private $sidelist;
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
     * Create a component instnace for this view
     * @param object $section Tinycar\System\Application\Xml\Section instance
     * @param object $xdata Tinycar\Core\Xml\Data instance
     * @return object Tinycar\System\Application\Component instance
     */
    public function createComponent(Section $section, Data $xdata)
    {
        // Current compnent index number
        $index = count($this->component_list);

        // Create new instance
        $result = Component::loadByType(
            $this->system, $this, $section, 'cmp-'.$index, $xdata
        );

        // Target id
        $id = $result->getId();

        // Add to list
        $this->component_list[] = $result;
        $this->component_index[$id] = $index;

        // Initiate component for use
        $result->init();

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
     * @return object|null Tinycar\System\Application\Xml\Action instance
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
     * @return array list of Tinycar\System\Application\Xml\Action instances
     */
    public function getActions(View $view)
    {
        $result = array();

        // Add view actions
        foreach ($view->getViewActions() as $item)
            $result[] = $item;

        // Add system actions
        foreach ($view->getSystemActions() as $item)
            $result[] = $item;

        // Add session actions
        foreach ($view->getSessionActions() as $item)
            $result[] = $item;

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
     * Get application color
     * @return string|null color or null on failure
     */
    public function getColor()
    {
        // Get color from application manifest
        $result = $this->getManifest()->getColor();

        // If necessary, check for a system default
        if (!is_string($result))
        {
            $sys_manifest = $this->system->getManifest();
            $result = $sys_manifest->getAppColor();
        }

        return $result;
    }


    /**
     * Get application color map
     * @return array map of colors and their adjustments
     *         - string base   base color
     *         - stirng dark   dark base color
     *         - stirng darker darker base color
     *         - string lite   light base color
     *         - string liter  liter base color
     */
    public function getColorMap()
    {
        $color = $this->getColor();

        return array(
            'base'   => $color,
            'dark'   => Format::adjustColor($color, -10),
            'darker' => Format::adjustColor($color, -25),
            'lite'   => Format::adjustColor($color, +10),
            'liter'  => Format::adjustColor($color, +60),
        );
    }


    /**
     * Get component instance by id
     * @param string $id target component id
     * @return object|null Tinycar\System\Application\Component instance or null on failure
     */
    public function getComponentById($id)
    {
        // No such component
        if (!array_key_exists($id, $this->component_index))
            return null;

        // Get component instance
        $index = $this->component_index[$id];
        return $this->component_list[$index];
    }


    /**
     * Get all registered component instances
     * @return array list of Tinycar\System\Application\Component instances
     */
    public function getComponents()
    {
        return $this->component_list;
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
     * Get application dialog by name
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
     * @param array [$vars] custom variables to add in key-value pairs
     * @return string locale property value or requested name on failure
     * @see Tinycar\App\Manager::getLocaleText()
     */
    public function getLocaleText($name, array $vars = array())
    {
        // Get from application's locale
        $locale = $this->getLocale();
        $value = $locale->getText($name, $vars);

        // Revert to system locale
        if (is_null($value))
            $value = $this->system->getLocaleText($name, $vars);

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
            $system  = $this->system;
            $app     = $this;
            $session = $this->system->getSession();
            $url     = $this->getUrlParams();

            // Get file
            include($file);
        }

        // Remember
        $this->services = $api;
        return $this->services;
    }


    /**
     * Get sidebar instance
     * @return object Tinycar\System\Application\SideBar instance
     */
    public function getSideBar()
    {
        // Already resolved
        if (!is_null($this->sidebar))
            return $this->sidebar;

        // Get system manifest
        $manifest = $this->system->getManifest();

        // Get sidebar instance for this application
        $instance = $manifest->getSideBar($this);

        // Remember
        $this->sidebar = $instance;
        return $this->sidebar;
    }


    /**
     * Get sidelist instance
     * @return object Tinycar\System\Application\SideList instance
     */
    public function getSideList()
    {
        // Already resolved
        if (!is_null($this->sidelist))
            return $this->sidelist;

        // Get node
        $node = $this->xdata->getNode('side');

        // Use a dummy node when none exists
        if (is_null($node))
            $node = $this->xdata->getAsNode(array());

        // Create new instance
        $instance = new SideList($this->system, $this, $node);

        // Remember
        $this->sidelist = $instance;
        return $this->sidelist;
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
     * @return object Tinycar\App\Url instance
     */
    public function getUrlParams()
    {
        // Already resolved
        if (is_object($this->url_params))
            return $this->url_params;

        // Remember empty instance
        $this->url_params = new Url(array());
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
     * Check if application has a sidebar
     * @return bool application has sidebar
     */
    public function hasSideBar()
    {
        return !$this->isLoginApplication();
    }


    /**
     * Check if application has a sidelist
     * @return bool application has sidelist
     */
    public function hasSideList()
    {
        return !is_null($this->xdata->getNode('side'));
    }


    /**
     * Check if application has specified view
     * @param string $name target view name
     * @return bool has view
     */
    public function hasView($name)
    {
        return array_key_exists($name, $this->getViews());
    }


    /**
     * Initialize application
     */
    public function init()
    {
        // Initialize related sections so that the
        // global component id's remain consistent

        if ($this->hasSidebar())
            $this->getSideBar()->getComponents();

        if ($this->hasSideList())
            $this->getSideList()->getComponents();
    }


    /**
     * Check if this is the system home application
     * @return bool is home application
     */
    public function isHomeApplication()
    {
        return ($this->getId() === Config::get('UI_APP_HOME'));
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
     * Check if this application is in development mode
     * @return bool is in development mode
     */
    public function isInDevmode()
    {
        return ($this->getData('devmode') === '1');
    }


    /**
     * Check if this is the system login application
     * @return bool is home application
     */
    public function isLoginApplication()
    {
        return ($this->getId() === Config::get('UI_APP_LOGIN'));
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
     * Load manifest for this application from source file
     * @return bool operation outcome
     * @throws Tinycar\Core\Exception
     */
    public function loadManifestFile()
    {
        try
        {
            // Try to load XML from file
            $instance = Data::loadFromFile(Config::getPath(
                'APPS_FOLDER', '/'.$this->getId().'/manifest.xml'
            ));
        }
        catch (Exception $e)
        {
            // Manifest is invalid
            throw new Exception('app_manifest_invalid', array(
                'id' => $this->getId(),
            ));
        }

        // Update data
        $this->xdata = $instance;

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
        $this->url_params = new Url($params);
    }
}
