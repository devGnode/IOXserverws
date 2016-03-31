<?php

/*	
	**
	* websocket.lib.php
	*
	* little webSocket libraries,she allows 
	* create socket web.
	*
	* @category   static library
	* @package    websocket
	* @author     looter
	* @copyright  2016 devgnode GPL/GPU 
	
	      0                   1                   2                   3
      0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1
     +-+-+-+-+-------+-+-------------+-------------------------------+
     |F|R|R|R| opcode|M| Payload len |    Extended payload length    |
     |I|S|S|S|  (4)  |A|     (7)     |             (16/64)           |
     |N|V|V|V|       |S|             |   (if payload len==126/127)   |
     | |1|2|3|       |K|             |                               |
     +-+-+-+-+-------+-+-------------+ - - - - - - - - - - - - - - - +
     |     Extended payload length continued, if payload len == 127  |
     + - - - - - - - - - - - - - - - +-------------------------------+
     |                               |Masking-key, if MASK set to 1  |
     +-------------------------------+-------------------------------+
     | Masking-key (continued)       |          Payload Data         |
     +-------------------------------- - - - - - - - - - - - - - - - +
     :                     Payload Data continued ...                :
     + - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - +
     |                     Payload Data continued ...                |
     +---------------------------------------------------------------+
 
*/

// const 
interface __webSocketconst__{
	
	// Frame opcode
	const CONTINUE_FRAME = 0x00;
	const TEXT_FRAME     = 0x01;
	const BINARY_FRAME   = 0x02;
	const CLOSE_FRAME    = 0x08;
	const PING_FRAME     = 0x09;
	const PONG_FRAME     = 0x0A;

	// 1000-1015
	const CLOSE_NORMAL      	= 0x03E8;
	const CLOSE_GOING_AWAY 		= 0x03E9;
	const CLOSE_PROTOCOL_ERROR	= 0x03EA;
	const CLOSE_UNSUPPORTED     = 0x02EB;
	const CLOSE_NO_STATUS       = 0x03EC;
	const CLOSE_ABNORMAL		= 0x03ED;
	const CLOSE_BAD_DATA		= 0x03EF;
	const CLOSE_TOO_LARGE		= 0x03F1;
	const CLOSE_BAD_EXT			= 0x03F2;
	const CLOSE_BAD_HANDSHAKE	= 0x03F7;
	
	// 3000-4000 Framwork or library error
	//...
	//...
	// 4000-4999 Application error
	//...
	//...
}

/*
* webSocket Trame RFC 6455
* basic structure websocket
*
*/
Class frameWebSocket{
	
	// header
	public $fin;
	public $rsv;
	public $opc;
	public $masked;
	
	public $mask;
		
	/*
	* content data receve
	* by the socket
	* 		
	* string payload
	* string decoedpayload
	*/
	public $payload    		= "";
	public $decodepayload	= "";
	public $payloadLen;

	// more
	public $frameLen;
	public $rawframe;
}
//

class __webSocket__ extends __http__ implements __webSocketconst__{
	
	/*
	* Build raw packet handshake
	* return a packet ready be sending.
	* param : httpHeaderQuery,
	* @reutrn String headerString
	*/
	public static function handshakePacket( httpHeaderQuery $header ){
	return self::rawHeaderResponse( 
			self::mountResponse(
				ST_SWITCH_PROTOCOL,"WebSocket Protocol Handshake",
				array( 
					"Connection" 			=> "Upgrade",
					"Sec-WebSocket-Accept"   => base64_encode( sha1( $header->options["Sec-WebSocket-Key"]."258EAFA5-E914-47DA-95CA-C5AB0DC85B11", true  ) ),
					"Sec-WebSocket-Location" => "ws://".$header->options["Host"],
					"Sec-WebSocket-Origin"   => "http://".$header->options["Host"],
					"Upgrade" 				=>  "WebSocket",
				)
		 ) );
	}
	
	private static function translateMessage( $data, $len, $mask, $encode ){
		$ret = "";
		try{
			for( $i = 0; $i < $len; $i++ ){
				isset( $data[ $i ] ) ?
					!$encode ?
					$ret .= $data[ $i ] ^ $mask[ $i%4 ] :
					$ret .=  chr( ord( $data[ $i ] ) ^ ord( $mask[ $i%4 ] ) )
					:0;
			}
		}catch( Exception $e ){};
	return $ret;
	}
	
	/*
	* parse Frame websocket
	* "\x81\x84\x1c\x2e\xf1\x68\x70\x44\x99\x03"
	* @return frameWebSocket frame
	*/
	public static function parse( $__trame__ = "" ){
		$fws = new frameWebSocket( );
	
		$first = ord( substr( $__trame__, 0, 1 ) );
		$secnd = ord( substr( $__trame__, 1, 2 ) );
		
		// first bytes parsing
		$fws->fin = ( bool )( ($first&0x80) >> 7);
		$fws->rsv = (( $first&0x70 ) >> 4 );
		$fws->opc = $first&0x0F;
		// seconde bytes parsing
		$fws->masked     = (($secnd&0x80) >>7 );
		$fws->payloadLen = $secnd&0x7F;
		$fws->rawframe   = $__trame__;
		
		if( $fws->masked ){
			// extend payload
			if( $fws->payloadLen >= 0x7E ){
				
				$fws->payloadLen  = ord( substr( $__trame__, 2, 1 ) ) + ord( substr( $__trame__, 3, 1 ) );
				$fws->mask	      = substr( $__trame__, 4, 4 );
				$fws->payload     = substr( $__trame__, 8 );
				
			}else{
				$fws->mask	   = substr( $__trame__, 2, 4 );
				$fws->payload  = substr( $__trame__, 6 );
			}
			
			$fws->decodepayload = self::translateMessage( 
				$fws->payload,
				$fws->payloadLen,
				$fws->mask, 
				false
				);
				
		}else{
			$fws->mask	   = NULL;
			$fws->payload  = $fws->decodepayload = $fws->payloadLen === 0x7F ? substr( $__trame__, 0x0A ) :
			$fws->payloadLen === 0x7E  ?  substr( $__trame__, 0x04 ) :
			substr( $__trame__, 0x02 );
		}
		$fws->frameLen = strlen( $__trame__ );
		
	return 	$fws;
	}
	//
	
	private static function genKey( ){
		return chr( rand( 1 , 255 ) ).
			   chr( rand( 16, 255 ) ).
			   chr( rand( 32, 255 ) ).
			   chr( rand( 64, 255 ) );
	}	
	public static function mount( $opcode, $data = NULL, $key = NULL, $masked = 1  ){
		$_key = "";
		
		$tmp  = ( 1 << 7 ); // fin
		$tmp |= $opcode&0x0F;
		$header = chr( $tmp );
		
		if( $data == NULL && strlen( $data ) >= 0x7F ){
			$tmp = ( ( ( $masked << 7) | strlen( 0x7F ) ) << 16 ) | strlen( $data );
			
		}else{
			$tmp = ($masked << 7) | strlen( $data );
		}
		$header .= chr( $tmp );
		
		if( $masked == 1 ){
			$header .= ( $_key = !$key && $key == NULL ? self::genKey( ) : $key );
			$header .= self::translateMessage( 
			$data,
			strlen( $data ),
			$_key,
			true
			);
		}else
			$header .= $data;;
		
	return $header;
	}
	
}

?>