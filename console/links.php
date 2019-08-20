<?php 
/*
todo
---------------------
add client callback
interface between direction,status,type and client gelled better
first and last name and email filled in
default link found on page
tabindex=-1 on view links by textareas

*/
//identify this script/GUI
$localSys['scriptGroup']='';
$localSys['scriptID']='generic';
$localSys['scriptVersion']='1.0';
$localSys['pageType']='Properties Window';



//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');
$statusTypes=array('Present','Not Present','Suspect','Site Down','Other');
$linkTypes=array('In-text','Listing','Featured','Image','Other');

//mini-settings for this page
$dataset='Link';


//------------------------ Navbuttons head coding v1.43 -----------------------------
//change these first vars and the queries for each instance
$object='Links_ID';
$recordPKField='ID'; //primary key field
$navObject='Links_ID';
$updateMode='updateLink';
$insertMode='insertLink';
$deleteMode='deleteLink';
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
$ids=q("SELECT ID FROM _v_links_warranting_dependent_flat_20090913 WHERE 1 ORDER BY CreateDate",O_COL);

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
	if($a=q("SELECT * FROM _v_links_warranting_dependent_flat_20090913 WHERE ID='".$$object."'",O_ROW)){
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
	$nullAbs=$nullCount+1; //where we actually are right then
}
//--------------------------- end coding --------------------------------




$hideCtrlSection=false;
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><!-- UnusableWithFormBegin template="/Templates/properties_04_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- UnusableWithFormBeginEditable name="doctitle" -->
<title>Console : Links</title>
<!-- UnusableWithFormEndEditable -->
<!-- UnusableWithFormBeginEditable name="head" -->

<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" href="/console/rbrfm_admin.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<style type="text/css">
body{
	background-color:#CCC;
	}
.objectWrapper{
	padding:0px 20px;
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


AddOnkeypressCommand("PropKeyPress(e)");
//var customDeleteHandler='deleteLink()';
function deleteLink(){
}


<?php
//default values
if($a=$defaultValues[$dataset]){
	foreach($a as $n=>$v)echo 'sets[\''.$n.'\']=\''.str_replace("'","\'",$v).'\';'."\n";
}
?>
</script>
<!-- UnusableWithFormEndEditable -->
</head>

<body>
<form id="form1" name="form1" target="w2" method="post" action="/console/resources/bais_01_exe.php" onSubmit="return beginSubmit();" enctype="multipart/form-data">
<input name="newframesontop" id="newframesontop" type="hidden" value="1" />
<div id="header"><!-- UnusableWithFormBeginEditable name="top_nav" -->
	<div id="headerBar1" style="padding:5px 10px 10px 12px; background-color:#CCC;">
		<div id="btns140" class="fr"><?php ob_start();?>
		<input id="Previous" type="button" name="Submit" value="Previous" class="navButton_A" onClick="focus_nav(-1, '<?php echo ($mode==$insertMode?'insert':'update')?>', <?php echo $mode==$insertMode?1:0?>, 0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $nullAbs<=1?'disabled':''?> >
		<?php
		//Handle display of all buttons besides the Previous button
		if($mode==$insertMode){
			if($insertType==2 /** advanced mode **/){
				//save
				?><input id="Save" type="button" name="Submit" value="Save" class="navButton_A" onClick="focus_nav(0,'insert',1,2<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveInitiallyDisabled?>><?php
			}
			//save and new - common to both modes
			?><input id="SaveAndNew" type="button" name="Submit" value="Save &amp; New" class="navButton_A" onClick="focus_nav(0,'insert', 1,1<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveAndNewInitiallyDisabled?>><?php
			if($insertType==1 /** basic mode **/){
				//save and close
				?><input id="SaveAndClose" type="button" name="Submit" value="Save &amp; Close" class="navButton_A" onClick="focus_nav(0,'insert', 1,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveAndCloseInitiallyDisabled?>><?php
			}
			?><input id="CancelInsert" type="button" name="Submit" value="Cancel" class="navButton_A" onClick="focus_nav_cxl('insert');"><?php
		}else{
			//OK, and appropriate [next] button
			?><input id="OK" type="button" name="Submit" value="OK" class="navButton_A" onClick="focus_nav(0,'<?php echo $mode==$insertMode?'insert':'update'?>',0,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);">
			<input id="Next" type="button" name="Submit" value="Next" class="navButton_A" onClick="focus_nav(1,'<?php echo $mode==$insertMode?'insert':'update'?>',0,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $nullAbs>$nullCount || ($denyNextToNew && $nullAbs==$nullCount) ?'disabled':''?>><?php
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
		<h2 class="nullBottom">Links</h2>
	</div>
<!-- UnusableWithFormEndEditable --></div>
<div id="mainBody"><!-- UnusableWithFormBeginEditable name="main_body" -->
<div class="objectWrapper">
	Direction: 
	<script language="javascript" type="text/javascript">
	function toggleJustify(n){
		g('justify').style.display=(n=='Outgoing'?'inline':'none');	
		g('Status').disabled=(n=='Outgoing'?true:false);
	}
	</script>
	<select name="Direction" id="Direction" onChange="toggleJustify(this.value);dChge(this);">
		<option value="">&lt;Select..&gt;</option>
		<option value="Incoming" <?php echo $Direction=='Incoming'?'selected':''?>>Incoming</option>
		<option value="Outgoing" <?php echo $Direction=='Outgoing'?'selected':''?>>Outgoing</option>
	</select>&nbsp;
	<span id="justify" style="display:<?php echo $Direction=='Outgoing'?'inline':'none'?>;">
		Justifying link: <span id="justifyinglink"><?php echo q("SELECT FoundOnPage FROM cmsb_links WHERE ID='$WarrantingLink'", O_VALUE);?></span> <?php
		if($WarrantingLinkStatus){
			echo " ($WarrantingLinkStatus)";
		}
		?>
	</span>
	<br />
	Status: 
	<select name="Status" id="Status" onChange="dChge(this);" disabled="<?php $Direction=='Outgoing'?1:0?>">
		<option value="">&nbsp;&nbsp;</option>
		<?php foreach($statusTypes as $v){?>
		<option value="<?php echo $v?>" <?php echo $Status==$v?'selected':''?>><?php echo $v?></option>
		<?php }?>
	</select>&nbsp;&nbsp;<?php echo $StatusComments;?>
	<br />
	Type:
	<select name="Type" id="Type" onChange="dChge(this);">
		<option value="">&lt;Select..&gt;</option>
		<?php foreach($linkTypes as $v){?>
		<option value="<?php echo $v?>" <?php echo $Type==$v?'selected':''?>><?php echo $v?></option>
		<?php }?>
	</select>
	<br />
	Client:
	<select name="Clients_ID" id="Clients_ID" onChange="dChge(this);">
		<option value="">&nbsp;&nbsp;</option>
		<?php foreach($clients=q("SELECT ID, CompanyName, PrimaryFirstName, PrimaryLastName FROM finan_clients WHERE Active=1 ORDER BY CompanyName, PrimaryLastName, PrimaryFirstName", O_ARRAY) as $v){?>
		<option value="<?php echo $v['ID']?>" <?php echo $Clients_ID==$v['ID']?'selected':''?>><?php 
		echo $v['CompanyName'] . ($v['CompanyName'] && $v['PrimaryLastName'] ? ' - ' : '') . $v['PrimaryLastName'] . ($v['PrimaryLastName']? ', ' : '').$v['PrimaryFirstName'];
		?></option>
		<?php }?>
	</select>
	<br />
	Link found on page:<br />
	<textarea name="FoundOnPage" id="FoundOnPage" cols="60" rows="2" onChange="dChge(this);"><?php echo h($FoundOnPage)?></textarea><a href="#" onClick="return ow(this.previousSibling.value,'l1_link','800,600');" title="Click the view this page">[View]</a>
	<br />
	Link directs to page:<br />
	<textarea name="LinkToPage" id="LinkToPage" cols="60" rows="2" onChange="dChge(this);"><?php echo h($LinkToPage)?></textarea><a href="#" onClick="return ow(this.previousSibling.value,'l1_link','800,600');" title="Click the view this page">[View]</a>
	<br />
	Comments:<br />
	<textarea name="Comments" id="Comments" cols="60" rows="2" onChange="dChge(this);"><?php echo h($Comments)?></textarea>
	<br />
	<br />
		
</div>
<!-- UnusableWithFormEndEditable --></div>
</form>
<div id="footer" class="objectWrapper"><!-- UnusableWithFormBeginEditable name="footer" -->


<!-- UnusableWithFormEndEditable --></div>
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
<!-- UnusableWithFormEnd --></html><?php
page_end();
?>