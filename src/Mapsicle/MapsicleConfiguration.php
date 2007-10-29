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
 * A class for configuring a Mapsicle object from an XML configuration
 * file.
 *
 * @category    Database
 * @package     Mapsicle
 * @author      Min Huang
 * @since       File available since Release 1.0
 */
class MapsicleConfiguration {
    /**
     * @var array The root element of the configuration file.
     */
    var $root = null;

    /**
     * @var array An array of MapsicleMapping objects.
     */
    var $mappings = null;

    /**
     * @var mixed The database connection.
     */
    var $db = null;

    /**
     * Configure the SQL map class with the file given.  If you don't specify
     * a file, it will assume that SQLMAP_CONFIG_RESOURCE is the filename.
     *
     * @param string configFile The name of the configuration file or PEAR_Error
     * if the SQL map client class couldn't be configured.
     * @return mixed A PEAR_Error if an error occurred while reading the
     * configuration file.
     */
    function configure($configFile = null) {
        // Read the configuration file
        $this->root =& $this->loadConfiguration($configFile);
        if (PEAR::isError($this->root)) {
            return $this->root;
        }
        
        // Create the database connection
        $this->db =& $this->getConnection($this->root);
        if (PEAR::isError($this->db)) {
            return $this->db;
        }

        // Read the mappings
        $this->mappings = array();
        $result =& $this->loadMappings();
        if (PEAR::isError($result)) {
            return $result;
        }
    }

    /**
     * Read the configuration file, parse its XML contents, and return the
     * result as an array.
     *
     * @param string configFile The name of the configuration file.
     * @return mixed The parsed configuration file as an array or PEAR_Error 
     * if the configuration file could not be read.
     */
    function loadConfiguration($configFile) {
        $config = new Config();
        $root =& $config->parseConfig($configFile, 'xml');
        if (PEAR::isError($root)) {
            return $root;
        }
        $root =& $root->toArray();
        return $root['root'];
    }

    /**
     * Create a PEAR_DB database connection from the connection parameters 
     * given.
     *
     * @param array config A configuration array for a PEAR_DB connection.
     * @return mixed A PEAR_DB object or PEAR_Error if the database connection
     * couldn't be established.
     */
    function getConnection($config) {
        // Check if all the elements are configured
        if ( !isset($config['mapsicle']['datasource']['property']) ) {
            return new PEAR_Error('Could not read DataSource configuration.');
        }
        $root =& $config['mapsicle']['datasource']['property'];
        
        // Read datasource connection properties
        $dsn = array();
        foreach ($root as $property) {
            if (!isset($property['@']['name'])
                    || !isset($property['@']['value'])) {
                continue;
            }
            $name = $property['@']['name'];
            $value = $property['@']['value'];
            $dsn[$name] = $value;

            // Bugfix: Earlier versions of Mapsicle allowed URL to be a valid
            // configuration property, but PEAR::DB expects 'hostspec' instead
            // of URL.
            if ($name == 'url') {
                $dsn['hostspec'] = $value;
            }
        }

        // Configure the database
        $options = array(
            'debug'    => 2,
            'portability' => DB_PORTABILITY_NONE & ~DB_PORTABILITY_LOWERCASE
        );
        $db =& DB::connect($dsn, $options);
        return $db;
    }

    /**
     * Load mappings from the parsed XML configuration data.
     * 
     * @return mixed No return value if no errors; PEAR_Error otherwise.
     */
    function loadMappings() {
        $result =& $this->loadMappingsByType('select');
        if (PEAR::isError($result)) {
            return $result;
        }

        $result =& $this->loadMappingsByType('update');
        if (PEAR::isError($result)) {
            return $result;
        }

        $result =& $this->loadMappingsByType('delete');
        if (PEAR::isError($result)) {
            return $result;
        }

        $result =& $this->loadMappingsByType('insert');
        if (PEAR::isError($result)) {
            return $result;
        }

        $result =& $this->loadMappingsByType('query');
        if (PEAR::isError($result)) {
            return $result;
        }
    }

    /**
     * Load mappings by type from the parsed XML configuration data.  By type,
     * we are talking about 'select', 'update', 'insert', or 'delete'.
     *
     * @param string type The type of SQL mappings to load.
     * @return mixed A PEAR_Error if an  error occurs while loading any of 
     * these mappings.
     */
    function loadMappingsByType($type) {
        $config =& $this->root['mapsicle'];

        // Check if any mappings of this type are defined
        if ( isset($config[$type]) ) {
            // If we have several of these types of mappings, load each one
            if ( !isset($config[$type]['#']) ) {
                foreach ($config[$type] as $element) {
                    // Create the mapping
                    $mapping =& $this->getMapping($element, $type);
                    if (PEAR::isError($mapping)) {
                        return $mapping;
                    }

                    // Check if the mapping exists already
                    if ( isset($this->mappings[$mapping->id]) ) {
                        return new PEAR_Error("Duplicate mapping with id $id");
                    }

                    // Add the mapping
                    $this->mappings[$mapping->id] = $mapping;
                }
            } else {
                // In this case, there is just a single mapping of this type
                $mapping =& $this->getMapping($config[$type], $type);
                if (PEAR::isError($mapping)) {
                    return $mapping;
                }

                // Check if the mapping exists already
                if ( isset($this->mappings[$mapping->id]) ) {
                    return new PEAR_Error("Duplicate mapping with id $id");
                }

                // Add the mapping
                $this->mappings[$mapping->id] = $mapping;    
            }
        }
    }

    /**
     * Create a MapsicleMapping object from a parsed XML element and the
     * sql statement type.  The XML element should be an array with keys '@'
     * and '#' if parsed by the PEAR_Config class.
     *
     * i.e. The array should look like this:
     * <code>
     * array('@' => array('id' => 'MapID', 'delimiter' => 'MapDelimiter'),
     *    '#' => 'select * from customer')
     * </code>
     * 
     * The type is either 'select', 'update', 'insert', or 'delete'.
     *
     * @param array element The XML element that defines this mapping.
     * @param string type The type of SQL statement in the mapping.
     * @return mixed A MapsicleMapping object or a PEAR_Error if the element
     * wasn't defined correctly.
     */
    function getMapping($element, $type) {
        // Check if id attribute is defined
        if ( !isset($element['@']['id']) ) {
            return new PEAR_Error('Mapping is missing id attribute.');
        }

        // Check if delimiter attribute is defined
        if ( !isset($element['@']['delimiter']) ) {
            $delimiter = MAPSICLE_DEFAULT_DELIMITER;
        } else {
            $delimiter = $element['@']['delimiter'];
        }

        // Check if pcdata exists
        if ( !isset($element['#']) ) {
            return new PEAR_Error('Could not read mapping.');
        }

        // Create the mapping
        $mapping = new MapsicleMapping();
        $mapping->id = $element['@']['id'];
        $mapping->sql = $element['#'];
        $mapping->type = $type;
        $mapping->delimiter = $delimiter;
        return $mapping;
    }

    /**
     * Build a Mapsicle object from this MapsicleConfiguration object.
     *
     * @return mixed A Mapsicle object that was configured from this
     * MapsicleConfiguration object or PEAR_Error if Mapsicle wasn't 
     * configured correctly.
     */
    function buildMapsicle() {
        if (is_null($this->db) || is_null($this->mappings)) {
            return new PEAR_Error('Error configuring Mapsicle.');
        }
        return new Mapsicle($this->db, $this->mappings);
    }
}
?>