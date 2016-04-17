<?php

	namespace Tinycar\System\Application\Component;

	use Tinycar\Core\Http\Params;
	use Tinycar\System\Application\Component;

	class CommentsList extends Component
	{


		/**
		 * Get specified as safe message HTML
		 * @param string $source original message
		 * @return $message sanitized HTML
		 */
		private function getMessageAsHtml($source)
		{
			// Encode HTML entities
			$source = htmlentities($source, null, 'UTF-8');

			// Turn new lines into breaks
			return nl2br($source);
		}


		/**
		 * @see Tinycar\System\Application\Compont::onDataAction()
		 */
		public function onDataAction(Params $params)
		{
			// Current record
			$record = $this->view->getDataRecord();

			// Call target service
			$data = $this->app->callService('commentslist.data', array(
				'app'  => $this->app->getId(),
				'row'  => $record->get('id'),
			));

			$result = array();

			// Manipulate data to fit component data
			foreach ($data as $row)
			{
				// Defaults
				$item = array(
				    'type'    => 'message',
				    'icon'    => 'talk',
					'author'  => null,
					'created' => 0,
					'message' => null,
				);

				// Add type
				if (array_key_exists('type', $row) && is_string($row['type']))
				    $item['type'] = $row['type'];

				// Add icon
				if (array_key_exists('icon', $row) && is_string($row['icon']))
				    $item['icon'] = $row['icon'];

				// Add author name
				if (array_key_exists('author', $row))
				{
					if (is_string($row['author']))
						$item['author'] = $row['author'];
				}

				// Add created timestamp
				if (array_key_exists('created', $row))
				{
					if (is_int($row['created']))
						$item['created'] = $row['created'];
				}

				// Add message
				if (array_key_exists('message', $row))
				{
					if (is_string($row['message']))
					{
						$item['message'] = $this->getMessageAsHtml(
							$row['message']
						);
					}
				}

				// Add to list
				$result[] = $item;
			}

			// Sort by creation timestamp
			usort($result, function(array $a, array $b)
			{
			    return ($a['created'] < $b['created'] ?
			        -1 : ($a['created'] > $b['created'] ? +1 : 0)
			    );
			});

			return $result;
		}


		/**
		 * When a new item should be inserted
		 * @param object $params Tinycar\Core\Http\Params instance
		 *               - string message target message
		 * @return bool operation outcome
		 */
		public function onInsertAction(Params $params)
		{
			// Current record
			$record = $this->view->getDataRecord();

			// Call target service
			return $this->app->callService('commentslist.insert', array(
				'app'  => $this->app->getId(),
				'row'  => $record->get('id'),
				'data' => $params->getAll(),
			));
		}


		/**
		 * @see Tinycar\System\Application\Compont::onModelAction()
		 */
		public function onModelAction(Params $params)
		{
			$result = parent::onModelAction($params);

			// Heading
			$result['heading'] = $this->getNodeString(
				'heading', '$locale.commentslist_comments'
			);

			// Insert label
			$result['insert_label'] = $this->getNodeString(
				'insert/label', '$locale.commentslist_new_comment'
			);

			// Insert placeholder
			$result['insert_placeholder'] = $this->getNodeString(
				'insert/placeholder', '$locale.commentslist_write_comment'
			);

			// Button label
			$result['insert_button'] = $this->getNodeString(
				'insert/button', '$locale.commentslist_add_comment'
			);

			return $result;
		}
	}