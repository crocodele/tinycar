<?php
	
	namespace Tinycar\System\Application\Component;

	use Tinycar\Core\Http\Params;
	use Tinycar\System\Application\View\Field;
	
	class Reference extends Field
	{
		
		/**
		 * Get id of referenced application
		 * @return string target application id
		 */
		private function getDataAppId()
		{
			$result = $this->xdata->getString('data/@dialog');
			
			if (!is_string($result) || stripos($result, ':') === false)
				$result = $this->app->getId();
			
			return array_shift(explode(':', $result));
		}
		
		
		/**
		 * @see Tinycar\System\Application\View\Field::onDataAction()
		 */
		public function onDataAction(Params $params)
		{
			$result = array();
			
			// Get data values
			$value = $params->getArray('value');

			// Target application instance
			$app = $this->system->getApplicationById(
				$this->getDataAppId()
			);
			
			// Get model property name
			$name = $this->xdata->getString('item');
			$name = is_string($name) ? $name : 'name';
		
			// Find matching items
			$query = $app->getRowQuery();
			$query->properties('id', $name);
			$query->idlist($value);

			// Get rows
			return $query->find()->getAllData();
		}
		
		
		/**
		 * @see Tinycar\System\Application\View\Field::onModelAction()
		 */
		public function onModelAction(Params $params)
		{
			$result = parent::onModelAction($params);
			
			// Data properties
			$result['data_dialog'] = $this->xdata->getString('data/@dialog');
			$result['data_limit'] = $this->xdata->getInt('data/@limit');
			$result['data_value'] = array_map('intval', $result['data_value']);
			
			// Target items
			$result['type_items'] = $this->onDataAction(new Params(array(
				'value' => $this->getDataValue(),
			)));
			
			// Field instructions
			$result['instructions'] = $this->view->getStringValue(
				$this->xdata->getString('instructions')
			);
			
			return $result;
		}
	}