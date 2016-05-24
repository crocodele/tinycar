<?php

namespace Tinycar\System\Application;

use Tinycar\App\Config;
use Tinycar\Core\Xml\Data;
use Tinycar\System\Application;
use Tinycar\System\Application\Webhook;

class Manifest
{
    private $app;
    private $xdata;
    private $webhooks;


    /**
     * Initiate class
     * @param object $app Tinycar\System\Application instance
     * @param object $xdata Tinycar\Core\Xml\Data instance
     */
    public function __construct(Application $app, Data $xdata)
    {
        $this->app = $app;
        $this->xdata = $xdata;
    }


    /**
     * Get manifest XML as a string
     * @return string XML
     */
    public function getAsXml()
    {
        return $this->xdata->getAsXml();
    }


    /**
     * Get application color
     * @return string|null color or null on failure
     */
    public function getColor()
    {
        return $this->xdata->getString('app/color');
    }


    /**
     * Get application description
     * @return string|null color or null on failure
     */
    public function getDescription()
    {
        return $this->xdata->getString('app/description');
    }


    /**
     * Get application icon data
     * @return string icon data
     */
    public function getIconData()
    {
        // System path to application-specific file
        $file = $this->getResourcePath('/icon.png');

        // No such file, revet to default image
        if (!file_exists($file))
        {
            $file = Config::getPath('SYSTEM_PATH',
                '/public/assets/base/images/default-appicon.png'
            );
        }

        // Read file
        return sprintf(
            'data:image/png;base64,%s',
            base64_encode(file_get_contents($file))
        );
    }


    /**
     * Get application layout name
     * @return string layout name
     */
    public function getLayoutName()
    {
        // Login application is always using the modal layout
        if ($this->app->getId() === Config::get('UI_APP_LOGIN'))
            return 'modal';

        // Main layout name
        return 'main';
    }


    /**
     * Get application name
     * @return string name
     */
    public function getName()
    {
        return $this->xdata->getString('app/name');
    }


    /**
     * Get application provider
     * @return string provider
     */
    public function getProvider()
    {
        return $this->xdata->getString('app/provider');
    }


    /**
     * Get path to specified application resource file
     * @param string $postfix target postfix string
     * @return string system path to resource file
     */
    public function getResourcePath($postfix)
    {
        return Config::getPath('APPS_FOLDER',
            '/'.$this->app->getId().$postfix
        );
    }


    /**
     * Get webhook instances
     * @return array Tinycar\System\Application\Webhook instances
     */
    public function getWebhooks()
    {
        // Already resolved
        if (is_array($this->webhooks))
            return $this->webhooks;

        $result = array();

        // Create instances
        foreach ($this->xdata->getNodes('webhook') as $node)
            $result[] = new Webhook($this->app, $node);

        // Remember
        $this->webhooks = $result;
        return $this->webhooks;
    }


    /**
     * Get webhook instances by action name
     * @param string $action target action name
     * @return array Tinycar\System\Application\Webhook instances
     */
    public function getWebhooksByAction($action)
    {
        $result = array();

        foreach ($this->getWebhooks() as $item)
        {
            if ($item->getAction() === $action)
                $result[] = $item;
        }

        return $result;
    }


    /**
     * Check if this application uses a local storage database
     * @return bool has a local storage database
     */
    public function hasStorageModel()
    {
        return (count($this->xdata->getNodes('model/property')) > 0);
    }
}
