<?php

//identify this script/GUI
$localSys['scriptGroup']='';
$localSys['scriptID']='generic';
$localSys['scriptVersion']='1.0';
$localSys['pageType']='Properties Window';

if(!$cssFolder)$cssFolder='/console/';

if($Articles_ID && !$ID)$ID=$Articles_ID;


//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Untitled Document</title>
</head>

<body>
<?php
ob_start();
?>
<p>here is a form:<br />
  first name [input:firstName ] <br />
last name: [input:lastName ]</p>
<p>year: [select:collegeYear options='freshman,sophomore,junior,senior,post-grad' ]</p>
<p>&nbsp;  </p>
<?php
$form=ob_get_contents();
ob_end_clean();

require('../functions/function_form_field_translator_v100.php');

echo form_field_translator($form);

?>
</body>
</html>
