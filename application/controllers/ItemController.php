<?php

/**
 * Controller for listing and editing items (messages and images)
 *
 * @package    application_controllers
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class ItemController extends Zend_Controller_Action {

    /**
     * Initialize controller (set language object, base etc.)
     *
     * @return void
     */
    public function init() {
        // initialize view
        $view = $this->initView();
        
        // set language
        $view->language = Zend_Registry::get('language');
        
        // set translate object
        $view->translate()->setTranslator($view->language);
        
        // no automatic view rendering
        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(true);
    }
    
    
    /**
     * shows a list of items
     * will be called on every change of settings and
     * returns the messages and images
     *
     * @return void
     */
    public function listAction() {
        // read settings
        $settings = $this->getRequest()->getPost();
        
        // get list template vars
        $listHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('list');
        try {
            $listHelper->readItems($settings);
            $listHelper->setTemplateVars($this->view);
        } catch(Exception $e) {
            echo Zend_Json::encode(array( 'error' => $e->getMessage()));
            return;
        }
        
        // save settings
        $settingsModel = new application_models_settings();
        $settingsModel->save( array( 
                'currentPriorityStart'   => $settings['currentPriorityStart'],
                'currentPriorityEnd'     => $settings['currentPriorityEnd'],
                'view'                   => $settings['view'],
                'selected'               => $settings['selected'],
                'unread'                 => $settings['unread'],
                'starred'                => $settings['starred']
        ) );
        
        // get unread items
        $itemCounter = Zend_Controller_Action_HelperBroker::getStaticHelper('itemcounter');
        
        // return items
        $feedModel = new application_models_feeds();
        echo Zend_Json::encode(array( 
            'html'           => $this->view->render('item/list.'.Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->getViewSuffix()),
            'categories'     => $itemCounter->unreadItemsCategories($settings),
            'feeds'          => $itemCounter->unreadItemsFeeds($settings),
            'starred'        => $itemCounter->starredItems($settings),
            'all'            => $itemCounter->allItems($settings),
            'countfeeds'     => $feedModel->count(Zend_Registry::get('session')->currentPriorityStart, 
                                                  Zend_Registry::get('session')->currentPriorityEnd)
        ));
    }
    
    
    /**
     * list more items
     * will be executed on clicking on more
     *
     * @return void
     */
    public function listmoreAction() {
        // read settings
        $settings = $this->getRequest()->getPost();
        
        // get items
        $result = $this->listItems($settings);
        
        // show items
        echo Zend_Json::encode($result);
    }
    
    
    /**
     * mark as read
     * sends a new image or message to client
     * if only unread items filter set
     *
     * @return void
     */
    public function markAction() {
        // get item
        $item = $this->getItem();
        if($item===false)
            return;
        
        // toggle item mark
        if($item->unread==1)
            $item->unread = 0;
        else
            $item->unread = 1;
        $item->save();
        
        // get next item only unread items visible? 
        $itemCounter = Zend_Controller_Action_HelperBroker::getStaticHelper('itemcounter');
        $settings = $this->getRequest()->getParams();
        $result = array();
        if($settings['unread'] == 1) {
            // load next item (if item exists)
            $settings['offset'] = $this->getRequest()->getParam('items');
            $settings['itemsperpage'] = 1;
            $result = $this->listItems($settings);
        }
        
        // return unread items
        $result['categories'] = $itemCounter->unreadItemsCategories();
        $result['feeds'] = $itemCounter->unreadItemsFeeds();
        
        echo Zend_Json::encode($result);
    }
    
    
    /**
     * mark all as read
     *
     * @return void
     */
    public function markallAction() {
        $items = $this->getRequest()->getParam('items');
        if(!is_array($items)) {
            echo Zend_Json::encode(true);
            return;
        }
            
        $items = array_unique($items);
        
        // mark items as read
        $itemModel = new application_models_items();
        foreach($items as $id) {
            $item = $itemModel->find($id);
            if($item->count()>0) {
                $item->current()->unread = 0;
                $item->current()->save();
            }
        }
        
        unset($items);
        
        // count items of all categories
        $itemCounter = Zend_Controller_Action_HelperBroker::getStaticHelper('itemcounter');
        $items = $itemCounter->unreadItemsCategories();
        
        // no more unread items available?
        if(count($items)==1) {
            echo Zend_Json::encode(array(
                'success'    => true,
                'next'       => '0'
            ));
            return;
        }
        
        // get current selected cat or feed
        $selected = Zend_Registry::get('session')->selected;
        if(strlen($selected)==0)
            $selected = 'cat_0';
            
        // category selected?
        if(strlen($selected)>4 && substr($selected,0,4)=='cat_') {
            $current = substr($selected,4);
            $type = 'cat_';
        }
        
        // feed selected?
        if(strlen($selected)>5 && substr($selected,0,5)=='feed_') {
            $items = $itemCounter->unreadItemsFeeds();
            $current = substr($selected,5);
            $type = 'feed_';
        }
        
        // other unread items found and current has no more unread items
        if(!array_key_exists($current, $items) && count($items)>0) {
            $keys = array_keys($items);
            echo Zend_Json::encode(array(
                'success'    => true,
                'next'       => $type.$keys[0]
            ));
            return;
        
        // current has more unread items
        } else
            echo Zend_Json::encode(array(
                'success'  => true,
                'next'     => $selected
            ));
    }
    
    
    /**
     * mark as starred
     *
     * @return void
     */
    public function starAction() {
        // get item
        $item = $this->getItem();
        if($item===false)
            return;
        
        // toggle item mark
        if($item->starred==1)
            $item->starred = 0;
        else
            $item->starred = 1;
        $item->save();
        
        // return starred items
        $itemCounter = Zend_Controller_Action_HelperBroker::getStaticHelper('itemcounter');
        $result['starred'] = $itemCounter->starredItems();
        
        echo Zend_Json::encode($result);
    }
    
    
    /**
     * unstarr all
     *
     * @return void
     */
    public function unstarrallAction() {
        $items = $this->getRequest()->getParam('items');
        if(!is_array($items)) {
            echo Zend_Json::encode(true);
            return;
        }
        $items = array_unique($items);
        
        // mark items as unstarred
        $itemModel = new application_models_items();
        foreach($items as $id) {
            $item = $itemModel->find($id);
            if($item->count()>0) {
                $item->current()->starred = 0;
                $item->current()->save();
            }
        }
        
        echo Zend_Json::encode(true);
    }
    
    
    /**
     * list more items
     *
     * @return array with items
     * @param array $settings current settings
     */
    public function listItems($settings) {
    
        // get items
        $listHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('list');
        try {
            $listHelper->readItems($settings);
        } catch(Exception $e) {
            return array( 'error' => $e->getMessage());
        }
        
        // create html for message and return as json
        $return = array();
        if($settings['offset']>0 && $settings['view']=='messages') {
            // render message items
            $return['messages'] = $this->view->partialLoop(
                                    'item/message-item.'.Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->getViewSuffix(),
                                    $listHelper->getMessages()
                                );
                                
            // indicates whether more items available or not
            $return['more']    = $listHelper->hasMoreMessages();
        }
        
        // create html for multimedia and return as json
        if($settings['offset']>0 && $settings['view']=='multimedia') {
            // render multimedia items
            $return['multimedia'] = $this->view->partialLoop(
                                    'item/multimedia-item.'.Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->getViewSuffix(),
                                    $listHelper->getMultimedia()
                                );
                                
            // indicates whether more items available or not                    
            $return['more'] = $listHelper->hasMoreMultimedia();
        }
        
        return $return;
    }
    
    
    /**
     * validates given item id and returns item row object
     *
     * @return Zend_Db_Table_Row|bool false on error or item row object
     */
    protected function getItem() {
        // get id
        $id = $this->getRequest()->getParam('id');
        
        // search and validate given id
        $itemModel = new application_models_items();
        $item = $itemModel->find($id);
        if($item->count()==0) {
            echo Zend_Json::encode(array(
                'error'     => $this->view->translate('invalid item id')
            ));
            return false;
        } else
            return $item->current();
    }
}

