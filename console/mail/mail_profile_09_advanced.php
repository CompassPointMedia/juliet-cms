<?php
/******** NOTES ON THIS PAGE ***************** 
This is the page which 
1. you must change sys.scriptID to the relevant value
2. eventually I want to have a single security include file
3. there is no layout control needed for this page, when created it will be named properties_i1_v100.css
4. make sure the proper .js pages are included or excluded
*********************************************/
session_start();
# Identify this script
$localSys[scriptID]='mail_profile_exe';
$localSys[scriptVersion]='1.0.0';
$localSys[componentID]='advanced';



//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
//includes
require('../../admin/general_00_includes.php');
require('mail_00_includes.php');
$qx['defCnxMethod']=C_DEFAULT;


//connection changes, globals must be on
require('../../systeam/php/auth_v200.php');
/*
$acct=$currentConnection=$_SESSION[currentConnection];
$db_cnx=mysql_connect(
	$_SESSION[cnx][$acct][hostName],
	$_SESSION[cnx][$acct][userName],
	$_SESSION[cnx][$acct][password]
);
mysql_select_db($acct,$db_cnx);
*/

if(trim($EmailColumns)){
	$str=preg_replace('/Column(\s|-)/i','',trim($_POST[EmailColumns]));
	$a=explode(',',$str);
	foreach($a as $v){
		//set to zero-based
		$_emailColumns[]=trim($v)-1;
	}
}
!strlen($Profiles_ID)?$Profiles_ID=0:'';

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Select Email Columns</title>
<link rel="stylesheet" href="/Library/css/common/i1.css" type="text/css"/>
<link rel="stylesheet" href="/Library/css/tables/i1.css" type="text/css"/>
<link rel="stylesheet" href="/Library/css/properties/properties_i1_v100.css" type="text/css"/>
<link rel="stylesheet" href="/Library/css/layers/layer_engine_v100.css" type="text/css"/>
<script src="/Library/js/global/global_i1_v100.js"></script>
<script src="/Library/js/common/common_i1_v100.js"></script>
<script src="/Library/js/p/properties_events_v100.js"></script>
<script src="/Library/js/p/properties_functions_v100.js"></script>
<script>
var isEscapable=2
</script>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<?php /*><script>*/
?>
</head>
<body style="background-image:none;background-color:menu;padding:5 3 5 3;">

<div class="controlSection" id="ctrlSection" style="display:none;"><iframe name="w3"></iframe></div>
<form action="mail_profile_01_exe.php?mode=setAdvanced" target="w3_advanced" method="POST">
Advanced Features<br />
<hr>
Required Fields:<br />
	<textarea name="RequiredFields" cols="49" rows="4" id="RequiredFields"><?php echo htmlentities($_SESSION[mail][$acct][templates][$Profiles_ID][advanced][RequiredFields])?></textarea>
	<br />
	Required fields work for Imported Files and for Advanced SQL queries.<br />
	If your page uses logic for output and depends on certain field name, this
	ensures the profile will not run unless these fields are present.<br />
	Fields should be listed separated by commas, e.g.: FirstName, LastName, etc.<br />
	Fields should contain no spaces or special characters.<br />
	<input type="submit" name="Submit" value="Submit Settings">
	<br />
	<br />
	<input type="hidden" name="Profiles_ID" value="<?php echo $Profiles_ID?>">
</form>
<div class="controlSection" id="ctrlSection" style="display:none;"><iframe name="w3_advanced"></iframe></div>
</body>
</html>