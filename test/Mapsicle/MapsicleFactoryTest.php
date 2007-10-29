<?php
/**
 * Mapsicle - SQL maps for PHP!
 * 
 * PHP versions 4 and 5
 * 
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category    Database
 * @package     Mapsicle
 * @author      Min Huang
 * @copyright   2006 Min Huang
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     1.08 alpha
 * @link        http://www.minformix.org/mapsicle
 * @see         http://ibatis.apache.org
 * @since       File available since Release 1.0
 */

/**
 * Unit tests for the MapsicleFactory class.
 *
 * @category    Database
 * @package     Mapsicle
 * @author      Min Huang
 * @since       Class available since Release 1.0
 */
class MapsicleFactoryTest extends PHPUnit_TestCase {
    /**
     * Construct this test case and set the name.
     *
     * @param string name The name of this test case.
     */
    function MapsicleFactoryTest($name) {
        $this->PHPUnit_TestCase($name);
    }

    /**
     * Test for building a mapsicle object with a valid XML file.
     */
    function testBuildMapsicle() {
        $sqlmap =& MapsicleFactory::buildMapsicle('etc/mapsicle.xml');
        if (PEAR::isError($sqlmap)) {
            $this->fail($sqlmap->getMessage());
        }
    }
}
?>