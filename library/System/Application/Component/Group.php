<?php

	namespace Tinycar\System\Application\Component;

	use Tinycar\Core\Http\Params;
	use Tinycar\System\Application\Component;
	use Tinycar\System\Application\View\Field;

	class Group extends Field
	{
		protected $components = array();


		/**
		 * @see Tinycar\System\Application\Compoent::init()
		 */
		public function init()
		{
			// Get components data
			$list = $this->xdata->getNodes('component');

			// Create instances
			foreach ($list as $xdata)
				$this->components[] = $this->view->createComponent($xdata);
		}


		/**
		 * @see Tinycar\System\Application\View\Field::onModelAction()
		 */
		public function onModelAction(Params $params)
		{
			$result = parent::onModelAction($params);

			// Properties
			$result['format'] = $this->xdata->getString('@format');
			$result['columns'] = $this->xdata->getInt('@columns');

			// Initiate components list
			$result['components'] = array();

			// Add group components
			foreach ($this->components as $item)
				$result['components'][] = $item->callAction('model');

			return $result;
		}
	}