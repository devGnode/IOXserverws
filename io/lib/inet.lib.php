<?php

class inet{
	
	/*
	* atol alpha to long ip
	* @return int
	*/
	public static function atol( $ip = "" ){
	$ip  = explode(".", $ip);
	return (int)$ip[0] << 0x18 | 
		   (int)$ip[1] << 0x10 | 
		   (int)$ip[2] << 0x08 | 
		   (int)$ip[3];
	}
	/*
	* atol long to alpha
	* @return string
	*/
	public static function ltoa( $ulip ){
		return ((( $ulip >> 24 )&0xff ).".".
			    (( $ulip >> 16 )&0xff ).".".
			    (($ulip  >> 8  )&0xff ).".".
			    ( $ulip&0xff ) );
	}
	/*
	* getHost 
	* @return string
	*/
	public static function getHost( $host = 0 ){
		return gethostbyaddr( 
			getType( $host ) == "number" ? self::ltoa( $host ) : $host 
		);
	}
}

?>