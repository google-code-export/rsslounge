<?PHP

/**
 * Helper class for fetching and saving an icon
 *
 * @package    application_controllers
 * @subpackage helpers
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Helper_Icon extends Zend_Controller_Action_Helper_Abstract {

    /**
     * loads icon using given url and stores it in a given path
     *
     * -> first search on given url (for <link rel="... tag)
     * -> then on domain url (for <link rel="... tag)
     * -> then favicon.ico file
     *
     * @return string|bool the filename of the new generated file, false if no icon was found
     * @param string $url source url
     * @param string $path target path
     */
    public function load($url, $path) {
        // search on given url
        $result = $this->searchAndDownloadIcon($url, $path);
        if($result!==false)
            return $result;
            
        // search on base page for <link rel="shortcut icon" url...
        $url = parse_url($url);
        $url = $url['scheme'] . '://'.$url['host'] . '/';
        $result = $this->searchAndDownloadIcon($url, $path);
        if($result!==false)
            return $result;
        
        // search domain/favicon.ico
        if(@file_get_contents($url . 'favicon.ico')!==false)
            return $this->loadIconFile($url . 'favicon.ico', $path);
        
        return false;
    }
    
    
    /**
     * downloads an icon file from given url in given path
     *
     * @return string|bool filename of the new icon, false on failure
     * @param string $url the url of the icon
     * @param string $path the target path
     */    
    public function loadIconFile($url, $path) {
        // read filetype
        $type = substr($url, strrpos($url, '.')+1);
        
        // target filename
        $target = md5($url) . '.' . $type;
        
        // get icon from source
        $data = @file_get_contents($url);
        if($data===false)
            return $data;
        
        // html text (e.g. error page) delivered
        if(strpos($data, '<html')!==false)
            return false;
        
        // empty file
        if(strlen($data)==0)
            return false;
        
        // write icon in file
        $fp = fopen($path . $target,"wb");
        $count = fwrite($fp,$data);
        fclose($fp);
        
        return $target;
    }
    
    
    /**
     * loads an html file and search for <link rel="shortcut icon"
     * on success: download
     *
     * @return string|bool filename on succes, false on failure
     * @param string $url source url
     * @param string $path target path
     */
    protected function searchAndDownloadIcon($url, $path) {
        $icon = $this->getLinkTag(
            $this->loadHtml($url)
        );
        
        // icon found: download it
        if($icon!==false) {
            // add http
            if(strpos($icon, 'http://') !== 0)
                $icon = $url . $icon;
            
            // download icon
            return $this->loadIconFile($icon, $path);
        }
        
        return false;
    }
    
    
    /**
     * loads html page of given url
     *
     * @return string content as string
     * @param string $url the source url
     */
    protected function loadHtml($url) {
        try {
            $client = new Zend_Http_Client($url);
            $response = $client->request();  
            return $response->getBody();
        } catch(Exception $e) {
            return false;
        }
    }
    
    
    /**
     * searches the first link tag in html page
     * with rel="shortcut icon" tag
     *
     * @return string icon href as string
     * @param string $content of the html page
     */
    protected function getLinkTag($content) {
        if($content===false)
            return false;
        try {
            $dom = new Zend_Dom_Query($content);  
            //$linkTags = $dom->query('link[rel="shortcut icon"]'); // don't work
            $linkTags = $dom->query('link');
            foreach($linkTags as $link) {
                if($link->getAttribute('rel') == 'shortcut icon')
                    return $linkTags->current()->getAttribute("href");    
            }
        } catch(Exception $e) {
        
        }
        return false;
    }

}
