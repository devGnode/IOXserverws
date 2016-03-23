<?php

// include
include "console.inc.php";
	
class Debug{
		
	public $debug  = false;
	private $sniff = null;
	
	public function __construct( $sniff = NULL ){
		if( getType( $sniff ) == "array" ){
			$this->sniff = $sniff;
		}
	}
	public function __debug( $__MSG__, $position = "" ){
		if( !$this->debug &&
			( !$this->debug && $this->sniff != NULL && !in_array(  $position, $this->sniff ) ) ){
			return;
		}
		console::log( $__MSG__ );
	return;
	}
	
}

?>