<?php 
//identify this script/GUI
$localSys['scriptGroup']='';
$localSys['scriptID']='view-invoices';
$localSys['scriptVersion']='1.0';
$localSys['pageType']='Properties Window';



//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');
//------------ Customize the query and layout using module Config --------
if($moduleConfig){
	if(count($moduleConfig['dataobjects']['finan_invoices']['joins']))
	foreach($moduleConfig['dataobjects']['finan_invoices']['joins'] as $n=>$v){
		if($v['ReplacesField']){
			$fieldReplacements[strtolower($v['ReplacesField'])]=$n;
		}else if($v['PlaceOnTab']){
			$tabExtraFields[strtolower($v['PlaceOnTab'])][$v['FieldLabel']]=$n;
		}
	}
}

//------------------------ Navbuttons head coding v1.41 -----------------------------
//change these first vars and the queries for each instance
$object='Orders_ID';
$recordPKField='ID'; //primary key field
$navObject='Orders_ID';
$updateMode='updateOrder';
$insertMode='insertOrder';
$deleteMode='deleteOrder';
$insertType=1; //1=Save&New and Save&Close; 2 = Save and Save&New
#set these to 'disabled' if desired
$saveInitiallyDisabled='';
$saveAndNewInitiallyDisabled='';
$saveAndCloseInitiallyDisabled='';
//v1.4 change - some information about the coding
$navVer='1.41';
//v1.3 change - declare this function if you need to add parameters to the query string
$navQueryFunction=''; //nav_query_add()
//v1.3 change - deny transiting from Next to New mode (shutting off ability to insert)
$denyNextToNew=false;
//declare the query to get the idSet or subset, ordered by desired sort order - note that if you're using quasi resources, then be sure and filter them out.
#$ids=q("SELECT ID FROM finan_clients WHERE ( OnBillingSystem=1 AND Active=1 AND ResourceType IS NOT NULL ) OR ID='$$object' ORDER BY ClientName", O_COL);

$ids=q("SELECT * FROM _v_finan_invoices_cash_sales ORDER BY HeaderNumber, ID", O_COL);
$nullCount=count($ids);
$j=0;
if($nullCount){
	foreach($ids as $v){
		$j++; //starting value=1
		if($j==$abs+$nav || (isset($$object) && $$object==$v)){
			$nullAbs=$j;
			//get actual primary key if passage by abs+nav
			if(!$$object) $$object=$v;
			break;
		}
	}
}else{
	$nullAbs=1;
}
//note the coding to on ResourceToken - this will allow a submitted page to come up again if the user Refreshes the browser
if(strlen($$object)){
	//get the record for the object - filter out orders with no transactions
	if($orderHeader=q("SELECT * FROM _v_finan_invoices_cash_sales WHERE ID=".$$object, O_ROW)){
		$mode=$updateMode;
		extract($orderHeader);
		$ID=$$object;
	}else{
		//object may have been deleted by another user, least perplexing approach is to present insert mode
		$mode=$insertMode;
		$nullAbs=$nullCount+1;
	}
}else{
	$mode=$insertMode;
	$nullAbs=$nullCount+1; //where we actually are right then
}
//--------------------------- end coding --------------------------------
$hideCtrlSection=false;

//2009-02-04: this dataobject including list and focus view
$dataobject='invoices';

$states=q("SELECT st_code, st_name FROM aux_states",O_COL_ASSOC, $public_cnx);
$countries=q("SELECT ct_code, ct_name FROM aux_countries",O_COL_ASSOC, $public_cnx);


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/properties_04_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo ($titleBase='Ecommerce Control Panel - ').($mode==$insertMode ? 'New Order' : 'Order #'.$HeaderNumber)?></title>
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

function printOrder(){
	if(mode=='insertOrder'){
		alert('You can only print an invoice after you have saved it (unsaved invoice print feature to be developed; please contact RelateBase for this feature!');
		return;
	}
	alert('coming soon');
}
function client_edit(x){
	var cl=g('Clients_ID').value;
	if(x==1){
		if(cl && cl!=='{RB_ADDNEW}'){
			g('editc').src='img/a/editc1.gif'; g('editc').alt='Edit selected client information';
		}else{
			g('editc').src='img/a/editc0.gif'; g('editc').alt='';
		}
		if(cl=='{RB_ADDNEW}'){
			return ow('clients.php?cb=2','l1_client','550,575');
			g('Clients_ID').selectedIndex=0;
		}
		return false;
	}
	if(x==2){
		if(!cl || cl=='{RB_ADDNEW}')return false;
		return ow('clients.php?ID='+cl+'&cb=2','l1_client','550,575');
		return false;
	}
}
</script>

<!-- tabbed menu -->
<link rel="stylesheet" href="/Library/css/DHTML/layer_engine_v301.css" type="text/css" />
<?php
$t=$_COOKIE['tabs'.$cg[1]['CGPrefix']];
$cg['orderDetail']['CGPrefix']='orderDetail';
$cg['orderDetail']['CGLayers']=array('detail','shipping');
$cg['orderDetail']['defaultLayer']=($defaultLayer? $defaultLayer : (in_array($t, $cg['orderDetail']['CGLayers']) ? $t : 'detail'));
$cg['orderDetail']['layerScheme']=2; //thin tabs vs old Microsoft tabs
$cg['orderDetail']['schemeVersion']=3.01;
$activeHelpSystem=false;
//this will generate JavaScript, all instructions are found in this file
require($_SERVER['DOCUMENT_ROOT'].'/Library/css/DHTML/layer_engine_v301.php');
?>

<!-- InstanceEndEditable -->
</head>

<body id="properties">
<form id="form1" name="form1" target="w2" method="post" action="/console/resources/bais_01_exe.php" onsubmit="return beginSubmit();" enctype="multipart/form-data">
<div id="header"><!-- InstanceBeginEditable name="top_nav" -->
	<div id="headerBar1" style="padding:5px 10px 10px 12px;">
		<div id="btns140" style="float:right;">
		<!--
		Navbuttons version 1.41. Last edited 2008-01-21.
		This button set came from devteam/php/snippets
		Now used in a bunch of RelateBase interfaces and also client components. Useful for interfaces where sub-records are present and being worked on.
		-->
		<input id="Previous" type="button" name="Submit" value="Previous" onClick="focus_nav(-1, '<?php echo ($mode==$insertMode?'insert':'update')?>', <?php echo $mode==$insertMode?1:0?>, 0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $nullAbs<=1?'disabled':''?>>
		<?php
		//Handle display of all buttons besides the Previous button
		if($mode==$insertMode){
			$btn='Save &amp; New';
			if($insertType==2 /** advanced mode **/){
				//save
				?><input id="Save" type="button" name="Save" value="Save" onClick="focus_nav(0,'insert',1,2<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveInitiallyDisabled?> /><?php
			}
			//save and new - common to both modes
			?><input id="SaveAndNew" type="button" name="SaveAndNew" value="<?php echo $btn?>" onClick="focus_nav(0,'insert', 1,1<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveAndNewInitiallyDisabled?> /><?php
			if($insertType==1 /** basic mode **/ && !(
				$mode==$insertMode && $IsPackage
			)){
				//save and close
				?><input id="SaveAndClose" type="button" name="SaveAndClose" value="Save &amp; Close" onClick="focus_nav(0,'insert', 1,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveAndCloseInitiallyDisabled?> /><?php
			}
			?><input id="CancelInsert" type="button" name="CancelInsert" value="Cancel" onClick="focus_nav_cxl('insert');" /><?php
		}else{
			//OK, and appropriate [next] button
			?><input id="OK" type="button" name="ActionOK" value="OK" onClick="focus_nav(0,'<?php echo $mode==$insertMode?'insert':'update'?>',0,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" />&nbsp;
			<input id="Save" type="button" name="ActionOK" value="Save" onClick="focus_nav(0,'<?php echo $mode==$insertMode?'insert':'update'?>',0,2<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" />
			<input id="Next" type="button" name="Next" value="Next" onClick="focus_nav(1,'<?php echo $mode==$insertMode?'insert':'update'?>',0,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $nullAbs>$nullCount || ($denyNextToNew && $nullAbs==$nullCount) ?'disabled':''?> /><?php
		}
		// *note that we could go back to the same page the 'New Record' click appeared on, but there's major issues programmatically on whether it would shift becuase of the placement of the new record.
		// *note that the primary key field is now included here to save time
		?>
		<input name="saveAsNew" type="hidden" id="saveAsNew"  />
		<input name="<?php echo $recordPKField?>" type="hidden" id="<?php echo $recordPKField?>" value="<?php echo $ID;?>" />
		<input name="navVer" type="hidden" id="navVer" value="<?php echo $navVer?>" />
		<input name="navObject" type="hidden" id="navObject" value="<?php echo $navObject?>" />
		<input name="nav" type="hidden" id="nav" />
		<input name="navMode" type="hidden" id="navMode" value="" />
		<input name="count" type="hidden" id="count" value="<?php echo $nullCount?>" />
		<input name="abs" type="hidden" id="abs" value="<?php echo $nullAbs?>" />
		<input name="insertMode" type="hidden" id="insertMode" value="<?php echo $insertMode?>" />
		<input name="updateMode" type="hidden" id="updateMode" value="<?php echo $updateMode?>" />
		<input name="mode" type="hidden" id="mode" value="<?php echo $mode?>" />
		<input name="componentID" type="hidden" id="componentID" value="<?php echo $localSys['componentID']?>" />
		<input name="IsPackage" type="hidden" id="IsPackage" value="<?php echo $IsPackage?>" />
		<input name="OriginalCategory" type="hidden" id="OriginalCategory" value="<?php echo h($Category)?>" />
		<input name="OriginalSubCategory" type="hidden" id="OriginalSubCategory" value="<?php echo h($SubCategory)?>" />
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
		?><br />
		</div>
		<h2 style="color:white"><?php
		echo ($mode==$insertMode ? 'Create a new ' : 'Edit ') . ($IsPackage?'Package':'Order') . ' <span id="SKUText">'.$SKU .'</span>';
		?></h2>
	</div>
	<div class="cb" style="font-size:2px;">&nbsp;</div>
<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->
<div class="objectWrapper" style="clear:both;">
	<div style="float:right;">
		<?php if(!$focusViewObjects[$dataobject]['hideFields']['Accounts_ID']){ ?>
		Use Account:
		<select name="Accounts_ID" id="Accounts_ID" onChange="dChge(this)">
			<option value="">&lt; Select.. &gt;</option>
			<?php
			// N = ID, V= Name.  Maps the two fields.
			foreach(q("SELECT ID, Name FROM finan_accounts ORDER BY Name", O_COL_ASSOC) as $n=>$v){
				?><option value="<?php echo $n?>" <?php echo $n==$Accounts_ID?'selected':''?>><?php echo h($v)?></option><?php
			}
			?>
		</select>
		<?php }?>
		<br />
		<br />
		<table border="0" cellspacing="0" cellpadding="0" style="border:1px solid #BBB;border-collapse:collapse;" align="right">
			<tr>
				<th style="font-size:103%;">Date</th>
				<th style="font-size:103%;">Order #</th>
			</tr>
			<tr>
				<td><?php
				if(isset($HeaderDate) && ($x=strtotime($HeaderDate))!=-1){
					$date=date('m/d/Y',$x);
				}else{
					if(!isset($HeaderDate))$date=date('m/d/Y');
				}
				?>
				<input name="HeaderDate" type="text" id="HeaderDate" value="<?php echo $date?>" size="11" date_box="1" onChange="dChge();" /></td>
				<td><?php
				if(isset($HeaderNumber)){
					$number=$HeaderNumber;
				}else{
					$number=q("SELECT MAX(CAST(HeaderNumber AS UNSIGNED)) + 1 FROM finan_headers 
					WHERE Accounts_ID='$Accounts_ID' AND HeaderNumber REGEXP('^[0-9]+$')",O_VALUE);
				}
				?>
				<input name="HeaderNumber" type="text" id="HeaderNumber" value="<?php echo $number?>" size="9" onChange="dChge();" />
				<input name="dupeNumberIts" type="hidden" id="dupeNumberIts" /></td>
			</tr>
		</table>
		<br />
	</div>
	<?php echo $settings['ClientWord']?>:
	<select name="Clients_ID" id="Clients_ID" onChange="dChge(this);newOption(this, 'members.php', 'l1_members', '700,700');" onFocus="this.setAttribute('initialIndex',this.selectedIndex);" cbtable="finan_clients">
		<option value="">&lt;Select..&gt;</option>
		<option style="background-color:thistle;" value="{RBADDNEW}">&lt;Add new..&gt;</option>
		<?php
		$a=q("SELECT a.ID AS ListID, c.LastName, c.FirstName, a.CompanyName FROM finan_clients a LEFT JOIN finan_ClientsContacts b ON a.ID=b.Clients_ID AND b.Type='Primary' LEFT JOIN addr_contacts c ON b.Contacts_ID=c.ID WHERE a.ResourceType IS NOT NULL AND (a.Active=1 OR a.ID='$Clients_ID') ORDER BY c.LastName, c.FirstName", O_ARRAY_ASSOC);
		foreach($a as $n=>$v){
			extract($v);
			if($CompanyName && strtolower($CompanyName)!==strtolower($FirstName . ' ' . $LastName) && strtolower($CompanyName)!==strtolower($FirstName . ' ' .$MiddleName.' ' .$LastName)){
				$c=' - '.$CompanyName;
			}else{
				$c='';
			}
			?><option value="<?php echo $n?>" <?php echo $n==$Clients_ID?'selected':''?>><?php echo $LastName. ', '.$FirstName.$c?></option><?php
		}
		?>
	</select>
	<a href="client.php" class="ia" onClick="return client_edit(2);"><img src="img/a/editc<?php echo $Clients_ID?1:0?>.gif" alt="edit" width="30" height="30" border="0" align="absbottom" id="editc" /></a>
	<br />
	<fieldset>
	<legend>Address on Order:
	</legend>
	<style type="text/css">
	.grid1{
		border-collapse:collapse;
		}
	.grid1 th{
		text-align:right;
		}
	.grid1 td{
		padding:1px 0px 0px 7px;
		}
	</style>
	<table border="0" cellspacing="0" cellpadding="0" class="grid1">
		<tr>
			<th>Name:</th>
			<td><input name="ShippingFirstName" id="ShippingFirstName" type="text" size="15" onChange="dChge();" value="<?php echo h($ShippingFirstName)?>" />
				<input name="ShippingLastName" id="ShippingLastName" type="text" size="15" onChange="dChge();" value="<?php echo h($ShippingLastName)?>" /></td>
		</tr>
		<tr>
			<th>Address:</th>
			<td><input name="ShippingAddress" id="ShippingAddress" type="text" size="30" onChange="dChge();" value="<?php echo h($ShippingAddress)?>" /></td>
		</tr>
		<tr>
			<th>Address2:</th>
			<td><input name="ShippingAddress2" id="ShippingAddress2" type="text" size="30" onChange="dChge();" value="<?php echo h($ShippingAddress2)?>" /></td>
		</tr>
		<tr>
			<th>City:</th>
			<td><input name="ShippingCity" id="ShippingCity" type="text" size="20" onChange="dChge();" value="<?php echo h($ShippingCity)?>" /></td>
		</tr>
		<tr>
			<th>State:</th>
			<td><select name="ShippingState" id="ShippingState" onChange="dChge(this);countryInterlock('ShippingState','ShippingState','ShippingCountry');" style="width:125px;">
				<option value="" class="ghost"> &lt;Select..&gt; </option>
				<?php
		$gotState=false;
		foreach($states as $n=>$v){
			?>
				<option value="<?php echo $n?>" <?php
			if($ShippingState==$n){
				$gotState=true;
				echo 'selected';
			}
			?>><?php echo h($v)?></option>
				<?php
		}
		if(!$gotState && $ShippingState!=''){
			?>
				<option value="<?php echo h($ShippingState)?>" style="background-color:tomato;" selected="selected"><?php echo $ShippingState?></option>
				<?php
		}
		?>
			</select></td>
		</tr>
		<tr>
			<th>Zip:</th>
			<td><input name="ShippingZip" id="ShippingZip" type="text" size="9" onChange="dChge();" value="<?php echo h($ShippingZip)?>" /></td>
		</tr>
		<tr>
			<th>Country:</th>
			<td><select name="ShippingCountry" id="ShippingCountry" onChange="dChge(this);countryInterlock('ShippingCountry','ShippingState','ShippingCountry');" style="width:125px;">
				<option value="" class="ghost"> &lt;Select..&gt; </option>
				<?php
		$gotCountry=false;
		foreach($countries as $n=>$v){
			?>
				<option value="<?php echo $n?>" <?php
			if($ShippingCountry==$n){
				$gotCountry=true;
				echo 'selected';
			}
			?>><?php echo h($v)?></option>
				<?php
		}
		if(!$gotCountry && $ShippingCountry!=''){
			?>
				<option value="<?php echo h($ShippingCountry)?>" style="background-color:tomato;" selected="selected"><?php echo $ShippingCountry?></option>
				<?php
		}
		?>
			</select></td>
		</tr>
	</table>
	</fieldset>

	Notes:
	<input name="Notes" type="text" size="40" onChange="dChge();" value="<?php echo h($Notes)?>" />
	<?php if($adminControls['modifyOrders']){ ?>
	<br />
	<label><input name="ToBeExported" type="checkbox" id="ToBeExported" value="1" <?php echo !isset($ToBeExported) || $ToBeExported?'checked':''?> onChange="dChge(this);" /> 
	To be exported </label>
	[<a href="resources/bais_01_exe.php?mode=exportOrder&Orders_ID=<?php echo $ID?>" title="export this specific order right now" target="w2">export</a>] 
	<?php } ?><br />
	<div class="tabs">
		<table cellpadding="0" cellspacing="0">
			<tr>
				<td style="vertical-align:bottom;">
					<div id="orderDetail_a_detail" class="ab <?php echo $cg['orderDetail']['defaultLayer']=='detail' ? 'tShow':'tHide'?>">Order Detail</div>
					<div id="orderDetail_i_detail" class="ib <?php echo $cg['orderDetail']['defaultLayer']=='detail' ? 'tHide':'tShow'?>" onClick="hl_1('orderDetail',orderDetail,'detail');">Order Detail</div></td>
				<td style="vertical-align:bottom;">
					<div id="orderDetail_a_shipping" class="ab <?php echo $cg['orderDetail']['defaultLayer']=='shipping' ? 'tShow':'tHide'?>">Shipping</div>
					<div id="orderDetail_i_shipping" class="ib <?php echo $cg['orderDetail']['defaultLayer']=='shipping' ? 'tHide':'tShow'?>" onClick="hl_1('orderDetail',orderDetail,'shipping');">Shipping</div></td>
			</tr>
		</table><input name="orderDetail_status" id="orderDetail_status" type="hidden" value="" />
	</div>
	<div id="orderDetail_detail" class="aArea <?php echo $cg['orderDetail']['defaultLayer']=='detail' ? 'tShow':'tHide'?>" style="min-height:300px;">
		<?php require('components/comp_21_orderdetail_v200.php')?>
	</div>
	<div id="orderDetail_shipping" class="aArea <?php echo $cg['orderDetail']['defaultLayer']=='shipping' ? 'tShow':'tHide'?>" style="min-height:300px;">
		<?php require('components/comp_22_orderdetail_shipping_v200.php')?>
	</div>
	<?php echo $settings['ClientWord']?> Message:
	<input name="ClientMessage" type="text" onChange="dChge();" value="<?php echo h($ClientMessage)?>" size="40" />
	<br />
	Order Summary:
	<input name="InvoiceSummary" type="text" onChange="dChge();" value="<?php echo h($InvoiceSummary)?>" size="40"  maxlength="255" />
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