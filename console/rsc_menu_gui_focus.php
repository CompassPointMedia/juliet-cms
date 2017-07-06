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
//------------------------ Navbuttons head coding v1.43 -----------------------------
//change these first vars and the queries for each instance
$object='ID';
$recordPKField='ID'; //primary key field
$navObject='ID';
$updateMode='updateNode';
$insertMode='insertNode';
$deleteMode='deleteNode';
$insertType=2; //1=Save&New and Save&Close; 2 = Save and Save&New
#set these to 'disabled' if desired
$saveInitiallyDisabled='';
$saveAndNewInitiallyDisabled='';
$saveAndCloseInitiallyDisabled='';
//v1.4 change - some information about the coding
$navVer='1.43';
//v1.3 change - declare this function if you need to add parameters to the query string
$navQueryFunction='nav_query_add()';
//v1.3 change - deny transiting from Next to New mode (shutting off ability to insert)
$denyNextToNew=false;
//declare the query to get the idSet or subset, ordered by desired sort order - note that if you're using quasi resources, then be sure and filter them out.
$ids=q("SELECT ID FROM _v_gen_nodes_hierarchy_nav WHERE 1",O_COL);

$nullCount=count($ids);
$j=0;
if($nullCount){
	foreach($ids as $v){
		$j++; //starting value=1
		if($j==$abs+$nav || (isset($$object) && $$object==$v)){
			$nullAbs=$j;
			//get actual primary key if passage by abs+nav
			if(!$$object) $$object=$v;
			break;
		}
	}
}else{
	$nullAbs=1;
}
//note the coding to on ResourceToken - this will allow a submitted page to come up again if the user Refreshes the browser
if(strlen($$object)){
	//2013-07-21 - make sure the page class field is present in the view
	//get the record for the object
	if($page=q("SELECT * FROM _v_gen_nodes_hierarchy_nav WHERE ID='".$$object."'",O_ROW)){
		$mode=$updateMode;
		@extract($page);
	}else{
		//object may have been deleted by another user, least perplexing approach is to present insert mode
		$mode=$insertMode;
		unset($$object);
		$nullAbs=$nullCount+1;
	}
}else{
	$mode=$insertMode;
	$nullAbs=$nullCount+1; //where we actually are right then
}
//--------------------------- end coding --------------------------------

$hideCtrlSection=false;


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="../Templates/reports_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Node and Page Relationship Manager</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" href="/Library/css/cssreset01.css" type="text/css" />
<link rel="stylesheet" href="rbrfm_admin.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/data_04_i1.css" type="text/css" />
<style type="text/css">
</style>

<script language="JavaScript" type="text/javascript" src="/Library/js/jquery.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/jquery.tabby.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/global_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/common_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/forms_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/loader_04_i1.js"></script>
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
var isEscapable=1;
AddOnkeypressCommand('PropKeyPress(e)'); //if not declared already
</script>

<!-- InstanceEndEditable -->
</head>

<body id="report">
<?php if(!$suppressForm){ ?>
<form action="/console/resources/bais_01_exe.php" method="post" enctype="multipart/form-data" name="form1" target="w2" id="form1" onsubmit="return beginSubmit();">
<?php }?>
<div id="header"><!-- InstanceBeginEditable name="top_nav" -->

<div id="btns140" class="fr"><?php
ob_start();
?>
<input id="Previous" type="button" name="Submit" value="Previous" class="navButton_A" onclick="focus_nav(-1, '<?php echo ($mode==$insertMode?'insert':'update')?>', <?php echo $mode==$insertMode?1:0?>, 0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $nullAbs<=1?'disabled':''?> >
<?php
//Handle display of all buttons besides the Previous button
if($mode==$insertMode){
	if($insertType==2 /** advanced mode **/){
		//save
		?><input id="Save" type="button" name="Submit" value="Save" class="navButton_A" onclick="focus_nav(0,'insert',1,2<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveInitiallyDisabled?>><?php
	}
	//save and new - common to both modes
	?><input id="SaveAndNew" type="button" name="Submit" value="Save &amp; New" class="navButton_A" onclick="focus_nav(0,'insert', 1,1<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveAndNewInitiallyDisabled?>><?php
	if($insertType==1 /** basic mode **/){
		//save and close
		?><input id="SaveAndClose" type="button" name="Submit" value="Save &amp; Close" class="navButton_A" onclick="focus_nav(0,'insert', 1,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveAndCloseInitiallyDisabled?>><?php
	}
	?><input id="CancelInsert" type="button" name="Submit" value="Cancel" class="navButton_A" onclick="focus_nav_cxl('insert');"><?php
}else{
	//OK, and appropriate [next] button
	?>
<input id="Save" type="button" name="Submit" value="Save" class="navButton_A" onclick="focus_nav(0,'insert',1,2<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveInitiallyDisabled?> />
<input id="OK" type="button" name="Submit" value="OK" class="navButton_A" onclick="focus_nav(0,'<?php echo $mode==$insertMode?'insert':'update'?>',0,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);">
	<input id="Next" type="button" name="Submit" value="Next" class="navButton_A" onclick="focus_nav(1,'<?php echo $mode==$insertMode?'insert':'update'?>',0,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $nullAbs>$nullCount || ($denyNextToNew && $nullAbs==$nullCount) ?'disabled':''?>><?php
}
$navbuttons=ob_get_contents();
ob_end_clean();
//2009-09-10 - change button names, set default as =submit, hide unused buttons
if(!$addRecordText)$addRecordText='Add Record';
if(!isset($navbuttonDefaultLogic))$navbuttonDefaultLogic=true;
if($navbuttonDefaultLogic){
	$navbuttonSetDefault=($mode==$insertMode?'SaveAndNew':'OK');
	if($cbSelect){
		$navbuttonOverrideLabel['SaveAndClose']=$addRecordText;
		$navbuttonHide=array(
			'Previous'=>true,
			'Save'=>true,
			'SaveAndNew'=>true,
			'Next'=>true,
			'OK'=>true
		);
	}
}
$navbuttonLabels=array(
	'Previous'		=>'Previous',
    'Save'			=>'Save',
    'SaveAndNew'	=>'Save &amp; New',
    'SaveAndClose'	=>'Save &amp; Close',
    'CancelInsert'	=>'Cancel',
    'OK'			=>'OK',
    'Next'			=>'Next'
);
foreach($navbuttonLabels as $n=>$v){
	if($navbuttonOverrideLabel[$n])
	$navbuttons=str_replace(
		'id="'.$n.'" type="button" name="Submit" value="'.$v.'"', 
		'id="'.$n.'" type="button" name="Submit" value="'.h($navbuttonOverrideLabel[$n]).'"', 
		$navbuttons
	);
	if($navbuttonHide[$n])
	$navbuttons=str_replace(
		'id="'.$n.'" type="button"',
		'id="'.$n.'" type="button" style="display:none;"',
		$navbuttons
	);
}
if($navbuttonSetDefault)$navbuttons=str_replace(
	'<input id="'.$navbuttonSetDefault.'" type="button"', 
	'<input id="'.$navbuttonSetDefault.'" type="submit"', 
	$navbuttons
);
echo $navbuttons;

// *note that we could go back to the same page the 'New Record' click appeared on, but there's major issues programmatically on whether it would shift because of the placement of the new record.
// *note that the primary key field is now included here to save time
?>
<input name="<?php echo $recordPKField?>" type="hidden" id="<?php echo $recordPKField?>" value="<?php echo $$object;?>" />
<input name="navVer" type="hidden" id="navVer" value="<?php echo $navVer?>" />
<input name="navObject" type="hidden" id="navObject" value="<?php echo $navObject?>" />
<input name="nav" type="hidden" id="nav" />
<input name="navMode" type="hidden" id="navMode" value="" />
<input name="count" type="hidden" id="count" value="<?php echo $nullCount?>" />
<input name="abs" type="hidden" id="abs" value="<?php echo $nullAbs?>" />
<input name="insertMode" type="hidden" id="insertMode" value="<?php echo $insertMode?>" />
<input name="updateMode" type="hidden" id="updateMode" value="<?php echo $updateMode?>" />
<input name="deleteMode" type="hidden" id="deleteMode" value="<?php echo $deleteMode?>" />
<input name="mode" type="hidden" id="mode" value="<?php echo $mode?>" />
<input name="submode" type="hidden" id="submode" value="" />
<input name="componentID" type="hidden" id="componentID" value="<?php echo $localSys['componentID']?>" />
<?php
if(count($_REQUEST)){
	foreach($_REQUEST as $n=>$v){
		if(substr($n,0,2)=='cb'){
			if(!$setCBPresent){
				$setCBPresent=true;
				?><!-- callback fields automatically generated --><?php
				echo "\n";
				?><input name="cbPresent" id="cbPresent" value="1" type="hidden" /><?php
				echo "\n";
			}
			if(is_array($v)){
				foreach($v as $o=>$w){
					echo "\t\t";
					?><input name="<?php echo $n?>[<?php echo is_numeric($o)? '': $o?>]" id="<?php echo $n?>[<?php echo is_numeric($o)? '': $o?>]" type="hidden" value="<?php echo stripslashes($w)?>" /><?php
					echo "\n";
				}
			}else{
				echo "\t\t";
				?><input name="<?php echo $n?>" id="<?php echo $n?>" type="hidden" value="<?php echo stripslashes($v)?>" /><?php
				echo "\n";
			}
		}
	}
}
if($mode!==$insertMode){
	?><br />
	<label><input name="refreshOpener" type="checkbox" id="refreshOpener" value="1" checked="checked" />
	Refresh the page</label>
	<?php
}
?>

<!-- end navbuttons 1.43 --></div>

<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->

<h2>Modify Node Attributes</h2>
<h2>
</h2>
<div id="node">
<style>
.yat{
	border-collapse:collapse;
	}
.yat td{
	padding:3px 4px 1px 4px;
	border:1px solid #777;
	}
.bread{
	font-size:134%;
	}
.arrow{
	color:midnightblue;
	font-size:smaller;
	font-weight:bold;
	}
.number{
	font-family:Georgia, "Times New Roman", Times, serif;
	font-size:169%;
	text-align:center;
	}
.number input[type="text"]{
	font-family:Georgia, "Times New Roman", Times, serif;
	font-size:119%;
	text-align:center;
	padding:2px 0px 1px 0px;
	}
.selected{
	background-color:orange;
	}
.yat td.name{
	padding-top:10px;
	font-weight:900;
	}
</style>
<input type="hidden" name="Nodes_ID" id="Nodes_ID" value="<?php echo $Nodes_ID;?>" />
<input type="hidden" name="ParentNodes_ID" id="ParentNodes_ID" value="<?php echo $ParentNodes_ID;?>" />
<input type="hidden" name="GroupNodes_ID" id="GroupNodes_ID" value="<?php echo $GroupNodes_ID;?>" />
<input type="hidden" name="originalName" id="originalName" value="<?php echo h($NameT4);?>" />

This menu node: <input name="Name" type="text" id="Name" value="<?php echo h($NameT4);?>" onchange="dChge(this);" />
<?php
if($NameT3){
	$a=q("SELECT ID, Nodes_ID, ParentNodes_ID FROM gen_nodes_hierarchy WHERE GroupNodes_ID=$GroupNodes_ID AND Nodes_ID=$ParentNodes_ID", O_ROW);
	?><span class="bread"><span class="arrow">&lt;&nbsp;</span><a href="rsc_menu_gui_focus.php?ID=<?php echo $a['ID'];?>"><?php echo $NameT3;?></a></span><?php
}
if($NameT2){
	$a=q("SELECT ID, Nodes_ID, ParentNodes_ID FROM gen_nodes_hierarchy WHERE GroupNodes_ID=$GroupNodes_ID AND Nodes_ID=".$a['ParentNodes_ID'], O_ROW);
	?><span class="bread"><span class="arrow">&lt;&nbsp;</span><a href="rsc_menu_gui_focus.php?ID=<?php echo $a['ID'];?>"><?php echo $NameT2;?></a></span><?php
}
if($NameT1){
	$a=q("SELECT ID, Nodes_ID, ParentNodes_ID FROM gen_nodes_hierarchy WHERE GroupNodes_ID=$GroupNodes_ID AND Nodes_ID=".$a['ParentNodes_ID'], O_ROW);
	?><span class="bread"><span class="arrow">&lt;&nbsp;</span><a href="rsc_menu_gui_focus.php?ID=<?php echo $a['ID'];?>"><?php echo $NameT1;?></a></span><?php
}
?>
<br />
Found Under: 
<?php
if($nav=q("SELECT v.*, n.Name AS MenuName FROM _v_gen_nodes_hierarchy_nav v 
	LEFT JOIN gen_nodes n ON n.ID=v.GroupNodes_ID 
	WHERE 1 ORDER BY GroupNodes_ID", O_ARRAY_ASSOC)){
	//2013-07-24: better presentation of options - should make much more sense
	foreach($nav as $n=>$v){
		if(!strlen($n))continue;
		if(is_null($v['NameT4'])){
			//should not happen!
			mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err='_v_gen_nodes_hierarchy_nav has a bad configuration? no worries'),$fromHdrBugs);
			error_alert($err.', developer has been notified');
		}
		$start=(is_null($v['NameT3'])?4:(is_null($v['NameT2'])?3:(is_null($v['NameT1'])?2:1)));
		$j=0;
		for($i=$start; $i<=4; $i++){
			$j++;
			$nav[$n]['key'.$j]=$v['NameT'.$i];
		}
		for($i=$j+1; $i<=4; $i++){
			$nav[$n]['key'.$i]='';
		}
		$nav[$n]['key0']=$v['MenuName'];
		$nav[$n]['jat']=$j;
	}
	$nav=subkey_sort($nav,array('key0','key1','key2','key3','key4'));
	unset($nav2);
	foreach($nav as $n=>$v){
		$nav2[$v['ID']]=$v;
	}
	$nav=$nav2;
}
?>
<span id="pageNavWrap">
<select name="pageNav[]"  id="pageNav" style="max-width:405px;" onchange="dChge(this);navInterlock(this);">
	<?php if(false && $mode==$insertMode){ ?>
	<option value="">&lt;select..&gt;</option>
	<?php }else{ ?>
	<option value="default">(Default menu)</option>
	<?php } ?>
	<!-- <option value="{RBADDNEW}">&lt;Add new..&gt;</option> -->
	<?php
	/* query is fairly complex here */
	$i=0;
	if($nav)
	foreach($nav as $n=>$v){ 
		$i++;
		if($v['MenuName']!==$buffer){
			if($i>1)echo '</optgroup>';
			?><optgroup label="<?php echo $v['MenuName']?>">
			<option value="-<?php echo $v['GroupNodes_ID'];?>" style="background-color:aliceblue;" <?php
			if($v['GroupNodes_ID']==$GroupNodes_ID && is_null($ParentNodes_ID)){
				$originalPageNav[]=$GroupNodes_ID*-1;
				echo 'selected';
			}
			?>>&lt;<?php echo h($v['MenuName']);?> - root item..&gt;</option><?php
			$buffer=$v['MenuName'];
		}
		?><option value="<?php echo $n?>"<?php if($ID==$v['ID'])echo ' disabled="disabled"';?> <?php
		if($ParentNodes_ID==$v['Nodes_ID']){
			$originalPageNav[]=$n;
			echo 'selected';
		}
		
		#echo @in_array($mode==$insertMode ? $v['Nodes_ID'] : $n, $selectedNavNodes) ? 'selected' : '';
		?>><?php echo h(
		$v['NameT1'] . 
		($v['NameT1'] ? ' > ':'') . $v['NameT2'] . 
		($v['NameT2'] ? ' > ':'') . $v['NameT3'] . 
		($v['NameT3'] ? ' > ':'') . $v['NameT4']
		);?></option><?php
	}
	?>
	</optgroup>
</select>
<input type="hidden" name="originalPageNav" id="originalPageNav" value="<?php echo implode(',',$originalPageNav);?>" />
</span>
<br />
<br />
<h3>Priority/Position among siblings:</h3>
<?php
if($a=q("SELECT
h.ID, n.Name, h.Priority
FROM gen_nodes_hierarchy h JOIN gen_nodes n ON h.Nodes_ID=n.ID AND n.Type='Node' WHERE h.GroupNodes_ID=$GroupNodes_ID AND h.ParentNodes_ID".(is_null($ParentNodes_ID)?' IS NULL':'='.$ParentNodes_ID).' ORDER BY h.Priority',O_ARRAY)){
	?>
	<div class="fl" style="border:1px solid #ccc; padding:10px 15px; border-radius:10px;">
	<table class="yat">
	<?php
	foreach($a as $n=>$v){
		?><tr>
		<td class="number<?php echo $ID==$v['ID']?' selected':''?>">
		<input type="hidden" name="originalPriority[<?php echo $v['ID'];?>]" value="<?php echo $v['Priority'];?>" />
		<input type="text" name="Priority[<?php echo $v['ID'];?>]" value="<?php echo $v['Priority'];?>" size="2" maxlength="3" onchange="dChge(this);" /></td>
		<td class="name<?php echo $ID==$v['ID']?' selected':''?>"><a href="rsc_menu_gui_focus.php?ID=<?php echo $v['ID']?>" tabindex="-1"><?php echo $v['Name'];?></a></td>
		</tr><?php
	}
	?>
	</table>
	</div>
	<div class="fl" style="border:1px solid #ccc; padding:10px 15px; border-radius:10px;">
	Options:<br />
	<label><input type="checkbox" name="condense" id="condense" value="1" onchange="dChge(this);" /> Condense priority list</label><br />
	</div>
	<?php
}else{
	?><span class="gray">(No siblings present)</span><?php
}
?>
</div>
<h2>Modify Page(s) Attributes </h2>
<p>not developed </p>
<p>
  this will list pages and allow the following</p>
<ul>
  <li>viewing the page(s) attached to this node</li>
  <li>moving the page to another node</li>
  <li>adding another secondary page</li>
  <li>assigning primary to a different page in the group</li>
  <li>deleting this page (and optionally moving its content to another page) </li>
</ul>
<?php
prn($page);

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