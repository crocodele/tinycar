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
			// Target service
			$service = $this->getNodeString(
				'action/@service', 'button.click'
			);

			// Current record
			$record = $this->view->getDataRecord();

			// Call target service
			return $this->app->callService($service, array(
				'app'  => $this->app->getId(),
				'row'  => $record->get('id'),
				'data' => $this->view->getAsModelData($params->getAll()),
			));
		}


		/**
		 * @see Tinycar\System\Application\Compont::onModelAction()
		 */
		public function onModelAction(Params $params)
		{
			$result = parent::onModelAction($params);

			// Button properties
			$result['button_dialog'] = $this->getNodeString('action/@dialog');
			$result['button_icon']   = $this->getNodeString('action/@icon');
			$result['button_label']  = $this->getNodeString('action/@label');
			$result['button_toast']  = $this->getNodeString('action/toast', '$locale.toast_action_processed');

			// Type properties
			$result['type_instructions'] = $this->getNodeString('instructions');

			return $result;
		}
	}