<?php

    namespace Tinycar\System\Application\Xml;

    use Tinycar\Core\Xml\Data;
    use Tinycar\System\Application\Xml\Section;

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
         * Load action data from section instance
         * @param object $section Tinycar\System\Application\Xml\Section instance
         * @param object $xdata Tinycar\Core\Xml\Data instance
         * @return object Tinycar\System\Application\Action instance
         */
        public static function loadFromSection(Section $section, Data $xdata)
        {
            // Defaults
            $data = array(
                'target'  => 'view',
            	'icon'    => $xdata->getString('@icon'),
                'type'    => $xdata->getString('@type'),
                'label'   => $xdata->getString('@label'),
                'service' => $xdata->getString('@service'),
                'link'    => $xdata->getAttributes('link'),
                'toast'   => $xdata->getString('toast'),
                'dialog'  => $xdata->getString('@dialog'),
            );

            // Default label using type
            if (!is_string($data['label']))
                $data['label'] = '$locale.action_'.$data['type'];

            // Default toast message for remove
            if (!is_string($data['toast']) && $data['service'] === 'storage.remove')
                $data['toast'] = '$locale.toast_removed_success';

            // Default toast message for save
            if (!is_string($data['toast']) && $data['type'] === 'save')
             	$data['toast'] = '$locale.toast_saved_success';

            // Enforce service for save
            if ($data['type'] === 'save')
          		$data['service'] = 'application.save';

            // Resolve locales
            $data['label'] = $section->getStringValue($data['label']);
            $data['toast'] = $section->getStringValue($data['toast']);

            return new self($data);
        }


        /**
         * Get as data structure
         * @return array data properties
         */
        public function getAll()
        {
            return array(
            	'target'  => $this->getTarget(),
            	'icon'    => $this->getIcon(),
                'type'    => $this->getType(),
                'label'   => $this->getLabel(),
                'link'    => $this->getLink(),
            	'dialog'  => $this->getDialog(),
                'service' => $this->getService(),
                'toast'   => $this->getToast(),
            );
        }


        /**
         * Get specified data property value
         * @param string $name target data property name
         * @return mixed|null property value or null on failure
         */
        private function get($name)
        {
            return (array_key_exists($name, $this->data) ?
                $this->data[$name] : null
            );
        }


        /**
         * Get dialog name
         * @return string|nulll dialog or null on failure
         */
        public function getDialog()
        {
        	return $this->get('dialog');
        }


        /**
         * Get action icon
         * @return string|null icon or null on failure
         */
        public function getIcon()
        {
        	$value = $this->get('icon');
        	return (is_string($value) ? $value : $this->getType());
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
         * Get action target section
         * @return string|null target or null on failure
         */
        public function getTarget()
        {
        	return $this->get('target');
        }


        /**
         * Get action toast message
         * @return string|null toast or null on failure
         */
        public function getToast()
        {
        	return $this->get('toast');
        }


        /**
         * Get action type
         * @return string|null type or null on failure
         */
        public function getType()
        {
            return $this->get('type');
        }


        /**
         * Check to see if this is a system action
         * @return bool is system action
         */
        public function isSystemAction()
        {
        	return ($this->get('target') === 'system');
        }


        /**
         * Check to see if this is a session action
         * @return bool is session action
         */
        public function isSessionAction()
        {
        	return ($this->get('target') === 'session');
        }


        /**
         * Check to see if this is a view action
         * @return bool is view action
         */
        public function isViewAction()
        {
        	return ($this->get('target') === 'view');
        }
    }
