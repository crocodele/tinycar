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
        $params->get('app') === Config::get('UI_APP_LOGIN') ||
        $system->hasAuthentication() === false ||
        $system->hasAuthenticated() === true
    );
});



/**
 * Get applicatoin image
 * @param object $params Tinycar\Core\Http\Params instance
 * @return string base64 encoded data source
 * @throws Tinycar\Core\Exception
 */
$api->setService('appimage', function(Params $params) use ($system)
{
    // Get target application
    $instance = $system->getApplicationById($params->get('app'));

    // Get manifest for application
    $manifest = $instance->getManifest();

    // Get icon image data
    return $manifest->getIconData();
});



/**
 * Get system actions
 * @param object $params Tinycar\Core\Http\Params instance
 * @return array list of actions and their properties
 * @throws Tinycar\Core\Exception
 */
$api->setService('systemactions', function(Params $params) use ($system)
{
    // Get target application
    $instance = $system->getApplicationById($params->get('app'));

    // Set URL parameters to application request
    $instance->setUrlParams($params->getArray('url'));

    // Get target view
    $view = $instance->getViewByName($params->get('view'));

    $result = array();

    // Get actions for active view
    foreach ($view->getSystemActions() as $item)
        $result[] = $item->getAll();

    return $result;
});


/**
 * Get session actions
 * @param object $params Tinycar\Core\Http\Params instance
 * @return array list of actions and their properties
 * @throws Tinycar\Core\Exception
 */
$api->setService('sessionactions', function(Params $params) use ($system)
{
    // Get target application
    $instance = $system->getApplicationById($params->get('app'));

    // Set URL parameters to application request
    $instance->setUrlParams($params->getArray('url'));

    // Get target view
    $view = $instance->getViewByName($params->get('view'));

    $result = array();

    // Get actions for active view
    foreach ($view->getSessionActions() as $item)
        $result[] = $item->getAll();

    return $result;
});


/**
 * Get view actions
 * @param object $params Tinycar\Core\Http\Params instance
 * @return array list of actions and their properties
 * @throws Tinycar\Core\Exception
 */
$api->setService('viewactions', function(Params $params) use ($system)
{
    // Get target application
    $instance = $system->getApplicationById($params->get('app'));

    // Set URL parameters to application request
    $instance->setUrlParams($params->getArray('url'));

    // Get target view
    $view = $instance->getViewByName($params->get('view'));

    $result = array();

    // Get actions for active view
    foreach ($view->getViewActions() as $item)
        $result[] = $item->getAll();

    return $result;
});
