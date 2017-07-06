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
if(!isset($_SESSION['special']['crossModelUpdateFields'])){
	$_SESSION['special']['crossModelUpdateFields']=$defaultCrossModelUpdateFields;
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Model Cross Update Fields</title>


<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" href="/console/rbrfm_admin.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<style type="text/css">
body{
	background-color:#CCC;
	padding:0px 20px;
	}
form{
	margin:10px;
	border:1px solid gold;
	padding:15px;
	display:block;
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
var isEscapable=2;
var isDeletable=1;
var isModal=1;
var talks=1; //whether this page broadcasts overall state changes which other pages listen for
var listens=0;
var UserName='<?php echo $UserName?>';
</script>

</head>

<body>
<form id="form1" name="form1" target="w2" method="post" action="/console/resources/bais_01_exe.php">
<h2> Cross Model Update Fields</h2>
<p>The fields that are check will be cross-updated when you update this product, if the option to cross update is checked:</p>
<p><?php
foreach($defaultCrossModelUpdateFields as $n=>$v){
	?><label><input type="checkbox" name="crossupdate[<?php echo $n?>]"	value="1" <?php echo $_SESSION['special']['crossModelUpdateFields'][$n]?'checked':''?> /> <?php echo $n?>
	<?php
	if($n=='UnitPrice') echo ' (normal retail price)';
	if($n=='UnitPrice2') echo ' (sale price)';
	?></label><br />
	<?php
	
}

?>
	<input name="mode" type="hidden" id="mode" value="updateCrossModelFields" />
	<input type="submit" name="button" id="button" value="Update Field List" />
	<input type="button" name="button2" id="button2" value="Cancel" onclick="window.close();" />
</p>
</form>
<div id="ctrlSection" style="display:<?php echo $testModeC ? 'block':'none'?>">
	<iframe name="w2" src="/Library/js/blank.htm"></iframe>
</div>
</body>
</html>