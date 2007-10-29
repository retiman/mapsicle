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
 * @version     CVS $Id: MapsicleTest.php,v 1.2 2006/04/26 02:30:47 minhuang Exp $
 * @link        http://www.minformix.org/mapsicle
 * @see         http://ibatis.apache.org
 * @since       File available since Release 1.0
 */
require_once('PHPUnit.php'); 
require_once('Mapsicle.php');
require_once('Customer.php');

/**
 * Configuration files for MapsicleTest testing.
 */
define('MAPSICLE_TEST_CONFIG', 'etc/mapsicle.xml');

/**
 * Unit tests for the Mapsicle class.
 *
 * @category    Database
 * @package     Mapsicle
 * @author      Min Huang
 * @since       Class available since Release 1.0
 */
class MapsicleTest extends PHPUnit_TestCase {
    /**
     * @var mixed A mapsicle test fixture.
     */
    var $sqlmap = null;

    /**
     * @var mixed A PEAR::DB object that can be used to verify that whatever you did with
     * Mapsicle actually made any changes to the database :)
     */
    var $db = null;

    /**
     * Construct this test case and set the name.
     *
     * @param string name The name of this test case.
     */
    function MapsicleTest($name) {
        $this->PHPUnit_TestCase($name);
    }

    /**
     * Setup the test fixtures.  Create a Mapsicle object from the test
     * configuration file.
     */
    function setUp() {
        $this->sqlmap =& MapsicleFactory::buildMapsicle(MAPSICLE_TEST_CONFIG);
        $this->db =& $this->sqlmap->db;
    }

    /**
     * Tear down the test fixtures.
     */
    function tearDown() {
        $this->sqlmap = null;
    }

    /**
     * Test Mapsicle::insert by using an array as parameters.
     */
    function testInsert1() {
        $params = array(
            'id' => 3,
            'first' => 'Jane',
            'last' => 'Doe',
            'email' => 'jane@doe.com'
        );
        $id =& $this->sqlmap->insert('Customer.create', $params);
        if (PEAR::isError($id)) {
            $this->fail($id->getMessage());
        }

        // Check if data is in the database
        $sql = 'select * from customer where id = 3';
        $rs =& $this->db->query($sql);
        if (PEAR::isError($rs)) {
            $this->fail($rs->getMessage());
        }
        while ($rs->fetchInto($row)) {
            $this->assertEquals($row[0], '3');
            $this->assertEquals($row[1], 'Jane');
            $this->assertEquals($row[2], 'Doe');
            $this->assertEquals($row[3], 'jane@doe.com');
            break;
        }

        // Delete from data store
        $sql = 'delete from customer where id = 3';
        $rs =& $this->db->query($sql);
        if (PEAR::isError($rs)) {
            $this->fail($rs->getMessage());
        }
    }

    /**
     * Test Mapsicle::insert by using an object as parameters.
     */
    function testInsert2() {
        $params = new Customer();
        $params->id = 3;
        $params->first = 'Jane';
        $params->last = 'Doe';
        $params->email = 'jane@doe.com';
        $id =& $this->sqlmap->insert('Customer.create', $params);
        if (PEAR::isError($id)) {
            $this->fail($id->getMessage());
        }

        // Check if data is in the database
        $sql = 'select * from customer where id = 3';
        $rs =& $this->db->query($sql);
        if (PEAR::isError($rs)) {
            $this->fail($rs->getMessage());
        }
        while ($rs->fetchInto($row)) {
            $this->assertEquals($row[0], '3');
            $this->assertEquals($row[1], 'Jane');
            $this->assertEquals($row[2], 'Doe');
            $this->assertEquals($row[3], 'jane@doe.com');
            break;
        }

        // Delete from data store
        $sql = 'delete from customer where id = 3';
        $rs =& $this->db->query($sql);
        if (PEAR::isError($rs)) {
            $this->fail($rs->getMessage());
        }
    }

    /**
     * Test Mapsicle::delete by using array parameters.
     */
    function testDelete1() {
        $sql = 'insert into customer values(\'3\', \'James\', \'Doe\', '
                . '\'james@doe.com\')';
        $rs =& $this->db->query($sql);
        if (PEAR::isError($rs)) {
            $this->fail($rs->getMessage());
        }

        $params = array('id' => 3);
        $this->sqlmap->delete('Customer.removeById', $params);
    }

    /**
     * Test Mapsicle::delete by using object parameters.
     */
    function testDelete2() {
        $sql = 'insert into customer values(\'3\', \'James\', \'Doe\', '
                . '\'james@doe.com\')';
        $rs =& $this->db->query($sql);
        if (PEAR::isError($rs)) {
            $this->fail($rs->getMessage());
        }

        $params = new Customer();
        $params->id = 3;
        $this->sqlmap->delete('Customer.removeById', $params);
    }

    /**
     * Test Mapsicle::queryForObject by using array parameters.
     */
    function testQueryForObject1() {
        $params = array('id' => 1);
        $customer =& $this->sqlmap->queryForObject('Customer.findById', 
                $params);
        if (PEAR::isError($customer)) {
            $this->fail($rs->getMessage());
        }

        $this->assertEquals($customer->id, '1');
        $this->assertEquals($customer->first, 'John');
        $this->assertEquals($customer->last, 'Doe');
        $this->assertEquals($customer->email, 'john@doe.com');
    }

    /**
     * Test Mapsicle::queryForObject using object parameters.
     */
    function testQueryForObject2() {
        $params = new Customer();
        $params->id = 1;
        $customer =& $this->sqlmap->queryForObject('Customer.findById', 
                $params);
        if (PEAR::isError($customer)) {
            $this->fail($rs->getMessage());
        }

        $this->assertEquals($customer->id, '1');
        $this->assertEquals($customer->first, 'John');
        $this->assertEquals($customer->last, 'Doe');
        $this->assertEquals($customer->email, 'john@doe.com');
    }

    /**
     * Test Mapsicle::queryForList.
     */
    function testQueryForList1() {
        $list =& $this->sqlmap->queryForList('Customer.findAll');
        if (PEAR::isError($list)) {
            $this->fail($list->getMessage());
        }

        $this->assertEquals(count($list), 2);
        $this->assertEquals($list[0]->id, '1');
        $this->assertEquals($list[0]->first, 'John');
        $this->assertEquals($list[0]->last, 'Doe');
        $this->assertEquals($list[0]->email, 'john@doe.com');
        $this->assertEquals($list[1]->id, '2');
        $this->assertEquals($list[1]->first, 'Jean');
        $this->assertEquals($list[1]->last, 'Doe');
        $this->assertEquals($list[1]->email, 'jean@doe.com');
    }

    /**
     * Test Mapsicle::queryForMap.
     */
    function testQueryForMap1() {
        $list =& $this->sqlmap->queryForMap('Customer.findAll');
        if (PEAR::isError($list)) {
            $this->fail($list->getMessage());
        }

        $this->assertEquals(count($list), 2);
        $this->assertEquals($list[0]['id'], '1');
        $this->assertEquals($list[0]['first'], 'John');
        $this->assertEquals($list[0]['last'], 'Doe');
        $this->assertEquals($list[0]['email'], 'john@doe.com');
        $this->assertEquals($list[1]['id'], '2');
        $this->assertEquals($list[1]['first'], 'Jean');
        $this->assertEquals($list[1]['last'], 'Doe');
        $this->assertEquals($list[1]['email'], 'jean@doe.com');
    }

    /**
     * Test Mapsicle::execute.
     */
    function testExecute1() {
        $rs =& $this->sqlmap->execute('Test.createTable');
        if (PEAR::isError($rs)) {
            $this->fail($rs->getMessage());
        }

        $rs =& $this->sqlmap->execute('Test.dropTable');
        if (PEAR::isError($rs)) {
            $this->fail($rs->getMessage());
        }
    }
}
?>