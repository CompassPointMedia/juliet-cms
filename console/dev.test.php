<?php
//identify this script/GUI
$localSys['scriptGroup']='';
$localSys['scriptID']='generic';
$localSys['scriptVersion']='1.0';
$localSys['pageType']='Properties Window';

if(!$cssFolder)$cssFolder='/console/';

require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php'); 
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');

$qx['defCnxMethod']=C_MASTER;
$qx['useRemediation']=false;


require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php'); 

enhanced_mail(array(
	'to'=>'sam-git@compasspointmedia.com',
	'from'=>'mailprocessor@science-aviation.org',
	'subject'=>'hello',
	'body'=>'world',
	'fSwitchEmail'=>'mailprocessor@science-aviation.org',
));
?>