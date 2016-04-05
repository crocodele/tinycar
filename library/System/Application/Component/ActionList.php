<?php

    namespace Tinycar\System\Application\Component;

    use Tinycar\Core\Http\Params;
    use Tinycar\System\Application\Component;

    class ActionList extends Component
    {

        /**
         * Get select list options data
         * @return array options data
         */
        private function getOptionsData()
        {
            // Current URL
            $url = $this->app->getUrlParams();

            // Call target service
            $data = $this->app->callService($this->getDataSource(), array(
                'url'  => $url->getAll(),
                'app'  => $url->get('app'),
                'view' => $url->get('view'),
                'row'  => $url->get('id'),
            ));

            $result = array();

            // Go through data
            foreach ($data as $row)
            {
                // Defaults
                $item = array(
                    'icon'    => null,
                    'type'    => '',
                    'label'   => null,
                	'link'    => null,
                    'dialog'  => null,
                	'service' => null,
                	'toast'   => null,
                );

                // Add custom icon
                if (array_key_exists('icon', $row))
                {
                    if (is_string($row['icon']))
                        $item['icon'] = $row['icon'];
                }

                // Add custom type
                if (array_key_exists('type', $row))
                {
                    if (is_string($row['type']))
                        $item['type'] = $row['type'];
                }

                // Add custom label
                if (array_key_exists('label', $row))
                {
                    if (is_string($row['label']))
                        $item['label'] = $row['label'];
                }

                // Add custom link
                if (array_key_exists('link', $row))
                {
                    if (is_array($row['link']) && count($row['link']) > 0)
                    	$item['link'] = $row['link'];
                }

                // Add custom dialog
                if (array_key_exists('dialog', $row))
                {
                	if (is_string($row['dialog']))
                		$item['dialog'] = $row['dialog'];
                }

                // Add custom service
                if (array_key_exists('service', $row))
                {
                	if (is_string($row['service']))
                		$item['service'] = $row['service'];
                }

                // Add custom toast message
                if (array_key_exists('toast', $row))
                {
                	if (is_string($row['toast']))
                		$item['toast'] = $row['toast'];
                }

                // Add to list
                $result[] = $item;
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
            $result['options'] = $this->getOptionsData();

            return $result;
        }
    }