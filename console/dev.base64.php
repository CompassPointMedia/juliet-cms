<?php
/*
2013-07-17: pulled over from AMS
*/
//identify this script/GUI
$localSys['scriptID']='generic';
$localSys['scriptVersion']='1.0';
$localSys['componentID']='main';


//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');exit;
$a=q("SELECT distinct AdminSettings FROM relatebase_rfm.rbase_modules where sku='040'", O_COL, C_SUPER);
foreach($a as $v){
	prn(base64_decode($v));
}

?>