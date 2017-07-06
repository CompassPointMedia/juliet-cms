<?php 
/*
BAIS Login (for San Marcos Area Arts Council) version 2.0 - html template 
This is improved from the Giocosa Foundation use of BAIS Login, and locations for js and css file locations have been moved closer to those for the Ecommerce Site version 4.0

OVERALL TODO ON THIS APPLICATION:
--------------------------------
have navbar read - develop navbar incl a basic style
have site alias
test file object (formlet)
incorporate into site

jingtao todo on this page:
--------------------------
include css for proper appearance including gradient (yellow) from SMAAC
soft-code detectChange
remove footer
add fckeditor interface
add error checking 
	domain name proper
	text present when that option is selected
	valid address if selected on map
	label wrapper for each checkbox and radio button
	[training] css for the formlet object
add label for the button lable - db field, and coding	

*/

if(strlen($sessionid)) session_id($sessionid);
session_start();
$sessionid ? '' : $sessionid = session_id();


//----------------- Begin Properties window coding 4.0 ---------------
//identify this script/GUI
$localSys['scriptGroup']='';
$localSys['scriptID']='manage_db';
$localSys['scriptVersion']='2.0';
$localSys['componentID']='main';
$localSys['modules']='ALL';
$localSys['accessLevel']='User'; //Superadministrator, Administrator, etc.
$localSys['pageType']='Properties Window';



//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');
$hideCtrlSection=false;
//-------------------------- end coding -------------------------------


//------------------------ Navbuttons head coding v1.41 -----------------------------
//change these first vars and the queries for each instance
$object='Properties_ID';
$recordPKField='ID'; //primary key field
$navObject='Properties_ID';
$updateMode='updateDomain';
$insertMode='updateDomain';
$insertType=1; //1=Save&New and Save&Close; 2 = Save and Save&New
#set these to 'disabled' if desired
$saveInitiallyDisabled='';
$saveAndNewInitiallyDisabled='';
$saveAndCloseInitiallyDisabled='';
//v1.4 change - some information about the coding
$navVer='1.41';
//v1.3 change - declare this function if you need to add parameters to the query string
$navQueryFunction=''; //'nav_query_add()';
//v1.3 change - deny transiting from Next to New mode (shutting off ability to insert)
$denyNextToNew=false;
//declare the query to get the idSet or subset, ordered by desired sort order - note that if you're using quasi resources, then be sure and filter them out.
$ids=q("SELECT a.ID FROM re1_properties_domain a LEFT JOIN re1_properties b ON a.ID=b.ID WHERE 1 ORDER BY b.Priority",O_COL);
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
//note the coding to on ResourceToken - this will allow a submitted page to come up again if the user Refreshes the browser
if(strlen($$object) /* || $Assns_ID=q("SELECT ID FROM sma_assns WHERE ResourceToken!='' AND ResourceToken='$ResourceToken' AND ResourceType IS NOT NULL", O_VALUE)*/){
	//get the record for the object
	if($a=q("SELECT a.*, b.DomainName, b.SiteSchematic, b.MenuStyle FROM re1_properties a LEFT JOIN re1_properties_domain b ON a.ID=b.ID WHERE a.ID=$Properties_ID", O_ROW)){
		$mode=$updateMode;
		extract($a);
		$page=q("SELECT LCASE(PageName), a.* FROM re1_properties_pages a WHERE Properties_ID=$Properties_ID", O_ARRAY_ASSOC);
	}else{
		exit('cannot find property');
		//object may have been deleted by another user, least perplexing approach is to present insert mode
		$mode=$insertMode;
		unset($$object);
		$nullAbs=$nullCount+1;
	}
}else{
	$mode=$insertMode;
	//$Assns_ID=$ID=quasi_resource_generic($MASTER_DATABASE, 'sma_assns', $ResourceToken, $typeField='ResourceType', $sessionKeyField='sessionKey', $resourceTokenField='ResourceToken', $primary='ID', $creatorField='Creator', $createDateField='CreateDate' /*, C_DEFAULT, $options */);

	$nullAbs=$nullCount+1; //where we actually are right then
}
//--------------------------- end coding --------------------------------



if(isset($customDocType)){
	//declare here
}else{ 
	?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><?php
}
?><html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/properties_04_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Domain Name Extension Manager</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" type="text/css" href="/Library/css/DHTML/dynamic_04_i1.css" />
<style type="text/css">
.objectWrapper{
	background-color:#CCC;
	min-height:400px;
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
var isModal=0;
var talks=0; //whether this page broadcasts overall state changes which other pages listen for
var listens=0;

function showFeedURL(){
	var u='http://www.hill-country-land-man.com/mlsfeed1/<?php echo $Handle?>?version=1';
	if(g('color').value)u+='&color='+g('color').value;
	if(g('bgcolor').value)u+='&bgcolor='+g('bgcolor').value;
	if(g('padding').value)u+='&padding='+g('padding').value;
	if(g('showtitle').checked)u+='&showtitle=1';
	if(g('showtext').value)u+='&showtext='+g('showtext').value;
	alert('The URL for the MLS feed page is:\n\n'+u);
	g('MLSURL').value=u;
	g('MLSURL').style.color='#000';
	g('MLSURL').select();
	window.open(u,'mlsfeed');
}
</script>

<?php if(true || $tabbedMenus){
	?>
	<link rel="stylesheet" href="/Library/css/DHTML/layer_engine_v301.css" type="text/css" />
	<?php
	$cg[1]['CGPrefix']='cg';
	$cg[1]['CGLayers']=array('slide','floorplan','listing','map','contact','statistics');
	$cg[1]['defaultLayer']='slide';
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
<!--
.style1 {
	color: DARKRED;
	font-style: italic;
}
-->
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
		<?php
		//Things to do to install this button set:
		#1. install contents of this div tag (btns140)
		#2. the coding above needs to go in the head of the document, change as needed to connect to the specific table(s) or get the resource in a different way
		#3. must declare the following vars in javascript:
		// var thispage='whatever.php';
		// var thisfolder='myfolder';
		// var count='[php:echo $nullCount]';
		// var ab='[php:echo $nullAbs]';
		#4. need js functions focus_nav() and focus_nav_cxl() in place
		?>
		<input id="Previous" type="button" name="Submit" value="Previous" onClick="focus_nav(-1, '<?php echo ($mode==$insertMode?'insert':'update')?>', <?php echo $mode==$insertMode?1:0?>, 0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $nullAbs<=1?'disabled':''?> >
		<?php
		//Handle display of all buttons besides the Previous button
		if($mode==$insertMode && $insertMode!=$updateMode){
			if($insertType==2 /** advanced mode **/){
				//save
				?><input id="Save" type="button" name="Save" value="Save" onClick="focus_nav(0,'insert',1,2<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveInitiallyDisabled?>><?php
			}
			//save and new - common to both modes
			?><input id="SaveAndNew" type="button" name="SaveAndNew" value="Save &amp; New" onClick="focus_nav(0,'insert', 1,1<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveAndNewInitiallyDisabled?>><?php
			if($insertType==1 /** basic mode **/){
				//save and close
				?><input id="SaveAndClose" type="button" name="SaveAndClose" value="Save &amp; Close" onClick="focus_nav(0,'insert', 1,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveAndCloseInitiallyDisabled?>><?php
			}
			?><input id="CancelInsert" type="button" name="CancelInsert" value="Cancel" onClick="focus_nav_cxl('insert');"><?php
		}else{
			//OK, and appropriate [next] button
			?><input id="OK" type="button" name="ActionOK" value="OK" onClick="focus_nav(0,'<?php echo $mode==$insertMode?'insert':'update'?>',0,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);">
			<input id="Next" type="button" name="Next" value="Next" onClick="focus_nav(1,'<?php echo $mode==$insertMode?'insert':'update'?>',0,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $nullAbs>$nullCount || ($denyNextToNew && $nullAbs==$nullCount) ?'disabled':''?>><?php
		}
		// *note that we could go back to the same page the 'New Record' click appeared on, but there's major issues programmatically on whether it would shift becuase of the placement of the new record.
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
		<input name="mode" type="hidden" id="mode" value="<?php echo $mode?>">
		<input name="componentID" type="hidden" id="componentID" value="<?php echo $localSys['componentID']?>">
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
							?><input name="<?php echo $n?>[<?php echo is_numeric($o)? '': $o?>]" id="<?php echo $n?>[<?php echo is_numeric($o)? '': $o?>]" type="hidden" value="<?php echo urlencode(stripslashes($w))?>" /><?php
							echo "\n";
						}
					}else{
						echo "\t\t";
						?><input name="<?php echo $n?>" id="<?php echo $n?>" type="hidden" value="<?php echo urlencode(stripslashes($v))?>" /><?php
						echo "\n";
					}
				}
			}
		}
		?><!-- end navbuttons 1.41 --></div>		
		<h2 class = "h2_1"><?php echo $companyName ?></h2> 
	</div>
<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->
	<div id="xToolbar"></div>
	<p>Property Identifier: <strong><?php echo $Handle?></strong><br />
	<br />
	Domain Name: 
	<input name="DomainName" type="text" id="DomainName" value="<?php echo $DomainName?>" onchange="dChge(this);" />
	[<a href="#" onclick="return ow('http://'+g('DomainName').value+'?testNew=1','viewsite','850,700');">view 1</a>] &nbsp;&nbsp;
	[<a href="#" onclick="return ow('http://'+g('DomainName').value+'?testNew=2','viewsite','850,700');">view 2</a>] &nbsp;&nbsp;</p>
	<h2>Pages to Show </h2>
	<div class="objectWrapper">
		<div class="tabs">
			<table cellpadding="0" cellspacing="0"><tr>
				<td style="vertical-align:bottom;"><div id="cg_a_slide" class="ab tShow">Slide Show</div><div id="cg_i_slide" class="ib tHide" onclick="hl_1('cg',cg,'slide');">Slide Show</div>
				</td>
				<td style="vertical-align:bottom;"><div id="cg_a_floorplan" class="ab tHide">Floor Plan</div><div id="cg_i_floorplan" class="ib tShow" onclick="hl_1('cg',cg,'floorplan');">Floor Plan</div></td>
				<td style="vertical-align:bottom;"><div id="cg_a_listing" class="ab tHide">Listing Information</div><div id="cg_i_listing" class="ib tShow" onclick="hl_1('cg',cg,'listing');">Listing Information</div></td>
				<td style="vertical-align:bottom;"><div id="cg_a_map" class="ab tHide">Map</div><div id="cg_i_map" class="ib tShow" onclick="hl_1('cg',cg,'map');">Map</div></td>
				<td style="vertical-align:bottom;"><div id="cg_a_contact" class="ab tHide">Contact</div><div id="cg_i_contact" class="ib tShow" onclick="hl_1('cg',cg,'contact');">Contact</div></td>
				<td style="vertical-align:bottom;"><div id="cg_a_statistics" class="ab tHide">Tools/Statistics</div>
					<div id="cg_i_statistics" class="ib tShow" onclick="hl_1('cg',cg,'statistics');">Tools/Statistics</div></td>
				</tr>
			</table>
		</div>
		<div id="cg_slide" class="aArea tShow" style="width:585px;min-height:300px;">
		  <div class="tabName">
				<input name="page[slide][Active]" type="checkbox" id="page[slide][Active]" value="1" checked="checked" onclick="if(!this.checked){alert('This option cannot be unchecked (the slideshow must be present)');  this.checked=true; this.blur(); }" />
				Slide Show<br />
				<br />

			  <a title="Upload slide show images for this site" href="../admin/file_explorer/index.php?uid=slideshow&amp;folder=slides/<?php echo $Handle?>" onclick="return ow(this.href,'slideshow','700,700');">Upload pictures</a> </div>
			
		</div>
		<div id="cg_floorplan" class="aArea tHide" style="width:585px;min-height:300px;">
			<div class="tabName">
				<input name="page[floorplan][ID]" type="hidden" id="page[floorplan][ID]" value="<?php echo $page['floorplan']['ID']?>" />
				<input name="page[floorplan][Active]" type="checkbox" id="page[floorplan][Active]" value="1" <?php echo $page['floorplan']['Active'] ? 'checked':''?> onchange="dChge(this);" />
				Show Floor Plan Page
			</div>
			<input name="page[floorplan][PrimaryOption]" type="radio" value="externalurl" <?php echo $page['floorplan']['PrimaryOption']=='externalurl' ? 'checked':''?> onchange="dChge(this);" />
			External URL:
			<input name="page[floorplan][ExternalURL]" type="text" id="page[floorplan][ExternalURL]" value="<?php echo $page['floorplan']['ExternalURL']?>" onchange="dChge(this);" />
			<br />
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input name="page[floorplan][OpenNewWindow]" type="checkbox" id="page[floorplan][OpenNewWindow]" value="1" <?php echo $page['floorplan']['OpenNewWindow'] ? 'checked':''?> onchange="dChge(this);" />
			 Open in new window <br />
			 <input name="page[floorplan][PrimaryOption]" type="radio" value="separatepage" <?php echo $page['floorplan']['PrimaryOption']=='separatepage' || !isset($page['floorplan']['PrimaryOption']) ? 'checked':''?> onchange="dChge(this);" />
			Text (below)<br />

			<script type="text/javascript">
			var sBasePath= '/Library/fck6/';
			var oFCKeditor = new FCKeditor('page[floorplan][PageText]') ;
			oFCKeditor.BasePath	= sBasePath ;
			oFCKeditor.ToolbarSet = 'xTransitional' ;
			oFCKeditor.Height = 150 ;
			oFCKeditor.Config[ 'ToolbarLocation' ] = 'Out:xToolbar' ;
			oFCKeditor.Value = '<?php
			//output section text
			$a=@explode("\n",$page[floorplan][PageText]);
			foreach($a as $n=>$v){
				$a[$n]=trim(str_replace("'","\'",$v));
			}
			echo implode('\n',$a);
			?>';
			oFCKeditor.Create() ;
			</script>


			
			<?php
			//file loader formlet
			$formletFilePrefix='floorplan_';
			$formletConfigure='icons';
			$formletFolderHTTP='../images/sites/'.$Handle.'/files'; //where the object would look for the files
			$formletFolder='images/sites/'.$Handle.'/files';  //where the exe page would look for the folder
			$formletIconFolder='../images/i';
			$formletMode='object';
			require($_SERVER['DOCUMENT_ROOT'].'/components/formlet_v100.php');
			?>
		</div>
		<div id="cg_listing" class="aArea tHide" style="width:585px;min-height:300px;">
			<div class="tabName">
				<input name="page[listing][ID]" type="hidden" id="page[listing][ID]" value="<?php echo $page['listing']['ID']?>" onchange="dChge(this);" />
				<input name="page[listing][Active]" type="checkbox" id="page[listing][Active]" value="1" <?php echo $page['listing']['Active'] ? 'checked':''?> onchange="dChge(this);" />
				Show Listing Information Page
			</div>
			<input name="page[listing][PrimaryOption]" type="radio" value="externalurl" <?php echo $page['listing']['PrimaryOption']=='externalurl' ? 'checked':''?> onchange="dChge(this);" />
			External URL:
			<input name="page[listing][ExternalURL]" type="text" id="page[listing][ExternalURL]" value="<?php echo $page['listing']['ExternalURL']?>" onchange="dChge(this);" />
			<br />
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input name="page[listing][OpenNewWindow]" type="checkbox" id="page[listing][OpenNewWindow]" value="1" <?php echo $page['listing']['OpenNewWindow'] ? 'checked':''?> onchange="dChge(this);" />
			Open in new window <br />
			<input name="page[listing][PrimaryOption]" type="radio" value="separatepage" <?php echo $page['listing']['PrimaryOption']=='separatepage' || !isset($page['listing']['PrimaryOption']) ? 'checked':''?> onchange="dChge(this);" />
			Text (below) <br /> 
			<br />
			<script type="text/javascript">
			var sBasePath= '/Library/fck6/';
			var oFCKeditor = new FCKeditor('page[listing][PageText]') ;
			oFCKeditor.BasePath	= sBasePath ;
			//oFCKeditor.ToolbarSet = 'Medium' ;
			oFCKeditor.Height = 150 ;
			oFCKeditor.Config[ 'ToolbarLocation' ] = 'Out:xToolbar' ;
			oFCKeditor.Value = '<?php
			//output section text
			$a=@explode("\n",$page[listing][PageText]);
			foreach($a as $n=>$v){
				$a[$n]=trim(str_replace("'","\'",$v));
			}
			echo implode('\n',$a);
			?>';
			oFCKeditor.Create() ;
			</script>
			
			<?php
			//file loader formlet
			$formletFilePrefix='listing_';
			$formletFolderHTTP='../images/sites/'.$Handle.'/files'; //where the object would look for the files
			$formletFolder='images/sites/'.$Handle.'/files';  //where the exe page would look for the folder
			#$formletConfigure='icons';
			#$formletMode='object';
			require($_SERVER['DOCUMENT_ROOT'].'/components/formlet_v100.php');
			?>
		</div>
		<div id="cg_map" class="aArea tHide" style="width:585px;min-height:300px;">
			<div class="tabName">
				<input name="page[map][ID]" type="hidden" id="page[map][ID]" value="<?php echo $page['map']['ID']?>" onchange="dChge(this);" />
				<input name="page[map][Active]" type="checkbox" id="page[map][Active]" value="1" <?php echo $page['map']['Active'] ? 'checked':''?> onchange="dChge(this);" />
				Show Map Page
			</div>
			<p>You will need to get a site key from Google to use for each embedded map. <a title="Get a google maps site key" href="http://code.google.com/apis/maps/" onclick="return ow(this.href+g('DomainName').value,'googleapi','700,700');"><strong>Click here to get the site key</strong></a>. Enter key here:<br />
				<textarea name="page[map][Extra2]" cols="33" rows="2" id="page[map][Extra2]" onchange="dChge(this);"><?php echo $page['map']['Extra2']?></textarea> 
				<br />
				Next you will need to get the property's latitude and longitude. Find your property on a google map and then paste this code into the URL bar and hit enter:<br />
		  <div style="border:1px dashed darkred;background-color:navajowhite;padding:5px 15px;font-family:'Courier New', Courier, monospace;">javascript:void(prompt('',gApplication.getMap().getCenter()));</div> 
				<br />
				Then paste the latitude and longitude here: 
				<input name="page[map][Extra3]" type="text" id="page[map][Extra3]" value="<?php echo $page[map][Extra3]?>" size="35" onchange="dChge(this);" />
				<br />
				<br />
				<strong>Address</strong><br /> 	
			<input name="page[map][PrimaryOption]" type="radio" value="listedaddress" <?php echo $page['map']['PrimaryOption']=='listedaddress' || !isset($page['map']['PrimaryOption'])? 'checked':''?> onchange="dChge(this);" />
			Use listed address for google map:<br />
			<input name="page[map][PrimaryOption]" type="radio" value="otheraddress" <?php echo $page['map']['PrimaryOption']=='otheraddress' ? 'checked':''?> onchange="dChge(this);" />
			Different address: 
			<textarea name="page[map][Extra1]" cols="45" id="page[map][Extra1]" onchange="dChge(this);"><?php 
			
			if(isset($page['map']['Extra1'])){
				echo $page['map']['Extra1'];
			}else{
				echo $Address . "\n";
				echo $City. ', '.$State.' '.$Zip;
			}?></textarea>
			<br />
			<input name="page[map][PrimaryOption]" type="radio" value="externalurl" <?php echo $page['map']['PrimaryOption']=='externalurl' ? 'checked':''?> onchange="dChge(this);" />
			External URL: 
			<input name="page[map][ExternalURL]" type="text" id="page[map][ExternalURL]" value="<?php echo $page['map']['ExternalURL']?>" size="45" onchange="dChge(this);" />
			</p>
			<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input name="page[map][OpenNewWindow]" type="checkbox" id="page[map][OpenNewWindow]" value="1" <?php echo $page['map']['OpenNewWindow'] ? 'checked':''?> onchange="dChge(this);" />
			Open in new window <br />
			Text (below)<br />
			<script type="text/javascript">
			var sBasePath= '/Library/fck6/';
			var oFCKeditor = new FCKeditor('page[map][PageText]') ;
			oFCKeditor.BasePath	= sBasePath ;
			//oFCKeditor.ToolbarSet = 'Medium' ;
			oFCKeditor.Height = 150 ;
			oFCKeditor.Config[ 'ToolbarLocation' ] = 'Out:xToolbar' ;
			oFCKeditor.Value = '<?php
			//output section text
			$a=@explode("\n",$page[map][PageText]);
			foreach($a as $n=>$v){
				$a[$n]=trim(str_replace("'","\'",$v));
			}
			echo implode('\n',$a);
			?>';
			oFCKeditor.Create() ;
			</script>
			<br />
			</p>
		</div>
		<div id="cg_contact" class="aArea tHide" style="width:585px;min-height:300px;">
			<div class="tabName">
				<input name="page[contact][ID]" type="hidden" id="page[contact][ID]" value="<?php echo $page['contact']['ID']?>" onchange="dChge(this);" />
				<input name="page[contact][Active]" type="checkbox" id="page[contact][Active]" value="1" <?php echo $page['contact']['Active'] ? 'checked':''?> onchange="dChge(this);" />
				Show Contact Page
		  </div>
			<p>
			<input name="page[contact][PrimaryOption]" type="radio" value="externalurl" <?php echo $page['contact']['PrimaryOption']=='externalurl' ? 'checked':''?> onchange="dChge(this);" />
			External URL: 
			<input name="page[contact][ExternalURL]" type="text" id="page[contact][ExternalURL]" value="<?php echo $page['contact']['ExternalURL']?>" onchange="dChge(this);" /><br /> 
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input name="page[contact][OpenNewWindow]" type="checkbox" id="page[contact][OpenNewWindow]" value="1" <?php echo $page['contact']['OpenNewWindow'] ? 'checked':''?> onchange="dChge(this);" />
			Open in new window <br />
			<input name="page[contact][PrimaryOption]" type="radio" value="lacecontact" <?php echo $page['contact']['PrimaryOption']=='lacecontact' || !isset($page['contact']['PrimaryOption'])? 'checked':''?> onchange="dChge(this);" />
			link to the LACE contact form </p>
			<p>
			<input name="page[contact][PrimaryOption]" type="radio" value="text" <?php echo $page['contact']['PrimaryOption']=='text' ? 'checked':''?> onchange="dChge(this);" />
			Text (below)</p>
			<script type="text/javascript">
			var sBasePath= '/Library/fck6/';
			var oFCKeditor = new FCKeditor('page[contact][PageText]') ;
			oFCKeditor.BasePath	= sBasePath ;
			//oFCKeditor.ToolbarSet = 'Medium' ;
			oFCKeditor.Height = 150 ;
			oFCKeditor.Config[ 'ToolbarLocation' ] = 'Out:xToolbar' ;
			oFCKeditor.Value = '<?php
			//output section text
			$a=@explode("\n",$page[contact][PageText]);
			foreach($a as $n=>$v){
				$a[$n]=trim(str_replace("'","\'",$v));
			}
			echo implode('\n',$a);
			?>';
			oFCKeditor.Create() ;
			</script>
		</div>
		<div id="cg_statistics" class="aArea tHide" style="width:585px;min-height:300px;">
			<div class="tabName"><a title="View Visits/Statistics for this site" onclick="return ow(this.href,'l1_statistics','700,700');" href="stats_v100.php?Handle=<?php echo $Handle?>">View Statistics</a></div>
		
		<fieldset><legend>Appearance</legend>
		Site Schematic:
		<select name="SiteSchematic" id="SiteSchematic" onchange="dChge(this);">
			<option value="LACE Site Piggyback 1.0">LACE Site Piggyback 1.0</option>
		</select>
		<br />
		Menu Style:
		<select name="MenuStyle" id="MenuStyle" onchange="dChge(this);">
			<option value="Basic">Basic - black tabs with gold highlighted</option>
		</select>
		</fieldset>
		<br />
		<br />
		<fieldset><legend>MLS Feed Page</legend>
		<span class="style1">This box allows you to gererate the URL to view the &quot;MLS feed&quot; for this page.  After you preview the feed page's layout, copy it from the field below.</span><br />
		<input name="showtitle" type="checkbox" id="showtitle" value="1" />
		Show Title<br />
		Show Description to 
		<select name="showtext" id="showtext">
			<option value="">(do not show description)</option>
			<option value="right">Right of slideshow</option>
			<option value="left">Left of slideshow</option>
		</select>
		
		<br />
		Padding (pixels): 
		<input name="padding" type="text" id="padding" size="5" />
		(default is 25 pixels) <br />
		Text Color: 
		<input name="color" type="text" id="color" size="17" />
		(default is black) <br />
		Background Color: 
		<input name="bgcolor" type="text" id="bgcolor" size="17" />
		 (default is tan) 
		<br />
		<input type="button" name="Button" value="View MLS Feed/URL:" onclick="showFeedURL();" />
		<input name="MLSURL" type="text" id="MLSURL" value="(copy url from this field)" size="45" style="color:SILVER;" />
		</fieldset>
		
		</div>
	</div>
	<input name="cg_status" id="cg_status" type="hidden" value="" />
<script language="javascript" type="text/javascript">
detectChange=1;
function estChge(){
	var inputs=document.getElementsByTagName('input');
    for(var i=0;i<inputs.length;i++){
		if(inputs[i].name.match('PageText')){
			g(inputs[i].name).onchange=dChge(g(inputs[i].name));
		}
	}
}
estChge();
</script>


<!-- InstanceEndEditable --></div>
<div id="footer"><!-- InstanceBeginEditable name="footer" -->




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