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
//------------------------ Navbuttons head coding v1.43 -----------------------------
//change these first vars and the queries for each instance
$object='Properties_ID';
$recordPKField='ID'; //primary key field
$navObject='Properties_ID';
$updateMode='updateFeaturedProperty';
$insertMode='insertFeaturedProperty';
$deleteMode='deleteFeaturedProperty';
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
$ids=q("SELECT ID FROM re1_properties WHERE 1 ORDER BY Priority",O_COL);
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
	if($a=q("SELECT a.*, b.Tree_ID, c.Name AS FeaturedImage FROM re1_properties a 
		LEFT JOIN re1_PropertiesTree b ON a.ID=b.Properties_ID AND b.Type='Featured Image' AND b.Idx=1 
		LEFT JOIN relatebase_tree c ON b.Tree_ID=c.ID 
		WHERE a.ID='".$$object."' GROUP BY a.ID",O_ROW)){
		$mode=$updateMode;
		@extract($a);
		if($Tree_ID){
			$path=tree_id_to_path($Tree_ID);
			$path=trim($path,'/');
			$path=explode('/',$path);
			array_pop($path);
			if($path[0]=='images')unset($path[0]);
			$path=implode('/',$path);
		}
	}else{
		//object may have been deleted by another user, least perplexing approach is to present insert mode
		$mode=$insertMode;
		unset($$object);
		$nullAbs=$nullCount+1;
	}
}else{
	$mode=$insertMode;
	//$$object=$ID=quasi_resource_generic($MASTER_DATABASE, 'sma_assns', $ResourceToken, $typeField='ResourceType', $sessionKeyField='sessionKey', $resourceTokenField='ResourceToken', $primary='ID', $creatorField='Creator', $createDateField='CreateDate' /*, C_DEFAULT, $options */);

	$nullAbs=$nullCount+1; //where we actually are right then
}
//--------------------------- end coding --------------------------------


foreach($consoleEmbeddedModules as $remid=>$v){
	if(strtolower($v['SKU'])=='rem-90'){
		$rem=$v;
		break;
	}
}
if(!$rem)exit('You do not have an REM module installed in your account');
$hideCtrlSection=false;

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/properties_04_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $titleBase='Real Estate Property Management '.($mode==$insertMode ? ' - Adding New Property':' - '.$PropertyName)?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" href="/console/rbrfm_admin.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<style type="text/css">
#properties{
	background-color:ivory;
	}
#mainBody{
	padding:0px 20px;
	}
</style>
<script language="JavaScript" type="text/javascript" src="/Library/js/global_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/common_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/forms_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/loader_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/contextmenus_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/dataobjects_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/jquery.js"></script>
<script type="text/javascript" src="../Library/ckeditor_3.4/ckeditor.js"></script>
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
var UserName='<?php echo $UserName?>';

AddOnkeypressCommand("PropKeyPress(e)");
//var customDeleteHandler='deleteItem()';
function deleteItem(){
}
function showFeatured(){
	file=g('null1').value+'.'+g('null3').value;
	node=g('null2').value;
	detectChange=1;
	g('Featured').innerHTML=g('null1').value+'.'+g('null3').value;
	g('deleteFeaturedImage').style.display='inline';
	openFolder=g('null2').value;
}
function deleteFeatured(){
	g('RemoveFeaturedImage').value='1'; 
	g('deleteFeaturedImage').style.display='none'; 
	g('Featured').innerHTML='&nbsp;'; 
	detectChange=1; 
}
var openFolder='<?php
if($FeaturedImage){
	echo $path;
}else if($mode==$updateMode){
	echo 'slides/'.$Handle;
}else{
	echo 'slides/';
}
?>';
var str2='&disposition=selector&overrideSelectorRedirect=1&cbTarget=null1&cbTargetNode=null2&cbTargetExt=null3&cbFunction=showFeatured';
$(document).ready(function(){
	$('#buttonFeaturedImage').click(function(){
		if(g('originalHandle').value!=='' && g('originalHandle').value!==g('Handle').value && !confirm('You have changed the identifier/handle for this property.  This will create a new folder for the pictures and you may have pictures in two different folders. Continue?'))return false;
		if(!g('Handle').value.match(/^[a-z0-9_]+$/gi)){
			alert('Handle/identifier must have only a-z, 0-9 and an underscore');
			return false;
		}
		ow('/admin/file_explorer/?uid=mgimgs&folder=slides%2F'+g('Handle').value+str2,'l1images','800,700');
	});
});
</script>

<!-- InstanceEndEditable -->
</head>

<body id="properties">
<form id="form1" name="form1" target="w2" method="post" action="/console/resources/bais_01_exe.php" onsubmit="return beginSubmit();" enctype="multipart/form-data">
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
	
	<input type="button" name="Button2" value="Set Up Domain Name.." onclick="if(detectChange &amp;&amp; !confirm('You have changed the record; if you switch to Set Up Domain Name, you will lose these changes.  Are you sure?'))return false; window.location=('focus_featured_properties_domain.php?Properties_ID=<?php echo $$object?><?php
	//pass on any callback function
	foreach($_GET as $n=>$v){
		if(preg_match('/^cb/',$n)){
			echo '&'.$n .'='.$v;
		}
	}	
	?>');" />
	
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

<br />
<br />
<br />


<div id="property">
	<input type="hidden" name="ShowCategory[]" id="ShowCategory[]" value="Standard" />
	<div class="fr">
	<input type="hidden" name="Active" value="0" />
	<label><input name="Active" type="checkbox" id="Active" value="1" <?php echo !isset($Active) || $Active==1 ? 'checked' : ''?> onChange="dChge(this)" /> Active Property/List on Site</label><br />
	Status: 
	<select name="Status" id="Status" onchange="dChge(this)">
      <option value="">&lt;select&gt;</option>
      <option <?php echo $Status=='For Sale'?'selected':''?> value="For Sale">For Sale</option>
      <option <?php echo $Status=='Sold'?'selected':''?> value="Sold">Sold</option>
      <option <?php echo $Status=='Closing/In Escrow'?'selected':''?> value="Closing/In Escrow">Closing/In Escrow</option>
    </select>
	<br />
	Posting date: 
	<input name="PostDate" type="text" id="PostDate" value="<?php echo date('m/d/Y g:iA',$PostDate ? strtotime($PostDate) : time())?>" onchange="dChge(this)" />
	<br />
	</div>
Property: 
<input name="PropertyName" type="text" id="PropertyName" onchange="dChge(this);if(g('KeywordsTitle').value=='')g('KeywordsTitle').value=this.value.replace(/[^a-z0-9 ]+/gi,'').replace(/ {2,}/gi,' ');" value="<?php echo h($PropertyName)?>" size="55" />
<br />
List price: 
<input name="Price" type="text" id="Price" onchange="dChge(this)" value="<?php echo $Price>0?number_format($Price,2):'';?>" size="17" />
<br />
Lowest offer price: 
<input name="LowestPrice" type="text" id="LowestPrice" onchange="dChge(this)" value="<?php echo $LowestPrice?>" size="17" />
(Price at which they will sell)
<br />
Terms of LOP: 
<input name="LowestPriceTerms" type="text" id="LowestPriceTerms" onchange="dChge(this);if(g('KeywordsTitle').value=='')g('KeywordsTitle').value=this.value.replace(/[^a-z0-9 ]+/gi,'').replace(/ {2,}/gi,' ');" value="<?php echo h($LowestPriceTerms)?>" size="55" />
<br />


<?php
ob_start();
?><div>
	<div id="viewInSite" class="fr"><?php if($ID){ ?>
	<input type="button" name="Submit" value="View in Site" onClick="window.location='../featured.php?Handle=<?php echo $Handle?>';" />
	<?php } ?></div>
	
	"Pretty URL": 
	<input name="KeywordsTitle" type="text" id="KeywordsTitle" onchange="dChge(this)" value="<?php echo h($KeywordsTitle)?>" size="55" />
	<br />
	Spaces will be replaced by dashes.
	<br />
	<span class="gray">This is how the property will list for SEO purposes (e.g. <strong><?php echo $HTTP_HOST;?>/hilltop-central-texas-estate-kyle</strong>)</span><br />
	<table>
	<tr>
	  <td style="width:200px;">MLS Number: </td>
	  <td><input name="MLSNumber" type="text" id="MLSNumber" value="<?php echo $MLSNumber?>" onchange="dChge(this)" /></td>
	  <td>&nbsp;</td>
	</tr>
	<tr>
	  <td><span class="fl">Agent name:</span></td>
	  <td><span class="fl">
		<input name="AgentName" type="text" id="AgentName" value="<?php echo isset($AgentName)?$AgentName : (isset($realtorInfo['FullName'])?$realtorInfo['FullName'] : '')?>" onchange="dChge(this);" />
	  </span></td>
	  <td>&nbsp;</td>
	</tr>
	<tr>
	  <td><span class="fl">Agent email:</span></td>
	  <td><span class="fl">
		<input name="AgentEmail" type="text" id="AgentEmail" value="<?php echo isset($AgentEmail)?$AgentEmail : (isset($realtorInfo['Email'])?$realtorInfo['Email'] : '')?>" onchange="dChge(this);" />
	  </span></td>
	  <td>&nbsp;</td>
	</tr>
	<tr>
		<td>Category:</td>
		<td>
		  <select name="ShowCategory[]" size="7" id="ShowCategory[]" onchange="dChge(this)" multiple="multiple">
			<?php
			$showCategories=array('Land','Residential','Commercial');
			if(!isset($ShowCategory) || $ShowCategory=='Standard'){
				$ShowCategory=array('Residential');
			}else $ShowCategory=explode(',',$ShowCategory);
			foreach($showCategories as $n=>$v){
				?><option value="<?php echo $v?>" <?php echo in_array($v,$ShowCategory)?'selected':''?>><?php echo $v?></option><?php
			}
			?>
		  </select>
		</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>Address:</td>
		<td>Street:
		  <input name="Address" type="text" id="Address" value="<?php echo h($Address)?>" onchange="dChge(this)" onblur="var reg=/[^_a-z0-9]/gi;if(g('Handle').value=='')g('Handle').value=this.value.replace(reg,'').toLowerCase();"/>
		  <br />
		  City:
		  <input name="City" type="text" id="City" value="<?php echo h($City)?>" onchange="dChge(this)" />
		  <br />
		  State: 
		  <select name="State" id="State" onchange="dChge(this)">
			<option value="">&lt;Select..&gt;</option>
			<?php
	$states=q("SELECT st_code, st_name FROM z_public.aux_states", O_COL_ASSOC, $public_cnx);
	foreach($states as $n=>$v){
		?>
			<option value="<?php echo $n?>" <?php echo $State==$n || ($mode==$insert && $State=='TX') ? 'selected':''?>><?php echo $v?></option>
			<?php
	}
	?>
		  </select>
		  <br />
		  Zip: 
		  <input name="Zip" type="text" id="Zip" value="<?php echo $Zip?>" onchange="dChge(this)" /></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>Notes:</td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td>Meta Title:<br />
		<span class="gray">(This is the title of the page)</span></td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td>Meta Description:<br />
		<span class="gray">(This is an important SEO element, must match property description)</span></td>
		<td></td>
		<td></td>
	</tr>
	</table>
</div><?php
get_contents_tabsection('Details');
?><div>
	<textarea cols="80" id="Description" name="Description" rows="10"><?php echo h($Description);?></textarea>
	<script type="text/javascript">
	var editor = CKEDITOR.replace( 'Description' );
	setTimeout('CheckDirty(\'Description\')',1000);
	</script>
</div><?php
get_contents_tabsection('description');
?><div>
	<input type="button" name="Button" value="Set Up Domain Name.." onClick="return ow('focus_featured_properties_domain.php?Properties_ID=<?php echo $ID?>','l1_domain','700,700');" />
</div><?php
get_contents_tabsection('External Website');
?><div>

Property Owner/Seller data here..<br />


</div><?php
get_contents_tabsection('Owner');
?><div>

MLS Data here..<br />


</div><?php
get_contents_tabsection('MLSData');
?><div>
	<p>Property Identifier:
	
	<input name="originalHandle" type="hidden" id="originalHandle" value="<?php echo $Handle;?>" />
	<input name="Handle" type="text" id="Handle" value="<?php echo $Handle?>" onchange="dChge(this)" />
	
	<input type="button" name="Button" value="Check.." onClick="checkHandle()" />
	<span id="checkHandle">&nbsp;</span>
	<br />
	<br />


	<input type="button" name="Submit" value="View/Manage Images.." onClick="if(<?php echo !$ID?'true':'false'?>){ alert('You must first save this new property before you add images'); return false; } return ow('../admin/file_explorer/index.php?uid=mgimgs1&folder=slides/<?php echo $Handle?>&createFolder=1','w<?php echo $Handle?>','700,500');" />
	<br />
	<input name="RemoveFeaturedImage" type="hidden" id="RemoveFeaturedImage" value="" />
	<input name="null1" type="hidden" id="null1" value="" />
	<input name="null2" type="hidden" id="null2" value="" />
	<input name="null3" type="hidden" id="null3" value="" />
	<input name="realtorID" type="hidden" id="realtorID" value="<?php echo $realtorInfo['Agents_ID']?>" />
	<input name="realtorOffice" type="hidden" id="realtorOffice" value="<?php echo $realtorInfo['Offices_ID']?>" />
	<br />
	<br />

	<input type="button" name="Submit" id="buttonFeaturedImage" value="Featured (Lead) Image.." />
	<span id="Featured"><?php echo htmlentities($FeaturedImage);?></span>
	&nbsp;
	<span id="deleteFeaturedImage" style="display:<?php echo $FeaturedImage?'inline':'none'?>">
		<input type="button" name="button" value="remove image.." onclick="deleteFeatured()" />
	</span>


	<br />
	<span class="gray">(this is also the folder where pictures are stored)</span><br />
	</p>
	<p>picture list here (thumbnails and by category)     </p>


	<div class="fr"><?php
	if(file_exists('../images/slides/featured/.thumbs.dbr/'.$LeadImage)){
		?><img src="<?php echo '../images/slides/featured/.thumbs.dbr/'.$LeadImage?>" /><?php
	}
	?></div>
</div><?php
get_contents_tabsection('Pictures');
?><div>
	<?php
	require($COMPONENT_ROOT.'/comp_511_propertyfiles.php');
	?>
	Flash Movie Code <span class="gray">(optional)</span>: <br />
	<textarea name="FlashMovieCode" onChange="dChge(this)" cols="45" rows="5" id="FlashMovieCode"><?php echo $FlashMovieCode?></textarea>
</div><?php
get_contents_tabsection('Media');
?><div>

</div><?php
get_contents_tabsection('Help');

tabs_enhanced(array(
	'Details'=>array(),
	'description'=>array(
		'label'=>'Description',
	),
	'External Website'=>array(
		'active'=>($rem['moduleAdminSettings']['implementExternalDomains']?1:0),
	),
	'Owner'=>array(),
	'MLSData'=>array(
		'label'=>'MLS/Idx',
	),
	'Pictures'=>array(),
	'Media'=>array(),
	'Help'=>array(),
),
array('fade'=>true)
);

?>
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