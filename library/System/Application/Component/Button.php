<?php

	namespace Tinycar\System\Application\Component;

	use Tinycar\Core\Http\Params;
	use Tinycar\System\Application\Component;

	class Button extends Component
	{


		/**
		 * When button is clicked
		 * @param object $params Tinycar\Core\Http\Params instance
		 * @return bool operation outcome
		 */
		public function onClickAction(Params $params)
		{
			return $this->app->callService(
				$this->xdata->getString('service'),
				$this->view->getAsModelData($params->getAll())
			);
		}


		/**
		 * @see Tinycar\System\Application\Compont::onModelAction()
		 */
		public function onModelAction(Params $params)
		{
			$result = parent::onModelAction($params);

			// Button properties
			$result['icon']  = $this->xdata->getString('icon');
			$result['toast'] = $this->xdata->getString('toast');

			// Default toast message
			if (is_null($result['toast']))
				$result['toast'] = '$locale.toast_action_processed';

			// Process locales
			$result['toast'] = $this->view->getStringValue($result['toast']);

			return $result;
		}
	}