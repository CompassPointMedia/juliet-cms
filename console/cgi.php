<?php 
/*
*/
//identify this script/GUI
$localSys['scriptID']='generic';
$localSys['scriptVersion']='1.0';
$localSys['componentID']='main';


//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');
$insertMode='insertUsemodSettings';
$updateMode='updateUsemodSettings';
if(!function_exists('form_field_translator'))require($FUNCTION_ROOT.'/function_form_field_translator_v100.php');

if(!count($consoleEmbeddedModules)){
	mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,
	get_globals($err='we got at this page with no consoleEmbeddedModules array'),$fromHdrBugs);
	exit($err);
}else{
	foreach($consoleEmbeddedModules as $n=>$v){
		if($v['SKU']!=='CGI-70')continue;
		if($usemodModule){
			mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,
			get_globals($err='multiple usemod modules for account'),$fromHdrBugs);
			exit($err);
		}else{
			$Modules_ID=$n;
			$usemodModule=$v;
		}
	}
	if(!$usemodModule){
		mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,
		get_globals($err='no cart module for account'),$fromHdrBugs);
		exit($err);
	}
}
if(count($usemodModule['moduleAdminSettings']['usemod'])){
	//OK
	$usemod=$usemodModule['moduleAdminSettings']['usemod'];
	$mode=$updateMode;
}else{
	$mode=$insertMode;
}
//prn($usemod);

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="../Templates/reports_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Site User Module Manager</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" href="/Library/css/cssreset01.css" type="text/css" />
<link rel="stylesheet" href="rbrfm_admin.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<style type="text/css">
</style>
<script language="JavaScript" type="text/javascript" src="/Library/js/jquery.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/global_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/common_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/forms_04_i1.js"></script>
<script language="JavaScript" type="text/javascript">
/* periwinkle coding */
var thispage='<?php echo $thispage?>';
var thisfolder='<?php echo $thisfolder?>';
var browser='<?php echo $browser?>';
var ctime='<?php echo $ctime?>';
var PHPSESSID='<?php echo $PHPSESSID?>';
//for nav feature
var count='<?php echo $nullCount?>';
var ab='<?php echo $nullAbs?>';

AddOnkeypressCommand("PropKeyPress(e)");
</script>

<!-- InstanceEndEditable -->
</head>

<body id="report">
<?php if(!$suppressForm){ ?>
<form action="../console/resources/bais_01_exe.php" method="post" enctype="multipart/form-data" name="form1" target="w2" id="form1" onsubmit="return beginSubmit();">
<?php }?>
<div id="header"><!-- InstanceBeginEditable name="top_nav" -->



<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->

<h1>Manage E-commerce/Cart Settings</h1>
<div class="fr">
	<input type="submit" name="Submit" value="Save Changes" />
	&nbsp;&nbsp;
	<input type="button" name="Submit2" value="Close" onclick="window.close();" />
</div>
<br />
<br />
<?php
ob_start(); //form_field_translator
?>

<style type="text/css">
textarea{
	background-color:#F4EEDF;
	}
fieldset{
	margin-top:15px;
	}
legend{
	font-size:119%;
	font-weight:900;
	letter-spacing:0.03em;
	}
</style>
<?php ob_start(); //tabs ?>
<h2>Site User Module Settings</h2>
<p><br />
</p>
<fieldset><legend>Address and Phone Information</legend>
   [checkbox:showCompanyField default=checked label='Show company fields' ]
</fieldset>
<br />
<fieldset><legend>Contact Preferences</legend>
coming soon
</fieldset>
<br />
<fieldset><legend>Interests</legend>
coming soon
</fieldset>
<br />
<fieldset><legend>Member Type-Specific</legend>
coming soon
</fieldset>
<br />

<?php
get_contents_tabsection('dataForms');
?>
<h2>Membership Management</h2>
<p class="gray">Here you specify how you manage members, customers and site visitors who sign up for services or your newsletter;; if a setting you need is not present here contact Administrator</p>
[checkbox:proxyLoginAllow label='Allow a proxy login' default=1]<br />
Proxy login presentation method: [select:proxyLoginPresentationMethod options='0:none, 1:by query string, 2:in adminMode, 3:always']<br />
[checkbox:proxyInsertAllow label='Allow a proxy record creation' default=1]<br />
<br />
<!-- this simple method was started 2012-07-01 -->
Authorized proxy usernames: [input:proxyUserNames size=45]<span class="gray">(separate by commas)</span> <br />
<br />
[input:rootTableName default='addr_contacts' label='Root table name' :break]
<fieldset>
<legend>Authenticating Members</legend>
Require email verification [select:useEnrollmentConfirmation options='0:N/A, 1:for wholesale accounts only, 2:always']
</fieldset>
<?php
get_contents_tabsection('management');  //-------------------------------------
?>

<?php
get_contents_tabsection('emails');  //-------------------------------------
?>

<?php
get_contents_tabsection('financial');  //-------------------------------------
?>

<h2>Help Under Development</h2>
<p>
no help docs for this page currently</p>
<?php
get_contents_tabsection('help'); //-------------------------------------
tabs_enhanced(array(
	'dataForms'=>array(
		'label'=>'Data/Forms',
	),
	'management'=>array(
		'label'=>'Management',
	),
	'emails'=>array(
		'label'=>'Emails',
	),
	'financial'=>array(
		'label'=>'Financial',
	),
	'help'=>array(
		'label'=>'Help'
	),
));

?>





<input name="mode" type="hidden" id="mode" value="<?php echo $mode;?>" />
<input name="Modules_ID" type="hidden" id="Modules_ID" value="<?php echo $Modules_ID;?>" />
<?php
$form=ob_get_contents();
ob_end_clean();
echo form_field_translator($form, array(
	'arrayString'=>'usemod',
));
?>
  
<!-- InstanceEndEditable --></div>
<div id="footer"><!-- InstanceBeginEditable name="footer" -->
&nbsp;
<!-- InstanceEndEditable --></div>
<?php if(!$suppressForm){ ?>
</form>
<?php }?>
<?php if(!$hideCtrlSection){ ?>
<div id="showTester" title="Javascript Tester" onclick="g('tester').style.display='block';">&nbsp;</div>
<div id="tester" >
	<a href="#" onclick="g('ctrlSection').style.display='block';return false;">Show Control Section</a><br />
	<textarea name="test" cols="65" rows="4" id="test">clear_form();</textarea><br />
	<input type="button" name="button" value="Test" onclick="jsEval(g('test').value);"><br />
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