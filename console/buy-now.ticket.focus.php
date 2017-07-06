<?php 
//identify this script/GUI
$localSys['scriptGroup']='';
$localSys['scriptID']='generic';
$localSys['scriptVersion']='1.0';
$localSys['pageType']='Properties Window';

//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');
$_thisnode_=36;
$JULIET_COMPONENT_ROOT=$_SERVER['DOCUMENT_ROOT'].'/components-juliet';
require($FUNCTION_ROOT.'/group_pJ_v100.php');
require($JULIET_COMPONENT_ROOT.'/buy-now.php');

//------------------------ Navbuttons head coding v1.43 -----------------------------
//change these first vars and the queries for each instance
$object='Tickets_ID';
$recordPKField='ID'; //primary key field
$navObject='Tickets_ID';
$updateMode='updateTicket';
$insertMode='insertTicket';
$deleteMode='deleteTicket';
$insertType=1; //1=Save&New and Save&Close; 2 = Save and Save&New
#set these to 'disabled' if desired
$saveInitiallyDisabled='';
$saveAndNewInitiallyDisabled='';
$saveAndCloseInitiallyDisabled='';
//v1.4 change - some information about the coding
$navVer='1.43';
//v1.3 change - declare this function if you need to add parameters to the query string
$navQueryFunction='nav_query_add()';
//v1.3 change - deny transiting from Next to New mode (shutting off ability to insert)
$denyNextToNew=false;
//declare the query to get the idSet or subset, ordered by desired sort order - note that if you're using quasi resources, then be sure and filter them out.
$ids=q("SELECT ID FROM _v_rv_tickets WHERE 1",O_COL);
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
if(strlen($$object) /* || $Assns_ID=q("SELECT ID FROM sma_assns WHERE ResourceToken!='' AND ResourceToken='$ResourceToken' AND ResourceType IS NOT NULL", O_VALUE)*/){
	//get the record for the object
	if($record=q("SELECT * FROM _v_rv_tickets a WHERE a.ID='".$$object."'",O_ROW)){
		$mode=$updateMode;
		$record['Settings']=unserialize(base64_decode($record['Settings']));
		@extract($record);
		$data=$Settings['data'];
		@$refcheck=unserialize(base64_decode($record['Survey']));
	}else{
		//object may have been deleted by another user, least perplexing approach is to present insert mode
		exit('no insert mode');
		$mode=$insertMode;
		unset($$object);
		$nullAbs=$nullCount+1;
	}
}else{
	exit('no insert mode');
	$mode=$insertMode;
	$nullAbs=$nullCount+1; //where we actually are right then
}
//--------------------------- end coding --------------------------------
($level=$record['Level']);
($verif=$record['Verif']);
$data=$Settings['data'];

$package=$verify[$level][$verif];

$hideCtrlSection=false;

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/properties_04_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $titleBase='Ticket Management '.($mode==$insertMode ? ' - Adding New Ticket':'')?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" href="/console/rbrfm_admin.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<style type="text/css">
body, td{
	font-size:14px;
	}
.yat td{
	border-bottom:1px solid #ccc;
	}
#header{
	background-color:#F9F0DB;
	}
.optionBox{
	margin-top:15px;
	border:1px solid #ccc;
	border-radius:15px;
	padding:15px;
	}
#btns40{
	display:none;
	}
</style>
<script language="JavaScript" type="text/javascript" src="/Library/js/global_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/common_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/forms_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/loader_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/contextmenus_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/dataobjects_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/jquery.js"></script>
<script type="text/javascript" src="../Library/ckeditor_3.4/ckeditor.js"></script>
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

AddOnkeypressCommand("PropKeyPress(e)");
//var customDeleteHandler='deleteItem()';
$(document).ready(function(){
	$('#OK').click(function(){ g('subsubmode').value='updateTicket'; });
	$('#updateApplication').click(function(){ g('subsubmode').value='updateApplication'; });
	$('#updateRefcheck').click(function(){ g('subsubmode').value='updateRefcheck'; });
	$('#finalizeRefcheck').click(function(){ g('subsubmode').value='finalizeRefcheck'; });	
	$('#addCall').click(function(){ g('subsubmode').value='addCall'; });	
});
</script>

<!-- InstanceEndEditable -->
</head>

<body id="properties">
<form id="form1" name="form1" target="w2" method="post" action="/console/resources/bais_01_exe.php" onsubmit="return beginSubmit();" enctype="multipart/form-data">
<div id="header"><!-- InstanceBeginEditable name="top_nav" -->
Logged in: <?php echo sun('fl');?>
<div id="btns140" class="fr"><?php
ob_start();
?>
<input id="Previous" type="button" name="Submit" value="Previous" class="navButton_A" onclick="focus_nav(-1, '<?php echo ($mode==$insertMode?'insert':'update')?>', <?php echo $mode==$insertMode?1:0?>, 0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $nullAbs<=1?'disabled':''?> >
<?php
//Handle display of all buttons besides the Previous button
if($mode==$insertMode){
	if($insertType==2 /** advanced mode **/){
		//save
		?><input id="Save" type="button" name="Submit" value="Save" class="navButton_A" onclick="focus_nav(0,'insert',1,2<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveInitiallyDisabled?>><?php
	}
	//save and new - common to both modes
	?><input id="SaveAndNew" type="button" name="Submit" value="Save &amp; New" class="navButton_A" onclick="focus_nav(0,'insert', 1,1<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveAndNewInitiallyDisabled?>><?php
	if($insertType==1 /** basic mode **/){
		//save and close
		?><input id="SaveAndClose" type="button" name="Submit" value="Save &amp; Close" class="navButton_A" onclick="focus_nav(0,'insert', 1,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveAndCloseInitiallyDisabled?>><?php
	}
	?><input id="CancelInsert" type="button" name="Submit" value="Cancel" class="navButton_A" onclick="focus_nav_cxl('insert');"><?php
}else{
	//OK, and appropriate [next] button
	?>
<input id="Next" type="button" name="Submit" value="Next" class="navButton_A" onclick="focus_nav(1,'<?php echo $mode==$insertMode?'insert':'update'?>',0,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $nullAbs>$nullCount || ($denyNextToNew && $nullAbs==$nullCount) ?'disabled':''?>><?php
}
$navbuttons=ob_get_contents();
ob_end_clean();
//2009-09-10 - change button names, set default as =submit, hide unused buttons
if(!$addRecordText)$addRecordText='Add Record';
if(!isset($navbuttonDefaultLogic))$navbuttonDefaultLogic=true;
if($navbuttonDefaultLogic){
	$navbuttonSetDefault=($mode==$insertMode?'SaveAndNew':'OK');
	if($cbSelect){
		$navbuttonOverrideLabel['SaveAndClose']=$addRecordText;
		$navbuttonHide=array(
			'Previous'=>true,
			'Save'=>true,
			'SaveAndNew'=>true,
			'Next'=>true,
			'OK'=>true
		);
	}
}
$navbuttonLabels=array(
	'Previous'		=>'Previous',
    'Save'			=>'Save',
    'SaveAndNew'	=>'Save &amp; New',
    'SaveAndClose'	=>'Save &amp; Close',
    'CancelInsert'	=>'Cancel',
    'OK'			=>'OK',
    'Next'			=>'Next'
);
foreach($navbuttonLabels as $n=>$v){
	if($navbuttonOverrideLabel[$n])
	$navbuttons=str_replace(
		'id="'.$n.'" type="button" name="Submit" value="'.$v.'"', 
		'id="'.$n.'" type="button" name="Submit" value="'.h($navbuttonOverrideLabel[$n]).'"', 
		$navbuttons
	);
	if($navbuttonHide[$n])
	$navbuttons=str_replace(
		'id="'.$n.'" type="button"',
		'id="'.$n.'" type="button" style="display:none;"',
		$navbuttons
	);
}
if($navbuttonSetDefault)$navbuttons=str_replace(
	'<input id="'.$navbuttonSetDefault.'" type="button"', 
	'<input id="'.$navbuttonSetDefault.'" type="submit"', 
	$navbuttons
);
echo $navbuttons;

// *note that we could go back to the same page the 'New Record' click appeared on, but there's major issues programmatically on whether it would shift because of the placement of the new record.
// *note that the primary key field is now included here to save time
?>
<input name="<?php echo $recordPKField?>" type="hidden" id="<?php echo $recordPKField?>" value="<?php echo $$object;?>" />
<input name="navVer" type="hidden" id="navVer" value="<?php echo $navVer?>" />
<input name="navObject" type="hidden" id="navObject" value="<?php echo $navObject?>" />
<input name="nav" type="hidden" id="nav" />
<input name="navMode" type="hidden" id="navMode" value="" />
<input name="count" type="hidden" id="count" value="<?php echo $nullCount?>" />
<input name="abs" type="hidden" id="abs" value="<?php echo $nullAbs?>" />

<input type="hidden" name="location" id="location" value="JULIET_COMPONENT_ROOT" />
<input type="hidden" name="file" id="file" value="buy-now.php" />
<input type="hidden" name="mode" id="mode" value="componentControls" />
<input name="submode" type="hidden" id="submode" value="staffActions" />
<input name="subsubmode" type="hidden" id="subsubmode" />


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
					?><input name="<?php echo $n?>[<?php echo is_numeric($o)? '': $o?>]" id="<?php echo $n?>[<?php echo is_numeric($o)? '': $o?>]" type="hidden" value="<?php echo stripslashes($w)?>" /><?php
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
<input id="OK" type="submit" name="Submit2" value="OK" class="navButton_A" />
<!-- end navbuttons 1.43 --></div>

<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->
<div class="fr">
Status: <strong><?php echo $statuses[$Status];?></strong>
</div>
Name: <strong><?php echo $LastName.', '.$FirstName;?></strong>
<br />
Ticket #<strong><?php echo $HeaderNumber.'-'.$Transactions_ID;?></strong><br />
<div id="attributes" class="fr">
Package type: <strong><?php echo $levels[$level]['title'];?></strong><br />
Follow up type: <strong><?php echo $verifs[$verif]['title'];?></strong><br />
</div>
<strong>Employer or Contact Name</strong>: <?php echo $data['supervisorName']?$data['supervisorName']:$data['referenceName'];?><br />
<br />
<div class="fl"><strong>Contact Info</strong>:</div>
<div class="fl">
<?php if($data['previousEmployerName'])echo $data['previousEmployerName'].'<br />';?>
<?php if($n=$data['previousEmployerAddress'])echo $n . '<br />';?>
<?php echo $data['previousEmployerCity'].($data['previousEmployerCity'] && $data['previousEmployerState'] ? ', ' : '').$data['previousEmployerState'].'&nbsp;&nbsp;'.$data['previousEmployerZip'];?><br />
<?php
if($level==1){
	?>Contact phone: <?php echo $data['referencePhone'];?><br />
	Email address: <?php echo $data['referenceEmail'];?><br />
	<?php
}else{
	?>
	<?php if($data['companyPhone'])echo $data['companyPhone'].'(p)';?>&nbsp;
	<?php if($data['companyFax'])echo $data['companyFax'].'(f)<br />';?>
	<?php if($data['supervisorPhone'] && $data['supervisorPhone']!==$data['companyPhone'])echo 'Supervisor phone: '.$data['supervisorPhone'];?>
	<?php
}

?>
</div>
<div class="cb0"></div>
<?php 
if($others=q("SELECT CONCAT(h.HeaderNumber,'-',t2.ID), t2.ID FROM 
	finan_headers h JOIN finan_transactions t ON h.ID=t.Headers_ID AND h.Accounts_ID!=t.Accounts_ID
	JOIN rv_tickets t2 ON t.ID=t2.Transactions_ID
	WHERE h.ID=$Headers_ID AND t2.ID!=$ID", O_COL_ASSOC)){ 
	?><span class="gray">(Other tickets: <?php
	//show others on this order
	// -135, -136, -137
	$i=0;
	foreach($others as $v){
		$i++;
		if($i<1)echo ', ';
		?><a href="/console/buy-now.ticket.focus.php?Tickets_ID=<?php echo $v;?>"><?php echo $n;?></a><?php
	}
	?>)</span><?php
}
?>

<?php
ob_start();
?><div>
<p>History is a list of all actions and status changes for this ticket.  To log a call click on Call Log/Actions and enter the information regarding the call.  Be sure and indicate whether it is an incoming or outgoing call</p>
<?php
ticketHistory($Tickets_ID);
?>
</div><?php
get_contents_tabsection('History');

?><div>
  <div class="optionBox">
<strong>New Call</strong><br />
Call happened at <input type="text" name="callTime" id="callTime" onchange="dChge(this);" /> <input type="button" name="button" value="Now.." onclick="g('callTime').value=new Date();" /><br />
<label><input type="radio" name="CallIncoming" id="CallIncoming0" value="0" checked onchange="dChge(this);" /> Outgoing</label>
&nbsp;&nbsp;
<label><input type="radio" name="CallIncoming" value="1" onchange="dChge(this);" /> Incoming</label><br />
Status of call: <select name="CallAnswered" id="CallAnswered" onchange="dChge(this);">
<option value="">&lt;Select..&gt;</option>
<?php
foreach($callStatuses as $n=>$v){
	?><option value="<?php echo $n;?>"><?php echo $v;?></option><?php
}
?></select>
<br />
Spoke to: <input type="text" name="Recipient" id="Recipient" value="" onchange="dChge(this);" /><br />
Call notes:<br />
<textarea name="Comments" id="Comments" rows="3" cols="55" onchange="dChge(this);"></textarea>
<input type="submit" name="addCall" id="addCall" value="Add Call" />
</div>
</div><?php
get_contents_tabsection('Calls');
?><div>
<?php

ob_start();
?><input type="submit" name="updateApplication" id="updateApplication" value="Update Application Data" /><?php
require($JULIET_COMPONENT_ROOT.'/buy-now.requestform.php');
$out=ob_get_contents();
ob_end_clean();
echo form_field_translator($out,array(
	'arrayString'=>'data',
));

?>
</div><?php
get_contents_tabsection('Application');
?><div>

<?php
if(!$package['refcheck']){
	?><div class="red">Unable to locate reference check form for this package! You need to develop this IMMEDIATELY!!</div><?php
}else{
	ob_start();
	?><input type="submit" name="updateRefcheck" id="updateRefcheck" value="Update Reference Check" />
	&nbsp;&nbsp;
	<input type="submit" name="finalizeRefcheck" id="finalizeRefcheck" value="Finalize Reference Check" onclick="if(!confirm('Are you sure you want to finalize your results? They must be complete')){return false;}" />
	<?php
	//Need to delete
	//$package['refcheck']='refcheck5';
	$refcheckDisposition='mail';
	require($JULIET_COMPONENT_ROOT.'/buy-now.'.$package['refcheck'].'.php');
	$out=ob_get_contents();
	ob_end_clean();
	echo form_field_translator($out,array(
		'arrayString'=>'refcheck',
	));
}
?></div><?php
get_contents_tabsection('refcheckResponse');
?><div>
documents associated with this ticket will be located here
</div><?php
get_contents_tabsection('Documents');
?><div>
Additional notes on ticket/research here:
textarea - update

</div><?php
get_contents_tabsection('Notes');

?><div>
<?php
prn($_SESSION);
prn($Settings);
?></div><?php
get_contents_tabsection('Help');

tabs_enhanced(array(
	'History'=>array(),
	'Application'=>array(),
	'refcheckResponse'=>array(
		'label'=>'Ref.Check Response',
	),
	'Calls'=>array(
		'label'=>'Call Log/Actions',
	),
	'Documents'=>array(),
	'Notes'=>array(),
	'Help'=>array(),
),
array('fade'=>true)
);

?>

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