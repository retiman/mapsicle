CONFIGURATION
=============
Before you start using Mapsicle, you must configure it for your project.  You must
create a mapsicle.xml file somewhere.  Important: This file will contain sensitive
information like your database login and password.  Either don't place this file in
your web root folder, or make sure your web server doesn't serve it up.

The configuration file looks like this:

    <?xml version="1.0"?>
    <mapsicle>
      <!-- Configure your database here //-->
      <datasource>
        <property name="phptype" value="mysql"/>
        <property name="url" value="localhost"/>
        <property name="database" value="test"/>
        <property name="username" value="mapsicle"/>
      </datasource>

      <!-- You may have select, insert, delete, and update statements //-->
      <select id="Customer.byName">
        select *
        from Customer
        where first_name    = #firstName#
        and last_name       = #lastName#
      </select>
      <insert id="Customer.create">
        insert into Customer (first_name, last_name)
        values (#firstName, #lastName#)
      </insert>
      <update id="Customer.update">
        update Customer
        set first_name      = #firstName#,
        last_name           = #lastName#
      </update>
      <delete id="Customer.removeById">
        delete from Customer where cust_id = #id#
      </delete>
    </mapsicle>

DELIMITERS
==========
Mapsicle uses a delimiter to parse out the named parameters in a query.  The default
delimiter, if you don't specify one for a query,  is #.  However, if your query
contains the character #, you may wish to specify a different delimiter, as long as
it doesn't have special meaning in PHP or SQL.  For example, you might use | or ^ as
delimiters; although, there is no need for delimiters to be restricted to one
character.

USAGE
=====
In your code, you will instantiate a Mapsicle object like so:

    require_once('Mapsicle.php');
    // It's important to include the ampersand (&), or you will just get a copy
    $sqlmap =& MapsicleFactory::createMapsicle();
    // Use the following factory method if the configuration file is not in the
    // include_path
    //$sqlmap =& MapsicleFactory::createMapsicle('mymapsicle.xml');

QUERYING FOR OBJECTS
====================
You can query for a single object with the following code.  Important: If your query
happens to return more than result, only the first result returned will be the
returned object!  If no objects are found by the query, the result is null.

    $params = array('firstName' => 'John', 'lastName' => 'Doe');
    $customer =& $sqlmap->queryForObject('Customer.byName', $params);
    echo 'Customer\'s email is: ' . $customer->email . '<br/>';

QUERYING FOR LISTS
==================
You can ask Mapsicle to return a list of objects with the following code.  If no
results were found, then the list will be empty.

    $params = array('firstName' => 'John', 'lastName' => 'Doe');
    $list =& $sqlmap->queryForList('Customer.byName', $params);
    foreach ($list as $customer) {
      echo 'Customer\'s email is: ' . $customer->email . '<br/>';
    }

QUERYING FOR MAPS
=================
Finally, you may ask Mapsicle to return an array of maps with the following code:

    $params = array('firstName' => 'John', 'lastName' => 'Doe');
    $list =& $sqlmap->queryForMap('Customer.byName', $params);
    foreach ($list as $customer) {
      echo 'Customer\'s email: ' . $customer['email'] . '<br/>';
    }

INSERT, UPDATE, DELETE
======================
The insert, update, and delete SQL statements can be executed in much the same manner
as select queries:

    $params = array('firstName' => 'John', 'lastName' => 'Doe');

    // Insert gives you back the last insert id, if your database supports it, and only
    // if you didn't specify the id yourself (you let the database generate it)
    $id = $sqlmap->insert('Customer.create', $params);
    // Update example
    $params['id'] = $id;
    $sqlmap->update('Customer.update', $params);
    // Delete example
    $sqlmap->delete('Customer.removeById', $params);

NAMED PARAMETERS
================
The named parameters passed in to the `queryForXXX`, `insert`, etc. methods don't
have to be hashmaps.  If you wish, you may use value objects instead.  Use this
example for reference:

    $customer = new Customer();
    $customer->id = 3;
    $customer->firstName = 'Jane';
    $customer->lastName = 'Doe';

    // Customer id 3 will be updated with the values above
    $sqlmap->update('Customer.update', $customer);
