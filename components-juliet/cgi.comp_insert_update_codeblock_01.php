<?php
/*
2007-01-25: handles most of the aspects of usemod through 2.8 - purpose of this code is to get a clean insert/update into addr_contacts, and return the ID if inserting
must receive $mode=insert|update

*/
#PRIVACY POLICY
if($mode=='insert' && $usemod['PrivacyPolicyTermsField'] && !$_POST[$usemod['PrivacyPolicyTermsField']]){
	error_alert('You must agree to the Privacy Policy to create an account');
}
#EMAIL
if($usemod['checkUniqueEmail']>($mode=='insert' ? EMAIL_CHECK_NONE : EMAIL_CHECK_NEW)){
	$and=($mode=='insert' ? '' : " AND ID!='".$_SESSION['cnx'][$cnxKey]['primaryKeyValue']."'");
	if($emID=q("SELECT ID FROM addr_contacts WHERE Email = '$Email' $and",O_VALUE)){
		error_alert('Please select a unique email address; the email address you have selected is in use.');
	}
}
#USER NAME - as of v2.8 this was set by query against primaryKeyValue for this node
if($mode=='insert'){
	if($usemod['autoGenerateUsername']){
		$UserName=strtolower(
			substr(preg_replace('/[^a-z]*/i','',$_POST['FirstName']),0,1).
			substr(preg_replace('/[^a-z]*/i','',$_POST['LastName']),0,16)
		);
		//use 2.32 or greater
		$sql_autoinc_options=array('pad'=>2, 'cnx'=>array($MASTER_HOSTNAME,$MASTER_USERNAME,$MASTER_PASSWORD,$MASTER_DATABASE));
		$UserName=sql_autoinc_text($MASTER_DATABASE.'._v_usernames','UserName',$UserName, $sql_autoinc_options);
		if($usemod['integrateWithFinancial'] && 
			$finID = q("SELECT ID FROM finan_clients WHERE UserName='$UserName'",O_VALUE)){//Not sure if I should change this for Foster In Texas - Parker
			//username has been assigned in one and not the other, very rare case - go with finan_clients and hope for the best
			$UserName = $_POST['UserName'] = 
			sql_autoinc_text($MASTER_DATABASE.'._v_usernames','UserName',$UserName,$sql_autoinc_options);
			ob_start();
			print_r($GLOBALS);
			$err=ob_get_contents();
			ob_end_clean();
			mail($developerEmail,'usernames in addr_contacts and finan_clients out of whack',$err,'From: bugreports@'.$siteDomain);
		}
	}else if(strlen($_POST['UserName'])){
		$UserName=strtolower($_POST['UserName']);
		$ln=__LINE__+4;
		if(!preg_match('/^[a-z0-9]{4,20}$/i',$UserName)){
			$unErr=true;
			$msg='Your user name must contain only letters and numbers, and can only be 4-20 characters';
		}else if($unid=q("SELECT ID FROM addr_contacts WHERE UserName='".$UserName."'".($mode=='update' ? " AND ID!='".$_POST['ID']."'": ''), O_VALUE)){
			$unErr=true;
			$msg='Sorry, the user name '.$UserName.' is already taken; please select a different user name.  \nIf you are '.$UserName.', please sign in.';
		}
		if($unErr)error_alert($msg);
	}else{
		error_alert('You must create a unique username.\nYour user name must contain only letters and numbers, and can only be 4-20 characters');
	}
}
#PASSWORD
if($mode=='insert'){
	if($usemod['autoGeneratePassword']){
		//create a password for them
		$plainTextPassword = /* $_POST['Password'] = */ substr(md5(rand(0,1000).time()),0,8);
	}else{
		$plainTextPassword = stripslashes($Password); //should come from Post normally
	}
	$PasswordMD5=md5(stripslashes($plainTextPassword));
	$Password=''; //blank out password in this case
}else{
	//we do not include password on an update
	unset($Password, $PasswordMD5, $_POST['Password'], $_POST['PasswordMD5']);
}
#WHOLESALE ACCESS
if($usemod['wholesaleToken'] && isset($_POST['WholesaleAccess']) && $usemod['presentWholesaleFields']>=4){
	mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals('here is an example of where we need a gettable_variable - the value of the label for MY ACCOUNT or NEW ACCOUNT'),$fromHdrBugs);
	if((!$_POST['WholesaleNumber'] || !$_POST['WholesaleState']) && !$usemod['wholesaleValuesNotNeeded']){
		if($usemod['presentWholesaleFields']){
			$msg='\\'.'n'.'\\'.'n'.'If you are not applying for a '.strtolower($umResellerWord).' account, go to the home page, click on My Account or New Account, and apply without checking the Retail Option';
		}
		error_alert('In order to create a '.strtolower($umResellerWord).' account, you must include your '.strtolower($umResellerWord).' number or ID, and the state issuing this number or ID'.$msg);
	}
	$wholesaleFields=array(
		'WholesaleAccess'=>'ALTER TABLE `'.$MASTER_DATABASE.'`.`addr_contacts` 
			ADD `WholesaleAccess` TINYINT(1) UNSIGNED DEFAULT "0" NOT NULL', /**  AFTER `BusWebsite` **/
		'WholesaleNumber'=>'ALTER TABLE `'.$MASTER_DATABASE.'`.`addr_contacts` 
			ADD `WholesaleNumber` CHAR( 30 ) NOT NULL AFTER `WholesaleAccess`',
		'WholesaleState'=>'ALTER TABLE `'.$MASTER_DATABASE.'`.`addr_contacts` 
			ADD `WholesaleState` CHAR( 3 ) NOT NULL AFTER `WholesaleNumber`',
		'WholesaleNotes'=>'ALTER TABLE `'.$MASTER_DATABASE.'`.`addr_contacts` 
			ADD `WholesaleNotes` TEXT AFTER `WholesaleState` '
	);
	if(!$mysql_declare_field_attributes_rtcs[$acct]['addr_contacts']){
		//get fields for addr_contacts
		$a=mysql_declare_field_attributes_rtcs($MASTER_DATABASE, 'addr_contacts');
		foreach($wholesaleFields as $n=>$v){
			if($a[strtolower($n)]){
				//OK - we have the field
			}else{
				$sql=$v;
				if($n=='WholesaleAccess' && $a['buswebsite']){
					$sql.=' AFTER BusWebsite';
				}
				q($sql, C_MASTER);
			}
		}
	}
	if($usemod['allowImmediateWholesaleAccess'] || 
	  ($usemod['allowAdminMode'] && $adminMode && $_POST['_AutoApproveWholesale'])
	){
		$WholesaleAccess=WHSLE_APPROVED;
	}else{
		$WholesaleAccess=WHSLE_PENDING;
	}
}else{
	//security, unset these
	unset($_POST['WholesaleAccess'], $_POST['WholesaleNumber'], $_POST['WholesaleState'], $_POST['WholesaleNotes']);
}
//for enrollment authorizations
if(
	$mode=='insert' && 
	(intval($usemod['useEnrollmentConfirmation']) >= (isset($_POST['WholesaleAccess']) ? 1 : 2) && 
	!(strlen($usemod['EEATOverride']) && $EEATOverride== md5(strtolower(trim($Email)). $usemod['EEATOverride']))) &&
	!($adminMode && $_POST['_AutoVerifyEEAT'])
	){
	$EnrollmentAuthToken=md5($PasswordMD5 . $usemod['EnrollmentConfirmationEncryptor']);
	$EnrollmentAuthDuration=$usemod['EnrollmentAuthDuration'];
}
#SPLIT/JOIN FIELDS
if($usemod['parseOptionFields']){
	foreach($usemod['parseOptionFields'] as $v){
		if(!is_array($_POST[$v]))continue;
		unset($implodeArray);
		foreach($_POST[$v] as $o=>$w) $implodeArray[]=$o;
		//reset parseOptionField to a string
		$_POST[$v]=implode(',',$implodeArray);
	}
}
//NON-PASSED VALUES - normally unchecked checkboxes
if(is_array($usemod['nonPassedFieldNames']) && count($usemod['nonPassedFieldNames'])){
	foreach($usemod['nonPassedFieldNames'] as $v){
		$w=strtolower($v);
		//security measure
		if($w=='password' || $w=='username' || $w=='email' || $w=='passwordmd5')continue;
		//set to default value=0
		if(!isset($_POST[$v]))$$v=$_POST[$v]='0';
	}
}
//declare the creator as signed-in user
if($_SESSION['cnx'][$cnxKey]){
	$Creator=$_SESSION['systemUserName'].'.ctc:'.$_SESSION['cnx'][$cnxKey]['primaryKeyValue'];
}else{
	//we assume they are creating their own record unless proxy
	$Creator=($_REQUEST['proxy'] ? $UN : $UserName);
}
//username is NOT PASSED on an update
if($mode=='update')unset($UserName);
$un_firstname=$FirstName;
$un_middlname=$MiddleName;
$un_lastname=$LastName;
$un_username=$UserName;
$un_password=$PasswordMD5;
$un_email=$Email;

$sql=sql_insert_update_generic($MASTER_DATABASE, 'addr_contacts', $mode);

if($mode=='insert'){
	$ln=__LINE__+1;
	$Contacts_ID = q($sql, C_MASTER, O_INSERTID);
}else{
	//we have Contacts_ID
	$ln=__LINE__+1;
	q($sql, C_MASTER);
	$Contacts_ID=$_SESSION['cnx'][$cnxKey]['primaryKeyValue'];
}
if(!$Contacts_ID){
	mail($developerEmail,'error file '. __FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
	error_alert('Abnormal error, unable to locate Contacts_ID value; staff have been notified.  Please try your entry again.');
}
prn($qr);
?>