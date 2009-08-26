<?PHP

/**
 * Model for accessing and edit the items
 *
 * @package    application_models
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class application_models_items extends application_models_base {

    /**
     * set up the table name
     *
     * @return void
     */
    protected function _setupTableName() {
        $this->_name = Zend_Registry::get('config')->resources->db->prefix . 'items';
        parent::_setupTableName();
    }
    
    
    /**
     * set up metadata as other reference table objects
     * and dependend table objects
     *
     * @return void
     */
    protected function _setupMetadata() {
        $this->_referenceMap = array(
            'feeds' => array(
                        'columns'       => 'feed',
                        'refTableClass' => 'application_models_feeds',
                        'refColumn'     => 'id'
                        )
        );
        parent::_setupMetadata();
    }

    
    /**
     * get items for listing on mainpage
     *
     * @return array of items (items in array themself 
     * arrays, not row objects)
     * @param array $settings array of filter etc.
     * @param string $type of media (messages or multimedia)
     */
    public function get($settings, $type) {
        // prepare select
        $select = $this->prepareSelect($settings, $type);
        
        // set offset (if given)
        if($settings['offset'])
            $select->limit($settings['itemsperpage'], $settings['offset']);
        else
            $select->limit($settings['itemsperpage']);
        
        // set order
        $select->order('datetime DESC');
        
        // execute search
        return $this->getAdapter()->fetchAll($select);
    }

    
    /**
     * checks whether more items available
     *
     * @return bool true if mor items available
     * @param array $settings current settings
     * @param string $type of media (messages or multimedia)
     */
    public function hasMore($settings, $type) {
        // prepare select
        $select = $this->prepareSelect($settings, $type);
        
        // remove limit
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::COLUMNS);
        
        // set count query
        $select->columns('count(*)');
        
        // index of first element of the next page
        $nextPage = $settings['offset'] + Zend_Registry::get('session')->itemsperpage;
        
        // less elements than needed for a new page?
        if($this->getAdapter()->fetchOne($select) <= $nextPage)
            return false;
        else
            return true;
    }
    
    
    /**
     * count items of every category
     *
     * @return array of category id (key) and count value
     * @param array $settings as array
     */
    public function countPerCategory($settings) {
        // count items
        $categories = $this->count($settings, 'c');
        
        // get all unread items
        
        // prepare select
        $select = $this->prepareSelect($settings, $settings['view']);
        
        // set count statement
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->columns('count(i.id)');
        $categories[0] = $this->getAdapter()->fetchOne($select);
        
        return $categories;
    }
    
    
    /**
     * count items of every feed
     *
     * @return array of feed id (key) and count value
     * @param array $settings as array
     */
    public function countPerFeed($settings) {
        return $this->count($settings, 'f');
    }
    
    
    /**
     * count all unread items
     *
     * @return int all unread items
     * @param array $settings as array
     */
    public function countAll($settings) {
        // prepare select
        $select = $this->prepareSelect($settings, $settings['view']);
        
        // set count statement
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->columns('count(*)');
        return $this->getAdapter()->fetchOne($select);
    }
    
    
    /**
     * count items
     *
     * @return array of id and count value
     * @param array $settings current settings
     * @param string $table the name of the table in the prepared table
     */
    private function count($settings, $table) {
        // prepare select
        $select = $this->prepareSelect($settings, $settings['view']);
        
        // set count statement
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->columns(array( $table.'.id', 'count(i.id)'))->group($table.'.id');
        
        // convert into id => count array
        $return = array();
        foreach($this->getAdapter()->fetchAll($select) as $res)
            $return[$res['id']] = $res['count(i.id)'];
            
        return $return;
    }
    
    
    /**
     * count starred
     *
     * @return int amount of starred items
     * @param array $settings current settings
     */
    public function countStarred($settings) {
        // prepare select
        $select = $this->prepareSelect($settings, $settings['view']);
        
        // set count statement
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->where('i.starred=1');
        $select->columns('count(i.id)');
        
        return $this->getAdapter()->fetchOne($select);
    }
    
    
    /**
     * cleanup all orphaned thumbnails
     *
     * @return void
     */
    public function cleanup() {
        // scan all thumbnails
        foreach(scandir(Zend_Registry::get('config')->thumbnails->path) as $file) {
            if(is_file(Zend_Registry::get('config')->thumbnails->path . '/' . $file)) {
                
                // search whether any link to this file exists
                $select = $this->select()->from($this, 'count(*)')
                                         ->where($this->getAdapter()->quoteInto('content=?', $file));
                $count = $this->getAdapter()->fetchOne($select);
                
                // no link => delete
                if($count==0)
                    unlink(Zend_Registry::get('config')->thumbnails->path . '/' . $file);
            }
        }
    }
    
    
    /**
     * prepares select statement for getting items
     *
     * @return Zend_Db_Select the prepared select statement
     * @param array $settings current settings
     * @param string $type (optional) type of media (messages or multimedia)
     */
    protected function prepareSelect($settings, $type = 'both') {
        // read prefix
        $p = Zend_Registry::get('config')->resources->db->prefix;
        
        // get database adapter
        $db = $this->getAdapter();
        
        // base select statement
        $select = $db->select()
                     ->from( array( 'i' => $p.'items' ), array('id','title','content','unread','starred','datetime','link') )
                     ->join( array( 'f' => $p.'feeds' ), 'i.feed = f.id', array('name','icon') )
                     ->join( array( 'c' => $p.'categories' ), 'c.id=f.category', array());
        
        // only multimedia content
        if($type=='multimedia')
            $select->where('f.multimedia=1');
            
        // only messages
        elseif($type=='messages')
            $select->where('f.multimedia=0');

        // category
        if(strlen($settings['selected'])>4 && substr($settings['selected'],0,4)=='cat_')
            $select->where( $db->quoteInto('c.id=?',substr($settings['selected'],4)) );
        
        // feed
        if(strlen($settings['selected'])>5 && substr($settings['selected'],0,5)=='feed_')
            $select->where( $db->quoteInto('f.id=?',substr($settings['selected'],5)) );
        
        // unread
        if($settings['unread']!=0)
            $select->where('i.unread=1');
        
        // starred
        if($settings['starred']!=0)
            $select->where('i.starred=1');
        
        // priority
        $select->where( $db->quoteInto('f.priority>=?', $settings['currentPriorityStart']) );
        $select->where( $db->quoteInto('f.priority<=?', $settings['currentPriorityEnd']) );

        // date
        if($settings['dateFilter']==1) {
            $select->where( $db->quoteInto('i.datetime>=?', $settings['dateStart'] . ' 00:00:00') );
            $select->where( $db->quoteInto('i.datetime<=?', $settings['dateEnd'] . ' 23:59:59') );
        }
        
        // search
        if(strlen(trim($settings['search']))) {
            $select->where( 
                'i.title LIKE ' . $db->quote("%".$settings['search']."%")
                . ' OR i.content LIKE ' . $db->quote("%".$settings['search']."%")
            );
        }
        
        return $select;
    }
}


?>