<?php
/* -------------------------
NAV BUTTONS
- This button set came from /home/rbase/lib/devteam/php/snippets
- Now used in a bunch of RelateBase interfaces and also client components. Useful for interfaces where sub-records are present and being worked on.


Things to do to install this button set:
#1. install contents of the div tag (btns140)

#2. install the pre-coding above the document, change as needed to connect to the specific table(s) or get the resource in a different way

#3. must declare the following vars in javascript:
var thispage='mypage.php';
var thisfolder='myfolder';
var count='[php:echo $nullCount]';
var ab='[php:echo $nullAbs]';
// ---------- new with 1.42: ----------
AddOnkeypressCommand('PropKeyPress(e)'); //if not declared already
var isDeletable=true; //required to fire off
var customDeleteHandler='deleteAlbum()'; //optional; default submits to bais_01_exe.php?mode=(deleteMode)
function deleteAlbum(){
	//write custom delete mode here
}
var isEscapable=1|2; //1 means confirm if detectChange, 2 means escape regardless of changes
#4. need js functions focus_nav() and focus_nav_cxl() in place

#5. delete this comment tag..

-------------------------------- */

/* 
to do
-----
on dChge=1, navigating, we need a ync vs. the confirm feature - doesn't fit



change log
----------
1.43 - 2009-09-10
-----------------
* ability to set a default button as a SUBMIT so that enter triggers the button
* ability to show or hide buttons
* added navButton_A as a class to all buttons


1.42 - 2009-08-19
-----------------
* ability to press escape and close window, if isEscapable=1+
* ability to press ctrl-D and delete record
* added variable and hidden field deleteMode


1.3 added ability to call a function to add querystring parameters to Back and Next buttons
also added ability to shut out Next -> New for when inserts aren't wanted

1.2 differs from v1.10 in insert mode.  We have a newMode, "remain", which is "save and stay here" - this presents a few issues ITO what has to change after success.  The buttons, instead of Save and New, Save and Close, and Cancel are going to be Save, Save and New, and Cancel - functionally equivalent, 2 clicks required for save and close only difference.

Additionally, maybe not part of this coding but "OK" should also have two meanings and eventually may need two buttons with one being cancel.

Make sure that you use the updated version of focus_nav().
A hidden field updateMode has been added after new object successfully submitted on navMode=remain


Delete these comments after buttons are installed..
GOTCHAS:
1. make sure that if you're looking for a subset of id's the where clause is consistent in both sql queries
2. make sure the sort order is consistent with any list mode they came from so that the starting and ending record make sense
3. be aware that if they are coming in from an exe page after having made a change, next and previous nav could be somewhat erratic.

The receiving exe page should have this code to handle navMode=='navig':
	if($navMode=='navig'){
		?><script>
		window.parent.location='thisfile.php?navMode=navig&nav=<?php echo $nav?>&count=<?php echo q("SELECT COUNT(*) FROM finan_invoices WHERE Accounts_ID='$Accounts_ID'",O_VALUE);?>&abs=<?php echo $nullAbs;?>';
		</script><?php
		exit;
	}
4. when you have no records in the set and are doing save and new, the Previous button is disabled.  If you then use clear_form() and don't reload the page from the exe window, then you'll also need to change nullCount.value, nullAbs.value, and enable the Previous button - clicking it from that point will then take the user to the last record entered
5. Make sure to change the insertType value to 2 if you're using the more advanced interface features!
6. On submit with remain mode, we have basically inserted a record, and it could be "anywhere" in the record at the point based on sort vs. its values.  The buttons however don't change, and exe will write the new value of the primary key field; Save & New will now mean an update, and navigate to the next new position in the recordset.  Point here is, navMode=insert means not that I'm inserting a record but "what to do afterward"
**/

//------------------------ Navbuttons head coding v1.43 -----------------------------
//change these first vars and the queries for each instance
$object='Assns_ID';
$recordPKField='ID'; //primary key field
$navObject='Assns_ID';
$updateMode='updateAssn';
$insertMode='insertAssn';
$deleteMode='deleteAssn';
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
$ids=q("SELECT ID FROM sma_assns WHERE ResourceType IS NOT NULL ORDER BY Name",O_COL);
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
if(strlen($$object) || $Assns_ID=q("SELECT ID FROM sma_assns WHERE ResourceToken!='' AND ResourceToken='$ResourceToken' AND ResourceType IS NOT NULL", O_VALUE)){
	//get the record for the object
	if($a=q("SELECT * FROM sma_assns WHERE ID='".$$object."'",O_ROW)){
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
	$Assns_ID=$ID=quasi_resource_generic($MASTER_DATABASE, 'sma_assns', $ResourceToken, $typeField='ResourceType', $sessionKeyField='sessionKey', $resourceTokenField='ResourceToken', $primary='ID', $creatorField='Creator', $createDateField='CreateDate' /*, C_DEFAULT, $options */);

	$nullAbs=$nullCount+1; //where we actually are right then
}
//--------------------------- end coding --------------------------------

?><div id="btns140" class="fr"><?php
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