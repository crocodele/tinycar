<?php

namespace Tinycar\System\Application\Component;

use Tinycar\Core\Http\Params;
use Tinycar\System\Application\Component;

class NumbersWidget extends Component
{


    /**
     * @see Tinycar\System\Application\Component::getDataSource()
     */
    public function getDataSource()
    {
        // Try to get service property value
        $service = $this->xdata->getString('data/@service');
        return (is_string($service) ? $service : 'numberswidget.data');
    }


    /**
     * @see Tinycar\System\Application\Compont::onDataAction()
     */
    public function onDataAction(Params $params)
    {
        // Current record
        $record = $this->view->getDataRecord();

        // Call target service
        $data = $this->app->callService(
            $this->getDataSource(), array(
                'app'  => $this->app->getId(),
                'row'  => $record->get('id'),
        ));

        // Current options
        $options = $this->getOptionsData();

        $result = array();

        // Manipulate data to fit component data
        foreach ($data as $name => $row)
        {
            // This is not a supported option
            if (!array_key_exists($name, $options))
                continue;

            // Defaults
            $item = array(
                'value'  => null,
            );

            // Add custom value
            if (array_key_exists('value', $row))
            {
                if (is_string($row['value']) || is_int($row['value']) || is_float($row['value']))
                    $item['value'] = $row['value'];
            }

            // Add to list
            $result[$name] = $item;
        }

        return $result;
    }


    /**
     * Get number options data
     * @return array options data
     */
    private function getOptionsData()
    {
        $result = array();

        // Go through options
        foreach ($this->xdata->getNodes('options/option') as $node)
        {
            // Target option name
            $name = $node->getString('@name');

            // Add to list
            $result[$name] = array(
                'label' => $this->getStringValue($node->getString('@label')),
            );
        }

        return $result;
    }


    /**
     * @see Tinycar\System\Application\View\Field::onModelAction()
     */
    public function onModelAction(Params $params)
    {
        $result = parent::onModelAction($params);

        // Properties
        $result['heading'] = $this->getNodeString('heading');
        $result['options'] = $this->getOptionsData();

        return $result;
    }
}
