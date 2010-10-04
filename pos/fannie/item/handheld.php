<?php
/*******************************************************************************

    Copyright 2010 Whole Foods Community Co-op

    This file is part of Fannie.

    Fannie is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    Fannie is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    in the file license.txt along with IS4C; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*********************************************************************************/

include('../src/mysql_connect.php');

?>
<html><head><title>Item Maintenance</title>
<script type="text/javascript" 
	src="<?php echo $FANNIE_URL; ?>src/jquery-1.2.6.min.js">
</script>
<style>
body {
	margin: 10px;
}
em {
	color: #330066;
	font-style: normal;
}
a {
	color: blue;
}
</style>
</head>

<?php

if(isset($_REQUEST['upc']) && !empty($_REQUEST['upc'])){
	if (isset($_REQUEST['submit'])){
		$query = "SELECT p.upc,p.description,p.normal_price,p.special_price,
			p.department,p.tax,p.foodstamp,p.scale,p.qttyEnforced,p.discount,
			p.discounttype,x.manufacturer,x.distributor,u.likeCode,l.likeCodeDesc,
			p.modified
			FROM products AS p LEFT JOIN prodExtra AS x ON p.upc=x.upc
			LEFT JOIN upcLike AS u ON p.upc=u.upc LEFT JOIN likeCodes AS l
			ON l.likeCode=u.likeCode ";
		if (!is_numeric($_REQUEST['upc'])){
			$query .= "WHERE p.description like ".$dbc->escape("%".$_REQUEST['upc']."%");
		}
		else {
			switch($_REQUEST['ntype']){
			case 'Brand Prefix':
				$query .= "WHERE p.upc LIKE '%".$_REQUEST['upc']."%'";
				break;
			case 'SKU':
				$query .= "INNER JOIN vendorItems AS v ON p.upc=v.upc
					WHERE v.sku='".$_REQUEST['upc']."'";
			case 'UPC':
			default:
				$query .= "WHERE p.upc='".str_pad($_REQUEST['upc'],13,'0',STR_PAD_LEFT)."'";
				break;
			}
		}
		$query .= " ORDER BY p.description";

		$result = $dbc->query($query);
		$num = $dbc->num_rows($result);
		if ($num > 1){
			echo "<body><b>Multiple items found</b><br />";
			while($row=$dbc->fetch_row($result)){
				printf("<a href=\"handheld.php?submit=Submit&upc=%s\">%s</a><br />",
					$row['upc'],$row['description']);
			}
			echo "</body></html>";
			return;
		}
		else if (is_numeric($_REQUEST['upc']) && ($num == 1 || $num == 0)){
			$row = array();
			if ($num == 1)
				$row = $dbc->fetch_row($result);
			else {
				$row['upc'] = str_pad($_REQUEST['upc'],13,'0',STR_PAD_LEFT);
				$row['description'] = "New Item";
				$row['normal_price'] = 0;
				$row['discounttype'] = 0;
				$row['special_price'] = 0;
				$row['department'] = 1;
				$row['tax'] = 0;
				$row['foodstamp'] = 0;
				$row['discount'] = 1;
				$row['scale'] = 0;
				$row['qttyEnforced'] = 0;
				$row['likeCode'] = "";
				$row['manufacturer'] = "";
				$row['distributor'] = "";
				$row['modified'] = "";
			}
			echo "<body onload=\"$('#pricefield').focus();\">";
			echo "<form action=handheld.php method=get>";
			echo "<table cellpadding=4><tr><td colspan=2>";
			echo "<span style=\"color:red;\">".$row['upc']."</span>";
			printf(" <input type=text name=desc value=\"%s\" /></td></tr>",
				$row['description']);

			printf("<tr><td colspan=2 style=\"font-weight:bold;\"><em>Price</em>
				\$<input style=\"font-size:100%%;height:40px;\" size=\"4\"
				id=\"pricefield\" type=text name=price value=\"%.2f\" />
				 <a style=\"color:green;\" href=hhSale.php?upc=%s>%s</a>
				</td></tr>",$row['normal_price'],$row['upc'],
				(($row['discounttype']>0 && $row['special_price'] != 0)?
				sprintf("On Sale @ \$%.2f",$row['special_price']):""));

			$departments = array();
			$deptQ = "SELECT dept_no,dept_name FROM departments ORDER BY dept_no";
			$deptR = $dbc->query($deptQ);
			while($deptW = $dbc->fetch_row($deptR))
				$departments[$deptW[0]] = $deptW[1];
			echo "<tr><td><em>Dept</em> <select name=dept>";
			foreach($departments as $k=>$v){
				printf("<option %s value=%d>%d %s</option>",
					($row['department']==$k?'selected':''),
					$k,$k,substr($v,0,10));
			}
			echo "</select></td>";

			$taxrates = array();
			$taxrates[0] = "NoTax";
			$taxQ = "SELECT id,description FROM taxrates ORDER BY id";
			$taxR = $dbc->query($taxQ);
			while($taxW = $dbc->fetch_row($taxR))
				$taxrates[$taxW[0]] = $taxW[1];
			echo "<td><em>Tax</em> <select name=tax>";
			foreach($taxrates as $k=>$v){
				printf("<option %s value=%d>%s</option>",
					($row['tax']==$k?'selected':''),
					$k,$v);
			}
			echo "</select></td></tr>";

			echo "<tr><td><em>FS</em> <input type=checkbox name=fs";
			echo ($row['foodstamp']==1?' checked':'');
			echo " /></td>";

			echo "<td><em>Scale</em> <input type=checkbox name=scale";
			echo ($row['scale']==1?' checked':'');
			echo " /></td></tr>";

			echo "<tr><td><em>QtyFrc</em> <input type=checkbox name=qttyForce";
			echo ($row['qttyEnforced']==1?' checked':'');
			echo " /></td>";

			echo "<td><em>NoDisc</em> <input type=checkbox name=nodisc";
			echo ($row['discount']==0?' checked':'');
			echo " /></td></tr>";

			echo "<tr><td colspan=2><input type=submit value=\"Update Item\"
				name=submit2 style=\"width:100%;height:40px;font-size:110%;\" 
				/></td></tr>";

			echo "<tr><td colspan=2><input type=submit value=\"Back\"
				name=back style=\"width:350px;height:40px;font-size:110%;\" 
				onclick=\"top.location='handheld.php';return false;\"
				/></td></tr>";

			printf("<tr><td colspan=2><em>Likecode</em>:
				<a href=\"hhLike.php?upc=%s\">%s</a></td></tr>",
				$row['upc'],(empty($row['likeCode'])?'None':
				$row['likeCode'].' '.$row['likeCodeDesc']));

			echo "<tr><td colspan=2><em>Manufacturer</em>: ";
			echo (!empty($row['manufacturer'])?$row['manufacturer']:'n/a');
			echo "</td></tr>";

			echo "<tr><td colspan=2><em>Distributor</em>: ";
			echo (!empty($row['distributor'])?$row['distributor']:'n/a');
			echo "</td></tr>";

			echo "<tr><td colspan=2><em>Last modified</em>: ";
			echo (!empty($row['modified'])?$row['modified']:'Unknown');
			echo "</td></tr>";
			
			printf("<input type=hidden name=lc value=\"%s\" />",$row['likeCode']);
			printf("<input type=hidden name=upc value=%s /></form>",$row['upc']);
			printf("<input type=hidden name=olditem value=%d /></form>",$num);
			
			echo "</body></html>";	
			return;
		}
		else {
			echo "<i>Error: No item found for: ".$_REQUEST['upc']."</i><br />";
		}
	}
	elseif(isset($_REQUEST['submit2'])){
		$upcs = array();
		$upcs[] = $_REQUEST['upc'];
		$dept = $_REQUEST['dept'];
		$tax = $_REQUEST['tax'];
		$price = $_REQUEST['price'];
		$scale = isset($_REQUEST['scale'])?1:0;
		$fs = isset($_REQUEST['fs'])?1:0;
		$qttyForce = isset($_REQUEST['qttyForce'])?1:0;
		$disc = isset($_REQUEST['nodisc'])?0:1;
		$desc = $_REQUEST['desc'];

		if (!empty($_REQUEST['lc'])){
			// drop from like code, re-add if appropriate
			$delQ = "DELETE FROM upcLike WHERE upc='".$upcs[0]."'";
			$delR = $dbc->query($delQ);
			if (is_numeric($_REQUEST['lc']) && $_REQUEST['lc'] != 0 && $_REQUEST['lc'] != -1){
				$insQ = sprintf("INSERT INTO upcLike (likeCode,upc) 
					VALUES (%d,%s)",$_REQUEST['lc'],$dbc->escape($upcs[0]));
				$insR = $dbc->query($insQ);

				$fetchQ = sprintf("SELECT upc FROM upclike WHERE likeCode=%d",
						$_REQUEST['lc']);
				$fetchR = $dbc->query($fetchQ);
				$upcs = array();
				while($w = $dbc->fetch_row($fetchR))
					$upcs[] = $w[0];
			}
		}

		include('laneUpdates.php');
		foreach($upcs as $upc){
			$query = sprintf("UPDATE products SET
					normal_price=%.2f,
					tax=%d,
					foodstamp=%d,
					department=%d,
					scale=%d,
					discount=%d,
					qttyEnforced=%d,
					modified=%s,
					description=%s
					WHERE upc=%s",
					$price,$tax,$fs,$dept,$scale,$disc,
					$qttyForce,$dbc->now(),
					$dbc->escape($desc),
					$dbc->escape($upc));
			if ($upc == $_REQUEST['upc'] && $_REQUEST['olditem'] == 0){
				$query = sprintf("INSERT INTO products (upc,description,normal_price,
					pricemethod,groupprice,quantity,special_price,specialpricemethod,
					specialgroupprice,specialquantity,start_date,end_date,department,
					size,tax,foodstamp,scale,scaleprice,mixmatchcode,modified,advertised,
					tareweight,discount,discounttype,unitofmeasure,wicable,qttyEnforced,
					idEnforced,cost,inUse,numflag,subdept,deposit,local) VALUES
					(%s,%s,%.2f,0,0.00,0,0.00,0,0.00,0,'1900-01-01','1900-01-01',%d,'',
					%d,%d,%d,0.00,'',%s,1,0,%d,0,'LB',0,%d,0,0.00,1,0,0,0.00,0)",
					$dbc->escape($upc),$dbc->escape($desc),$price,$dept,$tax,$fs,
					$scale,$dbc->now(),$disc,$qttyForce);
				$xQ = "INSERT INTO prodExtra (upc) VALUES (".$dbc->escape($upc).")";
				$dbc->query($xQ);
			}
			$dbc->query($query);
			updateProductAllLanes($upc);

			if ($dbc->table_exists("prodUpdate")){
				$query = sprintf("INSERT INTO prodUpdate VALUES ('%s',%s,%.2f,%d,%d,%d,%d,
					%d,%s,%d,%d,%d,1)",$upc,$dbc->escape($desc),$price,$department,$tax,$fs,$scale,
					0,$dbc->now(),0,$qttyForce,$disc);
				$result =  $dbc->query($query);
			}
		}
		if(count($upcs)==1)
			echo "<i>Item $upcs[0] updated</i><br />";
		else
			echo "<i>Multiple items updated</i><br />";
		printf("[ <a href=\"handheld.php?submit=Submit&upc=%s\">View Last Item</a> ]<br />",
			$_REQUEST['upc']);

	}
}

echo "<body onload=\"$('#upc').focus();\">";
echo "<b>Item Maintenance</b>";
echo "<form action=handheld.php method=get>";
echo "<table><tr><td>";
echo "<input name=upc type=text id=upc> 
</td><td>
<select name=\"ntype\">
<option>UPC</option>
<option>SKU</option>
<option>Brand Prefix</option>
</select><td></tr><tr> 
<td colspan=2 align=right>or description</td></tr>";
echo "<tr><td colspan=2 align=left>";
echo "<input name=submit type=submit value=Submit 
	style=\"width:150px;height:50px;font-size:110%;\" /> ";
echo "</td></tr></table></form>";
echo "</body>";


?>
</html>
