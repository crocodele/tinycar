<?php

    // Get runtime
    require_once(dirname(__DIR__).'/run.php');
    
    // Create new manager instance
    $api = new Tinycar\Service\Manager();
    
    // Add request paramters
    $api->addParameters($_GET);
    $api->addParameters($_POST);
    $api->addRawData(file_get_contents('php://input'));
    
    // Show a response
    $api->show();