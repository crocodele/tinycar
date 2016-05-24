<?php

namespace Tinycar\System\Application\Component;

use Tinycar\System\Application\View\Field;

class Toggle extends Field
{


    /**
     * @see Tinycar\System\Application\View\Field::getDataValue()
     */
    public function getDataValue($default = null)
    {
        $result = parent::getDataValue($default);
        return (is_bool($result) ? $result : false);
    }
}
