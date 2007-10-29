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
 * @version     CVS $ID:$
 * @link        http://www.minformix.org/mapsicle
 * @see         http://ibatis.apache.org
 * @since       File available since Release 1.0
 */

/**
 * A simple bean, I mean, object for storing SQL mappings.
 *
 * @category    Database
 * @package     Mapsicle
 * @author      Min Huang
 * @since       Class available since Release 1.0
 */
class MapsicleMapping {
    /**
     * @var string Uniquely identifies the SQL mapping.
     */
    var $id;

    /**
     * @var string The SQL statement associated with this mapping.
     */
    var $sql;

    /**
     * @var string The type of this SQL statement.  Is either
     * 'select', 'update', 'delete', or 'insert'.
     */
    var $type;
    
    /**
     * @var string This is the delimiter used for named parameters.  For
     * example, suppose your SQL statement has the character # inside a string
     * so you don't want to use # as a delimiter.  You can use | or $, or just
     * about any string as a delimiter to get around this limitation.
     */
    var $delimiter;
}
?>