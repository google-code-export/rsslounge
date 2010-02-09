<?PHP

/**
 * Helper class for bayes learning
 *
 * @package    application_controllers
 * @subpackage helpers
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Helper_Bayes extends Zend_Controller_Action_Helper_Abstract {

    /**
     * instance of b8 library
     *
     * @var b8
     */
    protected $b8;

    
    /**
     * prepares the b8 library
     *
     * @return void
     */
    public function __construct() {
        $this->b8 = null;
        
        $mysqlRes = mysql_connect(
            Zend_Registry::get('config')->resources->db->params->host, 
            Zend_Registry::get('config')->resources->db->params->username, 
            Zend_Registry::get('config')->resources->db->params->password);

        if(!$mysqlRes) {
            Zend_Registry::get('logger')->log('b8 error: could not connect to MySQL (' . mysql_error() . ')', Zend_Log::ERR);
            return;
        }
        
        if(!mysql_select_db(Zend_Registry::get('config')->resources->db->params->dbname)) {
            Zend_Registry::get('logger')->log('b8 error: could not select database', Zend_Log::ERR);
            return;
        }

        # Include the b8 code
        require_once Zend_Registry::get('config')->includePaths->library . "/b8/b8.php";

        ob_start();
        
        # Create a new b8 instance and pass the MySQL-link resource to b8
        $this->b8 = new b8(array('mysqlRes' => $mysqlRes, 'tableName' => Zend_Registry::get('config')->resources->db->prefix.'b8wordlist'));

        # Check if everything worked smoothly
        if(!$this->b8->constructed)
            Zend_Registry::get('logger')->log('b8 error: could not construct', Zend_Log::ERR);
            
        $errors = ob_get_contents();
        ob_end_clean();
        if(strlen($errors)>0)
            Zend_Registry::get('logger')->log('b8 error: '.$errors, Zend_Log::ERR);
    }


    /**
     * learn from given params
     *
     * @return void
     * @param mixed $params the params for learning (text, undo, interesting)
     */
    public function learn($params) {
        if($this->b8!=null) {
            
            ob_start();
            
            if($params['undo']) {
                Zend_Registry::get('logger')->log('bayes: unlearn', Zend_Log::DEBUG);
                $this->b8->unlearn(
                    $params['text'], 
                    $params['interesting'] ? 'ham' : 'spam'
                );
            }
            
            Zend_Registry::get('logger')->log('bayes: learn', Zend_Log::DEBUG);
            $this->b8->learn(
                $params['text'], 
                $params['interesting'] ? 'spam' : 'ham'
            );
            
            
            $errors = ob_get_contents();
            ob_end_clean();
            if(strlen($errors)>0)
                Zend_Registry::get('logger')->log('b8 error: '.$errors, Zend_Log::ERR);
        }
    }

    
    /**
     * unlearn from given params
     *
     * @return void
     * @param mixed $params the params for learning (text, undo, interesting)
     */
    public function unlearn($params) {
        if($this->b8!=null) {
            ob_start();
            Zend_Registry::get('logger')->log('bayes: unlearn', Zend_Log::DEBUG);
            $this->b8->unlearn(
                $params['text'], 
                $params['interesting'] ? 'ham' : 'spam'
            );
            
            $errors = ob_get_contents();
            ob_end_clean();
            if(strlen($errors)>0)
                Zend_Registry::get('logger')->log('b8 error: '.$errors, Zend_Log::ERR);
        }
    }
    
    
    /**
     * classify
     *
     * @return void
     * @param string $text classify following thest
     */
    public function classify($text) {
        
        if($this->b8!=null) {
            ob_start();
        
            $result = $this->b8->classify($text);
            
            $errors = ob_get_contents();
            ob_end_clean();
            if(strlen($errors)>0)
                Zend_Registry::get('logger')->log('b8 error: '.$errors, Zend_Log::ERR);
            else
                return $result;
        }
        
        return 0;
    }

}