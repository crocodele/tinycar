<?php

	use Tinycar\Core\Data\RowQuery;
	use Tinycar\Core\Http\Params;


	/**
	 * Helper service to filter and sort given data to match dataquery
	 * @param object $params Tinycar\Core\Http\Params instance
	 *               - array  data         source data
	 *               - array  [rows]       get only specified row id's
	 *               - array  [properties] list of property names to return
	 * @return array rows data
	 */
	$api->setService('rows', function(Params $params) use ($system)
	{
		// Create new static data query from data
		$query = new RowQuery($params->getArray('data'));

		// Get only specified rows
		if ($params->has('rows'))
			$query->idlist($params->getArray('rows'));

		// Set properties
		if ($params->has('properties'))
			$query->properties($params->getArray('properties'));

		// Get query results as as a native array
		return $query->find()->getAllData();
	});
