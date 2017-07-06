<?php
if(strlen($sessionid)) session_id($sessionid);
session_start();
$sessionid ? '' : $sessionid = session_id();
$bufferDocument=true;


#currently no docs on this and should be - look in /admin/development/properties_v100.php
$localSys['scriptGroup']='mailer';
$localSys['scriptID']='MPM-preview';
$localSys['scriptVersion']='2.1.0';
$localSys['modules']='ALL';//only mail module can access this page
$localSys['accessLevel']='User';
$localSys['pageType']='Properties Window';
$localSys['rootLocation']='/client/mail';
$localSys['rootFileName']='mail_profile_07_preview.php';
$localSys['acctSwitchable']='0';



//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
//includes
require('../../admin/general_00_includes.php');
require('mail_00_includes.php');
$qx['defCnxMethod']=C_DEFAULT;


//connection changes, globals must be on
require('../../systeam/php/auth_v200.php');
$params['Composition']='template';
$params['TemplateMethod']=$_POST['TemplateMethod'];
$params['TemplateLocationURL']=$_POST['TemplateLocationURL'];
foreach($_POST['regions'] as $n=>$v){
	$_POST['regions'][$n]=stripslashes($v);
}
$string=get_email_body($Profiles_ID, $params, $_POST['regions']);

$js='<script>function document.onkeypress(){
	if(event.keyCode==27)window.close();
}
</script>';
$js='';
?><div style="position:absolute;top:0px;left:0px;"><input type="button" name="Submit" value="Close" onclick="window.close();" /></div><?php
print($js.$string);
?>