<?PHP

/**
 * UnitTest for the base settings and bootstrap process
 *
 * @package    tests_settings
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class BootstrapTest extends PHPUnit_Framework_TestCase {
    
    /**
     * tests the framework
     */
    public function testFramework() {
        $this->assertEquals('1.9.2', Zend_Version::VERSION);
    }
    
}