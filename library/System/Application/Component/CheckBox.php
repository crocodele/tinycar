<?php
	
	namespace Tinycar\System\Application\Component;

	use Tinycar\Core\Http\Params;
	use Tinycar\System\Application\View\Field;
	
	class CheckBox extends Field
	{
		
		
		/**
		 * @see Tinycar\System\Application\View\Field::onModelAction()
		 */
		public function onModelAction(Params $params)
		{
			$result = parent::onModelAction($params);
			
			// Move type label to field data
			$result['label'] = $this->getTypeLabel();
			$result['type_label'] = null;
			
			// Field instructions
			$result['instructions'] = $this->view->getStringValue(
				$this->xdata->getString('instructions')
			);
			
			return $result;
		}
	}