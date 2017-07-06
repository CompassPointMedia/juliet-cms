<?php 
//identify this script/GUI
$localSys['scriptGroup']='';
$localSys['scriptID']='generic';
$localSys['scriptVersion']='1.0';
$localSys['pageType']='Properties Window';



//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');require($COMPONENT_ROOT.'/settings.comp110_articles_v101.php');

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/properties_04_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Change Password</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" href="/console/rbrfm_admin.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<style type="text/css">
</style>
<script language="JavaScript" type="text/javascript" src="/Library/js/global_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/common_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/forms_04_i1.js"></script>
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
var isModal=0;
var talks=0; //whether this page broadcasts overall state changes which other pages listen for
var listens=0;
</script>

<!-- InstanceEndEditable -->
</head>

<body id="properties">
<form id="form1" name="form1" target="w2" method="post" action="/console/resources/bais_01_exe.php" onsubmit="return beginSubmit();" enctype="multipart/form-data">
<div id="header"><!-- InstanceBeginEditable name="top_nav" -->
	<div id="headerBar1">
		<h2 class = "h2_1">Change Password</h2> 
	</div>
<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->


<div style="width:530px;padding: 7px 8px 0px 12px;">
	<div style="float:right;width:200px;">
		The console stores all passwords in an encrypted format.
	</div>
	<h2>Change password for: <strong><?php echo q("SELECT CONCAT(FirstName, ' ',LastName) from addr_contacts where UserName='$un_username'", O_VALUE);?></strong></h2>
	<style type="text/css">
	.spacer td{
		padding:1px 6px;
		}
	</style>
	<table class="spacer" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td>Member Current Password<br />
				-or-<br />
				Master Site Password </td>
			<td valign="top"><input name="OriginalPW" type="password" id="OriginalPW"></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td>New Password</td>
			<td><input name="PW" type="password" id="PW"></td>
		</tr>
		<tr>
			<td>re-type Password</td>
			<td><input name="ConfirmPW" type="password" id="ConfirmPW"></td>
		</tr>
	</table>
	<br /><input name="update" type="checkbox" id="update" value="1" onclick="if(this.checked)alert('Caution: This will send the new password to the member via plaintext.');">
	Notify member of changed password. <br /><?php
	if(!valid_email(q("SELECT email from addr_contacts WHERE username='$un_username'", O_VALUE))){
		?><span style="color:darkred;font-weight:900;">User has no current email address!</span><?php
	}
	?>
	<input name="mode" type="hidden" id="mode" value="changepassword">
	<input name="un_username" type="hidden" id="un_username" value="<?php echo $un_username?>">
	<p>&nbsp;</p>
	<p>
		<input type="submit" name="Submit" value=" Change Password ">
		&nbsp;&nbsp;
		<input type="button" name="Button" value="Cancel" onclick="window.close();">
	</p>
</div>


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