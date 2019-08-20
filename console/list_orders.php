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
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');/* 2013-09-11
this is now redirecting to SystemEntry for handling.  Which means I'm "buying" systementry as a way to lose the hard-coding, and make invoices/cash sales (which is a good table to have in any accounting ap) a viable GUI.
the issues I will be facing are that some previous accounts have finan1.0 tables or incomplete fields (the view holds ALL tables)

* we need to open up still to the OLD order focus page
* we need to have some fields normally hidden, and the order needs improved
* ID needs to be hidden
* values need translated
* THE DATASET VIEW NEEDS TO BE <<USEFUL>>!!

*/

ob_start();
//synch the finan tables
	#finan_headers
	#finan_invoices
		#[if old convert the orders!]
	#finan_billing
	#finan_transactions (Shipping_ID)
	#finan_TransactionsTransactions (this may not be synchronized)
	#finan_classes (may not be present)
//attempt to create the view
q("SELECT COUNT(*) FROM _v_finan_invoices_cash_sales", ERR_ECHO);
//register the view if not already
q("SELECT COUNT(*) FROM system_tables", ERR_ECHO);
q("SELECT COUNT(*) FROM system_profiles", ERR_ECHO);
if(!($Tables_ID=q("SELECT ID FROM system_tables WHERE SystemName='_v_finan_invoices_cash_sales'", O_VALUE, ERR_ECHO))){
	$Tables_ID=q("INSERT INTO system_tables SET
	SystemName='_v_finan_invoices_cash_sales',
	Name='_v_finan_invoices_cash_sales',
	KeyField='ID',
	Description='View registered by list_orders.php',
	Type='view',
	Level=1",O_INSERTID, ERR_ECHO);
}
if(!($Profiles_ID=q("SELECT ID FROM system_profiles WHERE Tables_ID=$Tables_ID AND Identifier='default'", O_VALUE, ERR_ECHO))){
	$Profiles_ID=q("INSERT INTO system_profiles SET
	Tables_ID=$Tables_ID,
	Identifier='default',
	type='Data View',
	Name='Invoice and Cash Sales',
	Description='View with everything related to shipping part-out, status of shipping, payment status and client information'
	/* Settings='$defaultSettings' */", O_INSERTID, ERR_ECHO);
}
$err=ob_get_contents();
ob_end_clean();
if($err){
	$err="\n".'--------------------------------'."\n".$err."\n".'-------------------------------------'."\n";
	mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err),$fromHdrBugs);
	?>
	<h1>System Error (Orders)</h1>
	<p>Attempting to call the order page required a system check; some errors resulted which have been sent to the system administrator.  Try refreshing this page; if you do not see this message again, the problem has been resolved</p>
	<p>
	<input type="button" name="button" value="Try Again" onClick="window.location='/console/list_orders.php" />
	</p><?php
	exit;
}else{
	header('Location: /console/root_systementry_list.php?_Profiles_ID_='.$Profiles_ID);
	exit;
}



$hideCtrlSection=false;

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/rbrfm_01.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo h($adminCompany);?> Admin Suite : Manage Orders and Invoices</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" type="text/css" href="rbrfm_admin.css" />
<link rel="stylesheet" type="text/css" href="/Library/css/DHTML/dynamic_04_i1.css" />
<style>
/** CSS Declarations for this page **/
</style>

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


<?php 
//js var user settings
js_userSettings();
?>
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

	<?php if(false){ ?>
	<!-- Important Links section -->
	<div style="display:none;float:right;margin-top:-5px;">
		<form action="index_01_exe.php" method="post" name="form1" target="w2" id="form1" style="display:none;padding:0px;margin:0px;">
			<input type="text" name="textfield" />
			<input name="mode" type="hidden" id="mode" value="searchParent" />
			<img src="../images/i/magglass1_30x30.gif" width="32" height="30" />
		</form>
	</div>
	<br />
	<h3 class="fl nullBottom">Orders</h3>
	<div class="menubar fr">
	<div style="float:left">
		<a id="optionsOrders" title="View Options" href="javascript:;" onClick="hidemenuie5(event,1);showmenuie5(event,1)" oncontextmenu="return false;">Options</a>&nbsp;&nbsp; 
	</div>
	<div style="float:left">
		<a id="reportsOrders" title="View Report Options" href="javascript:;" onClick="hidemenuie5(event,1);showmenuie5(event,1)" oncontextmenu="return false;"><img src="../images/i/addr_26x27.gif" alt="Reports" width="23" height="30" /> Reports</a>&nbsp;&nbsp;
	</div>
	</div>
	<script type="text/javascript" language="JavaScript">
	function optionsOrders(){
		g('oh02').innerHTML=(hideInactiveOrders?'Show inactive orders':'Hide inactive orders');
	}
	function reportsOrders(){
	}
	function toggleActive(){
		window.open('resources/bais_01_exe.php?mode=toggleActive&node=Orders&current='+hideInactiveOrders+'&component=orderList','w2');	
	}
	function exportOrders(node){
		if(node=='csv-all' && !confirm('This will export the entire list of orders.  Continue?'))return;
		window.open('resources/bais_01_exe.php?suppressPrintEnv=1&mode=exportmembers&node='+node,'w2');
	}
	function openExportManager(){
		ow('orders_exportmanager.php','l1_exportmanager','800,450');
	}
	AssignMenu('^optionsOrders$', 'optionsOrdersMenu');
	AssignMenu('^reportsOrders$', 'reportsOrdersMenu');
	</script>
	<div id="optionsOrdersMenu" class="menuskin1" style="z-index:1000;" onMouseOver="hlght2(event)" onMouseOut="llght2(event)" onClick="executemenuie5(event)" precalculated="optionsOrders();">
		<?php if($adminControls['modifyOrders']>=8){ ?>
		<div id="oh01" style="font-weight:900;" class="menuitems" command="addOrder();" status="Add a new order">New Order</div>
		<hr class="mhr"/>
		<?php } ?>
		<div id="oh02" nowrap="nowrap" class="menuitems" command="toggleActive();" status="option2">Show Inactive Orders</div>
	</div>
	<div id="reportsOrdersMenu" class="menuskin1" style="z-index:1000;width:225px;" onMouseOver="hlght2(event)" onMouseOut="llght2(event)" onClick="executemenuie5(event)" precalculated="reportsOrders();">
		<div id="or03" nowrap="nowrap" class="menuitems" command="openExportManager();" status="Open the export manager">Export Manager</div>
		<div id="or02" nowrap="nowrap" class="menuitems" command="exportObject('customer','session');" status="option2">Export IIF Quickbooks File for these results</div>
		<hr class="mhr"/>
		<div id="or00" class="menuitems" command="exportOrders('csv-all');" status="Export a complete spreadsheet (all active and inactive orders)">Export COMPLETE CSV spreadsheet</div>
		<div id="or01" class="menuitems" command="exportOrders('csv');" status="Export CSV spreadsheet for these results">Export CSV spreadsheet for these results</div>
	</div>
	<?php  } ?>
	
	<?php require('components/comp_20_orders_v201.php'); ?>
	
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