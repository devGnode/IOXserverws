<?php

/**
 * http.lib.php
 *
 * little http libraries,she allows 
 * read  a http header received or
 * just write header http . Be careful
 * this library does not  manages errors
 * malformed queries while reading http
 * header.
 *
 * @category   static library
 * @package    http
 * @author     looter
 * @copyright  2016 devgnode GPL/GPU 
 *
 */
 
 interface __httpconst__{
	
	// listing of codes HTTP more used
	// client & server
	
	// const Status code 
	// protocol HTTP
	const ST_CONTINUE			= 100;
	const ST_SWITCH_PROTOCOL	= 101;
	const ST_PROCESSING			= 102;

	// success 
	const ST_OK			= 200;
	const ST_CREATE		= 201;
	const ST_ACCEPT 	= 202;
	
	// redirect
	const ST_MOVED			= 301;
	const ST_FOUND			= 302;
	const ST_NOT_MODIFIED	= 304;
	
	// client 
	const ST_BAD_REQ		= 400;
	const ST_UNHAUTH		= 401;
	const ST_PAYMENTREQ		= 402;
	const ST_FORBIDDEN		= 403;
	const ST_NOTFOUND 		= 404;	
	const ST_NOTALLOWED		= 405;
	const ST_REQTIMEOUT		= 408;
	
	// sevrer
	const ST_INTERNAL_SERVER_ERROR	= 500;
	const ST_BAD_GATEWAY			= 502;
	const ST_SERVICE_UNAVAIBLE		= 503;
	const ST_GATEWAY_TIMEOUT		= 504;
	const ST_NOT_SUPPORTED			= 505;
	//...
	//...
	
}

 /*
// Differents structure  http hedear who 
// can be received it ( reading ) or send ( writing )
//	httpHeaderQuery	   ( client )
//  httpHeaderResponse ( server )
//  Object
*/
class httpHeaderQuery implements __httpconst__{
	
	public $method;
	public $uri;
	public $version;
	
	public $options  = Array( );
	public $postData = NULL;
}
class httpHeaderResponse implements __httpconst__{
	
	public $version     = "HTTP/1.1";
	public $statusCode  = 0x0000;
	public $msgCode     = "";

	public $options = Array( );
	public $content = NULL;
}

//
// 
//
class __http__ implements __httpconst__{
	
	//	StatusCode 
	private static $messageStatusCode = array( 
		self::ST_CONTINUE				=> 'Continue',
		self::ST_SWITCH_PROTOCOL		=> 'Switching Protocols',
		self::ST_PROCESSING				=> 'Processing',
		self::ST_OK						=> 'OK',
		self::ST_CREATE					=> 'Created',
		self::ST_ACCEPT					=> 'Accepted',
		203								=> 'Non-Authoritative Information',
		204								=> 'No Content',
		205								=> 'Reset Content',	
		206								=> 'Partial Content',
		300								=> 'Multiple Choices',
		self::ST_FOUND					=> 'Moved Permanently',
		self::ST_MOVED					=> 'Moved Temporarily',
		303								=> 'See Other',
		self::ST_NOT_MODIFIED			=> 'Not Modified',
		305								=> 'Use Proxy',
		self::ST_BAD_REQ				=> 'Bad Request',
		self::ST_UNHAUTH				=> 'Unauthorized',
		self::ST_PAYMENTREQ				=> 'Payment Required',
		self::ST_FORBIDDEN				=> 'Forbidden',
		self::ST_NOTALLOWED				=> 'Method Not Allowed',
		406								=> 'Not Acceptable',
		407								=> 'Proxy Authentication Required',
		self::ST_REQTIMEOUT				=> 'Request Time-out',
		409								=> 'Conflict',
		410								=> 'Gone',
		411								=> 'Length Required',
		412								=> 'Precondition Failed',
		413								=> 'Request Entity Too Large',
		414								=> 'Request-URI Too Large',
		415								=> 'Unsupported Media Type',
		self::ST_INTERNAL_SERVER_ERROR	=> 'Internal Server Error',
		501								=> 'Not Implemented',
		self::ST_BAD_GATEWAY			=> 'Bad Gateway',
		self::ST_SERVICE_UNAVAIBLE		=> 'Service Unavailable',
		self::ST_GATEWAY_TIMEOUT		=> 'Gateway Time-out',
		self::ST_NOT_SUPPORTED			=> 'HTTP Version not supported'
	);

	// getMessageStatusCode
	//
	private static function getStatusCode( httpHeaderResponse $header ){
	return( isset( self::$messageStatusCode[ $header->statusCode ] ) ?
			self::$messageStatusCode[ $header->statusCode ] : NULL );
	}
	
	// ParseHeader Query
	// 
	private static function headParse( $head, $ret ){
		$header = explode( "\r\n", $head );
		
		// review preg_match 
		// simply parsing
		preg_match("/(.+) (.+) (.+)/", $header[0], $tmp );
		if( count( $tmp ) <= 1 )
			return false;;
			//MALFORMED QUERY
		
		// HEADER
		if( $ret instanceof httpHeaderQuery ){
			$ret->method = $tmp[1];
			$ret->uri    = $tmp[2];
			$ret->version= $tmp[3];
		}
		if( $ret instanceof httpHeaderResponse ){
			$ret->version    = $tmp[1];
			$ret->statusCode = $tmp[2];
			$ret->msgCode    = $tmp[3];
		}
		
		try{
			// Options
			$tmp = [];
			for( $i=1; $i < count( $header ); $i++ ){
			
				preg_match( "/^(.+)\: (.+)/", $header[$i], $tmp );
				if( count( $tmp ) == 3 ){
					$ret->options[ $tmp[1] ] = $tmp[2];
				}
			}
		}catch( Exception $e ){};
		
		
		// DATA
		if( strlen( $header[ count( $header )-1 ] ) > 0 ){
			$ret->{ ( ($ret instanceof httpHeaderQuery ) ? "postData" : "content" ) } =  $header[ count( $header )-1 ];			
		}
		
	return $ret;
	}
	
	// parseQuery
	// @return httpHeaderQuery
	public static function parseQuery( $header = "" ){
		return self::headParse( $header, new httpHeaderQuery( ) );
	}
	// parseResponse
	// @return httpHeaderResponse
	public static function parseResponse( $header = "" ){
		return self::headParse( $header, new httpHeaderResponse( ) );
	}
	
	// mount headerResponse
	// @return httpHeaderResponse header
	public static function mountResponse( $status, $msgCode = NULL, $options = array( ), $data = NULL ){
		$header = new httpHeaderResponse( );	
		
		$header->statusCode  = $status;
		$header->msgCode   	 = ( $msgCode == NULL ? self::getStatusCode( $status ) : $msgCode );
		$header->options     = $options;
		$header->content     = $data;

	return $header;
	}
	
	// mount headerQuery
	// @return httpHeaderQuery header
	public static function mountQuery( $method, $uri, $options = array( ), $data = NULL ){
		$header = new httpHeaderQuery( );	
		
		$header->method   = $method;
		$header->uri      = $uri;
		$header->version  = "HTTP/1.1";
		$header->options  = $options;
		$header->postData = $data;

	return $header;
	}
	
	//  
	//
	private static function rawHeader( $header ){
		$ret = "";
		
		// headerResponse
		$ret .= ( ( $header instanceof httpHeaderResponse  ) ?
					( $header->version." ".
					  $header->statusCode." ".
					  $header->msgCode."\r\n" ) :
					  //
					( $header->method." ".
					  $header->uri." ".
				      $header->version."\r\n" ) );
		
		// options
		foreach( $header->options as $option=>$val ){
			$ret .= $option.": ".$val."\r\n";
		}
		$ret .= "\r\n";
		
		isset( $header->content ) && ( $ret instanceof httpHeaderResponse ) ? ( $ret .= $header->content != NULL ? $header->content  : "" ) : 0;
		isset( $header->postData ) && ( $ret instanceof httpHeaderQuery ) ? ( $ret .= $header->postData != NULL ? $header->postData : "" ): 0;
		
	return $ret;
	}
	
	//// ready to be sent
	// param : httpHeaderResponse header
	// @reutrn String headerFormat
	public static function rawHeaderResponse( httpHeaderResponse $header ){
		return self::rawHeader( $header );	
	}
	
	//// ready to be sent
	// param : httpHeaderQuery header
	// @reutrn String headerFormat
	public static function rawHeaderQuery( httpHeaderQuery $header ){
		return self::rawHeader( $header );		
	}
	
}
//

?>
