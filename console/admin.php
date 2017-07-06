<?php
//presumes htaccess access worked - config.php must declare $MASTER_DATABASE

//identify this script/GUI
$localSys['scriptID']='generic';
$localSys['scriptVersion']='1.0';
$localSys['componentID']='main';


//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');
$assumeErrorState=false;

if($logout==1){
	$_SESSION['special'][$MASTER_DATABASE]['adminMode']=0;
}else if(strtolower($HTTP_SERVER_VARS['PHP_AUTH_USER'])==strtolower($MASTER_DATABASE) && strlen($HTTP_SERVER_VARS['PHP_AUTH_PW'])){
	$_SESSION['special'][$MASTER_DATABASE]['adminMode']=1;
}else{
	exit('Improper login - out of synch with available logins');
}
header('Location: '.($src ? $src : '/'));
?>