<?php
$localSys['scriptGroup']='';
$localSys['scriptID']='members-deactivate';
$localSys['scriptVersion']='4.0';


//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');

$qx['defCnxMethod']=C_MASTER;
$qx['useRemediation']=true;

q("UPDATE finan_clients SET Active =  '0'");

error_alert("Done");