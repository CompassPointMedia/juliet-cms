<?php 
//identify this script/GUI
$localSys['scriptGroup']='';
$localSys['scriptID']='exportmanager';
$localSys['scriptVersion']='1.0';
$localSys['pageType']='Properties Window';



//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');
$hideCtrlSection=false;

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/properties_04_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo ($titleBase='Ecommerce Control Panel - ').($mode==$insertMode ? 'New Order' : 'Order #'.$InvoiceNumber)?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" href="/console/rbrfm_admin.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<style type="text/css">
body{
	background-color:#CCC;
	}
.objectWrapper {
	background-color:#CCC;
	min-height:400px;
	}
.objectWrapper1 {
	background-color:#CCC;
	min-height:400px;
	}
#header{
	height:inherit;
	border-bottom:1px dotted #000;
	position:relative;
	background-image:none;
	}
.tabs{
	margin-top:20px;
	}
</style>

<script language="JavaScript" type="text/javascript" src="/Library/js/global_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/common_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/forms_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/loader_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/contextmenus_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/dataobjects_04_i1.js"></script>
<script type="text/javascript" src="../Library/fck6/fckeditor.js"></script>
<script language="JavaScript" type="text/javascript">
/* periwinkle coding 2.1 */
var thispage='<?php echo $thispage?>';
var thisfolder='<?php echo $thisfolder?>';
var browser='<?php echo $browser?>';
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
</script>

<!-- InstanceEndEditable -->
</head>

<body id="properties">
<form id="form1" name="form1" target="w2" method="post" action="/console/resources/bais_01_exe.php" onsubmit="return beginSubmit();" enctype="multipart/form-data">
<div id="header"><!-- InstanceBeginEditable name="top_nav" -->


<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->
<style type="text/css">
#controlWrap{
	width:700px;
	margin:20px;
	background-color:#999900;
	}
#export{
	float:left;
	width:450px;
	height:300px;
	overflow:auto;
	background-color:white;
	}
#controls{
	float:right;
	width:225px;
	background-color:white;
	border:1px solid #777;
	height:300px;
	}
#export .alt{
	background-color:#f5e0c8;
	}
#export .first{
	padding:0px 0px 0px 4px;
	}
#export .last{
	padding:0px 4px 0px 0px;
	}
#export tr{
	cursor:pointer;
	}
#export td{
	border-bottom:1px dotted #333;
	padding-bottom:1px;
	}
</style>
<script language="javascript" type="text/javascript">
function exportOrders(action){
	if(action=='all' || action=='none'){
		for(var i in ids) g('export'+ids[i]).checked=(action=='all'?true:false);
		return false;
	}
	if(action=='do'){
		g('SubmitMode').value='';
		return true;
	}
	if(action=='resubmit'){
		
	}
}
</script>
<div class="objectWrapper">
	<input name="mode" type="hidden" id="mode" value="exportAll" />
	<input name="SubmitMode" type="hidden" id="SubmitMode" value="" />
	<div id="controlWrap">
		<div id="export">
		<table cellpadding="0" cellspacing="0" width="100%">
		<thead>
		<tr>
			<th class="first">&nbsp;</th>
			<th>Inv.#</th>
			<th>Date</th>
			<th>Placed By</th>
			<th class="tr last">Total</th>
		</thead>
		<tbody>
		<?php
		if($a=q("SELECT a.ID, a.InvoiceNumber, a.InvoiceDate, a.ShippingFirstName, a.ShippingLastName, SUM(b.Extension) AS Total FROM finan_invoices a, finan_transactions b WHERE a.ID=b.Invoices_ID AND a.ToBeExported=1 GROUP BY a.ID ORDER BY a.InvoiceNumber", O_ARRAY)){
			foreach($a as $v){
				extract($v);
				$ids[]=$ID;
				$i++;
				?><tr class="<?php echo fmod($i,2)?'alt':''?>" onClick="g('export<?php echo $ID?>').checked=!g('export<?php echo $ID?>').checked;">
				<td class="first"><input type="checkbox" name="export[<?php echo $ID?>]" id="export<?php echo $ID?>" value="1" checked="checked" /></td>
				<td><?php echo $InvoiceNumber?></td>
				<td><?php echo date('m/d/Y',strtotime($InvoiceDate))?></td>
				<td><?php
				echo $ShippingCompany ? $ShippingCompany : $ShippingFirstName . ' ' . $ShippingLastName;
				?></td>
				<td class="tr last"><?php echo number_format($Total,2);?></td>
				</tr><?php
			}
		}
		?>
		</tbody>
		</table>
		<script language="javascript" type="text/javascript">
		var ids=[<?php echo @implode(',',$ids);?>];
		</script>
		</div>
		<div id="controls">
			<input type="button" name="Button" value="Select All" onClick="exportOrders('all');" />
			<br />
			<input type="button" name="Button" value="Select None" onClick="exportOrders('none');" />
			<br />
			<input type="submit" name="Submit" value="Export Now" onClick="exportOrders('do');" />
			<br />
			<input type="button" name="Button" value="Cancel" onClick="window.close();" />
		</div>
	</div>

</div>
<!-- InstanceEndEditable --></div>
<div id="footer"><!-- InstanceBeginEditable name="footer" -->
&nbsp;
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