<?php 
//identify this script/GUI
$localSys['scriptGroup']='';
$localSys['scriptID']='generic';
$localSys['scriptVersion']='1.0';
$localSys['pageType']='Properties Window';

if(!$cssFolder)$cssFolder='/console/';

if($Articles_ID && !$ID)$ID=$Articles_ID;


//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');
require($COMPONENT_ROOT.'/settings.comp110_articles_v101.php');

//2013-03-21; add new flag fields if not present
$privateFields=array(
	'PrivateShowSubtitle'=>'ALTER TABLE cms1_articles ADD PrivateShowSubtitle TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 {after}, ADD INDEX(PrivateShowSubtitle)',
	'PrivateShowDate'=>'ALTER TABLE cms1_articles ADD PrivateShowDate TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 {after}, ADD INDEX(PrivateShowDate)',
	'PrivateShowAuthor'=>'ALTER TABLE cms1_articles ADD PrivateShowAuthor TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 {after}, ADD INDEX(PrivateShowAuthor)',
	'PrivateShowContent'=>'ALTER TABLE cms1_articles ADD PrivateShowContent TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 {after}, ADD INDEX(PrivateShowContent)',
	'PrivateShowContentWords'=>'ALTER TABLE cms1_articles ADD PrivateShowContentWords MEDIUMINT(4) UNSIGNED NOT NULL DEFAULT 50 {after}, ADD INDEX(PrivateShowContentWords)',
	'PrivateAlternateContent'=>'ALTER TABLE cms1_articles ADD PrivateAlternateContent TEXT {after}',
	'PrivateSearchable'=>'ALTER TABLE cms1_articles ADD PrivateSearchable TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 {after}, ADD INDEX(PrivateSearchable)',
);
unset($err);
$fields=q("EXPLAIN cms1_articles",O_ARRAY);
foreach($fields as $n=>$v){
	unset($fields[$n]);
	$fields[strtolower($v['Field'])]=$v['Field'];
}
foreach($privateFields as $field=> $create){
	if($fields[strtolower($field)])continue;
	ob_start();
	q(str_replace(' {after}',(' AFTER '.($after?$after:'Private')),$create), ERR_ECHO);
	$err[]=ob_get_contents();
	ob_end_clean();
	prn($qr);
	$after=$field;
}
if($err=@trim(implode("\n",$err))){
	mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err."\n\n"),$fromHdrBugs);
}

//2010-06-23 first example of a tie-in to the list view
$datasetFocusViewCall=true;
require($COMPONENT_ROOT.'/comp_12_contentlist_v100.php');

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
if($origin=='cgi'){
	$ids=q("SELECT ID FROM cms1_articles WHERE Creator='".$_SESSION['systemUserName']."' ORDER BY Priority",O_COL);
}else{
	$ids=q("SELECT ID FROM cms1_articles WHERE Category='Article' ORDER BY Priority",O_COL);
}

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
if(strlen($$object)){
	//get the record for the object
	if($a=q("SELECT a.*, f.Tree_ID AS FeaturedImageTree_ID, p.Tree_ID AS PDFTree_ID 
		FROM cms1_articles a 
		LEFT JOIN relatebase_ObjectsTree f ON f.ObjectName='cms1_articles' AND f.Objects_ID=a.ID AND f.Relationship='Featured Image'
		LEFT JOIN relatebase_ObjectsTree p ON p.ObjectName='cms1_articles' AND p.Objects_ID=a.ID AND p.Relationship='PDF Version'
		WHERE a.ID='".$$object."'",O_ROW)){
		$mode=$updateMode;
		@extract($a);
		if($FeaturedImageTree_ID){
			$FeaturedImagePath=tree_id_to_path($FeaturedImageTree_ID);
			if($gis=getimagesize($_SERVER['DOCUMENT_ROOT'].$FeaturedImagePath)){
				//OK
				ob_start();
				$FeaturedImageData=tree_image(array(
					'src'=>$FeaturedImageTree_ID,
					'boxMethod'=>2,
					'disposition'=>'250x250',
				));
				$FeaturedImageString=ob_get_contents();
				ob_end_clean();
				$FeaturedImageData=array_merge($FeaturedImageData,$gis);
			}else{
				q("DELETE FROM relatebase_ObjectsTree WHERE ObjectName='cms1_articles' AND Objects_ID=$ID AND Relationship='Featured Image'");
			}
		}
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

//--------------------------- begin elizabeth -------------------------
ob_start();
$a=q("SELECT COUNT(*) FROM relatebase_ObjectsTree", O_VALUE, ERR_ECHO);
$b=q("SELECT COUNT(*) FROM relatebase_tree", O_VALUE, ERR_ECHO);


$err=ob_get_contents();
ob_end_clean();

if($err){
	//echo $err;
}
$articleImageTypes=array('Featured Image','Additional Image','Slide Show Image','Diagram','Author Picture','PDF Version','PDF Resource');
$ot=q("EXPLAIN relatebase_ObjectsTree", O_ARRAY);
//make sure enum values are in
foreach($ot as $n=>$v){
	$ot[strtolower($v['Field'])]=$v;
	unset($ot[$n]);
	if($v['Field']=='Relationship'){
		if(preg_match('/(ENUM|SET)\s*\(/i',$v['Type'],$m)){
			$type=preg_replace('/^(ENUM|SET)\s*\(/i','',$v['Type']);
			$type=preg_replace('/\)$/','',$type);
			$types=explode('\',\'',trim(strtolower($type),'\''));
			unset($add);
			foreach($articleImageTypes as $w)if(!in_array(strtolower($w),$types))$add[]=$w;
			if($add){
				$add='\''.implode('\',\'',$add).'\'';
				q("ALTER TABLE relatebase_ObjectsTree CHANGE Relationship Relationship ".$m[1] . '('.$add.','.$type.')'.($v['Null']=='YES'?' NULL':' NOT NULL').($v['Default']?' DEFAULT \''.$v['Default'].'\'':''), ERR_ECHO);
			}
			break;
		}
	}
}
if(trim($FeaturedImage)){
	if(file_exists($_SERVER['DOCUMENT_ROOT'].'/'.trim($FeaturedImage,'/'))){
		if($FeaturedImageTree_ID=q("SELECT Tree_ID FROM relatebase_ObjectsTree WHERE ObjectName='cms1_articles' AND Objects_ID=$ID AND Relationship='Featured Image'", O_VALUE)){
			//OK
		}else{
			$FeaturedImageTree_ID=tree_build_path('/'.trim($FeaturedImage,'/'), $options=array('lastNodeType'=>'file'));
			q("INSERT INTO relatebase_ObjectsTree SET Objects_ID=$ID, ObjectName='cms1_articles', Tree_ID=$FeaturedImageTree_ID, Relationship='Featured Image', CreateDate=NOW(), Creator='".sun()."'");
		}
	}
	//sunset the featured image field eventually and featured pdf
	q("UPDATE cms1_articles SET FeaturedImage='', EditDate=EditDate WHERE ID=$ID");
}
//--------------------------- end elizabeth -------------------------


if(!$defaultSummaryWordCount)$defaultSummaryWordCount=35;

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/properties_04_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Articles and Content - <?php echo $mode==$insertMode?'Adding New':h($Title);?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" href="<?php echo $cssFolder?>rbrfm_admin.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<link href="/Library/ckeditor_3.4/_samples/sample.css" rel="stylesheet" type="text/css" />
<style type="text/css">
body{
	background-color:#FCF1DE;
	}
.indent{
	border-left:1px solid #ccc;
	margin-left:15px;
	padding-left:7px;
	}
</style>
<script language="JavaScript" type="text/javascript" src="/Library/js/global_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/common_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/forms_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/loader_04_i1.js"></script>
<script type="text/javascript" src="/Library/ckeditor_3.4/ckeditor.js"></script>
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

AddOnkeypressCommand('PropKeyPress(e)'); //if not declared already


function showFeatured(){
	file=g('null1').value+'.'+g('null3').value;
	node=g('null2').value;
	if(g('FeaturedImage').value!=='images/'+node+'/'+file)dChge(g('FeaturedImage'));
	g('Featured').innerHTML='images/'+node+'/'+g('null1').value;
	g('FeaturedImage').value='images/'+node+'/'+file;
	g('deleteFeaturedImage').style.display='block';
}
function showPDF(){
	file=g('null1').value+'.'+g('null3').value;
	node=g('null2').value;
	if(g('PDFLink').value!=='images/'+node+'/'+file)dChge(g('PDFLink'));
	g('FeaturedPDF').innerHTML='images/'+node+'/'+g('null1').value;
	g('PDFLink').value='images/'+node+'/'+file;
	g('deleteFeaturedPDF').style.display='block';
}
function fA(n){
	if(n=='prettyURL'){
		if(g('Title').value!='' && g('KeywordsTitle').value==''){
			var str=g('Title').value.replace(/\s+/g,'-');
			str=str.replace(/[^-a-z0-9]/gi,'');
			str=str.replace(/[-]{2,}/gi,'-');
			g('KeywordsTitle').value=str;
		}
	}
}
</script>
<!-- InstanceEndEditable -->
</head>

<body id="properties">
<form id="form1" name="form1" target="w2" method="post" action="/console/resources/bais_01_exe.php" onsubmit="return beginSubmit();" enctype="multipart/form-data">
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
		<h2 class = "h2_1">Manage Articles and Content - <?php echo $mode==$insertMode?'Adding New':h($Title);?></h2> 
	</div>
<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->

<input type="hidden" name="Active" value="0" />
<label> 
<input name="Active" type="checkbox" id="Active" value="1" <?php echo !isset($Active) || $Active==1 ? 'checked' : ''?> onchange="dChge(this)" /> Active Article</label>
<br />
<input type="hidden" name="LeadArticle" value="0" />
<label>
<input name="LeadArticle" type="checkbox" id="LeadArticle" value="1" <?php echo $LeadArticle ? 'checked' : ''?> /> Lead (featured) article</label>
<br />

<br />
Type of Content: 
<select name="Category" id="Category" onchange="dChge(this)">
  <option value="">&lt; Select.. &gt;</option>
  <?php
$a=$availableCols[$datasetGroup][$modApType][$modApHandle]['scheme']['Category']['fieldvalues'];
$arrayType='default';
foreach($a as $n=>$v){
	if(!is_int($n)){ $arrayType=='assoc'; break; }
}
$haveSelected=false;
foreach($a as $n=>$v){
	$key=($arrayType=='default'?$v:$n);
	if($key==$Category && strlen($Category))$haveSelected=true;
	?>
  <option value="<?php echo h($key);?>" <?php echo $key==$Category?'selected':''?>><?php echo h($v);?></option>
  <?php
}
if(!$haveSelected && strlen($Category)){
	?>
  <option value="<?php echo h($Category);?>" selected="selected"><?php echo h($Category)?></option>
  <?php
}
?>
</select>
<br />
Title:
<input name="Title" type="text" id="Title" onchange="dChge(this)" value="<?php echo $Title?>" size="60" onblur="fA('prettyURL');" />
<br />
<br />
<?php
ob_start(); 
if($mode==$insertMode && !$Contacts_ID)$Contacts_ID=$defaultArticleContacts_ID;
?>
Author: &nbsp;	
<select name="Contacts_ID" id="Contacts_ID" cbtable="cms1_articles" onchange="dChge(this);newOption(this, 'members.php', 'l1_members', '700,700');">
  <option value="">&lt; Select.. &gt;</option>
  <?php
	$contacts=q("SELECT a.ID, a.FirstName, a.LastName, c.ClientName, c.CompanyName FROM addr_contacts a LEFT JOIN finan_ClientsContacts b ON a.ID=b.Contacts_ID AND b.Type='Primary' LEFT JOIN finan_clients c ON b.Clients_ID=c.ID ORDER BY LastName", O_ARRAY_ASSOC);
	foreach($contacts as $n=>$v){
		?><option value="<?php echo $n?>" <?php	if($n==$Contacts_ID)echo 'selected';?>><?php echo $v['LastName'] .', '. $v['FirstName'].($v['ClientName'] && strtolower($v['ClientName'])!==strtolower($v['FirstName'] . ' ' . $v['LastName']) ? ', '.$v['ClientName'] : '');?></option>
  <?php
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
<input name="PostDate" type="text" id="PostDate" onchange="dChge(this)" value="<?php echo date('m/d/Y g:iA',$PostDate ? strtotime($PostDate) : time())?>" size="22" />
<br />
&quot;Pretty URL&quot;/Keywords Title:<br /> 
<input name="KeywordsTitle" type="text" id="KeywordsTitle" onchange="dChge(this)" value="<?php echo h($KeywordsTitle)?>" size="45" />
<br />
<em class="gray">(a-z and 0-9 only, spaces will be replaced by dashes)</em>
<p>Subtitle:<br />
<textarea name="SubTitle" cols="65" rows="2" id="SubTitle" onchange="dChge(this)"><?php echo $SubTitle?></textarea>
<br />
<br />
Description:<br />
<textarea name="Description" cols="65" rows="4" id="Description" onchange="dChge(this)"><?php echo $Description?></textarea>
</p>
<?php
get_contents_tabsection('summaryinfo');
?>
<a href="/admin/file_explorer/?uid=imglibrary" title="View pictures to add to articles" onclick="return ow(this.href,'l1_fex','700,700');">View Picture Library</a><br />
<textarea cols="80" id="Body" name="Body" rows="10"><?php echo h($Body);?></textarea>
<script type="text/javascript">
var editor = CKEDITOR.replace( 'Body' );
setTimeout('CheckDirty(\'Body\')',1000);
</script>
Youtube Embed Code (Only For Videos):<br />
<textarea cols="80" id="EmbedCode" name="EmbedCode" rows="10" onchange="dChge(this)"><?php echo h($EmbedCode);?></textarea>
<?php
get_contents_tabsection('body');
?>
Notification <br />
<label><input name="SendNotification" type="checkbox" id="SendNotification" value="1" onchange="dChge(this)" />
Send this out to: </label>
<select name="Queries_ID" id="Queries_ID" onchange="dChge(this)">
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
<select name="Templates_ID" id="Templates_ID" onchange="dChge(this)">
	<option value="default">default layout</option>
</select> 
<br />
<!-- ()using default template -->
Introductory message:<br />
<textarea name="CustomMessage" cols="40" rows="3" id="CustomMessage" onchange="dChge(this)"></textarea>
<!-- unsubscribe - what will it do -->


<?php
get_contents_tabsection('notification');
?>
<label><input name="BodySummaryTruncate" type="checkbox" id="BodySummaryTruncate" value="1" <?php echo $mode==$insertMode || $BodySummaryTruncate ? 'checked':''?> onchange="dChge(this);" /> 
Cut off summary page content over </label>
<input name="BodySummaryWordCount" type="text" id="BodySummaryWordCount" value="<?php echo $BodySummaryWordCount ? $BodySummaryWordCount : $defaultSummaryWordCount?>" size="3" onchange="dChge(this);" /> 
words<br /> 
<br />
Place lead image 
<select name="LeadImageVertical" id="LeadImageVertical" onchange="dChge(this)">
	<option>&lt;select..&gt;</option>
	<option <?php echo $mode==$insertMode || $LeadImageVertical=='top'?'selected':''?> value="top">at top</option>
	<option <?php echo $LeadImageVertical=='bottom'?'selected':''?> value="bottom">at bottom</option>
</select>, aligned to the 
<select name="LeadImageHorizontal" id="LeadImageHorizontal" onchange="dChge(this)">
	<option>&lt;select..&gt;</option>
	<option <?php echo $mode==$insertMode || $LeadImageHorizontal=='right'?'selected':''?> value="right">right</option>
	<option <?php echo $LeadImageHorizontal=='left'?'selected':''?> value="left">left</option>
	<option <?php echo $LeadImageHorizontal=='none'?'selected':''?> value="none">(no alignment)</option>
</select>
<br />
<br />
<!--
any ref to Featured Image - > Lead Image
show the image and get the name
show the name but title="" the path
submission still edits or deletes but uses the join table
handle the pdf - this just goes and gets from the icon library
-->
<div id="FeaturedImagePreview" class="fr"><?php
if($FeaturedImageString){
	//for button below
	$fpath=explode('/',$FeaturedImagePath);
	$file=array_pop($fpath);
	unset($fpath[0]);
	$path=implode('/',$path);

	?><a href="#" onclick="return ow(this.firstChild.src.replace(/disposition=[^&]*/,''),'l1_img','750,850');" title="Location=<?php echo h($FeaturedImagePath);?>; width=<?php echo $FeaturedImageData[0];?>; height=<?php echo $FeaturedImageData[1];?>"><?php echo $FeaturedImageString;?></a><?php
}
?></div>

<input type="button" name="Submit" value="Article Lead Image.." onclick="return ow('/admin/file_explorer/index.php?uid=mgfi&folder=<?php echo isset($fpath) ? $fpath : 'articles';?>&disposition=selector&cbTarget=null1&cbTargetExt=null3&cbTargetNode=null2&cbFunction=showFeatured','w_articlelead','700,500');" />
&nbsp;&nbsp;
<span id="Featured" style="font-weight:900;"><?php echo $FeaturedImagePath ? htmlentities($FeaturedImagePath) : '(none)';?></span>
<input name="FeaturedImageTree_ID" type="hidden" id="FeaturedImageTree_ID" value="<?php echo $FeaturedImageTree_ID?>" onchange="dChge(this)" />
<span id="deleteFeaturedImage" style="display:<?php echo $FeaturedImageTree_ID?'block':'none'?>"><a href="#" onclick="g('FeaturedImageTree_ID').value='';g('Featured').innerHTML='&nbsp;'; g('deleteFeaturedImage').style.display='none';return false;" >remove image</a></span>
<input name="null1" type="hidden" id="null1" value="" />
<input name="null2" type="hidden" id="null2" value="" />
<input name="null3" type="hidden" id="null3" value="" />
<br />
<br />

<input type="button" name="Submit" value="PDF Alternate File.." onclick="return ow('/admin/file_explorer/index.php?uid=mgpdfs2&folder=pdfs&createFolder=1&disposition=selector&cbTarget=null1&cbTargetNode=null2&cbTargetExt=null3&cbFunction=showPDF','w_articlelead','700,500');" />
&nbsp;&nbsp;
<span id="FeaturedPDF" style="font-weight:900;"><?php echo $PDFLink ? htmlentities($PDFLink) : '(none)';?></span>
<input name="PDFLink" type="hidden" id="PDFLink" value="<?php echo $PDFLink?>" onchange="dChge(this)" />
<span id="deleteFeaturedPDF" style="display:<?php echo $PDFLink?'block':'none'?>"><a href="#" onclick="g('PDFLink').value='';g('FeaturedPDF').innerHTML='&nbsp;'; g('deleteFeaturedPDF').style.display='none';return false;" >remove PDF</a></span>
  

<?php
get_contents_tabsection('settings');
?>
<table cellpadding="0">
  <tr>
    <td colspan="2"><input type="hidden" name="Private" value="0" />
      <label>
      <input name="Private" type="checkbox" id="Private" value="1" <?php echo $Private==1 ? 'checked' : ''?> onchange="dChge(this);" />
      <strong>Private content (must be signed in)</strong></label></td>
  </tr>
  <tr>
    <td colspan="2"><input type="hidden" name="PrivateShowSummaryPublicly" value="0" />
      <label>
      <input name="PrivateShowSummaryPublicly" type="checkbox" id="PrivateShowSummaryPublicly" value="1" <?php echo $PrivateShowSummaryPublicly?'checked':''?> onchange="dChge(this)" />
       Show  the summary  when  signed out, however </label></td>
    </tr>
  <tr>
    <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
    <td><strong>Show the following parts in summary when signed out: 
	</strong>
      <div class="indent">
	<label><img src="/images/i/yes.gif" /> Title (not controllable)</label>
	<br />
	<input type="hidden" name="PrivateShowSubtitle" value="0" />
	<label>
	<input type="checkbox" name="PrivateShowSubtitle" id="PrivateShowSubtitle" value="1" <?php echo $PrivateShowSubtitle || !isset($PrivateShowSubtitle)?'checked':''?> onchange="dChge(this);" /> Subtitle</label>
	<br />
	<input type="hidden" name="PrivateShowDate" value="0" />
	<label>
	<input type="checkbox" name="PrivateShowDate" id="PrivateShowDate" value="1" <?php echo $PrivateShowDate || !isset($PrivateShowDate)?'checked':''?> onchange="dChge(this);" /> Date</label>
	<br />
	<input type="hidden" name="PrivateShowAuthor" value="0" />
	<label>
	<input type="checkbox" name="PrivateShowAuthor" id="PrivateShowAuthor" value="1" <?php echo $PrivateShowAuthor || !isset($PrivateShowAuthor)?'checked':''?> onchange="dChge(this);" /> Author</label>
	<br />
	<input type="hidden" name="PrivateShowContent" value="0" />
	<label>
	<input type="checkbox" name="PrivateShowContent" id="PrivateShowContent" value="1" <?php echo $PrivateShowContent || !isset($PrivateShowContent)?'checked':''?> onchange="dChge(this);" /> Content up to </label>
	<input name="PrivateShowContentWords" type="text" id="PrivateShowContentWords" value="<?php echo $PrivateShowContentWords ? $PrivateShowContentWords : 50;?>" size="3" onchange="dChge(this);" /> words.<br />
	Alternate text<span class="gray">(HTML OK)</span>:<br />
	<textarea name="PrivateAlternateContent" id="PrivateAlternateContent" onchange="dChge(this);" class="tabby" rows="3" cols="55"><?php
	echo h($PrivateAlternateContent);
	?></textarea>
	<br />
	<br />
	<label><img src="/images/i/del2.gif" /> PDF Link if available (not controllable)</label>
	</div>	</td>
  </tr>
  <tr>
    <td colspan="2"><input type="hidden" name="PrivateSearchable" value="0" />
      <label>
      <input name="PrivateSearchable" type="checkbox" id="PrivateSearchable" value="1" <?php echo $PrivateSearchable==1 || !isset($PrivateSearchable) ? 'checked' : ''?> onchange="dChge(this)" /> 
      Include article in searches even when signed out
</label></td>
    </tr>
</table>
<input type="hidden" name="AllowComments" value="0" />
<label>
<input type="checkbox" name="AllowComments" id="AllowComments" value="1" onchange="dChge(this);" <?php echo $AllowComments || !isset($AllowComments)?'checked':''?> /> Allow comments on this posting</label>


<?php
get_contents_tabsection('privacy');
?>

<?php
get_contents_tabsection('help');
tabs_enhanced(array(
	'summaryinfo'=>array(
		'label'=>'Summary Info',
	),
	'body'=>array(
		'label'=>'Article Body',
	),
	'notification'=>array(
		'label'=>'Notification/Sending',
	),
	'settings'=>array(
		'label'=>'Setting/Layout',
	),
	'privacy'=>array(
		'label'=>'Privacy',
	),
	'help'=>array(
		'label'=>'Help',
	),
));
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
	<iframe name="w1" src="../Library/blank.htm"></iframe>
	<iframe name="w2" src="../Library/blank.htm"></iframe>
	<iframe name="w3" src="../Library/blank.htm"></iframe>
	<iframe name="w4" src="../Library/blank.htm"></iframe>
</div>
<?php } ?>
</body>
<!-- InstanceEnd --></html><?php
page_end();
?>