<?php

    namespace Tinycar\System\Application\Component;

    use Tinycar\Core\Http\Params;
    use Tinycar\System\Application\Component;

    class GraphWidget extends Component
    {


    	/**
    	 * @see Tinycar\System\Application\Component::getDataSource()
    	 */
    	public function getDataSource()
    	{
    		// Try to get service property value
    		$service = $this->xdata->getString('data/@service');
    		return (is_string($service) ? $service : 'graphwidget.data');
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

    		// Defaults
    		$result = array(
    			'max_width'   => 0,
    			'max_value'    => 1,
    			'item_amount'  => 1,
    			'item_width'   => 40,
    			'item_percent' => 0,
    			'item_points'  => array(),
    			'item_data'    => array(),
    		);

    		$items = array();

    		// Manipulate data to fit component data
    		foreach ($data as $name => $row)
    		{
   				// Defaults
   				$item = array(
   					'label' => '',
					'value' => 0,
  				);

    		   	// Add custom value
   				if (array_key_exists('value', $row))
   				{
   					if (is_int($row['value']) || is_float($row['value']))
    					$item['value'] = $row['value'];
   				}

   				// Add custom label
   				if (array_key_exists('label', $row))
   				{
   					if (is_string($row['label']) || is_int($row['label']))
   						$item['label'] = $row['label'];
   				}

   				// Update maximum value
   				$result['max_value'] = max(
   					$item['value'], $result['max_value']
   				);

    			// Add to list
    			$result['item_data'][] = $item;
    		}

    		// Update maximum amount
    		$result['item_amount'] = max(
    			$result['item_amount'], count($result['item_data'])
    		);

    		// Calculate single item's percentage width
    		$result['item_percent'] = round(
    			100 / $result['item_amount'], 3
    		);

    		// Calculate total width based on item amount
    		$result['max_width'] = (
    			$result['item_amount'] * $result['item_width']
    		);

    		// Calculate points
    		foreach ($result['item_data'] as $i => $item)
    		{
    			// Calculate coordinates
    			$x = round($i * $result['item_width'] + $result['item_width'] / 2);
    			$y = 100 - ($item['value'] > 0 ? round(100 * $item['value'] / $result['max_value']) : 0);

    			// Update point data
    			$result['item_data'][$i]['x'] = $x;
    			$result['item_data'][$i]['y'] = $y;

    			// Add to point summary
    			$result['item_points'][] = $x.','.$y;
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

            return $result;
        }
    }