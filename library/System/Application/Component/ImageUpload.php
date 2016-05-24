<?php

namespace Tinycar\System\Application\Component;

use Tinycar\Core\Http\Params;
use Tinycar\System\Application\View\Field;

class ImageUpload extends Field
{


    /**
     * @see Tinycar\System\Application\View\Field::getDataValue()
     */
    public function getDataValue($default = null)
    {
        // Get data value
        $result = parent::getDataValue($default);

        // Change direct value into an array
        if (is_int($result))
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
        return $result;
    }
}
