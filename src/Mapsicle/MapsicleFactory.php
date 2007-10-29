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
 * Factory for building Mapsicle objects.
 *
 * @category    Database
 * @package     Mapsicle
 * @author      Min Huang
 * @since       Class available since Release 1.0
 */
class MapsicleFactory {
    /**
     * Build a Mapsicle object from a given configuration file.
     * 
     * @param string configFile The mapsicle configuration file.
     * @return mixed A Mapsicle object or PEAR_Error if an error occured while
     * trying to configure the Mapsicle object.
     */
    function createMapsicle($configFile = null) {
        // Check for the configuration file
        if ($configFile == null) {
            $configFile = MAPSICLE_CONFIG_RESOURCE;
        }

        // Configure Mapsicle
        $configuration =& new MapsicleConfiguration();
        $result =& $configuration->configure($configFile);
        if (PEAR::isError($result)) {
            return $result;
        }
        return $configuration->buildMapsicle();
    }

    /**
     * This method is an alias for MapsicleFactory::createMapsicle
     * 
     * @param string configFile The mapsicle configuration file.
     * @return mixed A Mapsicle object or PEAR_Error if an error occured while
     * trying to configure the Mapsicle object.
     */
    function buildMapsicle($configFile = null) {
        return MapsicleFactory::createMapsicle($configFile);
    }
}
?>