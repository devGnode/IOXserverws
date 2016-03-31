<?php
/**
 * url.lib.php
 *
 * url library ,she allows 
 * parsing a simple url
 * protocol://host[ url library ]
 *
 * @category   Object library
 * @package    url
 * @author     looter
 * @copyright  2016 devgnode GPL/GPU 
 *
 */
class url extends queryString{
	
	private $url;

	public $get   = NULL;
	public $query = NULL;
	public $path  = NULL;
	public $file  = NULL;
	public $post  = NULL;
	
	/*
	* explode data _GET and POST
	* var=value&var1=value1
	* @return Array data
	*/
	private function explodeData( $data, $_FULL = Array( ) ){
		$ret = [];
		$data = explode("&", $data );
		$len = count( $data );
		$_;
		
		for( $i = 0; $i < $len; $i++ ){
			if( preg_match( "/^(\w+)\=(.+)$/", $data[ $i ], $_ ) ){
				$_FULL[ $_[1] ] = $_[2]; 
			}
		}
	return $_FULL;
	}
	//
	
	/*
	* __construct 
	* @return $this
	*/
	public function __construct( $uri ){
		$this->url = $uri;
		
		// if query $_GET
		if( preg_match( "/(.*)(\?.*)/", $uri, $find) ){
			
			$this->path   = $find[ 1 ];
			$this->query  = $find[ 2 ];
			$this->get    = str_replace("?","", $find[ 2 ]);
			
		}
		$this->path = $this->path == NULL ? $uri : $this->path;
		$this->file   = ( preg_match( "/(.+)\/(.+\..+)/", $this->path ) 
						|| preg_match("/\/(.+)\.(.+)/", $this->path ) );	
		
	return $this;
	}
	//
	
	// full Array _GET
	// @return Array $_GET 
	public function _GET( $arr = array( ) ){
		return ( $this->explodeData( $this->get,  $arr ) );
	}
	
	// full Array _POST
	// @return Array $_POST	
	public function _POST( $arr = array( ) ){
		return ( $this->explodeData( $this->post, $arr ) );
	}
	
	// 
	// @return string extension || false	
	public function getExtension( ){
		if( preg_match( "/(.+)\.(.+)$/", $this->path, $find ) ){
			return $find[2];
		}
	return false;
	}
}

?>
