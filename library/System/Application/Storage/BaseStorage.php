<?php

namespace Tinycar\System\Application\Storage;

use Tinycar\System\Application;
use Tinycar\System\Application\Storage\KeyQuery;
use Tinycar\System\Application\Storage\RowQuery;

class BaseStorage
{
    public $app;
    public $model;
    public $query;
    public $storage;


    /**
     * Initiate class
     * @param object $app Tinycar\System\Application instance
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->storage = $app->getStorage();
        $this->model = $app->getModel();
    }


    /**
     * Get query instance to search for model rows
     * @return object Tinycar\System\Application\Storage\KeyQuery instance
     */
    public function getKeyQuery()
    {
        // Create new query instance
        $result = new KeyQuery(
            $this->storage, $this->model
        );

        // Defaults
        $result->removed(false);

        return $result;
    }


    /**
     * Get query instance to search for model rows
     * @return object Tinycar\System\Application\Storage\RowQuery instance
     */
    public function getRowQuery()
    {
        // Create new query instance
        $result = new RowQuery(
            $this->storage, $this->model
        );

        // Defaults
        $result->removed(false);

        return $result;
    }
}
