<?php

require_once("../src/htmlparts.php");

if (isset($_POST['submitted'])) {
  /**
   * fpdf is the pdf creation class doc
   * manual and tutorial can be found in fpdf dir
   */
  require('../src/fpdf/fpdf.php');
  
  /**
   * prodFuction contains several product related functions
   */
  // require('prodFunction.php');
  
  /**-------------------------------------------------------- 
   *            begin  barcode creation class from 
   *--------------------------------------------------------*/
  
  /*******************************************************************************
  * Software: barcode                                                            *
  * Author:   Olivier PLATHEY                                                    *
  * License:  Freeware                                                           *
  * URL: www.fpdf.org                                                            *
  * You may use, modify and redistribute this software as you wish.              *
  *******************************************************************************/
  define('FPDF_FONTPATH','font/');
  
  class PDF extends FPDF
  {
    function EAN13($x,$y,$barcode,$h=16,$w=.35)
    {
          $this->Barcode($x,$y,$barcode,$h,$w,12);
    }
  
    function UPC_A($x,$y,$barcode,$h=16,$w=.35)
    {
          $this->Barcode($x,$y,$barcode,$h,$w,12);
    }
  
    function GetCheckDigit($barcode)
    {
          //Compute the check digit
          $sum=0;
          for($i=1;$i<=11;$i+=2)
                  $sum+=3*$barcode{$i};
          for($i=0;$i<=10;$i+=2)
                  $sum+=$barcode{$i};
          $r=$sum%10;
          if($r>0)
                  $r=10-$r;
          return $r;
    }
  
    function TestCheckDigit($barcode)
    {
          //Test validity of check digit
          $sum=0;
          for($i=1;$i<=11;$i+=2)
                  $sum+=3*$barcode{$i};
          for($i=0;$i<=10;$i+=2)
                  $sum+=$barcode{$i};
          return ($sum+$barcode{12})%10==0;
    }
  
    function Barcode($x,$y,$barcode,$h,$w,$len)
    {
      GLOBAL $genLeft;
      GLOBAL $descTop;
          //Padding
          //$barcode=str_pad($barcode,$len-1,'0',STR_PAD_LEFT);
      //$barcode = $barcode . $check;
          /*if($len==12)
                  $barcode='0'.$barcode;
      */
          //Add or control the check digit
          if(strlen($barcode)==12)
                  $barcode.=$this->GetCheckDigit($barcode);
          elseif(!$this->TestCheckDigit($barcode))
      {
                  $this->Error('This is an Incorrect check digit' . $barcode);
                  //echo $x.$y.$barcode."\n";
          }
          //Convert digits to bars
          $codes=array(
                  'A'=>array(
                          '0'=>'0001101','1'=>'0011001','2'=>'0010011','3'=>'0111101','4'=>'0100011',
                          '5'=>'0110001','6'=>'0101111','7'=>'0111011','8'=>'0110111','9'=>'0001011'),
                  'B'=>array(
                          '0'=>'0100111','1'=>'0110011','2'=>'0011011','3'=>'0100001','4'=>'0011101',
                          '5'=>'0111001','6'=>'0000101','7'=>'0010001','8'=>'0001001','9'=>'0010111'),
                  'C'=>array(
                          '0'=>'1110010','1'=>'1100110','2'=>'1101100','3'=>'1000010','4'=>'1011100',
                          '5'=>'1001110','6'=>'1010000','7'=>'1000100','8'=>'1001000','9'=>'1110100')
                  );
  
          $parities=array(
                  '0'=>array('A','A','A','A','A','A'),
                  '1'=>array('A','A','B','A','B','B'),
                  '2'=>array('A','A','B','B','A','B'),
                  '3'=>array('A','A','B','B','B','A'),
                  '4'=>array('A','B','A','A','B','B'),
                  '5'=>array('A','B','B','A','A','B'),
                  '6'=>array('A','B','B','B','A','A'),
                  '7'=>array('A','B','A','B','A','B'),
                  '8'=>array('A','B','A','B','B','A'),
                  '9'=>array('A','B','B','A','B','A')
                  );
          $code='101';
          $p=$parities[$barcode{0}];
          for($i=1;$i<=6;$i++)
                  $code.=$codes[$p[$i-1]][$barcode{$i}];
          $code.='01010';
          for($i=7;$i<=12;$i++)
                  $code.=$codes['C'][$barcode{$i}];
          $code.='101';
          //Draw bars
          for($i=0;$i<strlen($code);$i++)
          {
                  if($code{$i}=='1')
                          $this->Rect($x+$i*$w,$y,$w,$h,'F');
          }
          
          //Print text uder barcode

          $this->SetFont('Arial','',9);
          $this->SetXY($genLeft,$descTop + 24);
          $this->Cell(49.609375,4,substr($barcode,-$len),0,0,'C');

    }
  
  }
  
  /**------------------------------------------------------------
   *       End barcode creation class 
   *-------------------------------------------------------------*/
  
  
  /**------------------------------------------------------------
   *        Start creation of PDF Document here
   *------------------------------------------------------------*/
  if(isset($_POST['submit'])){
          foreach ($_POST AS $key => $value) {
                  $$key = $value;
          }
  }else{
        foreach ($_GET AS $key => $value) {
            $$key = $value;
        }
  }
  
  $_SESSION['deptArray'] = 0;
  
  if($_POST['allDepts'] == 1) {
  //	$_SESSION['deptArray'] = "1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,40";
          $dArray = "1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,40";
  } else {
          $allDepts = 0;
  }
  
  if(is_array($_POST['dept'])) {
  //	$_SESSION['deptArray'] = implode(",",$_POST['dept']);
          $dArray = implode(",",$_POST['dept']);
  }
    
  /**
   * connect to mysql server and then 
   * set to database with UNFI table ($data) in it
   * other vendors could be added here, as well. 
   * NOTE: upc in UNFI is without check digit to match standard in 
   * products.
   */
  
  $data = 'is4c_op';
  
//  $db = mysql_connect('localhost','root');
	$db = mysql_connect("localhost", "backend", "is4cbackend");
  mysql_select_db($data,$db);
  
  /** 
   * $testQ query creates select for barcode labels for items
   */ 
   
  
  $testQ = "select if(u.brand IS NULL,'',substring(u.brand,1,12)) as brand,  
                  if(u.sku IS NULL,'', u.sku) as sku,  
                  if(u.size IS NULL,'',u.size) as size,  
                  if(u.upc IS NULL,'',u.upc) as upc,  
                  if(u.units IS NULL,'',u.units) as units,  
                  if(u.cost IS NULL,'',u.cost) as cost,  
                  if(p.description IS NULL, substring(u.description,1,50),substring(p.description,1,50)) as description,   
                  right(p.upc,12) as pid,   
                  if(u.upc IS NULL, 'Misc', 'UNFI') as vendor, 
                  ROUND(normal_price,2) AS normal_price,
                  p.scale AS scale
          from is4c_op.products as p left outer join is4c_op.UNFI as u  on p.upc = u.upc  
          WHERE p.department IN($dArray)  
          AND date(modified) BETWEEN '$date1' AND '$date2'
          ORDER BY department";
  
  
  $result = mysql_query($testQ);
  if (!$result) {
     $message  = 'Invalid query: ' . mysql_error() . "\n";
     $message .= 'Whole query: ' . $query;
     die($message);
  }
  
  /**
   * begin to create PDF file using fpdf functions
   */

  $hspace = 0.79375;
  $h = 29.36875;
  $top = 12.7 + 2.5;
  $left = 4.85 + 1.25;
  $space = 1.190625 * 2;
  
  $pdf=new PDF('P', 'mm', 'Letter');
  $pdf->SetMargins($left ,$top + $hspace);
  $pdf->SetAutoPageBreak('off',0);
  $pdf->AddPage('P');
//  $pdf->SetFont('Arial','',10);
  $pdf->SetFont('Arial','',9);
  
  /**
   * set up location variable starts
   */
   
  $barLeft = $left + 4;
  $descTop = $top + $hspace;
  $desc2Top = $descTop + 2.6;
  $barTop = $descTop + 16;
  $priceTop = $descTop + 4.5;
  $labelCount = 0;
  $brandTop = $descTop + 4;
  $sizeTop = $descTop + 8;
  $genLeft = $left;
  $skuTop = $descTop + 12;
  $vendLeft = $left + 13;
  $down = 30.95625;
  $LeftShift = 51.990625;
  $w = 49.609375;
  $priceLeft = ($w / 2) + ($space);
  // $priceLeft = 24.85
  /**
   * increment through items in query
   */
   
  while($row = mysql_fetch_array($result)){
     /**
      * check to see if we have made 32 labels.
      * if we have start a new page....
      */
      
     if($labelCount == 32){
        $pdf->AddPage('P');
      $descTop = $top + $hspace;
	  $desc2Top = $descTop + 2.6;
      $barLeft = $left + 4;
      $barTop = $descTop + 16;
      $priceTop = $descTop + 4.5;
      $priceLeft = ($w / 2) + ($space);
      $labelCount = 0;
      $brandTop = $descTop + 4;
      $sizeTop = $descTop + 8;
      $genLeft = $left;
      $skuTop = $descTop + 12;
      $vendLeft = $left + 13;
    
     }
  
     /** 
      * check to see if we have reached the right most label
      * if we have reset all left hands back to initial values
      */
     if($barLeft > 175){
        $barLeft = $left + 4;
        $barTop = $barTop + $down;
        $priceLeft = ($w / 2) + ($space);
        $priceTop = $priceTop + $down;
        $descTop = $descTop + $down;
        $desc2Top = $desc2Top + $down;
        $brandTop = $brandTop + $down;
        $sizeTop = $sizeTop + $down;
        $genLeft = $left;
        $vendLeft = $left + 13;
        $skuTop = $skuTop + $down;
     }
  
  /**
   * instantiate variables for printing on barcode from 
   * $testQ query result set
   */
     if ($row['scale'] == 0) {$price = $row['normal_price'];}
     elseif ($row['scale'] == 1) {$price = $row['normal_price'] . "/lb";}
	 $description = strtoupper($row['description']);
	 if (strlen($description) > 25) {
		 $desc1 = substr($description, 0, 25);
		 $spaceidx = strrpos($desc1, " ");
		 $desc1 = substr($desc1, 0, $spaceidx);
		 $desc2 = substr($description, $spaceidx+1, 50);
	 } else {
		 $desc1 = $description;
		 $desc2 = "";
	 }

//     $desc = strtoupper(substr($row['description'],0,27));
     $brand = ucwords(strtolower(substr($row['brand'],0,13)));
     $pak = $row['units'];
     $size = $row['units'] . "-" . $row['size'];
     $sku = $row['sku'];
     $upc = $row['pid'];
  /** 
   * determine check digit using barcode.php function
   */
     $check = $pdf->GetCheckDigit($upc);
  /**
   * get tag creation date (today)
   */
     $tagdate = date('m/d/y');
     $vendor = substr($row['vendor'],0,7);
  
  /**
   * begin creating tag
   */
  $pdf->SetXY($genLeft, $descTop);
  $pdf->Cell($w,4,substr($desc1,0,25),0,0,'L');

  $pdf->SetXY($genLeft, $desc2Top);
  $pdf->Cell($w,4,substr($desc2,0,25),0,0,'L');


  $pdf->SetXY($genLeft,$brandTop);
  $pdf->Cell($w/2,4,$brand,0,0,'L');
  $pdf->SetXY($genLeft,$sizeTop);
  $pdf->Cell($w/2,4,$size,0,0,'L');
  $pdf->SetXY($priceLeft+9,$skuTop);
  $pdf->Cell($w/3,4,$tagdate,0,0,'R');
  // $pdf->SetFont('Arial','',10);
  $pdf->SetXY($genLeft,$skuTop);
  $pdf->Cell($w/3,4,$sku,0,0,'L');
  $pdf->SetXY($vendLeft,$skuTop);
  $pdf->Cell($w/3,4,$vendor,0,0,'C');
  $pdf->SetFont('Arial','B',20);
  $pdf->SetXY($priceLeft,$priceTop);
  $pdf->Cell($w/2,8,$price,0,0,'R');
  /** 
   * add check digit to pid from testQ
   */
    $newUPC = $upc . $check;
    $pdf->UPC_A($barLeft,$barTop,$upc,7);
  /**
   * increment label parameters for next label
   */
    $barLeft =$barLeft + $LeftShift;
    $priceLeft = $priceLeft + $LeftShift;
    $genLeft = $genLeft + $LeftShift;
    $vendLeft = $vendLeft + $LeftShift;
    $labelCount++;
  }
  
  /**
   * write to PDF
   */
  $pdf->Output();
        
        
} else { // Show the form.
  
  $header = 'Shelftag Generator';
//  include ('../src/header.html');
  echo "<html><head>";
  echo '<script type="text/javascript" src="../lib/jquery.js"></script>';
  echo '<script type="text/javascript" src="../lib/jquery-ui.js"></script>';
?>
<script language="Javascript">
$(document).ready(function() {
	$('#date1').datepicker({dateFormat: 'yy-mm-dd'});
	$('#date2').datepicker({dateFormat: 'yy-mm-dd'});
});

</script>

<link type="text/css" rel="stylesheet" href="../lib/jquery-ui.css" />

<?php
  echo head();
	echo "<title>IS4C - Shelftags</title></head><body>";
  echo body();
  // <script src="../src/CalendarControl.js" language="javascript"></script>
  echo '<link href="../style.css" rel="stylesheet" type="text/css" />
  </head>
  <body >
  <link href="../style.css" rel="stylesheet" type="text/css">
  
  <form method="post" action="shelftags.php" target="_blank">
          
  <h2>Shelftag Generator</h2>
  
  <table border="0" cellspacing="3" cellpadding="3">
          <tr> 
                  <th align="center"> <p><b>Select dept.*</b></p></th>
          </tr>
          <tr>';
//               include('../src/departments.php');

	$link = mysql_connect("localhost", "backend", "is4cbackend");
	if (!$link) {
		echo "couldn't connect to is4c_op.";
		exit;
	}
	$success = mysql_select_db('is4c_op', $link);
	if (!$success) {
		echo "Couldn't select log db: " . mysql_error();
		exit;
	}


$query = "SELECT * FROM departments ";
$result = mysql_query($query, $link);

echo "<td><font size='-1'>
	<p><input type='checkbox' value=1 name='allDepts' CHECKED><b>All Departments</b><br>";
while ($row = mysql_fetch_assoc($result)) {
	echo "<input type='checkbox' name='dept[]' value='".$row['dept_no']."'>".ucwords(strtolower($row['dept_name']))."<br>";
}
echo "</p></font></td>";



			echo '</tr>
  </table>
  <table border="0" cellspacing="3" cellpadding="3">
  <tr>
          <td align="right">
                  <p><b>Date Start</b> </p>
          <p><b>End</b></p>
          </td>
          <td>			
                  <p><input type=text size=10 name=date1 id="date1">&nbsp;&nbsp;*</p>
                  <p><input type=text size=10 name=date2 id="date2">&nbsp;&nbsp;*</p>
          </td>
          <td colspan=2>
                  <p>Date format is YYYY-MM-DD</br>(e.g. 2004-04-01 = April 1, 2004)</p>
          </td>
  </tr>
  <tr> 
          <td>&nbsp;</td>
          <td> <input type=submit name=submit value="Submit"> </td>
          <td> <input type=reset name=reset value="Start Over"> </td>
          <input type="hidden" name="submitted" value="TRUE">
  </tr>
  </table>	
  </form>';
  
  //include('../src/footer.html');
	echo foot();
}

?>

