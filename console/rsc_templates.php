<?php 
/*
Started 2011-08-01
simple template manager - and ability to declare regions inside of regions eventually

object=template
object=block
there is a hierarchy - very similar to gen_nodes
* eventually we will want to merge them or atleasst analyze their structure for semantic similarities



*/
//identify this script/GUI
$localSys['scriptID']='generic';
$localSys['scriptVersion']='1.0';
$localSys['componentID']='main';


//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');
$hideCtrlSection=false;

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/rbrfm_01.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo h($adminCompany);?> Page Manager</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" type="text/css" href="rbrfm_admin.css" />
<link rel="stylesheet" type="text/css" href="/Library/css/DHTML/dynamic_04_i1.css" />
<link rel="stylesheet" type="text/css" href="/Library/css/DHTML/data_04_i1.css" />
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

<?php
/*
todo
----

*/

?>

<h1>Template Manager</h1>
<style type="text/css">
.spacer td{
	border-bottom:1px solid #ccc;
	padding-top:2px;
	padding-bottom:2px;
	}
.spacer .inactive td{
	background-color:#ddd;
	color:#222;
	}
.spacer .focus td{
	background-color:lightgreen;
	}
</style>
<table width="100%" border="0" cellspacing="0" class="spacer">
  <thead>
    <tr>
      <th scope="col">&nbsp;</th>
      <th scope="col">Name</th>
      <th scope="col">Description</th>
      <th scope="col">Regions</th>
    </tr>
  </thead>
  <tfoot>
    <tr>
      <td colspan="100%">
	  <div style="border:1px solid #333;background-color:aliceblue;padding:15px;width:75%;">
	  <form action="resources/bais_01_exe.php" method="post" name="form1" target="w2" id="form1">
	  Create a new template named: 
	    <input name="Name" type="text" id="Name" size="35" />
	    <br />
	    Description:<br />
	    <textarea name="Description" cols="50" rows="3" id="Description"></textarea>
	    <input name="mode" type="hidden" id="mode" value="insertTemplate" />
	    <br />
	    <input type="submit" name="Submit" value="Submit" />
	  </form>
	  </div>
	  </td>
    </tr>
  </tfoot>
  <tbody>
    <?php
	if($templates=q("SELECT ID, Name, Description FROM gen_templates ORDER BY Name", O_ARRAY, C_MASTER)){
		foreach($templates as $v){
			?><tr <?php if($v['ID']==$focus)echo 'class="focus"';?>>
			  <td>[<a href="rsc_templates_focus.php?Templates_ID=<?php echo $v['ID'];?>" title="view the editor form for this template" onclick="return ow(this.href,'l1_templates','703,704');">edit</a>]</td>
			  <td><?php echo $v['Name'];?></td>
			  <td><?php echo $v['Description'];?></td>
			</tr><?php
		}
	}else{
		?>
		<tr>
			<td colspan="100%" class="ghost">No pages listed in the page manger.  Click Add Page below to create your first page</td>
		</tr>
		<?php
	}
	?>
  </tbody>
</table>

<!-- InstanceEndEditable -->
	<div class="cbsm"> </div>
	</div>
	<div id="footer">
	<!-- InstanceBeginEditable name="footer" --><!-- #BeginLibraryItem "/Library/rbrfm_footer.lbi" -->&copy;2008-<?php echo date('Y');?> RelateBase Services Inc. - 
<a href="/" target="_blank" title="View index page of your website">view site</a> | 
<a href="http://www.compasspointmedia.com/mediawiki/index.php?title=RelateBase_Ecommerce_Console:RBRFM:Public_Documentation" target="helpme">WIKI</a><!-- #EndLibraryItem -->

<!-- InstanceEndEditable -->
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