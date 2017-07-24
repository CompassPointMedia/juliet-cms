<?php
/* ------------------------------------------------------------------
2012-10-27: 
* allowed this to reference a local file to determine acct value
* sunsetted $uid and ONFIRSTVISIT, etc. constants


	2011-02-19: simplified config.php page - used for Juliet Project 2011
	_SESSION related information **MUST** be placed/declared after the master config include

--------------------------------------------------------------------- */

$appEnv = getenv('AppEnv');

//standard error reporting/display coding
function set_test_env(){
    error_reporting(E_ALL | E_STRICT);
    $AppEnv = getenv('AppEnv');
    if($AppEnv == 'production'){
        ini_set('display_errors',false);
    }else if($AppEnv == 'vagrant' || $AppEnv == 'develop' || $AppEnv == 'qa') {
        ini_set('display_errors',false);
    }else{
        // for now
        ini_set('display_errors',true);
    }
}
set_test_env();

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
         * $files = ['../private/conf.php', '../private/qa/conf.php'];
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
$config = [str_replace('/config.php', '/../private/config.php', __FILE__)];
if($appEnv){
    $config[] = str_replace('/private/config.php', '/private/'.$appEnv.'/config.php', $config[0]);
}
$config = config_get($config);
extract($config);

//if they have juliet, they are going to have the console and site creator

$removeThispageExtension=true;
$lowercaseThispage=true;

$JULIET_COMPONENT_ROOT=$_SERVER['DOCUMENT_ROOT'].'/components-juliet';
$PAGE_ROOT=$_SERVER['DOCUMENT_ROOT'].'/pages';

if(!empty($fromCRON)) goto compend;

if(!function_exists('q'))require_once($_SERVER['DOCUMENT_ROOT'].'/functions/function_q_v130.php');
if(!function_exists('prn'))require_once($_SERVER['DOCUMENT_ROOT'].'/functions/function_prn.php');
$qx['useRemediation']=true;
$qx['defCnxMethod']=C_MASTER;

if(empty($pJulietTemplate)){
    $pJulietTemplate=$_SERVER['DOCUMENT_ROOT'].'/Templates/relatebase_05_generic.php';
}
$overrideGeneric5tDecoding=true;

// Responsible for session_start();
require($_SERVER['DOCUMENT_ROOT'].'/components/master_config_v103.php');

// Add authentication for non-production, non-local environments (qa, develop, etc.)
if($appEnv !== 'production' && !($appEnv == 'vagrant' || $appEnv == 'local')) {
    // Very simple login form - see deprecated approach below this
    if(empty($_SESSION['develop_mode'])){
        if(isset($_REQUEST['develop_username'])){
            if(strtolower($_REQUEST['develop_username']) == $MASTER_USERNAME && $_REQUEST['develop_password'] == $MASTER_PASSWORD){
                $_SESSION['develop_mode'] = 'Develop site, logged in as '.$MASTER_USERNAME.' at '.date('Y-m-d H:i:s');
                if(!empty($_REQUEST['src'])){
                    header('Location: '.$src);
                    exit;
                }
            }else{
                exit('Your username and password were not correct.  You must use the account username and password for the site.  Go back and try again');
            }
        }else{
            ?>
            <form>
                <h1>Developer Site</h1>
                <p><strong>Enter your site's primary account name and password to edit this site.  Note, this is a development site, not production</strong></p>
                <p>User name: <input type="text" name="develop_username" /></p>
                <p>Password: <input type="password" name="develop_password" /> </p>
                <input type="submit" value="Submit" />
            </form>
            <?php
            exit;
        }
    }
    /*
    /!\ NOTE: this was conflicting with auth passwords in sub-folders, so I removed it.  Left here for legacy but I think relying on session is much better
    if (empty($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] != $MASTER_USERNAME || $_SERVER['PHP_AUTH_PW'] != $MASTER_PASSWORD) {
        header('WWW-Authenticate: Basic realm="Development site"');
        header('HTTP/1.0 401 Unauthorized');
        exit('This is a development server and requires a username and password; hit F5 and try again!');
    } else {
        if (strtolower($_SERVER['PHP_AUTH_USER']) != $MASTER_USERNAME) {
            exit('Invalid username; use the MASTER_USERNAME for your site.  Hit F5 and try again.');
        }
        if ($_SERVER['PHP_AUTH_PW'] !== $MASTER_PASSWORD) {
            exit('Invalid password; Hit F5 and try again.');
        }
    }
    */
}



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

compend:

