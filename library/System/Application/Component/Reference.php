<?php

namespace Tinycar\System\Application\Component;

use Tinycar\Core\Http\Params;
use Tinycar\System\Application\View\Field;

class Reference extends Field
{

    /**
     * Get id of referenced application
     * @return string target application id
     */
    private function getDataAppId()
    {
        $result = $this->xdata->getString('data/@dialog');

        if (!is_string($result) || stripos($result, ':') === false)
            $result = $this->app->getId();

        return array_shift(explode(':', $result));
    }


    /**
     * @see Tinycar\System\Application\View\Field::getDataValue()
     */
    public function getDataValue($default = null)
    {
        // Get data value
        $result = parent::getDataValue($default);

        // Change direct value into an array
        if (is_int($result) || is_string($result))
            $result = array($result);

        // Enforce array type
        if (!is_array($result))
            $result = array();

        // Use integers
        $result = array_map('intval', $result);

        return $result;
    }


    /**
     * @see Tinycar\System\Application\View\Field::onDataAction()
     */
    public function onDataAction(Params $params)
    {
        $result = array();

        // Target application instance
        $app = $this->system->getApplicationById(
            $this->getDataAppId()
        );

        // Target model property name
        $name = $this->xdata->getString('item');
        $name = is_string($name) ? $name : 'name';

        // Get data values
        $value = $params->getArray('value');

        // Get selected data items
        $result = $app->callService('storage.rows', array(
            'app'        => $app->getId(),
            'rows'       => $value,
            'properties' => array('id', $name),
        ));

        // Get data
        return (is_array($result) ? $result : array());
    }


    /**
     * @see Tinycar\System\Application\View\Field::onModelAction()
     */
    public function onModelAction(Params $params)
    {
        $result = parent::onModelAction($params);

        // Data properties
        $result['data_dialog'] = $this->xdata->getString('data/@dialog');
        $result['data_limit'] = $this->xdata->getInt('data/@limit');

        // Target items
        $result['type_items'] = $this->onDataAction(new Params(array(
            'value' => $this->getDataValue(),
        )));

        // Field instructions
        $result['instructions'] = $this->view->getStringValue(
            $this->xdata->getString('instructions')
        );

        return $result;
    }
}
