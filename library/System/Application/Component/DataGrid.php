<?php

	namespace Tinycar\System\Application\Component;

	use Tinycar\Core\Http\Params;
	use Tinycar\System\Application\Component;
	use Tinycar\System\Application\Model\Property;

	class DataGrid extends Component
	{


		/**
		 * Get data for columns
		 * @return array columns data
		 */
		private function getColumnsData()
		{
			$result = array();

			// Pick columns properties
			foreach ($this->xdata->getNodes('columns/column') as $node)
			{
				// Defaults
				$item = array(
					'name'   => $node->getString('@name'),
					'type'   => $node->getString('@type'),
					'label'  => $node->getString('@label'),
					'locale' => $node->getString('@locale'),
					'format' => $node->getString('@format'),
					'width'  => $node->getInt('@width'),
				);

				// Try to get model property from name
				$property = $this->getStringValue($item['name']);

				// We have a property, set some defaults
				if ($property instanceof Property)
				{
					// Update properties
					$item['name'] = $property->getName();
					$item['type'] = $property->getType();

					// Set default format
					if (is_null($item['format']))
						$item['format'] = '$format.'.$property->getTypeFormat();
				}

				// Set default type
				if (is_null($item['type']))
					$item['type'] = 'string';

				// Set default label
				if (is_null($item['label']))
					$item['label'] = '$locale.'.$item['name'];

				// Process locales
				$item['label'] = $this->getStringValue($item['label']);
				$item['format'] = $this->getStringValue($item['format']);

				// Add to list
				$result[] = $item;
			}

			return $result;
		}


		/**
		 * @see Tinycar\System\Application\Component::getDataSource()
		 */
		public function getDataSource()
		{
			$service = parent::getDataSource();
			return (is_string($service) ? $service : 'storage.rows');
		}


		/**
		 * Get linkage data
		 * @return array link date
		 */
		public function getLinkData()
		{
			return $this->xdata->getAttributes('link');
		}


		/**
		 * @see Tinycar\System\Application\Compont::onDataAction()
		 */
		public function onDataAction(Params $params)
		{
			// Get source data
			$data = parent::onDataAction($params);

			// Get columns
			$columns = $this->getColumnsData();

			$result = array();

			// Manipulate data to fit our datagrid column syntax
			foreach ($data as $row)
			{
				// Default
				$item = array(

					'id' => array(
						'value' => $row['id'],
						'text'  => strval($row['id']),
					),

				);

				// Process values for each column
				foreach ($columns as $column)
				{
					// Target value
					$value = (array_key_exists($column['name'], $row) ?
						$row[$column['name']] : ''
					);

					// Target text
					$text = strval($value);

					// Format as timestamp
					if ($column['type'] === 'epoch' && is_int($value))
						$text = date($column['format'], $value);

					// Format as locale
					else if (is_string($column['locale']))
					{
						$text = $this->view->getStringValue(
							'$locale.'.$column['locale'].$value
						);
					}

					// Add column data
					$item[$column['name']] = array(
						'value' => $value,
						'text'  => $text,
					);
				}

				// Add row data
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

			// Link properties
			$result['link'] = $this->getLinkData();

			// Datagrid columns
			$result['columns'] = $this->getColumnsData();

			return $result;
		}
	}