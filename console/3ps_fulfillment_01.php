<?php 
//identify this script/GUI
$localSys['scriptID']='gen_access1';
$localSys['scriptVersion']='1.0';
$localSys['pageType']='Properties Window';


//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/properties_04_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Invoices</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" href="/console/rbrfm_admin.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<style type="text/css">
.order{
	padding:5px;
	margin:0px auto;
	}
.break{
	page-break-after:always;
	}
.section{
	position:relative;
	width:45%;
	padding:5px;
	margin-right:25px;
	float:left;
	height:650px;
	}
.invHeader{
	position:absolute;
	left:100px;
	top:10px;
	padding:5px;
	}
.invAddress{
	position:absolute;
	left:100px;
	top:200px;
	padding:5px;
	}
.invDate{
	position:absolute;
	top:150px;
	left:10px;
	width:75%;
	font-size:109%;
	}
.invDetail{
	position:absolute;
	left:0px;
	top:290px;
	padding:5px;
	}
.invMessage{
	position:absolute;
	left:0px;
	top:600px;
	padding:5px;
	}
.invHeader h1{
	margin:0px;
	padding:0px;
	}
.invDate .value{
	float:right;
	}
.chTable{
	border-collapse:collapse;
	width:450px;
	}
.chTable th{
	padding:4px 7px;
	}
.chTable td{
	border:2px solid #000;
	padding:4px 7px;
	}
.firstElement{
	border-right:2px solid #000;
	}
@media screen{
	.order{
		outline:1px dotted #999;
		}
	.section{
		outline:1px dotted orange;
		}
	.invHeader{
		outline:1px dotted #ccc;
		}
	.invAddress{
		outline:1px dotted #ccc;
		}
	.invDetail{
		outline:1px dotted #ccc;
		}
	.invMessage{
		outline:1px dotted #ccc;
		}
}
</style>

<script language="JavaScript" type="text/javascript" src="/Library/js/jquery.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/global_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/common_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/forms_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/loader_04_i1.js"></script>
<script language="JavaScript" type="text/javascript">
/* periwinkle coding 2.1 */
var thispage='<?php echo $thispage?>';
var thisfolder='<?php echo $thisfolder?>';
var ctime='<?php echo $ctime?>';
var PHPSESSID='<?php echo $PHPSESSID?>';
//for nav feature
var count='<?php echo $nullCount?>';
var ab='<?php echo $nullAbs?>';
var isEscapable=1;
var isDeletable=1;
var isModal=1;
var talks=1; //whether this page broadcasts overall state changes which other pages listen for
var listens=0;

AddOnkeypressCommand("PropKeyPress(e)");

var saymode='';
function say(text){
	if(saymode!=='test')return;
	if(typeof this.i=='undefined')this.i=0;
	this.i++;
	$('#console').prepend('<div>'+new Date()+' ['+i+'] '+text+'</div>');
}
</script>

<!-- InstanceEndEditable -->
</head>

<body id="properties">
<form id="form1" name="form1" target="w2" method="post" action="/console/resources/bais_01_exe.php" onsubmit="return beginSubmit();" enctype="multipart/form-data">
<div id="header"><!-- InstanceBeginEditable name="top_nav" -->
<div class="printhide fr">
  <input type="button" name="Button" value="Close" onclick="window.close();" />
</div>
<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->
<?php

$duplicate=true;
unset($orders);
if(!($a=q("SELECT f.* FROM print_fulfillment f JOIN gen_batches_entries e ON e.Batches_ID=$Batches_ID AND e.Objects_ID=f.ID AND e.ObjectName='print_fulfillment' ORDER BY ID", O_ARRAY)))exit('Unable to locate that batch');
foreach($a as $n=>$v)$orders[$v['OrderId']][]=$v;
$i=0;
foreach($orders as $OrderID=>$thisorder){
	$i++;
	extract($thisorder[0]);
	?><div class="order<?php if($i<count($orders))echo ' break';?>">
	<?php ob_start();?>
	<div class="section firstElement">
		<div class="box invHeader">
		<h1 class="nullTop" style="font-family:Georgia, 'Times New Roman', Times, serif;">Good Bird Fulfillment</h1>
		2009 Windy Terrace<br />
		Cedar Park, TX 78613<br />
		</div>
		<div class="invDate">
		<span class="label">Date:</span><span class="value"><?php echo date('n/j/Y');?></span>
		</div>
		<div class="box invAddress">
		<h2 class="nullTop nullBottom"><?php echo $ShipFirstName.' ' . $ShipLastName;?></h2>
		<?php echo $ShipStreet1;?><br />
		<?php if($ShipStreet2)echo $ShipStreet2.'<br />';?>
		<?php echo "$ShipCity, $ShipState $ShipZip";?>
		<?php if(strtolower($ShipCountry)!=='united states')echo '<br />'.$ShipCountry;?>
		</div>
		<div class="box invDetail">
		<?php

		?>
		<table class="chTable">
		<thead>
		<tr><th style="width:35px;">Qty</th><th>Item/Description</th>
		</tr></thead>
		<tbody>
		<?php
		foreach($thisorder as $n=>$v){
			?><tr><td style="width:35px;"><?php echo $v['Qty'];?></td><td><?php echo $v['ProductName'];?></td></tr><?php echo "\n";
		}
		?>
		</tbody></table>
		<br />
		email: <?php echo $Email;?>		</div>
		<div class="box invMessage">
		THANK YOU!		</div>
	</div>
	<?php $out=ob_get_contents();
	ob_end_clean();
	echo $out;
	if($duplicate)echo str_replace(' firstElement','',$out);
	?>
	<div class="cb0"></div>
	
	
	</div>
	<?php
}
?>



<!-- InstanceEndEditable --></div><div id="footer"><!-- InstanceBeginEditable name="footer" -->
<div id="console">

</div>
<!-- InstanceEndEditable --></div>
</form>
<?php if(!$hideCtrlSection){ ?>
<div id="showTester" title="Javascript Tester" onClick="g('tester').style.display='block';">&nbsp;</div>
<div id="tester" >
	<a href="#" onClick="g('ctrlSection').style.display='block';return false;">Show Control Section</a><br />
	<textarea name="test" cols="65" rows="4" id="test">clear_form();</textarea><br />
	<input type="button" name="button" value="Test" onClick="jsEval(g('test').value);"><br />
	<textarea id="result" name="result" cols="65" rows="3" ></textarea>
</div>
<div id="ctrlSection" style="display:<?php echo $testModeC ? 'block':'none'?>">
	<iframe name="w1" src="/Library/js/blank.htm"></iframe>
	<iframe name="w2" src="/Library/js/blank.htm"></iframe>
	<iframe name="w3" src="/Library/js/blank.htm"></iframe>
	<iframe name="w4" src="/Library/js/blank.htm"></iframe>
</div>
<?php } ?>
</body>
<!-- InstanceEnd --></html><?php
page_end();
?>