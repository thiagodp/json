# JSON

A JSON encoder and decoder that can also convert PHP objects with private or protected attributes.

Depends only on [phputil\RTTI](https://github.com/thiagodp/rtti).

Current [version](http://semver.org/): `1.2` (stable, used in production code)

### What's New

* Version `1.2`: Added optional parameter "ignoreNulls" in JSON::encode. Defaults to false.
* Version `1.1`: Added method JSON::decode.
* Version `1.0`: First release, with JSON::encode.

### Classes

* [phputil\JSON](https://github.com/thiagodp/rtti/blob/master/lib/JSON.php)

### Installation

```command
composer require phputil/json
```

### Example 1

Converting an object with `private` attribute.

```php
<?php
require_once 'vendor/autoload.php'; // or 'RTTI.php' when not using composer

use phputil\JSON;

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
<?php
...

$obj1 = new stdClass();
$obj1->name = 'Bob';

$obj2 = new stdClass();
$obj2->name = 'Suzan';
$obj2->age = 21;

$json = JSON::encode( array( $obj1, $obj2 ) );
echo $json, '<br />'; // [ { "name": "Bob" }, { "name": "Suzan", "age": 21 } ]

$array = JSON::decode( $json );
var_dump( $array ); // array with the two PHP dynamic objects 
?>
```

### Example 3

Ignoring `NULL` values.

```php
<?php
$arr = array( 'name' => 'Bob', 'phone' => null, 'age' => 21 ); // phone is null
$json = JSON::encode( $arr, 'get', true ); // true for ignore nulls
echo $json, '<br />'; // { "name": "Bob", "age": 21 }
?>
```
