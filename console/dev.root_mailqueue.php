<?php 
$localSys['scriptID']='generic';
$localSys['scriptVersion']='1.0';
$localSys['componentID']='main';
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php'); 
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');
$qx['defCnxMethod']=C_MASTER;

$hideCtrlSection=false;
$tabVersion=3;

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/rbrfm_01.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" --><title>mailqueue</title><!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" type="text/css" href="rbrfm_admin.css" />
<link rel="stylesheet" type="text/css" href="/Library/css/DHTML/dynamic_04_i1.css" />
<link rel="stylesheet" type="text/css" href="/Library/css/DHTML/data_04_i1.css" />

<style>
/** CSS Declarations for this page **/
</style>

<script src="/Library/js/jquery.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/jquery.tabby.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/global_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/common_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/forms_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/loader_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/contextmenus_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/dataobjects_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/console/console.js" language="javascript" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
/* periwinkle coding 2.1 */
var thispage='<?php echo $thispage?>';
var thisfolder='<?php echo $thisfolder?>';
var browser='<?php echo $browser?>';
var ctime='<?php echo $ctime?>';
var PHPSESSID='<?php echo $PHPSESSID?>';
//for nav feature
var count='<?php echo $nullCount?>';
var ab='<?php echo $nullAbs?>';
var isEscapable=0;
var isDeletable=0;
var isModal=0;
var talks=0; //whether this page broadcasts overall state changes which other pages listen for
var listens=0;
</script>

<!-- InstanceEndEditable -->
</head>

<body>
<div id="mainContainer">
	<!-- InstanceBeginEditable name="admin_top" --><!-- #BeginLibraryItem "/Library/rbrfm_adminmenu_basic_01.lbi" --><?php
require($_SERVER['DOCUMENT_ROOT'].'/console/rbrfm_adminmenu_basic_02.php');
?><!-- #EndLibraryItem --><!-- InstanceEndEditable -->
	<!-- InstanceBeginEditable name="top_region" --><!-- InstanceEndEditable -->
	<div id="leftInset">
	<!-- InstanceBeginEditable name="left_inset" --><!-- InstanceEndEditable -->
	</div>
	<div id="mainBody">
	<!-- InstanceBeginEditable name="main_body" -->


<?php
for($i=1;$i<=5;$i++){
	echo enhanced_mail(array(

 	'to'=> 'sam-git@samuelfullman.com',
	'subject'=> 'tally ho',
	'body'=> 'test '.rand(1,1000000),
	'from'=> 'test@'.str_replace('www.','',$HTTP_HOST),
	'mode'=> 'plain/plaintext',
	'output'=>'queue',
	/*	
	-- new options --
	'emTest' => [1 - treat as a test and then reset the value to 0 | 2 - treat as a test and do NOT reset the value]
	'emTestAction' => [returnParams - return the params passed to the function itself except body | returnParamsAll - same as returnParams but with body included | shunt=someone@email.com]
	'creator'=> [varies by application used in]
	'cnx'=> [optional: e.g. array(host,username,password,database), if !specified defCnxMethod will be used]
	'logmail'=> [true - not needed if you set $enhanced_mail['logmail']=true globally]
	'mailedBy'=> can be session.admin.username, PHP_AUTH_USER, etc.
	'maillogNotes'=> 
	'templateSource'=>
	'maillogTable'=>[default relatebase_maillog]
	*/
		
	));
}

$fp=opendir('/var/spool/cron');
while(false!==($file=readdir($fp))){
	echo $file.'<br />';
	if($file==$acct)prn(implode('',file('/var/spool/cron/'.$file)));
}
prn($fp,1);
prn(`/home/mailqueue.php`);


exit;
//---------------------- this shows the dbs that have mailqueue ----------------------------
$a=q("SHOW DATABASES", O_ARRAY, C_SUPER);
foreach($a as $n=>$v){
	unset($a[$n]);
	$a[]=$v['Database'];
}
prn($a);
$qx['useRemediation']=false;
foreach($a as $db){
	ob_start();
	$count=q("SELECT COUNT(*) FROM $db.relatebase_mailqueue", O_VALUE, C_SUPER, ERR_ECHO);
	$err=ob_get_contents();
	ob_end_clean();
	if($err){
		prn('no table in '.$db.', '.$qr['err']);
	}else{
		prn('in '.$db.', '.$count.' records');
	}
}

?>
	


	<!-- InstanceEndEditable -->
	<div class="cbsm"> </div>
	</div>
	<div id="footer">
	<!-- InstanceBeginEditable name="footer" --><!-- InstanceEndEditable -->
	</div>
</div>

<div id="showTester" title="Javascript Tester" onClick="g('tester').style.display='block';">&nbsp;</div>
<div id="tester" >
	<a href="#" onClick="g('ctrlSection').style.display=op[g('ctrlSection').style.display]; return false;">iframes</a><br />
	<textarea name="test" cols="65" rows="4" id="test">clear_form();</textarea><br />
	<input type="button" name="button" value="Test" onClick="jsEval(g('test').value);"><br />
	<textarea id="result" name="result" cols="65" rows="3" ></textarea>
</div>
<?php if(!$hideCtrlSection){ ?>
<div id="ctrlSection" style="display:<?php echo $testModeC ? 'block':'none'?>">
	<iframe name="w1" src="/Library/js/blank.htm"></iframe>
	<iframe name="w2" src="/Library/js/blank.htm"></iframe>
	<iframe name="w3" src="/Library/js/blank.htm"></iframe>
	<iframe name="w4" src="/Library/js/blank.htm"></iframe>
</div>
<?php } ?>
</body>
<!-- InstanceEnd --></html><?php
page_end();
?>