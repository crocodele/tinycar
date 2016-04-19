<?php

	namespace Tinycar\System\Application\Component;

	use Tinycar\Core\Http\Params;
	use Tinycar\System\Application\Component;
	use Tinycar\System\Application\View\Field;

	class Password extends Field
	{


		/**
		 * @see Tinycar\System\Application\View\Field::onModelAction()
		 */
		public function onModelAction(Params $params)
		{
			$result = parent::onModelAction($params);

			// Properties
			$result['align']       = $this->xdata->getString('align');
			$result['placeholder'] = $this->getNodeString('placeholder');

			return $result;
		}
	}