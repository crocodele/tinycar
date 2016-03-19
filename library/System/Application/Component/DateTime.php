<?php
	
	namespace Tinycar\System\Application\Component;

	use Tinycar\Core\Http\Params;
	use Tinycar\System\Application\View\Field;
	
	class DateTime extends Field
	{
		
		
		/**
		 * @see Tinycar\System\Application\View\Field::onModelAction()
		 */
		public function onModelAction(Params $params)
		{
			$result = parent::onModelAction($params);
			
			// Default data format
			if (is_null($result['data_format']))
			{
				$result['data_format'] = $this->view->getStringValue(
					'$format.datetime'
				);
			}
			
			// Field instructions
			$result['instructions'] = $this->view->getStringValue(
				$this->xdata->getString('instructions')
			);
			
			return $result;
		}
	}