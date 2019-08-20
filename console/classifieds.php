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

//------------------------ Navbuttons head coding v1.41 -----------------------------
//change these first vars and the queries for each instance
$object='Ads_ID';
$recordPKField='ID'; //primary key field
$navObject='Ads_ID';
$updateMode='updateAd';
$insertMode='insertAd';
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

$ids=q("SELECT * FROM _v_publisher_ads_flat_with_order", O_COL);
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
	if($a=q("SELECT * FROM _v_publisher_ads_flat_with_order WHERE Ads_ID=".$$object, O_ROW)){
		$mode=$updateMode;
		extract($a);
		$Orders_ID=$ID;
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

//2009-02-04: this dataobject including list and focus view
$dataset='Ad';

//------- tabs coding --------
$tabPrefix='classifiedsMain';
$cg[$tabPrefix]['CGAllTabs']=array(
	'Invoice Info'	=>'invoiceinfo',
	'Card Info'	=>'cardinfo'
);
$cg[$tabPrefix]['CGLayers']=array();
foreach($cg[$tabPrefix]['CGAllTabs'] as $n=>$v){
	if(!@in_array($v,$hideTabs[$tabPrefix])){
		$cg[$tabPrefix]['CGLayers'][$n]=$v;
	}
}
if(!isset($cg[$tabPrefix]['defaultLayer'])){
	$cg[$tabPrefix]['defaultLayer']=current($cg[$tabPrefix]['CGLayers']);
}
$cg[$tabPrefix]['layerScheme']=2; //thin tabs vs old Microsoft tabs
$cg[$tabPrefix]['schemeVersion']=3.01;



$hideCtrlSection=false;
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/properties_04_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Manage Classified Ads</title>
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

/* -------- custom for ad calculator ----------- */
#entryForm{
	text-align:left;
	}
#inset{
	float:right;
	width:40%;
	border:1px solid #333;
	padding:25px 20px;
	}
#Preview{
	border:1px dotted DARKRED;
	padding:15px;
	font-family:"Times New Roman", Times, serif;
	margin:5px 0px;
	background-color:#FFF;
	font-size:109%;
	font-weight:400;
	}
#Heading{
	background-color:#000;
	color:#FFF;
	font-weight:900;
	font-family:Geneva, Arial, Helvetica, sans-serif;
	letter-spacing:.2em;
	padding:4px 15px 1px 15px;
	}
#leader{
	font-size:109%;
	word-spacing:.2em;
	font-weight:900;
	}
.sectHeader{
	background-color:THISTLE;
	border:bottom:1px solid #000;
	margin:15px 0px 3px 0px;
	}

.tabs{
	margin-bottom:-1px;
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

function wordCalc(){
	var words=0;
	var cost=0.00;
	var runtext=false;
	var reg1=/[ ,+\/]+$/g;
	var reg2=/[ ,+\/]+/g;
	var title=g('AdTitle').value.replace(reg1,'');
	var content=g('Content').value.replace(reg1,'');
	var title=title.replace(reg1,'');
	var content=content.replace(reg1,'');
	var t=title.split(reg2);
	var c=content.split(reg2);
	if(title)for(j in t)words++;
	if(content)for(j in c)words++;
	cost= (<?php echo number_format($basePrice,2)?> + (words > <?php echo $baseWords?> ? words - <?php echo $baseWords?> : 0)*<?php echo $pricePerWordExtra?> );
	cost=parseInt(cost*100)/100;
	//c=cost.toString() + (parseInt(cost)!==cost ? '0' : '.00');
	g('PriceStatic').value=cost;
	g('Words').value=words;
	g('WordsText').innerHTML=words;
	//set text
	if(g('AdTitle').value || g('Content').value)runtext=true;
	if(runtext){
		g('Heading').innerHTML=(g('Categories_ID').value ? g('Categories_ID').value.toUpperCase() : '(UNCATEGORIZED)');
		g('Preview').innerHTML='<span id="leader">'+title+'</span>'+' '+content;
	}
	setTimeout('wordCalc()',150);
}
function getClientInfo(o){
	window.open('resources/bais_01_exe.php?mode=getClientInfoClassifieds&Clients_ID='+o.value,'w2');
}
</script>
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
		echo ($mode==$insertMode ? 'Create a new ' : 'Edit ') . ' Classified Ad';
		?></h2>
	</div>
	<div class="cb" style="font-size:2px;">&nbsp;</div>

<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->
<div class="objectWrapper" style="clear:both;">
	<input name="Orders_ID" type="hidden" id="Orders_ID" value="<?php echo $Orders_ID?>" />
	<div class="fr">
		<label>
			<input type="checkbox" name="Active" value="1" <?php echo $mode==$insertMode || $Active ? 'checked':''?> onChange="dChge(this);" /> Current Ad (active)
		</label>
	</div>
	Status: <select name="Approved" id="Approved" onChange="dChge(this);">
		<option>&lt;select..&gt;</option>
		<option value="0" <?php echo $Approved=='0'?'selected':''?>>Pending</option>
		<option style="background-color:pink;" value="-1" <?php echo $Approved== -1 ? 'selected': ''?>>Rejected</option>
		<option value="1" <?php echo $Approved==1 || $mode==$insertMode?'selected':''?>>Approved</option>
	</select>
<fieldset><legend><strong>Contact Information</strong></legend>

<select name="Clients_ID" id="Clients_ID" onChange="dChge(this);getClientInfo(this);newOption(this, 'members.php', 'l1_members', '700,700');" cbtable="finan_clients" onfocus="this.setAttribute('initialIndex',this.selectedIndex);" style="width:225px;">
	<option value="">&lt;Select..&gt;</option>
	<option style="background-color:thistle;" value="{RBADDNEW}">&lt;Add new..&gt;</option>
	<?php
	$a=q("SELECT c.ID, LastName, FirstName, CompanyName FROM addr_contacts a LEFT JOIN finan_ClientsContacts b ON a.ID=b.Contacts_ID AND b.Type='Primary' LEFT JOIN finan_clients c ON b.Clients_ID=c.ID ORDER BY LastName, FirstName", O_ARRAY_ASSOC);
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
</select> [<a href="members.php?Clients_ID=" title="Edit this member information" onclick="if(!g('Clients_ID').value)return false; return ow(this.href+g('Clients_ID').value, 'l1_members','700,700');">edit</a>]<br />
<div id="addrInfo" style="padding-left:5px; visibility:<?php echo $Clients_ID ? 'visible' : 'hidden'?>">
	<?php
	$client=q("SELECT 
	c.HomeAddress, c.HomeCity, c.HomeState, c.HomeZip, c.HomePhone, c.HomeMobile
	FROM finan_clients a, finan_ClientsContacts b, addr_contacts c
	WHERE a.ID=b.Clients_ID AND b.Contacts_ID=c.ID AND a.ID='$Clients_ID'", O_ROW);
	?>
	<span id="d_address"><?php echo $client['HomeAddress']?></span><br />
	<span id="d_city"><?php echo $client['HomeCity']?></span>, <span id="d_state"><?php echo $client['HomeState']?></span> &nbsp;<span id="d_zip"><?php echo $client['HomeZip']?></span><br />
	
</div>
</fieldset>


<br />
<fieldset>
	<legend><strong>Ad Content</strong> &nbsp;&nbsp; (<a tabindex="-1" href="javascript:alert('Contact Developer to change these values for the database');"><?php echo number_format($basePrice,2);?>/wk up to <?php echo $baseWords?> words, <?php echo round($pricePerWordExtra*100,0);?> cents each additional word</a>)</legend>
	<div id="inset">
		<h2 class="nullTop">Ad preview:</h2>
		Word count: <span id="WordsText">0</span>
		<input name="Words" type="hidden" id="Words" />
		<br />
		Cost/week: $
		<input type="text" name="PriceStatic" id="PriceStatic" value="<?php
		if($PriceStatic){
			echo $PriceStatic;
		}else{
			echo number_format($basePrice,2);
		}
		/*
		if($Extension){
			echo number_format($Extension,2);
		}else if($mode==$updateMode){
			//calculate base + xtra words
			
			$a=trim(strip_tags($Content));
			$a=preg_split('/[ ,]+/',$a);
			$cost=($basePrice + (count($a)-$baseWords>0 ? count($a)-$baseWords : 0)*$pricePerWordExtra) * ceil((strtotime($EndDate) - strtotime($StartDate))/(3600*24*7));
			echo number_format($cost,2);
		}else{
			echo '14.00';
		}
		*/
		?>" /><br />
		<div id="Heading"><?php echo $mode==$insertMode ? 'REAL ESTATE' : (strlen($Category) ? $Category : '(UNCATEGORIZED)');?></div>
		<div id="Preview"><?php echo $mode==$insertMode ? '(example) <span id="leader">Homes on Acreage Ready for move-in!</span> Dale, Georgetown, Hutto, Kyle.  Call for showings. (512) 251-5614 RBI #02880' : '<span id="leader">'.$AdTitle.'</span>'.$Content?></div>
	</div>
	<?php
	//get date information	
	$dateIdx=date('w',time()); //0-6 = sun-sat
	$hourIdx=date('H',time());
	if($dateIdx==0 || ($dateIdx==1 && $hourIdx<17)){
		//cutoff is next Thursday through the following Wed
		$nextRelease=strtotime(date('Y-m-d',strtotime('next thursday')))+(24*3600*3);
	}else{
		//following Thursday
		$nextRelease=strtotime(date('Y-m-d', strtotime('next thursday')));
		if(date('Y-m-d')==date('Y-m-d',$nextRelease))$nextRelease+=(24*3600*7);
	}
	if(strlen($StartDate) && $StartDate!=='0000-00-00'){
		$seed=strtotime($StartDate);	
	}else{
		$seed=$nextRelease-(24*3600*7);
	}
	if($seed>time()){
		$seed=$nextRelease-(24*3600*7);
	}
	if(strlen($EndDate) && $EndDate!=='0000-00-000'){
		$Weeks= ceil((strtotime($EndDate)-strtotime($StartDate))/(24*3600*7));
	}
	
	?>
	Number of weeks to run: 
	<select name="Weeks" id="Weeks" onChange="dChge(this);">
	<?php 
	for($i=2;$i<=52;$i++){
		?><option value="<?php echo $i?>" <?php echo $Weeks==$i ? 'selected':''?>><?php echo $i?><?php 
	}
	?>
	</select>
	<br />
	<p>Run the ad: 
		<select name="RunMethod" id="RunMethod" onChange="dChge(this)">
		<option value="">&lt;Select..&gt;</option>
		<option value="One-time" <?php echo strtolower($RunMethod)=='one-time'?'selected':''?>>One time only</option>
		<option value="RTFN" <?php echo strtolower($RunMethod)=='rtfn'?'selected':''?>>RTFN - run till further notice</option>
		<option value="RTFN-C" <?php echo strtolower($RunMethod)=='rtfn-c'?'selected':''?>>RTFN - no billing</option>
		</select>
		<br />
		Starting on: 
	<select name="StartDate" id="StartDate" onChange="dChge(this);">
		<option value="">&lt;Select..&gt;</option>
		<?php
		while(true){
			?><option value="<?php echo date('Y-m-d',$seed)?>" <?php echo $StartDate==date('Y-m-d',$seed)?'selected':''?>><?php echo date('m/d/Y',$seed)?></option><?php
			$seed+=(24*3600*7);
			if($seed-time()>24*3600*60)break;
		}
		?>
	</select>
	<br />
	Classified Section: 
	<select name="Categories_ID" id="Categories_ID" onChange="dChge(this);">
		<option value="">&lt; Select section..&gt;</option>
		<?php
		foreach($Categories=q("SELECT ID, Name FROM publisher_ads_categories ORDER BY Name", O_COL_ASSOC) as $n=>$v){
			?><option value="<?php echo $v?>" <?php echo $Categories_ID==$n?'selected':''?>><?php echo $v?></option><?php
		}
		?>
	</select>
	<br />
	Title (these words in <strong>bold</strong>, count as words): <br />
	<input name="AdTitle" type="text" id="AdTitle" size="45" value="<?php echo h($AdTitle);?>" onChange="dChge(this);">
	<br />
	Remainder of the ad: <br />
	<textarea name="Content" cols="41" rows="6" id="Content" onChange="dChge(this);"><?php echo h($Content);?></textarea>
	<br />
	</p>


</fieldset>
<?php
//classifiedsMain
require($MASTER_COMPONENT_ROOT.'/comp_tabs_v100.php');
#overbuffer for entire layer set
ob_start(); 

?>
<?php 
//-------------------------------- first tab --------------------------
$tabNode='invoiceinfo';
if(!@in_array($tabNode,$hideTabs[$tabPrefix])){
ob_start(); 
?>
<div id="<?php echo $tabPrefix?>_<?php echo $tabNode?>" class="aArea <?php echo $tabDefault==$tabNode?'tShow':'tHide'?>" style="width:504px;border-top:1px solid #fff;">
	<div class="fr"> 
		<script language="javascript" type="text/javascript">
		function chargenow(o){
			if(<?php echo $mode==$insertMode?'true':'false'?>){
				if(!o.checked && !confirm('This will enter this ad but will not create an order. Continue?'))o.checked=true;
			}else if(o.checked && !confirm('This will charge this chard again for this ad!  Are you sure?')){
				o.checked=false;
			}
		}
		</script>
		<label><input type="checkbox" name="ChargeNow" id="ChargeNow" value="1" onClick="chargenow(this);" <?php echo $mode==$insertMode?'checked':''?> /> Charge this card <?php echo $mode==$updateMode ? 'again' : 'now'?></label>
	</div>
	<?php
	if($mode==$insertMode){

	}else{
		if($Transactions_ID){
			?>
			<table border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td>Order #:</td>
					<td><?php echo $InvoiceNumber?></td>
				</tr>
				<tr>
					<td>made:</td>
					<td><?php echo date('m/d/Y',strtotime($InvoiceDate));?></td>
				</tr>
				<tr>
					<td>Order Total: </td>
					<td><?php echo number_format(q("SELECT SUM(Extension) FROM finan_transactions WHERE Invoices_ID=$Invoices_ID AND (Payments_ID=0 OR Payments_ID IS NULL)", O_VALUE),2);?></td>
				</tr>
			</table><br />
			[<a href="orders.php?Orders_ID=<?php echo $Invoices_ID?>" onClick="return ow(this.href,'l1_orders','700,700');
">view order</a>]
			<?php
		}else{
			?>(has not been charged)<br />
			<?php
		}
	}
	?>
	<div class="cb">&nbsp;</div>
</div>
<?php
echo $layerOutput[$tabPrefix][$tabNode]=get_contents('trim');
}
//-------------------------------- next tab --------------------------
$tabNode='cardinfo';
if(!@in_array($tabNode,$hideTabs[$tabPrefix])){
ob_start();
?>
<div id="<?php echo $tabPrefix?>_<?php echo $tabNode?>" class="aArea <?php echo $tabDefault==$tabNode?'tShow':'tHide'?>" style="width:504px;border-top:1px solid #fff;">
	<fieldset><legend><strong>Credit card information</strong></legend>
	<label>
	<input tabindex="-1" type="checkbox" name="overrideCard" id="overrideCard" value="1" /> Ignore card information
	</label>
		<?php
		if($a=q("SELECT 
			BillingNameOnCard AS cardname,
			CCNumber AS cardnumber,
			CCExpYear AS expyear,
			CCExpMonth AS expmonth,
			CCBackThree,
			BillingAddress AS cardaddress,
			BillingCity AS cardcity,
			BillingState AS cardstate,
			BillingZip AS cardzip
			FROM finan_billing WHERE ID='$Billing_ID'", O_ROW)){
			extract($a);
		}
		?>
		<input name="Billing_ID" type="hidden" id="Billing_ID" value="<?php echo $Billing_ID?>" />
		Name on card:
		<input name="cardname" type="text" id="cardname" size="50" value="<?php echo $cardname?>" onChange="dChge(this);" />
		<br />
		Card number:
		<input name="cardnumber" type="text" id="cardnumber" size="50" value="<?php echo $cardnumber?>" onChange="dChge(this);" />
		<br />
		Card Expiration:
		<select name="expmonth" id="expmonth" onChange="dChge(this);" >
			<option value="">&lt; Select.. &gt;</option>
			<option <?php echo $expmonth=='01'?'selected':''?> value="01">01 Jan</option>
			<option <?php echo $expmonth=='02'?'selected':''?> value="02">02 Feb</option>
			<option <?php echo $expmonth=='03'?'selected':''?> value="03">03 Mar</option>
			<option <?php echo $expmonth=='04'?'selected':''?> value="04">04 Apr</option>
			<option <?php echo $expmonth=='05'?'selected':''?> value="05">05 May</option>
			<option <?php echo $expmonth=='06'?'selected':''?> value="06">06 Jun</option>
			<option <?php echo $expmonth=='07'?'selected':''?> value="07">07 Jul</option>
			<option <?php echo $expmonth=='08'?'selected':''?> value="08">08 Aug</option>
			<option <?php echo $expmonth=='09'?'selected':''?> value="09">09 Sep</option>
			<option <?php echo $expmonth=='10'?'selected':''?> value="10">10 Oct</option>
			<option <?php echo $expmonth=='11'?'selected':''?> value="11">11 Nov</option>
			<option <?php echo $expmonth=='12'?'selected':''?> value="12">12 Dec</option>
		</select>
		&nbsp;&nbsp;
		<select name="expyear" id="expyear" onChange="dChge(this);">
			<option value="">&lt; Select.. &gt;</option>
			<?php
			$y=date('Y')-1;
			for($i=$y; $i<=$y+11; $i++){
				?><option value="<?php echo $i?>" <?php echo $expyear==$i?'selected':''?> ><?php echo $i?></option><?php
			}
			?>
		</select>
		<br />
		CVV code: 
		<input name="CCBackThree" type="text" id="CCBackThree" size="6" value="<?php echo $CCBackThree?>" onChange="dChge(this);" />
		<br />
		Address:
		<input name="cardaddress" type="text" id="cardaddress" onChange="dChge(this);" value="<?php echo $cardaddress?>" size="30" />
		<br />
		City:
		<input name="cardcity" type="text" id="cardcity" value="<?php echo $cardcity?>" onChange="dChge(this);" />
		<br />
		State:
		<select name="cardstate" id="cardstate" onChange="dChge(this);">
			<option value="" class="ghost" style="color:#CCC;"> --State--</option>
			<?php 
			if(!$states)$states=q("SELECT st_code, st_name FROM aux_states",O_COL_ASSOC, $public_cnx);
			$gotState=false;
			foreach($states as $n=>$v){
				?><option value="<?php echo $n?>" <?php
				if($cardstate==$n){
					$gotState=true;
					echo 'selected';
				}
				?>><?php echo h($v)?></option><?php
			}
			if(!$gotState && strlen($cardstate)){ ?><option value="<?php echo $cardstate;?>" selected="selected"><?php echo h($cardstate)?></option><?php }?>
		</select>
		<br />
		Zip: <input name="cardzip" type="text" id="cardzip" value="<?php echo $cardzip?>" onChange="dChge(this);" />
		</p>
	</fieldset>
</div>
<?php
echo $layerOutput[$tabPrefix][$tabNode]=get_contents('trim');
}
//-------------------------------- end tabs --------------------------
$tabAction='layerOutput';
require($MASTER_COMPONENT_ROOT.'/comp_tabs_v100.php');
?>
</div>
<script language="javascript" type="text/javascript">
setTimeout('wordCalc()',150);
</script>

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