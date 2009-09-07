<?PHP

// set testing configuration
define('APPLICATION_ENV', 'testing');

// base configuration
require_once('../const.php');

// Zend_Application
require_once 'Zend/Application.php';  

// Create application, bootstrap
$application = new Zend_Application(
    APPLICATION_ENV, 
    CONFIG_PATH
);

$application->bootstrap();

Zend_Registry::set('application',$application);