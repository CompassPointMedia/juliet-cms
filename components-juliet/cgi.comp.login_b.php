<?php
/**
2007-12-30
-----------
adjusted to make password and passwordMD5 more solid - we assume passwords coming in are with slashes
2007-05-30
--------------

2007-01-26
--------------
This is used in 2.8 and higher.
Main change is checking finan_ClientsContacts and seeing if they have ONE join.  The priority is:
LOGIN_SINGLE_INITIAL	single record - look for any type
LOGIN_SINGLE_PRIMARY	single record - look for type=Primary
LOGIN_MULTI_ONEPRIMARY	multiple records - look for one with type=Primary
-----
LOGIN_MULTI_INITIAL	multiple records - but no type=Primary
LOGIN_MULTI_PRIMARY	multiple records - multiple with type=Primary

we cannot use the latter three as they are indefinite, and in fact there are difficulties faced in working with the first (from a standpoint of permissions) and third (from a standpoint of indefiniteness)



2006-02-23
--------------
This coding is also in the usemod install version 2.7 and higher; make sure it remains in synch.
login snippet initially copied from A_v100; this deals with login from either relatebase-cms.com or from any usemod out there, meaning that the variables need to be present in $usemod=array() OR we use the account name

RelateBase Account [___________]  Data Source: [______________[^]]
Your User Name [_____________]
Your Password [_____________]


Password verification: from now on (2006-02-23) we're going to assume that the password field will be encrypted in MD5 format and will reside in a field called PasswordMD5 - though Password may still be there.  We want to move this direction for security purposes, and the "forgot password" section will need to set a temporary password, flag this, and they must reset their first login.  The lost password will never be known if using a secure connection; this is what we want.
**/
$loginOK=false;
//first, how are we going to connect
if($MASTER_USERNAME && strlen($MASTER_PASSWORD)){
	//we are in a usemod environment
	/***
	for now we are presuming that the status of the account owning the database they are loggin into is OK.  In the future we need to add this layer so that if the account is suspended the guest cannot log in
	
	***/
	$accountIsOK=true;
	if($accountIsOK){
		//connection for q()
		$v280cnx=array($MASTER_HOSTNAME,$MASTER_USERNAME,$MASTER_PASSWORD,$MASTER_DATABASE);
	}else{
		//we need to determine if the action(s) the user will be performing, weighted by the status of the account, will allow the user to log in.  3 conditions are login, login with a caution, and do not log in.  We need to notify the user and also the account owner for cases 2 and 3.
	}
}else{
	mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err='this is not used'),$fromHdrBugs);
	error_alert($err.', developer has been notified');

	//get information on account connection using master database string
	$sql="SELECT Password FROM relatebase_dev.rbase_userbase WHERE UserName='$UN'";
	$mst_cnx=mysqli_connect($MASTER_HOSTNAME, $MASTER_USERNAME, $MASTER_PASSWORD);
	mysqli_select_db($mst_cnx, $MASTER_DATABASE);
	$result=mysqli_query($mst_cnx, $sql) or die(mysqli_error($mst_cnx));
	$r=mysqli_fetch_array($result, MYSQLI_ASSOC);
	mysqli_close($mst_cnx);
	//connection for q()
	$v280cnx=array('localhost',$database, $r['Password'], $database);
}
$table='addr_contacts';
//Password Verification - default is use md5 for password field
$passwordField= 'PasswordMD5';
$useMD5=true;

//2006-10-17: need to handle table joins where specified for enhanced data availability

//get the user record without the password; UN value passed must be non-blank and non-zero
$fl=__FILE__;$ln=__LINE__+1;
$rd=q("SELECT * FROM $table WHERE 
/** enforce username not blank **/ '$UN'!='' AND 
(UserName='$UN' OR Email='$UN')".
($usemod['additionalGeneralWhereClause']?' AND '.preg_replace('/^\s*AND\s+/i','',$usemod['additionalGeneralWhereClause']):''), O_ROW, $v280cnx);
unset($loginCode);
$setLoginVars=false;

if($qr['count']>1 && !$usemod['allowLoginForDuplicateUserTokens']){
	//abnormal error, owner needs notified eventually and the user needs to know there's an abError here so they don't waste further login
	mail($developerEmail,'Duplicate record match on login for '.$MASTER_DATABASE,
	'File: '.__FILE__."\n".'Line: '.__LINE__."\n\n\n".
	"During a login by user $UN into database ".$MASTER_DATABASE.", table $table, more than one record with this value in field UserName (or Email) was present and the user was not allowed to log in.  This is due to the fact that this field(s) have not been set with a unique index.  Please contact the database administrator immediately"	,
	$usemod['errFromHdr']);
	$loginCode=-2;
}else if(!$rd /** username doesnt exist **/){
	//that username or email does not exist
	$loginCode=0;
}else if($usemod['useEnrollmentConfirmation'] && (
	!(is_null($rd['EnrollmentAuthToken']) || isset($rd['EnrollmentAuthToken'])) || 
	!isset($rd['EnrollmentAuthDuration']))){
	//abnormal error, would be good to synchronize the table here - actually earlier would be better
	mail($developerEmail,'Missing Enrollment Authorization Token field(s) for '.$MASTER_DATABASE,
	'File: '.__FILE__."\n".'Line: '.__LINE__."\n\n\n".
	"During a login by user $UN into database ".$MASTER_DATABASE.", table $table appeared to be missing either the field EnrollmentAuthToken or EnrollmentAuthDuration.  Please contact the database administrator immediately"	,
	$usemod['errFromHdr']);
	$loginCode=-4;
}else if(strlen($rd['EnrollmentAuthToken']) /** record EAT present and non-blank **/){
	if($EnrollmentAuthToken /** user is passing an EAT **/){
		if(strtolower($EnrollmentAuthToken) !== strtolower($rd['EnrollmentAuthToken']) /** EAT not valid **/){
			//the link to enroll you automatically did not work
			$loginCode=15;
		}else if((time() - strtotime($rd['CreateDate']))/86400 > $rd['EnrollmentAuthDuration'] /** the enrollment period has expired **/){
			//your opportunity to enroll has expired - you will need to enroll again
			$loginCode=30;
			//unset this in session - no longer eligible
			unset($_SESSION['special'][$acct]['EEATUnverifiedID']);
		}else{
			//enroll them
			q("UPDATE $table SET EnrollmentAuthToken=NULL, Editor='".$MASTER_USERNAME."' WHERE ".$usemod['dbLoginFieldPrimary']."='".$rd[$usemod['dbLoginFieldPrimary']]."'",$v280cnx);

			//you have been successfully enrolled [1]
			$loginCode=100;
			$loginOK=true;
			if($usemod['signinAfterEnrollmentConfirmation'])$setLoginVars=true;

			//unset this in session - no longer unverified
			unset($_SESSION['special'][$acct]['EEATUnverifiedID']);
		}
	}else{
		//you must confirm your enrollment first [2]
		$loginCode=45;
	}
}else if(strlen($EnrollmentAuthToken) /** EAT is being passed **/){
	//you are already enrolled
	$loginCode=60;
	if($usemod['allowRepeatEnrollmentLinkToSignin']) $setLoginVars=true;

	//do this just in case
	unset($_SESSION['special'][$acct]['EEATUnverifiedID']);
}else if(strlen($PW)){
	//this is based on the assumption that nobody has a 32char password
	$originalPW=stripslashes($PW);
	if(!preg_match('/^[0-9a-f]{32}$/i',$PW)) $PW=md5(stripslashes($PW));
	if($originalPW==$MASTER_PASSWORD || $PW==$rd['PasswordMD5'] || ($usemod['masterGuestPassword']  && $PW==md5($usemod['masterGuestPassword'])) /** passwords match **/){
		$setLoginVars=true;
		//your login was successful
		$loginCode=200;
		$loginOK=true;
	}else{
		//your login was unsuccessful
		$loginCode=75;
	}
}
if($setLoginVars==true){
	//#1. set the root level information

	//the rule is that if session.identity is not there, nothing else will be; ALL RELATEBASE LOGINS set the identity in some fashion
	unset($b_login);
	$b_login['identity'] = 'Guest';
	$b_login['createDate'] = $rd['CreateDate'];
	$b_login['creator'] = $rd['Creator'];
	if(strtotime($rd['CreateDate'])!==strtotime($rd['EditDate'])){
		$b_login['editDate'] = $rd['EditDate'];
		if($rd['Editor'])$b_login['editor'] = $rd['Editor'];
	}
	$b_login['firstName'] = $rd['FirstName'];
	$b_login['middleName'] = $rd['MiddleName'];
	$b_login['lastName'] = $rd['LastName'];
	$b_login['email'] = $rd['Email'];
	$b_login['loginTime']=($dateStamp ? $dateStamp : date('Y-m-d H:i:s'));
	$b_login['sessionIP']=$_SERVER['REMOTE_ADDR'];
	$b_login['systemUserName']=$rd['UserName'];
	$b_login['sessionKey']=$_COOKIE['PHPSESSID'];
	if($postTime){
		$b_login['localTime'] = $postTime;
		$b_login['timeVariance'] = '-1'; //[expressed in seconds, positive=fast]
	}
	if($machineName){
		$b_login['machineName'] = $machineName;
	}else{
		$requestMachineName=true;
	}				

	//#2. add the connection
	if(!$overrideSetLoginVars) unset($_SESSION['cnx'][$cnxKey]);
	unset($a_login);

	//2017-07-24 SF - this was MASTER_DATABASE - we can no longer rely on the database being the account
	$a_login['acct'] = (!empty($cnxKey) ? $cnxKey : $acct);
	$a_login['acct_from'] = (!empty($cnxKey) ? 'cnxKey' : 'acct');
	$a_login['identity'] = 'Guest';
	$a_login['systemUserName'] = $UN;
	$a_login['primaryKeyField'] = 'ID';
		#[or Array [UserName, Field2]]
	$a_login['primaryKeyValue'] = $rd['ID'];
	/*
	20007-01-26: get joins with finan_clients and make available
	*/
	ob_start();
	$joins=q("SELECT a.Clients_ID, LCASE(a.Type) AS Type, b.ClientName FROM finan_ClientsContacts a, finan_clients b WHERE a.Clients_ID=b.ID AND a.Contacts_ID='".$a_login['primaryKeyValue']."'", O_ARRAY_ASSOC, ERR_ECHO, $v280cnx);
	$err=ob_get_contents();
	ob_end_clean();
	if($err){
		//mail administrator
		mail($developerEmail, 'error file '.__FILE__.', line '.__LINE__, get_globals(), $fromHdrBugs);
	}else if($joins){
		$primaries=0;
		foreach($joins as $n=>$v){
			$joinsAssoc[$n]=$v['Type'];
			if(strtolower($v['Type'])=='primary'){
				$defaultClients_ID=$n;
				$primaries++;
				continue;
			}
			if(!$defaultClients_ID)$defaultClients_ID=$n;
		}
		switch(true){
			case count($joins)==1:
				$financialRlx=($primaries==1 ? LOGIN_SINGLE_PRIMARY : LOGIN_SINGLE_INITIAL);
				$a_login['company']=$v['ClientName'];
			break;
			default:
				$financialRlx=($primaries==0 ? LOGIN_MULTI_INITIAL : $primaries==1 ? LOGIN_MULTI_ONEPRIMARY : LOGIN_MULTI_PRIMARY);
		}
		if(in_array($financialRlx, $usemod['acceptablePermissionTypes'])){
			$a_login['companyTableName']='finan_clients';
			$a_login['companyPrimaryKeyField']='ID';
			$a_login['companyPrimaryKeyValues']=$joinsAssoc;
			$a_login['defaultClients_ID']=$defaultClients_ID;			
		}else{
			mail($developerEmail, 'join type not acceptable, error file '.__FILE__.', line '.__LINE__, get_globals(), $fromHdrBugs);
		}
	}

	$a_login['firstName'] = $rd['FirstName'];
	$a_login['middleName'] = $rd['MiddleName'];
	$a_login['lastName'] = $rd['LastName'];
	$a_login['email']=$rd['Email'];
	if($rd['WholesaleAccess']){
		$a_login['wholesaleAccess']=$rd['WholesaleAccess'];
	}
	$a_login['hostName'] = ($MASTER_HOSTNAME ? $MASTER_HOSTNAME : 'localhost');
	$a_login['userName'] = ($MASTER_USERNAME ? $MASTER_USERNAME : $UN);
	$a_login['password'] = (function_exists('generic5t') ? generic5t($MASTER_PASSWORD ? $MASTER_PASSWORD : $PW, 'encode') : ($MASTER_PASSWORD ? $MASTER_PASSWORD : $PW));
	$a_login['status'] = 50; /** Standard RelateBase status field, this is not developed **/
	$a_login['localStatusField'] = 'RBStatus'; /** Normally RBStatus **/
	$a_login['localStatus'] = (isset($rd['RBStatus']) ? $rd['RBStatus'] : 50);/** Analogous AMAP to Status; 50=best value **/
	if($a=$usemod['sessionCnxNodeParameters']){
		foreach($a as $n=>$v){
			$a_login[$n]=$rd[$n]; //$v is reserved to operate on the data if needed
		}
	}

	//2010-02-13: set idLevel for extra security
	$a_login['idLevel']=($squishyLoginAttempt ? idlevel_cookied_squishy_login : idlevel_hard_login);

	//two versions of this table out there
	ob_start();
	if($accesses=q("SELECT a.ID, LCASE(a.Name) FROM addr_access a, addr_ContactsAccess b WHERE a.ID=b.Access_ID AND b.Contacts_ID='".$a_login['primaryKeyValue']."'", O_COL_ASSOC, $v280cnx, ERR_ECHO, O_DO_NOT_REMEDIATE)){
		$a_login['accesses']=$accesses;
	}
	$err=ob_get_contents();
	ob_end_clean();

	if($err && ($accesses=q("SELECT LCASE(AccessNode), 1 FROM addr_contacts_access WHERE Contacts_ID='".$a_login['primaryKeyValue']."'", O_COL_ASSOC, ERR_SILENT, O_DO_NOT_REMEDIATE, $v280cnx))){
		mail($developerEmail, 'Incorrect structure for Accesses table; error file '.__FILE__.', line '.__LINE__, get_globals(), $fromHdrBugs);
		$a_login['accesses']=$accesses;
	}
	if(!$overrideSetLoginVars){
		foreach($b_login as $n=>$v)$_SESSION[$n]=$v;
		$_SESSION['cnx'][$cnxKey]=$a_login;
		//we're done with the recordset
		unset($rd);
	}
}
