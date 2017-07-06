<?php
/*
Created 2010-04-11 by Samuel
2010-10-01: see note of same date below
2010-04-21: d'oh .. never put the goodies coding from auth_i2_v100.php in here - we weren't getting user settings
2010-04-14: fixed big hole - you could enter the password but the wrong account number.  Now you can only enter the username AND password for the site you're in

*/
$consoleMethod='http';
function login_failure($UN,$PW,$acct){
	global $test, $remoteCallPWOverride, $fl, $ln, $qr, $developerEmail, $fromHdrBugs;

	//2010-10-01: allow remote calls of the console from outside (e.g. the cgi) by sending remoteCallPWOverride=md5(MASTER_PASSWORD)
	$dbPW=q("SELECT Password FROM rbase_userbase WHERE UserName='$acct'", O_VALUE, C_SUPER);

	if(strlen($dbPW) && $remoteCallPWOverride==md5($dbPW)){
		return false;
	}
	
	//fail a blank login (remember password could be '0' so we use strlen())
	if(!$UN || !strlen($PW))return true;
	if(strtolower($acct)!=$UN)return true;
	
	//2010-04-12: the system username variable is established, system password is brand new as of today and needs encrypted
	if($_SESSION['systemUserName']==$UN && $_SESSION['systemPassword']==$PW){
		return false;
	}

	//2010-04-12: acct is already determined in config.php (this is definitely redundant)
	if($PW==$dbPW){
		return false;
	}else{
		mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
		$_SESSION['special']['HTTP-Auth-redirects']++;
		if($_SESSION['special']['HTTP-Auth-redirects']>10){
			exit('Unable to sign into console due to HTTP header redirect.  Admin has been notified');
		}
	}
	return true;
}
if($logout){
	if($_SERVER['PHP_AUTH_USER']==$_SESSION['systemUserName'] && $_SERVER['PHP_AUTH_PW']==$_SESSION['systemPassword']){
		unset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
		header('WWW-Authenticate: Basic realm="Protected Page: Sign in as another user."');
		header('HTTP/1.0 401 Unauthorized');  
	}
}

if($fail=login_failure($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW'],$acct)){
	// Bad or no username/password.  
	// Send HTTP 401 error to make the browser prompt the user.  
	header('WWW-Authenticate: Basic realm="Protected Page: Enter your username and password for access."');

	if($test==17){
		prn("$UN,$PW,$acct");
		prn($qr);
		exit(line . __LINE__);
	}
	// Display message if user cancels dialog  
	echo '
	<HTML>  
	<HEAD><TITLE>Authorization Failed man</TITLE></HEAD>
	<BODY>  
	<H1>Authorization Failed</H1>  
	<P>Without a valid username and password,  
	access to this page cannot be granted.  
	Please click reload and enter a  
	username and password when prompted. '." yo: ".$acct.'
	</P>   
	</BODY>
	</HTML>';
	exit;
	header('HTTP/1.0 401 Unauthorized');  
}
if($logout){
	//optional, redirect to prevent a dialog again if the user refreshes or hits F5..
	$q=preg_replace('/&*logout=[01]/','',$GLOBALS['QUERY_STRING']);
	if($q)$q='?'.$q;
	header('Location: '.$GLOBALS['PHP_SELF'].$q);
	exit;
}

/* ------------ 2010-04-21: I am deprecating these for now ----------
if(!$tabDisposition['memberMain']['company'])$tabDisposition['memberMain']['company']=CONTACTS_COMPANY;
if(!isset($tabDisposition['memberMain']['wholesaleAccess']))$tabDisposition['memberMain']['wholesaleAccess']=false;
*/
$defaultUserSettings=array();
$qx['defCnxMethod']=C_MASTER;
if($_SESSION['userSettings'] && !$refreshUserSettings){
	$userSettings=$_SESSION['userSettings'];
}else{
	//set initial values
	$userSettings=q("SELECT CONCAT( varnode, IF(varkey!='',':',''), varkey), varvalue FROM bais_settings WHERE UserName='".sun()."' ORDER BY varnode, varkey", O_COL_ASSOC);
	$_SESSION['userSettings']=array_merge(
		$defaultUserSettings ? $defaultUserSettings : array(), 
		$userSettings ? $userSettings : array()
	);
}
?>