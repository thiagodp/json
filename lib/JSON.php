<?php
namespace phputil;

/**
 *  JSON encoder and decoder. Depends on phputil\RTTI.
 *  
 *  @author		Thiago Delgado Pinto
 *  @version	1.0
 *  
 *  @see		phputil\RTTI
 */
class JSON {
	
	private static $conversions = array();
	
	/**
	 *  Add a conversion for objects of a certain class.
	 *  If the class name is already mapped, the function is overridden.
	 *  
	 *  @param [in] $className Class name.
	 *  @param [in] $function  Function that receives a value and returns a value.
	 *  
	 *  @details Example:
	 *  
	 *  addConversion( 'DateTime', function( $value ) {
	 *  	return $value->format( 'Y-m-d' );
	 *  } );
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
	 *  Remove a conversion for a certain class.
	 *  @param [in] $className Class name.
	 */
	static function removeConversion( $className ) {
		unset( self::$conversions[ $className ] );
	}

	/**
	 * Encode a data to JSON format.
	 * 
	 * @param data							the data to be serialized as JSON.
	 * @param getterPrefixForObjectMethods	the prefix for getter methods (defaults to 'get').
	 *										Ignore it for non object data.
	 * @return								a string in JSON format.
	 */
	static function encode( $data, $getterPrefixForObjectMethods = 'get' ) {
		$type = gettype( $data );
		$isObject = false;
		switch ( $type ) {		
			case 'string'	: return '"' . self::correct( $data ) . '"';
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
					
					if ( is_numeric( $key ) ) {
						$output []= $encodedValue;
					} else {
						$encodedKey = self::encode( $key, $getterPrefixForObjectMethods );
						$output []= $encodedKey . ' : ' . $encodedValue;
					}
				}
				return $isObject ? '{ ' . implode( ', ', $output ) . ' }' : '[ ' . implode( ', ', $output ) . ' ]';
			}
			default: return ''; // Not supported type
		}
	}
	
	/**
	 *  Decode a JSON content to an object or array.
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
	 * Correct the string to be returned as JSON. This function is replacing addslashes that fails
	 * in convert \' to '. The javascript fails if a \' is found in a JSON string.
	 *
	 * @param str	the string to be corrected.
	 * @return		a corrected string.
	 */
	private static function correct( $str ) {
		// I know that the parameters in str_replace could be an array but I think it is more
		// readable to use this way.
		$newStr = str_replace( '"', '\"', $str );
		return str_replace( '\\\'', '\'', $newStr );
	}

}

?>