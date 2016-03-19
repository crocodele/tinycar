<?php 

	namespace Tinycar\System\Application\View;
	
	use Tinycar\Core\Http\Params;
	use Tinycar\System\Application\Component;
	
	class Field extends Component
	{
		
		
		/**
		 * Get data formatting rule
		 * @return mixed data formatting rule
		 */
		public function getDataFormat()
		{
			return $this->xdata->getString('data/@format');
		}
		
		
		/**
		 * @see Tinycar\System\Application\Component::onModelAction()
		 */
		public function onModelAction(Params $params)
		{
			$result = parent::onModelAction($params);
			
			// Field name
			$result['data_name'] = $this->getDataName();
			
			// Field value
			$result['data_value'] = $this->getDataValue(
				$this->getDataDefault()
			);
			
			// Data format
			$result['data_format'] = $this->view->getStringValue(
				$this->xdata->getString('data/@format')
			);
			
			// Field is required
			$result['data_required'] = $this->isDataRequired();
			
			// Enabled state
			$result['type_enabled'] = $this->view->getStringValue(
				$this->xdata->getString('enabled')
			);
			
			// Field help text
			$result['type_help'] = $this->view->getStringValue(
				$this->xdata->getString('help')
			);
			
			// Field instructions
			$result['type_instructions'] = $this->view->getStringValue(
				$this->xdata->getString('instructions')
			);
			
			// Format value as yes or no
			if ($this->getDataFormat() === 'yesno')
			{
				$result['data_value'] = $this->app->getLocaleText((
					$result['data_value'] === true ? 'yes' : 'no'
				));
			}
			
			return $result;
		}
	}