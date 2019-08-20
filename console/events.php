<?php 
//identify this script/GUI
$localSys['scriptGroup']='';
$localSys['scriptID']='generic';
$localSys['scriptVersion']='1.0';
$localSys['pageType']='Properties Window';

if(!$cssFolder)$cssFolder='/console/';


//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');

//mini-settings for this page
$dataset='Event';
//------------------------ Navbuttons head coding v1.41 -----------------------------
//change these first vars and the queries for each instance
$object='Events_ID';
$recordPKField='ID'; //primary key field
$navObject='Events_ID';
$updateMode='updateEvent';
$insertMode='insertEvent';
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
if($origin=='cgi'){
	$ids=q("SELECT ID FROM cal_events WHERE ((Active=1 AND ResourceType IS NOT NULL) OR ID='$$object') AND Creator='".$_SESSION['systemUserName']."' ORDER BY StartDate", O_COL);
}else{
	$ids=q("SELECT ID FROM cal_events WHERE (Active=1 AND ResourceType IS NOT NULL) OR ID='$$object' ORDER BY StartDate", O_COL);
}
/*
(another good example more complex)
$ids=q("SELECT ID FROM `$cc`.finan_invoices WHERE Accounts_ID='$Accounts_ID' ORDER BY InvoiceDate, CAST(InvoiceNumber AS UNSIGNED)",O_COL);
*/

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
if(strlen($$object) || $$object=q("SELECT ID FROM cal_events WHERE ResourceToken!='' AND ResourceToken='$ResourceToken' AND ResourceType IS NOT NULL", O_VALUE)){
	//get the record for the object
	if($a=q("SELECT * FROM cal_events WHERE ID=$Events_ID",O_ROW)){
		$mode=$updateMode;
		@extract($a);
	}else{
		//object may have been deleted by another user, least perplexing approach is to present insert mode
		$mode=$insertMode;
		unset($$object);
		$nullAbs=$nullCount+1;
	}
}else{
	$mode=$insertMode;
	$$object=$ID=quasi_resource_generic($MASTER_DATABASE, 'cal_events', $ResourceToken, $typeField='ResourceType', $sessionKeyField='sessionKey', $resourceTokenField='ResourceToken', $primary='ID', $creatorField='Creator', $createDateField='CreateDate' /*, C_DEFAULT, $options */);
	$nullAbs=$nullCount+1; //where we actually are right then
}
//--------------------------- end coding --------------------------------
foreach($blankFills as $n=>$v){
	//not used
	continue;
	if(!trim($a[$n])){
		$a[$n]=htmlentities($v);
	}
}
@extract($a);


if($settable_parameters['051']['calMultipleCalendars']){
	$inMultiple='size="4" multiple="multiple"';
	if($mode==$updateMode){
		$Cal_ID=q("SELECT c.ID, c.ID FROM cal_cal c, cal_CalEvents ce WHERE c.ID=ce.Cal_ID AND ce.Events_ID=$ID", O_COL_ASSOC);	
	}else if($Cal_ID){
		if(is_array($Cal_ID)){
			//OK
		}else{
			$e=$Cal_ID;
			unset($Cal_ID);
			$Cal_ID[$e]=$e;
		}
	}
}

$hideCtrlSection=false;

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/properties_04_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $adminCompany;?> 1.0 - Event Management</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" href="<?php echo $cssFolder?>rbrfm_admin.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<link href="/Library/ckeditor_3.4/_samples/sample.css" rel="stylesheet" type="text/css" />
<style type="text/css">
body{
	background-color:#CCC;
	}
.objectWrapper {
	min-height:400px;
	}
.expTAExt{
	width:425px;
	border:1px solid #333;
	}
input[type="text"]{
	margin-top:2px;
	}
</style>

<script language="JavaScript" type="text/javascript" src="/Library/js/jq/jquery.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/global_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/common_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/forms_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/loader_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/dataobjects_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/console/console.js"></script>
<script type="text/javascript" src="/Library/ckeditor_3.4/ckeditor.js"></script>
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
var isDeletable=2;
var isModal=1;
var talks=1; //whether this page broadcasts overall state changes which other pages listen for
var listens=0;

var UserName='<?php echo $UserName?>';
function copyEvent(){
	if(!confirm('This will copy this calendar event as a new event.  Make sure you have changed the dates and event name/description as needed.  Continue?'))return false;
	detectChange=1;
	g('mode').value='<?php echo $insertMode?>';
	g('saveAsNew').value='1';
	g('ID').value='';
	return true;
}

AddOnkeypressCommand("PropKeyPress(e)");
//var customDeleteHandler='deleteEvent()';
function deleteEvent(){
}
$(document).ready(function(){
	$('#EditProfile').click(function(){
		if(!g('MailProfiles_ID').value){
			alert('Select a mail profile first');
			return;
		}
		ow('/console/mail/mail.php?Profiles_ID='+g('MailProfiles_ID').value,'l1_mail','800,700');
	});
	$('#AllowEventSignup').change(function(){
		g('NotifyInvite').disabled=!(this.value>0);
	});
	$('#Notify').click(function(){
		g('OK').value=(this.checked?'OK and Email':'OK');
	});
	
});

</script>

<!-- InstanceEndEditable -->
</head>

<body id="properties">
<form id="form1" name="form1" target="w2" method="post" action="/console/resources/bais_01_exe.php" onsubmit="return beginSubmit();" enctype="multipart/form-data">
<div id="header"><!-- InstanceBeginEditable name="top_nav" -->

<div id="headerBar1" style="padding:5px 10px 10px 12px; background-color:#CCC;">
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
		if($insertType==2 /** advanced mode **/){
			//save
			?><input id="Save" type="button" name="Save" value="Save" onClick="focus_nav(0,'insert',1,2<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveInitiallyDisabled?> /><?php
		}
		//save and new - common to both modes
		?><input id="SaveAndNew" type="button" name="SaveAndNew" value="Save &amp; New" onClick="focus_nav(0,'insert', 1,1<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveAndNewInitiallyDisabled?> /><?php
		if($insertType==1 /** basic mode **/){
			//save and close
			?><input id="SaveAndClose" type="button" name="SaveAndClose" value="Save &amp; Close" onClick="focus_nav(0,'insert', 1,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveAndCloseInitiallyDisabled?> /><?php
		}
		?><input id="CancelInsert" type="button" name="CancelInsert" value="Cancel" onClick="focus_nav_cxl('insert');" /><?php
	}else{
		//OK, and appropriate [next] button
		?><input id="OK" type="button" name="ActionOK" value="OK" onClick="focus_nav(0,'<?php echo $mode==$insertMode?'insert':'update'?>',0,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" />
		<input id="Next" type="button" name="Next" value="Next" onClick="focus_nav(1,'<?php echo $mode==$insertMode?'insert':'update'?>',0,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $nullAbs>$nullCount || ($denyNextToNew && $nullAbs==$nullCount) ?'disabled':''?> /><?php
	}
	// *note that we could go back to the same page the 'New Record' click appeared on, but there's major issues programmatically on whether it would shift becuase of the placement of the new record.
	// *note that the primary key field is now included here to save time
	?>
	<input name="saveAsNew" type="hidden" id="saveAsNew"  />
	<input name="<?php echo $recordPKField?>" type="hidden" id="<?php echo $recordPKField?>" value="<?php echo $$object;?>" />
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
						?><input name="<?php echo $n?>[<?php echo is_numeric($o)? '': $o?>]" id="<?php echo $n?>[<?php echo is_numeric($o)? '': $o?>]" type="hidden" value="<?php echo urlencode(stripslashes($w))?>" /><?php
						echo "\n";
					}
				}else{
					echo "\t\t";
					?><input name="<?php echo $n?>" id="<?php echo $n?>" type="hidden" value="<?php echo stripslashes($v)?>" /><?php
					echo "\n";
				}
			}
		}
	}
	?>
	</div>
	<h2>Events and Activities</h2>
</div>

<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->
	<div class="objectWrapper">
		<?php if($mode==$updateMode && !$cbPresent){ ?>
		<div class="fr">
			<input type="submit" name="Button" value="Copy this Event" onClick="return copyEvent();" />
		</div>
		<?php } ?>
		<?php if(!$eventsHideCalendars){ ?>
		Calendar: <?php
		if(false){
			//dropdown list of distinct selected? nah, delete this.. 2009-12-09
		}else{
			if($inMultiple)echo '<br />';
			?><select name="Cal_ID<?php echo $inMultiple?'[]':''?>" <?php echo $inMultiple;?> id="Cal_ID" onChange="dChge(this);newOption(this, 'events_calendars.php', 'l1_calendars', '700,700');" cbtable="cal_cal">
			<option value="">&lt;Select..&gt;</option>
			<?php
			if($a=q("SELECT ID, Name FROM cal_cal ORDER BY Name", O_COL_ASSOC))
			foreach($a as $n=>$v){
				?><option value="<?php echo $n?>" <?php echo ($inMultiple ? $Cal_ID[$n] : $Cal_ID)==$n?'selected':''?>><?php echo h($v)?></option><?php
			}
			?>
			<option style="background-color:thistle;" value="{RBADDNEW}">&lt;Add new..&gt;</option>
			</select><?php
		}
		
		?>
		<?php }?>
		<br />
		Event Name: 
			<input name="Name" type="text" id="Name" value="<?php echo h($Name)?>" size="35" maxlength="75" onChange="dChge(this);" />
			<br />
			Brief Description: 
			<input name="BriefDescription" type="text" id="BriefDescription" value="<?php echo h($BriefDescription)?>" size="45" maxlength="100" onChange="dChge(this);" />
			(100 characters)
			


<?php 
ob_start(); 
?>
<label>
<input type="checkbox" name="Inactive" id="Inactive" value="1" <?php echo $mode==$updateMode && !$Active ? 'checked' : ''?> onChange="dChge(this);" />
Hide Event
</label><br />
<fieldset><legend>Event Schedule </legend>
<img src="../images/i/cal-2-35.gif" alt="cal" width="35" height="33" /> &nbsp;Start Date: 
<input name="StartDate" type="text" id="StartDate" value="<?php echo t($StartDate);?>" size="14" onChange="dChge(this);" />
<br />
Ending Date (if different):
<input name="EndDate" type="text" id="EndDate" value="<?php echo t($EndDate);?>" size="14" onChange="dChge(this);" />
<br />
<img src="../images/i/clock-1-35.gif" alt="clock" width="35" height="37" />&nbsp;Event time from 
<input name="StartTime" type="text" id="StartTime" value="<?php echo t($StartTime,f_t)?>" size="7" onChange="dChge(this);" />
to 
<input name="EndTime" type="text" id="EndTime" value="<?php echo t($EndTime,f_t)?>" size="7" onChange="dChge(this);" />
<br />
Exceptions/schedule notes:<br />
<textarea name="ScheduleNotes" cols="30" rows="4" id="ScheduleNotes" onChange="dChge(this)"><?php echo $ScheduleNotes?></textarea>
</fieldset>
<fieldset>
<legend>Event Information </legend>
Event held by: 
<script language="javascript" type="text/javascript">
function addValue(o){
	//get from SMAAC - good example
}
function selectInterlock(){
	if(g('Clients_IDText').value!='')g('Clients_ID').value='';
	if(g('Clients_ID').value!='')g('Clients_IDText').value='';
	if(g('SponsoringClients_IDText').value!='')g('SponsoringClients_ID').value='';
	if(g('SponsoringClients_ID').value!='')g('SponsoringClients_IDText').value='';
	setTimeout('selectInterlock()',100);
}
</script>
<?php
$getAllMembers=true;

$sofar=q("SELECT DISTINCT a.Clients_ID, b.ClientName, d.FirstName, d.LastName, d.Email FROM cal_events a, finan_clients b, finan_ClientsContacts c, addr_contacts d WHERE a.Clients_ID=b.ID AND b.ID=c.Clients_ID AND c.Type='Primary' AND c.Contacts_ID=d.ID ORDER BY b.ClientName", O_ARRAY_ASSOC);
$sofar=@array_merge_accurate($sofar, q("SELECT DISTINCT a.SponsoringClients_ID, b.ClientName, d.FirstName, d.LastName, d.Email FROM cal_events a, finan_clients b, finan_ClientsContacts c, addr_contacts d WHERE a.SponsoringClients_ID=b.ID AND b.ID=c.Clients_ID AND c.Type='Primary' AND c.Contacts_ID=d.ID ORDER BY b.ClientName", O_ARRAY_ASSOC));
if(count($sofar)<100 || $getAllMembers){
	if(!count($sofar))$sofar=array();
	$sofar=@array_merge_accurate($sofar, q("SELECT b.ID, b.ClientName, d.FirstName, d.LastName, d.Email FROM finan_clients b, finan_ClientsContacts c, addr_contacts d WHERE b.ID=c.Clients_ID AND c.Type='Primary' AND c.Contacts_ID=d.ID ORDER BY b.ClientName", O_ARRAY_ASSOC));
}
?>
<select name="Clients_ID" id="Clients_ID" onChange="dChge(this); if(this.value=='{RBADDNEW}')addValue('Clients_ID');">
<option value=""><?php echo $mode==$insertMode ? '&lt;Select..&gt;':'-----'?></option>
<option <?php echo $Clients_ID==='0'?'selected':''?> value="0" style="background-color:#CCC;">  (none)  </option>
<?php 

if($sofar)
foreach($sofar as $n=>$v){
	?><option value="<?php echo $n?>" <?php echo $Clients_ID==$n?'selected':''?>><?php echo h($v['ClientName']. ' - '.$v['FirstName'] . ' '.$v['LastName'])?></option><?php
}
?>
<option value="{RBADDNEW}">&lt; Add Member.. &gt;</option>
</select>
<br />
<em>(or enter here</em>:) 
<input name="Clients_IDText" type="text" id="Clients_IDText" onChange="dChge(this)" value="<?php echo $Clients_IDText?>" />
<br />
<br />
Event sponsored by: 
<select name="SponsoringClients_ID" id="SponsoringClients_ID" onChange="dChge(this); if(this.value=='{RBADDNEW}')addValue('SponsoringClients_ID');">
<option value=""><?php echo $mode==$insertMode ? '&lt;Select..&gt;':'-----'?></option>
<option <?php echo $SponsoringClients_ID==='0'?'selected':''?> value="0" style="background-color:#CCC;">  (none)  </option>
<?php 
if($sofar)
foreach($sofar as $n=>$v){
	?><option value="<?php echo $n?>" <?php echo $SponsoringClients_ID==$n?'selected':''?>><?php echo h($v['ClientName']. ' - '.$v['FirstName'] . ' '.$v['LastName'])?></option><?php
}
?>
<option value="{RBADDNEW}">&lt; Add Member.. &gt;</option>
</select>
<br />
<em>(or enter here</em>:) 
<input name="SponsoringClients_IDText" type="text" id="SponsoringClients_IDText" onChange="dChge(this)" value="<?php echo $SponsoringClients_IDText?>" />
<script language="JavaScript" type="text/javascript">
selectInterlock();
</script>
<p>
Web Link/URL: 
<input name="URL" type="text" id="URL" onChange="dChge(this)" value="<?php echo $URL?>" size="65" />
<br />
Contact Name: 
<input name="ContactName" type="text" id="ContactName" onChange="dChge(this)" value="<?php echo $ContactName?>" />
<br />
Contact Email: 
<input name="ContactEmail" type="text" id="ContactEmail" onChange="dChge(this)" value="<?php echo $ContactEmail?>" />
<br />
Contact Phone: 
<input name="ContactPhone" type="text" id="ContactPhone" onChange="dChge(this)" value="<?php echo $ContactPhone?>" />
<br />
Location: 
<input name="Location" type="text" id="Location" onChange="dChge(this)" value="<?php echo $Location?>" />
<br />
Address: 
<input name="Address" type="text" id="Address" onChange="dChge(this)" value="<?php echo $Address?>" />
<br />
City: 
<input name="City" type="text" id="City" onChange="dChge(this)" value="<?php echo $City?>" /> 
State: 
<select name="State" id="State" onChange="dChge(this);" style="width:125px;">
	<option value=""> &lt;Select..&gt; </option>
	<?php
	$gotState=false;
	$states=q("SELECT st_code, st_name FROM aux_states",O_COL_ASSOC, $public_cnx);
	foreach($states as $n=>$v){
		?><option value="<?php echo $n?>" <?php
		if($State==$n){
			$gotState=true;
			echo 'selected';
		}
		?>><?php echo h($v)?></option><?php
	}
	if(!$gotState && $State!=''){
		?><option value="<?php echo h($State)?>" style="background-color:tomato;" selected><?php echo $State?></option><?php
	}
	?>
</select>
Zip: 
<input name="Zip" type="text" id="Zip" value="<?php echo $Zip?>" size="7" onChange="dChge(this);" />
<br />
Google Map Link:<br /> 
<textarea name="GoogleMapLink" cols="55" rows="4" id="GoogleMapLink" onchange="dChge(this);"><?php echo $GoogleMapLink?></textarea>
<br />
</p>
</fieldset>
<?php
get_contents_tabsection('evEvent');
?>
<div class="fr">
	<label>
	<input name="AllowOnlinePayment" type="checkbox" id="AllowOnlinePayment" <?php echo !isset($AllowOnlinePayment) || $AllowOnlinePayment=='1'?'checked':''?> onChange="dChge(this)" value="1" />
	Allow online payment </label>
	<br />
	<label>
	<input name="AllowMultiplePurchases" type="checkbox" id="AllowMultiplePurchases" <?php echo !isset($AllowMultiplePurchases) || $AllowMultiplePurchases=='1'?'checked':''?> onChange="dChge(this)" value="1" />
	Allow multiple purchases </label>
	<br />
	no-charge key:
	<input name="NoChangeKey" type="text" id="NoChangeKey" value="<?php echo $NoChangeKey?>" size="12" maxlength="25" onChange="dChge(this);" />
	<br />
	Maximum enrollments:
	<input name="MaxEnrollments" type="text" id="MaxEnrollments" value="<?php echo $MaxEnrollments?>" size="5" onChange="dChge(this);" />
	<br />
	Deadline: 
	<input name="Deadline" type="text" id="Deadline" value="<?php echo t($Deadline)?>" size="12" onChange="dChge(this);" />
</div>
Cost:<br />
<textarea style="float:left;" name="Cost" cols="10" rows="12" id="Cost" onChange="dChge(this);"><?php echo $Cost?></textarea>
<div style="float:left;height:75px;margin-left:15px;border:1px dotted darkred;padding:15px;">
use quantity:cost, one per line, for each price break <a href="#" onClick="alert('For an event that was 35.00 per person, 60.00 for two, and 100.00 for 4, write:\n\n1:35\n2:60\n4:100');return false;">[example]</a> - for a fixed cost, just put the price in without the quantity.

</div><br />
<div class="cb">&nbsp;</div>
<?php
get_contents_tabsection('evOnlinePayment');


if(!defined('attendance_dec'))define('attendance_dec',0);
if(!defined('attendance_req'))define('attendance_req',1);
if(!defined('attendance_inv'))define('attendance_inv',2);
if(!defined('attendance_tent'))define('attendance_tent',4);
if(!defined('attendance_acc'))define('attendance_acc',8);
if(!defined('attendance_sch'))define('attendance_sch',16);
if(!defined('attendance_uns'))define('attendance_uns',32);
if(!defined('attendance_inc'))define('attendance_inc',64);
if(!defined('attendance_ok'))define('attendance_ok',128);

$attendees=q("SELECT
c.ID, c.FirstName, c.LastName, c.UserName, c.PasswordMD5, c.Email, c.Email, c.MiddleName, ce.Status, ce.EditDate
FROM addr_contacts c, addr_ContactsEvents ce WHERE c.ID=ce.Contacts_ID AND ce.Events_ID='$ID' ORDER BY ce.Status DESC, ce.EditDate DESC", O_ARRAY);
$attendances=array(
	attendance_ok=>'Attended',
	attendance_inc=>'Incomplete',
	attendance_uns=>'Unsatisfactory',
	attendance_sch=>'Scheduled',
	attendance_acc=>'Attending',
	attendance_tent=>'Tentative',
	attendance_inv=>'Invited',
	attendance_req=>'Requested',
	attendance_dec=>'Not attending',
);
?>

<table id="thisTable" class="myTable">
<thead>
<tr>
	<th>Name</th>
	<th>On</th>
	<th>Comments</th>
	<th>&nbsp;</th>
</tr>
</thead>
<?php
ob_start();
?>
<tbody>
<?php
if($attendees){
	$i=0;
	foreach($attendees as $n=>$v){
		$i++;
		if($i==1 || $buffer !== $v['Status']){
			$j=0;
			$buffer=$v['Status'];
			?><tr>
			<td colspan="100%"><h3 class="nullTop nullBottom"><?php echo $attendances[$buffer];?></h3></td>
			</tr><?php
		}
		$j++;
		?><tr class="<?php echo !fmod($j,2)?'alt':''?>">
			<td><?php
			if($v['Email']){
				?><a href="mailto:<?php echo $v['Email'];?>" title="<?php $v['Email'];?>"><?php
			}
			if($v['FirstName'] || $v['LastName']){
				echo $v['LastName'];
				echo ($v['LastName'] && $v['FirstName'] ? ', ':'');
				echo $v['FirstName'];
				echo ($v['MiddleName'] ?' '.substr($v['MiddleName'],0,1):'');
			}else if($v['Email']){
				echo $v['Email'];
			}else echo $v['UserName'];
			if($v['Email']){
				?></a><?php
			}
			?></td>
			<td><?php echo date('n/j \a\t g:iA',strtotime($v['EditDate']));?></td>
			<td><?php
			if(trim($v['Comments'])){
				echo $v['Comments'];
			}else{
				?><em class="gray">none</em><?php
			}
			?></td>
			<td>
			<input type="hidden" name="_attendance[<?php echo $v['ID'];?>]" value="<?php echo $v['Status'];?>" />
			<select name="attendance[<?php echo $v['ID'];?>]" class="attStatus minimal" onchange="dChge(this);"><?php
			foreach($attendances as $o=>$w){
				?><option value="<?php echo $o?>" <?php echo $v['Status']==$o?'selected':'';?>><?php echo $w;?></option><?php
			}
			?>
			</select></td>
		</tr><?php
	}
}else{
	?><tr>
	<td colspan="100%"><em class="gray">No attendees currently</em></td>
	</tr><?php
}
?>
</tbody>
<?php
$body=ob_get_contents();
ob_end_clean();
if(false && 'use footer'){
	?>
	<tfoot>
	<tr>
		<td><?php echo $total1;?></td>
		<td><?php echo $total2;?></td>
	</tr>
	</tfoot>
	<?php
}
echo $body;
?>
</table>
<?php
get_contents_tabsection('evAttendees');
?><div>
<?php 
if(true){
	/*
	2013-12-12: we are pulling data and using the capabilities of the mail profile tool
	what is the last profile we used for this purpose - how would I store this
	*/
	$createEventFriendlyProfile=true;
	if($profiles=q("SELECT ID, Name, 0 AS EventDescription, 0 AS EventInvitation, 2 AS friendly FROM relatebase_mail_profiles WHERE ResourceType IS NOT NULL ORDER BY Name", O_ARRAY_ASSOC)){
		foreach($profiles as $n=>$v){
			if(!($areas=q("SELECT Idx, Ky, Val, 
			IF(Val LIKE '%{events:Description%' OR Val LIKE '%{events:StartDate%' OR Val LIKE '%{events:StartTime%',1,0) AS EventDescription,
			IF(Val LIKE '%{events:Invitation%',1,0) AS EventInvitation,
			IF(Val LIKE '%{events:Description%' OR Val LIKE '%{events:StartDate%' OR Val LIKE '%{events:StartTime%' OR Val LIKE '%{events:Invitation%',1,2) AS friendly
			FROM relatebase_mail_profiles_vars WHERE Name='EditableArea' AND Profiles_ID=$n ORDER BY 
			IF(Val LIKE '%{events:Description%' OR Val LIKE '%{events:StartDate%' OR Val LIKE '%{events:StartTime%' OR Val LIKE '%{events:Invitation%',1,2)", O_ARRAY)))continue;
			foreach($areas as $o=>$w){
				if($w['EventDescription']==1){
					$profiles[$n]['EventDescription']=1;
					$createEventFriendlyProfile=false;
				}
				if($w['EventInvitation']==1){
					$profiles[$n]['EventInvitation']=1;
					$createEventFriendlyProfile=false;
				}
				if($w['EventDescription']==1 || $w['EventInvitation']==1)$profiles[$n]['friendly']=1;
			}
		}
	}else $createEventFriendlyProfile=true;
	
	if($createEventFriendlyProfile){
		if(!$_SESSION['special']['profileResourceToken'])$_SESSION['special']['profileResourceToken']=substr(date('YmdHis'),3).rand(10000,99999);
		$EventFriendlyProfiles_ID=quasi_resource_generic(array(
			'db'=>$MASTER_DATABASE,
			'table'=>'relatebase_mail_profiles',
			'ResourceToken'=>$_SESSION['special']['profileResourceToken'],
			'insertFields'=>array(
				'Name','RecipientSource','Composition','FromName','FromEmail',
			),
			'insertValues'=>array(
				'Event API Profile','complex','blank',sun('fl'),sun('e'),
			),
			'fulfillResource'=>true,
		));
		//subject
		q("INSERT INTO relatebase_mail_profiles_vars SET Profiles_ID=$EventFriendlyProfiles_ID,
		CreateDate=NOW(),
		Creator='".sun()."',
		Name='Subject',
		Idx=NULL,
		Val='{events:Name}',
		Notes='Added by events.php line ".__LINE__."'");
		//body
		q("INSERT INTO relatebase_mail_profiles_vars SET Profiles_ID=$EventFriendlyProfiles_ID,
		CreateDate=NOW(),
		Creator='".sun()."',
		Name='EditableArea',
		Idx=NULL,
		Ky='_blank_email',
		Val='{events:InvitationBasic}<br /><br />{events:StartDate}<br />{events:StartTime}<br /><br />{events:Description}',
		Notes='Added by events.php line ".__LINE__."'");
		//query
		q("INSERT INTO relatebase_mail_profiles_vars SET Profiles_ID=$EventFriendlyProfiles_ID,
		CreateDate=NOW(),
		Creator='".sun()."',
		Name='ComplexQuery',
		Idx=NULL,
		Val='SELECT * FROM addr_contacts WHERE Email!=\'\'',
		Notes='Added by events.php line ".__LINE__."'");
		//email column
		$a=q("EXPLAIN addr_contacts", O_ARRAY);
		foreach($a as $n=>$v){
			if($v['Field']!=='Email')continue;
			$col=$n;
			break;
		}
		q("INSERT INTO relatebase_mail_profiles_vars SET Profiles_ID=$EventFriendlyProfiles_ID,
		CreateDate=NOW(),
		Creator='".sun()."',
		Name='EmailColumns',
		Idx=NULL,
		Val='Column $col',
		Notes='Added by events.php line ".__LINE__."'");
		$profiles[$EventFriendlyProfiles_ID]=array(
			'Name'=>'Event API Profile',
			'EventDescription'=>1,
			'EventInvitation'=>1,
			'friendly'=>1,
		);
	}
	$profiles=subkey_sort($profiles,'friendly');


	/*
	2013-12-15: event sign-up and invitation
	this coding is also found in juliet components calendar_v200.php - they are not integrated at this point
	
	*/
	
	ob_start();
	q("SELECT AllowEventSignup FROM cal_events WHERE ID='$ID'");
	$err=ob_get_contents();
	ob_end_clean();
	if($err){
		//make event editable by default - TIGRIS
		$defaultEventSignupValue=allowsignup_open;
		q("UPDATE cal_events SET AllowEventSignup='$defaultEventSignupValue'");
	}

	?>

	
	Allow for Event Signup:
	<select name="AllowEventSignup" id="AllowEventSignup" onchange="dChge(this);">
	<option value="0" <?php echo $AllowEventSignup==='0' ?'selected':''?>>Closed to sign-ups</option>
	<option value="10" <?php echo $AllowEventSignup==allowsignup_invite || ($mode==$insertMode && $defaultEventSignupValue==allowsignup_invite)?'selected':''?>>By invitation only</option>
	<option value="20" <?php echo $AllowEventSignup==allowsignup_open || ($mode==$insertMode && $defaultEventSignupValue==allowsignup_open)?'selected':''?>>Open to join</option>
	</select>

	<br />
	<br />
	<br />

	
	<input type="hidden" name="InviteSend" value="0" />
	<label><input type="checkbox" name="Notify" value="1" id="Notify" onchange="dChge(this);" /> Notify of event by email</label>
	<br />
	<input type="hidden" name="NotifyInvite" value="0" />
	<label><input type="checkbox" name="NotifyInvite" id="NotifyInvite" value="1" onchange="dChge(this);" <?php echo isset($AllowEventSignup) && $AllowEventSignup=='0'?'disabled':''?> /> Send invitations to this event</label>
	<br />
	<br />
	<br />
	Use the following mail profile:<br />
	<select name="MailProfiles_ID" id="MailProfiles_ID" onchange="dChge(this);">
	<option value="">&lt;Select..&gt;</option><?php
	/*
	selection logic:
	
	*/
	$i=0;
	$buffer='';
	$selected='';
	foreach($profiles as $n=>$v){
		$i++;
		if($v['friendly']!=$buffer){
			if($i>1 && $v['friendly']==2)echo '</optgroup>';
			$buffer=$v['friendly'];
			?><optgroup label="<?php echo $v['friendly']==1?'Usable profiles':'(Profiles without event details)';?>"><?php
		}
		?><option value="<?php echo $n;?>" <?php 
		echo ($MailProfiles_ID==$n || (!$MailProfiles_ID && $v['friendly']==1 && !$selected)?$selected='selected':'');
		?>><?php echo $v['Name'];
		$str='';
		if($v['EventInvitation'] || $v['EventDescription'])$str=' (Has ';
		if($v['EventDescription'])$str.= 'event description, ';
		if($v['EventInvitation'])$str.= 'invitation, ';
		if($v['EventInvitation'] || $v['EventDescription'])$str=rtrim($str,' ,').')';
		echo $str;
		?></option><?php
	}
	?></optgroup>
	</select>
	<input type="button" id="EditProfile" value="Edit Profile.." />
	<?php
}else{
	?>
	<p>
	<input name="InviteSend" type="checkbox" id="InviteSend" value="1" onchange="dChge(this)" />
	Send invitations to this event </p>
	<p>Using email template: 
	<select name="InviteTemplate" id="InviteTemplate" onchange="dChge(this)">
	<option value="default">(default)</option>
	</select>
	<br />
	Send to the following groups:
	<select name="InviteList" id="InviteList" onchange="dChge(this)">
	<option value="all">(all members)</option>
	</select>
	<br />
	Email content:<br />
	
	<textarea name="InviteContent" cols="90%" id="InviteContent" style="height:16px;" class="dynExpTA expTAExt" onkeyup="ta(this,'keyup')" onfocus="ta(this,'focus');" onblur="ta(this,'blur');" onchange="dChge(this);"><?php echo h($InviteContent);?></textarea>

	<br />
	Note: the invitation link will be added automatically. [help on this] <br />
	Please be responsible and respectful about sending invitations. All emails sent will be appended with a link to unsubscribe. Future email invitations will not be sent to members who have unsubscribed.<br />
	</p>	
	<?php 
}
?>
  
</div>
<?php
get_contents_tabsection('evInvitation');
?>
For a main image for this event, the file name must start with &quot;main&quot; (ex.: main.jpg) 
<br />
For slideshow pictures, each file must start with &quot;slide&quot; (example: slide1.jpg) <br />

[<a href="#" onClick="if(<?php echo $mode==$updateMode?'true':'false'?>){ window.open('../EventCalendarItem.php?Events_ID=<?php echo $ID?>','viewevent','scrollbars,width=1000,height=700,resizable,statusbar'); return false; }else{ alert('You must first save this event before viewing it'); } return false;">View event on website</a>]<br />
[<a href="#" onClick="return ow('../admin/file_explorer/?uid=events&folder=events/event<?php echo $Events_ID?>&createFolder=1&view=fullfolder','l2_eventsimages','700,700');">Manage images for this event..</a>]<br />
<div style="float:right;">
<label><input type="checkbox" name="ShowOnlyDescription" value="1" onChange="dChge(this)" <?php echo $ShowOnlyDescription?'checked':''?> onClick="if(!this.checked)alert('This will show ONLY the contents below and not the event details (except for online payment if available');" /> Show ONLY this description on the event focus page</label>
&nbsp;&nbsp;
</div>
Description:
<textarea cols="80" id="Description" name="Description" rows="10"><?php
echo h(trim($Description) ? $Description : '<p></p>');
?></textarea>
<script type="text/javascript">
var editor = CKEDITOR.replace( 'Description' );
setTimeout('CheckDirty(\'Description\')',1000);
</script>
<?php
get_contents_tabsection('evDescription');
?>
Title (meta tag):<br />
<textarea name="MetaTitle" cols="55" rows="3" id="MetaTitle" onChange="dChge(this)"><?php echo h($MetaTitle)?></textarea>
<br />
<br />
Description (meta tag)<br />
<textarea name="MetaDescription" cols="55" rows="5" id="MetaDescription" onChange="dChge(this)"><?php echo h($MetaDescription);?></textarea>
<br />
<br />
Keywords (meta tag)<br />
<textarea name="MetaKeywords" cols="55" rows="5" id="MetaKeywords" onChange="dChge(this)"><?php echo h($MetaKeywords)?></textarea>
<br />
<br />
<br />
<?php
get_contents_tabsection('evWebMeta');
?>
<div style="float:right"><a title="Edit the contents of this page help section" onClick="return ow(this.href,'l1_helpeditor','700,700');" href="help_editor.php?node=<?php echo $cg[1]['CGPrefix']?>&amp;cbFunction=le_helpmodule&amp;cbParam[]=fixed:<?php echo $cg[1]['CGPrefix']?>">Edit Help</a></div>
<div id="pageHelpStatus">Active Help Status</div>
<div id="pageHelpRegion" class="overflowInset1" style="width:95%;height:300;margin-top:8px;padding:5px 15px;background-color:OLDLACE;border:1px dotted DARKRED;"></div>
<?php
get_contents_tabsection('help');


tabs_enhanced(array(
	'evEvent'=>array(
		'label'=>'Event Info'
	),
	'evOnlinePayment'=>array(
		'label'=>'Online Payment'
	),
	'evAttendees'=>array(
		'label'=>'Attendees'
	),
	'evDescription'=>array(
		'label'=>'Description'
	),
	'evInvitation'=>array(
		'label'=>'Notify/Invite'
	),
	'evWebMeta'=>array(
		'label'=>'Web Data'
	),
	'help'=>array(
		'label'=>'help'
	),
));
?>
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