<?php

	namespace Tinycar\System\Application\Component;

	use Tinycar\Core\Http\Params;
	use Tinycar\System\Application\Component;

	class NavigationList extends Component
	{

		/**
		 * Get select list options data
		 * @return array options data
		 */
		private function getOptionsData()
		{
			$result = array();

			// Go through options
			foreach ($this->xdata->getNodes('options/option') as $node)
			{
				// Add to list
				$result[] = array(
					'name'    => $node->getString('@name'),
					'label'   => $this->getStringValue(
						$node->getString('@label')
					),
				);
			}

			return $result;
		}


		/**
		 * @see Tinycar\System\Application\Compont::onModelAction()
		 */
		public function onModelAction(Params $params)
		{
			$result = parent::onModelAction($params);

			// Get properties
			$result['link'] = $this->xdata->getAttributes('link');
			$result['options'] = $this->getOptionsData();

			return $result;
		}
	}