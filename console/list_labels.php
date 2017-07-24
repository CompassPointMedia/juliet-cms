<?php 
/*
NOTES this page:

*/
//identify this script/GUI
$localSys['scriptID']='generic';
$localSys['scriptVersion']='1.0';
$localSys['componentID']='main';


//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
$qx['defCnxMethod']=C_MASTER;
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');
$hideCtrlSection=false;

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/rbrfm_01.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo h($adminCompany);?> List Label Jobs</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" type="text/css" href="rbrfm_admin.css" />
<link rel="stylesheet" type="text/css" href="/Library/css/DHTML/dynamic_04_i1.css" />
<link rel="stylesheet" type="text/css" href="/Library/css/DHTML/data_04_i1.css" />
<script src="/Library/js/jquery.js" language="javascript" type="text/javascript"></script>
<script src="/Library/js/global_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/common_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/forms_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/loader_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/contextmenus_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/dataobjects_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/console/console.js" language="javascript" type="text/javascript"></script>
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
	<div id="leftInset">
	<!-- InstanceBeginEditable name="left_inset" --><!-- InstanceEndEditable -->
	</div>
	<div id="mainBody">
	<!-- InstanceBeginEditable name="main_body" -->
<style type="text/css">
.yat{
	border-collapse:collapse;
	}
.yat th{
	background-color:lavender;
	padding:3px 7px 1px 7px;
	}
.yat td{
	padding:7px 7px 4px 7px;
	border-bottom:1px solid #ccc;
	}
</style>
<h1>Label Jobs</h1>
<table class="yat">
<thead>
<tr>
	<th>&nbsp;</th>
	<th>Name</th>
	<th>Last Used/Modified</th>
	<th>History</th>
</tr>
</thead>
<tbody>
<?php
if($a=q("SELECT l.ID, l.Name, GREATEST(l.EditDate,IF(COUNT(DISTINCT g.ID),MAX(g.EditDate),0)) AS LastEdit, COUNT(DISTINCT g.ID) AS History FROM gen_labels l LEFT JOIN gen_labels g ON l.ID=g.Labels_ID WHERE l.Labels_ID IS NULL GROUP BY l.ID", O_ARRAY)){
	foreach($a as $n=>$v){
		?><tr>
		<td>[<a href="labels.php?Labels_ID=<?php echo $v['ID'];?>" onclick="return ow(this.href,'l1_labels','800,700');">open</a>]</td>
		<td><strong><?php echo $v['Name'];?></strong></td>
		<td><?php echo date('n/j/Y \a\t g:iA',strtotime($v['LastEdit']));?></td>
		<td class="tac"><?php echo $v['History']?$v['History']:'<span class="gray">(none)</span>';?></td>
		</tr><?php
	}
}else{
	?><tr><td colspan="100%"><em class="gray">(No labels present, click Add New Label Job below)</em></td></tr><?php echo "\n";
}

?></tbody></table>
<a href="labels.php" onclick="return ow(this.href,'l1_labels','800,700',true);">Add New Label Job</a>



<!-- InstanceEndEditable -->
	<div class="cbsm"> </div>
	</div>
	<div id="footer">
	<!-- InstanceBeginEditable name="footer" --><!-- #BeginLibraryItem "/Library/rbrfm_footer.lbi" -->&copy;2008-<?php echo date('Y');?> RelateBase Services Inc. - 
<a href="/" target="_blank" title="View index page of your website">view site</a> | 
<a href="http://www.compasspoint-sw.com/mediawiki/index.php?title=RelateBase_Ecommerce_Console:RBRFM:Public_Documentation" target="helpme">WIKI</a><!-- #EndLibraryItem --><!-- InstanceEndEditable -->
	</div>
</div>

<div id="showTester" title="Javascript Tester" onClick="g('tester').style.display='block';">&nbsp;</div>
<div id="tester" >
	<a href="#" onClick="g('ctrlSection').style.display=op[g('ctrlSection').style.display]; return false;">iframes</a><br />
	<textarea name="test" cols="65" rows="4" id="test">clear_form();</textarea><br />
	<input type="button" name="button" value="Test" onClick="jsEval(g('test').value);"><br />
	<textarea id="result" name="result" cols="65" rows="3" ></textarea>
</div>
<?php if(!$hideCtrlSection){ ?>
<div id="ctrlSection" style="display:<?php echo $testModeC ? 'block':'none'?>">
	<iframe name="w1" src="/Library/js/blank.htm"></iframe>
	<iframe name="w2" src="/Library/js/blank.htm"></iframe>
	<iframe name="w3" src="/Library/js/blank.htm"></iframe>
	<iframe name="w4" src="/Library/js/blank.htm"></iframe>
</div>
<?php } ?>
</body>
<!-- InstanceEnd --></html><?php
$out=ob_get_contents(); #end buffer to move iframes
ob_end_clean();
$ctrl=strstr($out,'<div id="ctrlSection"');
$ctrl=str_replace('</body>','',$ctrl);
$ctrl=str_replace('</html>','',$ctrl);
$out=str_replace($ctrl,'',$out);
$out.='</body></html>';
$out=str_replace($rand,$ctrl,$out);
echo($out);
page_end();
?>