<?php

	namespace Tinycar\System\Application\Component;

	use Tinycar\Core\Http\Params;
	use Tinycar\System\Application\View\Field;

	class Text extends Field
	{

		/**
		 * Get link properties
		 * @return array link properties in key-value pairs
		 */
		private function getLinkProperties()
		{
			$result = array();

			// Get attributes
			$list = $this->xdata->getAttributes('link');

			// Process values
			foreach ($list as $name => $value)
				$result[$name] = $this->view->getStringValue($value);

			return $result;
		}


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

			// Link properties
			$result['link_path'] = $this->getLinkProperties();
			$result['link_url']  = $this->getNodeString('link/url');

			return $result;
		}
	}