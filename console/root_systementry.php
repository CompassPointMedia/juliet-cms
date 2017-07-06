<?php 
/*
2013-07-17: pulled over from AMS
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
<!-- InstanceBeginEditable name="doctitle" --><title>RelateBase System Data Entry</title><!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" type="text/css" href="rbrfm_admin.css" />
<link rel="stylesheet" type="text/css" href="/Library/css/DHTML/dynamic_04_i1.css" />
<link rel="stylesheet" type="text/css" href="/Library/css/DHTML/data_04_i1.css" />

<style>
/** CSS Declarations for this page **/
</style>

<script src="/Library/js/jquery.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/jquery.tabby.js" language="JavaScript" type="text/javascript"></script>
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

<h2>System Entry Tables</h2>
<p class="gray">
To appear here, there must be both an entry in <code>system_tables</code> and <code>system_profiles</code>.  This table is currently hard-coded but should eventually flex to showing the records/list view of any of the tables in this table itself, and also in views or complex joins of tables
</p>
<table id="thisTable" class="yat">
<thead>
<tr>
	<th>Table</th>
	<th>Records</th>
	<th>Last Entry </th>
	<th>&nbsp;</th>
</tr>
</thead>
<tbody>
<?php
if($a=q("SELECT p.ID AS Profiles_ID, p.Settings, p.Identifier, t.*
FROM system_tables t, system_profiles p WHERE t.ID=p.tables_ID ORDER BY p.Identifier, t.Name", O_ARRAY)){
	$i=0;
	foreach($a as $n=>$v){
		extract($v);
		$i++;
		if($i==1 || $buffer !=$v['Identifier']){
			if(false && $i>1){
				//close previous
				?><tr>
				<td>subtotal 1</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>subtotal 2</td>
				</tr><?php
			}
			$j=0;
			$buffer=$v['Identifier'];
			?><tr>
			<td colspan="100%"><h2><?php echo $buffer;?></h2></td>
			</tr><?php
		}
		$j++;
		?><tr class="<?php echo !fmod($j,2)?'alt':''?>">
			<td><a href="system_entry.php?object=system_tables&identifier=default&system_tables_ID=<?php echo $ID;?>" title="Edit the table configuration" onclick="return ow(this.href,'l1_tables','700,700');"><img src="/images/i/note01.gif" width="8" height="10" alt="edit" /></a>&nbsp;<a href="system_entry.php?object=system_profiles&identifier=default&system_profiles_ID=<?php echo $Profiles_ID;?>" title="Edit this profile" onclick="return ow(this.href,'l1_profiles','700,700');"><?php echo $v['Name'];?></a><?php
			if($v['SystemName']!=$v['Name']){
				?><p class="gray" style="font-size:11px">(<?php echo $v['SystemName'];?>)</p><?php
			}
			?></td>
			<td class="tac"><?php echo q("SELECT COUNT(*) FROM $SystemName", O_VALUE, $MASTER_DATABASE);?></td>
			<td class="tac"><?php 
			echo date('n/j/Y \a\t g:iA',strtotime(q("SELECT UPDATE_TIME FROM information_schema.tables WHERE  TABLE_SCHEMA = '$MASTER_DATABASE' AND TABLE_NAME = '$SystemName'", O_VALUE, C_SUPER)));
			?></td>
			<td><a href="root_systementry_list.php?_Profiles_ID_=<?php echo $Profiles_ID;?>">open list</a> &nbsp;&nbsp;&nbsp;<a href="system_entry.php?object=<?php echo $SystemName;?>&identifier=<?php echo $Identifier;?>" onclick="return ow(this.href,'l1_<?php echo $SystemName;?>','700,700');">add new</a></td>
		</tr><?php
	}
}else{
	?><tr>
	<td colspan="100%"><em class="gray">No records found for that criteria</em></td>
	</tr><?php
}
?>
<tr>
<td colspan="100%">
<a href="system_entry.php?object=system_tables&identifier=default" title="Edit the table configuration" onclick="return ow(this.href,'l1_tables','700,700');">register new table</a>
&nbsp;
&nbsp;
<a href="system_entry.php?object=system_profiles&identifier=default" title="Edit this profile" onclick="return ow(this.href,'l1_profiles','700,700');">register new profile</a>
<p class="gray">Both links use the raw system entry system currently</p>
</td>
</tr>
</tbody>
</table>


	<!-- InstanceEndEditable -->
	<div class="cbsm"> </div>
	</div>
	<div id="footer">
	<!-- InstanceBeginEditable name="footer" --><!-- InstanceEndEditable -->
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