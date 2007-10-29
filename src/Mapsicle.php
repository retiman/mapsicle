<?php
/**
 * Mapsicle - SQL maps for PHP!
 *
 * Mapsicle was inspired by iBATIS Data Mapper (http://ibatis.apache.org), but
 * is not intended to be an exact port.  Mapsicle has less features than
 * iBATIS, but hopefully you will still find it useful.
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
 * @version     CVS $Id: Mapsicle.php,v 1.4 2006/04/28 07:16:35 minhuang Exp $
 * @link        http://www.minformix.org/mapsicle
 * @see         http://ibatis.apache.org
 * @since       File available since Release 1.0
 */
require_once('PEAR.php');
require_once('DB.php');
require_once('Config.php');
require_once('Mapsicle/MapsicleMapping.php');
require_once('Mapsicle/MapsicleConfiguration.php');
require_once('Mapsicle/MapsicleFactory.php');

/**
 * The default delimiter for use in SQL queries in your Mapsicle 
 * configuration file.
 */
define('MAPSICLE_DEFAULT_DELIMITER', '#');

/**
 * The default filename (if none specified) for the Mapsicle configuration
 * file.
 */
define('MAPSICLE_CONFIG_RESOURCE', 'mapsicle.xml');

/**
 * A simple SQL mapper for PHP.  You can define SQL statements and map them to
 * an id in a configuration file called 'mapsicle.xml' (or name it whatever
 * you want, and pass it in to MapsicleFactory::createMapsicle).
 *
 * For example, suppose you had a simple query:
 * <code>
 * select * from customers where customer_name = ?
 * </code>
 *
 * Simply define this query in your mapsicle.cfg.xml as:
 * <code>
 * <select id="Customers.byName">
 *        select cust_name         as name,
 *            cust_addr            as address,
 *            cust_city            as city,
 *            cust_state           as state,
 *            cust_zip             as zip,
 *            cust_email           as email
 *        from customers where customer_name = #name#
 * </select>
 * </code>
 *
 * In this case, #name# is a named parameter; you will call one of Mapsicle's
 * queryFor methods and pass in both the id and parameters.  For example:
 * <code>
 * $mapsicle =& MapsicleFactory::buildMapsicle();
 * if (PEAR::isError($mapsicle)) {
 *        die($mapsicle->getMessage());
 * }
 * $params = array('name' => 'John Doe');
 * $customer =& $mapsicle->queryForObject('Customer.byName', $params);
 * print $customer->email;
 * </code>
 *
 * @author      Min Huang
 * @package     Mapsicle
 * @since       Class available since Release 1.0
 */
class Mapsicle {
    /**
     * @var array An array of MapsicleMapping objects.
     */
    var $mappings = null;

    /**
     * @var mixed The database connection.  Mapsicle uses PEAR::DB for
     * database connectivity.
     */
    var $db = null;

    /**
     * @var string This is the last query executed, in case you want to use
     * it for debugging.
     */
    var $sql = null;

    /**
     * Initialize the Mapsicle with a database and mappings.
     *
     * @param mixed db A PEAR_DB object.
     * @param array mappings A hashmap of id to MapsicleMapping objects.
     */
    function Mapsicle($db, $mappings) {
        $this->db =& $db;
        $this->mappings =& $mappings;
    }

    /**
     * This function is an alias of MapsicleFactory::createMapsicle.
     *
     * @param string configFile The location of the Mapsicle configuration
     * file.
     */
    function create($configFile = null) {
        return MapsicleFactory::createMapsicle($configFile);
    }

    /**
     * Returns the last query executed (with named parameters replaced) in
     * case you want to use it for debugging purposes.
     *
     * @return string The last executed query.
     */
    function getLastQuery() {
        return $this->sql;
    }
    
    /**
     * Don't execute any SQL query, but return the mapped query with named
     * parameters replaced instead.
     *
     * @param string id The ID of the mapping.
     * @param mixed parameters The query parameters.
     * @return mixed The mapped query with named parameters replaced, or
     * PEAR_Error if the query wasn't found.
     */
    function getQuery($id, $parameters = null) {
        if ( !isset($this->mappings[$id]) ) {
            return new PEAR_Error('Mapping ' . $id . ' is not defined.');
        }
        if ( $this->mappings[$id]->type != 'select' ) {
            return new PEAR_Error('queryForList is only for select queries.');
        }

        // Find the mapping and execute the query
        $sql =& $this->replaceParams($this->mappings[$id], 
                $parameters);
        return $sql;
    }

    /**
     * Execute the SQL query mapped by the argument id with the given
     * parameters.  If there are no parameters, you may leave them null.
     *
     * Only one object will be returned, even if there are multiple objects!
     * 
     * The parameters may either by a hashmap of parameter name to parameter
     * values, or an object with the parameters as variable names.  All
     * parameters must be strings.
     *
     * @param string id The ID of the mapping.
     * @param mixed parameters The query parameters.
     * @return mixed A DB_row object if one was found, null if no object was 
     * found, and PEAR_Error if an error occured while trying to execute the 
     * query.
     */
    function queryForObject($id, $parameters = null) {
        $list =& $this->queryForList($id, $parameters);
        if (PEAR::isError($list)) {
            return $list;
        }

        // Get the first element from the list, if it exists
        if (count($list) >= 1) {
            return $list[0];
        } else {
            return null;
        }
    }

    /**
     * Execute the SQL query mapped by the argument id with the given
     * parameters.  If there are no parameters, you may leave them null.
     *
     * Returns an array of DB_row objects if any are found by the query; 
     * otherwise, an empty array is returned.
     *
     * The parameters may either by a hashmap of parameter name to parameter
     * values, or an object with the parameters as variable names.  All
     * parameters must be strings.
     *
     * @param string id The ID of the mapping.
     * @param mixed parameters The query parameters.
     * @return mixed An array of DB_row objects, or PEAR_Error if an error 
     * occured  while trying to execute the query.
     */
    function queryForList($id, $parameters = null) {
        // Check if this mapping is defined first and that it's for a select
        if ( !isset($this->mappings[$id]) ) {
            return new PEAR_Error('Mapping ' . $id . ' is not defined.');
        }
        if ( $this->mappings[$id]->type != 'select' ) {
            return new PEAR_Error('queryForList is only for select queries.');
        }

        // Find the mapping and execute the query
        $this->sql =& $this->replaceParams($this->mappings[$id], 
                $parameters);
        $this->db->setFetchMode(DB_FETCHMODE_OBJECT, 'DB_row');
        $rs =& $this->db->query($this->sql);
        if (PEAR::isError($rs)) {
            $err = 'Error executing SQL: ' . $this->sql . '.  Got DB Error: ' 
                    . $rs->getMessage();
            return new PEAR_Error($err);
        }

        // Return a map of the results
        $map = array();
        while ($rs->fetchInto($row)) {
            $map[] = $row;
        }
        return $map;
    }

    /**
     * Execute the SQL query mapped by the argument id with the given
     * parameters.  If there are no parameters, you may leave them null.
     *
     * Returns an array of hashmaps, with each field in the result row mapped
     * to that field's value in the row.
     *
     * The parameters may either by a hashmap of parameter name to parameter
     * values, or an object with the parameters as variable names.  All
     * parameters must be strings.
     *
     * @param string id The ID of the mapping.
     * @param mixed parameters The query parameters.
     * @return mixed An array of hashmaps, or PEAR_Error if an error occured 
     * while trying to execute the query.
     */
    function queryForMap($id, $parameters = null) {
        // Check if this mapping is defined first and that it's for a select
        if ( !isset($this->mappings[$id]) ) {
            return new PEAR_Error('Mapping ' . $id . ' is not defined.');
        }
        if ( $this->mappings[$id]->type != 'select' ) {
            return new PEAR_Error('queryForMap is only for select queries.');
        }

        // Find the mapping and execute the query
        $this->sql =& $this->replaceParams($this->mappings[$id], 
                $parameters);
        $this->db->setFetchMode(DB_FETCHMODE_ASSOC);
        $rs =& $this->db->query($this->sql);
        if (PEAR::isError($rs)) {
            $err = 'Error executing SQL: ' . $this->sql . '.  Got DB Error: '
                    . $rs->getMessage();
            return new PEAR_Error($err);
        }

        // Return a map of the results
        $map = array();
        while ($rs->fetchInto($row)) {
            $map[] = $row;
        }
        return $map;
    }

    /**
     * Execute an insert statement.  You will get back the last insert id if
     * your database supports it.  If you made the insert and specified the
     * primary key directly, please note that you won't get the correct last
     * insert id back.
     *
     * The parameters may either by a hashmap of parameter name to parameter
     * values, or an object with the parameters as variable names.  All
     * parameters must be strings.
     *
     * @param string id The ID of the mapping.
     * @param mixed parameters The query parameters.
     * @return mixed The last insert id if supported or null if not, or 
     * PEAR_Error if an error occured  while trying to execute the query.
     */
    function insert($id, $parameters = null) {
        // Check if this mapping is defined first and that it's for an insert
        if ( !isset($this->mappings[$id]) ) {
            return new PEAR_Error('Mapping ' . $id . ' is not defined.');
        }
        if ( $this->mappings[$id]->type != 'insert' ) {
            return new PEAR_Error('insert is only for insert queries.');
        }

        // Find the mapping and execute the query
        $this->sql =& $this->replaceParams($this->mappings[$id], 
                $parameters);
        $rs =& $this->db->query($this->sql);
        if (PEAR::isError($rs)) {
            $err = 'Error executing SQL: . ' . $this->sql . '.  Got DB Error: '
                    . $rs->getMessage();
            return new PEAR_Error($err);
        }

        // Get the last insert id (if supported); don't save as last executed
        // query.
        $sql = "select last_insert_id() as id";
        $rs =& $this->db->query($sql);
        if (PEAR::isError($rs)) {
            // Last insert id not supported
            return null;
        }
        $rs->fetchInto($row);
        settype($row[0], 'integer');
        return $row[0];
    }

    /**
     * Execute an update statement.
     *
     * The parameters may either by a hashmap of parameter name to parameter
     * values, or an object with the parameters as variable names.  All
     * parameters must be strings.
     *
     * @param string id The ID of the mapping.
     * @param mixed parameters The query parameters.
     * @return mixed true if the operation was successful, or or PEAR_Error if
     * an error occured  while trying to execute the query.
     */
    function update($id, $parameters = null) {
        // Check if this mapping is defined first and that it's for an update
        if ( !isset($this->mappings[$id]) ) {
            return new PEAR_Error('Mapping ' . $id . ' is not defined.');
        }
        if ( $this->mappings[$id]->type != 'update' ) {
            return new PEAR_Error('update is only for update queries.');
        }

        // Find the mapping and execute the query
        $this->sql =& $this->replaceParams($this->mappings[$id], 
                $parameters);
        $rs =& $this->db->query($this->sql);
        if (PEAR::isError($rs)) {
            $err = 'Error executing SQL: ' . $this->sql . '.  Got DB Error: '
                    . $rs->getMessage();
            return new PEAR_Error($err);
        }

        // Return affected rows
        $rows = $this->db->affectedRows();
        return true;
    }

    /**
     * Execute a delete statement.
     *
     * The parameters may either by a hashmap of parameter name to parameter
     * values, or an object with the parameters as variable names.  All
     * parameters must be strings.
     *
     * @param string id The ID of the mapping.
     * @param mixed parameters The query parameters.
     * @return mixed Returns true if the operation was successful, or or 
     * PEAR_Error if an error occured  while trying to execute the query.
     */
    function delete($id, $parameters = null) {
        // Check if this mapping is defined first and that it's for an update
        if ( !isset($this->mappings[$id]) ) {
            return new PEAR_Error('Mapping ' . $id . ' is not defined.');
        }
        if ( $this->mappings[$id]->type != 'delete' ) {
            return new PEAR_Error('delete is only for delete queries.');
        }

        // Find the mapping and execute the query
        $this->sql =& $this->replaceParams($this->mappings[$id], 
                $parameters);
        $rs =& $this->db->query($this->sql);
        if (PEAR::isError($rs)) {
            $err = 'Error executing SQL: ' . $this->sql . '.  Got DB Error: '
                    . $rs->getMessage();
            return new PEAR_Error($err);
        }
        return true;
    }

    /**
     * Execute a SQL statement.  You may use this for update or delete
     * statements if you wish, but it is convenient to use this method
     * for executing SQL statements such as <code>ALTER TABLE ...</code> 
     * or <code>CREATE TABLE</code>, etc.
     *
     * The parameters may either by a hashmap of parameter name to parameter
     * values, or an object with the parameters as variable names.  All
     * parameters must be strings.
     *
     * @param string id The ID of the mapping.
     * @param mixed parameters The query parameters.
     * @return mixed Returns true if the operation was successful, or or 
     * PEAR_Error if an error occured  while trying to execute the query.
     */
    function execute($id, $parameters = null) {
        // Check if this mapping is defined first and that it's for an update
        if ( !isset($this->mappings[$id]) ) {
            return new PEAR_Error('Mapping ' . $id . ' is not defined.');
        }
        if ( $this->mappings[$id]->type != 'query' ) {
            return new PEAR_Error('execute is only for query queries.');
        }

        // Find the mapping and execute the query
        $this->sql =& $this->replaceParams($this->mappings[$id], 
                $parameters);
        $rs =& $this->db->query($this->sql);
        if (PEAR::isError($rs)) {
            $err = 'Error executing SQL: ' . $this->sql . '.  Got DB Error: '
                    . $rs->getMessage();
            return new PEAR_Error($err);
        }
        return true;
    }

    /**
     * Replaces named parameters in a mapped SQL query.
     *
     * Because PHP does not have support for overloaded methods, here's our
     * hack of a solution to call the right method depending on the type of the
     * argument.
     *
     * @param mixed mapping The MapsicleMapping object that contains the SQL
     * query.
     * @param mixed parameters The query parameters.
     * @return mixed A SQL query with the arguments replaced and ready to be 
     * executed against the database, or a PEAR_Error if something went wrong 
     * while trying to replace parameters.
     */
    function replaceParams($mapping, $parameters) {
        // Obviously, if no parameters, then don't do any processing
        if ( is_null($parameters) ) {
            return $mapping->sql;
        }

        // But do something different depending on whether the parameters come
        // as an array or an object
        $sql = $mapping->sql;

        if ( is_array($parameters) ) {
            foreach ($parameters as $name => $value) {
                $search = $mapping->delimiter . $name . $mapping->delimiter;
                $replace = '\'' . addslashes($value) . '\'';
                $sql = str_replace($search, $replace, $sql);
            }
            return $sql;
        }

        if ( is_object($parameters) ) {
            $sql = $mapping->sql;
            foreach (get_object_vars($parameters) as $name => $value) {
                $search = $mapping->delimiter . $name . $mapping->delimiter;
                $replace = '\'' . addslashes($parameters->$name) . '\'';
                $sql = str_replace($search, $replace, $sql);
            }
            return $sql;
        }
    }
}
?>