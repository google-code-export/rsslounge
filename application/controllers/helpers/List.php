<?PHP

/**
 * Helper class for list items (messages and images)
 *
 * @package    application_controllers
 * @subpackage helpers
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Helper_List extends Zend_Controller_Action_Helper_Abstract {


    /**
     * append template vars at given view for
     * rendering the item list
     *
     * @return string|bool errormessage on failure, true on success
     * @param Zend_View $view current view object
     * @param array $settings the current settings
     */
    public function direct($view, $settings) {
        $itemsModel = new application_models_items();
        $settingsModel = new application_models_settings();
        
        // set current search as global var
        Zend_Registry::set('search', $settings['search']);
        
        // validate settings
        if(is_array($settingsModel->validate($settings)))
            return $view->translate('an error occured');
        
        // load messages
        if($settings['view']=='both' || $settings['view']=='messages') {
            $view->messages = $itemsModel->get($settings,'messages');
            $view->moreMessages = $itemsModel->hasMore($settings,'messages');
        }
        
        // load multimedia
        if($settings['view']=='both' || $settings['view']=='multimedia') {
            // set amount of images (which will be loaded)
            if($settings['view']=='both' && count($view->messages)!=0)
                $settings['itemsperpage'] = $settings['imagesHeight'] * Zend_Registry::get('config')->thumbnails->imagesperline;
        
            $view->multimedia = $itemsModel->get($settings,'multimedia');
            $view->moreMultimedia = $itemsModel->hasMore($settings,'multimedia');
        }
        
        return true;
    }

}
