<?php 
/*
2011-08-01
what needs to happen here is that a nodal window pops up with the id selected on a ddl for the node we want to add under (changeable), and the generic adding form.  Submission passes a mode=component & submode=that modal action.  and from there, what will be new is inserting the new coding block AFTER or wherever it goes vs. reloading the component.  the other issue will be outputting just that piece vs. the whole dataset (if done well, it will be the big step forward I've needed for large datasets 

the other jQuery thing is where we say "move" and a modal window pops up, operations are done on the select element "as if" the form was a proliferated part of each dataset, and then the same mode/submode action happens as listed above.

finally, it would be nice to simply be able to drag the blocks around and on mouse over a border, it highlights to say "I'll be the new parent for the div you're draggin around

toggling: given a div and a nextSibling div, toggle smoothly and cleanly through displaying and hiding, with a fade-in included



and then, finally, there are menus which I want to "hook together"


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
$object='Templates_ID';
$recordPKField='ID'; //primary key field
$navObject='Nodes_ID';
$updateMode='updateTemplate';
$insertMode='insertTemplate';
$deleteMode='deleteTemplate';
$insertType=1; //1=Save&New and Save&Close; 2 = Save and Save&New
#set these to 'disabled' if desired
$saveInitiallyDisabled='';
$saveAndNewInitiallyDisabled='';
$saveAndCloseInitiallyDisabled='';
//v1.4 change - some information about the coding
$navVer='1.43';
//v1.3 change - declare this function if you need to add parameters to the query string
$navQueryFunction='nav_query_add()';
//v1.3 change - deny transiting from Next to New mode (shutting off ability to insert)
$denyNextToNew=true;
//declare the query to get the idSet or subset, ordered by desired sort order - note that if you're using quasi resources, then be sure and filter them out.
$ids=q("SELECT ID FROM gen_templates WHERE 1 ORDER BY Name",O_COL);

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
	//get the record for the object
	if($template=q("SELECT * FROM gen_templates WHERE ID='".$$object."'",O_ROW)){
		$mode=$updateMode;
		@extract($template);
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
<title>Template Manager</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" href="/Library/css/cssreset01.css" type="text/css" />
<link rel="stylesheet" href="rbrfm_admin.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<style type="text/css">
/* local CSS styles */
</style>

<script language="JavaScript" type="text/javascript" src="/Library/js/global_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/common_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/forms_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/console/console.js"></script>
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
</script>


<!-- following coding modified from ajaxloader.info - a long way to go to be modular -->
<style type="text/css">
h1,h2,h3,h4{
	font-family:Georgia, "Times New Roman", Times, serif;
	}
.block{
	border-left:1px solid #666;
	border-bottom:1px solid #666;
	margin:2px;
	margin-left:0px;
	margin-top:30px;
	margin-bottom:20px;
	padding:5px;
	padding-top:0px;
	padding-left:20px;
	}
.hierHeader{
	margin-top:-25px;
	margin-left:-20px;
	padding-left:5px;
	padding-right:5px;
	background-color:#eee;
	}
.hier{
	font-family:"Courier New", Courier, monospace;
	}
.dynExpTA{
	width:400px;
	}
</style>
<script language="javascript" type="text/javascript">
function toggle(o){
	o.nextSibling.style.display=(o.nextSibling.style.display=='none'?'block':'none');
}
</script>

<!-- InstanceEndEditable -->
</head>

<body id="report">
<?php if(!$suppressForm){ ?>
<form action="../console/resources/bais_01_exe.php" method="post" enctype="multipart/form-data" name="form1" target="w2" id="form1" onsubmit="return beginSubmit();">
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
	?><input id="OK" type="button" name="Submit" value="OK" class="navButton_A" onclick="focus_nav(0,'<?php echo $mode==$insertMode?'insert':'update'?>',0,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);">
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
?><!-- end navbuttons 1.43 --></div>

<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->

<h1><?php echo $mode==$insertMode?'Edit Template Block Structure':'Edit Template Block Structure'?></h1>


Name: 
<input name="Name" type="text" id="Name" value="<?php echo $Name;?>" size="25" maxlength="30" />
<br />
Description:<br />
<textarea name="Description" cols="50" rows="3" id="Description"><?php echo h($Description);?></textarea>
<br />
<br /> 
Blocks:<br /><?php
$a=q("EXPLAIN gen_templates_blocks", O_ARRAY_ASSOC);
if(!$a['Blocks_ID']){
	q("ALTER TABLE `gen_templates_blocks` ADD `Blocks_ID` INT( 11 ) UNSIGNED NULL DEFAULT NULL COMMENT 'Added on the fly' AFTER `Templates_ID` ,
	ADD INDEX ( `Blocks_ID` )");
}
if(!$a['Parameters']){
	q("ALTER TABLE `gen_templates_blocks` ADD `Parameters` TEXT NOT NULL AFTER `Content` ");
}
function get_blocks($Templates_ID,$Blocks_ID=NULL, $level=0){
	global $get_blocks;
	if($a=q("SELECT * FROM gen_templates_blocks WHERE Templates_ID=$Templates_ID AND Blocks_ID".(!$Blocks_ID?' IS NULL':"='$Blocks_ID'")." ORDER BY ID", O_ARRAY)){
		foreach($a as $v){
			$get_blocks[$v['ID']]=array($level,$v['Name'],$Blocks_ID /* parent */);
			?><div class="block">
			<h3 class="hierHeader" onclick="toggle(this);"><a href="javascript:void();"><img src="/images/i/plusminus-plus.gif" /></a> <?php echo $v['Name']; ?></h3><div style="display:block;">
			Content:<br />
			<textarea name="Content[<?php echo $v['ID']?>]" cols="90%" id="Content[<?php echo $v['ID']?>]" style="margin-bottom:0px;height:16px;" class="dynExpTA" onkeyup="ta(this,'keyup')" onfocus="ta(this,'focus');" onblur="ta(this,'blur');" onchange="dChge(this);"><?php echo h($v['Content']);?></textarea>
			<br />
			Parameters:<br />
			<textarea name="Parameters[<?php echo $v['ID']?>]" cols="90%" id="Parameters[<?php echo $v['ID']?>]" style="margin-bottom:0px;height:16px;" class="dynExpTA" onkeyup="ta(this,'keyup')" onfocus="ta(this,'focus');" onblur="ta(this,'blur');" onchange="dChge(this);"><?php echo h($v['Parameters']);?></textarea>
			<br />
			<?php
			$l=$level+1;
			get_blocks($Templates_ID,$v['ID'], $l);
			?></div>
			</div><?php
		}
		?>
		[<a href="javascript:alert('not developed');">Add..</a>]
		<?php
	}
}
ob_start();
get_blocks($ID); 
$out=ob_get_contents();
ob_end_clean();
echo $out;

//this will be used later in moving a block ot anohter position..
ob_start();
?>
<select name="hierarchy" id="hierarchy">
<?php
if($get_blocks)
foreach($get_blocks as $n=>$v){
	?><option class="hier" value="<?php echo $n?>"><?php echo str_repeat('&nbsp;&nbsp;&nbsp;',$v[0]) . $v[1]?></option><?php
}
?>
</select>
<?php
$select=ob_get_contents();
ob_end_clean();
?>
</p>
<script language="javascript" type="text/javascript">
//initial height
for(var i=0; i<document.getElementsByTagName('textarea').length; i++){
	var j=document.getElementsByTagName('textarea')[i];
	if(j.className=='dynExpTA'){
		try{ 
		j.style.height=0;
		j.style.height=j.scrollHeight+'px'
		}catch(e){ }
	}
}

</script>
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