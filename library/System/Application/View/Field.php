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

			// Field properties
			$result['data_name']     = $this->getDataName();
			$result['data_value']    = $this->getDataValue($this->getDataDefault());
			$result['data_format']   = $this->getNodeString('data/@format');
			$result['data_required'] = $this->isDataRequired();

			// Field properties
			$result['type_label']        = $this->getTypeLabel();
			$result['type_enabled']      = $this->getNodeBoolean('enabled', true);
			$result['type_help']         = $this->getNodeString('help');
			$result['type_instructions'] = $this->getNodeString('instructions');

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