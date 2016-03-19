<?php
	
	namespace Tinycar\System\Application\Component;

	use Tinycar\Core\Http\Params;
	use Tinycar\System\Application\View\Field;
	
	class Toggle extends Field
	{
		
		
		/**
		 * @see Tinycar\System\Application\View\Field::onModelAction()
		 */
		public function onModelAction(Params $params)
		{
			$result = parent::onModelAction($params);
			
			// Field instructions
			$result['instructions'] = $this->view->getStringValue(
				$this->xdata->getString('instructions')
			);
			
			return $result;
		}
	}