<?php

namespace Tinycar\System\Application\Component;

use Tinycar\Core\Http\Params;
use Tinycar\System\Application\Component;
use Tinycar\System\Application\View\Field;

class TagsList extends Field
{


    /**
     * @see Tinycar\System\Application\View\Field::getDataValue()
     */
    public function getDataValue($default = null)
    {
        // Get data value
        $result = parent::getDataValue($default);

        // Change direct value into an array
        if (is_string($result))
            $result = array($result);

        // Enforce array type
        if (!is_array($result))
            $result = array();

        return $result;
    }


    /**
     * @see Tinycar\System\Application\View\Field::onModelAction()
     */
    public function onModelAction(Params $params)
    {
        $result = parent::onModelAction($params);

        // Placeholder text
        $result['placeholder'] = $this->getStringValue(
            $this->xdata->getString('placeholder')
        );

        return $result;
    }
}
