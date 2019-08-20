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

require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
?><script id="jsglobal" language="JavaScript" type="text/javascript" src="../Library/js/global_04_i1.js"></script>
<script id="jscommon" language="JavaScript" type="text/javascript" src="../Library/js/common_04_i1.js"></script>
<script id="jsforms" language="JavaScript" type="text/javascript" src="../Library/js/forms_04_i1.js"></script>
<script id="jsloader" language="JavaScript" type="text/javascript" src="../Library/js/loader_04_i1.js"></script>
<script id="jscontextmenu" language="JavaScript" type="text/javascript" src="../Library/js/contextmenus_04_i1.js"></script>
<script id="jsdataobjects" language="JavaScript" type="text/javascript" src="../Library/js/dataobjects_04_i1.js"></script>
<script id="3rdpartyfckeditor" type="text/javascript" src="../Library/fck6/fckeditor.js"></script>
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
</script><?php
//require('../console/resources/baislogin_00_includes.php');
//require('../console/systeam/php/auth_i2_v100.php');
$qx['defCnxMethod']=C_MASTER;

$hideCtrlSection=false;
//-------------------------- end coding -------------------------------


if($dir){
	//get the record, we assume it's PRESENT and the Priority is unique
	$r=q("SELECT * FROM re1_properties_imagesort WHERE Handle='$Handle' AND Priority=$idx", O_ARRAY);
	if(count($r)!==1){
		mail($developerEmail,'bad sort present in file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
	}else{
		$r=$r[1];
	}
	//trade places
	q("UPDATE re1_properties_imagesort SET Priority=$idx WHERE Handle='$Handle' AND Priority=$idx + $dir");
	q("UPDATE re1_properties_imagesort SET Priority=$idx + $dir WHERE Handle='$Handle' AND Name='".addslashes($r['Name'])."' AND Priority=$idx");
	header('Location: focus_properties_order2.php?Properties_ID='.$Properties_ID);
	exit;
}

//------------------------ Navbuttons head coding v1.41 -----------------------------
//change these first vars and the queries for each instance
$object='Properties_ID';
$recordPKField='ID'; //primary key field
$navObject='Properties_ID';
$updateMode='updateFeaturedProperty';
$insertMode='insertFeaturedProperty';
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
$denyNextToNew=true;
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
	if($a=q("SELECT * FROM re1_properties WHERE ID='".$$object."'",O_ROW)){
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
?>
<body>
<form id="form1" name="form1" target="w2" method="post" action="resources/bais_01_exe.php" onSubmit="return beginSubmit();">
<div id="header">
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
		if($mode==$insertMode){
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
		<h2 class = "h2_1"><?php echo $companyName?></h2> 
	</div>
</div>
<div id="mainBody">
	<h2>Order Pictures for Property <?php echo $Handle?></h2>
	<p>&nbsp;</p>
	<p>&nbsp; </p>
	<?php
	if(!function_exists('get_file_assets')) require($FUNCTION_ROOT.'/function_get_file_assets_v100.php');
	if($physicalFiles=get_file_assets($_SERVER['DOCUMENT_ROOT'].'/images/slides/'.$Handle.'/','large')){
		foreach($physicalFiles as $n=>$v){
			$p=q("SELECT Priority FROM re1_properties_imagesort WHERE Handle='$Handle' AND Name='".addslashes($n)."'", O_VALUE);
			$b[$n]=array($v['name'],($p ? $p : 1));
		}
	}else{
		$b=array();
		q("DELETE FROM re1_properties_imagesort WHERE Handle='$Handle'");
	}
	$maxIdx=q("SELECT MAX(Priority) FROM re1_properties_imagesort WHERE Handle='$Handle'", O_VALUE);
	//add any rows for imgs they added
	$addedRows=false;
	if($physicalFiles){
		foreach($physicalFiles as $v){
			if(!q("SELECT * FROM re1_properties_imagesort WHERE Handle='$Handle' AND Name='".addslashes($v['name'])."'", O_ROW)){
				$maxIdx++;
				$addedRows=true;
				q("REPLACE INTO re1_properties_imagesort SET Handle='$Handle', Name='".addslashes($v['name'])."', Priority=$maxIdx");
			}
		}
	}
	//refresh
	if($addedRows){
		
	}
	//now get keys from db
	if($a=q("SELECT LCASE(Name) AS NameKey, Name as NameValue, Priority FROM re1_properties_imagesort WHERE Handle='$Handle' ORDER BY Priority", O_ARRAY_ASSOC)){
		$i=0;
		foreach($a as $n=>$v){
			if(!$b[$n]){
				//if they have deleted an image since db write
				q("DELETE FROM re1_properties_imagesort WHERE Handle='$Handle' AND Name='".$n."'");
				continue;
			}
		}
	}else{
		//set natural order
		$i=0;
		foreach($b as $n=>$v){
			$i++;
			q("REPLACE INTO re1_properties_imagesort SET Handle='$Handle', Priority=$i, Name='".addslashes($v[0])."'");
			$b[$n][1]=$i;
		}
	}
	if(!function_exists('subkey_sort')) require($FUNCTION_ROOT.'/function_array_subkey_sort_v300.php');
	$b=subkey_sort($b,1);
	//now print table
	?>
	<table>
	  <?php
	$i=0;
	foreach($b as $n=>$v){
		$i++;
		?><tr>
	    <td><?php if($i>1){ ?>
	      <input type="button" name="Button" value="up" onClick="window.location='focus_properties_order2.php?Properties_ID=<?php echo $Properties_ID?>&dir=-1&Handle=<?php echo $Handle?>&idx=<?php echo $v[1]?>';" />
	      <?php } ?></td>
		    <td><?php if($i<count($b)){ ?>
			    <input type="button" name="Button" value="down" onClick="window.location='focus_properties_order2.php?Properties_ID=<?php echo $Properties_ID?>&dir=1&Handle=<?php echo $Handle?>&idx=<?php echo $v[1]?>';" />
			    <?php }?></td>
		    <td><a title="view full-sized image" href="../images/slides/<?php echo $Handle?>/<?php echo $v[0]?>" onclick="return ow(this.href,'l1_img','700,700');"><img src="../images/slides/<?php echo $Handle?>/.thumbs.dbr/<?php echo $v[0]?>" /></td></td>
		    <td><?php echo $v[0]?></td>
		  </tr><?php
	}
	?>
	  </table>
</div>
<div id="footer"></div>
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
</html><?php
//this function can vary and may flush the document 
function_exists('page_end') ? page_end() : mail($developerEmail,'page end function not declared', 'File: '.__FILE__.', line: '.__LINE__,'From: '.$hdrBugs01);
?>