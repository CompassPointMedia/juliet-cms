<?php
/*
Created 2013-06-19 SF
this integrates usemod in with the console.  This will now allow individual access to the console which I must now begin to define which pages which people have access to.

todo:
	* make part of my application process loading a logo for the company and a picture of the individual - personalize the console experience
	
	Testing:
		* MAKE SURE THIS BLOCKS OUT NON-LOGGED IN PEOPLE!
		
	DONE	style out loginForm; allow for external link style
	'acceptablePermissionTypes'=>array(),
	'sessionCnxNodeParameters'=>array(),
	some windows do not need a login form, they need a wpo.location=login, OR they need to close
	
	DONE	get the state city and zip fields in + country
	DONE	$UserName=sql_autoinc_text();
	DONE	get into finan_clients
	DONE	get the permissions in place
	DONE	log them in using comp b
	for consoleMethod=usemod, 
		* have the company as well(?)
		* have a bubble by the name:
		Access: DB Admin
		(also Admin, Etc.)
		[Edit your record]
		{RB settings access}
		{manage admins (if they have the right)}
		
	DONE add posttime, environment, machinename etc. as on relatebase login (say from simple fostercare) - CREATE THE TABLES IF NECESSARY! DO IT RIGHT
	PHP
		* give the user an interface to identify their computer as with relatebase.com/client/login from long ago
		* add hack attack prevention
		* relogin from previous - make sure it carries over
		* email send-out
		* I should have a defined constant for dbadmin, admin, regional, and etc. that maps well with GLF, LSS and AMS - research this
	JS
		* jquery interlock between existing contact and new entry
	* styling RelateBase Console, with link to the site
		we are beginning an application process of design your own website
	* help and resources - same, mostly, as help links when logged in
	* contact us form
	* implement idLevel
	* implement authToken -for soft logins
	


questions: 
	how about me being able to log in to any of these accounts because I'm in a universal table? what was the deal on this?

if(logging out){
	log them out, handle any logging of visit and show login form again
}else if(logged in){
	if(they have access to this page or action){
		great, return true
	}else{
		note there could be some give and take between here and the code block that has to tell them something more specific
		exit	
	}
}else if(logging in){
	determine if they have been configured for usemod login/set up
	
	if(usemod login){
		if(they successfully login match with at least one access){ //(limiting join to ContactsAccess)
			//set permissions, great
			
			return true
		}else{
			login form, error condition 1
		}
	}else if(logging in with master username and password){
		show the interim setup form
		create a contact, or identify a contant in the system - set up a token and md5 also for next step
	}else if(interim setup){
		error checking
		create record
		we are in iframe, redirect parent to src
	}else{
		login form, error condition 2
	}
}
*/


if(!defined('LOGIN_SINGLE_INITIAL'))
define('LOGIN_SINGLE_INITIAL',5);		// single record - look for any type
if(!defined('LOGIN_SINGLE_PRIMARY'))
define('LOGIN_SINGLE_PRIMARY',10);		// single record - look for type=Primary
if(!defined('LOGIN_MULTI_ONEPRIMARY'))
define('LOGIN_MULTI_ONEPRIMARY',15);	// multiple records - look for one with type=Primary
if(!defined('LOGIN_MULTI_INITIAL'))
define('LOGIN_MULTI_INITIAL',20);		// multiple records - but no type=Primary
if(!defined('LOGIN_MULTI_PRIMARY'))
define('LOGIN_MULTI_PRIMARY',25);		// multiple records - multiple with type=Primary

if(!defined('idlevel_anonymous'))
define('idlevel_anonymous',-16); 		//we have no cookie on them
if(!defined('idlevel_cookied_unknown'))
define('idlevel_cookied_unknown',-8);	//we have a cookie but they have never been identified
if(!defined('idlevel_cookied_assumed'))
define('idlevel_cookied_assumed',-4);	//we have a cookie and assume they are someone - but don't interact with them as "them"
if(!defined('idlevel_cookied_squishy_login'))
define('idlevel_cookied_squishy_login',-2);	//we interact with them via squishy login - but obviously not secure
if(!defined('idlevel_softlogin'))
define('idlevel_softlogin',-1);			//from an email link with UN & authToken and possibly a timeout - but not secure
if(!defined('idlevel_hard_login'))
define('idlevel_hard_login',0);			//from submitting a form.  They have manually logged in - assumed secure

$consoleMethod='usemod';
function usemod($options=array()){
	global $public_cnx;
	/* created 2013-06-19 */
	extract($options);
	if(!$acct)					global $acct;
	global $HTTP_USER_AGENT,$REMOTE_ADDR;
	global $MASTER_USERNAME,$MASTER_PASSWORD,$MASTER_HOSTNAME,$MASTER_DATABASE,$fl,$ln,$qr,$qx,$developerEmail,$fromHdrBugs;
	global $dateStamp,$postTime,$environment,$machineName,$requestMachineName,$squishyLoginAttempt;
	global $browser,$tz,$parse_javascript_gmt_date,$RelateBaseServerTZDifference;
	global $forgotPassword,$resetPassword;
	
	$acceptableAccesses=array('DB Admin','Superadmin','Admin');
	$authFile = $_SERVER['DOCUMENT_ROOT'] . '/components-juliet/cgi.comp.login_b.php';
	$machineFile=str_replace('auth_i4_Usemod-Authentication_v100','machine_identification',__FILE__);
	/*
	determine if they have been configured for usemod login/set up
	they need to have the two tables addr_access and addr_ContactsAccess
	they need to have the new field addr_access.Category
	they need to have the records DB Admin through Editor with Category='{system}'
	*/
	$usemodReady=true;
	
	//these __tigris__ variables need to be moved out and stored in some system - they are the last clutter of variables to be created
	$__tigris__accessSystemCategory='{system}';

	ob_start();
	$currentRemediation = $GLOBALS['qx']['useRemediation'];
	$GLOBALS['qx']['useRemediation'] = false;

	$records=array(
		array(
			'Category'=>$__tigris__accessSystemCategory,
			'Name'=>'DB Admin',
			'Description'=>'Database administrator',
		),
		array(
			'Category'=>$__tigris__accessSystemCategory,
			'Name'=>'Superadmin',
			'Description'=>'Superadministrator, can do more than a regular administator',
		),
		array(
			'Category'=>$__tigris__accessSystemCategory,
			'Name'=>'Admin',
			'Description'=>'Administrator, ability to install themes, manage components, and grant permissions to others',
		),
		array(
			'Category'=>$__tigris__accessSystemCategory,
			'Name'=>'Designer',
			'Description'=>'Designers are able to install themes and edit contents, but are not normally able to create editors',
		),
		array(
			'Category'=>$__tigris__accessSystemCategory,
			'Name'=>'Editor',
			'Description'=>'Content Editor, lowest permissions in the Juliet system',
		),
	);
	$f1=q_tools(array(
			'mode'=>'table_exists',
			'table'=>'addr_ContactsAccess',
			'return'=>'change',
		));
	if(false)$f2=q_tools(array(
			'mode'=>'field_exists',
			'table'=>'addr_ContactsAccess',
			'field'=>'EditDate',
			'return'=>'change',
		));
	$f3=q_tools(array(
			'mode'=>'table_exists',
			'table'=>'addr_access',
			'return'=>'change',
		));
	$f4=q_tools(array(
			'mode'=>'field_exists',
			'table'=>'addr_access',
			'field'=>'Category',
			'return'=>'change',
			/*figure out from template db, or optionally: */
			'command'=>'ALTER TABLE `addr_access` ADD `Category` CHAR( 30 ) NOT NULL COMMENT \''.date('Y-m-d').'\' AFTER `ID`, ADD UNIQUE `CategoryName`(`Category`,`Name`)',
			/* need special command to index the field */
			'post_command'=>'ALTER TABLE addr_ContactsAccess ADD UNIQUE name(etc, etc)',
			/* or optionally:
			'post_process'=>'do_function($value1, $value2)', */
		));
	$f5=q_tools(array(
			'mode'=>'records',
			'submode'=>'insert',
			'table'=>'addr_access',
			'records'=>$records,
			'check'=>array('Category','Name'),
			'return'=>'change',
		));
	if($f1 || $f2 || $f3 || $f4 || $f5)$usemodReady=false;
    if(!($ids=q("SELECT c.ID FROM addr_contacts c JOIN addr_ContactsAccess ca ON c.ID=ca.Contacts_ID JOIN addr_access a ON ca.Access_ID=a.ID WHERE a.Category='{system}' AND Name='Admin'", O_COL)))$usemodReady=false;

    $GLOBALS['qx']['useRemediation'] = $currentRemediation;
    ob_end_clean();

    //translation for component
	$usemod=array(
		'additionalGeneralWhereClause'=>'',
		'allowLoginForDuplicateUserTokens'=>false,
		'errFromHdr'=>$fromHdrBugs,
		'useEnrollmentConfirmation'=>false,
		'dbLoginFieldPrimary'=>'UserName',
		'signinAfterEnrollmentConfirmation'=>true,
		'allowRepeatEnrollmentLinkToSignin'=>true,
		'masterGuestPassword'=>NULL,
		'acceptablePermissionTypes'=>array(
			LOGIN_SINGLE_PRIMARY, LOGIN_MULTI_ONEPRIMARY
		),
		'sessionCnxNodeParameters'=>array(),
		/* for sending email */
		'siteName'=>$GLOBALS['adminCompany'],
		'replytoEmail'=>$GLOBALS['adminEmail'],
		'EmailLogo'=>false,
		'EmailHeader'=>false,
		'passwordResetTimeout'=>48,
		'usemodURLRoot'=>$GLOBALS['HTTP_HOST'].'/cgi',
	);
	//------------------------------------------------
	if(isset($UN) && (isset($PW) || isset($authKey))){
		if(strtolower($UN)==strtolower($MASTER_USERNAME) && md5(stripslashes($PW))==md5($MASTER_PASSWORD)){
			if($usemodReady){
				//login form, you are already set up, use your username and password
				/* but this gets sticky if they want to change things or data gets half-messed up */
				$form='login';
				$type='error';
				$message='You are using your account username and password, but this account is already set up.  Use your individual username and password instead';
				error_alert($message);
			}else if($_REQUEST['mode']=='setupConsole'){
				//2nd form submission with hidden username and password
				//----------- error checking ------------------
				$addr=$_POST['addr'];
				$GLOBALS[$addr.'Address']=$_POST['Address'];
				$GLOBALS[$addr.'City']=$_POST['City'];
				$GLOBALS[$addr.'State']=$_POST['State'];
				$GLOBALS[$addr.'Zip']=$_POST['Zip'];
				$GLOBALS[$addr.'Country']=$_POST['Country'];
				extract($_POST);
				if(strtolower($UN)!=strtolower($MASTER_USERNAME) || md5(stripslashes($PW))!=md5($MASTER_PASSWORD))
				error_alert('Abnormal error, master username or password have changed');
				//firstname, lastname, password
				if(!$Contacts_ID){
					//name, email, address, phone, mypassword
					if(!$newPW || $newPW!=$newPW2)error_alert('Enter and retype a password');
					if(stripslashes($newPW)==$MASTER_PASSWORD)error_alert('New password for administrator cannot be the same as the password for your account');
					if(strlen($newPW)<7 || !preg_match('/[a-zA-Z]/',$newPW) || !preg_match('/[0-9]/',$newPW))error_alert('Password must be at least 7 characters long and contain at least one letter and one number');
					if(!$FirstName || !$LastName)error_alert('Enter a first and last name');
					if(!valid_email($Email))error_alert('Enter a valid email address');
					if(strlen(preg_replace('/[^0-9]/','',$HomeMobile))<7 && strlen(preg_replace('/[^0-9]/','',$HomePhone))<7 &&strlen(preg_replace('/[^0-9]/','',$BusPhone))<7)error_alert('Enter at least one phone number of 7 digits or more');
					if(!$Address || !$City || !$State || !$Zip)error_alert('Enter a complete address, city, state and zip code');
					
					$GLOBALS['HomeDefault']=($addr=='home'?1:0);


					//2013-02-15: these are the three I have developed
					$userNameTables=array('finan_clients'=>'UserName','addr_contacts'=>'UserName','bais_universal'=>'un_username');
					foreach($userNameTables as $n=>$v){
						ob_start();
						q("SELECT COUNT($v) FROM $n", O_VALUE, ERR_ECHO, O_DO_NOT_REMEDIATE);
						$err=ob_get_contents();
						ob_end_clean();
						if(!$err){
							$tables[]=array('table'=>$n,'field'=>$v);
						}				
					}
					unset($err);
					$UN=$GLOBALS['UserName']=sql_autoinc_text($tables, NULL, array($FirstName,$LastName), array('where'=>$where));
					$PW=$GLOBALS['PasswordMD5']=md5(stripslashes($newPW));
				}

				if($Contacts_ID){
					//OK -- internal login
					if(!($a=q("SELECT UserName AS UN, PasswordMD5 AS PW FROM addr_contacts WHERE ID=$Contacts_ID", O_ROW)))error_alert('Abnormal error, contact id not located');
					extract($a);
					$a=q("SELECT Clients_ID FROM finan_ClientsContacts WHERE Contacts_ID=$Contacts_ID AND Type='Primary'", O_VALUE);
					if(!$a){
						$Clients_ID=q("INSERT INTO finan_clients(PrimaryFirstName, PrimaryMiddleName, PrimaryLastName, UserName, PasswordMD5, ResourceToken, Email, Mobile, Phone, Phone2, Address1, City, State, Zip, Country) SELECT
						FirstName, MiddleName, LastName, UserName, PasswordMD5,
						1,
						Email,
						HomeMobile, 
						IF(BusPhone!='',BusPhone,HomePhone), 
						IF(BusPhone!='',HomePhone,''), 
						IF(BusAddress!='',BusAddress,HomeAddress),
						IF(BusCity!='',BusCity,HomeCity),
						IF(BusState!='',BusState,HomeState),
						IF(BusZip!='',BusZip,HomeZip),
						IF(BusCountry!='',BusCountry,HomeCountry)
						FROM addr_contacts WHERE ID=$Contacts_ID", O_INSERTID);
						q("INSERT INTO finan_ClientsContacts SET Contacts_ID=$Contacts_ID, Clients_ID=$Clients_ID, Type='Primary', Notes='Added by ".end(explode('/',__FILE__))." line ".__LINE__."'");
					}
				}else{
					//create them
					$sql=sql_insert_update_generic($acct,'addr_contacts','INSERT');
					$Contacts_ID=q($sql,O_INSERTID);
					
					$GLOBALS['PrimaryFirstName']=$FirstName;
					$GLOBALS['PrimaryMiddleName']=$MiddleName;
					$GLOBALS['PrimaryLastName']=$LastName;
					$GLOBALS['CompanyName']=$FirstName . ' '. $LastName;
					$GLOBALS['ClientName']=sql_autoinc_text('finan_clients','ClientName',$FirstName. ' '. $LastName, array(
						'leftSep'=>'(',
						'rightSep'=>')',
						'returnLowerCase'=>true,
					));
					$GLOBALS['ResourceType']=1;
					$GLOBALS['Mobile']=$HomeMobile;
					$GLOBALS['Phone']=$BusPhone;
					$GLOBALS['Phone2']=$HomePhone;
					$sql=sql_insert_update_generic($acct,'finan_clients','INSERT');
					$Clients_ID=q($sql,O_INSERTID);
					
					q("INSERT INTO finan_ClientsContacts SET Contacts_ID='$Contacts_ID', Clients_ID='$Clients_ID', Type='Primary', Notes='Added by ".end(explode('/',__FILE__))." line ".__LINE__."'");
				}
				
				//give them permissions - all
				$present=q("SELECT LCASE(Name) FROM addr_ContactsAccess ca JOIN addr_access a ON ca.Access_ID=a.ID AND a.Category='{system}' WHERE ca.Contacts_ID=$Contacts_ID", O_COL);
				if(!$present)$present=array();
				foreach($acceptableAccesses as $v){
					if(in_array(strtolower($v),$present))continue;
					q("INSERT INTO addr_ContactsAccess SET Contacts_ID=$Contacts_ID, Access_ID='".q("SELECT ID FROM addr_access WHERE Category='{system}' AND Name='$v'",O_VALUE)."'");
				}
				//synch up the relatebase user on the account
				
				//send them an email
				
				//log them in
				$cnxKey=$acct;
				require($authFile);
				
				//change identities per console requirements
				//find lowest key in acceptableAccesses - note this is clumsy for the root session identity
				foreach($acceptableAccesses as $n=>$v){
					foreach($a_login['accesses'] as $w){
						if(strtolower($v)==strtolower($w)){
							$_SESSION['identity']=$v;
							$_SESSION['cnx'][$acct]['identity']=$v;
							break;
						}
					}
				}
				
				//we are in iframe, redirect parent to src
				?><script language="javascript" type="text/javascript">
				window.parent.location='<?php
				if($src){
					echo str_replace("'","\'",$src);
				}else{
					echo '/console/';
				}
				?>';
				</script><?php
				eOK();
			}else if($_REQUEST['mode']=='loginConsole'){
				$form='setup';
				$type='normal';
				$message='';
			}
		}else{
			$overrideSetLoginVars=true;
			require($authFile);
			unset($identity);
			if($a_login['accesses']) 
			foreach($acceptableAccesses as $n=>$v){
				foreach($a_login['accesses'] as $w){
					if(strtolower($v)==strtolower($w)){
						$identity=$v;
						break(2);
					}
				}
			}
			if($identity){
				#error_alert('test line'.__LINE__);
				//set session
				unset($_SESSION['cnx'][$acct]);
				foreach($b_login as $n=>$v){
					$_SESSION[$n]=$v;
				}
				$_SESSION['cnx'][$acct]=$a_login;
				$_SESSION['identity']=$identity;
				$_SESSION['cnx'][$acct]['identity']=$identity;

				//identify machine
				$userMethod='contacts';
				require($machineFile);

				//redirect parent
				?><script language="javascript" type="text/javascript">
				window.parent.location='<?php
				if($src){
					echo str_replace("'","\'",$src);
				}else{
					echo '/console/';
				}
				?>';
				</script><?php
				eOK();
			}else if($loginOK){
				if($usemodReady){
					error_alert('You do not have proper access to the console');
				}else{
					error_alert('To set up console access, you must first use the username and password you were given at account setup');
				}
			}else{
				$form='login';
				$type='error';
				$message='Your login is not correct';
				error_alert('Your login is not correct'.($loginCode?', login code '.$loginCode:''));
			}
		}
	}else if($logout){
		/*
		taken from cgi.2.8.7; modified for _CONSOLE_ identities called acceptableAccesses.  Gracefully kills session.
		logout:
			level 1 will remove only that cnx node AND the root if nothing remains in cnx.  Otherwise it will re-assign their identity to the highest level provided a comparison of levels can be given (else the last level encountered
			logoutSessionNodes, if passed as a comma-separated string or array, will also remove those session nodes specified, like 'admin' or 'special'
			level 2 will remove all cnx and session root values on the list below but nothing else
			level 3 will destroy the session completely
		*/
		//always forget persistentLogin
		setcookie('idToken','',time()-(24*3600*60),'/');
		$un=array('identity','cnx','createDate','creator','editDate','editor','firstName','middleName','lastName','email','loginTime','sessionIP','systemUserName','identity','sessionKey');
		if($logout==1){
			unset($_SESSION['cnx'][$acct]);
			if(!count($_SESSION['cnx'])){
				//remove session root values that cgi works with
				$_SESSION['identity']='';
				foreach($un as $v)unset($_SESSION[$v]);
			}else{
				$maxIdentity=100;
				foreach($_SESSION['cnx'] as $_acct_=>$node){
					if(!$node['accesses'])continue;
					foreach($acceptableAccesses as $n=>$v){
						foreach($node['accesses'] as $w){
							if(strtolower($v)==strtolower($w)){
								if($n<$maxIdentity){
									$maxIdentity=$n;
									$cnxNode=$_acct_;
									break(2);
								}
							}
						}
					}
				}
				if($cnxNode){
					$cnxNode=$_SESSION['cnx'][$cnxNode];
					$_SESSION['identity']=$cnxNode['identity'];
					//do not touch loginTime, sessionKey
					//in practice any one of these could change between sites					
					$_SESSION['firstName']=$cnxNode['firstName'];
					$_SESSION['middleName']=$cnxNode['middleName'];
					$_SESSION['lastName']=$cnxNode['lastName'];
					$_SESSION['email']=$cnxNode['email'];
	
					isset($cnxNode['createDate']) ? $_SESSION['createDate']=$cnxNode['createDate'] : '';
					isset($cnxNode['creator']) ? $_SESSION['creator']=$cnxNode['creator'] : '';
					isset($cnxNode['editDate']) ? $_SESSION['editDate']=$cnxNode['editDate'] : '';
					isset($cnxNode['editor']) ? $_SESSION['editor']=$cnxNode['editor'] : '';
					if(isset($cnxNode['systemUserName'])){
						$_SESSION['systemUserName']=$cnxNode['systemUserName'];
					}else{
						mail($developerEmail, 'Error on logout, no systemUserName to promote to root systemUserName in session, '.__FILE__.', line '.__LINE__,get_globals("this is because a cgi version < 2.8.5(360 Press Solutions) is installed"),$fromHdrBugs);
					}
				}
			}
		}else if($logout==2){
			$_SESSION['identity']='';
			foreach($un as $v)unset($_SESSION[$v]);
			unset($_SESSION['cnx']);
		}else{
			//unspecified, or logout=3; this kills everything
			session_destroy();
			$_SESSION=array();
			if (isset($_COOKIE['PHPSESSID'])) {
				//set a new cookie
				$newSessionKey = md5(rand(100,100000).$timeStamp);
				setcookie('PHPSESSID', $newSessionKey, time()+42000, '/');
			}
		}
		if($logout<3 && $logoutSessionNodes){
			!is_array($logoutSessionNodes) ? $logoutSessionNodes=explode(',',trim($logoutSessionNodes,',')) : '';
			foreach($logoutSessionNodes as $v){
				if(strtolower($v)=='cnx')continue;
				unset($_SESSION[$v]);
			}
		}
		header('Location: /console/');
		eOK();
	}else if($forgotPassword){
		$heading='Forgotten Password Retrieval';
		$form='forgot';
	}else if($mode=='forgotConsole'){
		$a=q("SELECT UserName, Email, Password, PasswordMD5 FROM ".$MASTER_DATABASE.".addr_contacts WHERE (Email='$UN' OR UserName='$UN')", O_ARRAY);
		if(count($a)>1){
			//multiple matches found, need to address this
		}
		if(!$a[1]['Email']){
			//for auth_i4, take no action - privacy issue
			$errMessage='No email on file to send to!  Contact us at '.$usemod['adminEmail'];
		}else{
			if(file_exists($_SERVER['DOCUMENT_ROOT'].'/emails/email_forgot.php')){
				ob_start();
				if(!$adminCompany)$adminCompany=$GLOBALS['adminCompany'];
				require($_SERVER['DOCUMENT_ROOT'].'/emails/email_forgot.php');
				$body=ob_get_contents();
				ob_end_clean();
				enhanced_mail(
					$a[1]['Email'],
					'Forgotten password request for '.$usemod['siteName'], 
					$body, 
					$usemod['replytoEmail'], 'html', '', '', '', 'mail', $usemod['replytoEmail']
				);
			}else{
				
			}
	
			//redirect
			?><script language="javascript" type="text/javascript">
			alert('A password reset link has been sent to this email');
			window.parent.location='/console/<?php if($src)echo '?src='.urlencode(stripslashes($src));?>';
			</script><?php
		}
		eOK();
	}else if(count($_SESSION['cnx'][$acct]['accesses'])){
		$access=false;
		foreach($_SESSION['cnx'][$acct]['accesses'] as $v){
			foreach($acceptableAccesses as $w){
				if(strtolower($v)==strtolower($w)){
					$access=true;
					break;
				}
			}
		}
		//acceptable accesses
		if($access){
			//OK - as of 2013-06-18, the page itself must determine if the access is sufficient
			return true;
		}else{
			//they may be signed in but they do NOT have access to the console
			$form='login';
			$type='normal';
			$message='Enter your email (or username) and password to log in';
		}
	}else{
		$form='login';
		$type='normal';
		if($usemodReady){
			$message='Enter your email (or username) and password to log in';
		}else{
			$message='Welcome to the console. Enter the username and password given when your account was set up to begin';
		}
	}
	//------------------------------------------------
	if(true){ 
		?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title>Console Sign-In</title>
		<link rel="stylesheet" href="/Library/css/cssreset01.css" type="text/css" />
		<style type="text/css">
		#login{
			width:700px;
			margin:10px auto;
			border-radius:20px;
			padding:20px;
			border:1px solid #ccc;
			}
		#login input[type=text], #login input[type=password]{
			border-width:2px;
			border-radius:5px;
			}
		</style>
		<script language="javascript" type="text/javascript" src="/Library/js/jquery.js"></script>
		<script language="javascript" type="text/javascript" src="/Library/js/global_04_i1.js"></script>
		<script language="javascript" type="text/javascript" src="/Library/js/common_04_i1.js"></script>
		<script language="javascript" type="text/javascript" src="/Library/js/forms_04_i1.js"></script>
		<script language="javascript" type="text/javascript">
		var submitting=true;
		userWillTolerate=20; //user waits this long in seconds for form to process
		$(document).ready(function(){
			$('#form1').submit(function(){
				submitting=true; //set state of form to submitting
				try{
					//time
					var yom=new Date();
					g('postTime').value=yom;
		
					//environment
					EXs=screen;
					var sw=EXs.width;
					var sh=EXs.height;
					navigator.appName!="Netscape"? cd=EXs.colorDepth:cd=EXs.pixelDepth;
					g('environment').value=sw+'x'+sh+'; '+cd;
				}catch(e){  }
				//2011-07-25 - develop this??
				//setTimeout('submitFail()',userWillTolerate*1000);
			});
			$('#UN').focus();
		});
		//not used but should be developed
		function submitFail(){
			if(!submitting)return;
			//window.body.cursor.style='pointer'; 
			try{
			g('Submit1').disabled=false;
			g('SubmitStatus1').innerHTML=' ';
			}catch(e){ }
			alert('There was an error in logging in; it looks like you were not successful; please refresh this page (Control-R or F5) and try one more time.');
			w2.location='email_emergency.php';
			submitting=false;
		}
		</script>
		</head>
		<body><?php
	}
	if(!$src){
		//hmm, redirect back to this page then
		$url=($GLOBALS['REDIRECT_URL']?$GLOBALS['REDIRECT_URL']:$GLOBALS['REQUEST_URI']);
		$src=$url.($GLOBALS['QUERY_STRING']?'?'.$GLOBALS['QUERY_STRING']:'');
	}
	?>
	<form id="form1" method="post" target="w2" action="/console/resources/bais_01_exe.php">
	<div id="login">
	<h1><?php echo $heading?$heading:'Sign In';?></h1>
	<div id="message" class="<?php echo $type;?>">
	<?php echo $message;?>
	</div>
	<!-- need CMSB here -->

	<div id="content">
	<?php
	if($form=='login'){
		?><div>
		Email or username: 
		<input name="UN" type="text" id="UN" value="<?php echo h($UN);?>" />
		<br />
		Password: 
		<input name="PW" type="password" id="PW" />
		<input name="postTime" type="hidden" id="postTime" value="<?php echo stripslashes(h($postTime));?>" />
        <input name="environment" type="hidden" id="environment" value="<?php echo stripslashes(h($environment));?>" />
        <br />
		</div><?php
	}else if($form=='setup'){
		?><div>
		Your console has not been set up with a primary user.<br />
		<?php
		if($a=q("SELECT ID, FirstName, LastName, Email FROM addr_contacts WHERE Email!='' AND LastName!='' AND FirstName!='' ORDER BY LastName, FirstName", O_ARRAY_ASSOC)){
		?>
		Select a current user as an administrator: <select name="Contacts_ID" id="Contacts_ID">
		<option value="">&lt;Select..&gt;</option>
		<?php
		foreach($a as $n=>$v){
			?><option value="<?php echo $n?>"><?php
			$b=array();
			if(trim($v['LastName']))$b[]=$v['LastName'];
			if(trim($v['FirstName']))$b[]=$v['FirstName'];
			echo implode(', ',$b).($v['Email']?' - '.$v['Email']:'');
			?></option><?php
		}
		?>
		</select><br />
		Or, set up a new user:<br />
		<?php
		}
		?>
		<div style="float:right;border:1px solid #666;width:250px;padding:15px 10px;">
		<h3>Password</h3>
		<p>Your password must be at least 8 characters long and include letters and numbers</p>
		Password: 
		<input name="newPW" type="password" id="newPW" size="10" />
		<br />
		re-type: 
		<input name="newPW2" type="password" id="newPW2" size="10" />
		<br />
		</div>
		First name: 
		<input name="FirstName" type="text" id="FirstName" value="<?php echo h($FirstName);?>" />
		<br />
		Last name: 
		<input name="LastName" type="text" id="LastName" value="<?php echo h($LastName);?>" />
		<br />
		Email: 
		<input name="Email" type="text" id="Email" value="<?php echo h($Email);?>" />
		<br />
		Cell phone: 
		<input name="HomeMobile" type="text" id="HomeMobile" value="<?php echo h($HomeMobile);?>" />
		<br />
		Home phone: 
		<input name="HomePhone" type="text" id="HomePhone" value="<?php echo h($HomePhone);?>" />
		<br />
		Work phone: 
		<input name="BusPhone" type="text" id="BusPhone" value="<?php echo h($BusPhone);?>" />
		<br />
		<strong>Address</strong><br />
		<label>
		<input name="addr" type="radio" value="Home" checked="checked" /> 
		home</label>
		&nbsp;&nbsp;&nbsp;
		<label>
		<input name="addr" type="radio" value="Bus" /> 
		work</label>
		<br />
		Street: 
		<input name="Address" type="text" id="Address" value="<?php echo h($Address);?>" />
		<br />
		City: 
		<input name="City" type="text" id="City" value="<?php echo h($City);?>" /> 
		State: 
		<select name="State" id="State" onChange="countryInterlock('State','State','Country');" style="width:150px;">
		<option value="" class="gray">&lt;Select state..&gt;</option>
		<?php 
		$states=q("SELECT st_code, st_name FROM aux_states",$public_cnx,O_COL_ASSOC);
		foreach($states as $n=>$v){
			?><option value="<?php echo $n?>" <?php echo $State==$n?'selected':''?>><?php echo $v;?></option><?php
		}
		?>
		</select>
		Zip:
		<input name="Zip" type="text" id="Zip" value="<?php echo h($Zip);?>" size="5" />
		<br />
		Country:
		<select name="Country" id="Country" onChange="countryInterlock('Country','State','Country');">
		<option value="" class="gray">&lt;Select country..&gt;</option>
		<?php 
		$countries=q("SELECT ct_code, ct_name FROM aux_countries",$public_cnx,O_COL_ASSOC);
		foreach($countries as $n=>$v){
			?><option value="<?php echo $n?>" <?php echo $Country==$n || (!$Country && $n=='USA')?'selected':''?>><?php echo $v;?></option><?php
		}
		?>
		</select>
		<input name="UN" type="hidden" id="UN" value="<?php echo h(stripslashes($UN));?>" />
		<input name="PW" type="hidden" id="PW" value="<?php echo h(stripslashes($PW));?>" />
		<input name="postTime" type="hidden" id="postTime" value="<?php echo stripslashes(h($postTime));?>" />
		<input name="environment" type="hidden" id="environment" value="<?php echo stripslashes(h($environment));?>" />
		<br />
		<?php
	}else if($form=='forgot'){
		?>
		Enter the email address associated with your account.  If the email is on file, you will receive a link to reset your password.<br />
		<input name="UN" type="text" id="UN" value="<?php echo h($UN);?>" size="40" />
		<?php
	}
	?>
	<input name="mode" type="hidden" id="mode" value="<?php echo $form;?>Console" />
	<input name="src" type="hidden" id="src" value="<?php echo h(stripslashes($src));?>" />
	<input type="submit" name="Submit" value="Submit" />
	</div>
	<br />
	<br />
	<?php if($form!=='forgot'){ ?>
	<a href="/console/?forgotPassword=1&src=<?php echo urlencode(stripslashes($src));?>">Forgotten your password?</a>
	<?php } ?>
	</div>
	</div>
	</form>
	<?php
	if($form=='setup'){
		?><script language="javascript" type="text/javascript">
		try{
		window.parent.document.getElementById('content').innerHTML=document.getElementById('content').innerHTML;
		}catch(e){}
		</script>
        <?php
	}
	?>
	<div style="display:none;">
	<iframe id="w2" name="w2"></iframe>
	</div>
	<?php
	if(true){
		?></body>
	</html><?php
	}
	eOK();
}
//call the function
$a=array();
if(isset($UN))$a['UN']=$UN;
if(isset($PW))$a['PW']=$PW;
if(isset($authKey))$a['authKey']=$authKey;
if(isset($src))$a['src']=stripslashes($src);
if(isset($logout))$a['logout']=$logout;
if(isset($mode))$a['mode']=$mode;
$loginOK=usemod($a);

