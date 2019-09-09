<?php
/**
Juliet CMS
 * 2019-09-09: $pJulietTemplate must no be declared in private config is desired
 * 2017-07-16: Added ability to get multiple config files by precedence, for example ../private/config.php and ../private/vagrant/config.php
*/

/**
 * Declare framework - we could use CI or Laravel etc.
 */
$framework = 'juliet-ci-supplement';

require(str_replace('/index.php','/config.php', __FILE__));


//2011-03-26 process the requested page - first folder is a component, 2nd folders are parameters, then the file
if(!empty($__page__)){
	$a = explode('/',$__page__);
	$component = (count($a)>1 ? $a[0] : '');
	$node = preg_replace('/\.(php|htm|html|asp|jsp)$/i','',$a[count($a)-1]);
	$julietParams = [];
	if(count($a)>2) for($i=1; $i<count($a)-1; $i++) $julietParams[]=$a[$i];
}


//we would normally pull this from the database
//plus any stylesheet configurations


/* ------ database settings --------- */
$object['topNav']='relatebase_01_topnav_style01.php';
$object['leftNav']='relatebase_01_leftnav_style01.php';
$object['leftNavSlide']='relatebase_01_leftnavslide_style01.php';


$systemPageNames=array(
	'{root_website_page}'=>array(
		'url'=>'/',
		'name'=>'Home Page',
	),
);


if(!empty($pJulietTemplate)){
    require($pJulietTemplate);
}else{
    chdir($framework);
    require('index.php');
}


