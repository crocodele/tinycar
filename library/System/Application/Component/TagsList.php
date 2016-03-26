<?php

	namespace Tinycar\System\Application\Component;

	use Tinycar\Core\Http\Params;
	use Tinycar\System\Application\Component;
	use Tinycar\System\Application\View\Field;

	class TagsList extends Field
	{


		/**
		 * @see Tinycar\System\Application\View\Field::onModelAction()
		 */
		public function onModelAction(Params $params)
		{
			$result = parent::onModelAction($params);

			// Placeholder text
			$result['placeholder'] = $this->getStringValue(
				$this->xdata->getString('placeholder')
			);

			// Change direct value into an array
			if (is_string($result['data_value']))
				$result['data_value'] = array($result['data_value']);

			// Enforce array type
			if (!is_array($result['data_value']))
				$result['data_value'] = array();

			return $result;
		}
	}