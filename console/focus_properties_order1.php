<?php 
/*
NOTES this page:

*/

//identify this script/GUI
$localSys['scriptID']='generic';
$localSys['scriptVersion']='1.0';
$localSys['componentID']='main';
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php'); 
//require('../console/systeam/php/auth_i2_v100.php');
$qx['defCnxMethod']=C_MASTER;

$hideCtrlSection=false;
$hideheader=true;
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/rbrfm_01.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" --><title>RelateBase Administrative Console</title><!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<link id="undoHTML" rel="stylesheet" type="text/css" href="../site-local/undohtml2.css" />
<link rel="stylesheet" type="text/css" href="rbrfm_admin.css" />
<link id="cssDynamic" rel="stylesheet" type="text/css" href="../Library/css/DHTML/dynamic_04_i1.css" />
<style>
/** CSS Declarations for this page **/
</style>

<script src="../Library/js/global_04_i1.js" id="jsglobal" language="JavaScript" type="text/javascript"></script>
<script src="../Library/js/common_04_i1.js" id="jscommon" language="JavaScript" type="text/javascript"></script>
<script src="../Library/js/forms_04_i1.js" id="jsforms" language="JavaScript" type="text/javascript"></script>
<script src="../Library/js/loader_04_i1.js" id="jsloader" language="JavaScript" type="text/javascript"></script>
<script src="../Library/js/contextmenus_04_i1.js" id="jscontextmenus" language="JavaScript" type="text/javascript"></script>
<script src="../site-local/jslocal.js" id="jsLocal" language="javascript" type="text/javascript"></script>
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
var isEscapable=0;
var isDeletable=0;
var isModal=0;
var talks=0; //whether this page broadcasts overall state changes which other pages listen for
var listens=0;


</script>

<!-- InstanceEndEditable -->
</head>

<body>
<div id="mainContainer">
<!-- InstanceBeginEditable name="admin_top" --><!-- #BeginLibraryItem "/Library/rbrfm_adminmenu_basic_01.lbi" --><?php
require($_SERVER['DOCUMENT_ROOT'].'/console/rbrfm_adminmenu_basic_02.php');
?><!-- #EndLibraryItem --><!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="top_region" --><!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="left_inset" --><!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="main_body" -->
<?php
require('components/comp_51_focus_properties_order_v100.php');
?>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="footer" --><!-- #BeginLibraryItem "/Library/rbrfm_footer.lbi" -->&copy;2008-<?php echo date('Y');?> RelateBase Services Inc. - 
<a href="/" target="_blank" title="View index page of your website">view site</a> | 
<a href="http://www.compasspointmedia.com/mediawiki/index.php?title=RelateBase_Ecommerce_Console:RBRFM:Public_Documentation" target="helpme">WIKI</a><!-- #EndLibraryItem --><!-- InstanceEndEditable --></div>

<div id="showTester" title="Javascript Tester" onClick="g('tester').style.display='block';">&nbsp;</div>
<div id="tester" >
	<a href="#" onClick="g('ctrlSection').style.display=op[g('ctrlSection').style.display]; return false;">iframes</a><br />
	<textarea name="test" cols="65" rows="4" id="test">clear_form();</textarea><br />
	<input type="button" name="button" value="Test" onClick="jsEval(g('test').value);"><br />
	<textarea id="result" name="result" cols="65" rows="3" ></textarea>
</div>
<?php if(!$hideCtrlSection){ ?>
<div id="ctrlSection" style="display:<?php echo $testModeC ? 'block':'none'?>">
	<iframe name="w1" src="../Library/blank.htm"></iframe>
	<iframe name="w2" src="../Library/blank.htm"></iframe>
	<iframe name="w3" src="../Library/blank.htm"></iframe>
	<iframe name="w4" src="../Library/blank.htm"></iframe>
</div>
<?php } ?>
</body>
<!-- InstanceEnd --></html><?php
//this function can vary and may flush the document 
function_exists('page_end') ? page_end() : mail($developerEmail,'page end function not declared', 'File: '.__FILE__.', line: '.__LINE__,'From: '.$hdrBugs01);
?>