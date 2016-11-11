# JSON

A JSON encoder and decoder that can also convert PHP objects with private or protected attributes.

[![Build Status](https://travis-ci.org/thiagodp/json.svg?branch=master)](https://travis-ci.org/thiagodp/json)

We use [semantic versioning](http://semver.org/). See [our releases](https://github.com/thiagodp/json/releases).

### Classes

* [phputil\JSON](https://github.com/thiagodp/json/blob/master/lib/JSON.php)

### Installation

```command
composer require phputil/json
```
Depends only on [phputil\RTTI](https://github.com/thiagodp/rtti). Requires PHP >= `5.4`.

### Example 1

Converting an object with `private` attribute.

```php
<?php
require_once 'vendor/autoload.php'; // or 'RTTI.php' when not using composer

use \phputil\JSON;

class User {
	private $name;
	function __construct( $n ) { $this->name = $n; }
	function getName() { return $this->name; }
}

echo JSON::encode( new User( 'Bob' ) ); // { "name": "Bob" }
?>
```

### Example 2

Converting an array of dynamic objects to JSON and back again.

```php
$obj1 = new stdClass();
$obj1->name = 'Bob';

$obj2 = new stdClass();
$obj2->name = 'Suzan';
$obj2->age = 21;

$json = JSON::encode( array( $obj1, $obj2 ) );
echo $json, '<br />'; // [ { "name": "Bob" }, { "name": "Suzan", "age": 21 } ]

$array = JSON::decode( $json );
var_dump( $array ); // array with the two PHP dynamic objects 
```

### Example 3

Ignoring `NULL` values.

```php
$arr = array( 'name' => 'Bob', 'phone' => null, 'age' => 21 ); // phone is null
$json = JSON::encode( $arr, 'get', true ); // true for ignore nulls
echo $json; // { "name": "Bob", "age": 21 }
```

### Example 4

Using value conversors.

```php
JSON::addConversion( 'DateTime', function( $value ) {
	return $value->format( 'Y-m-d' ); // transforms into a formatted string
} );

$obj = new stdClass();
$obj->user = 'bob';
$obj->birthdate = new DateTime( "1980-12-31" ); // object

echo JSON::encode( $obj ); // { "user": "bob", "birthdate": "1980-12-31" }
```
