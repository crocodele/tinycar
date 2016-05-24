<?php

namespace Tinycar\System\Application\Component;

use Tinycar\Core\Http\Params;
use Tinycar\System\Application\View\Field;

class SelectList extends Field
{

    /**
     * Get select list options data
     * @return array options data
     */
    private function getOptionsData()
    {
        $result = array();

        // Go trough options
        foreach ($this->xdata->getNodes('options/option') as $node)
        {
            // Add to list
            $result[] = array(
                'name'    => $node->getString('@name'),
                'label'   => $this->getStringValue(
                    $node->getString('@label')
                ),
            );
        }

        return $result;
    }


    /**
     * @see Tinycar\System\Application\Compont::onModelAction()
     */
    public function onModelAction(Params $params)
    {
        $result = parent::onModelAction($params);

        // Get options data
        $result['options'] = $this->getOptionsData();

        return $result;
    }
}
