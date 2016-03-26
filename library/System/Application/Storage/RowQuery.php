<?php

	namespace Tinycar\System\Application\Storage;

	use Tinycar\Core\Exception;
	use Tinycar\System\Application\Storage;
	use Tinycar\System\Application\Storage\BaseQuery;
	use Tinycar\System\Application\Storage\Record;
	use Tinycar\System\Application\Storage\RecordList;


	class RowQuery extends BaseQuery
	{
		protected $sql_idlist;
		protected $sql_properties = array();


		/**
		 * Add filters for results
		 * @param array $filters map of filters
		 * @throws Tinycar\Core\Exception
		 */
		public function filter(array $filters)
		{
			// Pick valid filters
			foreach ($filters as $name => $value)
			{
				// Try to get property instance
				$p = $this->model->getPropertyByName($name);

				// No such property was found
				if (is_null($p))
				{
					throw new Exception(
						'query_filter_unknown',
						array('filter' => $name)
					);
				}

				// Add to list
				$this->sql_filters[] = array(
					'name'  => $p->getName(),
					'value' => $p->getAsValue($value),
				);
			}
		}


		/**
		 * Search for requested results
		 * @return object Tinycar\System\Application\Storage\RecordList instance
		 */
		public function find()
		{
			// Empty list of requested id's
			if (is_array($this->sql_idlist) && count($this->sql_idlist) === 0)
				$data = array();

			// Get data with SQL
			else
			{
				$data = $this->query->getGrouped('
					SELECT p.row_id, r.id, r.created_time, r.modified_time,
						   r.removed_time, p.name, p.value
					FROM data_row_properties p '.
					$this->getSqlForJoins().' '.
					$this->getSqlForWhere().' '.
					$this->getSqlForLimit()
				);
			}

			$result = new RecordList();

			// Create instances with all properties
			foreach ($data as $item)
			{
				$result->add(Record::loadFromModelData(
					$this->model, $item, $this->sql_properties
				));
			}

			// Get property to sort by
			$property = $this->model->getPropertyByName(
				$this->sql_order
			);

			// Sort list
			if (is_object($property))
			{
				$result->sortByProperty(
					$property, $this->sql_sort
				);
			}

			return $result;
		}


		/**
		 * Get SQL string to use to join results
		 * @return string join string
		 */
		protected function getSqlForJoins()
		{
			$result = array();

			// Alayws join rows table
			$result[] = 'INNER JOIN data_rows r ON r.id=p.row_id';

			// Each filter is a join
			foreach ($this->sql_filters as $index => $item)
			{
				$result[] = sprintf(
					'INNER JOIN data_row_properties t%s '.
					'ON t%s.row_id=p.row_id',
					$index, $index
				);
			}

			// Wrap joins
			return implode(' ', $result);
		}


		/**
		 * Get SQL string to use to filter results
		 * @return string whre conditions string
		 */
		protected function getSqlForWhere()
		{
			$result = array();

			// Show only specified properties
			if (count($this->sql_properties) > 0)
			{
				$this->query->bind('propertylist:stringlist', $this->sql_properties);
				$result[] = 'p.name IN (:propertylist)';
			}

			// Show only removed
			if ($this->sql_removed === true)
				$result[] = 'r.removed_time IS NOT NULL';

			// Excelude removed
			else if ($this->sql_removed === false)
				$result[] = 'r.removed_time IS NULL';

			// Id is restricted
			if (is_array($this->sql_idlist))
			{
				$this->query->bind('idlist:intlist', $this->sql_idlist);
				$result[] = 'r.id IN (:idlist)';
			}

			// Each filter is a join
			foreach ($this->sql_filters as $index => $item)
			{
				// Bind values for upcoming query
				$this->query->bind('v'.$index.'name:string', $item['name']);
				$this->query->bind('v'.$index.'value:string', $item['value']);

				// Add conditions
				$result[] = sprintf('t%s.name=:v%sname', $index, $index);
				$result[] = sprintf('t%s.value=:v%svalue', $index, $index);
			}

			// No conditions at all
			if (count($result) === 0)
				return '';

			// Wrap conditions
			return 'WHERE '.implode(' AND ', $result);
		}


		/**
		 * Get specified record by id
		 * @param int $id target row id
		 * @return object|null Tinycar\System\Application\Storage\Record instance
		 *                     or null on failure
		 * @throws Tinycar\Core\Exception
		 */
		public function id($id)
		{
			// Set id
			$this->sql_idlist = array(intval($id));

			// Try to find
			$list = $this->find();

			// Get first result
			return $list->first();
		}


		/**
		 * Set specified list of id's to contstraint to
		 * @param array $list list of target row id's
		 */
		public function idlist(array $list)
		{
			$this->sql_idlist = $list;
		}


		/**
		 * Set result order
		 * @param string $order target property to sort by
		 * @param string $sort sorting deiction (asc|desc)
		 * @throws Tinycar\Core\Exception
		 */
		public function order($order, $sort)
		{
			// Verify validity from model
			if (is_null($this->model->getPropertyByName($order)))
				throw new Exception('query_order_unknown');

			// Invalid sorting value
			if ($sort !== 'asc' && $sort !== 'desc')
				throw new Exception('query_sort_unknown');

			// Remember
			$this->sql_order = $order;
			$this->sql_sort = $sort;
		}


		/**
		 * Set properties to return
		 * @param array $properties list of property names
		 */
		public function properties(array $properties)
		{
			$this->sql_properties = $properties;
		}
	}