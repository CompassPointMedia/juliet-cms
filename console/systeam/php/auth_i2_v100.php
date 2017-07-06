<?php
//auth_i2_v100.php

/**
Note 2005-06-26 by Sam: I hadn't thought to say this but this file only checks for appropriate access, comparing the localSys script ID's with the login backmap to authorized components.  It also sets a few login-specific variables cu and cuName.  So, this page could be omitted for a non-login section without consequence.



Last Edited 2004-06-16 by Sam: currently the file requires the scriptID and scriptVersion, compiles them like myscript-1.0 (i.e. version 1.0), and then checks to see if that process is in any roles that the current user has.  Once in, the page itself must determine further what can or cannot be done.

This file will eventually address other means of access.


this page for the admin section will do the following
1. determine the script id.  IF not there it shuts down the page
	(NOTE: get the version also)
2. determine the admin and see if they have access to this script
3. determine the user (applicant) and see if there is a relationship for this admin to this user in this script
**/
if(false){
	$roleAccessPresent=false;
	$gif=get_included_files();
	$authPresent=false;
	if(count($gif)){
		foreach($gif as $v){
			if(strstr($v,'config.php'))$authPresent=true;
		}
	}
	if(!$authPresent)exit('auth_i2_v100.php requires config.php to run');
	//script ID and Version
	if(!$localSys['scriptID'] || !$localSys['scriptVersion']){
		exit('Script ID and version not declared');
	}
	if(!$_SESSION['admin']['identity'] || !$_SESSION['admin'][userName]){
		//handle non-logged in
		echo 'not logged in';
		//handle notification inside iframes
		?><script defer>
		/***
		THIS SCRIPT IS NOT COMPATIBLE WITH ALL BROWSERS - window.opener is having some problems being identified
		***/
		if(window.parent.name!=self.name){
			alert('The action you wanted to perform cannot be performed because your login has timed out.\nLog in again in the main window, and retry.');
			window.parent.focus();
		}else if(typeof window.opener=='object'){
			//this is an l1 window
			alert('Not logged in, close window if necessary and re-Log in');
			var x=escape(window.location+'');
			window.location='/console/login/index.php?src='+x;
		}else{
			var x=window.location+'';
			window.location='/console/login/index.php?src='+x;
		}
		</script><?php
		if($authExitOverride==1){exit('authExitOverride');}
		//exit process
		exit;
	}else{
		$cu=$currentUser=$_SESSION[systemUserName];
		$cuName=$currentUserName=$_SESSION['admin']['firstName'].' '.$_SESSION['admin']['lastName'];
		//see if they have access to the page via their role
		$sI=$localSys[scriptID]; $sV=$localSys[scriptVersion];
		if($thisProcess=$_SESSION[sys][processBackmap]["$sI-$sV"]){
			if(is_array($_SESSION[admin][roles])){
				foreach($_SESSION[admin][roles] as $n=>$v){
					//$n is user's role, 
					if($_SESSION[sys][RolesProcesses][$n][$thisProcess]){
						//user has access through their role assignment
						$roleAccessPresent=true;
						break;
					}
				}
			}
			//NOTE: the user may still have access to this script if they are listed in StaffProcesses (for global use) or in StaffApplicantsProcesses (for use with a specific applicant)
		}else{
			//jingtao todo: email developer
			exit("This page $sI-$sV is not properly registered in the table 'bais_processes' - this can only be done by a db administrator");
		}
	}
}

//-----------------------  user settings - added 2008-04-20  ------------------------------
if(!$tabDisposition['memberMain']['company'])$tabDisposition['memberMain']['company']=CONTACTS_COMPANY;
if(!isset($tabDisposition['memberMain']['wholesaleAccess']))$tabDisposition['memberMain']['wholesaleAccess']=false;

$qx['defCnxMethod']=C_MASTER;
$defaultUserSettings=array(
	'hideInactiveMember'=>0,
	'hideInactiveEvent'=>0
	
	
	/* 'arraynode:key'=>'value' this is an example */
);
if($_SESSION['userSettings'] && !$refreshUserSettings){
	$userSettings=$_SESSION['userSettings'];
}else{
	//set initial values
	$userSettings=q("SELECT CONCAT( varnode, IF(varkey!='',':',''), varkey), varvalue FROM bais_settings WHERE UserName='".sun()."' ORDER BY varnode, varkey", O_COL_ASSOC);
	$_SESSION['userSettings']=array_merge($defaultUserSettings, $userSettings);
}
?>