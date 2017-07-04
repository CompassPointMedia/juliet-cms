<?php
/*
priorities:
* identify an account for this
* get the public_html folder pulling 100% symlinks exc. possibly a posting page like for file explorer or console

todo:
components like nav or flex in a library with their own self-contained editing form
link to a single product and refine this method


*/

require(str_replace('/index.php','/config.php',__FILE__));

//2011-03-26 process the requested page - first folder is a component, 2nd folders are parameters, then the file
if($__page__){
	$a=explode('/',$__page__);
	$component=(count($a)>1 ? $a[0] : '');
	$node=preg_replace('/\.(php|htm|html|asp|jsp)$/i','',$a[count($a)-1]);
	$julietParams=array();
	if(count($a)>2)for($i=1; $i<count($a)-1; $i++)$julietParams[]=$a[$i];
}

//we would normally pull this from the database
//plus any stylesheet configurations

/* ------ database settings --------- */
$object['topNav']='relatebase_01_topnav_style01.php';
$object['leftNav']='relatebase_01_leftnav_style01.php';
$object['leftNavSlide']='relatebase_01_leftnavslide_style01.php';
$leftInsetHeader='Products and Services';
$quickJumpField1='Theme';
$quickJumpField2='Function';

//custom configurations - push down to lowest level acceptable (the object itself if possible) until we can be solid on aggregating assets like js and css
$flash=array(
	'home'=>array(
		'index.php',
		'cmi_02.dwt.php',
	),
	'about us'=>array(
	),
);

$systemPageNames=array(
	'{root_website_page}'=>array(
		'url'=>'/',
		'name'=>'Home Page',
	),
);
function get_systemname($n, $return=''){
	global $systemPageNames;
	$a=$systemPageNames[strtolower($n)];
	return ($return ? $a[$return] : $a);
}

require('Templates/relatebase_05_generic.php');


