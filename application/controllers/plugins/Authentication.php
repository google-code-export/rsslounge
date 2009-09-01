<?php

/**
 * Plugin for authenticate the user
 *
 * @package    application_controllers
 * @subpackage plugins
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Plugin_Authentication extends Zend_Controller_Plugin_Abstract {

    /**
     * checks whether a user needs a login and is loggedin
     * otherwise redirect to login page
     *
     * @return void
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request) {
        // check whether user loggedin (allow update/silent without login for cronjob)
        if(!Zend_Registry::get('session')->authenticated 
            && !($request->getControllerName()=='update' && $request->getActionName()=='silent') ) {
        
            // no login required?
            if(!Zend_Registry::get('config')->login->username) {
                Zend_Registry::get('session')->authenticated = true;
                return;
            } else {
                $request->setControllerName('index');
                $request->setActionName('login');
            }
        }
    }

}