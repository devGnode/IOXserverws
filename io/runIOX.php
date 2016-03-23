<?php

include "IOXserverws.inc.php";
include "IOXcontroler.inc.php";

 // run server
 ( new IOXserverws( ) )->__runServer( "192.168.1.78", 8084, new controler( NULL ) );

?>
