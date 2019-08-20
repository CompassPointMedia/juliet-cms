<?php 
//identify this script/GUI
$localSys['scriptGroup']='';
$localSys['scriptID']='generic';
$localSys['scriptVersion']='1.0';
$localSys['pageType']='Properties Window';

require('systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php'); 
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');

require($COMPONENT_ROOT.'/settings.comp110_articles_v101.php');
$qx['defCnxMethod']=C_MASTER;
$qx['useRemediation']=true;
$qx['tableList']=array('addr_contacts','cms1_articles');


//------------------------ Navbuttons head coding v1.43 -----------------------------
//change these first vars and the queries for each instance
$object='ID';
$recordPKField='ID'; //primary key field
$navObject='ID';
$updateMode='updateFeaturedArticle';
$insertMode='insertFeaturedArticle';
$deleteMode='deleteFeaturedArticle';
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
$ids=q("SELECT ID FROM cms1_articles WHERE Category='Article' ORDER BY Priority",O_COL);

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
//note the coding to on ResourceToken - this will allow a submitted page to come up again if the user Refreshes the browser
if(strlen($$object) /* || $Assns_ID=q("SELECT ID FROM sma_assns WHERE ResourceToken!='' AND ResourceToken='$ResourceToken' AND ResourceType IS NOT NULL", O_VALUE)*/){
	//get the record for the object
	if($a=q("SELECT * FROM cms1_articles WHERE ID='".$$object."'",O_ROW)){
		$mode=$updateMode;
		@extract($a);
	}else{
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


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/properties_04_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Articles and Content</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link id="cssUndoHTML" rel="stylesheet" type="text/css" href="../site-local/undohtml2.css" />
<link id="cssSimple" rel="stylesheet" href="./rbrfm_admin.css" type="text/css" />
<link id="cssDHTML" rel="stylesheet" href="../Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<style type="text/css">
</style>
<script id="jsglobal" language="JavaScript" type="text/javascript" src="../Library/js/global_04_i1.js"></script>
<script id="jscommon" language="JavaScript" type="text/javascript" src="../Library/js/common_04_i1.js"></script>
<script id="jsforms" language="JavaScript" type="text/javascript" src="../Library/js/forms_04_i1.js"></script>
<script id="jsloader" language="JavaScript" type="text/javascript" src="../Library/js/loader_04_i1.js"></script>
<script id="jscontextmenu" language="JavaScript" type="text/javascript" src="../Library/js/contextmenus_04_i1.js"></script>
<script id="jsdataobjects" language="JavaScript" type="text/javascript" src="../Library/js/dataobjects_04_i1.js"></script>
<script id="3rdpartyfckeditor" type="text/javascript" src="../Library/ckeditor/fckeditor.js"></script>
<script id="jslocal" language="JavaScript" type="text/javascript">
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
var isModal=0;
var talks=0; //whether this page broadcasts overall state changes which other pages listen for
var listens=0;

function showFeatured(){
	file=g('null1').value+'.'+g('null3').value;
	node=g('null2').value;
	if(g('FeaturedImage').value!=='images/'+node+'/'+file)mgeChge(g('FeaturedImage'));
	g('Featured').innerHTML='images/'+node+'/'+g('null1').value;
	g('FeaturedImage').value='images/'+node+'/'+file;
	g('deleteFeaturedImage').style.display='block';
}
function showPDF(){
	file=g('null1').value+'.'+g('null3').value;
	node=g('null2').value;
	if(g('PDFLink').value!=='images/'+node+'/'+file)mgeChge(g('PDFLink'));
	g('FeaturedPDF').innerHTML='images/'+node+'/'+g('null1').value;
	g('PDFLink').value='images/'+node+'/'+file;
	g('deleteFeaturedPDF').style.display='block';
}
</script>

<?php

//------- tabs coding --------
$tabPrefix='contentMain';
$cg[$tabPrefix]['CGLayers']=array(
    'Summary Info'	=>'cmSummary',
	'Body'=>'cmBody',
	'Notification'=>'cmNotification',
    'Settings'	=>'cmSettings',	
);
if(!isset($cg[$tabPrefix]['defaultLayer'])){
    $cg[$tabPrefix]['defaultLayer']=current($cg[$tabPrefix]['CGLayers']);
}
$cg[$tabPrefix]['layerScheme']=2; //thin tabs vs old Microsoft tabs
$cg[$tabPrefix]['schemeVersion']=3.01;
$layerMinHeight=200;

?>
<!-- InstanceEndEditable -->
</head>

<body id="properties">
<form id="form1" name="form1" target="w2" method="post" action="../console/resources/bais_01_exe.php" onsubmit="return beginSubmit();" enctype="multipart/form-data">
<div id="header"><!-- InstanceBeginEditable name="top_nav" -->
	<div id="headerBar1">
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
		<h2 class = "h2_1">Manage Articles and Content </h2> 
	</div>
<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->
 
<div class="fr"><?php
if(file_exists('../'.$FeaturedImage)){
	?><img src="<?php echo '../'.$FeaturedImage?>" /><?php
}
?></div>

<input name="Active" type="checkbox" id="Active" value="1" <?php echo !isset($Active) || $Active==1 ? 'checked' : ''?> onchange="mgeChge(this)" />
Active Article<br />
<input name="LeadArticle" type="checkbox" id="LeadArticle" value="1" <?php echo $LeadArticle ? 'checked' : ''?> />
Lead (featured) article<br />
<label>
<input name="Private" type="checkbox" id="Private" value="1" <?php echo $Private==1 ? 'checked' : ''?> onchange="mgeChge(this)" 
onclick="alert('this is not developed; clicking this will not have any effect.  Contact Webmaster');" />
Private Article (must be signed in) &nbsp;&nbsp;
<label><input name="PrivateShowSummaryPublicly" type="checkbox" id="PrivateShowSummaryPublicly" value="1" <?php echo $PrivateShowSummaryPublicly?'checked':''?> onchange="mgeChge(this)" />
Show article summary for private articles when not signed in</label>
<br />
</label>

<?php
//article tabs
require($MASTER_COMPONENT_ROOT.'/comp_tabs_v200.php');
//-------------------------------- first tab --------------------------
ob_start(); 
?>

<?php
if($mergeAvailableContentCategories['scheme']){
	if(!function_exists('array_merge_accurate'))require_once($FUNCTION_ROOT.'/function_array_merge_accurate_v100.php');
	$availableContentCategories=array_merge_accurate($availableContentCategories, $mergeAvailableContentCategories);
}
foreach($availableContentCategories['scheme'] as $n=>$v){
	if(isset($v['active']) && !$v['active'])unset($availableContentCategories['scheme'][$n]);
}
if(!function_exists('subkey_sort'))require_once($FUNCTION_ROOT.'/function_array_subkey_sort_v300.php');
$availableContentCategories['scheme']=subkey_sort($availableContentCategories['scheme'],'idx');
?>
Type of Content: <select name="Category<?php if($availableContentCategories['settings']['allow_multiple'])echo '[]'?>" id="Category<?php if($availableContentCategories['settings']['allow_multiple'])echo '[]'?>" onchange="dChge(this)">
	<option value="">&lt; Select.. &gt;</option>
	<?php
	$haveSelected=false;
	foreach($availableContentCategories['scheme'] as $n=>$v){
		?><option title="<?php echo $v['title']?>" value="<?php echo $value=($v['id'] ? $v['id'] : ($v['value'] ? $v['value'] : $n))?>" <?php
		
		if($availableContentCategories['settings']['allow_multiple']=='relational'){
			mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
			exit('undeveloped');
		}else if($availableContentCategories['settings']['allow_multiple']=='comma-separated'){
			if(preg_match('/\b'.$value.'\b/i',$value)){
				echo $haveSelected='selected';
			}
		}else{
			if(strtolower($value)==strtolower($Category)){
				echo $haveSelected='selected';
			}
		}
		?>><?php echo h($v['label'] ? $v['label'] : $n);?></option><?php
	}
	?>
</select><br />

<?php
if($mode==$insertMode && !$Contacts_ID)$Contacts_ID=$defaultArticleContacts_ID;
?>
Author: &nbsp;	
<select name="Contacts_ID" id="Contacts_ID" cbtable="cms1_articles" onchange="dChge(this);newOption(this, 'members.php', 'l1_members', '700,700');">
<option value="">&lt; Select.. &gt;</option>
<?php
$contacts=q("SELECT a.ID, a.FirstName, a.LastName, c.ClientName, c.CompanyName FROM addr_contacts a LEFT JOIN finan_ClientsContacts b ON a.ID=b.Contacts_ID AND b.Type='Primary' LEFT JOIN finan_clients c ON b.Clients_ID=c.ID ORDER BY LastName", O_ARRAY_ASSOC);
foreach($contacts as $n=>$v){
	?><option value="<?php echo $n?>" <?php	if($n==$Contacts_ID)echo 'selected';?>><?php echo $v['FirstName'] .' '. $v['LastName'].($v['ClientName'] ? ', '.$v['ClientName'] : '');?></option><?php
}
?>
	<option value="{RBADDNEW}">&lt; Add new author.. &gt;</option>
</select>
&nbsp;<br />
..or type author name 
<input name="AuthorName" type="text" id="AuthorName" value="<?php echo $AuthorName?>" size="17" onchange="dChge(this)" />
 and email: 
 <input name="AuthorEmail" type="text" id="AuthorEmail" value="<?php echo $AuthorEmail?>" size="12" onchange="dChge(this)" />
 <br />
Link to author website (optional): 
<input name="AuthorLink" type="text" id="AuthorLink" value="<?php echo $AuthorLink?>" size="55" onchange="dChge(this)" />
<br />
Posting date: 
<input name="PostDate" type="text" id="PostDate" onchange="mgeChge(this)" value="<?php echo date('m/d/Y g:iA',$PostDate ? strtotime($PostDate) : time())?>" size="22" />
<br />
Title:
<input name="Title" type="text" id="Title" onchange="mgeChge(this)" value="<?php echo $Title?>" size="60" />
<br />
&quot;Pretty URL&quot;/Keywords Title: 
<input name="KeywordsTitle" type="text" id="KeywordsTitle" onchange="mgeChge(this)" value="<?php echo h($KeywordsTitle)?>" size="45" />
<em>(a-z and 0-9 only, spaces will be replaced by dashes)</em> <br />
Subtitle:<br />
<textarea name="SubTitle" cols="65" rows="2" id="SubTitle" onchange="mgeChge(this)"><?php echo $SubTitle?></textarea>
<br />
</p>
Description:<br />
<textarea name="Description" cols="65" rows="4" id="Description" onchange="mgeChge(this)"><?php echo $Description?></textarea>

<?php
//-------------------------------- store tab --------------------------
get_contents_layer('cmSummary');
?>
<a href="/admin/file_explorer/?uid=imglibrary" title="View pictures to add to articles" onclick="return ow(this.href,'l1_fex','700,700');">View Picture Library</a><br />

<div id="xToolbar"></div>
<script language="javascript" type="text/javascript">
var sBasePath= '/Library/ckeditor/';
var oFCKeditor = new FCKeditor('Body') ;
oFCKeditor.BasePath	= sBasePath ;
oFCKeditor.ToolbarSet = 'xTransitional' ;
oFCKeditor.Height = 275 ;
oFCKeditor.Config[ 'ToolbarLocation' ] = 'Out:xToolbar' ;
oFCKeditor.Value = '<?php
//output section text
$a=@explode("\n",$Body);
foreach($a as $n=>$v){
	$a[$n]=trim(str_replace("'","\\'",$v));
}
echo implode('\n',$a);
?>';
oFCKeditor.Create() ;
var oEditor=null;
/*
function CMSUpdater(){
	if(!oEditor)oEditor=FCKeditorAPI.GetInstance('Body');
	if(oEditor.IsDirty())detectChange=1;
	setTimeout('CMSUpdater()',350);
}
setTimeout('CMSUpdater()',2000);
*/

var oEditor=null;
function CMSUpdater(){
	if(typeof FCKeditorAPI=='undefined'){
		window.status+='[noAPI]';
		setTimeout('CMSUpdater()',2000);
		return;
	}
	if(!oEditor)oEditor=FCKeditorAPI.GetInstance('Body');
	if(typeof alerted=='undefined'){
		window.status=('Editor API Running');
		alerted=true;
	}
	if(oEditor.IsDirty()){
		detectChange=1;


		if(false) {
			/* this was commented before, PHP Storm showing an error */
			g('CMSBUpdate').disabled = false;
			try {
				comparepage = (window.opener.thispage ? window.opener.thispage.toLowerCase() : '');
				if (cmspage == comparepage && cmsfolder == window.opener.thisfolder && cmsquery == (cmsquerypassed ? window.opener.cmsquery : (window.opener.location + '').toLowerCase())) {
					cmsOriginalPagePresent = true;
					window.opener.g(cmssection).innerHTML = (oEditor.GetHTML(true));
				} else {
					cmsOriginalPagePresent = false;
				}
			} catch (e) {
			} //-----------------
		}


	}
	setTimeout('CMSUpdater()',350);
}

</script>

<?php
//-------------------------------- store tab --------------------------
get_contents_layer('cmBody');
?>

Notification <br />
<label><input name="SendNotification" type="checkbox" id="SendNotification" value="1" onchange="mgeChge(this)" />
Send this out to: </label>
<select name="Queries_ID" id="Queries_ID" onchange="mgeChge(this)">
	<option value="">&lt;Select..&gt;</option>
	<?php
	if($queries=q("SELECT ID, Title FROM relatebase_queries ORDER BY Title", O_COL_ASSOC)){
		foreach($queries as $n=>$v){
			?><option value="<?php echo $n?>" <?php if($n==$Queries_ID)echo 'selected'?>><?php echo h($v);?></option><?php
		}
	}
	?>
</select> 
<!-- or custom query or list:<br /> -->
<br />
Using this template or layout: 
<select name="Templates_ID" id="Templates_ID" onchange="mgeChge(this)">
	<option value="default">default layout</option>
</select> 
<br />

<!--
()using default template<br />
-->
Introductory message:<br />
<textarea name="CustomMessage" cols="40" rows="3" id="CustomMessage" onchange="mgeChge(this)"></textarea>
<!--
 unsubscribe - what will it do<br />
 -->


<?php
//-------------------------------- store tab --------------------------
get_contents_layer('cmNotification');
?><br />

<label><input name="BodySummaryTruncate" type="checkbox" id="BodySummaryTruncate" value="1" <?php echo $mode==$insertMode || $BodySummaryTruncate ? 'checked':''?> onchange="mgeChge(this);" /> 
Cut off summary page content over </label>
<input name="BodySummaryWordCount" type="text" id="BodySummaryWordCount" value="<?php echo $BodySummaryWordCount ? $BodySummaryWordCount : $defaultSummaryWordCount?>" size="3" onchange="mgeChge(this);" /> 
words<br /> 
<br />
Place lead image 
<select name="LeadImageVertical" id="LeadImageVertical" onchange="mgeChge(this)">
  <option>&lt;select..&gt;</option>
  <option <?php echo $mode==$insertMode || $LeadImageVertical=='top'?'selected':''?> value="top">at top</option>
  <option <?php echo $LeadImageVertical=='bottom'?'selected':''?> value="bottom">at bottom</option>
</select>
, aligned to the 
<select name="LeadImageHorizontal" id="LeadImageHorizontal" onchange="mgeChge(this)">
  <option>&lt;select..&gt;</option>
  <option <?php echo $mode==$insertMode || $LeadImageHorizontal=='right'?'selected':''?> value="right">right</option>
  <option <?php echo $LeadImageHorizontal=='left'?'selected':''?> value="left">left</option>
  <option <?php echo $LeadImageHorizontal=='none'?'selected':''?> value="none">(no alignment)</option>
</select>
<br />
<br />
<input type="button" name="Submit" value="Article Lead Image.." onclick="return ow('../admin/file_explorer/index.php?uid=mgimgs2&folder=articles&disposition=selector&cbTarget=null1&cbTargetExt=null3&cbTargetNode=null2&cbFunction=showFeatured','w_articlelead','700,500');" />
&nbsp;&nbsp;
<span id="Featured" style="font-weight:900;"><?php echo $FeaturedImage ? htmlentities($FeaturedImage) : '(none)';?></span>
<input name="FeaturedImage" type="hidden" id="FeaturedImage" value="<?php echo $FeaturedImage?>" onchange="mgeChge(this)" />
<span id="deleteFeaturedImage" style="display:<?php echo $FeaturedImage?'block':'none'?>"><a href="#" onclick="g('FeaturedImage').value='';g('Featured').innerHTML='&nbsp;'; g('deleteFeaturedImage').style.display='none';return false;" >remove image</a></span>
<input name="null1" type="hidden" id="null1" value="" />
<input name="null2" type="hidden" id="null2" value="" />
<input name="null3" type="hidden" id="null3" value="" />
<br />
<br />

<input type="button" name="Submit" value="PDF Alternate File.." onclick="return ow('../admin/file_explorer/index.php?uid=mgpdfs2&folder=pdfs&createFolder=1&disposition=selector&cbTarget=null1&cbTargetNode=null2&cbTargetExt=null3&cbFunction=showPDF','w_articlelead','700,500');" />
&nbsp;&nbsp;
<span id="FeaturedPDF" style="font-weight:900;"><?php echo $PDFLink ? htmlentities($PDFLink) : '(none)';?></span>
<input name="PDFLink" type="hidden" id="PDFLink" value="<?php echo $PDFLink?>" onchange="mgeChge(this)" />
<span id="deleteFeaturedPDF" style="display:<?php echo $PDFLink?'block':'none'?>"><a href="#" onclick="g('PDFLink').value='';g('FeaturedPDF').innerHTML='&nbsp;'; g('deleteFeaturedPDF').style.display='none';return false;" >remove PDF</a></span>
  

<?php
//-------------------------------- store tab --------------------------
get_contents_layer('cmSettings');
$tabAction='layerOutput';
require($MASTER_COMPONENT_ROOT.'/comp_tabs_v200.php');
?>


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
//this function can vary and may flush the document 
function_exists('page_end') ? page_end() : mail($developerEmail,'page end function not declared', 'File: '.__FILE__.', line: '.__LINE__,'From: '.$hdrBugs01);
?>