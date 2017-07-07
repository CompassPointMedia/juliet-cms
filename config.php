<?php
/* ------------------------------------------------------------------
2012-10-27: 
* allowed this to reference a local file to determine acct value
* sunsetted $uid and ONFIRSTVISIT, etc. constants


	2011-02-19: simplified config.php page - used for Juliet Project 2011
	_SESSION related information **MUST** be placed/declared after the master config include

--------------------------------------------------------------------- */
//standard error reporting/display coding
function set_test_env(){
    error_reporting(E_ALL | E_STRICT);
    $AppEnv = getenv('AppEnv');
    if($AppEnv == 'production'){
        ini_set('display_errors',false);
    }else if($AppEnv == 'vagrant') {
        ini_set('display_errors',false);
    }else{
        // for now
        ini_set('display_errors',true);
    }
}
set_test_env();

//2017-07-04 - this is the simplest possible globalizer; _GET vars have precedence
//extractor
$extract = ['_POST'=>1, '_GET'=>1];
foreach($extract as $_GROUP => $clean){
    if(empty($GLOBALS[$_GROUP])) continue;
    if($clean){
        foreach($GLOBALS[$_GROUP] as $n => $v){
            $GLOBALS[$_GROUP][$n] = addslashes($v);
        }
    }
    extract($GLOBALS[$_GROUP]);
}

require(str_replace('/config.php', '/../private/config.php', __FILE__));

//if they have juliet, they are going to have the console and site creator

$removeThispageExtension=true;
$lowercaseThispage=true;
$JULIET_COMPONENT_ROOT=$_SERVER['DOCUMENT_ROOT'].'/components-juliet';

if(!empty($fromCRON)) goto compend;

if(!function_exists('q'))require_once($_SERVER['DOCUMENT_ROOT'].'/functions/function_q_v130.php');
if(!function_exists('prn'))require_once($_SERVER['DOCUMENT_ROOT'].'/functions/function_prn.php');
$qx['useRemediation']=true;
$qx['defCnxMethod']=C_MASTER;

if(empty($pJulietTemplate)) $pJulietTemplate=$_SERVER['DOCUMENT_ROOT'].'/Templates/relatebase_05_generic.php';
$overrideGeneric5tDecoding=true;

//$sql="SELECT
//a.ID AS RECORD_ACCOUNT_ID,
//a.AcctName AS RECORD_MASTER_DATABASE,
//IF(a.DbseName, a.DbseName, a.AcctName) AS MASTER_DATABASE,
//a.UserName AS MASTER_USERNAME,
//a.HostName AS MASTER_HOSTNAME,
//pivot.Password AS MASTER_PASSWORD,
//IF(ctc.FirstName IS NULL, CONCAT(pivot.FirstName,' ',pivot.LastName),pivot.Company) AS adminCompany,
//
//IF(ctc.Email IS NULL, pivot.Email, ctc.Email) AS adminEmail,
//IF(ctc.FirstName IS NULL, pivot.FirstName, ctc.FirstName) AS adminFirstName,
//IF(ctc.LastName IS NULL, pivot.LastName, ctc.LastName) AS adminLastName,
//
//IF(ctc.FirstName IS NULL, CONCAT(pivot.FirstName,' ',pivot.LastName),pivot.Company) AS companyName,
//IF(ctc.Phone IS NULL, pivot.Phone, ctc.Phone) AS companyPhone,
//IF(ctc.Fax IS NULL, pivot.Fax, ctc.Fax) AS companyFax,
//IF(ctc.Address IS NULL, pivot.Address, ctc.Address) AS companyAddress,
//IF(ctc.City IS NULL, pivot.City, ctc.City) AS companyCity,
//IF(ctc.State IS NULL, pivot.State, ctc.State) AS companyState,
//IF(ctc.Zip IS NULL, pivot.Zip, ctc.Zip) AS companyZip,
//m.ID AS mid,
//m.Status AS ModuleStatus,
//mi.Source AS ExtractConfig
//FROM
//rbase_account a
//LEFT JOIN rbase_userbase pivot ON a.AcctName=pivot.UserName
//LEFT JOIN rbase_UserbaseUserbase ub ON pivot.UserName = ub.Parent_UserName AND ub.Type='Primary'
//LEFT JOIN rbase_userbase ctc ON Child_UserName=ctc.UserName,
//rbase_AccountModules am,
//rbase_modules m LEFT JOIN rbase_modules_items mi ON m.ID=mi.Modules_ID AND mi.Types_ID=5
//WHERE
//a.ID=am.Account_ID AND
//am.Modules_ID=m.ID AND
//a.AcctName='".$acct."' AND
//m.SKU='051'
//GROUP BY a.AcctName";
//$err1='Unable to pull your RelateBase module for account '.$acct.' - either <br />
//<br />
//(1) your RelateBase account or module have not been set up, in which case you can go to <a href="http://www.relatebase.com/admin/accounts/accounts.php?AcctName='.$acct.'&ResourceToken='.substr(date('YmdHis').rand(10000,99999),3,16).'" onclick="window.open(this.href,\'l1_account\',\'width=700,height=700,menubar,resizable,scrollbars\');return false;">the account settings</a> or <br />
//<br />
//(2) Your module has a configuration error (a) SKU!=051 or (b)No item with Type=Configuration.  An email has been sent to the developer';
//$err2='You currently have an error in your RelateBase account '.$acct.' - more than one "051" type module was present.  An email has been sent to the developer';
//if($acctData=q($sql, O_ARRAY, C_SUPER)){
//	if(count($acctData)>1){
//		mail($developerEmail,'Error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
//		exit($err2);
//	}else{
//		$acctData=$acctData[1];
//		@extract($acctData);
//
//		//legacy from config.pre.cpm___.php
//		if(!$pJulietTemplate)$pJulietTemplate=$_SERVER['DOCUMENT_ROOT'].'/Templates/relatebase_05_generic.php';
//		if(!isset($pJInDevelopment))$pJInDevelopment =true;
//		if(!isset($pJulietBalanceColumns))$pJulietBalanceColumns=false;
//		//new coding - saved me from having to go to v104 on master_config
//		$overrideGeneric5tDecoding=true;
//
//		if(preg_match('/<serialized[^>]*>([^<]+)<\/serialized>/i',$ExtractConfig,$a)){
//			$moduleConfig=@unserialize(base64_decode($a[1]));
//			@extract($moduleConfig);
//		}else{
//			if(is_null($ExtractConfig)){
//				mail($developerEmail,'Error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
//				q("INSERT INTO relatebase_rfm.rbase_modules_items SET
//				Modules_ID=$mid,
//				Mst_Items_ID=1,
//				Types_ID=5,
//				CreateDate=NOW(),
//				Creator='".$_SESSION['systemUserName']."'",C_SUPER);
//			}
//			$moduleConfig=array();
//
//			//some things we MUST have
//			$settings['ClientWord']='Customer';
//			$settings['ItemWord']='Item';
//		}
//	}
//	if($ModuleStatus<50){
//		ob_start();
//		print_r($GLOBALS);
//		$err=ob_get_contents();
//		ob_end_clean();
//		$msg='Your Ecommerce Console module is currently expired or not active.  Please contact an administrator';
//		mail($developerEmail,'module status < 50; Error file '.__FILE__.', line '.__LINE__,$msg."\n\n".$err,$fromHdrBugs);
//		exit($msg);
//	}
//}else{
//	ob_start();
//	print_r($GLOBALS);
//	$err=ob_get_contents();
//	ob_end_clean();
//	$msg='Cannot find RelateBase Juliet Project Account';
//	mail($developerEmail,'module status < 50; Error file '.__FILE__.', line '.__LINE__,$msg."\n\n".$err,$fromHdrBugs);
//	exit($msg.'<br />'.$err1);
//}

require($_SERVER['DOCUMENT_ROOT'].'/components/master_config_v103.php');

$thisnode=q("SELECT ID FROM gen_nodes WHERE Type='Object' AND Category='Website Page' AND ".
($thisfolder /* this means a component */ ? "PageType='$thisfolder:$thispage'" : 
($thisfolder=='' && ($thispage=='index' || $thispage=='') ? "SystemName='{root_website_page}'" : 
"REPLACE(REPLACE(Name,' ',''),'-','')='".str_replace(' ','',str_replace('-','',$thispage))."'")), O_VALUE);

if(!$thisnode && $a=q("SELECT ID, Category, SubCategory FROM cms1_articles WHERE REPLACE(KeywordsTitle,'-',' ')='".addslashes(str_replace('-',' ',$thispage))."'", O_ROW)){
	//try for article
	/*
	2011-06-30
	the deal is, there really IS no defined node that represents an article; nodes are the user's creation
	$thisnode=$a['ID'];
	*/
	$Articles_ID=$a['ID'];
	$blogCategory=$a['Category'];
	$blogType=$a['SubCategory'];
	//$thispage='kylenetworking-news.php'; - now "thispage" is the name of the article - no more identify of the news page
	$pJInBlogMode=true;
}

//----------------- codeblock 088233 ---------------------
$consoleEmbeddedModulesSKUs=array(
	'RSC-01','RSC-20','040','CGI-70'
);
$systemEmbeddedModules=array(
	/*
	'cgi'=>array(
		'ID'=>base_convert(md5('cgi'),16,10),
		'SKU'=>'cgi',
		'AdminSettings'=>'',
		'Settings'=>'',
		'moduleAdminSettings'=>array(
			'version'=>'0.1',
			'name'=>'Site User Features (CGI)',
			'handle'=>'cgi',
			'handleAliases'=>array('users'),
			'settable_parameters'=>array(),
			'gettable_parameters'=>array(),
			'flow'=>array(
				'usemod'=>array(
					'name'=>'add and modify form',
				),
				'login'=>array(
					'name'=>'log in or out',
				),
				'addresult'=>array(
					'name'=>'add result',
				),
				'updated'=>array(
					'name'=>'updated',
				),
				'switchboard'=>array(
					'switchboard page',
				),
				'forgotpassword'=>array(
					'forgot password',
				),
			),
			'_settings'=>array(),
		),
	),
	*/
	'articles'=>array(
		'ID'=>base_convert(md5('articles'),16,10),
		'SKU'=>'articles',
		'AdminSettings'=>'',
		'Settings'=>'',
		'moduleAdminSettings'=>array(
			'version'=>'0.1',
			'name'=>'Article Features',
			'handle'=>'articles',
			'handleAliases'=>array('news','blog'),
			'settable_parameters'=>array(),
			'gettable_parameters'=>array(),
			'flow'=>array(
				'summary'=>array(
					'name'=>'Main article/news summary',
				),
				'focus'=>array(
					'name'=>'Article focus page',
				),
			),
			'_settings'=>array(),
		),
	),
);
if($consoleEmbeddedModules=q("SELECT
	m.ID, m.SKU, m.AdminSettings, m.Settings, mi.Source
	FROM rbase_account a, rbase_AccountModules am, rbase_modules m LEFT JOIN rbase_modules_items mi ON m.ID=mi.Modules_ID AND mi.Types_ID=5
	WHERE 
	a.AcctName=a.UserName AND 
	a.DbseName='$RECORD_MASTER_DATABASE' AND 
	a.ID=am.Account_ID AND 
	m.ID=am.Modules_ID AND 
	m.SKU IN('".implode("','",$consoleEmbeddedModulesSKUs)."') AND
	a.AcctName='$acct'", O_ARRAY_ASSOC, C_SUPER)){	
	/*
	2011-03-29	housekeeping items, these are a few previously hardcoded variables we now pull from db
	mid = shopping cart mid -> should be changed to ecmid (e-commerce)
	acct -> declared already in this file
	
	*/
	foreach($consoleEmbeddedModules as $n=>$v){
		if($v['AdminSettings'])$consoleEmbeddedModules[$n]['moduleAdminSettings']=unserialize(base64_decode($v['AdminSettings']));
		unset($consoleEmbeddedModules[$n]['AdminSettings']);
		if($v['SKU']=='040'){
			//ecommerce module - the old "SHOPCART - 01"
			$mid=$n; //mid = "module id" = CART module id (this was before I got into multiple modules)
			$cartAcct=$acct; //hack..			
		}else if($v['SKU']=='RSC-01'){
			//site creator
			
		}
	}
}else{
	$consoleEmbeddedModules=array();
}
$consoleEmbeddedModules=array_merge($systemEmbeddedModules,$consoleEmbeddedModules);
if(is_array($addedEmbeddedModules) && $addedEmbeddedModulesAuth==md5($MASTER_PASSWORD)){
	$consoleEmbeddedModules=array_merge($addedEmbeddedModules,$consoleEmbeddedModules);
}

/* Just got $cartAcct and $mid, might as well set the shopping cart url */
$shoppingCartURL = 'https://www.relatebase.com/c/cart/en/v500/?sessionid='.($sessionid ? $sessionid : $GLOBALS['PHPSESSID']).'&acct='.$cartAcct.'&mid='.$mid;
//----------------- end codeblock 088233 ---------------------
compend:

