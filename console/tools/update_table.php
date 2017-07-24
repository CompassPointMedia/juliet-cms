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

$hideCtrlSection=false;


$featuredArticlePage='../articles.php';
if($_GET['Priority_y']){
	//clumsy but works
	$ps=q("SELECT ID FROM cms1_articles WHERE Category='Article' ORDER BY Priority ASC", O_COL);
	$e=0;
	foreach($ps as $v){
		$e++;
		q("UPDATE cms1_articles SET Priority=$e WHERE ID='$v' AND Category='Article'");
	}
	$max=q("SELECT MAX(Priority) FROM cms1_articles WHERE Category='Article'", O_VALUE);
	$thisp=q("SELECT Priority FROM cms1_articles WHERE ID='$ID' AND Category='Article'", O_VALUE);
	$dir=($Priority_y<9?1:-1);
	if(($dir==1 && $thisp==1) || ($dir==-1 && $thisp==$max)){
		/* ?><script>alert('You are at the top or bottom already');</script><?php */
	}else{
		//move up (number gets lower), or move down
		q("UPDATE cms1_articles SET Priority=Priority".($dir==1 ? '+' : '-')."1 WHERE Category='Article' AND Priority=$thisp".($dir==1 ? '-' : '+')."1");
		q("UPDATE cms1_articles SET Priority=Priority".($dir==1 ? '-' : '+')."1 WHERE ID='$ID'");
	}
}



?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="../../Templates/rbrfm_01.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo h($adminCompany);?> Admin Suite</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" type="text/css" href="../rbrfm_admin.css" />
<link rel="stylesheet" type="text/css" href="../../Library/css/DHTML/dynamic_04_i1.css" />
<style>
		table.data1{
			margin:0px 10px;
			border-left:1px solid #272727;
			border-right:1px solid #272727;
			}
		.data1 td, .data1 th{
			padding:3px 5px 1px 8px;
		}
		.data1 th{
			background-color:#ffA500;
			}
		table.data1{
			border-collapse:collapse;
			background-color:white;
		}
		.data1 td{
			border-bottom:1px dotted #555;
		}

</style>

<script src="../../Library/js/global_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="../../Library/js/common_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="../../Library/js/forms_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="../../Library/js/loader_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="../../Library/js/contextmenus_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="../../Library/js/dataobjects_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="../console.js" language="javascript" type="text/javascript"></script>
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

function setID(id,category){
	g("ID").value=id;
	g("Category").value=category;
}


<?php 
//js var user settings
js_userSettings();
?>

var login=<?php echo $_SESSION['identity'] ? 1 : 0?>;

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

<?php

require($FUNCTION_ROOT.'/function_mysql_update_table_v100.php');
$result=mysql_update_table($dbTableFrom, $dbTableTo);
//prn($result);
?>
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
page_end();
?>