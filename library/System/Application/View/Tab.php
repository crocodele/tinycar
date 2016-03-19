<?php 

	namespace Tinycar\System\Application\View;
	
	use Tinycar\Core\Xml\Data;
	use Tinycar\System\Application\View;
	
	class Tab
	{
		protected $view;
		protected $xdata;
		
		
		/**
		 * Initiate class
		 * @param object $view Tinycar\System\Application\View instance 
		 * @param object $xdata Tinycar\Core\Xml\Data instance
		 */
		public function __construct(View $view, Data $xdata)
		{
			$this->view = $view;
			$this->xdata = $xdata;
		}
		
		
		/**
		 * Get tab label
		 * @return string|null label or null on failure
		 */
		public function getLabel()
		{
			return $this->view->getStringValue(
				$this->xdata->getString('@label')
			);
		}
		
		
		/**
		 * Get tab name
		 * @return string|null name or null on failure
		 */
		public function getName()
		{
			return $this->xdata->getString('@name');
		}
	}
	