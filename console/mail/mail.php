<?php 
/*
2013-12-19

I am now beginning on the history aspect.  I'm putting together a folder on mail profile concepts if not one already.  The data for this is spread out all over the place, and there are several independent concepts
	emails are "content" as are pages and article, so the viewing of them by contacts is part of a larger statistic-taking operation
	bounce emails are also an independent operation, anytime the -f switch is mailprocessor@mydomain.com
	email actions need to be integrated in by a protocol of some kind
	
2013-10-24
began in earnest to clean up code, including:
	revived batch sending and batch fields - they were pretty good
	added AttachmentString for sending attachments
	no batch created in test mode
bugs
	large attachments cause crash for entry
	
2011-06-10 - fought to get things saved and working OK. Next most important items:
	* BatchesContacts insert and relatebase_content_batches is not working
	DONE 2013-10-24	* have mail go into mailqueue
	* have tables self-repair and clean-up
	* show a history of the mailing!!! so I can see what I've done
	* have an unsubscribe hub
	* have a basic template layout (just one region for now)
	* improve the appearance some
todo:
2013-10-24
	batch report email:
		what was the batch name
		what was this batch name
		format is horrible - make it look like Georgia and colors
		links to stuff
		send the email entirely as an attachment in this email - something that can be viewed by Outlook/Thunderbird
		lose the support@relatebase.com
		reply-to is blank as is bounce emails sent to etc.
	relatebase_content_batches table:
		batchnumber is really ugly
		
		
	inset background like constant contact at top
	attachments integrated with FEX and logic
	test logical expressions and document this
	see if i can save this in CMSB as a pseudo-page so I can roll back
	
2011-06-02 [following is also in console/docs/_changelog.txt]
* MAIN QUESTION IS, COULD A RANK AMATEUR GET IN HERE AND USE THIS THING
* success on the mailer; used for 2nd TSA flying club email; I have been able to save and send out with both mail merge fields and logic.  Also, set up in cpm155 (TSA) file m.php which serves as a validator for the email (IF they receive remote content), and fills in globals and user agent.  There are several things that I observe from this experience:
	1. it took a long time :)
	2. we are dealing with email validity
	3. we also need a way to forward bounce notices to an automated process like console.cpm155@tsaviation.com with a code in the subject line, which will update the email validity between 0=unknown, 1=validated, -255=BOUNCED, or -128=delayed
	4. NEXT STEP, the following needs done:
		aa. SQL queries are nice but I need to globalize some settings:
			* my contacts [if in a KNO environment]
			* members
			* all my friends
			* specific groups
		bb. send the mail to the mailqueue, make the history tab USABLE;
		cc. have the list view present; add as one tab of several the other being mailer history across all profiles, very nice and readable, lots of analytics on it.
		a. ability to unsubscribe in a very fine-grained method (including, do not receive email FROM {THIS USER} - so consider the variable environment - means individual logins into the console
		b. working on the URL's
			* add http://www.tsaviation to them if not absolute already
			* add to the query string, so that the receiving (Juliet) page validates the campaign
		c. from here to move to a one-section template
			* control stylesheet
			* control layout or store somewhere
	5. I want to compete with Constant Contact
	6. I want a general mail log, kind of like a readers list, which SPANS ACROSS *ALL* PROFILES, so I can see history on any of them
	7. ability to attach a ics calendar file, as Jenn sends me :)

2011-06-01
	* note that because of resource token, Profiles_ID=0 probably is not meaningful anymore
	* had a thought about making BatchesContacts.Status be a SET, multiple values OK
2011-05-28
	* remove refs to this table: relatebase_mail_batches_logs
	DONE	* HTMLOrText - is only set to 1, we need to be able to also send plain-text emails
	* pull Content from database on to THIS page
	* make the content dynamic on this page - retain all functionality of the pop-up window.
	* on mailer_profile_process_v100 - relatebase_content_batches entry needs ContentControlFieldHash to be specified vs all the individual fields that used to go into relatebase_mail_profile_batches
	* put batch report in emails/ folder
	* there is a huge problem with _emailColumns - conflict between text key versus number/integer key, and the fact that we have this needed from both a file import and an SQL query

*/
//identify this script/GUI
$localSys['scriptGroup']='mailer';
$localSys['scriptID']='MPM';
$localSys['scriptVersion']='2.2.0';
$localSys['pageType']='Properties Window';

//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');

require_once($_SERVER['DOCUMENT_ROOT'].'/console/resources/bais_00_includes_mail.php');

$RecipientSource='group';

//relatebase_BatchesContacts.Status values - 2013-12-19
if(!defined('mailstatus_sent'))define('mailstatus_sent',1);
if(!defined('mailstatus_apparentlyreceived'))define('mailstatus_apparentlyreceived',2);
if(!defined('mailstatus_returnreceipt'))define('mailstatus_returnreceipt',4);
if(!defined('mailstatus_repliedto'))define('mailstatus_repliedto',8);
if(!defined('mailstatus_viewed'))define('mailstatus_viewed',16);

//previous values - overkill
#if(!defined('mailstatus_willnotattend'))define('asdf',1);
#if(!defined('mailstatus_mayattend'))define('asdf',1);
#if(!defined('mailstatus_willattend'))define('asdf',1);
#if(!defined('mailstatus_scheduled'))define('asdf',1);
#if(!defined('mailstatus_attended'))define('asdf',1);
#if(!defined('mailstatus_failed'))define('asdf',1);
#if(!defined('mailstatus_ok'))define('asdf',1);
#if(!defined('mailstatus_passed'))define('asdf',1);

//for sub-tabs
function cg($fn, $group, $layer, $default=0){
	global $cgrp;
	if($cgrp[$group]==$layer || $default==1){
		$def=1;
	}
	if($fn=='ab' || $fn=='l'){
		$a='block';$b='none';
	}elseif($fn=='ib'){
		$a='none';$b='block';
	}
	//if we have a control group, all other layers are hidden
	if(isset($cgrp[$group]) && $cgrp[$group]!==$layer){
		return "display:$b;";
	}
	if(
		$_POST['null'.$group.'_status']==$layer || 
		(!isset($_POST['null'.$group.'_status']) && $def)
		){
		return "display:$a;";
	}else{
		return "display:$b;";
	}
}

q_tools(array(
	'mode'=>'field_exists',
	'table'=>'relatebase_mail_profiles',
	'field'=>'AttachmentString',
));

//------------------------ Navbuttons head coding v1.43 -----------------------------
//change these first vars and the queries for each instance
$object='Profiles_ID';
$recordPKField='ID'; //primary key field
$navObject='Profiles_ID';
$updateMode='updateMailProfile';
$insertMode='insertMailProfile';
$deleteMode='deleteMailProfile';
$insertType=2; //1=Save&New and Save&Close; 2 = Save and Save&New
#set these to 'disabled' if desired
$saveInitiallyDisabled='';
$saveAndNewInitiallyDisabled='';
$saveAndCloseInitiallyDisabled='';
//v1.4 change - some information about the coding
$navVer='1.43';
//v1.3 change - declare this function if you need to add parameters to the query string
$navQueryFunction='nav_query_add2();';
//v1.3 change - deny transiting from Next to New mode (shutting off ability to insert)
$denyNextToNew=true;
//declare the query to get the idSet or subset, ordered by desired sort order - note that if you're using quasi resources, then be sure and filter them out.
$ids=q("SELECT ID FROM $MASTER_DATABASE.relatebase_mail_profiles WHERE ResourceType IS NOT NULL ORDER BY CreateDate",O_COL);
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
if(strlen($$object) || $Profiles_ID=q("SELECT ID FROM $MASTER_DATABASE.relatebase_mail_profiles WHERE ResourceToken!='' AND ResourceToken='$ResourceToken'", O_VALUE)){
	//get the record for the object
	$sql="SELECT
	a.*,
	b.Val AS ComplexQuery,
	c.Val AS ManualList,
	d.Val AS EmailColumns,
	e.Val AS BatchRecordComment,
	f.Val AS Subject,
	h.Val AS RequiredFields,
	i.Val AS Content
	FROM relatebase_mail_profiles a 
	
	LEFT JOIN relatebase_mail_profiles_vars b
	ON a.ID = b.Profiles_ID AND b.Name='ComplexQuery'

	LEFT JOIN relatebase_mail_profiles_vars c
	ON a.ID = c.Profiles_ID AND c.Name='ManualList'

	LEFT JOIN relatebase_mail_profiles_vars d
	ON a.ID = d.Profiles_ID AND d.Name='EmailColumns'

	LEFT JOIN relatebase_mail_profiles_vars e
	ON a.ID = e.Profiles_ID AND e.Name='BatchRecordComment'

	LEFT JOIN relatebase_mail_profiles_vars f
	ON a.ID = f.Profiles_ID AND f.Name='Subject'

	LEFT JOIN relatebase_mail_profiles_vars h
	ON a.ID = h.Profiles_ID AND h.Name='RequiredFields'
	
	LEFT JOIN relatebase_mail_profiles_vars i ON
	a.ID=i.Profiles_ID AND i.Name='EditableArea' AND i.Ky='_blank_email'
	WHERE a.ID = '$Profiles_ID'";
	if($a=q($sql, O_ROW)){
		$mode=$updateMode;
		@extract($a);
	}else{
		//object may have been deleted by another user, least perplexing approach is to present insert mode
		$mode=$insertMode;
		$$object=$ID=quasi_resource_generic($MASTER_DATABASE, 'relatebase_mail_profiles', $ResourceToken);
		$nullAbs=$nullCount+1;
	}
}else{
	$mode=$insertMode;
	$$object=$ID=quasi_resource_generic($MASTER_DATABASE, 'relatebase_mail_profiles', $ResourceToken);
	$nullAbs=$nullCount+1; //where we actually are right then
}
//--------------------------- end coding --------------------------------

/*****
The following values are handled outside this page and are set in session the first time the profile loads ONLY.  That is, I open a profile, then load the email subject and body for the Compose Email window.  Once the Compose Email window is open, it may change the session values entirely.  These are updated to the database again only when we Save document (not send).

One additional complex part here is that when we use a Template from the internet, what is stored initially in the database is the TemplateURL, and either NOTHING for the editable regions in relatebase_mail_profiles_vars if no editing has been done, or the edited value if editing has been done.    The way this works for the users is that initially he will see the "native" editable region values of the template.  They will also be stored in session, AND they will be saved to database if they save the profile.  Same goes for editing.  However there's a button to revert to the web template.

*****/
if(!$_SESSION['mail'][$acct]['templates'][$Profiles_ID] || $revertDocument){
	//Required Fields for AdvSQL and Import file
	$_SESSION['mail'][$acct]['templates'][$Profiles_ID]['advanced']['RequiredFields']=$RequiredFields;
	//subject
	$_SESSION['mail'][$acct]['templates'][$Profiles_ID]['subj']=$Subject;
	//body
	if($TemplateMethod=='file'){
		//can't do it yet
		exit('Template Locations from a VOS file not developed yet');
	}else if($TemplateMethod=='url' || true){
		if($str=q("SELECT Val from relatebase_mail_profiles_vars WHERE Profiles_ID='$ID' AND Name='Body'",O_VALUE)){
			//----------------------------- get and display editable regions -------------------------
			//match editable region
			$templateType='Dreamweaver 4.0';
			if(preg_match('/<!--\s*#'.'BeginEditable\s+"([^"]+)"\s*-->/i',$str)){
				$templateType='Dreamweaver 4.0';
				// DW 4.0
				$start='/<!--\s*#'.'BeginEditable\s+"([^"]+)"\s*-->/i';
				$stop='/<!--\s*#'.'EndEditable\s*-->/i';
			}else if($templateType=='Dreamweaver 6.0+'){
			
			}else if($templateType=='XML Region'){
				//idea here is the tag name, e.g. div|span|p, containing the attribute, e.g. name= or var= etc.
			
			}
			
			$buff=$str;
			while(true){
				//here we toggle through and get the editable regions - much more reliable than regex
				$exp=(!$exp || $exp==$stop ? $start : $stop);
				if(preg_match($exp,$buff,$m)){
					$from=strstr($buff,$m[0]);
					$buff=substr($from, strlen($m[0])-strlen($from));
					if($exp==$start){
						//parse the name of the region
						$name=$m[1];
						//buffer the right string for later
						$buff2=$buff;
					}else{
						//must be stop
						$body=substr($buff2, 0, strlen($buff2) - strlen($buff) - strlen($m[0]));
						//keys are lowercase by convention
						$regions[strtolower($name)]=$body;
					}
				}else{
					break;
				}
				$i++;
				if($i>100){
					//notify admin loop failed
					break;
				}
			}
			if($regions){
				foreach($regions as $editableName=>$body){
					//fill with the existing SESSION, Db, or HTML in that precedence
					$_SESSION['mail'][$acct]['templates'][$Profiles_ID]['r'][$editableName]=$body;
					$_SESSION['mail'][$acct]['templates'][$Profiles_ID]['rName'][]=$editableName;
				}
			}
			//-------------------------------------------------------------
		}
	}
	//load column information
	$_SESSION['mail'][$acct]['templates'][$Profiles_ID]['EmailColumns']=$EmailColumns;
}

//--------- Added 2006-06-12: add lastMailProfile if coming from CMS group ------------
if($cb==1015 && $_GET['Groups_ID']){
	q("REPLACE INTO `relatebase_env` SET
	ParamAcct = '$MASTER_DATABASE',
	ParamUser = '$cu',
	Collection = '_SESSION',
	RootObject = 'cmsAttributes',
	ParamName = '".$_GET['Groups_ID'].".Profiles_ID',
	ParamValue = '$Profiles_ID'");
}
//--------------------------------------------------------------------------------------

$groupAccess=false;
if($_SESSION['cnx'][$acct]['modules'])
foreach($_SESSION['cnx'][$acct]['modules'] as $v){
	if($v['SKU']=='065' && $v['Status']==50){
		//they have access to this module
		$groupAccess=true;
		break;
	}
}
$groupAccess=true;


$hideCtrlSection=false;
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/properties_04_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Mail Profile Tool</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" href="/console/rbrfm_admin.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<link href="/Library/ckeditor_3.4/_samples/sample.css" rel="stylesheet" type="text/css" />
<style type="text/css">
.batchList table{
	border-collapse:collapse;
	}
.batchList td{
	border-bottom:1px dotted #CCC;
	padding:2px 4px;
	}
.batchList th{
	background-color:DARKRED;
	padding:2px 4px;
	color:#FFF;
	}
.batchList em[class="gray"]{
	font-size:12px;
	}
#Previous, #Next{
	display:none;
	}
#headerBar1{
	padding:5px 10px 10px 12px; 
	background-color:#CCC;
	}
</style>

<script language="JavaScript" type="text/javascript" src="/Library/js/jquery.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/global_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/common_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/forms_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/loader_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/contextmenus_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/dataobjects_04_i1.js"></script>
<script type="text/javascript" src="/Library/ckeditor_3.4/ckeditor.js"></script>
<script language="JavaScript" type="text/javascript" src="mail.js"></script>
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

var recipientSources= new Array();
recipientSources[1] = "complex";
recipientSources[2] = "none";
recipientSources[3] = "manual";
recipientSources[4] = "import";
recipientSources[5] = "group";
recipientSources[6] = "definedquery";
var activeHelpSystem=false;
function hl_1b(mygroup,mygroupArray,x){
	//--- version 2.1, no change from version 2.0
	// This function handles the tabbed interface
	// Last edit 2004-06-17, default is to show tabs, js auto-checks
	var pass=0;
	for(y in mygroupArray){
		//inactivate all buttons
		try{
			g(mygroup+"_a_"+mygroupArray[y]).style.display='none';
			g(mygroup+"_i_"+mygroupArray[y]).style.display='block';
		}
		catch(e){ }
	}
	//show targeted button
	try{
		g(mygroup+'_i_'+x).style.display='none';
		g(mygroup+'_a_'+x).style.display='block';
	}
	catch(e){ }
	for(y in mygroupArray){
		//hide layers
		g(mygroup+"_"+mygroupArray[y]).style.display='none';
	}
	//show targeted layer
	g(mygroup+'_'+x).style.display='block';
	//declare hidden field for initial value
	g(mygroup+'_status').value=x;
	//load help module if applicable
	if(activeHelpSystem){
		if(x=='help' && helpSet==false){
			le_helpmodule(mygroup);
		}
	}
}
function fComplexQuery(){
	if(g('ComplexQuery').value==''){
		alert('Please enter an SQL query first');
		return false;
	}
	g('submode').value='testquery';
}
function selEmailCols(){
	if(parseInt(g('FilePresent').value)>0){
		ow('email_columns.php?Profiles_ID=<?php echo $Profiles_ID?>&RecipientSource=import&ImportHeaders='+(g('ImportHeaders').checked?1:0)+'&ImportType='+g('ImportType').value+'&SessionToken='+g('SessionToken').value,'l2_aux1','575,350');
	}else{
		alert('Please click on Select File.. first');
	}
}
function URLCheck(){
	if(!g('TemplateLocationURL').value){
		alert('Please enter a complete URL in the From URL field.\nIf you need help, click on the Help tab and look under Template Email (from a URL)');
	}else{
		ow('mail_profile_01_exe.php?mode=checkURL&TemplateLocationURL='+g('TemplateLocationURL').value,'w3','500,450');
	}
}
function dbna(o){
	if(o.checked){
		g('bn').style.backgroundColor='#ccc';
		g('bn').disabled=true;
	}else{
		g('bn').disabled=false;
		g('bn').style.backgroundColor='#FFFFFF';
	}
}
function nav_query_add2(){
	g('submode').value='saveprofile';
	return nav_query_add();
}
$(document).ready(function(){
	$('#BatchRecord').click(function(){
		if(!this.checked)alert('This will skip recording the batch and you will have NO RECORD of sending these emails.  Continue?');
	});
	$('#testMode').click(function(){
		g('nullSub').value=(this.checked?'Send Test':'Send Batch');
	});
});
</script>
<!-- InstanceEndEditable -->
</head>

<body id="properties">
<form id="form1" name="form1" target="w2" method="post" action="/console/resources/bais_01_exe.php" onsubmit="return beginSubmit();" enctype="multipart/form-data">
<div id="header"><!-- InstanceBeginEditable name="top_nav" -->
<div id="headerBar1">
<div id="btns140" class="fr">
<input type="submit" name="nullSub" value="Save Profile" onclick="g('submode').value='saveprofile';" />&nbsp;&nbsp;
<input id="CancelInsert" type="button" name="CancelInsert" value="Close Profile" onclick="focus_nav_cxl('insert');">

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
<input name="submode" type="hidden" id="submode" value="" />
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
?><!-- end navbuttons 1.43 --></div>
<h2>Mail Profile</h2>
</div>
<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->
<p> Current Profile: 
<input type="text" name="Name" id="Name" value="<?php echo $Name?$Name:'(Untitled-1)'?>" size="37" maxlength="77" onchange="dChge(this);" />
<input type="hidden" name="saveAsNew" />
<input name="cb" type="hidden" id="cb" value="<?php echo $cb?>" />
</p>
<?php
//-------------------------------- first tab --------------------------
ob_start(); 
?>

<p>Create a list of recipients</p>	
<p>Method: 
<select name="RecipientSource" onChange="dChge(this);hl_1b('recipientSources',recipientSources,this.value);">
	<?php
	if($Groups_ID && $cb==1015){
		//override any native option of the profile
		$RecipientSource='group';
	}else{
		//if we have it
	}
	?><option <?php echo $RecipientSource=='none'?'selected':''?> value="none">&lt; Select Method ..&gt; 
	<option <?php echo $RecipientSource=='manual'?'selected':''?> value="manual">Manually entered list</option>
	<option <?php echo $RecipientSource=='import'?'selected':''?> value="import">From Imported File</option>
	<option <?php echo $RecipientSource=='definedquery'?'selected':''?> value="definedquery">From Defined Queries</option>
	<option <?php echo $RecipientSource=='complex'?'selected':''?> value="complex">From SQL Query</option><?php
	if(true /** should be, "if they have the CMS module installed" **/){
		?><option <?php echo $RecipientSource=='group'?'selected':''?> value="group">CMS Group</option><?php
	}
	?>
</select>
</p>
<div id="recipientSources_none" style="height:200px;"></div>
<fieldset id="recipientSources_definedquery" style="<?php echo cg('l','recipientSources','group')?>">
	<legend id="recipientSources_definedquerytxt">Defined Queries</legend>
	<select name="Queries_ID" id="Queries_ID" onchange="dChge(this);">
		<option value="">&lt; Select.. &gt;</option>
		<?php
		if($dq=q("SELECT ID, SystemIdentifier, Title, Content FROM relatebase_queries ORDER BY Title ASC", O_ARRAY_ASSOC))
		foreach($dq as $n=>$v){
			?><option value="<?php echo $n?>" <?php echo $Queries_ID==$n?'selected':''?>><?php echo h($v['Title']);?></option><?php
		}
		?>
	</select>
</fieldset>
<?php if(true || $groupAccess){ ?>
<fieldset id="recipientSources_group" style="<?php echo cg('l','recipientSources','group')?>"> 
	<legend id="recipientSources_grouptxt">CMS Group</legend> 
	<img src="/images/assets/groupsicon1.gif" width="54" height="50" />
	Group Name: <select name="Groups_ID[]" size="7" multiple="multiple" id="Groups_ID" onChange="dChge(this); grpOptions(this)">
	<?php
	unset($selGroups);
	if(isset($_GET['Groups_ID'])){
		$selGroups[]=$_GET['Groups_ID'];
	}else{
		$a=explode(',',trim($Groups_ID));
		foreach($a as $v){
			if(trim($a))$selGroups[]=$v;
		}
	}
	?>
	<option value="">- select -</option><?php
	//listing of groups
	if(q("SHOW TABLES IN `$MASTER_DATABASE` LIKE 'addr_groups'", O_VALUE)){
		//table exists and they have the CMS module
		if($a=q("SELECT ID, Name FROM addr_groups ORDER BY Name",O_COL_ASSOC)){
		//there are groups present
			foreach($a as $n=>$v){
				if(in_array($n,$selGroups)) $selectedCMSGroups[$n]==str_replace("'",'\'',$v);
				?><option value="<?php echo $n?>" <?php echo in_array($n,$selGroups) ? 'selected':''?>><?php echo htmlentities($v)?></option><?php
			}
		}
		?><option value="{RB_ADDNEW}">&lt; Add new.. &gt;</option><?php
	}else{
		//here is where we'd synchronize the tables and clean fields up
	}
	?></select>
	<br />
	<br />
	<input type="button" name="openCMSGroup" id="openCMSGroup" value=" Open " <?php echo $Groups_ID?'':'disabled'?>>
	<div id="selectedCMSGroupsMenu" class="menuskin1" onMouseOver="highlightie5()" onMouseOut="lowlightie5()" onclick="executemenuie5()">
		<!-- generate this list dynamically -->
	</div>
	<script language="javascript" type="text/javascript">
	/**
	<?php
	if(count($selectedCMSGroups)){
	foreach($selectedCMSGroups as $n=>$v){
		?>selectedCMSGroups[<?php echo $n?>]='<?php echo $v?>';<?php
	}
	?>CMSGroupSelCount=<?php echo count($selectedCMSGroups)?>;<?php
	//call the menu builder
	
	}
	?>
	**/
	grpOptions(g('Groups_ID'));
	AssignMenu('openCMSGroup','selectedCMSGroupsMenu');
	</script>
	<input type="button" name="groupExport" value="Export.." <?php echo $Groups_ID?'':'disabled'?> onclick="grpExport();"> 
	[note: right-click over Open button to see groups]
	<br />
</fieldset>
<?php } ?>
<fieldset id="recipientSources_complex" style="<?php echo cg('l','recipientSources','complex')?>"> 
	<legend id="recipientSources_complextxt">From Structured Query Language 
	Query</legend> 
	<textarea name="ComplexQuery" cols="90" rows="15" id="ComplexQuery" onchange="dChge(this);"><?php echo h($ComplexQuery);?></textarea>
	<br />
	<br />
	<br />
	<input name="button" type="submit" value="Test Query .." onclick="return fComplexQuery();" />
	<input name="SelEmailCols_2" type="button" id="SelEmailCols_2" onclick="return ow('email_columns.php?Profiles_ID=<?php echo $Profiles_ID?>&query='+escape(g('ComplexQuery').value),'l2_aux1','575,350');" value="Select Email Colum(s) ..">
	&nbsp; 
	<input type="button" name="button" value="Advanced" onclick="ow('mail_profile_09_advanced.php?idx=1&Profiles_ID='+Profiles_ID.value,'l1_MPMAdvanced','350,300');">
	<br />
	<span id="emailFieldList2" title="Fields containing emails"><?php
	echo htmlentities($_SESSION['mail'][$acct]['templates'][$Profiles_ID]['EmailColumns']);
	if($EmailColumns){
		//2006-10-08: UGH! this gets from either a sql or a csv file!
		#prn($_SESSION['mail']);
	}
	?></span>
	<input type="hidden" name="recipientSources_status" id="recipientSources_status" value="">
</fieldset>
<fieldset id="recipientSources_manual" style="<?php echo cg('l','recipientSources','manual')?>"> 
	<legend id="recipientSources_manualtxt">Manually entered list</legend> 
	Enter one email per line<br />
	<textarea name="ManualList" cols="45" rows="5" onchange="dChge(this);"><?php echo h($ManualList);?></textarea>
</fieldset>
<fieldset id="recipientSources_import" style="<?php echo cg('l','recipientSources','import')?>">
	<legend id="">From Imported File</legend><br />
	Type of Data: 
	<select name="ImportType" id="ImportType" onChange="dChge(this); if(this.value!='tab' && this.value!='csv'){alert('Only CSV and Tab options are currently supported');this.selectedIndex=0;return false;}">
		<option value="csv">CSV (Comma Separated)</option>
		<option value="tab">Tab (Tab-separated)</option>
		<option value="xls">Microsoft Excel (*.xls)</option>
		<option value="auto">Auto-detect</option>
	</select>
	<?php if(isset($ImportType)){?>
	<script language="javascript" type="text/javascript">g('ImportType').value='<?php echo $ImportType?>';</script>
	<?php }?>
	<br />
	<input type="checkbox" name="ImportHeaders" id="ImportHeaders" value="1" <?php echo $ImportHeaders?checked:''?> onchange="dChge(this);" />
	First Row Contains Column Names<br />
	<br />
	Column(s) containing Email Addresses: 
	<input type="text" name="EmailColumns" id="emailCols" value="<?php echo $EmailColumns?>" onchange="dChge(this);" />
	<br />
	File Currently Selected: <span id="fileCurrentlySelected">NONE</span><br />
	<span id="emailFieldList" title="Fields containing emails"></span>
	<input name="Submit" type="button" onclick="return ow('mail_profile_import.php?Profiles_ID=<?php echo $Profiles_ID?>&SessionToken='+g('SessionToken').value,'l2_mail','450,275');" value="Select File ..">
	<input type="hidden" name="filePresent" id="FilePresent" value="0">
	<input name="SelEmailCols" type="button" onclick="selEmailCols();" value="Select Email Column(s) ..">
	&nbsp; 
	<input type="button" name="button" value="Advanced" onclick="return ow('mail_profile_09_advanced.php?idx=2&Profiles_ID='+g('Profiles_ID').value+'&SessionToken='+g('SessionToken').value,'l1_MPMAdvanced','350,300');">
	<input name="mode" type="hidden" id="mode" value="<?php echo $mode?>">
	<input type="hidden" name="rowIdx" value="">
	<input type="hidden" name="compileTime" value="<?php echo strtotime($dateStamp)?>">
	<br />
	<br />
	<font color="#000099">NOTE: All records in the import file will be used 
	in sending email. Since there is no way to exclude rows, please make sure 
	your Import File contains only recipients to receive this email.</font> 
</fieldset>


<?php if(isset($RecipientSource) && $RecipientSource!=='none'){?>
<script language="javascript" type="text/javascript">
hl_1b('recipientSources',recipientSources,'<?php echo $RecipientSource ? $RecipientSource : 'none' ?>');
</script>
<?php }?>


<?php
//-------------------------------- store tab --------------------------
get_contents_tabsection('mpRecipients');
?>
<input name="TemplateMethod" type="hidden" id="TemplateMethod" value="<?php echo ''?>" />
<input name="Composition" type="hidden" id="Composition" value="<?php echo 'blank';?>" />

Send email in <label>
<input name="HTMLOrText" type="radio" value="1" <?php echo !isset($HTMLOrText) || $HTMLOrText=='1' ? 'checked':''?> onchange="dChge(this);" /> 
HTML </label>
<label>
<input name="HTMLOrText" type="radio" value="0" <?php echo $HTMLOrText==='0' ? 'checked':''?> onchange="dChge(this);" /> 
plain text</label>
<br />
<br />
<strong>Subject Line of Email</strong>:<br />
<input type="text" name="Subject" size="50" value="<?php echo h($Subject);?>" onchange="dChge(this);" /><br />
<br />
Content of email:<br />
<textarea cols="80" id="Content" name="Content" rows="10" onchange="dChge(this);"><?php
echo h(trim($Content) ? $Content : '<p></p>');
?></textarea>
<script type="text/javascript">
var editor = CKEDITOR.replace( 'Content' );
setTimeout('CheckDirty(\'Content\')',1000);
</script>


<?php if(false){ ?>
	<div style="float:right;"><input name="ComposeAndProofread" type="button" onclick="ow('compose.php?Profiles_ID=<?php echo $Profiles_ID?>&TemplateLocationURL='+escape(g('TemplateLocationURL').value)+'&Composition='+g('CompositionType').checked+'&TemplateMethod='+g('idTemplateMethod').checked+'&SessionToken='+g('SessionToken').value,'l2_compose','500,400');" value="Compose and Proofread..">
	</div>
	<fieldset id="fldSetCompose" style="width:350px;"><legend>Composition Options</legend> 
		<input type="radio" name="Composition" value="blank" onclick="DoBlankEmail();" <?php echo $Composition=='' || $Composition=='blank'?checked:''?> onchange="dChge(this);" />
		Compose Blank Email (button above)<br />
		<input id="CompositionType" name="Composition" type="radio" value="template" onclick="DoTemplateMail();" <?php echo $Composition=='template'?checked:''?> onchange="dChge(this);" />
		Compose Email from Template (below)<br />
	</fieldset> 
	<div  id="mailComp_blank"></div>
	<fieldset id="mailComp_template"> <legend>Template Options</legend>
		<?php
		//declare template method
		
		?>
		<input type="radio" name="TemplateMethod" value="file" <?php echo $TemplateMethod=='' || $TemplateMethod=='file'?checked:''?>>
		OPTION 1: From File: 
		<input name="submit" type="button" onclick="alert('not developed, we just need a link to the FEX system to select a file though!');" value="Get VOS File ..">
		<span id="templateFileName"> 
		<input type="hidden" name="Template_FileID" />
		<?php /** file name here **/?>
		</span> 
		<input type="hidden" name="Files_ID">
		<br />
		<input id="idTemplateMethod" name="TemplateMethod" type="radio" value="url" <?php echo !isset($TemplateMethod) || $TemplateMethod=='url'?checked:''?>>
		OPTION 2: From URL: 
		<input name="TemplateLocationURL" type="text" id="TemplateLocationURL" onKeyUp="DoUrlTemplate();" oncellchange="DoUrlTemplate();" size="35" maxlength="255" value="<?php echo $TemplateMethod=='url'?$TemplateFileOrURL:''?>">
		<input name="Submit" type="button" id="URLCheck" onclick="URLCheck();" value="Check">
		<br />
		<br />
		Editable Areas of Template: 
		<select name="EditableRegion">
		 <option value="DW40">Dreamweaver 4.0</option>
		</select>
	</fieldset>
	<script language="javascript" type="text/javascript">
	//<?php echo $Composition=='blank' ? 'DoBlankEmail();' : 'DoTemplateMail();' ?>
	</script><br />
<?php }?>


<?php
//-------------------------------- store tab --------------------------
get_contents_tabsection('mpCompose');
?>
<h3>Add Attachments</h3>
<p class="gray">Currently attachments must be entered on a single line per attachment as /images/path/to/file.xls for example</p>
<input type="button" name="Button" value="View File Explorer" onclick="return ow('/admin/file_explorer/?uid=attachments','l1_attach','800,700');" />
<br />

<textarea name="AttachmentString" id="AttachmentString" rows="7" cols="65" onchange="dChge(this);"><?php echo h($AttachmentString);?></textarea>
<?php
//-------------------------------- store tab --------------------------
get_contents_tabsection('mpAttachments');
?>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td>
		From (Name):
		<input type="text" name="FromName" value="<?php echo $mode==$updateMode ? $FromName : h($_SESSION['firstName'].' '.$_SESSION['lastName'])?>" size="20" maxlength="75" onchange="dChge(this);" />
		<br />
		From (Email):
		<input name="FromEmail" type="text" value="<?php echo $mode==$updateMode ? $FromEmail : $_SESSION['email']?>" size="17" maxlength="85" onchange="dChge(this);" />
		<br />
		Reply-to (Name):
		<input type="text" name="ReplyToName" value="<?php echo $mode==$insertMode ? '(optional)' : $ReplyToName;?>" size="22" maxlength="75" onfocus="if(this.value=='(optional)'){this.value='';this.className='';}" class="<?php echo $mode==$insertMode ? 'gray':''?>" onchange="dChge(this);" />
		<br />
		Reply-to (Email):
		<input type="text" name="ReplyToEmail" value="<?php echo $mode==$insertMode ? '(optional)' : $ReplyToEmail;?>" size="22" maxlength="85" onfocus="if(this.value=='(optional)'){this.value='';this.className='';}" class="<?php echo $mode==$insertMode ? 'gray':''?>" onchange="dChge(this);" />
            <br />
        Send bounced Emails to:
            <input type="text" name="BounceEmail" value="<?php echo $mode==$insertMode ? '(optional)' : $BounceEmail;?>" size="22" maxlength="85" onfocus="if(this.value=='(optional)'){this.value='';this.className='';}" class="<?php echo $mode==$insertMode ? 'gray':''?>" onchange="dChge(this);" />
		<br />
		<br />
		<input type="hidden" name="Importance" value="0" />
		<label><input type="checkbox" name="Importance" value="1" <?php echo $Importance?checked:''?> onchange="dChge(this);" />
		Mark as important</label>
		<input name="preview" id="preview" type="hidden" />
		<br />
		<br />
		<input type="hidden" name="AttachVCard" value="0" />
		<label><input type="checkbox" name="AttachVCard" value="1" <?php echo $AttachVCard?checked:''?> onclick="if(this.checked==true)alert('Sorry, this is not developed')" onchange="dChge(this);" /> Attach <i>your</i> record as a V-Card</label>	<input type="button" name="button" value="Edit Card .." onclick="alert('Not developed');return false;">
		<br />
		<br />
		<input type="hidden" name="ReturnReceipt" value="0" />
		<label><input type="checkbox" name="ReturnReceipt" value="1" <?php echo $ReturnReceipt?checked:''?> onclick="if(this.checked==true)alert('Sorry, this is not developed')" onchange="dChge(this);" /> Return receipt requested</label>
		<br />
		<input type="hidden" name="UseTrackingImage" value="0" />
		<label><input name="UseTrackingImage" type="checkbox" id="UseTrackingImage" onchange="dChge(this);" value="1" <?php !isset($UseTrackingImage) || $UseTrackingImage?'checked':'';?> /> Use Tracking Image</label>
		<br />
		<br />
		<input type="hidden" name="CrossCheckBatch" value="0" />
		<label><input type="checkbox" name="CrossCheckBatch" value="1" <?php echo $CrossCheckBatch?checked:''?> onclick="if(this.checked==true)alert('This will skip sending all emails to email addresses already included in the specified batch sent by THIS PROFILE.\nIn order to get the batch number, either call your administrator or view the batch report ID')" onchange="dChge(this);" /> Cross Check Against Batch number: </label> &nbsp; <input type="text" name="CrossCheckBatchNumber" size="4" onchange="dChge(this);" />
		<br />
		<label>
		<input name="pushOutput" type="checkbox" id="pushOutput" onchange="dChge(this);" value="1" /> 
		Push output through (do not queue)
		</label>
		</td>
		<td><fieldset>
<script language="javascript" type="text/javascript">
var testmodeCount=0;
</script>
		<legend>
		<input name="testmodeCount" type="hidden" id="testmodeCount" value="0" onchange="dChge(this);" />
		<label>
		<input type="checkbox" id="testMode" name="testMode" value="1" <?php echo true /** we always start in test mode **/ || $testMode=='1' || !isset($testMode) ? 'checked' : ''?> onchange="dChge(this);" /> Test Delivery Mode</label></legend>
		Send emails as a test to:<br />
		<input name="TestEmail" type="text" value="<?php echo isset($TestEmail) && trim($TestEmail) ? $TestEmail : $_SESSION['email']?>" size="22" maxlength="85" onchange="dChge(this);" />
		<br />
		Send
		<input name="TestEmailBatch" type="text" id="TestEmailBatch" value="<?php echo isset($TestEmailBatch) && $TestEmailBatch!=='0' ? $TestEmailBatch : '10'?>" size="3" onblur="cknumber('TestEmailBatch');" onchange="dChge(this);" /> emails <br />
		(starting from row
		<input name="TestEmailStart" type="text" id="TestEmailStart" value="<?php echo isset($TestEmailStart) && $TestEmailStart!=='0'? $TestEmailStart : '1'?>" size="3" onblur="cknumber('TestEmailStart');" onchange="dChge(this);" />
		).
		</fieldset>
		<br />
		<br />		
		<input type="submit" id="nullSub" name="nullSub2" value="  Send Test  " onclick="g('submode').value='sendbatch';" />
		<br />
		<div class="red" style="width:300px;">NOTE: always run a test send of a few records to yourself before sending out an e-mail campaign!</div>
		</td>
	</tr>
</table>
</p>
<?php
//-------------------------------- store tab --------------------------
get_contents_tabsection('mpDeliver');


/* ------ note: Your Records is hidden for now ----------- */
?>
<p class="gray">
A group of e-mails that are sent out as a campaign or bulletin are also referred to as a "batch".  These settings allow you to generally or specifically control what information is stored about the batch you are sending out.
</p>
<input type="hidden" name="BatchRecord" value="0" />
<label><input name="BatchRecord" type="checkbox" id="BatchRecord" value="1" <?php echo $BatchRecord || !isset($BatchRecord)?checked:''?> onchange="dChge(this);" /> Create a Batch Report</label>
&nbsp;&nbsp;&nbsp;
Default Name of batches: <input type="text" name="DefaultBatchName" maxlength="75" value="<?php echo h($DefaultBatchName?$DefaultBatchName:($ID?'mailbatch'.$ID:''))?>" onchange="dChge(this);" />
<br />
<br />
<input type="hidden" name="DefaultBatchNameAutoinc" value="0" />
<label><input id="field-dbna" type="checkbox" name="DefaultBatchNameAutoinc" value="1" onclick="dbna(this);"  <?php echo $DefaultBatchNameAutoinc?checked:''?> onchange="dChge(this);" /> Add unique number for each batch</label>
<br />
..or, name of <strong>this</strong> batch: 
<input type="text" id="bn" name="BatchName" maxlength="75" value="<?php echo h($BatchName)?>" onchange="dChge(this);" />
<br />
<br />
Send batch report copy to (Email): <input type="text" name="BatchRecordEmail" maxlength="85" value="<?php echo h($BatchRecordEmail?$BatchRecordEmail:sun('e'));?>" onchange="dChge(this);" />
<br />
Batch Comments:<br />
<textarea name="BatchRecordComment" cols="45" rows="5" onchange="dChge(this);"><?php echo h($BatchRecordComment)?></textarea>
</p>
<?php
get_contents_tabsection('mpRecords');
?>
<fieldset>
	<legend>Send History</legend>
	<div id="batchList">
	<table class="batchList" width="100%" cellspacing="0" cellpadding="0">
		<thead>
		<tr>
		  <th>Subject/Title</th>
			<th>Date</th>
			<th>Time</th>
			<th nowrap="nowrap">Total</th>
			<th>Att.</th>
			
			<th class="problems">Bounces</th>
			<th class="problems">Delays</th>
			<th>Views</th>
			<th>Clicks</th>
			<th>Fwds</th>
			<th>Unsub.</th>
			<th>ID</th>
			<th>&nbsp;</th><!-- actions -->
		</tr>
		</thead><?php
		//this was too slow
		$sql="SELECT
			b.*,
			COUNT(DISTINCT bc.ID) AS Total,
			COUNT(DISTINCT bc2.ID) AS Views,
			COUNT(DISTINCT s.ID) AS Bounced
			FROM relatebase_content_batches b 
			LEFT JOIN relatebase_BatchesContacts bc ON b.ID=bc.Batches_ID
			LEFT JOIN relatebase_BatchesContacts bc2 ON b.ID=bc2.Batches_ID AND bc2.Status>=".mailstatus_apparentlyreceived."
			LEFT JOIN system_bouncestorage s ON 
			/* -- equivalent of foreign key for relatebase_mail_profiles -- */
			(b.ContentObject='relatebase_mail_profiles' AND b.ContentKey=s.Profiles_ID)
			
			AND b.ID=s.Batches_ID /* what is bouncestorage status? */
			WHERE (b.ContentObject='relatebase_mail_profiles' AND b.ContentKey=$ID) GROUP BY b.ID ORDER BY b.CreateDate";
			
		$sql="SELECT b.*, COUNT(bc.ID) AS Total FROM relatebase_content_batches b
			LEFT JOIN relatebase_BatchesContacts bc ON b.ID=bc.Batches_ID
			WHERE (b.ContentObject='relatebase_mail_profiles' AND b.ContentKey=$ID) GROUP BY b.ID ORDER BY b.CreateDate";
		$queryTime=array();
		if($a=q($sql, O_ARRAY)){
			$queryTime[]=round($qr['time'],3).'s';
			$b=q("SELECT COUNT(bc2.ID) AS Views FROM 
			relatebase_content_batches b
			LEFT JOIN relatebase_BatchesContacts bc2 ON bc2.Batches_ID=b.ID AND bc2.Status>=".mailstatus_apparentlyreceived."
			WHERE (b.ContentObject='relatebase_mail_profiles' AND b.ContentKey=$ID) GROUP BY b.ID ORDER BY b.CreateDate",O_ARRAY);
			$queryTime[]=round($qr['time'],3).'s';
			$a=array_merge_accurate($a,$b);
			$c=q("SELECT 
			COUNT(s.ID) AS Bounced
			FROM relatebase_content_batches b
			LEFT JOIN system_bouncestorage s ON 
			/* -- equivalent of foreign key for relatebase_mail_profiles -- */
			(b.ContentObject='relatebase_mail_profiles' AND b.ContentKey=s.Profiles_ID)
			AND b.ID=s.Batches_ID /* what is bouncestorage status? */
			WHERE (b.ContentObject='relatebase_mail_profiles' AND b.ContentKey=$ID) GROUP BY b.ID ORDER BY b.CreateDate",O_ARRAY);
			$queryTime[]=round($qr['time'],3).'s';
			$a=array_merge_accurate($a,$c);
			$_Y='/'.date('Y');
			foreach($a as $n=>$v){
				$Settings=@(unserialize(base64_decode($v['FieldHash'])));
				?><tr>
				<td nowrap="nowrap"><?php echo $Settings['Subject'];?></td>
				<td nowrap="nowrap"><?php echo str_replace(' ','&nbsp;',str_replace($_Y,'',date('l n/j/Y',strtotime($v['CreateDate']))));?></td>
				<td><?php echo date('g:iA',strtotime($v['CreateDate']));?></td>
				<td><?php echo $v['Total']?></td>
				<td>&nbsp;</td>
				<td class="problems"><?php
				echo $v['Bounced'];
				?></td>
				<td class="problems"><em class="gray">(TBD)</em></td>
				<td><?php
				if($Settings['UseTrackingIMage'] || $v['Views']){
					echo $v['Views'];
				}else{
					?><strong title="This mail send did not specify a tracking image">N/A</strong><?php
				}
				?></td>
				<td><em class="gray">(TBD)</em></td>
				<td><em class="gray">(TBD)</em></td>
				<td><em class="gray">(TBD)</em></td>
				<td><?php echo $v['ID'];?></td>
				<td>Details<?php
				?></td>

				</tr><?php
			}
		}else{
			?><tr>
				<td colspan="104"><em class="gray">(no batch history for this profile)</em></td>
			</tr><?php
		}
		?>
	</table>
	<?php if(count($queryTime)){ ?><span class="small gray">Query times: <?php echo implode(', ',$queryTime);?></span><br /><?php } ?>
	</div>
</fieldset>
<?php
//-------------------------------- store tab --------------------------
get_contents_tabsection('mpHistory');
?>
<div id="pageHelpStatus">Active Help Status</div>
<div id="pageHelpRegion" class="overflowInset110" style="width:95%;<?php echo $browser=='Moz' ? 'min-' : ''?>height:350px;"></div>
<?php
//-------------------------------- store tab --------------------------
get_contents_tabsection('mpHelp');
?>

this should never show..

<?php
tabs_enhanced(array(
	'mpRecipients'=>array(
		'label'=>'Select Recipients'
	),
	'mpCompose'=>array(
		'label'=>'Compose Email'
	),
	'mpAttachments'=>array(
		'label'=>'Attachments'
	),
	'mpRecords'=>array(
		'label'=>'Your Records',
	),
	'mpDeliver'=>array(
		'label'=>'Deliver Email'
	),
	'mpHistory'=>array(
		'label'=>'History'
	),
	'mpHelp'=>array(
		'label'=>'Help'
	),
));
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