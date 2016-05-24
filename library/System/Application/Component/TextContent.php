<?php

namespace Tinycar\System\Application\Component;

use Tinycar\Core\Http\Params;
use Tinycar\System\Application\Component;
use Tinycar\System\Application\Model\Property;

class TextContent extends Component
{


    /**
     * Get list of text variables
     * @return array map text variables
     */
    private function getVarsList()
    {
        $result = array();

        // Resolve variables
        foreach ($this->xdata->getNodes('var') as $node)
        {
            // Get variable name
            $name = $node->getString('@name');

            // Get variable value
            $value = $this->view->getStringValue($node->getString());

            // Property value
            if ($value instanceof Property)
            {
                // Get target record instance
                $record = $this->view->getDataRecord();

                // Get record instance property value
                $value = $record->get($value->getName());
            }

            // Add to list
            $result[$name] = $value;
        }

        return $result;
    }


    /**
     * @see Tinycar\System\Application\Compont::onModelAction()
     */
    public function onModelAction(Params $params)
    {
        $result = parent::onModelAction($params);

        // Get text contents
        $result['align'] = $this->xdata->getString('align');
        $result['text']  = $this->getNodeString('text');

        // Replace variables
        foreach ($this->getVarsList() as $name => $value)
        {
            $result['text'] = str_replace(
                '$'.$name,
                '<span class="var">'.$value.'</span>',
                $result['text']
            );
        }

        return $result;
    }
}
