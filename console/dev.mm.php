<?php
//identify this script/GUI
$localSys['scriptID']='generic';
$localSys['scriptVersion']='1.0';
$localSys['componentID']='main';
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php'); 
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');
require_once($FUNCTION_ROOT.'/function_mm_v110.php');


if($disposition=='remoteSubmission'){
	$mmopt=array(
		'sections'=>array(
			'section3'=>array(
				'method'=>'jsonp.01',
			),
			'section1'=>array(
			),
			'section2'=>array(
			),
		),
		'immediate_call'=>true,
		'this'=>'mmopt',
	);
}


//------------------ section 1 ----------------
mm($mmopt);
switch($_mm['node']){
	case 'section1': goto section1;
	case 'section2': goto section2;
	case 'section3': goto section3;
}
section1:
?><p>this is paragraph 1</p><?php




//------------------ section 2 ----------------
mm($mmopt);
switch($_mm['node']){
	case 'section1': goto section1;
	case 'section2': goto section2;
	case 'section3': goto section3;
	case 'compend': goto compend;
}
section2:
?><p>this is paragraph 2</p><?php



//------------------ section 3 ----------------
mm($mmopt);
switch($_mm['node']){
	case 'section1': goto section1;
	case 'section2': goto section2;
	case 'section3': goto section3;
	case 'compend': goto compend;
}
section3:
?><p>this is paragraph 3</p><?php


mm($mmopt);
switch($_mm['node']){
	case 'section1': goto section1;
	case 'section2': goto section2;
	case 'section3': goto section3;
	case 'compend': goto compend;
}
compend:
?>