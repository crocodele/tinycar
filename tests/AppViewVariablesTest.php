<?php

use Tinycar\App\Manager;
use Tinycar\System\Application\Model\Property;
use Tinycar\App\Services;

class AppViewVariablesTest extends PHPUnit_Framework_TestCase
{
    /**
     * Application name to test
     * @var string
     */
    public static $app_name = 'tinycar.demo';

    /**
     * Shared system instance to avoid duplicate sessions
     * @var Manager
     */
    public static $system;


    /**
     * @see PHPUnit_Framework_TestCase::setUpBeforeClass()
     */
    public static function setUpBeforeClass()
    {
        self::$system = new Manager();
    }


    /**
     * Test getting and application
     */
    public function testGetApp()
    {
        $app = self::$system->getApplicationById(self::$app_name);
        $this->assertEquals(is_object($app), true);
    }


    /**
     * Test getting a view
     * @depends testGetApp
     */
    public function testGetView()
    {
        $app = self::$system->getApplicationById(self::$app_name);
        $view = $app->getViewByName('default');
        $this->assertEquals(is_object($view), true);
    }


    /**
     * Test parsing application variable
     * @depends testGetView
     */
    public function testParseViewAppVariable()
    {
        $app = self::$system->getApplicationById(self::$app_name);
        $view = $app->getViewByName('default');
        $value = $view->getStringValue('$app.id');
        $this->assertEquals($value, $app->getId());
    }


    /**
     * Test parsing data variable
     * @depends testGetView
     */
    public function testParseViewDataVariable()
    {
        $app = self::$system->getApplicationById(self::$app_name);
        $app->setUrlParams(array('id' => 1));

        // Create a dummy data retrieval service
        $services = $app->getServices();
        $services->setService('storage.row', function()
        {
            return array('varname' => 45);
        });

        $view = $app->getViewByName('edit');
        $value = $view->getStringValue('$data.varname');

        $this->assertEquals(is_int($value), true);
        $this->assertEquals($value, 45);
    }


    /**
     * Test parsing negative local variable
     * @depends testGetView
     */
    public function testParseViewNegativeDataVariable()
    {
        $app = self::$system->getApplicationById(self::$app_name);
        $app->setUrlParams(array('id' => 1));

        // Create a dummy data retrieval service
        $services = $app->getServices();
        $services->setService('storage.row', function()
        {
            return array('varname' => true);
        });

        $view = $app->getViewByName('edit');
        $value = $view->getStringValue('!$data.varname');

        $this->assertEquals(is_bool($value), true);
        $this->assertEquals($value, false);
    }


    /**
     * Test parsing locale variable
     * @depends testGetView
     */
    public function testParseViewLocaleVariable()
    {
        $app = self::$system->getApplicationById(self::$app_name);
        $view = $app->getViewByName('default');
        $value = $view->getStringValue('$locale.name');
        $this->assertEquals($value, 'Name');
    }


    /**
     * Test parsing model variable
     * @depends testGetView
     */
    public function testParseViewModelVariable()
    {
        $app = self::$system->getApplicationById(self::$app_name);
        $view = $app->getViewByName('default');
        $value = $view->getStringValue('$model.id');
        $this->assertEquals(($value instanceof Property), true);
    }


    /**
     * Test parsing string of multiple variables
     * @depends testGetView
     */
    public function testParseViewComboVariable()
    {
        $app = self::$system->getApplicationById(self::$app_name);
        $app->setUrlParams(array('id' => 1));

        // Create a dummy data retrieval service
        $services = $app->getServices();
        $services->setService('storage.row', function()
        {
            return array('varname' => 45);
        });

        $view = $app->getViewByName('edit');
        $value = $view->getStringValue('$locale.name is $data.varname');

        $this->assertEquals(is_string($value), true);
        $this->assertEquals($value, 'Name is 45');
    }


    /**
     * Test parsing string with only static content
     * @depends testGetView
     */
    public function testParseViewStaticVariable()
    {
        $app = self::$system->getApplicationById(self::$app_name);
        $view = $app->getViewByName('edit');
        $value = $view->getStringValue('static content');
        $this->assertEquals(is_string($value), true);
        $this->assertEquals($value, 'static content');
    }
}