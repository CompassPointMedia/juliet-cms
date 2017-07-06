<?php 
//identify this script/GUI
$localSys['scriptGroup']='';
$localSys['scriptID']='view-manufacters';
$localSys['scriptVersion']='1.0';
$localSys['pageType']='Properties Window';



//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');

//------------------------ Navbuttons head coding v1.41 -----------------------------
//change these first vars and the queries for each instance
$object='Manufacturers_ID';
$recordPKField='ID'; //primary key field
$navObject='Manufacturers_ID';
$updateMode='updateManufacturer';
$insertMode='insertManufacturer';
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
#$ids=q("SELECT ID FROM finan_clients WHERE ( OnBillingSystem=1 AND Active=1 AND ResourceType IS NOT NULL ) OR ID='$$object' ORDER BY ClientName", O_COL);

$ids=q("SELECT * FROM finan_manufacturers ORDER BY Name, EditDate", O_COL);
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
	if($a=q("SELECT * FROM finan_manufacturers WHERE ID=".$$object."
		GROUP BY ID", O_ROW)){
		$mode=$updateMode;
		$ID=$$object;
		extract($a);
	}else{
		//object may have been deleted by another user, least perplexing approach is to present insert mode
		$mode=$insertMode;
		$nullAbs=$nullCount+1;
	}


}else{
	$mode=$insertMode;
	$nullAbs=$nullCount+1; //where we actually are right then
}






//--------------------------- end coding --------------------------------
$hideCtrlSection=false;

//2009-02-04: this dataobject including list and focus view
$dataobject='manufacturers';


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/properties_04_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Manage Manufacturers <?php echo $Name ? ' : ' . $Name :  ''?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" type="text/css" href="rbrfm_admin.css" />
<link rel="stylesheet" type="text/css" href="/Library/css/DHTML/dynamic_04_i1.css" />
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
var UserName='<?php echo $UserName?>';
</script>

<?php if(true || $tabbedMenus){
	?>
	<link rel="stylesheet" href="/Library/css/DHTML/layer_engine_v301.css" type="text/css" />
	<?php
	$cg[1]['CGPrefix']="manfocus";
	$cg[1]['CGLayers']=array('description','manufacturercontact','help');
	
	
	
	$cg[1]['defaultLayer']='description';
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
		<input id="Previous" type="button" name="Submit" value="Previous" onClick="focus_nav(-1, '<?php echo ($mode==$insertMode?'insert':'update')?>', <?php echo $mode==$insertMode?1:0?>, 0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $nullAbs<=1?'disabled':''?>>
		<?php
		//Handle display of all buttons besides the Previous button
		if($mode==$insertMode){
			$btn='Save &amp; New';
			
			
			
			
			
			
			if($insertType==2 /** advanced mode **/){
				//save
				?><input id="Save" type="button" name="Save" value="Save" onClick="focus_nav(0,'insert',1,2<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveInitiallyDisabled?> /><?php
			}
			//save and new - common to both modes
			?><input id="SaveAndNew" type="button" name="SaveAndNew" value="<?php echo $btn?>" onClick="focus_nav(0,'insert', 1,1<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveAndNewInitiallyDisabled?> /><?php
			if($insertType==1 /** basic mode **/ && !(
				$mode==$insertMode && $IsPackage
			)){
				//save and close
				?><input id="SaveAndClose" type="button" name="SaveAndClose" value="Save &amp; Close" onClick="focus_nav(0,'insert', 1,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveAndCloseInitiallyDisabled?> /><?php
			}
			?><input id="CancelInsert" type="button" name="CancelInsert" value="Cancel" onClick="focus_nav_cxl('insert');" /><?php
		}else{
			//OK, and appropriate [next] button
			?><input id="OK" type="button" name="ActionOK" value="OK" onClick="focus_nav(0,'<?php echo $mode==$insertMode?'insert':'update'?>',0,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" />&nbsp;
			<input id="Save" type="button" name="ActionOK" value="Save" onClick="focus_nav(0,'<?php echo $mode==$insertMode?'insert':'update'?>',0,2<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" />
			<input id="Next" type="button" name="Next" value="Next" onClick="focus_nav(1,'<?php echo $mode==$insertMode?'insert':'update'?>',0,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $nullAbs>$nullCount || ($denyNextToNew && $nullAbs==$nullCount) ?'disabled':''?> /><?php
		}
		// *note that we could go back to the same page the 'New Record' click appeared on, but there's major issues programmatically on whether it would shift becuase of the placement of the new record.
		// *note that the primary key field is now included here to save time
		?>
		<input name="saveAsNew" type="hidden" id="saveAsNew"  />
		<input name="<?php echo $recordPKField?>" type="hidden" id="<?php echo $recordPKField?>" value="<?php echo $ID;?>" />
		<input name="navVer" type="hidden" id="navVer" value="<?php echo $navVer?>" />
		<input name="navObject" type="hidden" id="navObject" value="<?php echo $navObject?>" />
		<input name="nav" type="hidden" id="nav" />
		<input name="navMode" type="hidden" id="navMode" value="" />
		<input name="count" type="hidden" id="count" value="<?php echo $nullCount?>" />
		<input name="abs" type="hidden" id="abs" value="<?php echo $nullAbs?>" />
		<input name="insertMode" type="hidden" id="insertMode" value="<?php echo $insertMode?>" />
		<input name="updateMode" type="hidden" id="updateMode" value="<?php echo $updateMode?>" />
		<input name="mode" type="hidden" id="mode" value="<?php echo $mode?>" />
		<input name="componentID" type="hidden" id="componentID" value="<?php echo $localSys['componentID']?>" />
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
		�<h2 style="color:white"><?php
		echo ($mode==$insertMode ? 'Create a new ' : 'Edit ') . 'Manufacturer';
		?></h2>
	</div>
	<div class="cb" style="font-size:2px;">&nbsp;</div>

<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->















	<div id="xToolbar" style="height:95px;background-color:#EFEFDE;">&nbsp;</div>
	<div class="objectWrapper" style="clear:both;">


		<table cellpadding="2" cellspacing="2">	
			<tr>
			<td style="vertical-align:bottom">Name: </td>
			<td style="vertical-align:bottom"><input class="sig" name="Name" type="text" id="Name" value="<?php echo h($Name)?>" size="55" maxlength="75" onChange="dChge(this);" /></td>
		</tr>
		</table>
		<div class="tabs" style="margin-top:20px;">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td style="vertical-align:bottom;"><div id="manfocus_a_description" class="ab tShow">Description</div>
						<div id="manfocus_i_description" class="ib tHide" onClick="hl_1('manfocus',manfocus,'description');">Description</div></td>
					<td style="vertical-align:bottom;"><div id="manfocus_a_manufacturercontact" class="ab tHide">Manufacturer</div>
						<div id="manfocus_i_manufacturercontact" class="ib tShow" onClick="hl_1('manfocus',manfocus,'manufacturercontact');">Manufacturer</div></td>
					<td style="vertical-align:bottom;"><div id="manfocus_a_help" class="ab tHide">Help</div>
						<div id="manfocus_i_help" class="ib tShow" onClick="hl_1('manfocus',manfocus,'help');">Help</div></td>
				</tr>
			</table><input name="manfocus_status" id="manfocus_status" type="hidden" value="" />
		</div>
		<div id="manfocus_description" class="aArea tShow" style="width:508px;min-height:456px;">
			<table>
				<tr>
					<td>Site URL:</td>
					<td><input name="URL" type="text" id="URL" value="<?php echo h($URL) ?>" size="60" onChange="dChge(this);" />
					
				</tr>
				<tr>
					<td colspan="2"><div class="fr">[<a title="view images to add to text" href="/admin/file_explorer/?uid=mfrpicturelibrary&amp;folder=logos&amp;view=fullfolder" onClick="return ow(this.href,'l1_imglib','900,700');">Access Picture Library</a>]</div>
						Manufacturer's Description:</td>
					</tr>
				<tr>
					<td colspan="2">
						<label><input name="ddc" type="checkbox" id="ddc" value="1" onClick="if(this.checked)detectChange=1;" /> I am editing the description</label>
						<script type="text/javascript">
					var sBasePath= '/Library/fck6/';
					var oFCKeditor = new FCKeditor('Description') ;
					oFCKeditor.BasePath	= sBasePath ;
					oFCKeditor.ToolbarSet = 'xTransitional' ;
					oFCKeditor.Height = 275 ;
					oFCKeditor.Config[ 'ToolbarLocation' ] = 'Out:xToolbar' ;
					oFCKeditor.Value = '<?php
					//output section text
					$a=@explode("\n",$Description);
					foreach($a as $n=>$v){
						$a[$n]=trim(str_replace("'","\'",$v));
					}
					echo implode('\n',$a);
					?>';
					oFCKeditor.Create() ;
					</script></td> 
				</tr>
			</table>
			<br />
		</div>
		<div id="manfocus_manufacturercontact" class="aArea tHide" style="width:508px;min-height:456px;">
			<table>
				<tr>
					<td colspan="2" style="font-size:120%; font-weight:bold;">Manufacturer's Contact Information</td>
				</tr>
				<tr>
					<td>First Name:</td>
					<td><input name="FirstName" type="text" id="FirstName" value="<?php echo h($FirstName) ?>" size="50" onChange="dChge(this);" /></td> 
				</tr>
				<tr>
					<td>Last Name:</td>
					<td><input name="LastName" type="text" id="LastName" value="<?php echo h($LastName) ?>" size="50" onChange="dChge(this);" /></td>
				</tr>
				<tr>
					<td>Phone #:</td>
					<td><input name="Phone" type="text" id="Phone" value="<?php echo h($Phone) ?>" size="30" onChange="dChge(this);" /></td>
				</tr>
				<tr>
					<td>E-mail:</td>
					<td><input name="Email" type="text" id="Email" value="<?php echo h($Email) ?>" size="50" onChange="dChge(this);" /></td>
				</tr>
				<tr>
				
					<td>Address:</td>
					<td><input name="Address" type="text" id="Address" value="<?php echo h($Address) ?>" size="50" onChange="dChge(this);" /></td>
				</tr>
				<tr>
					<td>City:</td>
					<td><input name="City" type="text" id="City" value="<?php echo h($City) ?>" size="50" onChange="dChge(this);" />
				</tr>
				<tr>
					<td>State:</td>
					<td><input name="State" type="text" id="State" value="<?php echo h($State) ?>" size="5" onChange="dChge(this);" /></td>
				</tr>
				<tr>	
					<td>ZIP:</td>
					<td><input name="ZIP" type="text" id="ZIP" value="<?php echo h($ZIP) ?>" size="5" onChange="dChge(this);" /></td>
				</tr>
			</table>
		</div>
		<div id="manfocus_help" class="aArea tHide" style="width:508px;min-height:456px;">
			<div style="float:right"><a title="Edit the contents of this page help section" onClick="return ow(this.href,'l1_helpeditor','700,700');" href="help_editor.php?node=<?php echo $cg[1]['CGPrefix']?>&amp;cbFunction=le_helpmodule&amp;cbParam[]=fixed:<?php echo $cg[1]['CGPrefix']?>">Edit Help</a></div>
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
page_end();
?>