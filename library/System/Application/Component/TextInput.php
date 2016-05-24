<?php

namespace Tinycar\System\Application\Component;

use Tinycar\Core\Http\Params;
use Tinycar\System\Application\Component;
use Tinycar\System\Application\View\Field;

class TextInput extends Field
{


    /**
     * @see Tinycar\System\Application\View\Field::onModelAction()
     */
    public function onModelAction(Params $params)
    {
        $result = parent::onModelAction($params);

        // Input properties
        $result['rows']      = $this->xdata->getInt('rows');
        $result['width']     = $this->xdata->getInt('width');
        $result['maxlength'] = $this->xdata->getInt('maxlength');

        // Placeholder text
        $result['placeholder'] = $this->getStringValue(
            $this->xdata->getString('placeholder')
        );

        return $result;
    }
}
