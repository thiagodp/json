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

Converting an object with `private` or `protected` attributes.

```php
<?php
require_once 'vendor/autoload.php';

use \phputil\JSON;

class Foo {
	private $a = 1;
	protected $b = 2;
	public $c = 3;
	
	function getA() { return $this->a; }
	function getB() { return $this->b; }
	function getC() { return $this->c; }
}

echo JSON::encode( new Foo() ); // { "a": 1, "b": 2, "c": 3 }
?>
```

### Example 2

Converting a *dynamic object*.

```php
$obj = new stdClass();
$obj->name = 'Suzan';
$obj->age = 21;

echo JSON::encode( $obj ); // { "name": "Suzan", "age": 21 }
```

### Example 3

Converting an `array` of *dynamic objects* to JSON and back again.

```php
$obj1 = new stdClass();
$obj1->name = 'Bob';

$obj2 = new stdClass();
$obj2->name = 'Suzan';
$obj2->age = 21;

$json = JSON::encode( array( $obj1, $obj2 ) );
echo $json; // [ { "name": "Bob" }, { "name": "Suzan", "age": 21 } ]

$array = JSON::decode( $json );
var_dump( $array ); // array with the two PHP dynamic objects 
```

### Example 4

Converting attributes from classes that use the `__call` magic method.

```php
class Foo {
	private $a = 1;
	protected $b = 2;
	public $c = 3;
	
	function __call( $name, $args ) {
	    if ( 'getA' === $name ) { return $this->a; }
	    if ( 'getB' === $name ) { return $this->b; }
	    if ( 'getC' === $name ) { return $this->c; }
	}
}

echo JSON::encode( new Foo() ); // { "a": 1, "b": 2, "c": 3 }
```

### Example 5

Ignoring `NULL` values in objects' attributes or array values.

```php
$arr = array( 'name' => 'Bob', 'phone' => null, 'age' => 21 ); // phone is null
// true as the third argument makes encode() to ignore null values
echo JSON::encode( $arr, 'get', true ); // { "name": "Bob", "age": 21 }
```

### Example 6

Using value conversors. A value conversor is a function to convert values of a certain type correctly. For example, suppose that you need to convert values of the type `DateTime` to the format `year-month-day`. All you need is to register the type and a function to convert its values, using the static method `addConversion`:

```php
JSON::addConversion( 'DateTime', function( $value ) {
	return $value->format( 'Y-m-d' ); // year-month-day
} );

$obj = new stdClass();
$obj->user = 'bob';
$obj->birthdate = new DateTime( "12/31/1980" ); // month/day/year

echo JSON::encode( $obj ); // { "user": "bob", "birthdate": "1980-12-31" }
```

## License

[MIT](https://choosealicense.com/licenses/mit/) (c) [Thiago Delgado Pinto](https://github.com/thiagodp)