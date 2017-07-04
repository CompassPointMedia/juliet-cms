<?php
/*
#--------------------------------------------------------#
Big overview of cgi. At 11:09PM 8/22 during the "breaking and doing" phase of my life..
CGI/usemod was created as a grand scheme to allow site owners to configure multiple ways the end users could sign in and modify their information in conjunction with other applications that would naturally be on their site, including e-commerce and e-newsletters.  There was a vast array of proxy login settings, adding a record by proxy, verification modes, and the like.  I spent years on this and as of 8/22/2012 It's still not finished.  It is time to look at the big picture and see what CGI/usemod needs to be.

Currently 8/22 the pices are a component in RelateBase (that is not GUI-editable), and some files in components-juliet.  The main things that need to be done are:
1. we need a form to modify the values
2. we need documentation of each feature, precisely
3. we need to be able to develop forms and fields on them in a settings type of way. The forms are completely inadequate for the range of needs my clients have.  SO FOR THAT MATTER, we need {forms::baseball_form} type variables in juliet that CMSB can call or we could call in PHP development as say vars('forms','baseball_forms');

2013-05-19: now I am bringing the component file logic in so that usemod will have a settings interface
VERY IMPORTANT: first time doing this, created {cgi_hub} system node so that we can store settings.  thispage and thisfolder are declared, but thisnode for cgi is NOT.  Also this virtual page will probably be sunsetted when I finish Juliet 2.0, but I needed a place to store values using a componentfile, and did not want to use the module anymore.


#--------------------------------------------------------#

2012-08-22
* went through and did a major pare-down of usemod variables, recall that these can be modified by relatebase.com/admin and modify settings for CGI-70
* there is a piece of knowledge I don't want to lose and that is 'RBStatusField' where I could go to relatebase-user.com and log in to any account from there.  This was also associated with what I attempted for R iverT reeE states so that they would have an equivalent of Yahoo groups.  There are tables and fields associated with this that join the two databases, as well as a WHOLE BUNCH OF SETTINGS in the rbase_ tables for individual users of modules.  So they both go together.
* there is also the issue of presumed identity and squish login.  This was only nearly implemented for 360P ressS olutions.  This requires a code call on any page that needs this type of login.


2012-01-28
* changed meaning of useEnrollmentConfirmation to 1=only for resellers and 2=for everyone



Todo:
-----
phase out consoleEmbeddedModules - no need for this to be a relatebase module - shoudl be an inherent part of Juliet
get a handle on {forms::variable}, as part of the Tigris project of documenting all these
to reset password, they only need to enter their old password if they are in a "squish" login
would you like to be a reseller? you are in the wrong place! click here to go to the reseller form
privacy policy page by default

DONE	2012-01-25	flot the ecommerce items
DONE	2012-01-25	click here to go to your cart
DONE	2012-01-25	big fields on shopping cart
DONE	2012-01-25	colors on the table + styling
DONE	2012-01-25	permanently show-hide edit links
DONE	2012-01-25	and apply to all
DONE	2012-01-25	creator should be that username on insert

bug: Your opportunity to confirm your email address has expired


2012-08-22; are these still valid?
---------------------------------
sign up did not set WHSLE_PENDING
add_result is blank - messages assoc with it

set settings so wholesale optional but showable
pull this and configure layout
basic texts for the wholesale process
verify mail process and approval

*/
$handle='usemod';
$version='2.0';

/* --------------------------- Handle different types of logins -------------------------------
this begins to integrate usemod with financial in a many-to-many relationship */
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

define('EMAIL_CHECK_NONE',0);
define('EMAIL_CHECK_NEW',5);
define('EMAIL_CHECK_EXISTING',10);

define('WHSLE_NO',0);
define('WHSLE_REJECTED',1);
define('WHSLE_PENDING',4);
define('WHSLE_APPROVED',8);

define('idlevel_anonymous',-16); 			//we have no cookie on them
define('idlevel_cookied_unknown',-8);		//we have a cookie but they have never been identified
define('idlevel_cookied_assumed',-4);		//we have a cookie and assume they are someone - but don't interact with them as "them"
define('idlevel_cookied_squishy_login',-2);	//we interact with them via squishy login - but obviously not secure
define('idlevel_softlogin',-1);				//from an email link with UN & authToken and possibly a timeout - but not secure
define('idlevel_hard_login',0);				//from submitting a form.  They have manually logged in - assumed secure

if(!function_exists('cgi_message_manager')){
function cgi_message_manager($message,$CMSMessage,$options=array()){
	/*2013-05-27
	
	*/
	extract($options);
	global $mode,$separateProxy,$acct;
	//defaults
	ob_start();
	//-----------------------------------
	if(false){ ?><div style="display:none;"><?php } 
	switch(strtolower($message)){
		case 'loginheader':
			?>
			<h2>Sign In</h2>
			<p>Enter your email and password</p>
			<?php
		break;
		case 'loginfooter':
		break;
		case 'newaccountheader':
			?><h2>Don't have an account?</h2><?php
		break;
		case 'formheader':
			if($_SESSION['cnx'][$acct]){
				?><h1>Update Your Information</h1>
				<p>Fields marked with an asterisk are required.  Thanks for keeping your information current, we appreciate your effort!</p><?php
			}else{
				?><h1>Create an Account</h1>
				<p>Fields marked with an asterisk are required.  Be sure and enter your correct email address.  We appreciate your effort!</p><?php
			}
		break;
		case 'formfooter':
		
		break;
	}
	if(false){ ?></div><?php } 
	//------------------------------------
	$defaultContent=ob_get_contents();
	ob_end_clean();
	//output
	if(preg_match('/^set/i',$CMSMessage)){
		CMSB(array(
			'section'=>'cgi_'.$message.$mode.("in proxy state" && false && $separateProxy?'proxy':'').preg_replace('/^set/i','',strtolower($CMSMessage)),
			'defaultContent'=>$defaultContent,
		));
	}else echo $defaultContent;
}
}


//----------------- taken from calendar_v200.php 2013-05-19 ----------------------
//let's go ahead and register the component if not done
if(!($_Components_ID_=q("SELECT ID FROM gen_components WHERE Handle='$handle' AND Version='$version'", O_VALUE))){
	mail($developerEmail, 'Warning in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err='component was registered on-the-fly'),$fromHdrBugs);
	$f=explode('/',__FILE__);
	$ComponentFile=array_pop($f);
	$Location=(end($f)=='components-juliet'?'JULIET_COMPONENT_ROOT':(end($f)=='components'?'COMPONENT_ROOT':end($f)));
	$_Components_ID_=q("INSERT INTO gen_components SET Handle='$handle', Version='$version', Location='$Location', ComponentFile='$ComponentFile', CreateDate=NOW(), Creator='".sun()."'", O_INSERTID);
	prn($qr);
}
if(!$thisnode){
	if(!($thisnode=q("SELECT ID FROM gen_nodes WHERE SystemName='{cgi_hub}'", O_VALUE))){
		$thisnode=q("INSERT INTO gen_nodes SET
		Active=8, SystemName='{cgi_hub}', Type='Object', Category='Website Page', 
		CreateDate=NOW()",O_INSERTID);
		mail($developerEmail, 'Warning in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err='a virtual node with systemname {cgi_hub} was created in gen_nodes for this account so that usemod could be linked in gen_ComponentsNodes'),$fromHdrBugs);
	}
}

//let's get our data
if($Parameters=q("SELECT cn.Settings FROM gen_components c LEFT JOIN gen_ComponentsNodes cn ON c.ID=cn.Components_ID WHERE c.ID='$_Components_ID_' AND cn.Nodes_ID='".($_thisnode_ ? $_thisnode_ : $thisnode) ."'", O_VALUE)){
	$Parameters=unserialize(base64_decode($Parameters));
	if(!empty($Parameters))$pJ['componentFiles'][$handle]=$Parameters;
}else{
	unset($pJ['componentFiles'][$handle]);
}
//---------------------------------------------------------------------------------

//2013-05-29 no longer associated with $usemod
$umContactCategory=pJ_getdata('umContactCategory',NULL);
$umResellerWord=pJ_getdata('umResellerWord','Reseller');
$umUpdateButtonText=pJ_getdata('umUpdateButtonText','Update Information');
$umInsertButtonText=pJ_getdata('umInsertButtonText','Create Account');
$umPremiumMemberWord=pJ_getdata('umPremiumMemberWord','Premium Member');

/*
2012-07-01: this is the initial state of the usemod array.  MANY of these settings are unnecessary or not useful/conflicting, and over the month of July I will be paring this down.  However, lacking some values such as usemod[primaryKeyValue] might cause things to fail in some cases.  I am looking only through the cgi.x suite of component files, but there may be gotchas out there in files or DB's as well..

*/
//2013-09-11: I am phasing out $consoleEmbeddedModules; this should not be controlled in relatebase
$overrideConsoleEmbeddedModules=array(
	'cpm192',
	'cpm184',
	'cpm185',
);
$usemod=array(
	'logmail' => pJ_getdata('logmail',true),
	'usemodURLRoot' => 'http://'.$SERVER_NAME.'/cgi',
	'replytoEmail' => '',
	'errFromHdr' => pJ_getdata('errFromHdr',$fromHdrBugs), /*2013-09-11*/
	'CGI_COMPONENT_ROOT' => '',
	'masterGuestPassword' => '',
	'pendingApplicationEmail' => '',
	'acceptableLoginIdentities' => array (
	  'Superadministrator',
	  'Administrator',
	  'Assistant',
	  'User',
	  'Guest',
	),
	'sessionCnxNodeParameters' => array (),
	'loginUserNameTreatment' => 'Email',
	'loginUserNameLabel' => '',
	
	'parseOptionFields' => '',
	'nonPassedFieldNames' => array (
	  0 => 'NewsletterOK',
	),
	'contactInsertFieldDefaults' => array (),
	'allowGrants' => pJ_getdata('allowGrants',false),
	'acceptablePermissionTypes' => array (
	  0 => 5,
	  1 => 10,
	  2 => 15,
	  3 => 20,
	  4 => 25,
	),
	'showCompanyField' => pJ_getdata('showCompanyField',false),
	'showMiddleName' => pJ_getdata('showMiddleName',true),
	'showCountry' => pJ_getdata('showCountry',false),
	'showNotes' => pJ_getdata('showNotes',false),
	'showNewsLetterPreferences' => pJ_getdata('showNewsLetterPreferences',false),
	'integrateWithFinancial' => pJ_getdata('integrateWithFinancial',true),
	'backIntegrateWithFinancial' => pJ_getdata('backIntegrateWithFinancial',false),
	'allowAsNewEntries' => pJ_getdata('allowAsNewEntries',false),
	'allowLoginForDuplicateUserTokens' => pJ_getdata('allowLoginForDuplicateUserTokens',true),
	'checkUniqueEmail' => pJ_getdata('checkUniqueEmail',EMAIL_CHECK_NONE), /*2013-09-11: default EMAIL_CHECK_NONE*/
	'autoGenerateUsername' => pJ_getdata('autoGenerateUsername',true),
	'autoGeneratePassword' => pJ_getdata('autoGeneratePassword',false),
	'additionalGeneralWhereClause' => '',
	'formHeaderTemplate' => '_usemod_formheader_default.php',
	'formTemplate' => '_usemod_form_default.php',
	'formFooterTemplate' => '_usemod_formfooter_default.php',
	'contactClientTranslator' => '_usemod_contact_client_translator_default.php',
	'loginSimpleHeadProcessor' => 'echo $loginHTMLHead;',
	'loginSimpleAboveProcessor' => pJ_getdata('loginSimpleAboveProcessor',true),
	'wholesaleToken' => pJ_getdata('wholesaleToken',false),
	'presentWholesaleFields' => pJ_getdata('presentWholesaleFields',true),
	'wholesaleValuesNotNeeded' => pJ_getdata('wholesaleValuesNotNeeded',false),
	'allowPresentWholesaleFields' => pJ_getdata('allowPresentWholesaleFields',true),
	'allowImmediateWholesaleAccess' => pJ_getdata('allowImmediateWholesaleAccess',false),
	'proxyLoginAllow' => pJ_getdata('proxyLoginAllow',true),
	'proxyLoginPresentationMethod' => 1,
	'proxyLoginObject' => 'Member',
	'EmailLogo' => 'images/assets/emaillogo.png',
	'EmailHeader' => '',
	'email_001_please_reply_to_confirm_email' => 'email_001.php',
	'email_002_please_reply_to_confirm_then_reviewed' => 'email_002.php',
	'email_003_please_reply_to_confirm_then_approved' => 'email_003.php',
	'email_004_immediate_welcome' => 'email_004_approved.php',
	'email_005_welcome_wholesale_to_approve' => 'email_005.php',
	'email_006_immediate_welcome_wholesale' => 'email_006.php',
	'email_007_admin_notification' => 'email_007_admin_notice.php',
	'email_review_approve_reject' => 'email_review_approve_reject.php',
	'email_you_are_approved_rejected' => 'email_approve-reject.php',
	'useEnrollmentConfirmation' => pJ_getdata('useEnrollmentConfirmation'),
	'EEATOverride' => '',
	'insertAutoLogin' => pJ_getdata('insertAutoLogin',true),
	'EnrollmentConfirmationEncryptor' => '',
	'signinAfterEnrollmentConfirmation' => pJ_getdata('signinAfterEnrollmentConfirmation',true),
	'allowRepeatEnrollmentLinkToSignin' => pJ_getdata('allowRepeatEnrollmentLinkToSignin',true),
	'EnrollmentAuthDuration' => 7,
	'forceEnrollmentConfirmationRedirect' => pJ_getdata('forceEnrollmentConfirmationRedirect',true),
	'enrollmentConfirmationRedirect' => '',
	'allowPersistentLogin' => pJ_getdata('allowPersistentLogin',true),
	'allowPersistentSoftLogin' => pJ_getdata('allowPersistentSoftLogin',false),
	'persistentLoginEffectivePeriod' => 6,
	'idTokenLength' => 13,
	'persistentLoginThreshold' => 100,
	'postInsertRedirect' => '/cgi/addresult',
	'loginCodeMessage' => array (
	  -2147483648 => 'There is a problem with the database at this time (data uniqueness conflict error).  An administrator has been notified, please try again later',
	  0 => 'Your login was unsuccessful',
	  15 => 'The link to confirm your email address automatically was not valid',
	  30 => 'Your opportunity to confirm your email address has expired',
	  45 => 'You must confirm your email address first by following the link that was sent to you by email',
	  60 => 'Your email has already been verified',
	  75 => 'Your login was unsuccessful',
	  100 => 'Your email has now been verified',
	  200 => 'Your login was successful',
	),
	'requiredFields' => array (
	  0 => 'title',
	  1 => 'company',
	  2 => 'firstname',
	  3 => 'lastname',
	  4 => 'email',
	  5 => 'password',
	  6 => 'homeaddress',
	  7 => 'homecity',
	  8 => 'homestate',
	  9 => 'homezip',
	  10 => 'homecountry',
	  11 => 'homephone',
	),
	'ec' => array (
	  'HomeAddressOptional' => pJ_getdata('HomeAddressOptional',true),
	  'BusAddressOptional' => pJ_getdata('BusAddressOptional',true),
	  'EmailOptional' => pJ_getdata('EmailOptional',true),
	  'HomeMobileOptional' => pJ_getdata('HomeMobileOptional',true),
	  'BusPhoneOptional' => pJ_getdata('BusPhoneOptional',true),
	),
	'errorCheckingTemplate' => '_usemod_error_checking_default.php',
	/* these are added 2013-09-22 after I found that at least a few of them are still needed such as dbLoginFieldPrimary */
	'config'=>'/home/cpm163/public_html/config.php',
	'currentVersion'=>'2.8.6',
	'memberWord'=>'Member',
	'rootTableName'=>'addr_contacts',
	'primaryKeyField'=>'ID',
	'localStatusField'=>'RBStatus',
	'usemodFirstNameField'=>'FirstName',
	'usemodLastNameField'=>'LastName',
	'dbLoginFieldPrimary'=>'UserName',
	'dbLoginFieldSecondary'=>'Email',
	'dbPasswordField'=>'PasswordMD5',
	'useMD5ForPassword'=>true,
	'identifyCreator'=>true,
	'addressPrefix'=>'Home',
	'adminModeToken'=>'$_SESSION[\'special\'][$usemod[\'database\']][\'adminMode\']',
	'minimumPasswordLength'=>5,
);

function node_compare($a,$b,$options=array()){
	extract($options);
	if(!$output)$output='table';
	if(is_array($a))
	foreach($a as $n=>$v){
		$keys[strtolower($n)]['a']=$v;
		$keys[strtolower($n)]['key']=$n;
		$case[strtolower($n)]=$n;
	}
	if(is_array($b))
	foreach($b as $n=>$v){
		$keys[strtolower($n)]['b']=$v;
		if(!$keys[strtolower($n)]['key'])$keys[strtolower($n)]['key']=$n;
		if(isset($keys[strtolower($n)]['a'])){
			$keys[strtolower($n)]['equal']=($keys[strtolower($n)]['a']==$v?1:0);
			if($case[strtolower($n)]!==$n)$keys[strtolower($n)]['case_mismatch']=true;
		}
	}
	if($output=='array')return $keys;
	if($output=='table'){
		?><table><thead><tr>
		<th>Key</th><th>A</th><th>B</th><th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th>
		</tr></thead>
		<tbody>
		<?php
		foreach($keys as $key=>$v){
			?><tr><td><?php
			echo $v['key'];
			?></td><td><?php
			echo isset($v['a'])?'Y':'&nbsp;';
			?></td><td><?php
			echo isset($v['b'])?'Y':'&nbsp;';
			?></td><td><?php
			if($v['case_mismatch'])echo '*';
			?></td><td><?php
			echo isset($v['a']) && isset($v['b']) ? ($v['equal']?'equal':'') : '&nbsp;';
			?></td><td><?php
			if(isset($v['equal'])){
				if($v['equal']){
					echo $v['a'];
				}else{
					echo $v['a'].':'.$v['b'];
				}
			}else{
				echo isset($v['a'])?$v['a']:$v['b'];
			}
			?></td></tr><?php
		}
		?>
		</tbody></table><?php
	}
}
$usemod_merge=array();
if($consoleEmbeddedModules){
	foreach($consoleEmbeddedModules as $n=>$v){
		if(in_array($acct,$overrideConsoleEmbeddedModules))continue;
		if($v['SKU']=='CGI-70' && is_array($v['moduleAdminSettings']['usemod'])){
			mail($developerEmail, 'Legacy in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err='the cgi.php component is referencing the "CGI-70" module. Check if this has useful information, also check the console for editing features, and add this $acct to overrideConsoleEmbeddedModules in the file.  PHASE THIS OUT by end of 2013'."\n\n"),$fromHdrBugs);
			$usemod_merge=$v['moduleAdminSettings']['usemod'];
		}
	}
}
if(false && $UN=='cmccallister'){
	node_compare($usemod,$usemod_merge);
	exit;
}
if(!empty($usemod_merge))$usemod=array_merge_accurate($usemod,$usemod_merge);
if(!$overrideUsemodSettingsMessages)require_once($_SERVER['DOCUMENT_ROOT'].'/components-juliet/cgi.messages.php');
if(!$usemod['replyToEmail'])$usemod['replytoEmail']='info@'.$siteDomain;
//this is a wholesaler's account - NOTE THIS FIELD IS USED IN THE SHOPPING CART ALSO!!!
$whsle=(
	($usemod['wholesaleToken'] && $usemod['presentWholesaleFields']==8) || 
	($usemod['wholesaleToken'] && $usemod['presentWholesaleFields']>=4 /*allow*/ && $presentWholesale=='1') || 
	($mode=='update' && $WholesaleAccess && trim($WholesaleNumber) && trim($WholesaleAccess)!=WHSLE_REJECTED)
);
//Email Enrollment Authorization Token
$useEEAT=(
	intval($usemod['useEnrollmentConfirmation']) >= (isset($_POST['WholesaleAccess']) ? 1 : 2) 
	&& 
	!(strlen($usemod['EEATOverride']) && $EEATOverride== md5(strtolower(trim($Email)). $usemod['EEATOverride'])) ? 
	true : 
	false
);

//override EEAT if called for in admin mode
if($usemod['allowAdminMode'] && $adminMode && $_POST['_AutoVerifyEEAT'])$useEEAT=false;

//2012-09-20: set proxy login default
if(isset($proxy) && $proxy==='0'){
	setcookie('proxy',0,time()+(3600*24*60));
}else if($proxy){
	setcookie('proxy',$proxy,time()+(3600*24*60));
}else if($proxy=$_COOKIE['proxy']){
	//OK
}

for($__i__=1; $__i__<=1; $__i__++){ //---------------- begin i break loop ---------------

//where should this go?
if($adminMode && $_POST['_sendEmail']=='2' && !valid_email($_POST['_sendEmailTo']))error_alert('You selected an alternate email to send notifications to; however it is not valid.  Please change your selection or enter a valid email address');

if($comboMode=='insertUpdate'){

    exit('point 1');

	if(isset($_q) && isset($_r)){
		//jasperandwendy
		$r=$_POST['_res'][$_POST['_r']];
		$q=$_POST['_q'];
		for($i=2; $i<=min($r-2,22); $i++){
			if( round(sqrt($i) / pow($r - $i, .3333),4) == round($q,4))$pass=true;
		}
		if(!$pass)error_alert('You are either not a human being or you made a simple math error.  Hit the "back" button on your browser and try again.');
	}
	//for email logging - 2013-09-11 I am not sure how often this was used, only in cpm160 PENN
	if(file_exists($_SERVER['DOCUMENT_ROOT'].'/components-juliet/'.$acct.'.cgiprecoding.php')){
		require($_SERVER['DOCUMENT_ROOT'].'/components-juliet/'.$acct.'.cgiprecoding.php');
	}
	$enhanced_mail['logmail']=$usemod['logmail'];

	if($mode=='update' && (!(strlen($_SESSION['identity']) || !count($_SESSION['cnx'][$cnxKey])))){
		?><script language="JavaScript" type="text/javascript">
		alert('We\'re sorry, your session has timed out.  Please go to signin and return to edit your information');
		</script><?php
		$assumeErrorState=false;
		exit;
	}		
	if(count($_SESSION['cnx'][$cnxKey]) /* signed in */){
		if(($_SESSION['cnx'][$cnxKey]['allowGrants'] && $mode=='insert') ||
			($usemod['allowAsNewEntries'] && $_POST['addNewEntry'] /* this normally comes from the query string */)
			/* I am adding a contact by some token, or my login is to be ignored */){
			
			//insert the record - include the fact that I have added the record
			$mode='insert';

			/*
			error checking - vars declared as necessary to trigger actions in the required file
			*/
			require(str_replace('cgi.php','cgi.error_checking_default.php',__FILE__));

			//this sets $Contacts_ID
			require(str_replace('cgi.php','cgi.comp_insert_update_codeblock_01.php',__FILE__));
					
			//handle approval and EEAT process done below
			
			if($a=$_SESSION['cnx'][$cnxKey]['companyPrimaryKeyValues']){
				$i=0;
				foreach($a as $n=>$v){
					$i++;
					if(strtolower($v)=='primary')$Clients_ID=$n;
				}
			}
			if(($i==1 && $Clients_ID) ||
				($_SESSION['special']['companyJoinID'])
				/* either I am a primary (and not specifically excluded) or a specific company join is specified */){
				//create the record in finan_clients
				
				//join the records in the specified relationship
				
				
			}
		}else if(false /* I am updating another contact */){
			//not developed
		
		}else if(false){
			//some other condition
			
		}else{
			//update the record
			$mode='update';

			/*
			error checking - vars declared as necessary to trigger actions in the required file
			*/
			require(str_replace('cgi.php','cgi.error_checking_default.php',__FILE__));

			//security
			$ID=$_SESSION['cnx'][$cnxKey]['primaryKeyValue'];
			$UserName=q("SELECT UserName FROM addr_contacts WHERE ID='".$_POST['ID']."' ".$usemod['additionalGeneralWhereClause'], O_VALUE);
			unset($_POST['Password'], $_POST['PasswordMD5'], $_POST['Password_MD5']);

			require(str_replace('cgi.php','cgi.comp_insert_update_codeblock_01.php',__FILE__));

			//translate
			require(str_replace('cgi.php','cgi.contact_client_translator_default.php',__FILE__));			
			
			//only adminMode can select the terms using this form
			if(!$usemod['allowAdminMode'])unset($_POST['Terms_ID']);
	
			if($usemod['integrateWithFinancial']){
				if($companies=q("SELECT Clients_ID, Type FROM finan_ClientsContacts WHERE Contacts_ID='$Contacts_ID'",O_COL_ASSOC)){
					//they already have tie(s) to finan_clients - we can at minimum update their names
					foreach($companies as $n=>$v){
						if(strtolower($v)!=='primary')continue;
						q("UPDATE finan_clients SET 
						".(count($companies)==1 && isset($ClientName)? "ClientName='".$ClientName."'," : '')."
						".(count($companies)==1 && isset($CompanyName) ? "CompanyName='".$CompanyName."'," : '')."
						PrimaryFirstName='$FirstName', 
						PrimaryLastName='$LastName', 
						Email='$Email' 
						WHERE ID='$n'");
						prn($qr);
					}
					if(count($companies)>1){
						ob_start();
						print_r($GLOBALS);
						$out=ob_get_contents();
						ob_end_clean();
						mail($developerEmail,'More than one company for a usemod user','File: '.__FILE__.', Line: '.__LINE__."\n\n".$out,'From: bugreports@'.$siteDomain);
					}
				}else if($usemod['backIntegrateWithFinancial']){
					//translate

					$sql=sql_insert_update_generic($MASTER_DATABASE, 'finan_clients', 'INSERT', $options=array('setCtrlFields'=>true, 'addslashes'=>false));
					//finan_clients entry
					$ln=__LINE__+1;
					$Clients_ID = q($sql, C_MASTER, O_INSERTID);
					prn($qr);
					//join the two
					$ln=__LINE__+1;
					q("INSERT INTO finan_ClientsContacts SET Contacts_ID='$Contacts_ID', Clients_ID='$Clients_ID', Type='primary', Notes='Added by ".end(explode('/',__FILE__)).'\'');
					
				}
			}
		}
	}else{
		//not signed in, user is adding himself
		$mode='insert';
		if($_REQUEST['proxy']){
			if(!$usemod['proxyInsertAllow'])error_alert('Proxy record inserts are not allowed for the system.  Please go to your console and select Members > User Signin Settings to activate proxy record inserts');
			if(!strlen($UN) || !strlen($PW))error_alert('You must enter your user name and password to create a proxy record');
			if($usemod['proxyLoginPresentationMethod]']==2 && !$adminMode)error_alert('You must be in admin mode (i.e. site editor mode) to create a proxy record');
			
			//now do the login but DO NOT set sessionvars at this point
			$overrideSetLoginVars=true;
			unset($proxyA_login,$proxyB_login);
			require(str_replace('cgi.php','cgi.comp.login_b.php',__FILE__));
			if(!$loginOK)error_alert('Your proxy login was not successful.  Check your username or password and try again, or do not use proxy login to create a record');

			//they must be on the list
			if(!in_array(strtolower($b_login['systemUserName']),explode(',',strtolower(preg_replace('/[^,a-z0-9]/i','',$usemod['proxyUserNames'])))))error_alert('You are not on the authorized username list to add a proxy record insert');

			$proxyA_login=$a_login;
			$proxyB_login=$b_login;

			//we are now authorized to create the record and be listed as the proxy via $a_login and $b_login
			$proxyLoginToken=md5($MASTER_PASSWORD);
		}
		/*
		error checking - vars declared as necessary to trigger actions in the required file
		*/
		require(str_replace('cgi.php','cgi.error_checking_default.php',__FILE__));

		if(false){
			//set a salesreps_ID
			$rep=q("SELECT a.FirstName, a.LastName, a.Email, a.Salesreps_ID, b.RepCode FROM addr_contacts a LEFT JOIN finan_salesreps b ON a.ID=b.Contacts_ID WHERE a.ID=$defaultSalesreps_ID", O_ROW);
			$Salesreps_ID=$rep['Salesreps_ID'];
		}

		//2010-02-23: hard-code/set certain values on insert
		if(count($usemod['contactInsertFieldDefaults'])){
			foreach($usemod['contactInsertFieldDefaults'] as $n=>$v){
				$$n=addslashes($v);
			}
		}

		//this sets $Contacts_ID
		require(str_replace('cgi.php','cgi.comp_insert_update_codeblock_01.php',__FILE__));

		//translate			
		require(str_replace('cgi.php','cgi.contact_client_translator_default.php',__FILE__));

		//only adminMode can select the terms using this form
		if(!$usemod['allowAdminMode'])unset($_POST['Terms_ID']);

		if($usemod['integrateWithFinancial'] /* token to integrate with financial */){
			//add the company
			$sql=sql_insert_update_generic($MASTER_DATABASE, 'finan_clients', 'INSERT', $options=array('setCtrlFields'=>true, 'addslashes'=>false));
			$ln=__LINE__+1;
			$Clients_ID = q($sql, C_MASTER, O_INSERTID);
			prn($qr);
			
			//join the two
			$ln=__LINE__+1;
			q("INSERT INTO finan_ClientsContacts SET Contacts_ID='$Contacts_ID', Clients_ID='$Clients_ID', Type='primary', Notes='Added by ".end(explode('/',__FILE__)).'\'');
			prn($qr);
		}
	}
	//handle approval and EEAT
	/*
	An organization has two basic drives; one the one hand they want to get as many people into their organization as possible and on the other hand they want to approve some or all of those people for a "token" of some kind - in this case the token is wholesale access, where WholesaleAccess=1 in their record.
	There are several flows possible here:
	1. apply -> EEAT -> auto-accepted but not for wholesale token -> sent to admin for wholesale token approval
					1. accepted generally but not for wholesale token
					2. accepted for wholesale token
					3. rare case, reject entirely (i.e. revoke acceptance)
	2. apply -> EEAT -> sent to admin for [wholesale token] approval
					1. accept w/wholesale token
					2. accept but not w/wholesale token
					3. reject
	3. apply ->  sent to admin for [wholesale token] approval [1]
					(send something to the user)
					1. accept w/wholesale token
					2. accept but not w/wholesale token
					3. rare case, reject entirely (i.e. revoke acceptance)
	4. apply -> sent to admin for [wholesale token] approval
					(send something to the user)
					(same as for 3 - then send out EEAT) [2]
								
	
	[1] no EEAT
	[2] method 4 is not likely to be used and not addressed in usemod 2.8
	
	*/
	if($mode=='insert'){
		//determine reseller call and allowable values
		switch(true){
			case !$usemod['wholesaleToken']:
			case !isset($_POST['WholesaleAccess']):
				$wholesale=WHSLE_NO; 
			break;
			case $usemod['allowImmediateWholesaleAccess']:
			case $usemod['allowAdminMode'] && $adminMode && $_POST['_AutoApproveWholesale']:
				$wholesale=WHSLE_APPROVED; 
			break;
			default:
				$wholesale=WHSLE_PENDING;
		}			
	
		if(!$useEEAT && $wholesale==WHSLE_PENDING){
			//send an additional email to an administrator
			ob_start();
			require($_SERVER['DOCUMENT_ROOT'].'/emails/email_005_approve.php');
			$body=ob_get_contents();
			ob_end_clean();
			enhanced_mail(array(
				'to'=>($shuntToDeveloper ? $developerEmail : $adminEmail),
				'subject'=>$usemod['memberWord'].' Application Submitted for '.$siteName,
				'body'=>$body, 
				'from'=>$usemod['replytoEmail'], 
				'mode'=>'plaintext',
				'from'=>$usemod['replytoEmail']
			));
		}
		//determine email to go out
		switch(true){
			case $useEEAT && $wholesale==WHSLE_NO:
				//Please reply to confirm you email address
				$textCase=1;
				$email=$_SERVER['DOCUMENT_ROOT'].'/emails/'.$usemod['email_001_please_reply_to_confirm_email'];
			break;
			case $useEEAT && $wholesale==WHSLE_PENDING:
				//Please reply to confirm your email address.
				//An administrator will then review your wholesale credentials and approve your wholesale account
				$textCase=2;
				$email=$_SERVER['DOCUMENT_ROOT'].'/emails/'.$usemod['email_002_please_reply_to_confirm_then_reviewed'];
			break;
			case $useEEAT && $wholesale==WHSLE_APPROVED:
				//Please reply to confirm your email address.  
				//As soon as you do, you are approved for a wholesale account
				$textCase=3;
				$email=$_SERVER['DOCUMENT_ROOT'].'/emails/'.$usemod['email_003_please_reply_to_confirm_then_approved'];
			break;
			case !$useEEAT && $wholesale==WHSLE_NO:
				//Welcome!
				$textCase=4;
				$email=$_SERVER['DOCUMENT_ROOT'].'/emails/'.$usemod['email_004_immediate_welcome'];
			break;
			case !$useEEAT && $wholesale==WHSLE_PENDING:
				//Welcome! An administrator will review your whsle credentials and approve your wholesale account
				$textCase=5;
				$email=$_SERVER['DOCUMENT_ROOT'].'/emails/'.$usemod['email_005_welcome_wholesale_to_approve'];
			break;
			case !$useEEAT && $wholesale==WHSLE_APPROVED:
				//Welcome! You are approved and can now begin to access the privileges of a wholesale account
				$textCase=6;
				$email=$_SERVER['DOCUMENT_ROOT'].'/emails/'.$usemod['email_006_immediate_welcome_wholesale'];
			break;
			default:
		}
		if($email && !$suppressEmail){	
			if($adminMode && $_POST['_sendEmail']=='2'){
				$sendEmail=$_POST['_sendEmailTo'];
			}else if($adminMode && $_POST['_sendEmail']=='3'){
				$sendEmail='';
			}else{
				$sendEmail=$Email;
			}
			if($sendEmail){
				ob_start();
				require($email);
				$body=ob_get_contents();
				ob_end_clean();
				enhanced_mail(array(
					'to'=>($shuntToDeveloper ? $developerEmail : $sendEmail), 
					'subject'=>($wholesale ? 'Thank you for your application with' : ($useEEAT ? 'Confirm you e-mail with ' : 'Welcome to ')).$adminCompany, 
					'body'=>$body, 
					'from'=>$usemod['replytoEmail'], 
					'mode'=>'html'
				));
			}
			//added 2009-11-24: let admin know an ap has been submitted
			if($useEEAT && $usemod['pendingApplicationEmail']){
				ob_start();
				require($_SERVER['DOCUMENT_ROOT'].'/emails/ap_pending.php');
				$body=ob_get_contents();
				ob_end_clean();
				mail(
					($shuntToDeveloper ? $developerEmail : $usemod['pendingApplicationEmail']),
					'Application pending, email to be verified', 
					$body, 
					'From: do-not-reply@'.preg_replace('/^www\./i','',$_SERVER['HTTP_HOST'])
				);
			}
			if($textCase==4 && $email=$usemod['email_007_admin_notification']){
				$emailSource=$_SERVER['DOCUMENT_ROOT'].'/emails/'.$email;
				$systemEmail['to']=($shuntToDeveloper ? $developerEmail : $adminEmail);
				require($MASTER_COMPONENT_ROOT.'/emailsender_03.php');
			}
		}
		if($useEEAT){
			?><script language="JavaScript" type="text/javascript">window.parent.location='/cgi/addresult';</script><?php
		}else{
			if($usemod['insertAutoLogin']==true){
				$UN=$UserName; $PW=$plainTextPassword;
				//this uses the same component as the login.php page
				$overrideSetLoginVars=false;
				require(str_replace('cgi.php','cgi.comp.login_b.php',__FILE__));
				
				//2012-07-01: handle proxy login in session
				if($proxyLoginToken==md5($MASTER_PASSWORD)){
					$_SESSION['cnx'][$cnxKey]['proxy']=$proxyB_login;
					$_SESSION['cnx'][$cnxKey]['proxy']['cnx']=$proxyA_login;
				}
				
				if($loginOK==true){
					$l=($src ? $src : ($trigger ? $trigger : '/'));
					?><script language="JavaScript" type="text/javascript">window.parent.location='<?php echo $l?>';</script><?php
				}else{
					//this should not happen - as of 2012-07-01 I don't recall when it has happened
					mail($developerEmail, 'Internal login failed', 'A use of add_modify.php on insert mode failed to log the person in, file='.__FILE__.', line='.__LINE__,$usemod['errFromHdr']);	
					?><script language="JavaScript" type="text/javascript">window.parent.location='/cgi/login';</script><?php
				}
			}else{
				?><script language="JavaScript" type="text/javascript">window.parent.location='/cgi/addresult';</script><?php
			}
		}
	}else if($mode=='update'){
		?><script language="JavaScript" type="text/javascript">
		window.parent.location='/cgi/updated';
		</script><?php
	}
	$assumeErrorState=false;
	exit;		
}else if($mode=='componentControls'){

    exit('point 2');

    if($submode=='eventHandle'){
		//error checking

		//database entries

		//mincemeat
		mm(array(
			'sections'=>array(
				'keysection'=>array(
					'method'=>'basic',
				),
			)
		));
		//this is an artificial stop to simulate all HTML-output parts of the component being sectioned
		goto placeholder_sectionstart; 
	}
}else if($mode=='componentEditor'){

    exit('point 3');

    if($submode=='import'){
		$ImportString=trim($ImportString);
		if(!preg_match('/^[+a-zA-Z0-9=]+$/',$ImportString))error_alert('The string you are attempting to import does not appear to be valid.  It must be a base 64-encoded serialized array');
		$temp=unserialize(base64_decode($ImportString));
		if(empty($temp))error_alert('The string you are attempting to import does not appear to be valid.  It must be a base 64-encoded serialized array');
		if($ImportMerge){
			$a=unserialize(base64_decode($ImportString));
			$ImportString=base64_encode(serialize(array_merge_accurate($Parameters,$a)));
		}else{
			//no action
		}
		error_alert('since development of gen_ComponentsNodes, imports have been disabled');
		switch(true){
			case strlen($thissection):
				q("UPDATE cmsb_sections SET Options='".$ImportString."' WHERE Section='$thissection'");
			break;
			case strlen($_thisnode_):
				q("UPDATE gen_nodes_settings SET Settings='".$ImportString."' WHERE Nodes_ID='$_thisnode_'");
			break;
			default:
				q("UPDATE gen_templates_blocks SET Parameters='".$ImportString."' WHERE Templates_ID='$Templates_ID' AND Name='$pJCurrentContentRegion'");
		}
		?><script language="javascript" type="text/javascript">
		alert('Your settings have been successfully imported.  Juliet will now reload this page');
		var l=window.parent.location+'';
		window.parent.location=l;
		</script><?php
	}
	/* 2012-03-12: this is universal code which should be updated on ALL components	*/
	if($submode=='export')ob_start();
	if($_thisnode_){
		/*
		2013-04-06: this is completely new coding to go into gen_components.ComponentSettings and gen_ComponentsNodes.Settings, vs. gen_nodes_settings.Settings.  this is a much better and less conflicting storage
		*/

		//pJ.componentFiles is the var storage cabinet for all components
		!is_array($pJ['componentFiles'][$handle]) ? $pJ['componentFiles'][$handle]=array() : '';
		//now integrate the form post turtled
		$pJ['componentFiles'][$handle]['data'][$formNode]=stripslashes_deep($_POST[$formNode]);
		//we assume (2013-04-06) that the page node exists, but the join record may not
		if($a=q("SELECT * FROM gen_ComponentsNodes WHERE Components_ID=$_Components_ID_ AND Nodes_ID='".($_thisnode_ ? $_thisnode_ : $thisnode) ."'", O_ROW)){
			$Settings=unserialize(base64_decode($a['Settings']));
			prn($qr);
			prn($Settings);
		}else{
			q("INSERT INTO gen_ComponentsNodes SET Components_ID=$_Components_ID_, Nodes_ID=".($_thisnode_?$_thisnode_:$thisnode));
			$Settings=array();
			prn($qr);
		}
		$Settings['data'][$formNode]=$pJ['componentFiles'][$handle]['data'][$formNode];
		q("UPDATE gen_ComponentsNodes SET Settings='".base64_encode(serialize($Settings))."' WHERE Components_ID=$_Components_ID_ AND Nodes_ID=".($_thisnode_?$_thisnode_:$thisnode));
		break;
	}else{
		exit('unable to update component');
	}
	if($submode=='export'){
		ob_end_clean();
		$str='-- Juliet version '.$pJVersion.', file '.end(explode('/',__FILE__)).'; exported '.date('n/j/Y \a\t g:iA').' - to re-import, paste the code on the next line into the desired component ----'."\n";
		$str.=base64_encode(serialize($Parameters));
		$str.="\n--------- the following should NOT be pasted in but is an unencoded version of the above -------\n";
		ob_start();
		print_r($Parameters);
		$str.=ob_get_contents();
		ob_end_clean();
		attach_download('', $str, str_replace('.php','',end(explode('/',__FILE__))).'_'.date('Y-m-d_his').'.txt');
	}
	break;
}else if($formNode=='default'){

    exit('point 4');

    $tabVersion=3;
	ob_start();
	?>
	<p class="gray">Terms</p>

	If you want a contact category, specify it here:
	<input name="default[umContactCategory]" type="text" id="default[umContactCategory]" onchange="dChge(this);" value="<?php echo $umContactCategory;?>" />
	<br />
	If you allow reseller accounts, specify your word for this type of account:
	<input name="default[umResellerWord]" type="text" id="default[umResellerWord]" onchange="dChge(this);" value="<?php echo $umResellerWord;?>" />
	<br />
	Default <u>insert</u> record button text:
	<input name="default[umInsertButtonText]" type="text" id="default[umInsertButtonText]" onchange="dChge(this);" value="<?php echo $umInsertButtonText;?>" />
	<br />
	Default <u>update</u> record button text:
	<input name="default[umUpdateButtonText]" type="text" id="default[umUpdateButtonText]" onchange="dChge(this);" value="<?php echo $umUpdateButtonText;?>" />
	<br />
	If you have a premium membership specify it here:
	<input name="default[umPremiumMemberWord]" type="text" id="default[umPremiumMemberWord]" onchange="dChge(this);" value="<?php echo $umPremiumMemberWord;?>" />
	<br />
	<?php
	get_contents_tabsection('terms');
	?>
	<p class="gray">Coding</p>
	Email uniqueness requirement <span class="gray">(for all forms)</span>: 
	<select id="default[checkUniqueEmail]" name="default[checkUniqueEmail]" onchange="dChge(this);">
	<option value="<?php echo EMAIL_CHECK_NONE;?>" <?php echo $usemod['checkUniqueEmail']==EMAIL_CHECK_NONE?'selected':''?>>Not required</option>
	<option value="<?php echo EMAIL_CHECK_NEW;?>" <?php echo $usemod['checkUniqueEmail']==EMAIL_CHECK_NEW?'selected':''?>>Only for new accounts</option>
	<option value="<?php echo EMAIL_CHECK_EXISTING;?>" <?php echo $usemod['checkUniqueEmail']==EMAIL_CHECK_EXISTING?'selected':''?>>For new and updating accounts</option>	
	</select><br />
	Where error messages are from <span class="gray">(for all forms)</span>: <input name="default[errFromHdr]" type="text" id="default[errFromHdr]" onchange="dChge(this);" value="<?php echo h($usemod['errFromHdr']);?>" size="42" />
	<br />
	
	<?php
	get_contents_tabsection('coding');
	
	get_contents_tabsection('communication');
	tabs_enhanced(
		array(
			'terms'=>array( 'label'=>'Terms'),
			'communication'=>array(),
			'coding'=>array( 'label'=>'Coding'),
		) 
	);
	break;
}

$block='mainRegionCenterContent';
ob_start();
//edit link
echo pJ_call_edit(array(
	'level'=>ADMIN_MODE_DESIGNER,
	'location'=>'JULIET_COMPONENT_ROOT',
	'file'=>end(explode('/',__FILE__)),
	'thisnode'=>$thisnode,
	'thissection'=>$thissection,
	'label'=>'Edit Usemod Settings',
));
placeholder_sectionstart:

switch(true){
	case $thispage=='usemod':
	    exit('case a');
		//add_modify.php
		if($_GET['Email'] && $_GET['src'] /** coming in from email **/){
			if($thisUN=q("SELECT UserName FROM addr_contacts WHERE Email='".$_GET['Email']."'", O_VALUE)){
				//they have since signed up
				header('Location: /cgi/login?src='.urlencode($_GET['src']));
				?><script>window.location='/cgi/login?src=<?php echo urlencode($_GET['src'])?>';</script>
				redirecting to existing user sign in<?php
				exit;
			}
		}
		if(strlen($_SESSION['identity']) && count($_SESSION['cnx'][$cnxKey])){
			//They are logged in, interpret as an update
			if($usemod['allowAsNewEntries'] && $addNewRecord=='1'){
				$mode='insert';
			}else{
				$mode='update';
				$sql="SELECT
				a.*, /* we treat the contacts portion and the main portion; this is a 'user' form */
				c.CompanyName AS Company /* we allow them to control full legal company name, we control client name */,
				/* -- this is a unique address set from contacts -- */
				c.Address1,
				c.Address2,
				c.City,
				c.State,
				c.Zip,
				c.Country,
				/* -- this is a unique address set from contacts -- */
				c.ShippingAddress,
				c.ShippingAddress2,
				c.ShippingCity,
				c.ShippingState,
				c.ShippingCountry
				/* Phones are supposed to be integrated in from the contacts record */
				FROM addr_contacts a 
				LEFT JOIN finan_ClientsContacts b ON a.ID=b.Contacts_ID AND b.Type='Primary' 
				LEFT JOIN finan_clients c ON b.Clients_ID=c.ID
				WHERE a.ID=".$_SESSION['cnx'][$cnxKey]['primaryKeyValue'];
				if($a=q($sql, O_ARRAY)){
					if(count($a)>1){
						mail($developerEmail,'notice file '.__FILE__.', line '.__LINE__,get_globals('The logged in persion is primary in more than one company'),$fromHdrBugs);
					}
					$a=$a[1];
					foreach($a as $n=>$v){ $a[$n]=htmlentities($v); }
					@extract($a);
				}else{
					//record not found, redirect to login
					mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
					?><script language="javascript" type="text/javascript">
					window.location='/cgi/login?err=noRecordFound&src=add_modify.php';
					</script><?php
					exit;
				}
			}
		}else{
			//They are adding their information
			$mode='insert';
		}

		//we call this again because the extracted vars were not available
		$whsle=(
			($usemod['wholesaleToken'] && $usemod['presentWholesaleFields']==8) || 
			($usemod['wholesaleToken'] && $usemod['presentWholesaleFields']>=4 /*allow*/ && $presentWholesale=='1') || 
			($mode=='update' && $WholesaleAccess && trim($WholesaleNumber) && trim($WholesaleAccess)!=WHSLE_REJECTED)
		);

		//components of add/modify form
		require(str_replace('cgi.php','cgi.formheader_default.php',__FILE__));
		require(str_replace('cgi.php','cgi.form_default.php',__FILE__));
		require(str_replace('cgi.php','cgi.formfooter_default.php',__FILE__));
	break;
	case $thispage=='login':
		//login
		/**
		2007-01-26: this has not been touched for a long time! Wishlist
			0. handle encrypted password schema vs. plaintext
			00. utility to convert from plaintext to encryption
			1. standardize with my newer iframe coding and shutdown to prevent problems with back-browsing
			2. repeated hack prevention and IP logging
			3. get rid of bad coding (see notes)
			4. make more compatible with a universal login - more gentle to session variables
		**/
		//identify this page
		$localSys['scriptID']='UMLI';
		$localSys['scriptVersion']='2.8.3';
		$localSys['componentID']='';
		$fl=__FILE__;

		if($logout){
			if($sessionid)session_id($sessionid);
			session_start();

			//add ExitTime to the logout in main database - not developed
			/**********
			$logout_cnx=mysqli_connect($MASTER_HOSTNAME, $MASTER_USERNAME, $MASTER_PASSWORD);
			mysqli_select_db($logout_cnx, $MASTER_DATABASE);
			$sql="UPDATE rbase_logs SET
			ExitTime = IF(ExitTime='0000-00-00 00:00:00','$dateStamp',ExitTime),
			Logouttime = '$dateStamp'
			WHERE
			SessionKey = '" . $_SESSION['sessionKey'] . "'  AND
			IPAddress = '".$_SESSION[sessionIP]."' AND
			logUserName = '" . $_SESSION['systemUserName'] . "' AND
			logAcctName = '". $_SESSION['currentConnection']."'";
			mysqli_query($logout_cnx, $sql);
			*********/
			
			//always forget persistentLogin
			setcookie('idToken','',time()-(24*3600*60),'/');
			
			/*
			2009-12-25: This will more gracefully kill the session - we want to kill "this" connection. logout=1 is a standard logout which kills only this database connection for the user or guest - they would still be logged into other sites using dbs in the relatebase family
			
			logout:
				level 1 will remove only that cnx node AND the root if nothing remains in cnx.  Otherwise it will re-assign their identity to the highest level provided a comparison of levels can be given (else the last level encountered
				logoutSessionNodes, if passed as a comma-separated string or array, will also remove those session nodes specified, like 'admin' or 'special'
				level 2 will remove all cnx and session root values on the list below but nothing else
				level 3 will destroy the session completely
			*/
			if($logout==1){
				unset($_SESSION['cnx'][$cnxKey]);
				if(!count($_SESSION['cnx'])){
					//remove session root values that cgi works with
					$_SESSION['identity']='';
					unset(
						$_SESSION['identity'],
						$_SESSION['cnx'],
						$_SESSION['createDate'],
						$_SESSION['creator'],
						$_SESSION['editDate'],
						$_SESSION['editor'],
						$_SESSION['firstName'],
						$_SESSION['middleName'],
						$_SESSION['lastName'],
						$_SESSION['email'],
						$_SESSION['loginTime'],
						$_SESSION['sessionIP'],
						$_SESSION['systemUserName'],
						$_SESSION['identity'],
						$_SESSION['sessionKey']
					);
				}else{
					unset($maxIdentity);
					foreach($_SESSION['cnx'] as $n=>$v){
						if(!in_array($v['identity'], $usemod['acceptableLoginIdentities'])){
							unset($_SESSION['cnx'][$n]);
							continue;
						}
						if(array_search($v['identity'],$usemod['acceptableLoginIdentities'])>$maxIdentity || !isset($maxIdentity)){
							$maxIdentity=$v['identity'];
							$cnxNode=$v;
						}
					}
					if($maxIdentity && $cnxNode){
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
					unset(
						/* we destroy all cnx nodes */
						$_SESSION['cnx'],
						$_SESSION['identity'],
						$_SESSION['createDate'],
						$_SESSION['creator'],
						$_SESSION['editDate'],
						$_SESSION['editor'],
						$_SESSION['firstName'],
						$_SESSION['middleName'],
						$_SESSION['lastName'],
						$_SESSION['email'],
						$_SESSION['loginTime'],
						$_SESSION['sessionIP'],
						$_SESSION['systemUserName'],
						$_SESSION['identity'],
						$_SESSION['sessionKey']
					);
			}else if($logout==3){
				//this kills everything - the objective is to change the session cookie also
				session_destroy();
				$_SESSION=array();
			}
			if($logout<3 && $logoutSessionNodes){
				!is_array($logoutSessionNodes) ? $logoutSessionNodes=explode(',',trim($logoutSessionNodes,',')) : '';
				foreach($logoutSessionNodes as $v){
					if(strtolower($v)=='cnx')continue;
					unset($_SESSION[$v]);
				}
			}
		
			//redirect to most appropriate location
			/*
			--------------- 2009-12-23: delete this later on ---------------
			this old code made the assumption that you had go somewhere after a logout - whereas, with no src declared, you'll just be at the login screen which is very useful in cases
			if(!$src){
				$src=$PHP_SELF;
				if($qs=preg_replace('/logout=[1-2]/','',$QUERY_STRING)){
					$qs=str_replace('&&','&',$qs);
					$src.='?'.$qs;
				}
			}
			*/
			if($src && !$relogin){
				header('Location: '.$src);
				?><script language="JavaScript" type="text/javascript">window.location='<?php echo str_replace("'","\'",$src)?>';</script><?php
				exit('redirect');
			}
		}
		
		if($relogin){
			$newSessionKey = md5(rand(100,100000).$timeStamp);
			session_id($newSessionKey);	
		}else if($sessionid){
			session_id($sessionid);
		}
		session_start();
		if($relogin){
			$_SESSION=array();	
		}
		
		if(strlen($postTime)){
			$pt=explode(' ',$postTime);
			$time=$pt[3];
			$date=implode(' ',array($pt[1],$pt[2].',',$pt[5]));
		}
		//for persistent login below
		$monthNumber=(date('Y')-2000)*12 + date('n');

		/*
		prn("UN = $UN");
		prn('-- server --');
		prn($_SERVER);
		prn('-- session --');
		prn($_SESSION);
		prn($GLOBALS);
		exit;
		*/

		//pull UN from cookie if present
		if(!$UN && $_COOKIE['idToken']) $UN=generic5t(current(explode(':',$_COOKIE['idToken'])),'decode');
		if($UN){
			if(strlen($PW)){
				//OK
			}else if($authKey){
				//we want "login" i.e. setting session key to only happen in this script, so when a new user signs up we redirect here with the md5 of their passwordmd5 PLUS the masterpassword
                $sql = "SELECT PasswordMD5 FROM addr_contacts WHERE UserName='$UN' ".$usemod['additionalGeneralWhereClause'];
				$keyPW=q($sql, O_VALUE);
				if($authKey==md5($MASTER_PASSWORD.$keyPW)){
					$PW=$keyPW;
				}
			}else if($EnrollmentAuthToken){
				//They are verifying their email address, this trumps a login - we do not use the password but set a false value - this needs reworked
				$keyPW=q("SELECT PasswordMD5, EnrollmentAuthToken from addr_contacts WHERE UserName='$UN' ".$usemod['additionalGeneralWhereClause'], O_ROW);
				if($EnrollmentAuthToken==$keyPW['EnrollmentAuthToken']){
					$PW=$keyPW['PasswordMD5'];
				}
			}else if($usemod['allowPersistentLogin'] && $_COOKIE['idToken'] && ($tokenMonthNumber=substr(end(explode(':',$_COOKIE['idToken'])),0,3))>=$monthNumber){
				//hash structure introduced 2009-10-28
				mail($developerEmail,'Squishy login '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
				$keyPW=q("SELECT PasswordMD5 FROM addr_contacts WHERE UserName='$UN' ".$usemod['additionalGeneralWhereClause'], O_VALUE);
				if(
					substr($_COOKIE['idToken'], -$usemod['idTokenLength']) ==  
					substr(md5($tokenMonthNumber . $keyPW . $MASTER_PASSWORD),0,$usemod['idTokenLength'])
				){
					$PW=$keyPW;
				}
			}


			if(strlen($PW)){
				//see if they're good, log them in
				require(str_replace('cgi.php','cgi.comp.login_b.php',__FILE__));

				if(
					$persistentLogin && 
					$usemod['allowPersistentLogin'] && 
					$loginCode>=$usemod['persistentLoginThreshold'] &&
					!($authKey && !$usemod['allowPersistentSoftLogin'])
				){
					//compile the username
					$str=generic5t(stripslashes($UN),'encode');
					$str.=':';
					//get the actual md5 password in case they're using master password
					$keyPW=q("SELECT PasswordMD5 FROM addr_contacts WHERE UserName='$UN'", O_VALUE, $v280cnx);
					$str.=str_pad($monthNumber + $usemod['persistentLoginEffectivePeriod'],3,'0',STR_PAD_LEFT);
					$str.=substr(md5(str_pad($monthNumber + $usemod['persistentLoginEffectivePeriod'],3,'0',STR_PAD_LEFT) . $keyPW . $MASTER_PASSWORD), 0, $usemod['idTokenLength']);
					$out=ob_get_contents();
					setcookie('idToken',$str,time()+(3600*24*30*$usemod['persistentLoginEffectivePeriod']),'/');
				}
				if($loginOK==true){
					/* note on this: the query is correct, because if we're using wholesale, then WholesaleAccess would in fact have been set to WHSLE_PENDING; if it was WHSLE_REJECTED or _NO, this doesn't apply
					*/
					if($loginCode==100){
						if(!($record=q("SELECT * FROM addr_contacts WHERE ID='".$_SESSION['cnx'][$cnxKey]['primaryKeyValue']."' ". ($usemod['jan2010bug01'] ? '' : "AND WholesaleAccess='".WHSLE_PENDING."' ").$usemod['additionalGeneralWhereClause'], O_ROW))){
							mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
						}
						if($usemod['allowImmediateWholesaleAccess']){
							//approve them
							q("UPDATE addr_contacts SET WholesaleAccess='".WHSLE_APPROVED."' WHERE ID='".$_SESSION['cnx'][$cnxKey]['primaryKeyValue']."'");
			
							//notify them
							//-------------- new email send coding --------------
							$mode='approveWholesale';
							$emailSource=($_SERVER['DOCUMENT_ROOT'].'/emails/'.$usemod['email_you_are_approved_rejected']);
							if($shuntToDeveloper)$emailTo=$developerEmail;
							require($MASTER_COMPONENT_ROOT.'/emailsender_03.php');
						}else{
							//send email to the administrator to approve them
							//-------------- new email send coding --------------
							$systemEmail['to']=$adminEmail;
							$emailSource=($_SERVER['DOCUMENT_ROOT'].'/emails/'.$usemod['email_review_approve_reject']);
							if($shuntToDeveloper)$emailTo=$developerEmail;
							require($MASTER_COMPONENT_ROOT.'/emailsender_03.php');
						}
					}
					//show the results page
					#redirect so we don't have to refresh page every time we hit the back button
					if($src){
						header('Location: ' . $src);
						?><script language="JavaScript" type="text/javascript">window.location='<?php echo $src?>';</script><?php
						exit('redirecting to source');
					}else{
						header('Location: /');
						?><script language="JavaScript" type="text/javascript">window.location='/';</script><?php
						exit('end processing');
					}
				}else{
					$errMessage=1;
					require(str_replace('cgi.php','cgi.login_form.php',__FILE__));
				}
			}else{
				if(in_array($_SESSION['identity'],$usemod['acceptableLoginIdentities']) && $_SESSION['cnx'][$cnxKey]){
					//show Administrator page
					require($_SERVER['DOCUMENT_ROOT'].'/components-juliet/cgi.login_result.php');
				}else{
					//show the Login form
					require($_SERVER['DOCUMENT_ROOT'].'/components-juliet/cgi.login_form.php');
				}
			}
		}else{
			if(in_array($_SESSION['identity'],$usemod['acceptableLoginIdentities']) && $_SESSION['cnx'][$cnxKey]){
				//show Administrator page
				require($_SERVER['DOCUMENT_ROOT'].'/components-juliet/cgi.login_result.php');
			}else{
				//show the Login form
				require($_SERVER['DOCUMENT_ROOT'].'/components-juliet/cgi.login_form.php');
			}
		}
	break;
	case $thispage=='addresult':

                                                                                                            exit('case c');
		//add_result.php
		if($n=$message[$messageCode]){
			echo $n;
		}else{
			if($usemod['useEnrollmentConfirmation'] && $EnrollmentAuthToken=q("SELECT EnrollmentAuthToken FROM addr_contacts WHERE ID='".$_SESSION['cnx'][$cnxKey]['primaryKeyValue']."'", O_VALUE)){
				//they are pending
				?><!-- generic success message -->
				<h1>You still have one more step!</h1>
				<p>
				For security purposes we need to verify that you own the email address you used in your application.  Check your email inbox; you should receive a link to verify your email address.  Click on the link provided in the email, or paste it into your browser.  Once you do this, your application will be sent to us for review, and we will contact you when you have been approved.<br />
				<br />
				<a href="/products/catalog">Click here to go to the products catalog</a></p>
				<?php
			}else{
				?><!-- generic success message -->
				<h1>You have successfully created a new account</h1>
				<?php
				if($usemod['insertAutoLogin']){
					?>You have also been signed in automatically.<br />
					<br />
					<a href="/cgi/usemod">Click here to change your information</a>.
					<br />
					<br />
					<a href="/cgi/login?logout=3&src=/">Click here to sign out</a>.
					<?php
				}else{
					?>To sign in, <a href="/cgi/login?UN=<?php echo $UN?><?php echo ($src ? '&src='.urlencode($src) : '')?>">click here</a><?php
				}
			}
		}
	break;
	case $thispage=='updated':
	    exit('case d');
		?>
		<h1>Your Information Has Been Updated</h1>
		<?php
		CMSB();
	break;
	case $thispage=='switchboard':
		if(in_array($_SESSION['identity'],$usemod['acceptableLoginIdentities']) && $_SESSION['cnx'][$cnxKey]){
			?>
			<h1>My Switchboard</h1>
			<p>No switchboard features are installed for this site</p>
			
			<p>However, you may use the following links:</p>
			
			<a href="/cgi/usemod" title="View and edit your information">Modify your information</a><br />
			<a href="/cgi/resetpassword" title="Change your password" onclick="return ow(this.href,'l1_reset','700,700');">Reset Your Password</a><br />
			<a href="/cgi/login?logout=1&src=/" title="Sign out">Sign out</a><br />
			<?php
		}else{
			header('Location: /cgi/login?src=switchboard');
			exit;
		}
	break;
	case $thispage=='resetpassword':
	    exit('case e');
		//reset password
		if(!$usemod['passwordResetTimeout'] || $usemod['passwordResetTimeout']>72)$usemod['passwordResetTimeout']=24;
		if($_POST){
			if($tAuthKey){
				if(!($a=q("SELECT UserName, Email, PasswordMD5, FirstName, LastName FROM addr_contacts WHERE UserName='$UN' ".$usemod['additionalGeneralWhereClause'], O_ROW))){
					$errMessage='It appears you did not reach this page by a proper request';
				}else if(time()-($usemod['passwordResetTimeout']*3600)>$t || !$t){
					$errMessage='The password reset link has expired.  Please close this window and request a password reset again';
				}else if(md5($MASTER_PASSWORD.$a['PasswordMD5'].$t)!==$tAuthKey){
					$errMessage='The reset link is invalid';
				}else if($Password!==$nullPassword){
					$errMessage='Your passwords did not match.  Try again.';
				}else{
					q("UPDATE addr_contacts SET PasswordMD5='".md5(stripslashes($Password))."' WHERE UserName='$UN'");
				}
			}else{
				//must be logged in to reset password
				if(!$_SESSION['cnx'][$cnxKey]['identity']){
					$errMessage='You must be signed in to reset your password.  Close this window and sign in';
				}else if(md5(stripslashes($OldPW))!==q("SELECT PasswordMD5 FROM addr_contacts WHERE UserName='".$_SESSION['systemUserName']."' ".$usemod['additionalGeneralWhereClause'], O_VALUE)){
					$errMessage='Your old password is not correct.';
				}else if($Password!==$nullPassword){
					$errMessage='Your passwords did not match.  Try again.';
				}else{
					q("UPDATE addr_contacts SET PasswordMD5='".md5(stripslashes($Password))."' WHERE UserName='".$_SESSION['systemUserName']."'");
				}
			}
			if($errMessage){
				?><h1 class="red"><?php echo $errMessage;?></h1>
				<a href="resetpassword">Click here to try again</a>
				<?php
			}else{
				//redirect to prevent page expired
				$loc='/cgi/resetpassword?complete=1&src='.urlencode($src);
				header('Location: '.$loc);
				?><script language="javascript" type="text/javascript">
				window.location='<?php echo $loc?>';
				</script><?php
				exit;
			}
		}else{
			if($complete==1){
				//show the result
				?><h1>Your password was successfully reset</h1>
				<input type="button" name="Close" value="Close Window" onclick="window.close();" /><?php
			}else{
				//show the form
				?><div style="color:DARKRED;font-weight:900;"><?php echo $errMessage?></div>
				<form name="form1" id="form1" action="" method="post">
				<p>
				This form will reset your password. Select a password and enter it a second time to be sure you typed it right.
				<?php
				if($tAuthKey){
					?><input name="tAuthKey" type="hidden" id="tAuthKey" value="<?php echo $tAuthKey?>">
					<input name="UN" type="hidden" id="UN" value="<?php echo $UN?>">
					<input name="t" type="hidden" id="t" value="<?php echo $t?>">
					<?php
				}else{
					?><br />
					<br />
					First enter your old password:
					<input name="OldPW" type="password" id="OldPW">
					<?php
				}
				?>
				</p>
				<br />
				<br />
				<table border="0" cellspacing="0" cellpadding="0">
				  <tr>
					<td>New Password:</td>
					<td><input name="Password" type="password" id="Password" /></td>
				  </tr>
				  <tr>
					<td>Retype password:</td>
					<td><input name="nullPassword" type="password" id="nullPassword" /></td>
				  </tr>
				</table>
				<p>&nbsp;</p>
				<p>
				<input class="cgiButton_A" type="submit" name="Submit" value="Reset Password" />    
				</p>
				</form><?php
			}
		}
	break;
	case $thispage=='forgotpassword':
	    exit('case f');
		if($_POST){
			if($a=q("SELECT UserName, Email, Password, PasswordMD5 FROM ".$MASTER_DATABASE.".addr_contacts WHERE (Email='$UN' OR UserName='$UN')", O_ARRAY)){
				if(count($a)>1){
					//multiple matches found, need to address this
				}
				if(!$a[1]['Email']){
					$errMessage='No email on file to send to!  Contact us at '.$usemod['adminEmail'];
				}else{
					ob_start();
					require('./emails/email_forgot.php');
					$body=ob_get_contents();
					ob_end_clean();
					enhanced_mail(
						$shuntToDeveloper && $cognate!=='public_html' ? $developerEmail : $a[1]['Email'],
						'Forgotten password request for '.$usemod['siteName'], 
						$body, 
						$usemod['replytoEmail'], 'html', '', '', '', 'mail', $usemod['replytoEmail']
					);
			
					//redirect to prevent page expired
					$loc='forgotpassword?complete=1&src='.urlencode($src);
					header('Location: '.$loc);
					?><script language="javascript">
					window.location='<?php echo $loc?>';
					</script>
				<?php
					exit;
				}
				$showPage='form';
			}else{
				$errMessage='No such record found in the database';
				require('forgot_form.php');
			}
		}else{
			if($complete==1){
				//show the result
				$showPage='result';
			}else{
				//show the form
				$showPage='form';
			}
		}
		if($showPage=='form'){
			?>
			<div style="color:red;"><?php echo $errMessage;?><br /></div>
			<form class="formLayout1" method="post" name="form1" id="form1">
			<h2 class="redhead">Forgotten Password</h2>
			<p>Enter your E-mail Address<?php echo $usemod['autoGenerateUsername'] ? '' : ' or user name'?>.<br />
			You will receive a link to reset your password via e-mail.
			</p>
			<table width="400">
				<tr> 
					<td>
						<div align="center">Your email address<?php echo $usemod['autoGenerateUsername'] ? '' : ' or user name'?>:</div>
					</td>
					<td> 
					<input type="text" name="UN" value="<?php echo $UN;?>" />
					</td>
				</tr>
				<tr> 
					<td colspan="2"> 
					<input type="hidden" name="src" id="src" value="<?php echo $src?>" />
					<center><input type="submit" name="Submit" value="Get Password" /></center>
					</td>
				</tr>
			</table>
			</form><?php
		}else if($showPage=='result'){
			?>
			<h2>Password Reset Link On Its Way</h2>
			Thank you. Your password reset link is being sent to the email address we have on file for you.<br />
			<a href="login.php?src=<?php echo urlencode($src)?>">Sign in</a>
			<?php
		}
	break;
	case $thispage=='tools':
	    exit('case g');
		switch(true){
			case $mode=='rejectWholesale':
			case $mode=='rejectWholesaleNotify':
			case $mode=='approveWholesale':
				//for email logging
				$enhanced_mail['logmail']=$usemod['logmail'];
				if($a=q("SELECT * FROM addr_contacts WHERE UserName='$UN' ".$usemod['additionalGeneralWhereClause'], O_ROW)){
					if(md5($MASTER_PASSWORD.$a['PasswordMD5'])==$authKey){
						q("UPDATE addr_contacts SET WholesaleAccess='".($mode=='approveWholesale' ? WHSLE_APPROVED : WHSLE_REJECTED)."' WHERE ID='".$a['ID']."' AND UserName='".$UN."'");
						if($mode!=='rejectWholesale'){ //approveWholesale || rejectWholesaleNotify
		
							//-------------- new email send coding --------------
							
							$emailSource=($_SERVER['DOCUMENT_ROOT'].'/emails/'.$usemod['email_you_are_approved_rejected']);
							require($MASTER_COMPONENT_ROOT.'/emailsender_03.php');
						}
						$code='Completed';
					}else{
						$code='Key mismatch';
					}
				}else{
					$code='Not found';
				}
				$assumeErrorState=false;
				//pass through to parent page
			break;
		}
		switch(true){
			case $code=='Not found':
				?>
				<h2>No such record found to be approved/declined.  The record may have been deleted (<?php echo $a['UserName']?>)</h2>
				<?php
			break;
			case $code=='Key mismatch':
				mail($developerEmail,'Key mismatch for '.$QUERY_STRING,'File: '.__FILE__."\nLine: ".__LINE__,'From: bugreports@'.$siteName);
				?>
				<h2>The query contained a key mismatch and is not recognized.  Administration has been notified</h2>
				<?php
			break;
			default:
				?>
				<h2>Success</h2>
				This record (<?php echo htmlentities($a['FirstName'] . ' ' . $a['LastName'] . ($a['Company'] ? ' of ' . $a['Company'] : ''))?>) has been <?php echo $mode=='approveWholesale'?'approved':'declined'?><?php if($mode!=='rejectWholesale')echo ' and the contact has been notified';?>.
				<?php
		}
		?></form>
		<?php
	break;
}
$$block=ob_get_contents();
ob_end_clean();

}//---------------- end i break loop ---------------
compend:

