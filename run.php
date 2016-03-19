<?php

    // Get autoloader
    require_once(__DIR__.'/vendor/autoload.php');

    // Get configuration
    require_once(__DIR__.'/config/general.php');
    
    // Set default timeezone
    date_default_timezone_set(
    	Tinycar\App\Config::get('SYSTEM_TIMEZONE')
    );
    
    // Set error reporting level
    error_reporting(E_ALL);
    
    // Disable displaying errors on page
    ini_set('display_errors', false);
    
    // Set logfile for errors
    ini_set('error_log', Tinycar\App\Config::getPath(
    	'STORAGE_FOLDER', '/logs/php.log'
    ));
