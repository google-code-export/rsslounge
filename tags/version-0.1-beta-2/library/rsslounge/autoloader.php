<?PHP

/**
 * Special autoloader for extern libraries (currently only wideimage)
 *
 * @package    library
 * @subpackage rsslounge
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class rsslounge_autoloader implements Zend_Loader_Autoloader_Interface {
    
    /**
     * special autoloader for extern libraries
     *
     * @return void
     * @param string $class current classname
     */
    public function autoload($class) {
        if($class=='wiImage')
            require_once(Zend_Registry::get('config')->includePaths->library . '/wideimage/Image.class.php');
        if($class=='wiTrueColorImage')
            require_once(Zend_Registry::get('config')->includePaths->library . '/wideimage/TrueColorImage.class.php');
        if($class=='wiPaletteImage')
            require_once(Zend_Registry::get('config')->includePaths->library . '/wideimage/PaletteImage.class.php');
        if($class=='wiException')
            require_once(Zend_Registry::get('config')->includePaths->library . '/wideimage/Exception.class.php');
        if($class=='wiDimension')
            require_once(Zend_Registry::get('config')->includePaths->library . '/wideimage/Dimension.class.php');
        if($class=='wiOpFactory')
            require_once(Zend_Registry::get('config')->includePaths->library . '/wideimage/OpFactory.class.php');
        if($class=='wiFileMapperFactory')
            require_once(Zend_Registry::get('config')->includePaths->library . '/wideimage/FileMapperFactory.class.php');
    }
    
}