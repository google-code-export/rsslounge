<?php

/**
 * Controller for change the settings
 *
 * @package    application_controllers
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class SettingsController extends Zend_Controller_Action {

    /**
     * Initialize controller (set language object, base etc.)
     *
     * @return void
     */
    public function init() {
        // initialize view
        $view = $this->initView();
        
        // set translate object
        $view->translate()->setTranslator(Zend_Registry::get('language'));
    }

    
    /**
     * Show edit dialog
     *
     * @return void
     */
    public function indexAction() {
        // load available languages
        $this->view->languages = array();
        foreach(Zend_Registry::get('language')->getList() as $lang)
            $this->view->languages[$lang] = Zend_Registry::get('language')->translate($lang);
            
        $this->view->username = Zend_Registry::get('config')->login->username;
    }
    
    
    /**
     * Save new settings
     *
     * @return void
     */
    public function saveAction() {
        // suppress view rendering
        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(true);
        
        // save username password
        $activateLogin = $this->getRequest()->getParam('activate_login');
        $username = $this->getRequest()->getParam('username',false);
        $password = $this->getRequest()->getParam('password',false);
        $passwordAgain = $this->getRequest()->getParam('password_again',false);
        
        $result = array();
        
        // deactivate login
        if($activateLogin=='0') {
            $this->removeLogin();
            
        // activate login
        } else {
            // any data changed?
            if($username!=Zend_Registry::get('config')->login->username || strlen($password)!=0 ) {
                if($password!=$passwordAgain)
                    $result = array('password_again' => Zend_Registry::get('language')->translate('given passwords not equal'));
                else if(strlen(trim($password))!=0 && strlen(trim($username))==0)
                    $result = array('username' => Zend_Registry::get('language')->translate('if you set a password you must set an username') );
                else 
                    $this->saveLogin($username, $password);
            }
        }
        
        // save new settings
        if(count($result)==0) {
            $settingsModel = new application_models_settings();
            $result = $settingsModel->save($this->getRequest()->getPost());
        }
        
        // return result (errors or success)
        $this->_helper->json($result);
    }
    

    /**
     * save login
     *
     * @return void
     */
    private function saveLogin($username, $password) {
        $config = file_get_contents(CONFIG_PATH);
        if(strlen($username)!=0)
            $config = str_replace('login.username = ' . Zend_Registry::get('config')->login->username, 'login.username = '.trim($username), $config);
        
        if(strlen($password)==0 && strlen(Zend_Registry::get('config')->login->password)!=0)
            $password = Zend_Registry::get('config')->login->password;
        else
            $password = sha1($password);
        
        $config = str_replace('login.password = ' . Zend_Registry::get('config')->login->password, 'login.password = '.$password, $config);
        file_put_contents(CONFIG_PATH, $config);
    }


    /**
     * remove login
     *
     * @return void
     */
    private function removeLogin() {
        $config = file_get_contents(CONFIG_PATH);
        $config = str_replace('login.username = ' . Zend_Registry::get('config')->login->username, 'login.username = ', $config);
        $config = str_replace('login.password = ' . Zend_Registry::get('config')->login->password, 'login.password = ', $config);
        file_put_contents(CONFIG_PATH, $config);
    }    
}

