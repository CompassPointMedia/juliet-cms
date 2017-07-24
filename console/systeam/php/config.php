<?php 
/*
CHANGE LOG
------------------------
2009-07-11: allowed for relatebase-rfm.com/~cpm000 URLs
2009-05-22: moved session_start() to this page
2008-12-12: moved this over from DAC Int.  Was developing the items list page and decided it was time to turn this into a component.  This config page has been pared down into functions and universal (non site-specific) information such as browser detection etc..  The connection database to get module information is relatebase_dev

*/


$appEnv = getenv('AppEnv');

//time function for benchmarking
if(!function_exists('gmicrotime')){
    /**
     * @param string $marker
     * @param array $options
     * @return void
     */
    function gmicrotime($marker='', $options=[]){
        #version 1.2, 2017-05-13

        extract($options);
        if(!isset($mem)) $mem = true; // || false, don't worry about memory
        if(!isset($format)) $format = 'array'; // || string

        global $mT;
        if($marker=='all') return $mT;

        list($usec, $sec) = explode(' ',microtime());
        $t=round((float)$usec + (float)$sec,6);

        if($format == 'string'){
            $value = $t;
        }else{
            $value = ['time'=>$t];
        }
        if($mem){
            $_mem = memory_get_usage();
            $_max = memory_get_peak_usage();
            if($format == 'string'){
                $value .= ":$mem:$max";
            }else{
                $value['memory'] = $_mem;
                $value['max'] = $_max;
            }
        }

        //store everything in this array
        $mT['all'][]=$value;

        //build associative 1-indexed array
        if(empty($mT['indexed'][$marker])){
            $mT['indexed'][$marker]=$value;
        }else{
            if(is_array($mT['indexed'][$marker])){
                $mT['indexed'][$marker][ count($mT['indexed'][$marker])+1 ]=$value;
            }else{
                $mT['indexed'][$marker][1]=array($mT['indexed'][$marker], $value);
            }
        }
    }
    gmicrotime('initialize');
}

//2017-07-04 - this is the simplest possible globalizer; _GET vars have precedence
//extractor
if(!function_exists('addslashes_deep')){
    function addslashes_deep($value){
        $value = is_array($value) ?
            array_map('addslashes_deep', $value) :
            addslashes($value);
        return $value;
    }
}
$extract = ['_POST'=>1, '_GET'=>1];
foreach($extract as $_GROUP => $clean){
    if(empty($GLOBALS[$_GROUP])) continue;
    if($clean){
        foreach($GLOBALS[$_GROUP] as $n => $v){
            $GLOBALS[$_GROUP][$n] = addslashes_deep($v);
        }
    }
    extract($GLOBALS[$_GROUP]);
}

//standardize the date and time stamps for long or repeated queries
$dateStamp=date('Y-m-d H:i:s');
$timeStamp=date('YmdHis',strtotime($dateStamp));

if(strlen($sessionid)) session_id($sessionid);
session_start();
$sessionid ? '' : $sessionid = session_id();

//system-level emails
$developerEmail='sam-git@samuelfullman.com';
$fromHdrBugs='From: bugreports@'.$GLOBALS['HTTP_HOST'];

//settings for members branch - see below for more after config.console.php called
define('CONTACTS_COMPANY',1);
define('CLIENTS_COMPANY',2);

define('COL_VISIBLE',16,false);
define('COL_AVAILABLE',8,false);
define('COL_SYSTEM',4,false);
define('COL_HIDDEN',2,false);
define('COL_RESTRICTED',1,false);

//key folder locations
$CONSOLE_ROOT	= 		$_SERVER['DOCUMENT_ROOT'].'/console';
$COMPONENT_ROOT = 		$_SERVER['DOCUMENT_ROOT'].'/console/components'; //this needs to mean the "master" component root instead
$CONSOLE_COMPONENT_ROOT=$_SERVER['DOCUMENT_ROOT'].'/console/components'; //new as of 2013-12-05 - preferred use for var above
$FUNCTION_ROOT	= 		$_SERVER['DOCUMENT_ROOT'].'/functions';
$MASTER_COMPONENT_ROOT= $_SERVER['DOCUMENT_ROOT'].'/components';			  //this needs to die
$JULIET_COMPONENT_ROOT= $_SERVER['DOCUMENT_ROOT'].'/components-juliet';
$PAGE_ROOT=$_SERVER['DOCUMENT_ROOT'].'/pages';

$globalSetCtrlFields=true;
$tabVersion=3.0;

if(!function_exists('config_get')){
    /**
     * config_get: return defined variables for multiple config(.php) files in order called.  File paths must be readable as-is.
     *
     * @created = 2017-07-13
     * @author = Sam Fullman <sam-git@samuelfullman.com>
     * @param $__files
     * @param array $__config (Note: this position is reserved if needed)
     * @param array $__args
     * @return array
     */
    function config_get($__files, $__config = [], $__args = []){
        /*
         * Example of use:
         * ---------------
         * $files = ['../private/config.php', '../private/qa/config.php'];
         * print_r(config_get($files, [], ['foo'=>'bar']));
         */

        // File input list must be valid
        if(empty($__files) || !is_array($__files)) return $__args;

        // Accept only valid readable files
        foreach($__files as $__n=>$__v){
            unset($__files[$__n]);
            if(!is_readable($__v) || !is_file($__v)) continue;

            // Read the file
            require($__v);
            break;
        }

        // Collect defined vars in config file, or array if none present
        $__working = get_defined_vars();
        foreach(['__files', '__config', '__args', '__n', '__v'] as $__unset) unset($__working[$__unset]);
        $__args = array_merge($__args, $__working);

        return config_get($__files, $__config, $__args);

    }
}

// Get config files by precedence
$config = [ $_SERVER['DOCUMENT_ROOT'] . '/../private/config.php' ];
if($appEnv){
    $config[] = str_replace('/private/config.php', '/private/'.$appEnv.'/config.php', $config[0]);
}
$config = config_get($config);
extract($config);

require_once($FUNCTION_ROOT.'/function_q_v130.php');
require_once($FUNCTION_ROOT.'/function_prn.php');

$qx['useRemediation']=true;
$qx['tableList']=array('bais_settings');

$sql="SELECT
    IF(a.DbseName, a.DbseName, a.AcctName) AS MASTER_DATABASE,
    a.AcctName AS RECORD_MASTER_DATABASE,
    a.UserName AS MASTER_USERNAME,
    a.HostName AS MASTER_HOSTNAME,
    pivot.Password AS MASTER_PASSWORD,
    IF(ctc.FirstName IS NULL, CONCAT(pivot.FirstName,' ',pivot.LastName),pivot.Company) AS adminCompany,
    IF(ctc.FirstName IS NULL, pivot.FirstName, ctc.FirstName) AS adminFirstName,
    IF(ctc.LastName IS NULL, pivot.LastName, ctc.LastName) AS adminLastName,
    IF(ctc.Email IS NULL, pivot.Email, ctc.Email) AS adminEmail,
    IF(ctc.Phone IS NULL, pivot.Phone, ctc.Phone) AS adminPhone,
    
    pivot.Address AS adminAddress,
    pivot.City AS adminCity,
    pivot.State AS adminState,
    pivot.Zip AS adminZip,
    pivot.Country AS adminCountry,
    
    m.ID AS mid,
    m.Status AS ModuleStatus,
    mi.Source AS ExtractConfig
FROM
    rbase_account a
    LEFT JOIN rbase_userbase pivot ON a.AcctName=pivot.UserName
    LEFT JOIN rbase_UserbaseUserbase ub ON pivot.UserName = ub.Parent_UserName AND ub.Type='Primary'
    LEFT JOIN rbase_userbase ctc ON Child_UserName=ctc.UserName,
    rbase_AccountModules am,
    rbase_modules m LEFT JOIN rbase_modules_items mi ON m.ID=mi.Modules_ID AND mi.Types_ID=5
WHERE
    a.ID=am.Account_ID AND
    am.Modules_ID=m.ID AND
    a.AcctName='".addslashes($acct)."' AND
    m.SKU='051'
GROUP BY a.AcctName";
$err1='Unable to pull your RelateBase module for account '.$acct.' - either (1) your RelateBase account or module have not been set up, or (2) Your module has a configuration error (a) SKU!=051 or (b)No item with Type=Configuration.  An email has been sent to the developer';
$err2='You currently have an error in your RelateBase account '.$acct.' - more than one "051" type module was present.  An email has been sent to the developer';


if($acctData=q($sql, O_ARRAY, C_SUPER)){
	//OK
	if(count($acctData)>1){
		mail($developerEmail,'Error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
		exit($err2);
	}else{
		$acctData=$acctData[1];
		@extract($acctData);
		if(preg_match('/<serialized[^>]*>([^<]+)<\/serialized>/i',$ExtractConfig,$a)){
			$moduleConfig=@unserialize(base64_decode($a[1]));
			@extract($moduleConfig);
		}else{
			if(is_null($ExtractConfig)){
				mail($developerEmail,'Error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
				q("INSERT INTO relatebase_rfm.rbase_modules_items SET
				Modules_ID=$mid,
				Mst_Items_ID=1,
				Types_ID=5,
				CreateDate=NOW(),
				Creator='".$_SESSION['systemUserName']."'",C_SUPER);
			}
			$moduleConfig=array();
			
			//some things we MUST have
			$settings['ClientWord']='Customer';
			$settings['ItemWord']='Item';
			
		}
	}
}else{
	exit($err1);
}

if($ModuleStatus<50){
	mail($developerEmail,'module status < 50; Error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
	exit('Your Ecommerce Console module is currently expired or not active.  Please contact an administrator');
}
if($useDevLogicForDatabase){
	$a=explode('/',$_SERVER['DOCUMENT_ROOT']);
	if($a[3]!=='public_html') $MASTER_DATABASE.='_'.$a[3];
}
if($alternateUseDatabase){
	$MASTER_DATABASE=$alternateUseDatabase;
}
//----------------- codeblock 088233 ---------------------
$consoleEmbeddedModulesSKUs=array(
	'RSC-01','RSC-20','040','051','CGI-70','REM-90',
);
$systemEmbeddedModules=array(
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
	m.ID, m.SKU, m.UserName, m.AdminSettings, m.Settings, mi.Source
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
		if($v['AdminSettings']){
			$consoleEmbeddedModules[$n]['moduleAdminSettings']=unserialize(base64_decode($v['AdminSettings']));
		}
		if($v['SKU']=='040'){
			//ecommerce module - the old "SHOPCART - 01"
			$mid=$n; //mid = "module id" = CART module id (this was before I got into multiple modules)
			$cartAcct=$acct; //hack..			
		}else if($v['SKU']=='RSC-01'){
			//site creator
			
		}
		$gettable_parameters[$v['SKU']]=array();
		if(!empty($consoleEmbeddedModules[$n]['moduleAdminSettings']['gettable_parameters'])){
			foreach($consoleEmbeddedModules[$n]['moduleAdminSettings']['gettable_parameters'] as $o=>$w){
				$gettable_parameters[$v['SKU']][$o]=(is_array($w) ? $w[0] : $w);
			}
		}
		$settable_parameters[$v['SKU']]=array();
		if(!empty($consoleEmbeddedModules[$n]['moduleAdminSettings']['settable_parameters'])){
			foreach($consoleEmbeddedModules[$n]['moduleAdminSettings']['settable_parameters'] as $o=>$w){
				$settable_parameters[$v['SKU']][$o]=(is_array($w) ? $w[0] : $w);
			}
		}
	}
}else{
	$consoleEmbeddedModules=array();
}
if(!function_exists('array_merge_accurate'))require($FUNCTION_ROOT.'/function_array_merge_accurate_v100.php');
//$consoleEmbeddedModules=array_merge_accurate($systemEmbeddedModules,$consoleEmbeddedModules);

/* Just got $cartAcct and $mid, might as well set the shopping cart url */
$shoppingCartURL = 'https://www.relatebase.com/c/cart/en/v410/?sessionid='.($sessionid ? $sessionid : $GLOBALS['PHPSESSID']).'&acct='.$cartAcct.'&mid='.$mid;

$tabList['items'][0]=array(
	'parts'=>'Package Makeup',
	'description'=>'Description',
	'pricing'=>'Pricing',
	'manufacturer'=>'Manufacturer',
	'attributes'=>'Attributes',
	'media'=>'Files and Media'
);
$qbksItemTypes=array(
	'Service'=>'SERV',
	'Inventory part'=>'INVENTORY',
	'Non-inventory part'=>'PART',
	'Other charge'=>'OTHC'
);
$defaultCrossModelUpdateFields=array(
	'Model'=>true,
	'Name'=>true,
	'Description'=>true,
	'LongDescription'=>true,
	'Category'=>true,
	'SubCategory'=>true,
	'Manufacturer'=>true,
	'Manufacturers_ID'=>true,
	'Keywords'=>true,
	'UnitPrice'=>false,
	'UnitPrice2'=>false
);

$moduleVersion='2.0';
$moduleRevision='1';
$ctime=time();
//this is the system configuration file, created by user systeam 
if(!isset($localSys['scriptID']) || !isset($localSys['scriptVersion']))exit('CONFIG.PHP: Script ID (handle) and version not declared for '.$_SERVER['PHP_SELF'].', componentID is optional');


//name of this page
if(substr($GLOBALS['REQUEST_URI'],0,strlen($GLOBALS['PHP_SELF']))==$GLOBALS['PHP_SELF'] || !trim($GLOBALS['REQUEST_URI'],'/')){
	//previous page/folder method
	if(!strlen($thispage) || !isset($thisfolder)){
		$a=preg_split('/\\\|\//',$GLOBALS['PHP_SELF']);
		$thispage=$a[count($a)-1];
		if(count($a)>2){
			$thisfolder=$a[count($a)-2];
		}else{
			$thisfolder='';
		}
	}
}else{
	//2009-04-24, new method: presumed 404 page masquerading as other page, get page from REQUEST_URI
	$a=explode('?',$GLOBALS['REQUEST_URI']);
	$a=preg_split('/\\\|\//',$a[0]);
	$thispage=$a[count($a)-1];
	if(count($a)>2){
		$thisfolder=$a[count($a)-2];
	}else{
		$thisfolder='';
	}
	//NEW 2010-06-29
	if($GLOBALS['REDIRECT_QUERY_STRING']){
		$QUERY_STRING=$GLOBALS['QUERY_STRING']=$GLOBALS['REDIRECT_QUERY_STRING'];
		parse_str($GLOBALS['REDIRECT_QUERY_STRING']);
	}
}

//browser detection
if(preg_match('/^Mozilla\/4/i',$GLOBALS['HTTP_USER_AGENT'])){
	//Internet Explorer current versions
	$browser='IE';
}else if(preg_match('/^Mozilla\/5/i',$GLOBALS['HTTP_USER_AGENT'])){
	//Firefox, Mozilla
	$browser='Moz';
}else if(!preg_match('/gigabot|msnbot/i',$GLOBALS['HTTP_USER_AGENT'])){
	//mail($developerEmail,'Unknown browser type',$HTTP_USER_AGENT.', called from file '. $thisfolder . '/'. $thispage,$fromHdrBugs);
	$browser='Moz'; #assume
}

//global variables and arrays
$canada 				= array('AB','BC','MB','NB','NF','NS','NT','ON','PE','PQ','QC','SK','YT');
$militaryPOs			= array('AA','AE','AP');
$normalTitles			= array('mr.','mrs.','dr.','rev.','mr. & mrs.','ms.');
$normalPhoneTypes		= array('cell1','cell2','faxday1','faxevening1','home office','pager / voicemail','phoneday1','phoneday2','phoneevening1','phoneevening2'); //all lower case
$normalAddressTypes	= array('home','business','school');
$tlds=array('com','net','org','info','biz','uk','cc','tv','edu','us');

$blankFills = array(
	'un_username'=>' User Name',
	'Email'=>' Email',
	'FirstName'=>' First Name',
	'MiddleName'=>' M.N.',
	'LastName'=>' Last Name',
	'Address'=>' Address',
	'City'=>' City',
	'Phone'=>' Phone',
	'WorkPhone'=>' Work Phone',
	'Fax'=>' Fax',
	'Zip'=>' Zip',
	'DateReleased'=>' (N/A)',
	'Caption'=>'(optional)'
);

//no longer needed - default is all accounts are usemod
$usemodAccts=array(
	'cpm076'=>true, /* mps */
	'cpm192'=>true, /* hpn */
	'cpm155'=>true, /* tsa */
	'cpm004'=>true, /* pbs */
	'cpm191'=>true, /* cj */
	'cpm006'=>true,
	'cpm185'=>true, /* wro */
	'cpm187'=>true,
	'cpm204'=>true, /* jls */
	'cpm081'=>true, /* fog */
	'cpm203'=>true, /* att */
	'cpm207'=>true, /* otsm */
	'cpm210'=>true, /* rv  */
);

//moved over 2009-02-04: handle post new values
if(count($_POST))
foreach($_POST as $n=>$v){
	if(strlen($_POST[$n.'_RBADDNEW']) && $_POST[$n.'_RBADDNEWMODIFICATION']=='distinct'){
		if($_POST[$n]=='{RBADDNEW}'){
			//as it should be
			unset($$n,$_POST[$n]);
			$$n=$_POST[$n]=$_POST[$n.'_RBADDNEW'];
			if(!$persistPostNewValues)unset($GLOBALS[$n.'_RBADDNEW'], $_POST[$n.'_RBADDNEW'], $GLOBALS[$n.'_RBADDNEWMODIFICATION'], $_POST[$n.'_RBADDNEWMODIFICATION']);
		}else{
			//js error - should not happen
			//mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
		}
	}
}

$modApType='embedded';
$modApHandle='first';

//last updated 2011-03-15 - added Site Creator Pages
$DHTMLmenu=array(
    '(settings)'=>array( /* settings */ ),
    'Home'=>array(
        '(settings)'=>array( /* settings */ ),
        'Admin Home Page'=>array(),
        'Settings'=>array(),
    ),
    'Site Creator'=>array(
        '(settings)'=>array(
            /* settings */
            'DHTMLmenu_evaluate'
        ),
        'Template Manager'=>array(),
        'Menu Manager'=>array(),
        'Page Manager'=>array(),
        'Site Creator Help'=>array(),
    ),
    'Shopping Cart'=>array(
        '(settings)'=>array(
            /* settings */
            'DHTMLmenu_evaluate'
        ),
        'Manage Settings'=>array(),
        'Create Sample Order'=>array(),
        'Recompile Cart'=>array(),
    ),
    'Items'=>array(
        '(settings)'=>array( /* settings */ ),
        'List Items'=>array(),
        'Add New Item'=>array(),
        'Manufacturers'=>array(),
        'Shippers'=>array(),
        'Chart of Accounts'=>array(),
    ),
    'Members'=>array(
        '(settings)'=>array( /* settings */ ),
        'List Members'=>array(),
        'Add New Member'=>array(),
        'List Contacts'=>array(),
        'Add New Contact'=>array(),
        'Usemod Settings'=>array(),
    ),
    'Orders'=>array(
        '(settings)'=>array( /* settings */ ),
        'List Orders'=>array(),
        'Add Order/Invoice'=>array(),
        'Reporting'=>array(),
    ),
    'Content'=>array(
        '(settings)'=>array( /* settings */ ),
        'List Articles'=>array(),
        'Add New Article'=>array(),
        'List Albums'=>array(),
        'Linking and SEO'=>array(),
    ),
    'Events'=>array(
        '(settings)'=>array( /* settings */ ),
        'List Events'=>array(),
        'Add New Event'=>array(),
    ),
    'Clothing Product Options'=>array(
        '(settings)'=>array(
            /* settings */
            'DHTMLmenu_evaluate'
        ),
        'Colors and Brands'=>array(),
        'Styles and Brands'=>array(),
    ),
    'Classifieds'=>array(
        '(settings)'=>array(
            /* settings */
            'DHTMLmenu_evaluate'
        ),
        'List Classifieds'=>array(),
        'New Classified'=>array(),
    ),
    'Properties'=>array(
        '(settings)'=>array( /* settings */
                             'DHTMLmenu_evaluate'
        ),
        'Show All Listings'=>array(),
        'Add New Listing'=>array(),
    ),
    'Images'=>array(
        '(settings)'=>array( /* settings */ ),
        'File Explorer'=>array(),
    ),
    'Utilities'=>array(
        '(settings)'=>array( /* settings */ ),
        'Import and Export Manager'=>array(),
        'Statistics'=>array(),
    ),
    'Help'=>array(
        '(settings)'=>array( /* settings */ ),
        'With this page..'=>array(),
        'About'=>array(),
    ),
);


/* ---------------------------- functions only from here ------------------------ */
if(!function_exists('h')){
function h($v){
	return htmlentities($v);
}
}
if(!function_exists('eOK')){
function eOK($l=''){
	global $assumeErrorState,$suppressPrintEnv,$suppressNormalIframeShutdownJS;
	if(strtolower($l)=='quiet'){
		$l='';
		$suppressNormalIframeShutdownJS=true;
	}
	$assumeErrorState=false;
	exit($suppressPrintEnv || !$l ? '' : 'exit line '.$l);
}
}
if(!function_exists('email_encoded')){
function email_encoded($email,$label=''){
	return '<a title="'.$email.'" href="mailto:'.$email.'">'.($label ? $label : $email).'</a>';
}
}
if(!function_exists('stripslashes_deep')){
function stripslashes_deep($value){
	$value = is_array($value) ?
	array_map('stripslashes_deep', $value) :
	stripslashes($value);
	return $value;
}
}
if(!function_exists('addslashes_deep')){
function addslashes_deep($value){
	$value = is_array($value) ?
	array_map('addslashes_deep', $value) :
	addslashes($value);
	return $value;
}
}
if(!function_exists('get_globals')){
function get_globals($msg=''){
	ob_start();
	//snapshot of globals
	$a=$GLOBALS;
	//unset redundant nodes
	unset($a['HTTP_SERVER_VARS'], $a['HTTP_ENV_VARS'], $a['HTTP_GET_VARS'], $a['HTTP_COOKIE_VARS'], $a['HTTP_SESSION_VARS'], $a['HTTP_POST_FILES']);
	print_r($a);
	unset($a);
	$out=ob_get_contents();
	ob_end_clean();
	return ($msg ? "\nMessage:".$msg : '')."\n\n" . $out;
}
}
if(!function_exists('error_alert')){
function error_alert($x,$options=array()){
	global $assumeErrorState,$error_alert;
	if(is_array($options)){
		extract($options);
	}else{
		$continue=$options;
	}
	/*
	parameters stroable in $error_alert global
	------------------------------------------
	storeErrorAlert
	errors=array()
	
	*/
	echo "\n";
	echo '<!-- error_alert() called -->'."\n";
	if(strlen($error_alert['storeErrorAlert']) && $error_alert['storeErrorAlert']==md5($GLOBALS['MASTER_PASSWORD'])){
		$error_alert['errors'][]=$x;
	}else{
		?><script language="javascript" type="text/javascript">
		alert('<?php echo $x?>');
		<?php if($focusField){ ?>
		window.parent.g('<?php echo $focusField?>').focus();
		window.parent.g('<?php echo $focusField?>').select();
		<?php } ?>
		</script><?php
		if(!$continue){
			$assumeErrorState=false;
			exit;
		}
	}
}
}
if(!function_exists('valid_email')){
function valid_email($x){
	if(preg_match('/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)+$/',$x))return true;
	return false;
}
}
if(!function_exists('iframe_shutdown')){
function iframe_shutdown(){
	/*
	2011-12-17 version 2.00
		* got output working finally - why did I wait so long for this?
	2007-03-21 @6:21AM 	version 1.01 
		* cleaned things up and started depending on external fctns like get_globals(); this version was used in jboyce.com
	*/
	global $qr, $qx, $iframe_shutdown_options, $assumeErrorState, $parentUnSubControl, $suppressNormalIframeShutdownJS, $developerEmail, $fromHdrBugs, $suppressMailOutput;
	if(!$suppressNormalIframeShutdownJS){
		?><script language="javascript" type="text/javascript">
		//notify the waiting parent of success, prevent timeout call of function
		window.parent.submitting=false;
		try{
			if(<?php echo $parentUnSubControl ? 'true' : 'false' ?>){
				eval('<?php echo $parentUnSubControl?>');
			}else{
				window.parent.document.getElementById('SubmitApplication').disabled=false;
				window.parent.document.getElementById('SubmitStatus1').innerHTML=' ';
			}
		}catch(e){ }
		</script><?php
	}
	if(!$assumeErrorState)return false; //this is the end of the script

	//for mailing
	if(!$suppressMailOutput){
		$out=ob_get_contents();
		ob_end_clean();
		if(strlen($out))echo "\n".'HTML output successfully intercepted';
	}

	//handle errors
	?><script>
	//for the end user - you can improve this rather scary-sounding message
	try{
		window.parent.g('ctrlSection').style.display='block';
	}catch(e){ }
	alert('We\'re sorry but there has been an abnormal error while submitting your information, and staff have been emailed.  Please try refreshing the page and entering your information again');
	</script><?php

	//we also mail that this has happened
	unset($mail);
	if($fl)$mail.="\n".'File: '.$fl;
	if($ln)$mail.="\n".'Line: '.$ln;

	$mail.="\n\n----------- Part 1: ENVIRONMENT VARIABLES -------------\n";
	$mail.=get_globals();
	
	//Page Output - normally we print out results after each SQL query for example
	if(strlen($out))$mail.="\n\n----------- Part 2: HTML OUTPUT -------------\n".unhtmlentities($out);
	
	//send email notification
	$acct=end(explode('/',trim(preg_replace('/juliet|public_html|dev/i','',$_SERVER['DOCUMENT_ROOT']),'/')));
	mail($developerEmail,'Ab-shutdown: '.$acct, $mail, $fromHdrBugs);
	return true;
}
}
if(!function_exists('page_end')){
function page_end(){

}
}
if(!function_exists('js_user_settings')){
function js_userSettings($bookends=false){
	//declare js for user settings - created 2008-04-20
	if($bookends)echo '<script language="javascript" type="text/javascript" id="jsUserSettings">'."\n";
	if($_SESSION['userSettings'])
	foreach($_SESSION['userSettings'] as $n=>$v){
		$x=(!strlen($v) ? "''" : (is_numeric($v) ? $v : ("'".str_replace("'","\'",$v)."'")));
		if(strstr($n,':')){
			$n=explode(':',$n);
			if(!$called[$n[0]]){
				$called[$n[0]]=true;
				echo 'var '.$n[0].'= new Array();'."\n";
			}
			echo $n[0].'[\''.$n[1].'\']= '.$x.';'."\n";
		}else{
			echo 'var '.$n . '='.$x.';'."\n";
		}
	}
	if($bookends)echo '</script>'."\n";
}
}
if(!function_exists('DHTMLmenu_evaluate')){
function DHTMLmenu_evaluate($o,$k){
	//brought over from Gi ocosaCa re
	global $apSettings, $consoleEmbeddedModules,$fromHdrBugs,$developerEmail;
	switch($o){
		case 'Clothing Product Options':
			if(count($consoleEmbeddedModules))
			foreach($consoleEmbeddedModules as $v)
			if(strtolower($v['SKU'])=='cpro-01')return 1;
		break;
		case 'Site Creator':
			if(count($consoleEmbeddedModules))
			foreach($consoleEmbeddedModules as $v)
			if(strtolower($v['SKU'])=='rsc-01')return 1;
		break;
		case 'Shopping Cart':
			if(count($consoleEmbeddedModules))
			foreach($consoleEmbeddedModules as $v)
			if(strtolower($v['SKU'])=='040')return 1;
		break;
		case 'Properties':
			global $acct;
			if($acct=='cpm135' || $acct=='cpm208')return 1;
		break;
		case 'Classifieds':
			global $acct;
			if($acct=='cpm075')return 1;
		break;
		/* old from Gi ocosaCa re --
		case 'Quickbooks Exporting':
			return ($apSettings['implementQuickBooks'] ? 1 : 0);
		*/
		default:
			mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
			return 1;
	}
}
}
if(!function_exists('DHTMLmenu')){
function DHTMLmenu($roles=''){
	//this must get changed with the DHTML Menu Generator (sothink)
	//smallest array index is PERMISSION_LEVEL 
	global $apSettings, $DHTMLmenu;
	if(!$roles)$roles=array(
		1=>1,
		2=>2,
		3=>3,
		5=>5,
		10=>10,
		20=>20,
	);
	
	$show[1]=1; //beginSTM
	$show[2]=1; //beginSTMB
	$k=2;
	foreach($DHTMLmenu as $n=>$col){
		if($n=='(settings)')continue;
		if(!$roles && !in_array($n,array('Home Page','Help'))){
			$showPrimary=0;
		}else if($str=$col['(settings)'][0]){
			//---------------------------------------------
			if(is_numeric($str)){
				$showPrimary=(@min($roles) > $str ? 0 : 1); 
			}else if(is_array($str)){
				foreach($str as $g=>$h){
					if($roles[$h]){
						$showPrimary=1;
						break;
					}
					$showPrimary=0;
				}
			}else{
				$showPrimary=$str($n,$k);
			}
			//---------------------------------------------
		}else{
			$showPrimary=1;
		}
		//appendSTMI
		$k++;
		$show[$k]=$showPrimary;
		
		//beginSTMB
		$k++;
		$show[$k]=$showPrimary;
		foreach($col as $o=>$w){
			if($o=='(settings)')continue;
			if($w[0]){
				//--------------------------------
				if(is_numeric($w[0])){
					$showSecondary=(@min($roles) > $w[0] ? 0 : 1);
				}else if(is_array($w[0])){
					foreach($w[0] as $g=>$h){
						if($roles[$h]){
							$showSecondary=1;
							break;
						}
						$showSecondary=0;
					}
				}else{
					$function=$w[0];
					$showSecondary=$function($o,$k);
				}
				//--------------------------------
			}else{
				$showSecondary=1;
			}
			//appendSTMI - ADD AN ELEMENT
			$k++;
			$show[$k]=($showSecondary && $showPrimary? 1 : 0);
		}
		//endSTMB
		$k++;
		$show[$k]=$showPrimary;
	}
	$k++;
	$show[$k]=true;
	$k++;
	$show[$k]=true;
	return $show;
}
}
if(!function_exists('sun')){
function sun($n=''){
	/*
	v1.00 2013-11-11: this is the most advanced version; for cnx, we are agnostic about .identity...
	*/
	global $acct;
	if($_SESSION['admin']['userName']){
		extract($_SESSION['admin']);
		switch($n){
			case 'e': return $email;
			case 'fl': return $firstName . ' '. $lastName;
			case 'lf': return $lastName . ', '.$firstName;
			case 'lfi': return $lastName.', '.$firstName.($middleName?' '.substr($middleName,0,1).'.':'');
			default: return $userName;
		}
	}else if(($a=$_SESSION['cnx'][$acct]) && $_SESSION['systemUserName']){
		extract($a);
		switch($n){
			case 'e': return $email;
			case 'fl': return $firstName . ' '. $lastName;
			case 'lf': return $lastName . ', '.$firstName;
			case 'lfi': return $lastName.', '.$firstName.($middleName?' '.substr($middleName,0,1).'.':'');
			default: return $_SESSION['systemUserName'];
		}
	}else{
		return $GLOBALS['PHP_AUTH_USER'];
	}
}}
function dynamic_title($static=true){
    global $SCRIPT_NAME, $QUERY_STRING, $CustomTitle;
    $url=$SCRIPT_NAME.($QUERY_STRING?'?'.$QUERY_STRING:'');
    $return=q("SELECT VarKey FROM bais_settings WHERE UserName='".sun()."' AND VarGroup='Custom Report' AND VarValue='$url'",O_VALUE);
    if($return>''){
        $CustomTitle=$return;
        return($return);
        q("UPDATE bais_settings SET EditDate=NOW() WHERE UserName='".sun()."' AND VarGroup='Custom Report' AND VarValue='$url'");
    }else{
        return($static);
    }
}
function sql_table_relationships($options=array()){
	/* created 2012-10-15: this was in form_field_presenter() but is atomic enough to pull out */
	global $sql_table_relationships;
	if($sql_table_relationships['relations'])return $sql_table_relationships['relations'];
	extract($options);
	//see when tables were touched
	if(!$db){
		global $GCUserName;
		$db=$GCUserName;
	}
	foreach($a=q("SHOW TABLES", O_ARRAY) as $v){
		$t=current($v);
		if(substr($t,0,1)=='_')continue; //speed it up 
		$h=md5(preg_replace('/AUTO_INCREMENT=[0-9]+/','',end(q("SHOW CREATE TABLE ".current($v), O_ROW))));
		$hash.=$h;
	}
	$hash=md5($hash);
	if($_SESSION['relations_hash']!=$hash || $refreshRelations){
		$_SESSION['relations_hash']=$hash;
		?><div id="getting">getting database table information..</div><?php
		flush();
		foreach(q("SHOW TABLES", O_ARRAY) as $v){
			$n=current($v);
			$compCognate=strtolower(current(explode('_',$n)));
			if(substr($n,0,1)=='_')continue;
			$_fields=q("EXPLAIN $n", O_ARRAY);
			$primary='';
			$cog=strtolower(end(explode('_',$n)));
			$label=false;
			foreach($_fields as $w){
				if($w['Key']=='PRI'){
					$primary=strtolower($w['Field']);
					if($relations[$cog.'_'.$primary]){
						if($compCognate!=$cognate){
							//this one is not any better
							break;
						}else{
							#prn('replacing relation table with '.$n);
						}
					}
					$relations[$cog.'_'.$primary]=array(
						'table'=>$n,
					);
				}
				if($primary && !$label && preg_match('/char|varchar/',$w['Type']) && !preg_match('/creator|editor/i',$w['Field']) && (q("SELECT COUNT(DISTINCT `".$w['Field']."`)/COUNT(*) FROM $n WHERE `".$w['Field']."`!='' AND `".$w['Field']."` IS NOT NULL", O_VALUE)==1 || !q("SELECT COUNT(*) FROM $n WHERE `".$w['Field']."`!='' AND `".$w['Field']."` IS NOT NULL",O_VALUE))){
					$label=true;
					$relations[$cog.'_'.$primary]['label']=$w['Field'];
				}
			}
			/* 2012-11-01 this is not being used at this time */
			if(false)
			$sql_table_relationships['tables'][strtolower($n)]=$_fields;
		}
		$_SESSION['relations']=/* not used! $sql_table_relationships['relations']=*/$relations;
		?><script language="javascript" type="text/javascript">g('getting').style.display='none';</script><?php
		return $relations;
	}else{
		return $_SESSION['relations'];
	}
}


