<?php

	Tinycar\App\Config::addAll(array(

		// System properties
		'SYSTEM_TITLE'    => 'Tinycar',
		'SYSTEM_VERSION'  => '0.1',
		'SYSTEM_TIMEZONE' => 'UTC',
		'SYSTEM_LOCALE'   => 'default',
		'SYSTEM_PATH'     => dirname(__DIR__),
		'SYSTEM_SALT'     => 'ew6KR7Vj',

		// System folders
		'APPS_FOLDER'      => dirname(__DIR__).'/apps',
		'SERVICES_FOLDER'  => dirname(__DIR__).'/services',
		'STORAGE_FOLDER'   => dirname(__DIR__).'/storage',

		// UI properties
		'UI_API_PATH'      => 'api.php',
		'UI_PATH_PARAM'    => 'p',

		// Applications
		'UI_APP_APPS'      => 'tinycar.applications',
		'UI_APP_HOME'      => 'tinycar.dashboard',
		'UI_APP_LOGIN'     => null,

		// External vendor scripts
		'UI_VENDOR_SCRIPTS' => array(
			'jquery'             => '//ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min',
		    'jquery.fileupload'  => 'assets/vendor/jquery.fileupload/jquery.fileupload.min',
		    'jquery.ui.widget'   => 'assets/vendor/jquery.ui.widget/jquery.ui.widget.min',
			'jqueryui'           => 'assets/vendor/jqueryui/jquery-ui.min',
			'trumbowyg'          => 'assets/vendor/trumbowyg/trumbowyg.min',
		),

		// External vendor styles
		'UI_VENDOR_STYLES' => array(
			'trumbowyg' => 'assets/vendor/trumbowyg/ui/trumbowyg.min',
		),

		// Fixed login credentials for default login application
		'LOGIN_CREDENTIALS' => array(
			array('id' => 1, 'username' => 'admin', 'password' => 'admin'),
		),

	));
