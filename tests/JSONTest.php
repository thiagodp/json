<?php
namespace phputil\tests;

use PHPUnit_Framework_TestCase;
use phputil\JSON;


class Dummy { // phputil\tests\Dummy

	private $value;
	
	function __construct( $value ) { $this->value = $value; }
	
	function getValue() { return $this->value; }
}

class DummyWithCall {
	
	private $a = 1;
	protected $b = 2;
	public $c = 3;
	
    public function __call( $name, $args ){
		if ( $name === 'getA' ) { return $this->a; }
		if ( $name === 'getB' ) { return $this->b; }
		if ( $name === 'getC' ) { return $this->c; }
	}
	
}


/**
 * JSON Test
 *
 * @author	Thiago Delgado Pinto
 */
class JSONTest extends PHPUnit_Framework_TestCase {
	
	
	function setUp() {
		JSON::removeAllConversions();
	}
	
	function test_accepts_conversion() {
		
		$dtStr = '2000-01-01 12:00:00';
		$dt = new \DateTime( $dtStr );
		
		JSON::addConversion( 'DateTime', function ( $value ) {
			return $value->format( 'Y-m-d H:i:s' );
		} );
		
		$obj = new \stdClass();
		$obj->dateTime = $dt;
		
		$encoded = JSON::encode( $obj );
		$decoded = JSON::decode( $encoded );
		
		$this->assertEquals( $dtStr, $decoded->dateTime );
	}
	
	function test_converts_objects_with_private_attributes() {
		$value = 'hello';
		$obj = new Dummy( $value ); 

		$encoded = JSON::encode( $obj );
		$decoded = JSON::decode( $encoded );

		$this->assertEquals( $value, $decoded->value );
	}
	
	function test_accepts_conversion_from_class_of_another_namespace() {
		
		JSON::addConversion( 'phputil\tests\Dummy', function ( $value ) {
			return $value->getValue();
		} );
		
		$value = 'hello';
		$obj = new Dummy( $value ); 
		
		$encoded = JSON::encode( $obj );
		$decoded = JSON::decode( $encoded );
		
		$this->assertEquals( $value, $decoded );
	}	
	
	function test_accepts_class_with_magic_call() {
		$obj = new DummyWithCall();
		$encoded = JSON::encode( $obj );

		$this->assertEquals( '{ "a": 1, "b": 2, "c": 3 }', $encoded );
	}
}

?>