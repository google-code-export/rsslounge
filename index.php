<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

// define path to configuration file
define('CONFIG_DIST_PATH',APPLICATION_PATH . '/../updates/config-dist.ini');
define('CONFIG_PATH',APPLICATION_PATH . '/../config/config.ini');

// install or run
if(!file_exists(CONFIG_PATH))
    require_once('updates/install.php');
else {

    /** Zend_Application */
    require_once 'Zend/Application.php';  

    // Create application, bootstrap, and run
    $application = new Zend_Application(
        APPLICATION_ENV, 
        CONFIG_PATH
    );
    $application->bootstrap()
                ->run();

}