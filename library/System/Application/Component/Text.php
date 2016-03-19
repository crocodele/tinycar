<?php
	
	namespace Tinycar\System\Application\Component;

	use Tinycar\Core\Http\Params;
	use Tinycar\System\Application\View\Field;
	
	class Text extends Field
	{
		
		/**
		 * @see Tinycar\System\Application\View\Field::onModelAction()
		 */
		public function onModelAction(Params $params)
		{
			$result = parent::onModelAction($params);
				
			// Placeholder content
			$result['placeholder'] = $this->view->getStringValue(
				$this->xdata->getString('placeholder')
			);
				
			return $result;
		}
	}