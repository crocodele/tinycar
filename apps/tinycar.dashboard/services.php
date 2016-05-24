<?php

use Tinycar\Core\Http\Params;

/**
 * List application intances supported by the systm
 * @param object $params Tinycar\Core\Http\Params instance
 * @return array applications and their properties from system.applications
 */
$api->setService('applications', function(Params $params) use ($system)
{
    // Get list of applications from the system
    $list = $system->callService('system.applications');

    $result = array();

    // Pick suitable applications
    foreach ($list as $item)
    {
        if ($item['is_system'] !== true)
            $result[] = $item;
    }

    return $result;
});