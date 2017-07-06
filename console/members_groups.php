<?php 
//identify this script/GUI
$localSys['scriptGroup']='';
$localSys['scriptID']='members-groups';
$localSys['scriptVersion']='1.0';
$localSys['pageType']='Properties Window';



//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');
//for hidden fields
$updateMode=$mode='addClientGroup';
$recordPKField='ID'; //primary key field



if(($ObjectName=='addr_contacts') || $Contacts_ID){
	if(!$Contacts_ID)exit('Contact mode with no contacts_id');

	if(!($member=q("SELECT 
	'Contact' AS Type, '' AS CompanyName, '' AS ClientName, 1 AS ResourceType
	c.FirstName, c.MiddleName, c.LastName, c.HomeAddress, c.HomeCity, c.HomeState, c.HomeZip, c.HomeCountry,c.Email,c.NewsletterOK,c.Active FROM addr_contacts c WHERE c.ID=$Contacts_ID", O_ROW)))exit('Unable to find member by that Contacts_ID');
	$groups=q("SELECT cg.Groups_ID, g.Name FROM addr_ContactsGroups cg JOIN addr_groups g ON cg.Groups_ID=g.ID WHERE cg.Contacts_DataSource='addr_contacts' AND cg.Contacts_ID=$Contacts_ID", O_COL_ASSOC);
	$object='Contacts_ID';
	$ObjectName='addr_contacts';
	$navObject='Contacts_ID';
}else if(($ObjectName=='finan_clients') || $Clients_ID){
	if(!$Clients_ID)exit('Client mode with no clients_id');
	if(!($member=q("SELECT 
	'Client' AS Type, cl.CompanyName, cl.ClientName, cl.ResourceType,
	c.FirstName, c.MiddleName, c.LastName, c.HomeAddress, c.HomeCity, c.HomeState, c.HomeZip, c.HomeCountry,c.Email,c.NewsletterOK,c.Active			
	FROM addr_contacts c RIGHT JOIN finan_ClientsContacts cc ON c.ID=cc.Contacts_ID AND cc.Type='Primary' RIGHT JOIN finan_clients cl ON cc.Clients_ID=cl.ID WHERE cl.ID=$Clients_ID", O_ROW)))exit('Unable to find member by that Clients_ID');
	$groups=q("SELECT cg.Groups_ID, g.Name FROM addr_ContactsGroups cg JOIN addr_groups g ON cg.Groups_ID=g.ID WHERE cg.Contacts_DataSource='finan_clients' AND cg.Contacts_ID=$Clients_ID", O_COL_ASSOC);
	$object='Clients_ID';
	$ObjectName='finan_clients';
	$navObject='Clients_ID';
}else{
	exit('not enough information to get client/contact');
}

ob_start();
if($a=q("SHOW CREATE TABLE addr_ContactsGroups", O_ROW)){
	$a=$a['Create Table'];
	preg_match('/Ver\.([0-9]+)\.[0-9]+/i',$a,$m);
	if($m[1]<2){
		//backup table contents, email to me, drop and reload the table
		if(q("SELECT COUNT(*) FROM addr_ContactsGroups", O_VALUE)){
			$out=q("SELECT * FROM addr_ContactsGroups", O_ARRAY);
			mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err='old ContactsGroups table value'),$fromHdrBugs);
			unset($err);
		}
		q("DROP TABLE addr_ContactsGroups");
		//will remediate
		q("SHOW CREATE TABLE addr_ContactsGroups", O_ROW);
		$alter=true;
	}
}else{
	//no action; should remediate
	$alter=true;
}
if($alter)q("ALTER TABLE `addr_ContactsGroups` CHANGE `Creator` `Creator` CHAR( 30 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '$acct'");
$out=ob_get_contents();
ob_end_clean();

$hideCtrlSection=false;
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/properties_04_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Manage Member Groups</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" href="/console/rbrfm_admin.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<style type="text/css">
</style>

<script language="javascript" type="text/javascript" src="/Library/js/jquery.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/global_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/common_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/forms_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/loader_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/contextmenus_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/dataobjects_04_i1.js"></script>
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
var isEscapable=2;
var isDeletable=1;
var isModal=1;
var talks=1; //whether this page broadcasts overall state changes which other pages listen for
var listens=0;
AddOnkeypressCommand('PropKeyPress(e)'); //if not declared already
</script>

<style type="text/css">
body{
	background-color:#CCC;
	}
.objectWrapper {
	background-color:#CCC;
	min-height:400px;
	margin:0px 15px;
	}
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
		<input id="Previous" type="button" name="Submit" value="Previous" onClick="focus_nav(-1, '<?php echo ($mode==$insertMode?'insert':'update')?>', <?php echo $mode==$insertMode?1:0?>, 0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $nullAbs<=1?'disabled':''?>>
		<?php
		//Handle display of all buttons besides the Previous button
		if($mode==$insertMode){
			if($insertType==2 /** advanced mode **/){
				//save
				?><input id="Save" type="button" name="Save" value="Save" onClick="focus_nav(0,'insert',1,2<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveInitiallyDisabled?> /><?php
			}
			//save and new - common to both modes
			?><input id="SaveAndNew" type="button" name="SaveAndNew" value="Save &amp; New" onClick="focus_nav(0,'insert', 1,1<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveAndNewInitiallyDisabled?> /><?php
			if($insertType==1 /** basic mode **/){
				//save and close
				?><input id="SaveAndClose" type="button" name="SaveAndClose" value="Save &amp; Close" onClick="focus_nav(0,'insert', 1,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveAndCloseInitiallyDisabled?> /><?php
			}
			?><input id="CancelInsert" type="button" name="CancelInsert" value="Cancel" onClick="focus_nav_cxl('insert');" /><?php
		}else{
			//OK, and appropriate [next] button
			?><input id="OK" type="button" name="ActionOK" value="OK" onClick="focus_nav(0,'<?php echo $mode==$insertMode?'insert':'update'?>',0,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" />
			<input id="Next" type="button" name="Next" value="Next" onClick="focus_nav(1,'<?php echo $mode==$insertMode?'insert':'update'?>',0,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $nullAbs>$nullCount || ($denyNextToNew && $nullAbs==$nullCount) ?'disabled':''?> /><?php
		}
		// *note that we could go back to the same page the 'New Record' click appeared on, but there's major issues programmatically on whether it would shift becuase of the placement of the new record.
		// *note that the primary key field is now included here to save time
		?>
		<input name="<?php echo $recordPKField?>" type="hidden" id="<?php echo $recordPKField?>" value="<?php echo $$object;?>" />
		<input name="navVer" type="hidden" id="navVer" value="<?php echo $navVer?>" />
		<input name="navObject" type="hidden" id="navObject" value="<?php echo $navObject?>" />
		<input name="nav" type="hidden" id="nav" />
		<input name="navMode" type="hidden" id="navMode" value="" />
		<input name="count" type="hidden" id="count" value="<?php echo $nullCount?>" />
		<input name="abs" type="hidden" id="abs" value="<?php echo $nullAbs?>" />
		<input name="insertMode" type="hidden" id="insertMode" value="<?php echo $insertMode?>" />
		<input name="updateMode" type="hidden" id="updateMode" value="<?php echo $updateMode?>" />
		<input name="mode" type="hidden" id="mode" value="<?php echo $mode?>" />
		<input name="ObjectName" type="hidden" id="ObjectName" value="<?php echo $ObjectName?>" />
		<input name="componentID" type="hidden" id="componentID" value="<?php echo $localSys['componentID']?>" />
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
						?><input name="<?php echo $n?>" id="<?php echo $n?>" type="hidden" value="<?php echo stripslashes($v)?>" /><?php
						echo "\n";
					}
				}
			}
		}
		?>
		</div>
	</div>
<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->

<h2>Manage Member Groups</h2>
<?php
if($member['Type']=='Company'){
	echo $member['FirstName'] . ' ' . $member['LastName'] . '<br />';
	if($member['HomeAddress'])echo $member['HomeAddress'] . '<br />';
	if($member['HomeCity'])echo $member['HomeCity'] . ', ' . $member['HomeState'] . '&nbsp;'.$member['HomeZip'].'<br />';

}else{
	//as individual
	echo $member['FirstName'] . ' ' . $member['LastName'] . '<br />';
	if($member['HomeAddress'])echo $member['HomeAddress'] . '<br />';
	if($member['HomeCity'])echo $member['HomeCity'] . ', ' . $member['HomeState'] . '&nbsp;'.$member['HomeZip'].'<br />';
}
?>


<div class="objectWrapper">
	<?php
	if($groupList=q("SELECT g.ID, g.Name, COUNT(cg.Contacts_ID) AS Members
	FROM addr_groups g LEFT JOIN addr_ContactsGroups cg ON g.ID=cg.Groups_ID GROUP BY g.ID ORDER BY IF(COUNT(cg.Contacts_ID)>0,1,2), g.Name", O_ARRAY)){
		foreach($groupList as $v)if($groups[$v['ID']])$selected[]=$v['Name'];
	}
	?>
	Hold down the control key to select multiple groups <br />
	Currently selected:<br />
	<div id="groupList" style="border:1px solid darkred;padding:15px;">
	<?php echo count($selected) ? implode(', ',$selected) : '<em>(none selected)</em>';?>
	</div>
	<script language="javascript" type="text/javascript">
	function showGroups(){
		var labels=new Array();
		//see how many groups they have
		groups=0;
		if(browser=='IE'){
			var opts=g('Groups').options.length;
			for(var m=0; m<opts; m++){
				if(g('Groups').options[m].selected){
					groups++;
					labels[labels.length]=g('Groups').options[m].label;
				}
			}
		}else{
			for(var m in g('Groups').options){
				if(g('Groups').options[m].selected){
					groups++;
					labels[labels.length]=g('Groups').options[m].label;
				}
			}
		}
		var txt=labels.join(', ');
		g('groupList').innerHTML=(txt ? txt : '<em>(none selected)</em>');
		g('updateGroups').disabled=false;
	}
	</script>
	<select name="Groups[]" size="15" multiple="multiple" id="Groups" onChange="dChge(this);showGroups();">
		<option value="" class="gray">(none)</option>
		<?php
		if(count($groupList))
		foreach($groupList as $v){
			if($v['Members']>0 && !$hasMembers){
				$hasMembers=true;
				?><optgroup label="Used"><?php
			}else if($v['Members']==0 && !$hasNoMembers){
				$hasNoMembers=true;
				if($hasMembers){
					?></optgroup><?php
				}
				?><optgroup label="Not Used"><?php
			}
			?><option value="<?php echo $v['ID'];?>" <?php echo $groups[$v['ID']] ? 'selected':'';?>><?php echo $v['Name'];?><?php echo $v['Members']?'('.$v['Members'].')':''?></option><?php
		}
		if($hasMembers || $hasNoMembers){
			?></optgroup><?php
		}
		?>
	</select>
	<br />
	Add additional group/category (use with care!): 
	<input name="NewGroup" type="text" id="NewGroup" onChange="dChge(this);showGroups();" />
	<br />
	<input id="updateGroups" disabled type="submit" name="Submit" value="<?php echo count($selected) ? 'Update Groups' : 'Add Groups'?>" />
	<input type="button" name="Button" value="Cancel" onClick="window.close();" />
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