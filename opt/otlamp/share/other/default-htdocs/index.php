<?php
/*ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);

        <h1>IT WORKS!</h1><br/>

*/

?>
<!DOCTYPE html>
<html>
    <head>
	<link rel="icon" type="image/vnd.microsoft.icon" href="/favicon.ico" />
        <title>OTLamp default page</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <style>
        h1 { font-size: 50px; }
	html { height: 100%;
		position: relative;
		display: table;
		width: 100%;
	     }	
        body {  text-align:center;
		font: 20px Helvetica, sans-serif;
		color: #0E5F87;
		height: 100%;
		vertical-align: middle;
		display: table-cell;
		width: 100%;
	     }
        p.man { font-size: 15px;}
        body * { margin:0;}
        </style>
    </head>
    <body>
	 <img src="img/logo_otlamp.png"> 
	<h3>Сервер обслуживается компанией Открытые Технологии.</h3>
        <p class="man"><?php   echo 'Path: ' .str_replace("/var/opt/otlamp","",dirname(dirname( dirname(__FILE__))));
	echo ' ';
        echo date('l dS of F Y h:i:s A');
        ?>
        </p1>
    </body>
</html>
