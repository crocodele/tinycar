<?php

	namespace Tinycar\System\Application\Component;

	use Tinycar\Core\Http\Params;
	use Tinycar\System\Application\Component;

	class Image extends Component
	{

		/**
		 * Get image data for the target image
		 * @return string|null image data or null on failure
		 */
		private function getImageData()
		{
			// Current URL
			$url = $this->app->getUrlParams();

			// Call target service
			return $this->app->callService($this->getDataSource(), array(
				'url'  => $url->getAll(),
				'app'  => $url->get('app'),
				'view' => $url->get('view'),
				'row'  => $url->get('id'),
			));
		}


		/**
		 * @see Tinycar\System\Application\Component::onModelAction()
		 */
		public function onModelAction(Params $params)
		{
			$result = parent::onModelAction($params);

			// Properties
			$result['image_data'] = $this->getImageData();

			return $result;
		}
	}