<?php

namespace Tinycar\System\Application;

use Tinycar\App\Config;
use Tinycar\Core\Xml\Data;
use Tinycar\System\Application;
use Tinycar\System\Application\Component;
use Tinycar\System\Application\Model\Property;
use Tinycar\System\Application\Xml\Action;
use Tinycar\System\Application\Xml\Section;
use Tinycar\System\Application\View\Tab;

class View extends Section
{
    private $session_actions;
    private $system_actions;
    private $tabs;


    /**
     * Map specified data to components and get as model data
     * @param array $data source data to study
     * @return array mapped data
     */
    public function getAsModelData(array $data)
    {
        $result = array();

        // Set new values to components
        foreach ($data as $id => $value)
        {
            // Try to get component
            $component = $this->getComponentById($id);

            // Invalid component
            if (!is_object($component))
                continue;

            // No component data name available
            if (is_null($component->getDataName()))
                continue;

            // Update data value
            $component->setDataValue($value);

            // Pick property value
            $result[$component->getDataName()] = $component->getDataValue();
        }

        return $result;
    }


    /**
     * Get map of values that are used for binding in this vaiew
     * @return array map of values as key-property values
     */
    public function getBindValues()
    {
        // Default values
        $result = array(
            'id' => '$data.id',
        );

        // Study all components from application level
        foreach ($this->app->getComponents() as $item)
        {
            foreach ($item->getBindRules() as $name => $rules)
                $result[$name] = '$data.'.$name;
        }

        // Translate to initial values
        foreach ($result as $name => $value)
            $result[$name] = $this->getStringValue($value);

        return $result;
    }


    /**
     * Get view details line string
     * @return string|null details line or null on failure
     */
    public function getDetailsLine()
    {
        return $this->getStringValue(
            $this->xdata->getString('details')
        );
    }


    /**
     * Get view name
     * @return string name
     */
    public function getName()
    {
        $name = $this->xdata->getString('@name');
        return (is_string($name) ? $name : 'default');
    }


    /**
     * Get view layout type
     * @return string layout type
     */
    public function getLayoutType()
    {
        $value = $this->xdata->getString('layout');
        return (is_string($value) ? $value : 'default');
    }


    /**
     * Get sessio nactions data for application and current specified view
     * @return array list of Tinycar\System\Application\Xml\Action instances
     */
    public function getSessionActions()
    {
        // Already resolved
        if (is_array($this->session_actions))
            return $this->session_actions;

        $result = array();

        // Expand/collapse sidelist
        if ($this->app->hasSideList())
        {
            // Add expand/collapse action
            $result[] =  new Action(array(
                'target' => 'session',
                'type'   => 'list',
                'label'  => $this->app->getLocaleText('action_sidelist'),
            ));
        }

        // Logout link
        if ($this->system->hasAuthentication() && $this->system->hasAuthenticated())
        {
            $result[] = new Action(array(
                'target'    => 'session',
                'type'      => 'logout',
                'icon'      => 'logout',
                'label'     => $this->app->getLocaleText('action_logout'),
                'service'   => 'session.logout',
                'link_path' => array('app'  => Config::get('UI_APP_LOGIN'), 'view' => 'out'),
            ));
        }

        // Remember
        $this->session_actions = $result;
        return $this->session_actions;
    }


    /**
     * Get system actions data for application and current specified view
     * @return array list of Tinycar\System\Application\Xml\Action instances
     */
    public function getSystemActions()
    {
        // Already resolved
        if (is_array($this->system_actions))
            return $this->system_actions;

        // System applications
        $app_home = Config::get('UI_APP_HOME');
        $app_apps = Config::get('UI_APP_APPS');

        $result = array();

        // Home action
        $result[] = new Action(array(
            'target'    => 'system',
            'type'      => 'home',
            'label'     => $this->app->getLocaleText('action_home'),
            'link_path' => array('app' => $app_home, 'view' => 'default'),
        ));

        // Applications action
        if (is_string($app_apps))
        {
            $result[] = new Action(array(
                'target'    => 'system',
                'type'      => 'apps',
                'label'     => $this->app->getLocaleText('action_apps'),
                'link_path' => array('app' => $app_apps, 'view' => 'default'),
            ));
        }

        // Remember
        $this->system_actions = $result;
        return $this->system_actions;
    }


    /**
     * Get list of tab item instances
     * @return array list of Tinycar\System\Application\View\Tab instances
     */
    public function getTabs()
    {
        // Already resolved
        if (is_array($this->tabs))
            return $this->tabs;

        $result = array();

        // Create instances
        foreach ($this->xdata->getNodes('tabs/tab') as $node)
            $result[] = new Tab($this, $node);

        // Remember
        $this->tabs = $result;
        return $this->tabs;
    }


    /**
     * Get view actions data for application and current specified view
     * @return array list of Tinycar\System\Application\Xml\Action instances
     */
    public function getViewActions()
    {
        // Get direct actions
        $result = $this->getActions();

        // Add actions from a sidelist
        if ($this->app->hasSideList())
        {
            // Get sidelist
            $sidelist = $this->app->getSideList();

            // Add sidelist actions
            foreach ($sidelist->getActions() as $item)
                $result[] = $item;
        }

        return $result;
    }


    /**
     * Check to seee if this is the default view
     * @return bool is default view
     */
    public function isDefault()
    {
        return ($this->getName() === 'default');
    }

}
