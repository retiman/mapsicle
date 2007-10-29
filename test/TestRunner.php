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
 * @version     CVS $Id: TestRunner.php,v 1.1 2006/04/21 07:12:50 minhuang Exp $
 * @link        http://www.minformix.org/mapsicle
 * @see         http://ibatis.apache.org
 * @since       File available since Release 1.0
 */
ini_set('include_path', '..' . PATH_SEPARATOR . ini_get('include_path'));
require_once('PHPUnit.php');
require_once('MapsicleTest.php');
require_once('Mapsicle/MapsicleConfigurationTest.php');
require_once('Mapsicle/MapsicleFactoryTest.php');

/**
 * To run these unit tests on your machine, you need:
 * a) PHPUnit installed.
 * b) To run etc/mapsicle.sql against your database to set up the test
 * database entries.
 * c) To give select/insert/update/delete priviledges to the user mapsicle
 * with password mapsicle (or modify the username/password in etc/mapsicle.xml)
 *
 * PHPUnit is used instead of PHPUnit2 because Mapsicle is targeted toward
 * PHP4.x users, who don't have access to PDO (PHP Data Objects) or other
 * PHP5 persistence solutions.
 *
 * @category    Database
 * @package     Mapsicle
 * @author      Min Huang
 * @since       Class available since Release 1.0
 */

/**
 * Run the test suite for MapsicleTest.
 */
$suite  = new PHPUnit_TestSuite('MapsicleTest');
$result = PHPUnit::run($suite);
print($result->toString());

/**
 * Run the test suite for MapsicleConfigurationTest.
 */
$suite = new PHPUnit_TestSuite('MapsicleConfigurationTest');
$result = PHPUnit::run($suite);
print($result->toString());

/**
 * Run the test suite for MapsicleFactoryTest.
 */
$suite = new PHPUnit_TestSuite('MapsicleFactoryTest');
$result = PHPUnit::run($suite);
print($result->toString());
?>