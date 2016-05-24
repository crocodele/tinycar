<?php

namespace Tinycar\Core\Data;

use Tinycar\System\Application\Storage;
use Tinycar\System\Application\Storage\Record;
use Tinycar\System\Application\Storage\RecordList;

class RowQuery
{
    protected $data = array();
    protected $idlist;
    protected $properties;


    /**
     * Initiate class
     * @param array $data source data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }


    /**
     * Get desire data item properties
     * @param array $item source data item
     * @return array $item with only desired properties
     */
    private function getItemProperties(array $item)
    {
        // No custom properties defined
        if (!is_array($this->properties))
            return $item;

        $result = array();

        // Pick properties
        foreach ($this->properties as $name)
        {
            $result[$name] = (array_key_exists($name, $item) ?
                $item[$name] : null
            );
        }

        return $result;
    }


    /**
     * Search for requested results
     * @return object Tinycar\System\Application\Storage\RecordList instance
     */
    public function find()
    {
        $result = new RecordList();

        // Empty list of requested id's, none will match
        if (is_array($this->idlist) && count($this->idlist) === 0)
            return $result;

        // Find matching rows
        foreach ($this->data as $item)
        {
            // Id is invalid
            if (!$this->isItemForIdList($item))
                continue;

            // Add desired properties to list
            $result->add(Record::loadFromCustomData(
                $this->getItemProperties($item)
            ));
        }

        return $result;
    }


    /**
     * Set specified list of id's to contstraint to
     * @param array $list list of target row id's
     */
    public function idlist(array $list)
    {
        $this->idlist = $list;
    }


    /**
     * Check if specified data item is acceptable for id list
     * @param array $item source data item
     * @return bool
     */
    private function isItemForIdList(array $item)
    {
        // List of id's is not limited
        if (!is_array($this->idlist))
            return true;

        // Item has no id
        if (!array_key_exists('id', $item))
            return false;

        // Check if id appears in list
        return in_array($item['id'], $this->idlist);
    }


    /**
     * Set properties to return
     * @param array $properties list of property names
     */
    public function properties(array $properties)
    {
        $this->properties = $properties;
    }
}
