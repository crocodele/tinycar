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

			// Initial row amount
			$result['rows'] = $this->xdata->getInt('rows');
			
			// Maximum length
			$result['maxlength'] = $this->xdata->getInt('maxlength');
			
			// Placeholder text
			$result['placeholder'] = $this->getStringValue(
				$this->xdata->getString('placeholder')
			);
			
			return $result;
		}
	}