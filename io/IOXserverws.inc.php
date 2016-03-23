<?php
	include "/lib/socket.lib.php";
	include "/lib/http.const.php";
	include "/lib/http.lib.php";
	include "/lib/url.lib.php";
	
	include "/debug.inc.php";
	
	// debugging repport console
	$debug    = new Debug( 
				array( "FATAL_ERROR", "CONNECT" 
				// "RECEVE", ""CLOSE", "HEADER_QUERY", "BAD"
				) );

	// Class Server 
	class IOXserverws{
		
		public function __runServer( $ip, $port, $__ctrl ){
			global $debug;
			
			set_time_limit(0);
			ini_set('default_socket_timeout', 10);
		
			// create socket
			$__true__ = !0;
			$__ctrl->__ws__ = $__ws__   = new webSocket( $ip, $port );
			
			// error socket
			if( !$__ws__->msock ){
				$debug->__debug( socket_strerror( socket_last_error( ) ), "FATAL_ERROR" );
				return;
			}
			
			// httpPacket
			$__query__;
			$__packet__;
			
			$__ws__->listen( );
			try{
				while( $__true__ ){
			
				// 
				$read   = $__ws__->socks;
				$except = $__ws__->socks;
				$write  = NULL;
				socket_select( $read, $write, $except, 0);
				$closure = false;
				
				//
				foreach( $read as $socket ){
				
					// accept connection
					if( $socket == $__ws__->msock ){
					
						if( ( $client = socket_accept( $__ws__->msock ) ) ){	
							$debug->__debug("webSocket : new query entry ! ", "CONNECT");
							$__ctrl->accept( $client );
						}
					
					}else{
						
						// receve Data
						if( socket_recv( $socket, $buffer, 2048, 0 ) ){
					
							// query handshake
							if( $__query__ = __http__::parseQuery( $buffer ) ){
								
								// no query webSocket
								// Error query
								if( !isset( $__query__->options["Sec-WebSocket-Key"] ) ){
									
									$__packet__ = __http__::rawHeaderResponse(
										__http__::mountResponse( 
											ST_FORBIDDEN, "FORBIDDEN",
											array( 
												"Connection"     => "Close",
												"content-lenght" => strlen("<b>FORBIDDEN</b>"),
												"X-Protocol-Use" => "WebSocket",
												"Server"	     => "IOXserverws/1.0"
											),
											"<b>FORBIDDEN</b>"
										)
									);
									$closure = true;
								
								// Good Request
								}else{
								
									// get header WebSocket
									// $__packet__ 
									$__packet__ = __http__::handshakePacket( $__query__ );
									$__ctrl->handshake( $socket, $__query__, $debug );
							
									// log
									$debug->__debug( "WebSocket-Key : ".$__query__->options["Sec-WebSocket-Key"], "CONNECT" );
									$debug->__debug( $__query__, "HEADER_QUERY" );
							
								}
								// send Data
								socket_write( $socket, $__packet__, strlen( $__packet__ ) );
								
								// BAD query closure socket
								if( $closure ){
									$__ctrl->closeClient( $socket, $debug );
									$debug->__debug( "BAD QUERY", "BAD" );									
								}
								
							// receve Data
							}else{
								$__ctrl->receve( $socket, $buffer, $debug );
								$debug->__debug( "recev : ".$buffer, "RECEVE" );
							}
							//
						
						// close client
						}else{
							$__ctrl->closeClient( $socket, $debug ); 
							$debug->__debug( "closing : ".$socket , "CLOSE");
						}
					}
				
				}// foreach
			
					sleep( 1 );
				}// while
		
				$__ws__->close( );	
				
			}catch( Exception $e ){
				$debug->__debug( $e, "FATAL_ERROR" );
			}
		}
		
	}


?>