<?php
/*******************************************************************************

    Copyright 2011 Missoula Food Co-op, Missoula, Montana.

    This file is part of Fannie.

    IS4C is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    IS4C is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    in the file license.txt along with IS4C; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*********************************************************************************/

require_once('../src/formlib.inc');
require_once('../src/htmlparts.php');

$page_title = "Reporting";
$header = "Custom Items Report";
// include ('../src/header.html');

$html='<!DOCTYPE HTML>
<html>
<head>';
	
$html.=head();
	
// $html .= '<script src="../src/CalendarControl.js" language="javascript"></script>';

$html.='
	<title>IS4C - Custom Items</title>
</head>
<body>';
	
$html.=body();


	$link = mysql_connect("localhost", "backend", "is4cbackend");
	if (!$link) {
		echo "couldn't connect to is4c_op.";
		exit;
	}
	$success = mysql_select_db('is4c_op', $link);

	if (!$success) {
		echo "Couldn't select op db: " . mysql_error();
		exit;
	} 

	$query = "SELECT  upc, description from products where upc <= 99999 ORDER BY upc asc";
//	echo $query . "<br \>\n";
	$res = mysql_query($query, $link);
	if (!$res) {
		echo "error: " . mysql_error() . "<br />\n";
	}

	$custitems = array();
	while ($row = mysql_fetch_assoc($res)) {
		$upc = $row['upc'];
		$desc = $row['description'];

		$custitems[$upc] = $desc;
	}

	$sections = array(
		array("1", 999, "misc"),
		array("1000", 1099, "grains"),
		array("1100", 1199, "beans"),
		array("1200", 1399, "spices"),
		array("1400", 1499, "teas and herbs"),
		array("1500", 1599, "flours and baking"),
		array("1600", 1699, "snacks and candy"),
		array("1700", 1799, "coffee"),
		array("1800", 1899, "pastas"),
		array("1900", 1999, "oils and vinegars (liquids)"),
		array("2000", 2399, "local packaged grocery and bakery"),
		array("2400", 2999, "unassigned"),
		array("3000", 3499, "chill repack cheese"),
		array("3500", 3599, "bulk or repack nuts and seeds"),
		array("3600", 3699, "dried fruit"),
		array("3700", 3799, "bulk or repack olives"),
		array("3800", 3899, "unassigned"),
		array("4000", 4999, "conventional produce"),
		array("5000", 5099, "local frozen"),
		array("5100", 5999, "unassigned"),
		array("6000", 6249, "local meats"),
		array("6250", 6999, "unassigned"),
		array("7000", 7499, "local teas, and HABA"),
		array("7500", 7999, "unassigned"),
		array("8000", 8999, "unassigned"),
		array("9000", 9999, "local organic or homegrown produce"),
		array("10000", 98999, "unknown"),
		array("99000", 99999, "local organic or homegrown produce"),
	);

	$html .= "<h2>Custom Items</h2>";

	$nextupc = 0;
	
	$html .= "<table >";
//	$html .= "<tr><th>UPC</th><th>Product</th></tr>";
	foreach ($sections as $sec) {

		$bufferrows = array();
		foreach ($custitems as $upc => $desc) {
			// a bit inefficient, but it works for now
			if ($upc >= $sec[0] && $upc <= $sec[1])
				$bufferrows[] = tablerow("<a href=\"/item/?a=search&q=".$upc."&t=upc\">$upc</a>", $desc);
		}

		if (count($bufferrows) > 0) {
			$html .= "<tr><td colspan=\"2\"><b>" . $sec[2] . "</b></td></tr>";
		}

		foreach ($bufferrows as $thisrow) {
			$html .= $thisrow;
		}

	}
	$html .= "</table>";


$html.=foot();
	
$html.='
	</body>
</html>';


echo $html;

?>