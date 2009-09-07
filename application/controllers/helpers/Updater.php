<?PHP

/**
 * Helper class for refresh the feeds (search and fetch new items)
 *
 * @package    application_controllers
 * @subpackage helpers
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Helper_Updater extends Zend_Controller_Action_Helper_Abstract {

    /**
     * updates a given feed
     * returns an error or true on success
     *
     * @return int timestamp for this refresh
     * @param mixed $feed current feed model
     */
    public function feed($feed) {
        
        // logging
        $logger = Zend_Registry::get('logger');
        $logger->log('start feed fetching "' . $feed->name.'"', Zend_Log::DEBUG);
        
        // get feed plugin
        $logger->log('load feed plugin', Zend_Log::DEBUG);
        $messagesModel = new application_models_messages();
        $plugin = Zend_Controller_Action_HelperBroker::getStaticHelper('pluginloader')->getPlugin($feed->source);
        if($plugin===false) {
            $logger->log('error loading feed plugin ' . $feed->source, Zend_Log::ERR);
            return $messagesModel->add($feed, 'unknown plugin');
        }
        
        // receive new content
        $logger->log('load feed content', Zend_Log::DEBUG);
        try {
            $plugin->load($feed->url);
        } catch(Exception $e) {
            $logger->log('error loading feed content: ' . $e->getMessage(), Zend_Log::ERR);
            return $messagesModel->add($feed, $e->getMessage());
        }
        
        // save html url
        $feed->htmlurl = $plugin->getHtmlUrl();
        $feed->save();
        
        // current date
        $now = Zend_Date::now();
        $now->sub(Zend_Registry::get('session')->deleteItems, Zend_Date::DAY);
        $logger->log('current date ' . $now, Zend_Log::DEBUG);
        
        // insert new items in database
        $logger->log('start item fetching', Zend_Log::DEBUG);
        $itemsModel = new application_models_items();
        foreach ($plugin as $item) {
            // test date: continue with next if item too old
            $date = new Zend_Date($item->getDate(), Zend_Date::ISO_8601);
            if($now->compare($date)==1 && Zend_Registry::get('session')->deleteItems!=0) {
                $logger->log('item "' . $item->getTitle() . '" (' . $date . ') older than '.Zend_Registry::get('session')->deleteItems.' days', Zend_Log::DEBUG);
                continue;
            }
            
            // filter match?
            try {
                if($this->filter($feed, $item)===false)
                    continue;
            } catch(Exception $e) {
                $messagesModel->add($feed, 'filter error');
                continue;
            }
            
            // item already in database?
            if($this->itemExists($item)===true)
                continue;
            
            // insert new item
            $logger->log('start insert new item', Zend_Log::DEBUG);
            $nitem = array(
                    'title'        => $this->stripAll( $item->getTitle() ),
                    'content'      => $this->stripContent( $item->getContent() ),
                    'feed'         => $feed->id,
                    'unread'       => 1,
                    'starred'      => 0,
                    'datetime'     => $item->getDate(),
                    'uid'          => $item->getId(),
                    'link'         => $this->stripAll( $item->getLink() )
                );
            
            // multimedia item: get and save thumbnail
            if($plugin->multimedia) {
                
                try {
                    // download and generate thumbnail
                    $thumbnail = $this->generateThumbnail($item->getThumbnail());
                
                    // set thumbnailpath as content
                    $nitem = array_merge(
                                $nitem,
                                array(
                                    'content' => $thumbnail
                                )
                            );
                } catch(Exception $e) {
                    $logger->log('thumbnail error ' . $e->getMessage(), Zend_Log::ERR);
                    $messagesModel->add($feed, $e->getMessage());
                    continue;
                }
            }
            
            // insert new item
            $itemsModel->insert($nitem);
            $logger->log('item inserted', Zend_Log::DEBUG);
        }
    
        // success: set lastrefresh
        $feed->lastrefresh = Zend_Date::now()->get(Zend_Date::TIMESTAMP);
        $feed->error = 0;
        $feed->save();
        
        // cleanup old items
        $logger->log('cleanup old items', Zend_Log::DEBUG);
        $this->cleanupOldItems();
        
        // destroy feed object (prevent memory issues)
        $logger->log('destroy feed object', Zend_Log::DEBUG);
        $plugin->destroy();
        
        return $feed->lastrefresh;
    }
    
    
    /**
     * clean up orphaned thumbnails
     *
     * @return void
     */
    public function cleanup() {
        // cleanup orphaned thumbnails
        $itemsModel = new application_models_items();
        $itemsModel->cleanup();
    }
    
    /**
     * clean up old items
     *
     * @return void
     */
    public function cleanupOldItems() {
        if(Zend_Registry::get('session')->deleteItems==0)
            return;
        $itemsModel = new application_models_items();
        $date = Zend_Date::now();
        $date->sub(Zend_Registry::get('session')->deleteItems, Zend_Date::DAY);
        $itemsModel->delete(
            $itemsModel->getAdapter()->quoteInto('datetime<?', $date->toString('YYYY-MM-dd') . ' 00:00:00')
        );
    }
    
    
    /**
     * set timeout in the session and 
     * returns the timeout for the next refresh
     *
     * @return int timeout
     */
    public function timeout() {
        // no lastrefresh set
        if(Zend_Registry::get('session')->lastrefresh==0)
            Zend_Registry::get('session')->timeout = 0;
        else {
            // calc seconds between now and last refresh
            $now = Zend_Date::now();
            $last = new Zend_Date();  
            $last->set(Zend_Registry::get('session')->lastrefresh,Zend_Date::TIMESTAMP);
            $diff = $now->sub($last);
            
            $diff = (Zend_Registry::get('session')->refresh*60) - $diff;
            
            // set timeout 0 if refresh intervall was exceed
            if($diff<0)
                $diff = 0;
            
            // set timeout in session
            Zend_Registry::get('session')->timeout = $diff;
        }
        
        return Zend_Registry::get('session')->timeout;
    }
    
    
    /**
     * download and create thumbnail
     *
     * @return generated filename
     * @param string $thumbnail url of the target image
     */
    protected function generateThumbnail($thumbnail) {
        // target filename
        $thumbnailFile = Zend_Registry::get('config')->thumbnails->path . md5($thumbnail);
        
        // load, resize and save
        $image = wiImage::load($thumbnail)
                ->resize(
                    Zend_Registry::get('config')->thumbnails->width, 
                    Zend_Registry::get('config')->thumbnails->height)
                ->saveToFile(
                    $thumbnailFile . '.jpg'
                    );
        
        return md5($thumbnail) . '.jpg';
    }
    

    /**
     * strip unallowed tags
     *
     * @return string stripped content
     * @param string $content unstripped content
     */
    protected function stripContent($content) {
        $tags = '<' . str_replace(',','><',Zend_Registry::get('config')->rss->allowed->tags) . '>';
        return $this->stripTags(
            $content,
            $tags,
            Zend_Registry::get('config')->rss->allowed->attribs
        );
    }
    
    
    /**
     * strip unallowed tags and attributes
     * from http://de.php.net/manual/en/function.strip-tags.php
     * I use this instead Zend_Filter_StripTags because of an bug:
     * on Windows Vista Apache crashes
     *
     * @return string stripped content
     * @param string $string unstripped content
     * @param array $allowedtags the allowed tags
     * @param array $allowedattributes the allowed attributes
     */
    protected function stripTags($string, $allowtags=NULL, $allowattributes=NULL) {
        $string = strip_tags($string, $allowtags);
        
        if (!is_null($allowattributes)) {
            if(!is_array($allowattributes))
                $allowattributes = explode(",",$allowattributes);
            if(is_array($allowattributes))
                $allowattributes = implode(")(?<!",$allowattributes);
            if (strlen($allowattributes) > 0)
                $allowattributes = "(?<!".$allowattributes.")";
            $string = preg_replace_callback("/<[^>]*>/i",create_function(
                '$matches',
                'return preg_replace("/ [^ =]*'.$allowattributes.'=(\"[^\"]*\"|\'[^\']*\')/i", "", $matches[0]);'   
            ),$string);
        }
        return $string;
    } 
    

    /**
     * strip all html from title
     *
     * @return string stripped content
     * @param string $content unstripped content
     */
    protected function stripAll($content) {
        $filter = new Zend_Filter_StripTags();
        return $filter->filter($content);
    }
    
    
    /**
     * check whether filter matches or not
     *
     * @return boolean true if filter match, false if not
     * @param Zend_Db_Table_Row $feed the current feed
     * @param mixed the current item
     */
    protected function filter($feed, $item) {
        if(strlen(trim($feed->filter))!=0) {
            $resultTitle = @preg_match($feed->filter, $item->getTitle());
            $resultContent = @preg_match($feed->filter, $item->getContent());
            
            // wrong filter
            if(!$resultTitle || !$resultContent) {
                Zend_Registry::get('logger')->log('filter error ' . $feed->filter, Zend_Log::ERR);
                throw new Exception();
            }
            
            // test filter
            if($resultTitle==0 && $resultContent==0)
                return false;
        }
        
        return true;
    }

    
    /**
     * item still in database?
     *
     * @return boolean true if item is already in database
     * @param mixed the current item
     */
    protected function itemExists($item) {
        $itemsModel = new application_models_items();
        
        $res = $itemsModel->fetchAll( 
                $itemsModel->select()
                     ->from($itemsModel, array('amount' => 'Count(*)'))
                     ->where('uid="'.$item->getId().'"')
                );
        if($res[0]['amount']>0) {
            Zend_Registry::get('logger')->log('item "' . $item->getTitle() . '" already fetched', Zend_Log::DEBUG);
            return true;
        }
        
        return false;
    }
}