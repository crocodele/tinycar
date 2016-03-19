<?php 

	namespace Tinycar\System\Application\View;
	
	use Tinycar\Core\Xml\Data;
	use Tinycar\System\Application\View;
	
	class Action
	{
		protected $data = array();
		
		
		/**
		 * Initiate class
		 * @param array $data initial data
		 */
		public function __construct(array $data)
		{
			$this->data = $data;
		}
		
		
		/**
		 * Load action data from view instance
		 * @param object $view Tinycar\System\Application\View instance
		 * @param object $xdata Tinycar\Core\Xml\Data instance
		 * @return object Tinycar\System\Application\Action instance
		 */
		public static function loadFromView(View $view, Data $xdata)
		{
			// Defaults
			$data = array(
				'target' => 'view',
				'type'   => 'default',
				'label'   => '',
				'service' => $xdata->getString('@service'),
				'link'    => $xdata->getAttributes('link'),
				'toast'   => $xdata->getString('toast'),
				'dialog'  => $xdata->getString('@dialog'),
			);
			
			// Resolve type
			$type = $xdata->getString('@type');
			$label = $xdata->getString('@label');
			
			// Custom type
			if (is_string($type))
				$data['type'] = $type;
			
			// Default label using type
			if (!is_string($label))
				$label = '$locale.action_'.$data['type'];
			
			// Default toast message
			if (!is_string($data['toast']) && $type === 'remove')
				$data['toast'] = '$locale.toast_removed_success';
			
			// Default service name
			if (!is_string($data['service']) && $type === 'remove')
				$data['service'] = 'storage.remove';
			
			// Resolve locales
			$data['label'] = $view->getStringValue($label);
			$data['toast'] = $view->getStringValue($data['toast']);
			
			return new self($data);
		}
		
		
		/**
		 * Get as data structure
		 * @return array data properties
		 */
		public function getAll()
		{
			return array(
				'type'    => $this->getType(),
				'target'  => $this->get('target'),
				'label'   => $this->getLabel(),
				'link'    => $this->getLink(),
				'service' => $this->getService(),
				'toast'   => $this->get('toast'),
				'dialog'  => $this->get('dialog'),
			);
		}
		
		
		/**
		 * Get specified data property value
		 * @param string $name target data property name
		 * @return mixed|null property value or null on failure
		 */
		protected function get($name)
		{
			return (array_key_exists($name, $this->data) ?
				$this->data[$name] : null
			);
		}
		
		
		/**
		 * Get action label
		 * @return string|null label or null on failure
		 */
		public function getLabel()
		{
			return $this->get('label');
		}
		
		
		/**
		 * Get action link parameters
		 * @return array|null link parameters or null on failure
		 */
		public function getLink()
		{
			return $this->get('link');
		}
		
		
		/**
		 * Get action service name
		 * @return string|null service or null on failure
		 */
		public function getService()
		{
			return $this->get('service');
		}
		
		
		/**
		 * Get action type
		 * @return string|null type or null on failure
		 */
		public function getType()
		{
			return $this->get('type');
		}
	}
	