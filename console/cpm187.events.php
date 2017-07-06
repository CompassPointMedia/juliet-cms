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
$updateMode='updateEventTCA1';
$insertMode='insertEventTCA1';
$deleteMode='deleteEventTCA1';
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
$ids=q("SELECT ID FROM cal_events WHERE (Active=1 AND ResourceType IS NOT NULL) OR ID='$$object' ORDER BY StartDate", O_COL);

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
<title>Flight Reservations</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" href="<?php echo $cssFolder?>rbrfm_admin.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<link href="/Library/ckeditor_3.4/_samples/sample.css" rel="stylesheet" type="text/css" />
<style type="text/css">
</style>

<script language="JavaScript" type="text/javascript" src="/Library/js/global_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/common_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/forms_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/loader_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/console/console.js"></script>
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

</script>

<!-- InstanceEndEditable -->
</head>

<body id="properties">
<form id="form1" name="form1" target="w2" method="post" action="/console/resources/bais_01_exe.php" onsubmit="return beginSubmit();" enctype="multipart/form-data">
<div id="header"><!-- InstanceBeginEditable name="top_nav" -->

<div id="headerBar1">
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
	<input name="deleteMode" type="hidden" id="deleteMode" value="<?php echo $deleteMode?>" />
	<input name="mode" type="hidden" id="mode" value="<?php echo $mode?>" />
	<input name="componentID" type="hidden" id="componentID" value="<?php echo $localSys['componentID']?>" />
	<input name="TCA_Group" type="hidden" id="TCA_Group" value="Flight Schedule" />
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
	<h2>Flight Reservations</h2>
for: <strong><?php
if($mode==$insertMode){
	if(false && 'I can schedule others'){
		//dropdown list of pilots
	}else{
		echo sun('fl');
	}
}else{
	echo sun('fl');
}
?></strong></div>

<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->



<?php if($mode==$updateMode && !$cbPresent){ ?>
<div class="fr"><input type="submit" name="Button" value="Copy this flight" onClick="return copyEvent();" /></div>
<?php } ?>
Aircraft: <?php
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
</select>

<strong><br />
Date of flight</strong>: 
<input name="StartDate" type="text" id="StartDate" value="<?php echo t($StartDate);?>" size="14" onChange="dChge(this);" />
&nbsp;&nbsp;<em class="gray">(Ending date if different):</em>
<input name="EndDate" type="text" id="EndDate" value="<?php echo t($EndDate);?>" size="14" onChange="dChge(this);" />
<br />
<br />
Time of flight: 
<select name="StartTime" id="StartTime" onChange="dChge(this);">
<option value="">&lt;Select..&gt;</option>
<?php
for($i=0;$i<=47;$i++){
	$j=1800*$i + (12*3600);
	$n=date('H:i:s',$j);
	$v=date('g:iA',$j);
	?><option value="<?php echo $n;?>" <?php echo $mode==$updateMode && date('H:i:s',strtotime($StartTime))==$n?'selected':''?>><?php echo $v?></option><?php
}
?>
</select>
to 
<select name="EndTime" id="EndTime" onChange="dChge(this);">
<option value="">&lt;Select..&gt;</option>
<?php
for($i=0;$i<=47;$i++){
	$j=1800*$i + (12*3600);
	$n=date('H:i:s',$j);
	$v=date('g:iA',$j);
	?><option value="<?php echo $n;?>" <?php echo $mode==$updateMode && date('H:i:s',strtotime($EndTime))==$n?'selected':''?>><?php echo $v?></option><?php
}
?>
</select>
<br />
Notes/Comments on flight:<br />
<textarea name="ScheduleNotes" cols="45" rows="4" id="ScheduleNotes" onChange="dChge(this)"><?php echo $ScheduleNotes?></textarea>




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