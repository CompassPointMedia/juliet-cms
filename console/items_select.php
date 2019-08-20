<?php 
/*
BAIS Login (for Kyle Area Chamber of Commerce) version 2.0 - html template 
This is improved from the Giocosa Foundation use of BAIS Login, and locations for js and css file locations have been moved closer to those for the Ecommerce Site version 4.0
*/
if(strlen($sessionid)) session_id($sessionid);
session_start();
$sessionid ? '' : $sessionid = session_id();

//identify this script/GUI
$localSys['scriptID']='generic';
$localSys['scriptVersion']='1.0';
$localSys['componentID']='main';



//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');
$hideCtrlSection=false;

if($package=q("SELECT Name, SKU, Description FROM finan_items WHERE ID=$ParentItems_ID", O_ROW)){
	//OK
}else{
	exit('unable to locate package');
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/reports_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Add Item to Package - <?php echo h($package['Name']);?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link id="cssUndoHTML" rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link id="cssSimple" rel="stylesheet" href="../site-local/mps_ec40_simple.css" type="text/css" />
<link id="cssDHTML" rel="stylesheet" href="../Library/css/DHTML/dynamic_04_i1.css" type="text/css" />

<style type="text/css">
body{
	background-color:#CCC;
	}
#header{
	height:100px;
	border-bottom:1px dotted #000;
	position:relative;
	padding:0px 0px 0px 5px;
	}
#instructions{
border:dotted 1px;
background-color:white;
padding:2px 2px 2px 2px;
}
#mainBody{
background-image:none;
}
.data913{
overflow:scroll;
height:350px;
margin:0px 25px;
background-color:white;
}
.data913 td{
cursor:pointer;
border-bottom:1px dotted #333;
}
.data913 th{
background-color:#FF9900;
color:black;
}
</style>

<script id="jsglobal" language="JavaScript" type="text/javascript" src="../Library/js/global_04_i1.js"></script>
<script id="jscommon" language="JavaScript" type="text/javascript" src="../Library/js/common_04_i1.js"></script>
<script id="jsforms" language="JavaScript" type="text/javascript" src="../Library/js/forms_04_i1.js"></script>
<script id="jsloader" language="JavaScript" type="text/javascript" src="../Library/js/loader_04_i1.js"></script>
<script id="jscontextmenu" language="JavaScript" type="text/javascript" src="../Library/js/contextmenus_04_i1.js"></script>
<script id="jsdataobjects" language="JavaScript" type="text/javascript" src="../Library/js/dataobjects_04_i1.js"></script>
<script id="jslocal" language="JavaScript" type="text/javascript">
/* periwinkle coding */
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
var UserName='<?php echo $UserName?>';

function selectItemPackage(o){
	var reg=/[^0-9]*/;
	for(var j in hl_grp['selectItemPackage'])id=j.replace(reg,'');
	if(!id){
		alert('Select an item first');
		return false;
	}
	g('ChildItems_ID').value=id;
	o.submit();
	return true;
}
</script>

<!-- InstanceEndEditable -->
</head>

<body>
<form id="form1" name="form1" target="w2" method="post" action="resources/bais_01_exe.php" onsubmit="return beginSubmit();">
<div id="header"><!-- InstanceBeginEditable name="top_nav" -->
<table width="100%">
<tr>
	<td><img src="../images/i/package.gif" /> <h2>Add Item to Package<?php echo h($package['SKU']);?></h2>
	<br /><?php echo h($package['Name']);?></td>
	<td id="instructions" width="30%"><center><img src=../images/i/alert01sm.gif  /></center>Select the item you wish to add from the list below, and click the 'Select Item' button.</td>
</tr>
</table>
<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->
<?php
if(count($_REQUEST)){
	foreach($_REQUEST as $n=>$v){
		if(substr($n,0,2)=='cb'){
			if(!$setCBPresent){
				$setCBPresent=true;
				?><!-- callback fields automatically generated --><?php
				echo "\n";
				?><input name="cbPresent" id="cbPresent" value="1" type="hidden" /><?php
				echo "\n";
			}
			if(is_array($v)){
				foreach($v as $o=>$w){
					echo "\t\t";
					?><input name="<?php echo $n?>[<?php echo is_numeric($o)? '': $o?>]" id="<?php echo $n?>[<?php echo is_numeric($o)? '': $o?>]" type="hidden" value="<?php echo h(stripslashes($w))?>" /><?php
					echo "\n";
				}
			}else{
				echo "\t\t";
				?><input name="<?php echo $n?>" id="<?php echo $n?>" type="hidden" value="<?php echo h(stripslashes($v))?>" /><?php
				echo "\n";
			}
		}
	}
}
?>
<input name="mode" type="hidden" id="mode" value="selectItemPackage" />
<input name="ParentItems_ID" type="hidden" id="ParentItems_ID" value="<?php echo $ParentItems_ID?>" />
<input name="ChildItems_ID" type="hidden" id="ChildItems_ID" value="" />
<input type="submit" name="Submit" id="selectItem" value="Select Item" disabled="disabled" onclick="return selectItemPackage(this.form);" />
<input type="button" name="Cancel" id="cancel" value="Cancel" onclick="window.close()" />
<div class="data913">
<table width="100%" border="0" cellspacing="0" cellpadding="0" summary="select only one item">
    <thead>
    <tr>
        <th scope="col">&nbsp;</th>
        <th scope="col">Item Nbr.</th>
        <th scope="col">Name</th>
        <th scope="col">Category</th>
        <th scope="col">Subcategory</th>
        <th scope="col">Description</th>
        </tr>
    <thead>
    <tbody>
    <?php
    $a=q("SELECT a.*,
IF(SUM(c.ParentItems_ID),1,0) AS IsInPackage
 FROM 
finan_items a LEFT JOIN finan_items_packages b ON a.ID=b.Items_ID 
LEFT JOIN finan_ItemsItems c ON a.ID=c.ChildItems_ID AND c.ChildItems_ID!=$ParentItems_ID WHERE 
a.Active=1 AND b.Items_ID IS NULL AND a.ID!=$ParentItems_ID
GROUP BY a.ID
ORDER BY  a.Category, a.SubCategory, a.SKU", O_ARRAY);
    if(count($a)){
        foreach($a as $n=>$v){
            extract($v);
            ?><tr  id="rii_<?php echo $ID?>" onclick="h(this,'selectItemPackage',0,0,event);g('selectItem').disabled=false;" ondblclick="h(this,'selectItemPackage',0,0,event);selectItemPackage(g('form1'));" oncontextmenu="h(this,'selectItemPackage',0,1,event);">
            <td>&nbsp;</td>
            <td><?php echo h($SKU)?></td>
            <td><?php echo h($Name)?></td>
            <td><?php echo h($Category)?></td>
            <td><?php echo h($SubCategory)?></td>
            <td><?php echo h($Description)?></td>
            </tr><?php
        }
    }
    else{
        ?><tr>
            <td colspan="5">Sorry, there are no items which can be added to this package</td>
        </tr><?php
    }
    ?>
    </tbody>
    <tfoot>
    <tr>
        <td colspan="5">Select one item from the list above</td>
    </tr>
    </tfoot>
</table>
</div>
<script type="text/javascript" language="javascript">
hl_bg['selectItemPackage']='darkslategray';
hl_txt['selectItemPackage']='white';
//declare the ogrp.handle.sort value even if blank
ogrp['selectItemPackage']=new Array();
ogrp['selectItemPackage']['sort']='';
ogrp['selectItemPackage']['rowId']='';
ogrp['selectItemPackage']['highlightGroup']='selectItemPackage';
</script>

<!-- InstanceEndEditable --></div>
<div id="footer"><!-- InstanceBeginEditable name="footer" -->
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