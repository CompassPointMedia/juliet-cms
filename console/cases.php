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
//mini-settings for this page
$dataset='Member';



//------------------------ Navbuttons head coding v1.41 -----------------------------
//change these first vars and the queries for each instance
$object='Cases_ID';
$recordPKField='ID'; //primary key field
$navObject='Cases_ID';
$updateMode='updateCase';
$insertMode='insertCase';
$insertType=1; //1=Save&New and Save&Close; 2 = Save and Save&New
#set these to 'disabled' if desired
$saveInitiallyDisabled='';
$saveAndNewInitiallyDisabled='';
$saveAndCloseInitiallyDisabled='';
//v1.4 change - some information about the coding
$navVer='1.41';
//v1.3 change - declare this function if you need to add parameters to the query string
$navQueryFunction=''; //nav_query_add()
//v1.3 change - deny transiting from Next to New mode (shutting off ability to insert)
$denyNextToNew=false;
//declare the query to get the idSet or subset, ordered by desired sort order - note that if you're using quasi resources, then be sure and filter them out.
$ids=q("SELECT ID FROM gen_cases ORDER BY ID", O_COL);
/*
(another good example more complex)
$ids=q("SELECT ID FROM `$cc`.finan_invoices WHERE Accounts_ID='$Accounts_ID' ORDER BY InvoiceDate, CAST(InvoiceNumber AS UNSIGNED)",O_COL);
*/


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
if($mode==$updateMode){
	if($a=q("SELECT * FROM gen_cases WHERE ID=".$Cases_ID,O_ROW)){
		$mode=$updateMode;
		@extract($a);
	}
}
//--------------------------- end coding --------------------------------

$hideCtrlSection=false;
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/properties_04_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Console : Cases</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" href="/console/rbrfm_admin.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<style type="text/css">
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
var ctime='<?php echo $ctime?>';
var PHPSESSID='<?php echo $PHPSESSID?>';
//for nav feature
var count='<?php echo $nullCount?>';
var ab='<?php echo $nullAbs?>';
var isEscapable=1;
var isDeletable=1;
var isModal=1;
var talks=1; //whether this page broadcasts overall state changes which other pages listen for
var listens=0;

function assignFile(n){
	var buffer=g('submode').value;
	g('submode').value='assignFile';
	document.forms['form1'].submit();
	g('submode').value=buffer;
}

<?php
//default values
if($a=$defaultValues[$dataset]){
	foreach($a as $n=>$v)echo 'sets[\''.$n.'\']=\''.str_replace("'","\'",$v).'\';'."\n";
}
?>
</script>

<style type="text/css">
body{
	background-color:#CCC;
	}
.objectWrapper{
	padding:0px 20px;
	}
</style>
<!-- InstanceEndEditable -->
</head>

<body id="properties">
<form id="form1" name="form1" target="w2" method="post" action="/console/resources/bais_01_exe.php" onsubmit="return beginSubmit();" enctype="multipart/form-data">
<div id="header"><!-- InstanceBeginEditable name="top_nav" -->
	<div id="headerBar1" style="padding:5px 10px 10px 12px; background-color:#CCC;">
		<div id="btns140" style="float:right;">
		<!--
		Navbuttons version 1.41. Last edited 2008-01-21.
		This button set came from devteam/php/snippets
		Now used in a bunch of RelateBase interfaces and also client components. Useful for interfaces where sub-records are present and being worked on.
		-->
		<input id="Previous" type="button" name="Submit" value="Previous" onClick="focus_nav(-1, '<?php echo ($mode==$insertMode?'insert':'update')?>', <?php echo $mode==$insertMode?1:0?>, 0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $nullAbs<=1?'disabled':''?>>
		<?php
		//Handle display of all buttons besides the Previous button
		if($mode==$insertMode){
			if($insertType==2 /** advanced mode **/){
				//save
				?><input id="Save" type="button" name="Save" value="Save" onClick="focus_nav(0,'insert',1,2<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveInitiallyDisabled?> /><?php
			}
			//save and new - common to both modes
			?><input id="SaveAndNew" type="button" name="SaveAndNew" value="Save &amp; New" onClick="focus_nav(0,'insert', 1,1<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveAndNewInitiallyDisabled?> /><?php
			if($insertType==1 /** basic mode **/){
				//save and close
				?><input id="SaveAndClose" type="button" name="SaveAndClose" value="Save &amp; Close" onClick="focus_nav(0,'insert', 1,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveAndCloseInitiallyDisabled?> /><?php
			}
			?><input id="CancelInsert" type="button" name="CancelInsert" value="Cancel" onClick="focus_nav_cxl('insert');" /><?php
		}else{
			//OK, and appropriate [next] button
			?><input id="OK" type="button" name="ActionOK" value="OK" onClick="focus_nav(0,'<?php echo $mode==$insertMode?'insert':'update'?>',0,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" />
			<input id="Next" type="button" name="Next" value="Next" onClick="focus_nav(1,'<?php echo $mode==$insertMode?'insert':'update'?>',0,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $nullAbs>$nullCount || ($denyNextToNew && $nullAbs==$nullCount) ?'disabled':''?> /><?php
		}
		// *note that we could go back to the same page the 'New Record' click appeared on, but there's major issues programmatically on whether it would shift becuase of the placement of the new record.
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
		?>
		</div>
		<h2 class="nullBottom">Document Send </h2>
	</div>
<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->
<div class="objectWrapper">
<?php
$tabPrefix='docSend';
$cg[$tabPrefix]['CGLayers']=array(
    'Document'=>'docDocument',
    'Progress'=>'docProgress',
    'Help'    =>'docHelp'
);
if(!isset($cg[$tabPrefix]['defaultLayer'])){
    $cg[$tabPrefix]['defaultLayer']=current($cg[$tabPrefix]['CGLayers']);
}
$cg[$tabPrefix]['layerScheme']=2; //thin tabs vs old Microsoft tabs
$cg[$tabPrefix]['schemeVersion']=3.01;
$layerMinHeight=250;
//print tab HTML
require($MASTER_COMPONENT_ROOT.'/comp_tabs_v200.php');
//-------------------------------- first tab --------------------------
ob_start();
?>
	Name: <input name="Name" type="text" id="Name" value="<?php echo h($Name)?>" size="45" maxlength="75" onChange="dChge(this);" /><br />
	<label><input type="checkbox" name="RequireResponse" id="RequireResponse" value="1" onChange="dChge(this);" <?php echo !isset($RequireResponse) || $RequireResponse ? 'checked':''?> /> Require a Response</label>
	<br />
	<br />
	<input type="button" name="SelectDocument" id="SelectDocument" value="Select a Document" onChange="dChge(this);" />&nbsp;&nbsp;<strong><span id="PathToFile">C://whatever/whatever/files/look at me/mydogskip.gif</span></strong>
	<br /><br />

<?php
$fOdefaultFolder='library';
$fOBoxWidth='350';
$fOBoxHeight='350';
$fOJSObjectRelationship='.firstChild';
$fOSetFileTabNew=false;
$fOCallbackQuery='cbFunction=assignFile&cbParam=fixed:hello';
require($MASTER_COMPONENT_ROOT.'/imagemanagerwidget_01_v111.php');
	///admin/file_explorer/index.php?uid=fmw&folder=library/minutes&cbPathMethod=abs&disposition=selector&cbTarget=fmwFile&cbTargetExt=fmwExt&cbTargetNode=fmwPath&cbFunction=assignFile&cbParam=fixed:hello
	
?>	
<!--


	<div id="imgInset_1"><img src="/images/i/fex104/i-textdoc.png" width="95" height="94" /></div>
	<div class="cb">&nbsp;</div>

-->
<style type="text/css">
#imgInset_1{
	float:left;
	border:1px solid darkblue;
	padding:5px;
	margin:5px;
	margin-left:0px;
	cursor:pointer;
	}
</style>
<div id="title_1" class="title">
title here
</div>
<div id="imgInset_1" class="img" onClick="hm_cxlseq=2;showmenuie5(event);"><img 
src="/images/i/fex104/i-textdoc.png" 
alt="picture or file" 
filename=""
filepath="" 
size="" 
nofile="1" 
noimage="1" 
dims="" 
description="" 
mime=""
/></div>
<div class="cb">&nbsp;</div>

<?php
//require('components/comp_28_albumobject_v102.php');
?>

	<br /><br />

	Description (HTML is OK):
	<div style="background-color:#ccc;height:177px;">
	<div id="xToolbar" style="height:75px;background-color:cornsilk;"></div>
	<script language="javascript" type="text/javascript">
	var sBasePath= '/Library/fck6/';
	var oFCKeditor = new FCKeditor('Description');
	oFCKeditor.BasePath	= sBasePath;
	oFCKeditor.ToolbarSet = 'xTransitional';
	oFCKeditor.Height = 100;
	oFCKeditor.Config['ToolbarLocation'] = 'Out:xToolbar';
	oFCKeditor.Value = '<?php
	//output section text
	$a=@explode("\n",$Description);
	foreach($a as $n=>$v){
		$a[$n]=trim(str_replace("'","\'",$v));
	}
	echo implode('\n',$a);
	?>';
	oFCKeditor.Create() ;
	</script>
	</div>
	<br />
	<h2 class="nullBottom">Send To:</h2>
	<label><input type="checkbox" name="AllMembers" id="AllMembers" value="1" onChange="dChge(this);" /> All Members</label> <br />
	Categories:<br />
	<select name="Category" id="Category" onChange="dChge(this);">
		<option>Group 1</option>
		<option>Group 2</option>
		<option>Group 3</option>
	</select>
	<br />
	<br />
	CC to:&nbsp;<input type="text" name="CC" id="CC" onChange="dChge(this);" size="60" />
	<br />
<?php
//-------------------------------- store tab 1 --------------------------
get_contents_layer('docDocument');
?>
	progress
<?php
//-------------------------------- store tab 2 --------------------------
get_contents_layer('docProgress');
?>
	help with this page
<?php
//-------------------------------- store tab 3 --------------------------
get_contents_layer('docHelp');
//-------------------------- done, now output tabs ----------------------
$tabAction='layerOutput';
require($MASTER_COMPONENT_ROOT.'/comp_tabs_v200.php');
?>

</div>
<input type="submit" value="Send Document" name="Submit" />
<!-- InstanceEndEditable --></div>
<div id="footer"><!-- InstanceBeginEditable name="footer" -->
footer
<!-- InstanceEndEditable --></div>
</form>
<?php if(!$hideCtrlSection){ ?>
<div id="showTester" title="Javascript Tester" onClick="g('tester').style.display='block';">&nbsp;</div>
<div id="tester" >
	<a href="#" onClick="g('ctrlSection').style.display='block';return false;">Show Control Section</a><br />
	<textarea name="test" cols="65" rows="4" id="test">clear_form();</textarea><br />
	<input type="button" name="button" value="Test" onClick="jsEval(g('test').value);"><br />
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