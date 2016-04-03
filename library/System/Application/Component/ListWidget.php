<?php

    namespace Tinycar\System\Application\Component;

    use Tinycar\Core\Http\Params;
    use Tinycar\System\Application\Component;
    use Tinycar\System\Application\Component\DataGrid;

    class ListWidget extends DataGrid
    {



    	/**
    	 * @see Tinycar\System\Application\Component::getDataSource()
    	 */
    	public function getDataSource()
    	{
    		// Try to get service property value
    		$service = $this->xdata->getString('data/@service');
    		return (is_string($service) ? $service : 'listwidget.data');
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