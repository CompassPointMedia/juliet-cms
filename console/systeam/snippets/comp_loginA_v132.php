<?php
/**
2009-05-14: tied in group extension table for that module type
2009-05-13: this was copied over from v130
2007-09-01: added

$MASTER_PASSWORD 

2007-08-31: this is a back-update from MGA Services lib/cpm/comp_loginA_v130.php, also modified the login coding to handle lockout, and added rbase_failedlogins table
2007-04-30: updated some system related variables such as loginTimeUNIX etc.
2006-04-16: this now makes sure all tables listed in the module are present and in synch with the the corresponding tables in relatebase_template (which contains the most recent version of all relatebase tables used in modules)

**/
$loginAllowedAttempts=5; //number of attempts allowed before lockout
$loginGracePeriod=5; //minutes
$loginLockoutTime=20; //time forward from last failed login during which lockout is in effect
define("LOCKOUT_EFFECTIVE",3);
define("LOCKOUT_SET",2);
define("LOCKOUT_NONE",1);

if(!function_exists('parse_javascript_gmt_date')){
	require("$FUNCTION_ROOT/function_parse_javascript_gmt_date_v120.php");
}
/** login snippet pulled out 2006-01-03: this sets $loginOK to true only if login is successful **/
$loginOK=false;

//format for authKey is md5( MASTER_PASSWORD . Password )
if(strlen($authKey) && $authKey == md5($MASTER_PASSWORD . ($x=q("SELECT Password FROM rbase_userbase WHERE UserName='$UN'", O_VALUE, C_MASTER)))){
	$PW=addslashes($x);
}

$fl=__FILE__;$ln=__LINE__+1;
$result=q("SELECT
a.*,
TRIM(c.Company) AS Company,
IF(ctc.FirstName  IS NOT NULL, ctc.FirstName , c.FirstName ) AS FirstName,
IF(ctc.LastName   IS NOT NULL, ctc.LastName  , c.LastName  ) AS LastName ,
IF(ctc.MiddleName IS NOT NULL, ctc.MiddleName, c.MiddleName) AS MiddleName,
IF(ctc.Email      IS NOT NULL, ctc.Email     , c.Email     ) AS Email,
IF(ctc.CreateDate IS NOT NULL, ctc.CreateDate, c.CreateDate) AS CRDATE,
IF(ctc.EditDate   IS NOT NULL, ctc.EditDate  , c.EditDate  ) AS EDDATE,
c.Password
FROM rbase_account a, rbase_userbase c 
LEFT JOIN rbase_UserbaseUserbase pivot ON Parent_UserName = c.UserName AND pivot.Type='Primary'
LEFT JOIN rbase_userbase ctc ON Child_UserName = ctc.UserName
LEFT JOIN rbase_temppws b ON c.UserName=b.UserName
WHERE
a.UserName = c.UserName AND
(	a.UserName = '$UN' OR c.Email='$UN' ) AND 
(	b.Password='$PW' OR c.Password='$PW' OR '$PW' IN('$OVERRIDE_PASSWORD')	) AND
'$PW' != ''
ORDER BY IF(a.AcctName=a.UserName,1,2)",C_MASTER);
$synchronizedTables=array();

sleep(1); //prevent rapid-fire attacks
$lockout=LOCKOUT_NONE;
$LockoutTime=q("SELECT LockoutTime FROM rbase_failedlogins WHERE IPAddress='".$GLOBALS['REMOTE_ADDR']."' AND UserName='".$UN."' AND Lockout=1", O_VALUE, C_MASTER);
if($LockoutTime>time()){
	//lockout in effect
	$lockout=LOCKOUT_EFFECTIVE;
}else if(mysqli_num_rows($result)){
	//clear probation
	q("DELETE FROM rbase_failedlogins WHERE IPAddress='".$GLOBALS['REMOTE_ADDR']."' AND UserName='".$UN."'", C_MASTER);
	$idx=0;
	while($out = mysqli_fetch_array($result,MYSQLI_ASSOC)){
		prn($out);
		$idx ++;
		$acct=$out['AcctName'];
		$type=$out['Type'];
		if($idx==1){
			//get the root level information
			$_SESSION['identity'] = $userType[$type];
			$_SESSION['createDate'] = $out['CRDATE'];
			$_SESSION['editDate'] = $out['EDDATE'];
			$_SESSION['firstName'] = $out['FirstName'];
			$_SESSION['middleName'] = $out['MiddleName'];
			$_SESSION['lastName'] = $out['LastName'];
			$_SESSION['email'] = $out['Email'];
			$_SESSION['createDate'] = $out['CRDATE']; //represent the create and edit date of the userbase record, i.e. primary person or individual
			$_SESSION['editDate'] = $out['EDDATE'];
			if($postLoginTime=parse_javascript_gmt_date($postTime, $browser)){
				$_SESSION['loginTimeFulltext']=date('Y-m-d H:i:s',$postLoginTime);
				$_SESSION['loginTime']=preg_replace('/[^0-9]*/','',$_SESSION['loginTimeFulltext']);
				$_SESSION['loginTimeTZ']=$parse_javascript_gmt_date['TZ'];
				$_SESSION['loginTimeTZString']=$parse_javascript_gmt_date['TZString'];
				$_SESSION['loginTimeSource']='User Agent';
				$loginTimeVariance =
				$_SESSION['env']['timeVariance']=
					$postLoginTime - $parse_javascript_gmt_date['TZ']*3600 - /*adjusted post time */
					(time() - ($RelateBaseServerTZDifference * 3600)) /* adjusted server time */;
			}else{
				//can't set time zone, and use system datestamp as login time
				$_SESSION['loginTimeFulltext']=$dateStamp;
				$_SESSION['loginTime']=$timeStamp;
				$_SESSION['loginTimeTZ']='';
				$_SESSION['loginTimeTZString']='';
				$_SESSION['loginTimeSource']='Server';
				$_SESSION['env']['timeVariance']='unknown';
			}
			
			//get last login time
			$fl=__FILE__;$ln=__LINE__+1;
			if($lastLoginTime=q("SELECT MAX(EnterTime) FROM rbase_logs WHERE logUserName='".$out['UserName']."'", O_VALUE, C_MASTER)){
				$_SESSION['lastLoginTimeFulltext'] = $lastLoginTime;
				$_SESSION['lliTime'] = 
					str_replace(':','',
					str_replace(' ','',
					str_replace('-','',
					$lastLoginTime)));
			}
			$_SESSION['sessionIP']=$GLOBALS['REMOTE_ADDR'];
			$_SESSION['systemUserName']=$out['UserName'];
			$_SESSION['sessionKey']=$GLOBALS['PHPSESSID'];
			$_SESSION['loginComponent']='comp_loginA_v131.php';

			if($machineName){
				$_SESSION['machineName'] = $machineName;
			}else{
				$requestMachineName=true;
			}				
			//get the default connection (defaultConnection) -- see note
			/***
			2004-05-14: if I change the default connection, then I must also change and/or spawn a new rbase_logs record equivalent to a switch
			
			***/
			//look in querystring, post, cookie
			//note 11-13-02: this follows the "GPSCD" order for figuring the default connection
			if($_SESSION['defaultConnection'] = $_GET['defaultConnection']){
				//set the cookie
				setcookie('dCon',$_GET['defaultConnection'],time()+(3600*24*60),'/');
			}else if($_SESSION['defaultConnection']=$_POST['defaultConnection']){
				//set the cookie
				setcookie('dCon',$_POST['defaultConnection'],time()+(3600*24*60),'/');
			}else if($_SESSION['defaultConnection']=$_COOKIE['dCon']){
			}else{
				setcookie('dCon',$acct,time()+(3600*24*60),'/');
				$_SESSION['defaultConnection'] = $acct;
			}
			//set the current connection as current location since this is a login
			$cc=$_SESSION['currentConnection'] = $_SESSION['defaultConnection'];
			$cu=$out['UserName'];
		}
		$connections[]=$acct;
		//enter this IP address in the AcctName-UserName record
		q("UPDATE rbase_account SET LastIPAddress='".$GLOBALS['REMOTE_ADDR']."' WHERE AcctName='$acct' AND UserName='$cu'", C_MASTER);

		//get the connection settings
		$fl=__FILE__;$ln=__LINE__+1;
		if($company=q("SELECT Company FROM rbase_account a, rbase_userbase b WHERE a.AcctName=b.UserName AND a.UserName='".$out['UserName']."'",O_VALUE, C_MASTER)){
			$_SESSION['cnx'][$acct]['company'] = $company;
		}
		$fl=__FILE__;$ln=__LINE__+1;
		if($a=q("SELECT Type, Position FROM rbase_UserbaseUserbase WHERE Parent_UserName='$acct' AND Child_UserName='".$out['UserName']."'",O_ROW, C_MASTER)){
			$_SESSION['cnx'][$acct]['type'] = $a['Type'];
			$_SESSION['cnx'][$acct]['position'] = $a['Position'];
		}
		$_SESSION['cnx'][$acct]['id'] = $out['ID'];
		$_SESSION['cnx'][$acct]['status'] = $out['Status'];
		$_SESSION['cnx'][$acct]['acctName'] = $acct;
		$_SESSION['cnx'][$acct]['platform'] = $out['Platform'];
		$_SESSION['cnx'][$acct]['hostName'] = $out['HostName'];
		$_SESSION['cnx'][$acct]['userName'] = $out['AcctName'];
		$_SESSION['cnx'][$acct]['password'] = q("SELECT Password FROM rbase_userbase WHERE UserName='".$out['AcctName']."'", O_VALUE);
		$_SESSION['cnx'][$acct]['identity'] = $userType[$out['Type']];

		//modules is an array
		ob_start();
		if($modList=q("SELECT b.Notes, c.* 
		FROM rbase_account a, rbase_AccountModules b, rbase_modules c WHERE 
		a.ID = b.Account_ID AND c.ID = b.Modules_ID AND a.ID = '".$out['ID']."'
		ORDER BY SKU ASC", O_ARRAY, C_MASTER))
		foreach($modList as $mod){
			//echo "<em>".$mod['SKU'] . "</em><br>";
			unset($a);
			$a['Category'] = $mod['Category'];
			$a['SKU'] = $mod['SKU'];
			$a['PartNumber'] = $mod['PartNumber'];
			$a['ShortDescription'] = $mod['ShortDescription'];
			$a['Status'] = $mod['Status'];
			$a['EffectiveEnd'] = $mod['EffectiveEnd'];
			$a['EffectiveCharge'] = $mod['EffectiveCharge'];
			$a['VersionInfo'] = $mod['VersionInfo'];
			$x=$userType[$type];
			if($x=='Administrator' || $x=='Superadministrator'){
				$a['Unix']=$mod['UnixPermissions'];
				$a['DB']=$mod['DBPermissions'];
			}else{
				$a['Unix']=(is_null($mod['UnixPermissions'])?0:$mod['UnixPermissions']);
				$a['DB']=(is_null($mod['DBPermissions'])?0:$mod['DBPermissions']);
			}
			//2009-05-14
			if(strtoupper($a['SKU'])=='GRP' && $grp=q("SELECT Modules_ID, Name, Notes, Settings, EditDate FROM modules_groups WHERE Modules_ID=".$mod['ID'], O_ROW)){
				if(@$b=unserialize(base64_decode($grp['Settings']))){
					$grp['Settings']=$b;
				}else unset($grp['Settings']);
				$a['Group']=$grp;
			}
			$_SESSION['cnx'][$acct]['modules'][$mod[ID]]=$a;
			
			//we want to verify that the module has all of the current tables and other assets - if there is any discrepancy email admin.  However with tables we can do the synch right now.
			//ob_start();
			$sysComponents=q("SELECT b.Name, b.Types_ID
			FROM rbase_mst_ModulesItems a, rbase_mst_items b WHERE a.Modules_ID='".$mod['Mst_modules_ID']."' AND a.Items_ID=b.ID ORDER BY b.Name, b.Types_ID", O_COL_ASSOC, C_MASTER);
			//print_r($qr['query']);
			$modComponents=q("SELECT Name, Types_ID
			FROM rbase_modules_items WHERE Modules_ID='".$mod['ID']."' ORDER BY Name, Types_ID", O_COL_ASSOC, C_MASTER);
			//print_r($qr['query']);

			//see if they're equal
			$RB_CURRENTACCTNAME=$acct; //set rb_var - this is the only current one(? - not sure)
			reset($sysComponents); reset($modComponents);
			$moduleConforms=true;
			//print_r($sysComponents);
			//print_r($modComponents);
			//$err=ob_get_contents();
			//ob_end_clean();
			while($sc=each($sysComponents)){
				$mc=each($modComponents);
				if(/** Types: **/ $sc[1]!=$mc[1] || /** Names: **/($sc[0]!=$mc[0] && rb_vars($sc[0])!=$mc[0])){
					$moduleConforms=false;
					mail($adminEmail,'Module out of synch','The module (ID= '.$mod['ID'].') for user '.$UN.' in account '.$acct.' is out of synch with the master module '.$mod['Mst_modules_id'].', err as follows:'."\n".$err,$fromHdrBugs);
					//records should be added to the module to keep in synch but I'm waiting on that until I have a specific protocol - AND THE USER NEEDS TO BE ABLE TO ACCEPT THIS as an "update" to their software - some of these actions could compromise data
					break;
				}
			}
			#prn(get_included_files(),1);
			if($modComponents){
				//echo 'got components<br>';
				foreach($modComponents as $modComponent=>$compType){
					//echo "$modComponent:$compType<br>";
					if($compType==20){
						//RTCS Table Group - very important to synch this with new developments
						foreach($RTCS as $RTCSTable){
							if(!in_array(strtolower($RTCSTable),$synchronizedTables)){
								//echo 'synchronizing table '.$RTCSTable . "<br>";
								//rtcs_mysql_synchronize($mostCurrentDb[$RTCSTable], $RTCSTable, 'mysql', $acct, 	$RTCSTable, 'mysql', array('error_action'=>NO_ACTION, 'notification'=>ACTION_NOTIFICATION));
								$synchronizedTables[]=strtolower($RTCSTable);
							}
						}
					}else if($compType==30){
						//individual table
						if(!in_array(strtolower($modComponent),$synchronizedTables)){
							if(!$mostCurrentDb[$modComponent]){
								mail($adminEmail, 'Missing mostCurrentDb.thisTable value in array: '.$modComponent, 'fix it',$fromHdrBugs);
							}else{
								//echo 'synchronizing table '.$modComponent . "<br>";
								//rtcs_mysql_synchronize($mostCurrentDb[$modComponent], $modComponent, 'mysql', $acct, $modComponent, 'mysql', array('error_action'=>NO_ACTION, 'notification'=>ACTION_NOTIFICATION));
								$synchronizedTables[]=strtolower($modComponent);
							}
						}
					}
				}
			}
		}
		$debug=ob_get_contents();
		ob_end_clean();
	}
	//reset current and default connections if needed
	if(!in_array($cc,$connections)){
		$cc=$_SESSION['currentConnection']=$connections[0];
	}
	if(!in_array($_SESSION['defaultConnection'],$connections)){
		setcookie('dCon',$connections[0],time()+(24*3600*60),'/');
		$_SESSION['defaultConnection']=$connections[0];
	}

	//we're done with the recordsets
	unset($out);
	
	//log the event
	q("INSERT INTO rbase_logs SET
	EditDate ='$timeStamp',
	logAcctName  = '$cc',
	logUserName  = '".$_SESSION['systemUserName']."',
	logEmail  = '".$_SESSION['email']."',
	Action  = '1', /* 1=login */
	RequestType  = '".(isset($_POST['UN']) ? 1 : 2)."', /** 1=POST, 2=GET **/
	SessionKey  = '".$_COOKIE['PHPSESSID']."',
	IPAddress  = '".$GLOBALS['REMOTE_ADDR']."',
	Referrer  = '" . $GLOBALS['HTTP_REFERER'] . "',
	EnterTime  = '$dateStamp',
	ExitTime  = '0000-00-00 00:00:00'", C_MASTER);

	//We need to log the session in our database and in the respective company(s) databases
	#not done
	
	$loginOK=true;
}else if($try=q("SELECT * FROM rbase_failedlogins WHERE IPAddress='".$GLOBALS['REMOTE_ADDR']."' AND UserName='".$UN."'", O_ROW, C_MASTER)){
	/* there is a failed login record */
	if(time()-$try['ProbationTime'] > $loginGracePeriod*60){
		//reset the record
		q("DELETE FROM rbase_failedlogins WHERE IPAddress='".$GLOBALS['REMOTE_ADDR']."' AND UserName='".$UN."'", C_MASTER);
		q("INSERT INTO rbase_failedlogins SET Tries=1, ProbationTime=".time().", IPAddress='".$GLOBALS['REMOTE_ADDR']."', UserName='".$UN."'", C_MASTER);
		$tries=1;
	}else{
		$tries=$try['Tries']+1;
		if($tries>=$loginAllowedAttempts){ /* number of tries above allowed */
			//set lockout
			$LockoutTime=time()+$loginLockoutTime*60;
			q("UPDATE rbase_failedlogins SET
			Tries='$tries',
			Lockout=1,
			LockoutTime='$LockoutTime'
			WHERE IPAddress='".$GLOBALS['REMOTE_ADDR']."' AND UserName='".$UN."'", C_MASTER);
			$lockout=LOCKOUT_SET;
		}else{
			//increase tries
			q("UPDATE rbase_failedlogins SET Tries=$tries WHERE IPAddress='".$GLOBALS['REMOTE_ADDR']."' AND UserName='".$UN."'", C_MASTER);
		}
	}
}else{
	//set a failed login record
	q("INSERT INTO rbase_failedlogins SET Tries=1, ProbationTime=".time().", IPAddress='".$GLOBALS['REMOTE_ADDR']."', UserName='".$UN."'", C_MASTER);
	$tries=1;
}
?>