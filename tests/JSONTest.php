<?php
namespace phputil\tests;

use PHPUnit_Framework_TestCase;
use phputil\JSON;


class Dummy { // phputil\tests\Dummy

	private $value;
	
	function __construct( $value ) { $this->value = $value; }
	
	function getValue() { return $this->value; }
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
}

?>