<?php 
//identify this script/GUI
$localSys['scriptGroup']='';
$localSys['scriptID']='view-clients-contacts';
$localSys['scriptVersion']='4.0';
$localSys['componentID']='main';
$localSys['pageType']='Properties Window';
$localSys['pageLevel']=1;



//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');
$qx['defCnxMethod']=C_MASTER;
$qx['useRemediation']=true;
$qx['tableList']=array_merge($qx['tableList'], array('addr_contacts','finan_clients','finan_clients_statuses','finan_ClientsContacts'));


//mini-settings for this page
$dataset='Member';



/*
-- section #2: this should be abstracted for specific purposes --
customFieldsPrefixes
$customFields is declared in ../private/config.php
*/

//for this page
$custom = [
    'finan_clients' => 'a',
    'addr_contacts' => 'c',
];

//get all custom fields
$localCustomFields = [];
$localCustomFieldsString = '';
foreach($custom as $table => $alias){
    //we gather these fields broadly
    $fields = q('EXPLAIN ' . $table, O_ARRAY);
    foreach($fields as $field){
        $idx = $field['Field'];
        if(preg_match('/^([A-Z]+)_([a-zA-Z0-9]+)$/', $idx, $a)){
            //custom field
            $localCustomFields[$idx] = array_merge($field, ['alias' => $alias]);
            if(!empty($customFields[$table][$idx])){
                $localCustomFields[$idx] = array_merge($localCustomFields[$idx], $customFields[$table][$idx]);
            }
            $localCustomFieldsString .= ', ' . $localCustomFields[$idx]['alias'] . '.`' . $idx . '`';
        }
    }
}


//------------------------ Navbuttons head coding v1.41 -----------------------------
//change these first vars and the queries for each instance
$object='Clients_ID';
$recordPKField='ID'; //primary key field
$navObject='Clients_ID';
$updateMode='updateClient';
$insertMode='insertClient';
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
$ids=q("SELECT a.ID FROM finan_clients a LEFT JOIN finan_ClientsContacts b ON a.ID=b.Clients_ID AND b.Type='Primary' LEFT JOIN addr_contacts c ON b.Contacts_ID=c.ID WHERE (a.Active=1 AND a.ResourceType IS NOT NULL ) OR a.ID='$$object' ORDER BY c.LastName, c.FirstName, a.ClientName", O_COL);
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
if(strlen($$object) || $$object=q("SELECT ID 
    FROM finan_clients 
    WHERE 
    ResourceToken!='' AND 
    ResourceToken='$ResourceToken' AND 
    ResourceType IS NOT NULL", O_VALUE)){
	//get the record for the object
    $sql = "SELECT 
		a.*, c.Title, c.FirstName, c.MiddleName, c.LastName, c.Suffix, b.Contacts_ID, 
		c.HomePhone, c.HomeMobile, c.UserName, c.Email AS PersonalEmail, 
		c.HomeAddress, c.HomeCity, c.HomeState, c.HomeZip, c.HomeCountry, c.WholesaleAccess,
		BusAddress, BusCity, BusState, BusZip, Company, BusCountry,
		HomePhone, BusPhone, BusFax,
		c.Spouse, c.Gender, c.Birthday, c.Anniversary, c.Children,
		c.Notes, c.Salesreps_ID, c.PasswordMD5, c.EnrollmentAuthToken, c.EnrollmentAuthDuration,
		c.NewsletterOK
		" . $localCustomFieldsString . "
		FROM 
		finan_clients a LEFT JOIN finan_ClientsContacts b ON a.ID=b.Clients_ID AND b.Type='Primary'
		LEFT JOIN addr_contacts c ON b.Contacts_ID=c.ID
		WHERE a.ID=$Clients_ID";
	if($record = q($sql, O_ROW)){
		$mode=$updateMode;
		$bufferClients_ID=$record['Clients_ID']; //parent object heirarchy in table
		unset($record['Clients_ID']);
		@extract($record);
	}else{
		//object may have been deleted by another user, least perplexing approach is to present insert mode
		$mode=$insertMode;
		unset($$object);
		$$object=$ID=quasi_resource_generic($MASTER_DATABASE, 'finan_clients', $ResourceToken);
		$nullAbs=$nullCount+1;
	}
}else{
	$mode=$insertMode;
	$$object=$ID=quasi_resource_generic($MASTER_DATABASE, 'finan_clients', $ResourceToken);
	$nullAbs=$nullCount+1; //where we actually are right then
}
//--------------------------- end coding --------------------------------

$states=q("SELECT st_code, st_name FROM aux_states", O_COL_ASSOC, $public_cnx);
$countries=q("SELECT ct_code, ct_name FROM aux_countries", O_COL_ASSOC, $public_cnx);

$hideCtrlSection=false;

$PageTitle=($mode==$updateMode?'Update member information':'Add new member/customer');
$AutoCreatePassword=true;

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/reports_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo dynamic_title($PageTitle.' - '.$AcctCompanyName);?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->


<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" href="rbrfm_admin.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/data_04_i1.css" type="text/css" />
<link rel="stylesheet" href="/Library/ckeditor_3.4/_samples/sample.css" type="text/css" />
<style type="text/css">
#headerBar1{
	padding:5px 10px 10px 12px; 
	background-color:#CCC;
	}
</style>

<script language="JavaScript" type="text/javascript" src="/Library/js/jquery.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/global_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/common_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/forms_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/loader_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/dataobjects_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/ckeditor_3.4/ckeditor.js"></script>
<script language="JavaScript" type="text/javascript">
/* periwinkle coding */
var thispage='<?php echo $thispage?>';
var thisfolder='<?php echo $thisfolder?>';
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

<?php
//-- section #3: default values --
if($a=$defaultValues[$dataset]){
	foreach($a as $n=>$v)echo 'sets[\''.$n.'\']=\''.str_replace("'","\'",$v).'\';'."\n";
}
?>

$(document).ready(function(){
	$('#ClientName').change(function(){
		if(g('CompanyName').value=='')g('CompanyName').value=g('ClientName').value;
	});
	$('#AutoCreatePassword').change(function(){
		g('AutoCreatePassword').checked ? (g('Password').disabled=true) && (g('Password2').disabled=true) : (g('Password').disabled=false) || (g('Password2').disabled=false);
	});
});
function copyAddr(n){
	var a={'Address1':'Address', 'Address2':'Address2', 'City':'City', 'State':'State', 'Zip':'Zip', 'Country':'Country'};
	for(var i in a){
		b=(n==1?'Shipping'+a[i]:i);
		c=(n==1?i:'Shipping'+a[i]);
		g(b).value=g(c).value;
	}
}
</script>
<!-- InstanceEndEditable -->
</head>

<body id="report">
<?php if(!$suppressForm){ ?>
<form action="resources/bais_01_exe.php" method="post" enctype="multipart/form-data" name="form1" target="w2" id="form1" onSubmit="return beginSubmit();">
<?php }?>
<div id="header"><!-- InstanceBeginEditable name="top_nav" -->
	<div id="headerBar1">
		<div id="btns140" class="fr">
		<!--
		Navbuttons version 1.41. Last edited 2008-01-21.
		This button set came from devteam/php/snippets
		Now used in a bunch of RelateBase interfaces and also client components. Useful for interfaces where sub-records are present and being worked on.
		-->
		<input id="Previous" type="button" name="Submit" value="Previous" onclick="focus_nav(-1, '<?php echo ($mode==$insertMode?'insert':'update')?>', <?php echo $mode==$insertMode?1:0?>, 0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $nullAbs<=1?'disabled':''?>>
		<?php
		//Handle display of all buttons besides the Previous button
		if($mode==$insertMode){
			if($insertType==2 /** advanced mode **/){
				//save
				?><input id="Save" type="button" name="Save" value="Save" onclick="focus_nav(0,'insert',1,2<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveInitiallyDisabled?> /><?php
			}
			//save and new - common to both modes
			?><input id="SaveAndNew" type="button" name="SaveAndNew" value="Save &amp; New" onclick="focus_nav(0,'insert', 1,1<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveAndNewInitiallyDisabled?> /><?php
			if($insertType==1 /** basic mode **/){
				//save and close
				?><input id="SaveAndClose" type="button" name="SaveAndClose" value="Save &amp; Close" onclick="focus_nav(0,'insert', 1,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveAndCloseInitiallyDisabled?> /><?php
			}
			?><input id="CancelInsert" type="button" name="CancelInsert" value="Cancel" onclick="focus_nav_cxl('insert');" /><?php
		}else{
			//OK, and appropriate [next] button
			?><input id="OK" type="button" name="ActionOK" value="OK" onclick="focus_nav(0,'<?php echo $mode==$insertMode?'insert':'update'?>',0,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" />
			<input id="Next" type="button" name="Next" value="Next" onclick="focus_nav(1,'<?php echo $mode==$insertMode?'insert':'update'?>',0,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $nullAbs>$nullCount || ($denyNextToNew && $nullAbs==$nullCount) ?'disabled':''?> /><?php
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
		?>
		</div>
		<h2 class="nullTop nullBottom">Members</h2>
	</div>
<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->



<a name="topAnchor"></a>
<div class="fr">
<input type="hidden" name="Active" value="0" />
<label><input type="checkbox" name="Active" id="Active" <?php echo $mode==$insertMode || $Active ? 'checked' : ''?> value="1" onchange="dChge(this)" /> Active record </label>
</div>
<?php
//---- section #4: very unique to this page tabDisposition ---
if($tabDisposition['memberMain']['company']==CONTACTS_COMPANY){ //--------- show name header here --------
?>
Salutation (Mr./Mrs.): 
<input name="Title" type="text" id="Title" onchange="dChge(this);" value="<?php echo h($Title)?>" size="6" />
<br />
First name:
<input name="FirstName" type="text" id="FirstName" onchange="dChge(this);" value="<?php echo h($FirstName)?>" />
&nbsp;
Middle:
<input name="MiddleName" type="text" id="MiddleName" onchange="dChge(this);" value="<?php echo h($MiddleName)?>" size="7" />
&nbsp;
Last name:
<input name="LastName" type="text" id="LastName" onchange="dChge(this);" value="<?php echo h($LastName)?>" />
<?php }else{ ?>
Customer Name: 
<input name="ClientName" type="text" id="ClientName" value="<?php echo h($ClientName)?>" size="45" maxlength="75" onchange="dChge(this);" />
<?php }?>

<br />
<!-- 2012-10-31 statuses needs to be sunsetted; I see no reason for its use
<?php if(false){ // ?>
Status: 
<select name="Statuses_ID" id="Statuses_ID" onchange="dChge(this);" >
	<option value="">&lt;Select..&gt;</option>
	<?php
	if(!($a=q("SELECT ID, Name FROM finan_clients_statuses", O_COL_ASSOC))){
		q("INSERT INTO finan_clients_statuses SELECT * FROM relatebase_template.finan_clients_statuses", array(
		$SUPER_MASTER_HOSTNAME, $SUPER_MASTER_USERNAME, $SUPER_MASTER_PASSWORD, $SUPER_MASTER_DATABASE
		));
		$a=q("SELECT ID, Name FROM finan_clients_statuses", O_COL_ASSOC);
	}
	foreach($a as $n=>$v){
		?><option value="<?php echo $n?>" <?php echo $n==$Statuses_ID?'selected':''?>><?php echo h($v)?></option><?php
	}
	?>
</select>
<?php } ?>
-->
<?php
ob_start(); //------------------ start first tab -------------------
?>
<label> <input type="checkbox" name="Inactive" id="Inactive" value="1" <?php echo $mode==$updateMode && !$Active ? 'checked' : ''?> onchange="dChge(this);" /> Inactive Client</label> (cannot log into website if checked)
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td>Full Company Name:<br /> 
			<input name="CompanyName" type="text" id="CompanyName" value="<?php echo h($CompanyName)?>" size="30" maxlength="75" onchange="dChge(this);" />
		<br /></td>
		<td><br />
			Phone:
			<input name="Phone" type="text" id="Phone" value="<?php echo h($Phone)?>" onchange="dChge(this);" />
			<br />
			Alt. Phone: 
			<input name="Phone2" type="text" id="Phone2" value="<?php echo h($Phone2)?>" onchange="dChge(this);" />
			<br />
			Fax:
			<input name="Fax" type="text" id="Fax" value="<?php echo h($Fax)?>" onchange="dChge(this);" />
			<br />
			Email: <input name="Email" type="text" id="Email" value="<?php echo h($Email)?>" onchange="dChge(this);" />
			<br />
			Email CC: 
		<input name="EmailCC" type="text" id="EmailCC" value="<?php echo h($EmailCC)?>" onchange="dChge(this);" /></td>
	</tr>
</table>
<fieldset>
<legend>Addresses</legend>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td>			<div class="fr" style="margin-right:7px;">
[&nbsp;<a href="javascript:copyAddr(1);">copy</a>>]<br />
				[&lt;<a href="javascript:copyAddr(-1);">copy</a>&nbsp;]
			</div>
		<strong>Mailing Address / Bill  To</strong><br />
			Addr1:
			<input name="Address1" type="text" id="Address1" value="<?php echo h($Address1)?>" onchange="dChge(this);" />
			<br />
			Addr2:
			<input name="Address2" type="text" id="Address2" value="<?php echo h($Address2)?>" onchange="dChge(this);" />
			<br />
			City:
			<input name="City" type="text" id="City" value="<?php echo h($City)?>" onchange="dChge(this);" />
			<br /> 
			State: 
			<select name="State" id="State" onChange="dChge(this);countryInterlock('State','State','Country');" style="width:125px;">
				<option value="" class="ghost"> &lt;Select..&gt; </option>
				<?php
				$gotState=false;
				foreach($states as $n=>$v){
					?><option value="<?php echo $n?>" <?php
					if($State==$n){
						$gotState=true;
						echo 'selected';
					}
					?>><?php echo h($v)?></option><?php
				}
				if(!$gotState && $State!=''){
					?><option value="<?php echo h($State)?>" style="background-color:tomato;" selected><?php echo $State?></option><?php
				}
				?>
			</select>
			Zip:
			<input name="Zip" type="text" id="Zip" onchange="dChge(this);" value="<?php echo h($Zip)?>" size="7" />
			<br />
			Country: 
			<select name="Country" id="Country" onChange="dChge(this);countryInterlock('Country','State','Country');" style="width:175px;">
				<option value="" class="ghost"> &lt;Select..&gt; </option>
				<?php
				$gotCountry=false;
				foreach($countries as $n=>$v){
					?><option value="<?php echo $n?>" <?php
					if($Country==$n){
						$gotCountry=true;
						echo 'selected';
					}
					?>><?php echo h($v)?></option><?php
				}
				if(!$gotCountry && $Country!=''){
					?><option value="<?php echo h($Country)?>" style="background-color:tomato;" selected><?php echo $Country?></option><?php
				}
				?>
			</select>	  </td>
		<td>
			<strong>Physical Address / Ship To</strong><br />
			Addr1:
			<input name="ShippingAddress" type="text" id="ShippingAddress" value="<?php echo h($ShippingAddress)?>" onchange="dChge(this);" />
			<br />
			Addr2:
			<input name="ShippingAddress2" type="text" id="ShippingAddress2" value="<?php echo h($ShippingAddress2)?>" onchange="dChge(this);" />
			<br />
			City:
			<input name="ShippingCity" type="text" id="ShippingCity" value="<?php echo h($ShippingCity)?>" onchange="dChge(this);" />
			<br />
			State:
			<select name="ShippingState" id="ShippingState" onchange="dChge(this);countryInterlock('ShippingState','ShippingState','ShippingCountry');" style="width:125px;">
			<option value="" class="ghost"> &lt;Select..&gt; </option>
			<?php
			$gotState=false;
			foreach($states as $n=>$v){
				?><option value="<?php echo $n?>" <?php
				if($State==$n){
					$gotState=true;
					echo 'selected';
				}
				?>><?php echo h($v)?></option><?php
			}
			if(!$gotState && $ShippingState!=''){
				?><option value="<?php echo h($ShippingState)?>" style="background-color:tomato;" selected="selected"><?php echo $ShippingState?></option><?php
			}
			?>
			</select>
			Zip:
			<input name="ShippingZip" type="text" id="ShippingZip" onchange="dChge(this);" value="<?php echo h($ShippingZip)?>" size="7" />
			<br />
			Country: 
			<select name="ShippingCountry" id="ShippingCountry" onChange="dChge(this);countryInterlock('ShippingCountry','ShippingState','ShippingCountry');" style="width:175px;">
				<option value="" class="ghost"> &lt;Select..&gt; </option>
				<?php
				$gotCountry=false;
				foreach($countries as $n=>$v){
					?><option value="<?php echo $n?>" <?php
					if($ShippingCountry==$n){
						$gotCountry=true;
						echo 'selected';
					}
					?>><?php echo h($v)?></option><?php
				}
				if(!$gotCountry && $ShippingCountry!=''){
					?><option value="<?php echo h($ShippingCountry)?>" style="background-color:tomato;" selected><?php echo $ShippingCountry?></option><?php
				}
				?>
			</select>		</td>
	</tr>
</table>
</fieldset>
<?php
get_contents_tabsection('company');
?>
<div style="float:right;"><strong>Personal Address</strong><br />
	Address:
	<input name="HomeAddress" type="text" id="HomeAddress" value="<?php echo h($HomeAddress)?>" onchange="dChge(this);" />
	<br />
	City:
	<input name="HomeCity" type="text" id="HomeCity" value="<?php echo h($HomeCity)?>" onchange="dChge(this);" />
	<br />
	State:
	<select name="HomeState" id="HomeState" onchange="dChge(this);countryInterlock('HomeState','HomeState','HomeCountry');" style="width:125px;">
		<option value="" class="ghost"> &lt;Select..&gt; </option>
		<?php
		$gotState=false;
		foreach($states as $n=>$v){
			?><option value="<?php echo $n?>" <?php
			if($HomeState==$n || ($mode==$insertMode && $defaultValues[$dataset]['HomeState']==$n)){
				$gotState=true;
				echo 'selected';
			}
			?>><?php echo h($v)?></option><?php
		}
		if(!$gotState && $HomeState!=''){
			?><option value="<?php echo h($HomeState)?>" style="background-color:tomato;" selected="selected"><?php echo $HomeState?></option><?php
		}
		?>
	</select>
	Zip:
	<input name="HomeZip" type="text" id="HomeZip" onchange="dChge(this);" value="<?php echo h($HomeZip)?>" size="7" />
	<br />
	Country: 
	<select name="HomeCountry" id="HomeCountry" onChange="dChge(this);countryInterlock('HomeCountry','HomeState','HomeCountry');" style="width:175px;">
		<option value="" class="ghost"> &lt;Select..&gt; </option>
		<?php
		$gotCountry=false;
		foreach($countries as $n=>$v){
			?><option value="<?php echo $n?>" <?php
			if($HomeCountry==$n){
				$gotCountry=true;
				echo 'selected';
			}
			?>><?php echo h($v)?></option><?php
		}
		if(!$gotCountry && $HomeCountry!=''){
			?><option value="<?php echo h($HomeCountry)?>" style="background-color:tomato;" selected><?php echo $HomeCountry?></option><?php
		}
		?>
	</select>
	<br />
	<br />
	<strong>Business Address</strong><br />
	Company:
	<input name="Company" type="text" id="Company" value="<?php echo h($Company)?>" onchange="dChge(this);" />
	<br />
	Address:
	<input name="BusAddress" type="text" id="BusAddress" value="<?php echo h($BusAddress)?>" onchange="dChge(this);" />
	<br />
	City:
	<input name="BusCity" type="text" id="BusCity" value="<?php echo h($BusCity)?>" onchange="dChge(this);" />
	<br />
	State:
	<select name="BusState" id="BusState" onchange="dChge(this);countryInterlock('BusState','BusState','BusCountry');" style="width:125px;">
		<option value="" class="ghost"> &lt;Select..&gt; </option>
		<?php
		$gotState=false;
		foreach($states as $n=>$v){
			?><option value="<?php echo $n?>" <?php
			if($BusState==$n || ($mode==$insertMode && $defaultValues[$dataset]['BusState']==$n)){
				$gotState=true;
				echo 'selected';
			}
			?>><?php echo h($v)?></option><?php
		}
		if(!$gotState && $BusState!=''){
			?><option value="<?php echo h($BusState)?>" style="background-color:tomato;" selected="selected"><?php echo $BusState?></option><?php
		}
		?>
	</select>
	Zip:
	<input name="BusZip" type="text" id="BusZip" onchange="dChge(this);" value="<?php echo h($BusZip)?>" size="7" />
	<br />
	Country: 
	<select name="BusCountry" id="BusCountry" onChange="dChge(this);countryInterlock('BusCountry','BusState','BusCountry');" style="width:175px;">
		<option value="" class="ghost"> &lt;Select..&gt; </option>
		<?php
		$gotCountry=false;
		foreach($countries as $n=>$v){
			?><option value="<?php echo $n?>" <?php
			if($BusCountry==$n){
				$gotCountry=true;
				echo 'selected';
			}
			?>><?php echo h($v)?></option><?php
		}
		if(!$gotCountry && $BusCountry!=''){
			?><option value="<?php echo h($BusCountry)?>" style="background-color:tomato;" selected><?php echo $BusCountry?></option><?php
		}
		?>
	</select>
</div>
<input name="Contacts_ID" type="hidden" id="Contacts_ID" value="<?php echo $Contacts_ID?>" />
<?php
if($tabDisposition['memberMain']['company']!==CONTACTS_COMPANY){ //--------- show name header here --------
?>
First name:
<input name="FirstName" type="text" id="FirstName" onchange="dChge(this);" value="<?php echo h($FirstName)?>" />
<br />
Middle:
<input name="MiddleName" type="text" id="MiddleName" onchange="dChge(this);" value="<?php echo h($MiddleName)?>" size="7" />
<br />
Last name:
<input name="LastName" type="text" id="LastName" onchange="dChge(this);" value="<?php echo h($LastName)?>" />

<?php }?>
<br />
<div style="width:265px; <?php echo $EnrollmentAuthToken?'background-color:papayawhip; padding:5px;':''?>">
Personal Email:
<input name="PersonalEmail" type="text" id="PersonalEmail" value="<?php echo h($PersonalEmail)?>" onchange="dChge(this);" />
<br />
<input type="hidden" name="PersonalEmailVerified" value="0" />
<label>
<input name="PersonalEmailVerified" type="checkbox" id="PersonalEmailVerified" value="1" onchange="if(!this.checked && !confirm('This will require that they follow their email link to verify their email before they can sign in.  Continue?')){ this.checked=true; return false; }dChge(this);" <?php if(!$EnrollmentAuthToken)echo 'checked';?> /> 
Has been verified 
</label>
<br /></div>
Relationship: 
<select name="Relationship" id="Relationship">
	<option value="Primary">Primary</option>
</select>
<br />
Salutation (Mr./Mrs.): 
<input name="Title" type="text" id="Title" onchange="dChge(this);" value="<?php echo h($Title)?>" size="6" />
<br />


Mobile: 
<input name="HomeMobile" type="text" id="HomeMobile" value="<?php echo h($HomeMobile)?>" onchange="dChge(this);" />
<br />
Home Phone: 
<input name="HomePhone" type="text" id="HomePhone" value="<?php echo h($HomePhone)?>" onchange="dChge(this);" />
<br />
Business Phone:
<input name="BusPhone" type="text" id="BusPhone" value="<?php echo h($BusPhone)?>" onchange="dChge(this);" />
<br />
Fax:
<input name="BusFax" type="text" id="BusFax" value="<?php echo h($BusFax)?>" onchange="dChge(this);" />
<br />
<br />
<?php
if($MASTER_DATABASE=='cpm104' || $MASTER_DATABASE=='cpm104_dev'){
	?>
	<select name="Salesreps_ID" id="Salesreps_ID" onchange="dChge(this);">
	<option value="">&lt; Select.. &gt;</option>
	<?php
	foreach(q("SELECT a.Contacts_ID, CONCAT(RepCode,' - ',FirstName, ' ' ,LastName) FROM finan_salesreps a LEFT JOIN addr_contacts b ON a.Contacts_ID=b.ID", O_COL_ASSOC) as $n=>$v){
		?><option value="<?php echo $n?>" <?php echo $Salesreps_ID==$n?'selected':''?>><?php echo h($v);?></option><?php
	}
	?>
	</select>
<?php
}
if($moduleConfig['1.0']['addons'][1] /* 1=memberPrivileges */){
	/*
	2009-10-17: genericized permissions from addr_ContactsAccess if called for in moduleConfig
	*/
	?><fieldset style="margin:10px 15px 0px 0px;"><legend>Permissions</legend>
	<?php
	if($permissions=q("SELECT a.ID, a.Name, a.Description, COUNT(b.Contacts_ID) AS HasAccess
	FROM addr_access a LEFT JOIN addr_ContactsAccess b ON a.ID=b.Access_ID AND b.Contacts_ID='$Contacts_ID'
	WHERE a.ID>2
	GROUP BY a.ID", O_ARRAY_ASSOC))
	foreach($permissions as $n=>$v){
		?>
		<label title="<?php echo h($v['Description']);?>"><input type="checkbox" name="Accesses[<?php echo $n?>]" id="Accesses[<?php echo $n?>]" value="1" onchange="dChge(this);" <?php echo $v['HasAccess']?'checked':''?> <?php if($n==2)echo 'disabled';?> /> <?php echo h($v['Name'])?></label> <br />
		<?php
	}
	?></fieldset><?php
}
?>
<?php
get_contents_tabsection('contact');
?>
Spouse name:
<input name="Spouse" type="text" id="Spouse" value="<?php echo $Spouse?>" onchange="dChge(this);" />
<br />
Children:
<input name="Children" type="text" id="Children" onchange="dChge(this);" value="<?php echo $Children?>" size="25" />
<br />
General Notes:<br />
<textarea name="Notes" cols="55" rows="4" id="Notes" onchange="dChge(this);"><?php echo h($Notes)?></textarea><br />


<?php
if(!empty($localCustomFields)){
    ?><h3>Custom Fields</h3><?php
    /* ?><table><?php */
    foreach($localCustomFields as $field => $v){
        preg_match('/^([a-z]+)(\([^)]+\))*/', $v['Type'], $a);
        $type = $a[1];
        $params = trim($a[2], '()');

        /* echo "\n";
        ?><tr><?php */

        //current value
        $current = $$field;
        if(preg_match('/int/', $type)){
            $current = (int) $current;
        }

        //default value
        if($v['default_value']){
            $default = $v['default'];
        }else if($v['Default']){
            $default = $v['Default'];
        }else{
            $default = ''; //this has problems in the case of date or numeric field type
        }

        //label
        if($v['label']){
            $label = $v['label'];
        }else{
            $label = preg_replace('/([a-z])([A-Z])/','$1 $2',preg_replace('/^[A-Z]+_/','',$field));
        }
            /*
            echo "\n";
            ?><td>
            <?php */
            echo $label . ': ';
            /* ?>
            </td>
            <td><?php */

        if($v['form_element'] === 'select' || $type === 'enum'){
            //options
            if(!empty($v['values'])){
                $options = $v['values'];
            }else{
                $options = trim(preg_replace('/^enum\(/', '', $v['Type']), '()');
                $rand = md5(time());
                $options=preg_replace('/(^\')|(\'$)/','', str_replace("\\'", $rand, $options));
                $options=explode("','",$options);
                foreach($options as $o => $w) $options[$o] = str_replace($rand, "'", $w);
            }

            ?>
            <select name="<?php echo $field;?>" id="<?php echo $field;?>" onchange="dChge(this);">
                <option value="">&lt;Select..&gt;</option>
                <?php
                if(!in_array($current, $options)){
                    //unlisted value
                    ?><option value="<?php echo $current;?>" selected><?php echo $current; ?></option><?php
                }
                foreach($options as $w){
                    ?><option value="<?php echo h($w);?>" <?php if($w === $current) echo 'selected';?>><?php
                    echo h($w);
                    ?></option><?php
                }
                ?>
            </select>
            <?php
        }else if($v['form_element'] === 'textarea' || $type == 'text' || $type == 'longtext'){
            //textarea
            ?>
            <br />
            <textarea name="<?php echo $v['Field']?>" id="<?php echo $v['Field']?>" cols="45" rows="4" onchange="dChge(this);"><?php echo h($current);?></textarea>
            <?php
        }else{
            ?>
            <input name="<?php echo $field?>" type="text" id="<?php echo $field?>" value="<?php
            if($type === 'time'){
                echo t($current, f_t);
            }else if($type === 'date' || $type === 'datetime'){
                echo t($current);
            }else{
                echo h($current);
            }
            ?>" <?php if($type=='char' || $type=='varchar'){ ?>maxlength="<?php echo $params?>"<?php }?> onchange="dChge(this);" />
            <?php
        }
        if(!empty($v['comment'])){
            ?> &nbsp; <span class="gray">(<?php echo $v['comment'];?>)</span> <?php
        }
        echo "\n";
        ?><br /><?php

            /* echo "\n";
            ?></td><?php */

        /* echo "\n";
        ?></tr><?php */

    }
    /* echo "\n";
    ?></table><?php */
}
?>
<?php
get_contents_tabsection('memberinfo');
?>
<p>Sales Quotes Pulled - History here</p>
<p>NO CURRENT HISTORY</p>
<?php
get_contents_tabsection('activity');
?>
<?php
$website=q("SELECT * FROM finan_clients_websites WHERE ID='$Clients_ID' AND PageName='default'", O_ROW);
?>
<!--
<strong>NOTE</strong>: image path = images/sites/<?php echo $mode==$insertMode ? '[username]' : $UserName?><br />
-->
<!--
[<a href="#" onclick="if(<?php echo $mode==$updateMode?'true':'false'?>){ opened = window.open('../MemberFocus.php?ID=<?php echo $Clients_ID?>&UserName=<?php echo $UserName?>','memberpagepreview'); opened.focus(); }else{ alert('You must first save this new member record before viewing it'); } return false;">View member page on website</a>]
<br />
[<a href="#" onclick="if(<?php echo $mode==$updateMode?'false':'true'?>){ alert('You must first save this new member record before working with pictures'); return false; } return ow('../admin/file_explorer/?uid=members&amp;folder=sites/<?php echo $UserName; ?>&amp;createFolder=1&view=fullfolder','l1_imglibrary','700,700');">Manage images for this member..</a>]
-->
<br />

<textarea cols="80" id="Content" name="Content" rows="10"><?php
//this is easy
$Content=q("SELECT Content FROM finan_clients_websites WHERE ID=$Clients_ID", O_VALUE);
echo h(trim($Content) ? $Content : '<p></p>');
?></textarea>
<script type="text/javascript">
var editor = CKEDITOR.replace( 'Content' );
setTimeout('CheckDirty(\'Content\')',1000);
</script>

<?php
get_contents_tabsection('webpage');
?>
<input type="hidden" name="PageActive" value="0" />
<label>
<input name="PageActive" onchange="dChge(this);" type="checkbox" id="PageActive" value="1" <?php echo !isset($PageActive) || $PageActive==1 ? 'checked' : ''?> /> 
Web Page is active</label><br />
Web Page: <input name="WebPage" type="text" id="WebPage" onchange="dChge(this);" value="<?php echo h($WebPage)?>" size="45" />
<br />
Contact Page: <input name="ContactPage" type="text" id="ContactPage" onchange="dChge(this);" value="<?php echo h($ContactPage)?>" size="45" />
<br />
Landing Page: <input name="LandingPage" type="text" id="LandingPage" onchange="dChge(this);" value="<?php echo h($LandingPage)?>" size="45" /><br />

Show business card information: 
<select name="ShowCard" id="ShowCard" onchange="dChge(this);">
	<option <?php echo $website['ShowCard']=='top'?'selected':''?> value="top">At top</option>
	<option <?php echo $website['ShowCard']=='bottom'?'selected':''?> value="bottom">At bottom</option>
	<option <?php echo $website['ShowCard']=='right'?'selected':''?> value="right">Floated to the right</option>
	<option <?php echo $website['ShowCard']=='none'?'selected':''?> value="none">(do not show business card)</option>
</select><br />
META Title for Website (255 Chars):<br />
<input name="MetaTitle" type="text" id="MetaTitle" value="<?php echo h($MetaTitle)?>" size="65" maxlength="255" onchange="dChge(this);" /> 
<br />
Brief Description of Company (255 Chars, goes in META Description):<br />
<textarea name="Description" cols="65" rows="2" id="Description" onchange="dChge(this);"><?php echo h($Description)?></textarea> 
<br />
Keywords (Separate by comma, goes in META Keywords):<br /> 
<textarea name="Keywords" cols="65" rows="3" id="Keywords" onchange="dChge(this);"><?php echo h($Keywords)?></textarea>
<?php
get_contents_tabsection('webfields');
?>
<div style="float:right;">
<?php if($mode==$updateMode){ ?>
<input type="button" name="Submit" value="Change Password.." onClick="return ow('change_password.php?un_username=<?php echo $UserName?>','l2_changepassword','600,700');" style="width:140px;">
<?php }else{ ?>

<label><input type="checkbox" name="AutoCreatePassword"  id="AutoCreatePassword" <?php if($AutoCreatePassword)echo 'checked';?> /> Auto-create password</label><br />
Password: <input name="Password" type="password" id="Password" onchange="dChge(this);" <?php echo $AutoCreatePassword?'disabled':''?> />
<br />
(Confirm): <input name="Password2" type="password" id="Password2" onchange="dChge(this);" <?php echo $AutoCreatePassword?'disabled':''?> />
<br />	
<?php } ?>
</div>
<?php if($mode==$updateMode){ ?>UserName: <?php echo $UserName;?><br /><?php } ?>
Newsletter receipt status (general):
<select name="NewsletterOK" id="NewsletterOK" onchange="dChge(this);">
<option value="">&lt;Select..&gt;</option>
<option value="0" <?php echo $NewsletterOK==0?'selected':''?>>(unspecified)</option>
<option value="1" <?php echo $NewsletterOK==1?'selected':''?>>Opted out voluntarily</option>
<option value="2" <?php echo $NewsletterOK==2?'selected':''?>>Opted out involuntarily (removed from any mailing)</option>
<option value="3" <?php echo $NewsletterOK==3?'selected':''?>>Opted IN involuntarily (as part of signup)</option>
<option value="4" <?php echo $NewsletterOK==4?'selected':''?>>Opted IN voluntarily</option>	
</select><br />
<br />

<?php require('components/comp_02_clientgroups_v100.php')?>

<br />
<h3>Access:</h3>
<?php
//2013-07-08: added system accesses
$a=q("SELECT a.*, ca.Contacts_ID FROM addr_access a LEFT JOIN addr_ContactsAccess ca ON a.ID=ca.Access_ID AND ca.Contacts_ID='$Contacts_ID' WHERE Category='{system}' AND Name!='Superadmin'", O_ARRAY);
?><table>
<?php
foreach($a as $n=>$v){
    //if ($v['Name'] == "Season Pass") error_alert($v['ID']);
	?><label><input name="access[<?php echo $v['ID'];?>]" type="checkbox" id="access[]" value="<?php echo $v['ID'];?>" onchange="dChge(this);" <?php if($v['Contacts_ID'])echo 'checked'; ?> />
	<?php echo $v['Name'];?></label> <br />
	<?php
}
?>
</table>
<br />
Reseller Status: 
<select name="WholesaleAccess" id="WholesaleAccess" onchange="dChge(this);" >
	<option value="8" <?php echo $WholesaleAccess==8?'selected':''?>>Approved Dealer</option>
	<option value="4" <?php echo $WholesaleAccess==4?'selected':''?>>Pending Dealer Account</option>
	<option value="2" <?php echo $WholesaleAccess==2?'selected':''?> style="color:TOMATO;">Rejected Dealer Account</option>
	<option value="0" <?php echo $WholesaleAccess==0?'selected':''?>>(Not a dealer - newsletter only)</option>
</select>
<?php
get_contents_tabsection('settings');
?>
<a href="#" onclick="if(!UserName){alert('You must first save this record'); return false; } return ow('/admin/file_explorer/?uid=manageimgs&folder=sites/'+UserName+'&createFolder=1','l1_manageimgs','700,700');">View and Add Images</a>
<?php
get_contents_tabsection('images');
$adminMode=1;
CMSB('helpsection');
get_contents_tabsection('help');

tabs_enhanced(
	array(
		'company'=>array(
			'label'=>'Company',
		),
		'contact'=>array(
			'label'=>'Contact',
		),
		'memberinfo'=>array(
			'label'=>'Member Info',
		),
		'activity'=>array(
			'label'=>'Activity',
		),
		'webpage'=>array(
			'label'=>'Web Page',
		),
		'webfields'=>array(
			'label'=>'Web Fields',
		),
		'settings'=>array(
			'label'=>'Settings',
		),
		'images'=>array(
			'label'=>'Images',
		),
		'help'=>array(
			'label'=>'Help',
		),
	) 
	/*
	array(
		'location'=>'bottom',
		'aColor'=>'royalblue',
		'brdColor'=>'#aaa',
	)
	*/
);

?>

<!-- InstanceEndEditable --></div>
<div id="footer"><!-- InstanceBeginEditable name="footer" -->
&nbsp;&nbsp;
<!-- InstanceEndEditable --></div>
<?php if(!$suppressForm){ ?>
</form>
<?php }?>
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
<!-- InstanceEnd --></html><?php page_end();?>