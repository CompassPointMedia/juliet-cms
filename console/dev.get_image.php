<?php

//identify this script/GUI
$localSys['scriptID']='generic';
$localSys['scriptVersion']='1.0';
$localSys['componentID']='main';

require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php'); 
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');

require($FUNCTION_ROOT.'/function_get_image_v230.php');

$images=get_file_assets('../images/products/large');
$a=get_image(
    array('bad1','bad2','clsw001','clsf001'),
    $images,
    array(
        'appendage'=>'_',
        'normalize'=>'/[^-_a-zA-Z0-9]/'
    )
);
prn($a);
prn($get_imagex);
$qx['defCnxMethod']=C_MASTER;

