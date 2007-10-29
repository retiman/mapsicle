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
 * Configuration files for MapsicleConfigurationTest testing.
 */
define('MAPSICLE_TEST_CONFIG_DELIM', 'etc/mapsicle.xml');

/**
 * Unit tests for the MapsicleConfiguration class.
 *
 * @category    Database
 * @package     Mapsicle
 * @author      Min Huang
 * @since       Class available since Release 1.0
 */
class MapsicleConfigurationTest extends PHPUnit_TestCase {
    /**
     * Construct this test case and set the name.
     *
     * @param string name The name of this test case.
     */
    function MapsicleConfigurationTest($name) {
        $this->PHPUnit_TestCase($name);
    }

    /**
     * Test if the correct mapping is created with a valid array
     * (with a specified delimiter).
     */
    function testGetMapping1() {
        $id = 'HelloWorldMapID';
        $delimiter = '&';
        $sql = 'select * from customer where id = #id#';
        $type = 'select';

        $element = array(
            '@' => array(
                'id' => $id,
                'delimiter' => $delimiter
            ),
            '#' => $sql
        );
        $mapping =& MapsicleConfiguration::getMapping($element, $type);

        $this->assertEquals($mapping->id, $id);
        $this->assertEquals($mapping->delimiter, $delimiter);
        $this->assertEquals($mapping->sql, $sql);
        $this->assertEquals($mapping->type, $type);
    }

    /**
     * Test if the correct mapping is created with without specifying a
     * delimiter (the delimiter should be #).  And with a different delimiter
     * (!).
     */
    function testDefaultDelimiter1() {
        $config = 'etc/mapsicle.default.delimiter.xml';
        $sqlmap =& MapsicleFactory::buildMapsicle($config);
        if (PEAR::isError($sqlmap)) {
            $this->fail($sqlmap->getMessage());
        }

        $mapping =& $sqlmap->mappings['Customer.findById#'];
        $this->assertEquals($mapping->delimiter, '#');
        $mapping =& $sqlmap->mappings['Customer.findById!'];
        $this->assertEquals($mapping->delimiter, '!');
    }
}
?>