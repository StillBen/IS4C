<?php # Script 8.1 - mysql_connect.php (Second version after 7.2)

// This file contains the database access information.
// This file also establishes a connection to MySQL and selects the database.

/* try to deal with relative paths for includes */
$path = "";
$found = False;
$uri = $_SERVER['REQUEST_URI'];
$tmp = explode("?",$uri);
foreach(explode("/",$_SERVER["REQUEST_URI"]) as $x){
	if (strpos($x,".php") === False
		&& strlen($x) != 0){
		$path .= "../";
	}
	if (!$found && stripos($x,"fannie") !== False){
		$found = True;
		$path = "";
	}
}

include($path.'config.php');

if (!class_exists("SQLManager"))
	include($path.'src/SQLManager.php');

// Make the connection.
$dbc = new SQLManager($FANNIE_SERVER,$FANNIE_SERVER_DBMS,$FANNIE_OP_DB,
		$FANNIE_SERVER_USER,$FANNIE_SERVER_PW);
setlocale(LC_MONETARY, 'en_US');

if (!function_exists('escape_data')) include($path."src/escape_data.php");


?>
