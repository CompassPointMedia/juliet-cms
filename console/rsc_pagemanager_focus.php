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
$object='Nodes_ID';
$recordPKField='ID'; //primary key field
$navObject='Nodes_ID';
$updateMode='updatePage';
$insertMode='insertPage';
$deleteMode='deletePage';
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
$ids=q("SELECT ID FROM _v_pages_juliet WHERE 1 ORDER BY Name",O_COL);

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
	$a=q("SHOW CREATE VIEW _v_pages_juliet", O_ROW);
	if(!strstr($a['Create View'],'`Class`') || !strstr($a['Create View'],'`URL`')){
		ob_start();
		q("ALTER TABLE gen_nodes ADD `URL` TEXT AFTER `ComponentLocation`", ERR_ECHO);
		q("CREATE OR REPLACE VIEW `_v_pages_juliet` AS select `n`.`ID` AS `ID`,`n`.`Active` AS `Active`,`n`.`SystemName` AS `SystemName`,`n`.`Name` AS `Name`,`n`.`PageType` AS `PageType`,`n`.`Class` AS `Class`, `n`.`ComponentLocation` AS `ComponentLocation`, `n`.`URL` AS `URL`,`o`.`Settings` AS `Settings`,`m`.`Title` AS `Title`,`m`.`Description` AS `Description`,`m`.`Keywords` AS `Keywords`,`m`.`TTable` AS `TTable`,`m`.`TField` AS `TField`,`m`.`TVar1` AS `TVar1`,`m`.`TVar2` AS `TVar2`,`m`.`DTable` AS `DTable`,`m`.`DField` AS `DField`,`m`.`DVar1` AS `DVar1`,`m`.`DVar2` AS `DVar2`,count(distinct `h`.`GroupNodes_ID`) AS `Menus`,`h`.`GroupNodes_ID` AS `GroupNodes_ID`,`h`.`Rlx` AS `Rlx`,`g`.`SystemName` AS `MenuSystemName`,`g`.`Name` AS `MenuName`,count(distinct `s`.`Section`) AS `Sections`,greatest(`n`.`EditDate`,`m`.`EditDate`,`s`.`EditDate`,`o`.`EditDate`) AS `EditDate` from (((((`gen_nodes` `n` left join `gen_nodes_settings` `o` on((`n`.`ID` = `o`.`Nodes_ID`))) left join `gen_nodes_hierarchy` `h` on((`n`.`ID` = `h`.`Nodes_ID`))) left join `gen_nodes` `g` on((`h`.`GroupNodes_ID` = `g`.`ID`))) left join `site_metatags` `m` on((`n`.`ID` = `m`.`Objects_ID`))) left join `cmsb_sections` `s` on((`n`.`ID` = `s`.`Objects_ID`))) where ((`n`.`Type` = 'Object') and (`n`.`Category` = 'Website Page')) group by `n`.`ID`", ERR_ECHO);
		$err=ob_get_contents();
		ob_end_clean();
		if($err)mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err),$fromHdrBugs);		
	}
	//get the record for the object
	if($page=q("SELECT * FROM _v_pages_juliet WHERE ID='".$$object."'",O_ROW)){
		$mode=$updateMode;
		@extract($page);
		@$Settings=unserialize(base64_decode($Settings));
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
<title>Page Manager</title>
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

function componentInterlock(o){
	if(o.value=='{CUSTOM_PHP_EDITABLE}' || o.value=='{redirect_soft}'){
		g('FreeContent').style.display='block';
		g('FreeContent').focus();
	}else{
		g('FreeContent').style.display='none';
	}
}
function yaf(o){
	if(n=g('pageNav').value){
		ow(o.href+n,'l2_nav','500,500');
	}else{
		alert('select a nav menu item first');
	}
	return false;
}
</script>


<!-- following coding modified from ajaxloader.info - a long way to go to be modular -->
<script language="javascript" type="text/javascript">
function navInterlock(o){
	<?php if($mode==$insertMode){ ?>
	if(o.value!='')g('disposition2').checked=true;
	if(o.value.indexOf('-')!= -1){
		g('pageUse2').disabled=true;
		g('pageUse3').disabled=true;
		g('pageUse1').checked=true;
		g('MenuPage').innerHTML='Menu';
		g('Name').focus();
	}else{
		g('pageUse2').disabled=false;
		g('pageUse3').disabled=false;
	}
	<?php } ?>
}
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

<h1 class="nullTop"><?php echo $mode==$insertMode?'Create a Page':'Edit Page'?></h1>

<?php
ob_start();

if($mode==$insertMode){
	//disposition, pageUse and pageNav can be passed
	@$selectedNavNodes=explode(',',$pageNav);
}else{
	$selectedNavNodes=q("SELECT h1.ID FROM gen_nodes_hierarchy h1, gen_nodes_hierarchy h2 WHERE h1.Nodes_ID=h2.ParentNodes_ID AND h2.Nodes_ID='".$page['ID']."'", O_COL);
	$disposition=(q("SELECT COUNT(DISTINCT ParentNodes_ID) FROM gen_nodes_hierarchy h WHERE Nodes_ID='$Nodes_ID'", O_VALUE) ? 2 : 1);
}


ob_start();
if($nav=q("SELECT v.*, n.Name AS MenuName FROM _v_gen_nodes_hierarchy_nav v LEFT JOIN gen_nodes n ON n.ID=v.GroupNodes_ID WHERE 1 ORDER BY GroupNodes_ID", O_ARRAY_ASSOC)){
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
//prn($nav,1);
?>
<input type="hidden" name="originalPageNav" id="originalPageNav" value="<?php echo @implode(',',$selectedNavNodes)?>" />
<span id="pageNavWrap">
<select name="pageNav[]"  id="pageNav" style="max-width:225px;" onchange="dChge(this);navInterlock(this);">
	<?php if($mode==$insertMode){ ?>
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
			<option value="-<?php echo $v['GroupNodes_ID'];?>" style="background-color:aliceblue;">&lt;<?php echo h($v['MenuName']);?> - root item..&gt;</option><?php
			$buffer=$v['MenuName'];
		}
		?><option value="<?php echo $n?>" <?php 
		echo @in_array($mode==$insertMode ? $v['Nodes_ID'] : $n, $selectedNavNodes) ? 'selected' : '';
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
</span>
<?php
$menuSelect=ob_get_contents();
ob_end_clean();
?>
<div class="fr">
Page is: <select name="Active" id="Active" onchange="dChge(this);" >
    <option <?php echo $Active==8 ?'selected':''?> value="8">Active</option>
    <option <?php echo $Active==4 ?'selected':''?> value="4">Test Level 2</option>
    <option <?php echo $Active==2 ?'selected':''?> value="2">Test Level 1</option>
    <option <?php echo $Active==1 ?'selected':''?> value="1">Draft</option>
    <option <?php echo $Active==='0' ?'selected':''?> value="0">Inactive</option>
  </select>
</div>
<label>
<input name="disposition" id="disposition1" type="radio" onchange="dChge(this);" value="1" <?php echo $disposition==1?'checked':''?> onclick="if(this.checked){ g('pageUse2').checked=true; }" />
 no menu associated with this page</label>
<br />
<br />
<?php 
if($mode==$insertMode){
	?>
	<label><input name="disposition" id="disposition2" type="radio" <?php echo $disposition==2?'checked':''?> value="2" onchange="dChge(this);" /> 
	attach page to the following menu item: </label>
	<?php echo $menuSelect?>
	<br />
	<div style="padding-left:25px;">
	  <label>
		<input name="pageUse" id="pageUse2" type="radio" value="2" <?php echo $pageUse==2 || !$pageUse?'checked':''?> onchange="dChge(this);" onclick="if(this.checked){g('disposition2').checked=true; } g('MenuPage').innerHTML='Page';" /> 
	  as the new primary page for the selected  menu item above</label>
		<br />
		<label>
		<input name="pageUse" id="pageUse3" type="radio" value="3" <?php echo $pageUse==3?'checked':''?> onchange="dChge(this);" onclick="if(this.checked){  g('disposition2').checked=true; } g('MenuPage').innerHTML='Page';" />
		as a secondary page for the selected menu item above	</label>
		<br />
	  <label>
		<input name="pageUse" type="radio" id="pageUse1" value="1" <?php echo $pageUse==1?'checked':''?> onchange="dChge(this);" onclick="if(this.checked){ g('MenuPage').innerHTML='Menu'; g('Name').focus(); g('disposition2').checked=true; }" />
	  as a new menu item AND page below the selected  menu item above</label>
		<br />
	</div>
	<span id="MenuPage"> <?php echo $pageUse<3?'Page':'Menu';?></span> label <span class="gray">(35 characters max)</span>: 
	<input name="Name" type="text"  id="Name" onchange="dChge(this);" size="35" maxlength="75" />
	<br />
	<br />
	
	<?php 
}else{
	?>
	<label>
	<?php echo '<input name="disposition" id="disposition2" type="radio" value="2" '.($disposition==2?'checked':'').' onchange="dChge(this);" />';?>
	part of the following menu:	</label>
	<br />
	<?php echo $menuSelect?> <a href="/console/rsc_navmanager.php?HierarchyNodes_ID=" onclick="return yaf(this);" title="Rename your link">Click To Rename</a>
	
	<div style="padding-left:20px;">
	<label>
	<input name="pageUse" type="radio" value="2" onchange="dChge(this);" <?php if($page['Rlx']=='Primary' || !$page['Rlx'])echo $isPrimary='checked';?> /> 
	primary page</label>
	<br />
	<label <?php echo $isPrimary?'class="gray"':''?>>
	<input name="pageUse" type="radio" value="3" onchange="dChge(this);" <?php if($page['Rlx']=='Secondary')echo 'checked';?> <?php echo $isPrimary?'disabled':''?> /> 
	secondary page</label> <?php if($isPrimary){ ?>
	<span class="gray">(To make this page a secondary page, set another page as primary)</span>
	<?php } ?>
	<br /><br />
	<?php
	if(rand(1,5)==2)mail($developerEmail, 'Notice file '.__FILE__.', line '.__LINE__,get_globals('the Name field in this page needs to logically consider the {root_website_page} and other registered types in its presentation - otherwise it just shows as blank; somehting like a ddl with an interlock, where if it\'s a {r_w_p} the Name field is disabled; but that means I need to deal with registry of types.  BTW I\'m thinking 10-50% of the columns in nodes and nodes_hierarchy are irrelevant or redundant to queries even with all that these tables are meant to accomplish'),$fromHdrBugs);
	?>
	Page is a: 
	<select name="SystemName"  id="SystemName" onchange="dChge(this); g('Name').disabled=(this.value=='{root_website_page}'); if(this.value!='{root_website_page}')g('Name').focus();">
	  <option value="" <?php echo !$SystemName?'checked':''?>>Normal Page</option>
	  <option value="{root_website_page}" <?php echo $SystemName=='{root_website_page}'?'selected':''?>>Home page for the site</option>
	  <option value="{redirect_soft}" <?php echo $SystemName=='{redirect_soft}'?'selected':''?>>Soft Redirect (see help)</option>
	</select>
	<br />
	Menu label  <span class="gray">(35 characters max)</span>:
	<input name="Name" type="text"  id="Name" onchange="dChge(this);" value="<?php echo $Name?>" size="20" maxlength="75" <?php echo $mode==$updateMode && $SystemName!=''?'disabled':''?> />
	</div><?php
}
?>
<br />
<br />
<?php if($mode==$insertMode){ ?>
Page is a: 
<select name="SystemName"  id="SystemName" onchange="dChge(this); g('Name').disabled=(this.value=='{root_website_page}'); if(this.value!='{root_website_page}')g('Name').focus();">
  <option value="" <?php echo !$SystemName?'checked':''?>>Normal Page</option>
  <option value="{root_website_page}" <?php echo $SystemName=='{root_website_page}'?'selected':''?>>Home page for the site</option>
  <option value="{redirect_soft}" <?php echo $SystemName=='{redirect_soft}'?'selected':''?>>Soft Redirect (see help)</option>
</select><br />
<?php } ?>

Class: <?php
//this hijacks the function to do a SELECT DISTINCT list with add-new capability
$options=array(
	'a'=>array(
		'AddThroughModification'=>'distinct',
		'ForeignKeyField'=>'Class',
		'AllowAddNew'=>true,
		'AddThrough'=>'simple',
		'InsertLabel'=>'< Select.. >',
		'MapsToField'=>'DISTINCT Class',
		'LabelField'=>'Class',
		'InTable'=>'gen_nodes',
		'JoinType'=>'oneToMany',
		'AllowBlankOnUpdates'=>'(none)',
		'oneToManyDatasetWhere'=>'Class!=\'\''
	),
	'configNode'=>'Class',
);
echo relatebase_dataobjects_settings('Class',$options);
?> <em class="gray">(Used for CSS)</em>
<br />
Title: 
<input name="Title" type="text"  id="Title" onblur="if(g('Description').value=='')g('Description').value=this.value;" onchange="dChge(this);" value="<?php echo h($Title);?>" size="35" maxlength="255" />
<br />
Description: 
<input name="Description" type="text"  id="Description" onchange="dChge(this);" value="<?php echo h($Description);?>" size="75" maxlength="255" />
<br />
Page type:
<select name="PageType"  id="PageType" onchange="dChge(this);">
  <option value="default" <?php if($PageType=='default') echo 'selected';?>>Standard Page</option>
  <option value="advanced" <?php if($PageType=='advanced') echo 'selected';?>>Advanced Page</option>
	<?php
	if($consoleEmbeddedModules)
	foreach($consoleEmbeddedModules as $n=>$v){
		if(empty($v['moduleAdminSettings']) || preg_match('/^rsc-[0-9]+/i',$v['moduleAdminSettings']['SKU']))continue;
		?><optgroup label="<?php echo $v['moduleAdminSettings']['name']?>"><?php
		foreach($v['moduleAdminSettings']['flow'] as $o=>$w){
			?><option value="<?php echo $v['moduleAdminSettings']['handle'].':'.$o?>" <?php echo $PageType==$v['moduleAdminSettings']['handle'].':'.$o?'selected':''?>><?php echo $w['name']?></option><?php
		}
		?></optgroup><?php

	}
	/* this was orphaned; couldn't find where moduleAdminSettings declared in console OR juliet; changed to above
	if($moduleAdminSettings){
		foreach($moduleAdminSettings as $n=>$v){
			?><optgroup label="<?php echo $v['name']?>"><?php
			foreach($v['flow'] as $o=>$w){
				?><option value="<?php echo $v['handle'].':'.$o?>" <?php echo $PageType==$v['handle'].':'.$o?'selected':''?>><?php echo $w['name']?></option><?php
			}
			?></optgroup><?php
		}
	}
	*/
	?>
</select><br />
Alternate URL: 
<input name="URL" type="text"  id="URL" onchange="dChge(this);" value="<?php echo h($URL);?>" size="75" maxlength="255" />
<br />
<p class="gray">(This is only used in some nav bar menus)</p>
<?php
$buffer=$COMPONENT_ROOT;
$COMPONENT_ROOT=$_SERVER['DOCUMENT_ROOT'].'/components';
$JULIET_COMPONENT_ROOT=$_SERVER['DOCUMENT_ROOT'].'/components-juliet';
require_once($FUNCTION_ROOT.'/function_pJprocess_folder.php');
pJprocess_folder(array(
	'files'=>get_file_assets($JULIET_COMPONENT_ROOT),
	'folder'=>'JULIET_COMPONENT_ROOT',
));
pJprocess_folder(array(
	'files'=>get_file_assets($COMPONENT_ROOT),
	'folder'=>'COMPONENT_ROOT',
	'requireDocumentation'=>true,
));
if(!strstr(trim($ComponentLocation),"\n")){
	if(!strstr($ComponentLocation,"\n")){
		//single call must be on one line
		if(strstr($ComponentLocation,'$JULIET_COMPONENT_ROOT')){
			$desiredAction=str_replace('<?php require($JULIET_COMPONENT_ROOT.\'/','',$ComponentLocation);
			$desiredAction=str_replace('\');?>','',$desiredAction);			
			$desiredActionLocation='$JULIET_COMPONENT_ROOT';
		}else if(strstr($ComponentLocation,'$COMPONENT_ROOT')){
			$desiredAction=str_replace('<?php require($COMPONENT_ROOT.\'/','',$ComponentLocation);
			$desiredAction=str_replace('\');?>','',$desiredAction);			
			$desiredActionLocation='$COMPONENT_ROOT';
		}else if($SystemName=='{redirect_soft}'){
			$desiredAction=$SystemName;
		}else{
			//leave content as is
		}
	}
}
?>
Component Location or PHP Coding: <select name="desiredAction"  id="desiredAction" onChange="dChge(this);componentInterlock(this);" style="width:350px;">
<option value="">(None)</option>
<?php
if($pJprocess_folder['output']){
	$start=true;
	foreach($pJprocess_folder['output'] as $n=>$v){
		$a=explode(':',$n);
		if($a[0]!=$buffer){
			if(!$start)echo '</optgroup>';
			$buffer=$a[0];
			echo '<optgroup label="'.(stristr($a[0],'$JULIET_COMPONENT_ROOT') ? 'Juliet Components' : 'Global Components').'">';
			$start=false;
		}
		?><option value="<?php echo $n?>" <?php echo strtolower($n)==strtolower($desiredActionLocation.':'.$desiredAction)?$selected='selected':''?> <?php if(strstr($v,'{02}'))echo 'class="gray it"';?><?php if(substr($v,0,3)=='   ')echo ' style="padding-left:45px;"';?>><?php echo $v;?></option><?php
	}
	echo '</optgroup>';
}
?>
<optgroup label="Advanced">
<option value="{CUSTOM_PHP_EDITABLE}" <?php echo trim($ComponentLocation) && !$selected?'selected':'';?>>Custom Coding (PHP allowed)</option>
</optgroup>
<optgroup label="Redirect String">
<option value="{redirect_soft}" <?php echo $desiredAction=='{redirect_soft}'?'selected':''?>>Redirect String</option>
</optgroup>
</select>
<?php
$COMPONENT_ROOT=$buffer;
?>
<textarea name="FreeContent" class="tabby" cols="65" rows="10"  id="FreeContent" style="display:<?php echo trim($ComponentLocation) && !$selected?'block':'none';?>" onChange="dChge(this);"><?php echo h($ComponentLocation);?></textarea>
<br />
<br />

<fieldset><legend>Additional Settings</legend>
<input type="hidden" name="Settings[ViewLoggedIn]" value="0" />
<label>
<input name="Settings[ViewLoggedIn]" type="checkbox" id="Settings[ViewLoggedIn]" value="1" onchange="dChge(this);" <?php echo $Settings['ViewLoggedIn']?'checked':''?> />
View logged-in only	</label>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Logged-in level required:
<select name="Settings[LoginLevelRequired]" id="Settings[LoginLevelRequired]" onchange="dChge(this);" >
  <option value="client" <?php echo strtolower($Settings['client'])=='client'?'selected':''?>>Client/Member</option>
  <option value="contact" <?php echo strtolower($Settings['contact'])=='client'?'selected':''?>>Contact</option>
</select>
<br />

<input type="hidden" name="Settings[SearchableLoggedOut]" value="0" />
<label>
<input name="Settings[SearchableLoggedOut]" type="checkbox" id="Settings[SearchableLoggedOut]" value="1" onchange="dChge(this);" <?php echo $Settings['SearchableLoggedOut']?'checked':''?> />
Searchable  when logged out	</label>
<br />
<input type="hidden" name="Settings[ShowLinkLoggedOut]" value="0" />
<label>
<input name="Settings[ShowLinkLoggedOut]" type="checkbox" id="Settings[ShowLinkLoggedOut]" value="1" onchange="dChge(this);" <?php echo $Settings['ShowLinkLoggedOut']?'checked':''?> />
Show link even when logged out	</label>
<br />
Custom CSS this page (declared in &lt;head&gt; of page):<br />
<textarea name="Settings[CustomCSS]" class="tabby" cols="45" rows="5"  id="Settings[CustomCSS]" onchange="dChge(this);"><?php echo h($Settings['CustomCSS']);?></textarea>
<br />
<br />
Advanced Settings<br />
Block suppression override: 
<input type="text" size="35" name="Settings[BlockSuppressionOverride]" id="Settings[BlockSuppressionOverride]" onchange="dChge(this);" value="<?php echo h($Settings['BlockSuppressionOverride']);?>" /> 
<em class="gray">(separate by commas)</em>
</fieldset>

<?php
get_contents_tabsection('main');

//2013-09-18
$buffer=$adminMode;
$adminMode=2;
CMSB(array(
	'section'=>'rsc_pagemanager_focus',
	'cnx'=>$public_cnx, /*array(
		'localhost','relatebase','*****','z_public'
	),*/
	'passCnx'=>true,
));
$adminMode=$buffer;


get_contents_tabsection('help');
tabs_enhanced(array(
	'main'=>array(
		'label'=>'Edit Page'
	),
	'help'=>array(
		'label'=>'Help'
	),
));

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