<?php 
if(strlen($sessionid)) session_id($sessionid);
session_start();
$sessionid ? '' : $sessionid = session_id();


$localSys['scriptID']='view_generic';
$localSys['scriptVersion']=1.0;
$localSys['pageType']='Properties Window';




//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');

if($storagemethod=='module'){
	if(!is_array($moduleConfig['dataobjects'][$object]['joins']))$moduleConfig['dataobjects'][$object]['joins']=array();
	$config=&$moduleConfig['dataobjects'][$object];
	$joins=&$config['joins'];
	$fieldList=q("EXPLAIN $object", O_ARRAY);
}else{
	exit('no storage method var');
}


$hideCtrlSection=false;
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/properties_04_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Settings Manager for Items</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" href="/console/rbrfm_admin.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<style type="text/css">
body{
	background-color:#CCC;
	}
.objectWrapper {
	background-color:#CCC;
	min-height:400px;
	}
.objectWrapper1 {
	background-color:#CCC;
	min-height:400px;
	}
#header{
	height:inherit;
	border-bottom:1px dotted #000;
	position:relative;
	background-image:none;
	}
<?php if($ParentItems_ID){ ?>
#Previous, #Save, #Next{
	display:none;
	}
<?php }?>
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
var browser='<?php echo $browser?>';
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
</script>

<?php if(false || $tabbedMenus){
	?>
	<link rel="stylesheet" href="/Library/css/DHTML/layer_engine_v301.css" type="text/css" />
	<?php
	$cg[1]['CGPrefix']="itemfocus";
	$cg[1]['CGLayers']=array('description', 'pricing', 'manufacturer', 'attributes', 'media', 'help');
	if($IsPackage){
		$cg[1]['CGLayers']=array_merge( array('parts'), $cg[1]['CGLayers'] );
	}
	$cg[1]['defaultLayer']=($IsPackage ? 'parts' : 'company');
	$cg[1]['layerScheme']=2; //thin tabs vs old Microsoft tabs
	$cg[1]['schemeVersion']=3.01;
	$activeHelpSystem=true;
	//this will generate JavaScript, all instructions are found in this file
	?><?php
	require('../Library/css/DHTML/layer_engine_v301.php');
	?><?php
}
?>
	<style type="text/css">
	</style>
<!-- InstanceEndEditable -->
</head>

<body id="properties">
<form id="form1" name="form1" target="w2" method="post" action="/console/resources/bais_01_exe.php" onsubmit="return beginSubmit();" enctype="multipart/form-data">
<div id="header"><!-- InstanceBeginEditable name="top_nav" -->
	<div id="headerBar1" style="padding:5px 10px 10px 12px;">
		<div id="btns140" style="float:right;">
		
		<!--
		Navbuttons version 1.41. Last edited 2008-01-21.
		This button set came from devteam/php/snippets
		Now used in a bunch of RelateBase interfaces and also client components. Useful for interfaces where sub-records are present and being worked on.
		-->
		<?php
		//Handle display of all buttons besides the Previous button
		$insertMode='insertDataobjectSettings';
		$updateMode='updateDataobjectSettings';
		$mode=$updateMode;
		if($mode==$insertMode){
			if($IsPackage){
				$btn=' Save ';
			}else if($ParentItems_ID){
				$btn='Add Item';
			}else{
				$btn='Save &amp; New';
			}
			if($insertType==2 /** advanced mode **/){
				//save
				?><input id="Save" type="button" name="Save" value="Save" onClick="focus_nav(0,'insert',1,2<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveInitiallyDisabled?> /><?php
			}
			//save and new - common to both modes
			?><?php
			if($insertType==1 /** basic mode **/){
				//save and close
				?><input id="SaveAndClose" type="button" name="SaveAndClose" value="Save &amp; Close" onClick="focus_nav(0,'insert', 1,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveAndCloseInitiallyDisabled?> /><?php
			}
			?><?php
		}else{
			//OK, and appropriate [next] button
			?>&nbsp;
			<input id="Save" type="button" name="ActionOK" value="Save" onClick="focus_nav(0,'<?php echo $mode==$insertMode?'insert':'update'?>',0,2<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" />
			<?php
		}
		// *note that we could go back to the same page the 'New Record' click appeared on, but there's major issues programmatically on whether it would shift becuase of the placement of the new record.
		// *note that the primary key field is now included here to save time
		?>
		<input id="CancelInsert" type="button" name="CancelInsert" value="Cancel" onClick="focus_nav_cxl('insert');" />
		<input name="object" type="hidden" id="object" value="<?php echo $object?>" />
		<input name="insertMode" type="hidden" id="insertMode" value="insertDataobjectSettings" />
		<input name="storagemethod" type="hidden" id="storagemethod" value="<?php echo $storagemethod?>" />
		<input name="updateMode" type="hidden" id="updateMode" value="updateDataobjectSettings" />
		<input name="navMode" type="hidden" id="navMode" />
		<input name="nav" type="hidden" id="nav" />
		<input name="mode" type="hidden" id="mode" value="updateDataobjectSettings" />
		<?php
		if(count($_REQUEST)){
			foreach($_REQUEST as $n=>$v){
				if(substr($n,0,2)=='cb'){
					if(!$setCBPresent){
						$setCBPresent=true;
						?><!-- callback fields automatically generated --><?php
						echo "\n";
						?>
		<input name="cbPresent" id="cbPresent" value="1" type="hidden" /><?php
						echo "\n";
					}
					if(is_array($v)){
						foreach($v as $o=>$w){
							echo "\t\t";
							?><input name="<?php echo $n?>[<?php echo is_numeric($o)? '': $o?>]" id="<?php echo $n?>[<?php echo is_numeric($o)? '': $o?>]" type="hidden" value="<?php echo h(stripslashes($w))?>" /><?php
							echo "\n";
						}
					}else{
						echo "\t\t";
						?><input name="<?php echo $n?>" id="<?php echo $n?>" type="hidden" value="<?php echo h(stripslashes($v))?>" /><?php
						echo "\n";
					}
				}
			}
		}
		?><br />
		</div>
		�<h2><?php echo $adminCompany?> - Settings for My Items</h2>
	</div>
	<div class="cb" style="font-size:2px;">&nbsp;</div>

<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->
<div class="objectWrapper">
<style type="text/css">
.lyr{
	height:170px;
	border:1px dotted #333;
	padding:8px;
	}
.addtlFields{
	background-color:oldlace;
	margin-bottom:25px;
	border:1px solid #CCC;
	padding:15px 8px 10px 15px;
	}
</style>
<script language="javascript" type="text/javascript">
function toggleJoin(o,idx){
	g('lyr_'+idx+'_18').style.display=( o.value=='oneToMany' ? 'block' : 'none' );
	g('lyr_'+idx+'_88').style.display=( o.value=='oneToMany' ? 'none' : 'block' );
}
</script>
<div style="overflow:scroll;height:410px;background-color:white;padding:5px 15px;">
<?php
for($i=1;$i<=count($joins)+1;$i++){
	unset($AllowAddNew, $AllowMultiple);
	@extract($joins[$i-1]);
	//clean last
	if($i==count($joins)+1 && count($joins)>0){
		foreach($joins[$i-2] as $o=>$w)unset($$o);
	}
	?>
	<div id="addtlFields<?php echo $i?>" class="addtlFields">
	<?php if($i<count($joins)+1){ ?>
	<div class="fr">
		<label><input name="joins[DeleteFieldSetting][]" type="checkbox" id="DeleteFieldSetting" value="1" onChange="dChge(this);" /> Delete this field setting</label>
	</div>
	<?php } ?>
	Field Label: 
	<input name="joins[FieldLabel][]" type="text" id="FieldLabel" value="<?php echo h($FieldLabel)?>" size="25" maxlength="45" onChange="dChge(this);" />
	<br />
	Field Description: <br />
	<textarea name="joins[FieldDescription][]" cols="45" rows="2" id="FieldDescription" onChange="dChge(this);"><?php echo h($FieldDescription)?></textarea>
	<br />
	<br />
	Place field on tab: 
	<select name="joins[PlaceOnTab][]" id="PlaceOnTab" onChange="dChge(this);">
		<option value="" style="font-style:italic;">&lt; Select.. &gt;</option>
		<?php
		foreach($tabList['items'][0] as $n=>$v){
			?><option value="<?php echo $v?>" <?php echo $PlaceOnTab==$v?'selected':''?>><?php echo $v?></option><?php
		}
		?>
	</select>
	&nbsp;&nbsp;&nbsp;
	<label><input name="joins[WhereOnTab][<?php echo $i-1?>]" type="radio" value="bottom" <?php echo $WhereOnTab=='bottom' || !$WhereOnTab?'checked':''?> onChange="dChge(this);" /> At bottom</label>&nbsp;&nbsp;
	<label><input name="joins[WhereOnTab][<?php echo $i-1?>]" type="radio" value="top" <?php echo $WhereOnTab=='top'?'checked':''?> onChange="dChge(this);" /> At top </label>
	<br />
	Insert prompt label: 
	<input name="joins[InsertLabel][]" type="text" id="InsertLabel[]" value="<?php echo h($InsertLabel)?>" onChange="dChge(this);" />
	<br />
	Join Type: 
	<select name="joins[JoinType][]" id="JoinType" onChange="toggleJoin(this,<?php echo $i?>);dChge(this);">
		<option value="oneToMany" <?php echo $JoinType=='oneToMany'?'selected':''?>>One to many</option>
		<option value="manyToMany" <?php echo $JoinType=='manyToMany'?'selected':''?>>Many to many</option>
	</select>
	<div id="lyr_<?php echo $i?>_18" class="lyr" style="display:<?php echo $JoinType=='oneToMany' || !$JoinType?'block':'none';?>">
		Foreign Key Field:
		<select name="joins[ForeignKeyField][]" id="ForeignKeyField" onChange="dChge(this);">
		<option value="" style="font-style:italic;">&lt; Select.. &gt;</option>
		<?php
		foreach($fieldList as $n=>$v){
			?><option value="<?php echo $v['Field']?>" <?php echo $ForeignKeyField==$v['Field']?'selected':''?>><?php echo $v['Field']?></option><?php
		}
		?>
		</select>	
		<br />
		Maps to field: 
		<input name="joins[MapsToField][]" type="text" id="MapsToField" value="<?php echo h($MapsToField)?>" size="12" onChange="dChge(this);" />
		<br />
		In table: 
		<input name="joins[InTable][]" type="text" id="InTable" value="<?php echo h($InTable)?>" onChange="dChge(this);" />
		[case sensitive]<br />
		Replaces field: <input name="joins[ReplacesField][]" type="text" id="MapsToField" value="<?php echo h($ReplacesField)?>" size="12" onChange="dChge(this);" /> 
		<span style="color:#AAA;">(optional)</span><br />
		Label field: 
		<input name="joins[LabelField][]" type="text" id="LabelField" onChange="dChge(this);" value="<?php echo h($LabelField)?>" />
		<br />
	<label>
		<input name="joins[AllowAddNew][]" type="checkbox" id="AllowAddNew" value="1" <?php echo $AllowAddNew?'checked':''?> onChange="dChge(this);" />
	Allow add-new</label><br />
	through..<br />
	<label><input name="joins[AddThrough][<?php echo $i-1?>]" type="radio" value="simple" <?php echo $AddThrough=='simple' || !$AddThrough?'checked':''?> onChange="dChge(this);" /> 
	Simple name fill</label>
	&nbsp;&nbsp;&nbsp;
	<label><input name="joins[AddThrough][<?php echo $i-1?>]" type="radio" value="generic" <?php echo $AddThrough=='generic'?'checked':''?> onChange="dChge(this);" /> 
	Generic table update</label>
	&nbsp;&nbsp;&nbsp;
	<label><input name="joins[AddThrough][<?php echo $i-1?>]" type="radio" value="link" <?php echo $AddThrough=='link'?'checked':''?> onChange="dChge(this);" /> 
	Link to page: </label>
	<input name="joins[LinkToPage][]" type="text" id="LinkToPage" value="<?php echo h($LinkToPage)?>" onChange="dChge(this);" />
	<br />
	<br />
	</div>
	<div id="lyr_<?php echo $i?>_88" class="lyr" style="display:<?php echo $JoinType=='manyToMany'?'block':'none';?>">
	Values table name: 
		<input name="joins[ValueTableName][]" type="text" id="ValueTableName" value="<?php echo h($ValueTableName)?>" onChange="dChge(this);" />
		<br />
		Values table primary key: 
		<input name="joins[ValueTablePK][]" type="text" id="ValueTablePK" value="<?php echo h($ValueTablePK)?>" size="7" onChange="dChge(this);" />
		<br />
	Values table label field: 
	<input name="joins[ValueTableLabel][]" type="text" id="ValueTableLabel" value="<?php echo h($ValueTableLabel)?>" onChange="dChge(this);" />
	<br />
	Join table name: 
	<input name="joins[JoinTable][]" type="text" id="JoinTable" value="<?php echo h($JoinTable)?>" onChange="dChge(this);" />
	<br />
	Join table foreign key for <?php echo $object?>: 
	<input name="joins[JoinTableFKLocal][]" type="text" id="JoinTableFKLocal" value="<?php echo h($JoinTableFKLocal)?>" onChange="dChge(this);" />
	<br />
	Join table foreign key for values table: 
	<input name="joins[JoinTableFKRemote][]" type="text" id="JoinTableFKRemote" value="<?php echo h($JoinTableFKRemote)?>" onChange="dChge(this);" />
	<br />
	<label>
	<input name="joins[AllowMultiple][]" type="checkbox" id="AllowMultiple" value="1" <?php echo $AllowMultiple?'checked':''?> onChange="dChge(this);" />
	Allow multiple</label>
	&nbsp;&nbsp;&nbsp;
	Height of list:
	<select name="joins[ListHeight][]" id="ListHeight" onChange="dChge(this);">
	<option value="">&lt; Select.. &gt;</option>
	<?php for($j=1;$j<=15;$j++){ ?><option value="<?php echo $j?>" <?php echo $j==$ListHeight?'selected':''?>><?php echo $j?></option><?php } ?>
	</select>
	<br />
	<label>
	<input name="joins[AllowBlankOnUpdates][]" type="checkbox" id="AllowBlankOnUpdates[]" value="1" <?php echo $AllowBlankOnUpdates?'checked':''?> onChange="dChge(this);" />
	Allow blank selection on update with label: 
	</label>
	<input name="joins[BlankUpdateLabel][]" type="text" id="BlankUpdateLabel[]" value="<?php echo h($BlankUpdateLabel)?>" onChange="dChge(this);" />
	</div>
	</div>
	<?
}
?>
</div>
The above field configurmations become candidates for columns in the list mode, and will affect the SQL query that is used to gather data.  NOTE, in some cases these changes can slow down the retrieval of large tables of data.  If this is noted contact an administrator and request a check of indexing to increase speed.<br />
</div>
<!-- InstanceEndEditable --></div>
<div id="footer"><!-- InstanceBeginEditable name="footer" -->
&nbsp;

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