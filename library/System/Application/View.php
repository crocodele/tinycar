<?php

	namespace Tinycar\System\Application;

	use Tinycar\Core\Xml\Data;
	use Tinycar\System\Application;
	use Tinycar\System\Application\Component;
	use Tinycar\System\Application\Model\Property;
	use Tinycar\System\Application\Xml\Section;
	use Tinycar\System\Application\View\Tab;

	class View extends Section
	{
		protected $tabs;


		/**
		 * Map specified data to components and get as model data
		 * @param array $data source data to study
		 * @return array mapped data
		 */
		public function getAsModelData(array $data)
		{
			$result = array();

			// Set new values to components
			foreach ($data as $id => $value)
			{
				// Try to get component
				$component = $this->getComponentById($id);

				// Invalid component
				if (!is_object($component))
					continue;

				// No component data name available
				if (is_null($component->getDataName()))
					continue;

				// Update data value
				$component->setDataValue($value);

				// Pick property value
				$result[$component->getDataName()] = $component->getDataValue();
			}

			return $result;
		}


		/**
		 * Get view name
		 * @return string name
		 */
		public function getName()
		{
			$name = $this->xdata->getString('@name');
			return (is_string($name) ? $name : 'default');
		}


		/**
		 * Get view layout type
		 * @return string layout type
		 */
		public function getLayoutType()
		{
			$value = $this->xdata->getString('layout');
			return (is_string($value) ? $value : 'default');
		}


		/**
		 * Get list of tab item instances
		 * @return array list of Tinycar\System\Application\View\Tab instances
		 */
		public function getTabs()
		{
			// Already resolved
			if (is_array($this->tabs))
				return $this->tabs;

			$result = array();

			// Create instances
			foreach ($this->xdata->getNodes('tabs/tab') as $node)
				$result[] = new Tab($this, $node);

			// Remember
			$this->tabs = $result;
			return $this->tabs;
		}


		/**
		 * Check to seee if this is the default view
		 * @return bool is default view
		 */
		public function isDefault()
		{
			return ($this->getName() === 'default');
		}

	}
