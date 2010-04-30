DESCRIPTION
===========
Mapsicle is a data mapper for PHP (versions 4 and 5), inspired by iBATIS.
Mapsicle is not a direct port (as it wouldn't make sense to be), but Mapsicle
will feel familiar if you have used iBATIS before.

With Mapsicle, you can place all of your SQL statements in an external XML file and
assign id's to them. You may reference these SQL statements by id in your code,
replace any named parameters, and then have Mapsicle execute them for you. This is
how iBATIS does it, but in retrospect, it is not the best approach. I have no
motivation to improve the software as I no longer write much PHP, so an update will
not be forthcoming.

Mapsicle will map a SQL select statement's results to an object or list of objects
(although, the option to map to a list of hash maps is available as well). There's no
need to work with result sets or manage database connections; all the configuration
is done through an XML file. You may use Mapsicle to execute update, delete, and
insert statements as well.

Note: The final version of Mapsicle is 1.09.01a; it is no longer being developed.

INSTALLATION
============
The first step is to download and install the Mapsicle package. You can simply unzip
it into a directory that is in your `include_path`, or add this code to any script
that uses Mapsicle (adding it to the top of the script is a good idea):

    ini_set('include_path', '/path/to/Mapsicle'
      . PATH_SEPARATOR
      . ini_get('include_path'));
    require_once('Mapsicle.php');

Next, you must create a `mapsicle.xml` file and put it anywhere. This file contains
your SQL maps as well as your database configuration. Here's a sample file:

    <?xml version="1.0"?>
    <mapsicle>
      <datasource>
        <property name="phptype"  value="mysql"/>
        <property name="hostspec" value="localhost"/>
        <property name="database" value="test"/>
        <property name="username" value="mapsicle"/>
        <property name="password" value="mapsicle"/>
      </datasource>
      <select id="Customer.findById" delimiter="#">
        select  id          as id,
          cust_first        as firstName,
          cust_last         as lastName,
          cust_email        as email
        from Customer
          where   id        = #id#
      </select>
    </mapsicle>

Note that you may have `<insert>`, `<update>`, and `<delete>` elements as well, for
statements of those types.  Your id can be any string, but it's customary to use the
object name followed by the operation name.  The delimiter tells Mapsicle that the
literal between the delimiter is a named parameter.

USAGE
=====
Just build a mapsicle object and begin using it:

    $sqlmap   =& MapsicleFactory::createMapsicle("mapsicle.xml");
    $customer =  $sqlmap->queryForObject("Customer.findById", array("id" => 3));
    echo "Customer ID: "           . $customer->id . "<br/>";
    echo "Customer's first name: " . $customer->firstName . "<br/>";
    echo "Customer's last name: "  . $customer->lastName . "<br/>";
    echo "Customer's email: "      . $customer->email . "<br/>";

See the USAGE docs for more information.
