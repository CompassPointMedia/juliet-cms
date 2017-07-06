<?php 
//identify this script/GUI
$localSys['scriptGroup']='';
$localSys['scriptID']='generic';
$localSys['scriptVersion']='1.0';
$localSys['pageType']='Properties Window';

require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php'); 
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');
$qx['defCnxMethod']=C_MASTER;
$qx['useRemediation']=true;

 

//------------ Customize the query and layout using module Config --------
if($moduleConfig){
	if(count($moduleConfig['dataobjects']['finan_items']['joins']))
	foreach($moduleConfig['dataobjects']['finan_items']['joins'] as $n=>$v){
		if($v['ReplacesField']){
			$fieldReplacements[strtolower($v['ReplacesField'])]=$n;
		}else if($v['PlaceOnTab']){
			$tabExtraFields[strtolower($v['PlaceOnTab'])][$v['FieldLabel']]=$n;
		}
	}
}

//------------------------ Navbuttons head coding v1.43 -----------------------------
//change these first vars and the queries for each instance
$object='Items_ID';
$recordPKField='ID'; //primary key field
$navObject='Items_ID';
$updateMode='updateItem';
$insertMode='insertItem';
$deleteMode='deleteItem';
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
$denyNextToNew=false;
//declare the query to get the idSet or subset, ordered by desired sort order - note that if you're using quasi resources, then be sure and filter them out.
$ids=q("SELECT * FROM finan_items ORDER BY Type, Name", O_COL);

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
	if($a=q("SELECT
		a.*,
		b.OverallDescription,
		b.OverallLongDescription,
		b.PricingType,
		b.PriceValue,
		b.AutoUpdatePrice,
		b.ShowItemPicture,
		IF(b.Items_ID IS NULL, 0,IF(SUM(d.ChildItems_ID)>0, 2, 1)) AS IsPackage,
		/* IF(COUNT(DISTINCT c.Items_ID), 0,0) AS IsDeletable, */
		COUNT(c.Items_ID) AS UseCount		
		FROM finan_items a LEFT JOIN finan_items_packages b ON a.ID=b.Items_ID
		LEFT JOIN finan_transactions c ON a.ID=c.Items_ID
		LEFT JOIN finan_ItemsItems d ON a.ID=d.ParentItems_ID
		WHERE a.ID=".$$object."
		GROUP BY a.ID", O_ROW)){
		$mode=$updateMode;
		unset($a['Items_ID']);
		if(strlen($_REQUEST['IsPackage']))unset($a['IsPackage']);
		@extract($a);
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

//2009-02-04: this dataobject including list and focus view
$dataobject='items';

mysql_declare_field_attributes_rtcs($MASTER_DATABASE,'finan_items');


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/properties_04_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $titleBase='Ecommerce Control Panel - '.($IsPackage ? 'PACKAGE MANAGER' : 'Item Manager'); echo ($SKU ? ' - '.$SKU:'')?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" type="text/css" href="/Library/css/undohtml3.css" />
<link rel="stylesheet" href="/console/rbrfm_admin.css" type="text/css" />
<link rel="stylesheet" href="../Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<link href="../Library/ckeditor_3.4/_samples/sample.css" rel="stylesheet" type="text/css" />
<style type="text/css">
body{
	background-color:#CCC;
	}
.objectWrapper{
	padding:0px 20px;
	}
<?php if($ParentItems_ID){ ?>
#Previous, #Save, #Next{
	display:none;
	}
<?php }?>
</style>

<script language="JavaScript" type="text/javascript" src="../Library/js/global_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="../Library/js/common_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="../Library/js/forms_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="../Library/js/loader_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="../Library/js/contextmenus_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="../Library/js/dataobjects_04_i1.js"></script>
<script type="text/javascript" src="../Library/ckeditor_3.4/ckeditor.js"></script>
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
var UserName='<?php echo $UserName?>';


function handleGroupLeader(o){
	var updateMode=<?php echo $mode==$updateMode?'true':'false'?>;
	if(o.checked && updateMode && g('originalGroupLeader').value!=='1' && !confirm('This will set this as the group leader product for this model and unset any others; make sure you have a complete "Long Description" for this part to make it the group leader.  Continue?')){
		g('GroupLeader').checked=false;
		return false;
	}else if(!o.checked && g('originalGroupLeader').value=='1' && updateMode) alert('This will remove this product as the group leader.  Make sure to set another group leader or it will be done automatically on the site (and the product may not be the one you wish)');
}
AddOnkeypressCommand("PropKeyPress(e)");
//var customDeleteHandler='deleteItem()';
function deleteItem(){
}
</script>

<?php if(true || $tabbedMenus){
	?>
	<link rel="stylesheet" href="../Library/css/DHTML/layer_engine_v301.css" type="text/css" />
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
<form id="form1" name="form1" target="w2" method="post" action="../console/resources/bais_01_exe.php" onsubmit="return beginSubmit();" enctype="multipart/form-data">
<div id="header"><!-- InstanceBeginEditable name="top_nav" -->
	<div id="headerBar1" style="padding:5px 10px 10px 12px;">
		<div id="btns140" class="fr"><?php ob_start();?>
		<input id="Previous" type="button" name="Submit" value="Previous" class="navButton_A" onclick="focus_nav(-1, '<?php echo ($mode==$insertMode?'insert':'update')?>', <?php echo $mode==$insertMode?1:0?>, 0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $nullAbs<=1?'disabled':''?> >
		<?php
		//Handle display of all buttons besides the Previous button
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
		<input name="<?php echo $recordPKField?>" type="hidden" id="<?php echo $recordPKField?>" value="<?php echo $$object;?>">
		<input name="navVer" type="hidden" id="navVer" value="<?php echo $navVer?>">
		<input name="navObject" type="hidden" id="navObject" value="<?php echo $navObject?>">
		<input name="nav" type="hidden" id="nav">
		<input name="navMode" type="hidden" id="navMode" value="">
		<input name="count" type="hidden" id="count" value="<?php echo $nullCount?>">
		<input name="abs" type="hidden" id="abs" value="<?php echo $nullAbs?>">
		<input name="insertMode" type="hidden" id="insertMode" value="<?php echo $insertMode?>">
		<input name="updateMode" type="hidden" id="updateMode" value="<?php echo $updateMode?>">
		<input name="deleteMode" type="hidden" id="deleteMode" value="<?php echo $deleteMode?>">
		<input name="mode" type="hidden" id="mode" value="<?php echo $mode?>">
		<input name="componentID" type="hidden" id="componentID" value="<?php echo $localSys['componentID']?>">
		<input name="IsPackage" type="hidden" id="IsPackage" value="<?php echo $IsPackage?>" />
		<input name="OriginalCategory" type="hidden" id="OriginalCategory" value="<?php echo h($Category)?>" />
		<input name="OriginalSubCategory" type="hidden" id="OriginalSubCategory" value="<?php echo h($SubCategory)?>" />
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
		<h2 style="color:white"><?php
		echo ($mode==$insertMode ? 'Create a new ' : 'Edit ') . ($IsPackage?'Package':'Item') . ' <span id="SKUText">'.$SKU .'</span>';
		?></h2>
		<?php if($ParentItems_ID)require('./components/comp03_itemrlx.php')?>
	</div>
	<div class="cb">&nbsp;</div>

<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->
	<div class="objectWrapper" style="clear:both;">
	<div class="fr">
		<label>
		<input name="Inactive" type="checkbox" id="Inactive" value="1" <?php echo isset($Active) && $Active=='0'?'checked':''?> onchange="dChge(this);" /> Inactive Item</label><br />
		<label>
		<input name="GroupLeader" type="checkbox" id="GroupLeader" value="1" <?php if($mode==$insertMode || $GroupLeader)echo 'checked';?> onclick="handleGroupLeader(this)" onchange="dChge(this);" />
		Group Leader Product</label>
		<input name="originalGroupLeader" type="hidden" id="originalGroupLeader" value="<?php echo $GroupLeader?>" />
	</div>
	<table cellpadding="2" cellspacing="2">	
	<tr>
		<td style="vertical-align:bottom">Part Nbr.: </td> 
		<td style="vertical-align:bottom"><input name="SKU" type="text" class="sig" id="SKU" onchange="dChge(this);" onkeyup="g('SKUText').innerHTML=this.value;document.title='<?php echo $titleBase?> - '+this.value;" value="<?php echo h($SKU)?>" size="18" />
		<?php
		/* 
		2010-12-04:
		did have view in site and view in subcategory here - but not ready to support this without global recognized variables
		
		SEE _bk339 file for the link coding that was here..
		 */
		?>
		</td>
	</tr>
	<tr>
		<td style="vertical-align:bottom">Name: </td>
		<td style="vertical-align:bottom"><input class="sig" name="Name" type="text" id="Name" value="<?php echo h($Name)?>" size="45" maxlength="75" onchange="dChge(this);" /></td>
	</tr>
		</table>
		<div class="tabs" style="margin-top:20px;">
			<table cellpadding="0" cellspacing="0">
				<tr><?php
					ob_start();
					if($IsPackage){
						?><td style="vertical-align:bottom;"><div id="itemfocus_a_parts" class="ab tHide">Package Makeup</div>
						<div id="itemfocus_i_parts" class="ib tShow" onclick="hl_1('itemfocus',itemfocus,'parts');">Package Makeup</div></td><?php
					}
					?>
					<td style="vertical-align:bottom;"><div id="itemfocus_a_description" class="ab tShow">Description</div>
						<div id="itemfocus_i_description" class="ib tHide" onclick="hl_1('itemfocus',itemfocus,'description');">Description</div></td>
					<td style="vertical-align:bottom;"><div id="itemfocus_a_pricing" class="ab tHide">Pricing</div>
						<div id="itemfocus_i_pricing" class="ib tShow" onclick="hl_1('itemfocus',itemfocus,'pricing');">Pricing</div></td>
					<td style="vertical-align:bottom;"><div id="itemfocus_a_manufacturer" class="ab tHide">Manufacturer</div>
						<div id="itemfocus_i_manufacturer" class="ib tShow" onclick="hl_1('itemfocus',itemfocus,'manufacturer');">Manufacturer</div></td>
					<td style="vertical-align:bottom;"><div id="itemfocus_a_attributes" class="ab tHide">Attributes</div>
						<div id="itemfocus_i_attributes" class="ib tShow" onclick="hl_1('itemfocus',itemfocus,'attributes');">Attributes</div></td>
					<td style="vertical-align:bottom;"><div id="itemfocus_a_media" class="ab tHide">Pictures & Media</div>
						<div id="itemfocus_i_media" class="ib tShow" onclick="hl_1('itemfocus',itemfocus,'media');">Pictures & Media</div></td>
					<td style="vertical-align:bottom;"><div id="itemfocus_a_help" class="ab tHide">Help</div>
						<div id="itemfocus_i_help" class="ib tShow" onclick="hl_1('itemfocus',itemfocus,'help');">Help</div></td><?php
					$out=ob_get_contents();
					ob_end_clean();
					$t=$_COOKIE['tabs'.$cg[1]['CGPrefix']];
					if(in_array($t,$cg[1]['CGLayers'])){
						$showTab=$t;
						$out=str_replace('itemfocus_a_description" class="ab tShow','itemfocus_a_description" class="ab tHide',$out);
						$out=str_replace('itemfocus_i_description" class="ib tHide','itemfocus_i_description" class="ib tShow',$out);
						$out=str_replace('itemfocus_a_'.$t.'" class="ab tHide','itemfocus_a_'.$t.'" class="ab tShow',$out);
						$out=str_replace('itemfocus_i_'.$t.'" class="ib tShow','itemfocus_i_'.$t.'" class="ib tHide',$out);
					}else if($IsPackage){
						$showTab='parts';
						$out=str_replace('itemfocus_a_description" class="ab tShow','itemfocus_a_description" class="ab tHide',$out);
						$out=str_replace('itemfocus_i_description" class="ib tHide','itemfocus_i_description" class="ib tShow',$out);
						$out=str_replace('itemfocus_a_parts" class="ab tHide','itemfocus_a_parts" class="ab tShow',$out);
						$out=str_replace('itemfocus_i_parts" class="ib tShow','itemfocus_i_parts" class="ib tHide',$out);
					}else{
						$showTab='description';
					}
					echo $out;
					?>
				</tr>
			</table><input name="itemfocus_status" id="itemfocus_status" type="hidden" value="" />
		</div>
		<?php if($IsPackage){ ?>
		<div id="itemfocus_parts" class="aArea <?php echo $showTab=='parts'?'tShow':'tHide'?>" style="width:508px;min-height:456px;">
			<?php
			//uses logic if it is a new package
			require('./components/comp01_packageitems_v100.php');
			?>
		</div>
		<?php } //end if IsPackage ?>
		<div id="itemfocus_description" class="aArea <?php echo $showTab=='description'?'tShow':'tHide'?>" style="width:508px;min-height:456px;">
			<table>
				<tr>
					<td>Category:</td>
					<td>
					<?php
					//this hijacks the function to do a SELECT DISTINCT list with add-new capability
					$options=array(
						'a'=>array(
							'AddThroughModification'=>'distinct',
							'ForeignKeyField'=>'Category',
							'AllowAddNew'=>true,
							'AddThrough'=>'simple',
							'InsertLabel'=>'< Select.. >',
							'MapsToField'=>'DISTINCT Category',
							'LabelField'=>'Category',
							'InTable'=>'finan_items',
							'JoinType'=>'oneToMany',
							'AllowBlankOnUpdate'=>'(none)',
							'oneToManyDatasetWhere'=>'Category!=\'\''
						),
						'configNode'=>'Category',
					);
					echo relatebase_dataobjects_settings('Category',$options);
					?>
					&nbsp;&nbsp;<a style="display:none;" title="View map of categories and subcategories to assist in setting category for this item" onclick="return ow(this.href,'l1_categorypopup','900,450');" href="list_categories_popup.php?Category=<?php echo $Category?>&amp;SubCategory=<?php echo $SubCategory?>&amp;cbFunction=updateCategorySubcategory">view category map</a> <br /></td>
				</tr>
				<tr>
					<td>Subcategory:</td>
					<td><?php
					//this hijacks the function to do a SELECT DISTINCT list with add-new capability
					$options=array(
						'a'=>array(
							'AddThroughModification'=>'distinct',
							'ForeignKeyField'=>'SubCategory',
							'AllowAddNew'=>true,
							'AddThrough'=>'simple',
							'InsertLabel'=>'< Select.. >',
							'MapsToField'=>'DISTINCT SubCategory',
							'LabelField'=>'SubCategory',
							'InTable'=>'finan_items',
							'JoinType'=>'oneToMany',
							'AllowBlankOnUpdates'=>'(none)',
							'oneToManyDatasetWhere'=>'SubCategory!=\'\''
						),
						'configNode'=>'SubCategory',
					);
					echo relatebase_dataobjects_settings('Category',$options);
					?></td>
				</tr>

				<?php if($mysql_declare_field_attributes_rtcs[0][$MASTER_DATABASE]['finan_items']['theme']){ ?>
				<tr>
					<td>Theme:</td>
					<td><?php
					//this hijacks the function to do a SELECT DISTINCT list with add-new capability
					$options=array(
						'a'=>array(
							'AddThroughModification'=>'distinct',
							'ForeignKeyField'=>'Theme',
							'AllowAddNew'=>true,
							'AddThrough'=>'simple',
							'InsertLabel'=>'< Select.. >',
							'MapsToField'=>'DISTINCT Theme',
							'LabelField'=>'Theme',
							'InTable'=>'finan_items',
							'JoinType'=>'oneToMany',
							'AllowBlankOnUpdates'=>'(none)',
							'oneToManyDatasetWhere'=>'Theme!=\'\''
						),
						'configNode'=>'Theme',
					);
					echo relatebase_dataobjects_settings('Theme',$options);
					?></td>
				</tr>
				<?php } ?>
				<?php if($mysql_declare_field_attributes_rtcs[0][$MASTER_DATABASE]['finan_items']['function']){ ?>
				<tr>
					<td>Function:</td>
					<td><?php
					//this hijacks the function to do a SELECT DISTINCT list with add-new capability
					$options=array(
						'a'=>array(
							'AddThroughModification'=>'distinct',
							'ForeignKeyField'=>'Function',
							'AllowAddNew'=>true,
							'AddThrough'=>'simple',
							'InsertLabel'=>'< Select.. >',
							'MapsToField'=>'DISTINCT Function',
							'LabelField'=>'Function',
							'InTable'=>'finan_items',
							'JoinType'=>'oneToMany',
							'AllowBlankOnUpdates'=>'(none)',
							'oneToManyDatasetWhere'=>'Function!=\'\''
						),
						'configNode'=>'Function',
					);
					echo relatebase_dataobjects_settings('Function',$options);
					?></td>
				</tr>
				<?php } ?>

				<tr>
					<td>Model:</td>
					<td>
					<?php
					//this hijacks the function to do a SELECT DISTINCT list with add-new capability
					$options=array(
						'a'=>array(
							'AddThroughModification'=>'distinct',
							'ForeignKeyField'=>'Model',
							'AllowAddNew'=>true,
							'AddThrough'=>'simple',
							'InsertLabel'=>'< Select.. >',
							'MapsToField'=>'DISTINCT Model',
							'LabelField'=>'Model',
							'InTable'=>'finan_items',
							'JoinType'=>'oneToMany',
							'AllowBlankOnUpdates'=>'(none)',
							'oneToManyDatasetWhere'=>'Model!=\'\''
						),
						'configNode'=>'Model',
					);
					echo relatebase_dataobjects_settings('Model',$options);
					?></td>
				</tr>
				<tr>
				  <td>UPC Code: </td>
				  <td><input name="UPC" type="text" id="UPC" value="<?php echo $UPC;?>" onchange="dChge(this);" /></td>
			  </tr>
				<tr>
					<td>Caption: </td>
					<td><input name="Caption" type="text" id="Caption" value="<?php echo h($Caption) ? h($Caption) : '(optional)'?>" onfocus="if(this.value=='(optional)'){this.className='noGhost';this.value='';}" class="<?php echo !strlen($Caption)?'ghost':''?>" size="60" onchange="dChge(this);" /></td>
				</tr>
				<tr>
					<td>Description:</td> 
					<td><textarea name="Description" cols="50" rows="3" id="Description" onchange="dChge(this);"><?php echo h($Description)?></textarea></td>
				</tr>
				<tr>
					<td colspan="2"><div class="fr">[<a title="view images to add to text" href="/admin/file_explorer/?uid=itempicturelibrary&amp;folder=products&amp;view=fullfolder" onclick="return ow(this.href,'l1_imglib','900,700');">Access Picture Library</a>]</div>
						Long Description:</td>
					</tr>
				<tr>
					<td colspan="2">
					<textarea cols="80" id="LongDescription" name="LongDescription" rows="10"><?php echo h($LongDescription);?></textarea>
					<script type="text/javascript">
					var editor = CKEDITOR.replace( 'LongDescription' );
					setTimeout('CheckDirty(\'LongDescription\')',1000);
					</script>
					</td> 
				</tr>
			</table>
			<br />
		</div>
		<div id="itemfocus_pricing" class="aArea <?php echo $showTab=='pricing'?'tShow':'tHide'?>" style="width:508px;min-height:456px;">
			<div style="float:right;">
				<?php if(!$focusViewObjects[$dataobject]['hideFields']['Accounts_ID']){ ?>
				Use Account: 
				<select name="Accounts_ID" id="Accounts_ID" onchange="dChge(this)">
					<option value="">&lt; Select.. &gt;</option>
					<?php
					// N = ID, V= Name.  Maps the two fields.
					foreach(q("SELECT ID, Name FROM finan_accounts ORDER BY Name", O_COL_ASSOC) as $n=>$v){
						?><option value="<?php echo $n?>" <?php echo $n==$Accounts_ID?'selected':''?>><?php echo h($v)?></option><?php
					}
					?>
				</select>
				<?php }?>
				<?php if(!$focusViewObjects[$dataobject]['hideFields']['Type']){ ?>
				<br />
				<table cellpadding="2" cellspacing="2">
					<tr>
						<td>Type: </td>
						<td><select name="Type" id="Type" onchange="dChge(this)">
								<option value="">&lt; Select.. &gt;</option>
								<?php
								foreach(q("SELECT DISTINCT Type FROM finan_items ORDER BY Type", O_COL) as $v){
									?><option value="<?php echo $v?>" <?php echo $Type==$v?'selected':''?>><?php echo h($v)?></option><?php
								}
								?>
							</select>
							&nbsp; <br />							
							&nbsp;[<a href="#" onclick="alert('not developed'); return false;">What is type?</a>]</td>
					</tr>
				</table>
				<?php }else{ ?>
				<input type="hidden" name="Type" id="Type" value="<?php echo isset($Type)?h($Type):'Non-inventory part'?>" />
				<?php }?>
			</div>
			<!-- prices -->
			<table>
				<?php if(!$focusViewObjects[$dataobject]['hideFields']['PurchasePrice']){ ?>
				<tr>	
					<td><?php
					if($x=$focusViewLabels[$dataobject]['PurchasePrice']){
						echo $x;
					}else{ echo 'Purchase Price:'; }
					?></td>
					<td>$<input name="PurchasePrice" type="text" id="PurchasePrice" value="<?php echo number_format($PurchasePrice,2)?>" size="10" onchange="dChge(this);" /></td>
				</tr>
				<?php } ?>
				<tr>	
					<td><?php
					if($x=$focusViewLabels[$dataobject]['UnitPrice']){
						echo $x;
					}else{ echo 'Normal Price:'; }
					?></td>
					<td>$<input name="UnitPrice" type="text" id="UnitPrice" value="<?php echo number_format($UnitPrice,2)?>" size="10" onchange="dChge(this);" /></td>
				</tr>
				<?php if(!$focusViewObjects[$dataobject]['hideFields']['UnitPrice2']){ ?>
				<tr>
					<td><?php
					if($x=$focusViewLabels[$dataobject]['UnitPrice2']){
						echo $x;
					}else{ echo 'Sale Price:'; }
					?></td>
					<td>$<input name="UnitPrice2" type="text" id="UnitPrice2" value="<?php echo number_format($UnitPrice2,2)?>" size="10" onchange="dChge(this);" /></td>
				</tr>
				<?php } ?>
				<?php if(!$focusViewObjects[$dataobject]['hideFields']['WholesalePrice']){ ?>
				<tr>		
					<td><?php
					if($x=$focusViewLabels[$dataobject]['WholesalePrice']){
						echo $x;
					}else{ echo 'Wholesale Price:'; }
					?></td>
					<td>$<input name="WholesalePrice" type="text" id="WholesalePrice" value="<?php echo number_format($WholesalePrice,2)?>" size="10" onchange="dChge(this);" /></td>
				</tr>
				<?php } ?>
				<?php if(!$focusViewObjects[$dataobject]['hideFields']['Taxable']){ ?>
				<tr>
					<td><label><input name="Taxable" type="checkbox" id="Taxable" value="1" <?php if($Taxable==1 || !isset($Taxable))echo 'checked';?> />&nbsp;Taxable</label></td>
				</tr>
				<?php } ?>
			</table>
		</div>
		<div id="itemfocus_manufacturer" class="aArea <?php echo $showTab=='manufacturer'?'tShow':'tHide'?>" style="width:508px;min-height:456px;">
			<table>
				<tr>
					<td>Manufacturer:</td>
					<td><?php
					//replace field if called for
					if(isset($fieldReplacements['manufacturer'])){
						echo relatebase_dataobjects_settings($fieldReplacements['manufacturer']);
					}else{
						?><input name="Manufacturer" type="text" id="Manufacturer" value="<?php echo h($Manufacturer)?>" size="45" maxlength="45" /><?php
					}
					?></td>
				</tr>
				<?php if(!$focusViewObjects[$dataobject]['hideFields']['Brand']){ ?>
				<tr>
					<td>Brand:</td>
					<td><input name="Brand" type="text" id="Brand" value="<?php echo h($Brand)?>" size="35" /></td>
				</tr>
				<?php } ?>
				<?php if(!$focusViewObjects[$dataobject]['hideFields']['InStock']){ ?>
				<tr>
					<td># in Stock:</td>
					<td><input name="InStock" type="text" id="InStock" value="<?php echo h($InStock)?>" size="7" /></td>
				</tr>
				<?php } ?>
				<?php if(!$focusViewObjects[$dataobject]['hideFields']['ReorderPt']){ ?>
				<tr>
					<td>Reorder Point:</td>
					<td><input name="ReorderPt" type="text" id="ReorderPt" value="<?php echo h($ReorderPt)?>" size="7" /></td>
				</tr>
				<?php } ?>
			</table>
		</div>
		<div id="itemfocus_attributes" class="aArea <?php echo $showTab=='attributes'?'tShow':'tHide'?>" style="width:508px;min-height:456px;">
			<?php
			if($transSettings['itemCreateMultipleOnInsert']==true){
				if($mode==$insertMode){
					?><label>
					<input name="CreateMultipleItems" type="checkbox" id="CreateMultipleItems" value="1" checked="checked" onchange="dChge(this);" />
					Create Multiple Items from these settings</label><br />
					Sizes: 
					<input name="SizeList" type="text" id="SizeList" size="45" />
				<br />
<br />
					Colors:<br />
					<script language="javascript" type="text/javascript">
					function toggleColorChart(n){
						g('colorlist').style.display=(parseInt(n)==-1 ? 'block' : 'none');
					}
					</script>
					<span id="colorchartwrap"><select name="Colorcharts_ID" id="Colorcharts_ID" onchange="toggleColorChart(this.value);dChge(this);">
					<option value="">Select a color chart..</option>
					<?php 
					if($colorcharts=q("SELECT ID, Name FROM finan_colorcharts ORDER BY Name", O_COL_ASSOC))
					foreach($colorcharts as $n=>$v){
						?><option value="<?php echo $n?>" <?php echo $Colorcharts_ID==$n?'selected':''?>><?php echo h($v)?></option><?php
					}
					?>
					<option value="-1">&lt;New color chart..&gt;</option>
					</select></span>
					<div id="colorlist" style="display:none;">
					Name of color chart: 
					<input name="ColorChartName" type="text" id="ColorChartName" size="35" />
					<br />
					List of colors:<br />
					<textarea name="ColorChartValues" cols="45" rows="3" id="ColorChartValues"></textarea>
					<br />
					(you will fill in RGB values later)
					</div>
					<br />
					<?php
				}
			}
			?>
			<?php
			if($MASTER_DATABASE=='cpm112'){
				?>
				<label><input name="updateAcrossModel" type="checkbox" id="updateAcrossModel" value="1" checked="checked" /> update certain changes for all items in this model</label> (<a href="items_crossupdatefields.php" title="Modify which fields are cross-updated for a model" onclick="return ow(this.href,'l1_misc','400,400');">modify</a>)<br />
				<?php	
			}
			/*
			2008-12-13
			this is a hard-coded and somewhat hidebound layout but first implementation of the relatebase_ function below
			*/
			$tabAfter=$out='';
			if($a=$tabExtraFields['attributes']){
				foreach($a as $ConfigLabel=>$node){
					ob_start();
					echo $ConfigLabel . ':<br />';
					echo relatebase_dataobjects_settings($node);
					echo '<br />';
					$out=ob_get_contents();
					ob_end_clean();
					if($moduleConfig['dataobjects']['finan_items']['joins'][$node]['WhereOnTab']=='top'){
						echo $out;
					}else{
						$tabAfter.=$out;
					}
				}
				if($out)echo '<br />';
			}
			?>
			<table>
				<tr>
					<td>Weight (oz.):</td>
					<td><input type="text" id="Weight" value="<?php echo h($Weight)?>" /></td>
				</tr>
				<tr>
					<td>Width:</td>
					<td><input type="text" id="Width" value="<?php echo h($Width)?>" /></td>
				</tr>
				<tr>
					<td>Depth:</td>
					<td><input type="text" id="Depth" value="<?php echo h($Depth)?>" /></td>
				</tr>
				<tr>
					<td>Length:</td>
					<td><input type="text" id="Length" value="<?php echo h($Length)?>" /></td>
				</tr>
			</table>
			Keywords:<br />
			<textarea name="Keywords" cols="50" rows="5" id="Keywords" onchange="dChge(this);"><?php echo h($Keywords)?></textarea>
			<br />
			Product FootNote:<br />
			<textarea name="ItemFootnote" cols="50" rows="5" id="ItemFootnote" onchange="dChge(this);"><?php echo h($ItemFootnote)?></textarea>
			<?php
			echo ($tabAfter?'<br />':'');
			echo $tabAfter;
			?>
		</div>
		<div id="itemfocus_media" class="aArea <?php echo $showTab=='media'?'tShow':'tHide'?>" style="width:508px;min-height:456px;">
			Pictures and Media
		</div>
		<div id="itemfocus_help" class="aArea tHide" style="width:508px;min-height:456px;">
			<div style="float:right"><a title="Edit the contents of this page help section" onclick="return ow(this.href,'l1_helpeditor','700,700');" href="help_editor.php?node=<?php echo $cg[1]['CGPrefix']?>&amp;cbFunction=le_helpmodule&amp;cbParam[]=fixed:<?php echo $cg[1]['CGPrefix']?>">Edit Help</a></div>
			<div id="pageHelpStatus">Active Help Status</div>
			<div id="pageHelpRegion" class="overflowInset1" style="width:95%;height:300;margin-top:8px;padding:5px 15px;background-color:OLDLACE;border:1px dotted DARKRED;"></div>
		</div>
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
//this function can vary and may flush the document 
function_exists('page_end') ? page_end() : mail($developerEmail,'page end function not declared', 'File: '.__FILE__.', line: '.__LINE__,'From: '.$hdrBugs01);
?>