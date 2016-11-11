<?php
namespace phputil;

/**
 *  JSON encoder and decoder. Depends on phputil\RTTI.
 *  
 *  @author		Thiago Delgado Pinto
 *  @see		phputil\RTTI
 */
class JSON {
	
	private static $conversions = array();
	
	/**
	 *  Adds a conversion for objects of a certain class.
	 *  If the class name is already mapped, the current function is overridden.
	 *  
	 *  @param string	$className Class name.
	 *  @param callable	$function  Function that receives a value and returns a value.
	 *  
	 *  @details Example:
	 *
	 *  	addConversion( 'DateTime', function( $value ) {
	 *  		return $value->format( 'Y-m-d' );
	 *  	} );
	 *  
	 */
	static function addConversion( $className, $function ) {
		self::$conversions[ $className ] = $function;
	}
	
	/** @return bool */
	static function hasConversion( $className ) {
		return array_key_exists( $className, self::$conversions );
	}
	
	/**
	 *  Removes a conversion for a certain class.
	 *  
	 *  @param string $className
	 */
	static function removeConversion( $className ) {
		unset( self::$conversions[ $className ] );
	}
	
	/**
	 *  Removes all conversions.
	 */
	static function removeAllConversions() {
		self::$conversions = array();
	}

	/**
	 *  Encodes a variable into JSON format.
	 *  
	 *  @param	mixed $data								Data to be encoded.
	 *  @param	string $getterPrefixForObjectMethods	Prefix used for getter methods.
	 *  												OPTIONAL. Defaults to 'get'.
	 *  @param	bool $ignoreNulls						Ignore null values when encoding.
	 *  												OPTIONAL. Defaults to false.
	 *  
	 *  @return	string
	 */
	static function encode( $data, $getterPrefixForObjectMethods = 'get', $ignoreNulls = false ) {
		$type = gettype( $data );
		$isObject = false;
		switch ( $type ) {		
			case 'string'	: return '"' . self::fixString( $data ) . '"';
			case 'number'	: ; // continue
			case 'integer'	: ; // continue
			case 'float'	: ; // continue			
			case 'double'	: return $data;				
			case 'boolean'	: return ( $data ) ? 'true' : 'false';
			case 'NULL'		: return 'null';
			case 'object'	: {
				$className = get_class( $data );
				if ( array_key_exists( $className, self::$conversions )
					&& is_callable( self::$conversions[ $className ] ) ) {
					$function = self::$conversions[ $className ];
					$convertedValue = call_user_func( $function, $data );
					return self::encode( $convertedValue );
				}
				$data = RTTI::getAttributes( $data, RTTI::anyVisibility(), $getterPrefixForObjectMethods );
				$isObject = true;
				// continue
			}
			case 'array'	: {
				$output = array();
				foreach ( $data as $key => $value ) {
					
					$encodedValue = self::encode( $value, $getterPrefixForObjectMethods );
					
					if ( $ignoreNulls && 'null' === $encodedValue ) { continue; }
					
					if ( is_numeric( $key ) ) {
						$output []= $encodedValue;
					} else {
						$encodedKey = self::encode( $key, $getterPrefixForObjectMethods );
						$output []= $encodedKey . ': ' . $encodedValue;
					}
				}
				return $isObject ? '{ ' . implode( ', ', $output ) . ' }' : '[ ' . implode( ', ', $output ) . ' ]';
			}
			default: return ''; // Not supported type
		}
	}
	
	/**
	 *  Decodes a JSON content into an object or an array.
	 *  
	 *  @see http://php.net/manual/en/function.json-decode.php
	 *  
	 *  @param string	$json					The JSON content.
	 *  @param bool		$convertObjectsToArrays	When true, converts objects to arrays.
	 *  @param int		$recursionDepth			Recursion depth.
	 *  @param int		$options				Bit mask of JSON options. Currently
	 *  										supports only JSON_BIGINT_AS_STRING.
	 *  										Default is to cast large integers as
	 *  										floats.
	 *  
	 *  @return object | array | bool | null	NULL is returned if the JSON cannot
	 *  										be decoded or if the encoded data is
	 *  										deeper than the recursion limit.
	 */
	static function decode(
		$json,
		$convertObjectsToArrays = false,
		$recursionDepth = 512, // same as PHP default
		$options = 0
		) {
		// Just use PHP's decode function
		return json_decode( $json, $convertObjectsToArrays, $recursionDepth, $options );
	}
	
	/**
	 * Fixes a string to be returned as JSON. This function is replacing addslashes that fails
	 * in convert \' to '. The javascript fails if a \' is found in a JSON string.
	 *
	 * @param	string $str	String to be fixed.
	 * @return	string
	 */
	private static function fixString( $str ) {
		// We know that the parameters in str_replace could be an array but I think it is more
		// readable to use this way.
		$newStr = str_replace( '"', '\"', $str );
		return str_replace( '\\\'', '\'', $newStr );
	}

}

?>