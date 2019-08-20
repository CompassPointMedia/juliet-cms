<?php 
/*
2013-01-17
	* what I am sure of is the object we are working on in actually gen_batches - each time we export we are creating a batch and probably sub-batches
	* 06:12AM - 
	so I've done these property windows umpteen times.  True, this is a batch object but how many people want to know that.  Certainly the nav buttons shouldn't be as prominent, and I'd like to see a batch history more than actually navigate
	I think the answer = tabs, Export, History - basically a list view and focus view on one page
*/
$f=str_replace('.php','.assets.php',__FILE__);
if(file_exists($f))require($f);
$f=($REDIRECT_URL ? $REDIRECT_URL : $SCRIPT_FILENAME);
if(end(explode('/',$f))==end(explode('/',__FILE__))){
	//identify this script/GUI
	$localSys['scriptGroup']='';
	$localSys['scriptID']='generic';
	$localSys['scriptVersion']='1.0';
	$localSys['pageType']='Properties Window';
	
	
//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');
}

switch(true){
	/*
	case $method=='transactions':
		$object='finan_headers';
	break;
	case $method=='customers':
		$object='finan_clients';
	break;
	*/
	default:
		$object='gen_batches';
}
//---------- begin edgar -------------
$objectFields=q("EXPLAIN $object", O_ARRAY);
foreach($objectFields as $n=>$v){
	if($v['Key']=='PRI')$recordPKField[]=$v['Field'];
	//first non-numeric field = default sorter
	if(!$sorter && preg_match('/(char|varchar)/',$v['Type']) && !preg_match('/createdate|creator|editdate|editor/i',$v['Field']))$sorter=$v['Field'];
	if(preg_match('/resourcetype/i',$v['Field']) && $v['Null']=='YES')$quasiResourceTypeField=$v['Field'];
	if(preg_match('/resourcetoken/i',$v['Field']))$quasiResourceTokenField=$v['Field'];
	if(preg_match('/sessionkey/i',$v['Field']))$sessionKeyField=$v['Field'];

	if(preg_match('/creator/i',$v['Field']))$creatorField=$v['Field'];
	if(preg_match('/createdate/i',$v['Field']))$createDateField=$v['Field'];
}
//------------ end edgar -------------


if($mode=='insertBatchExportObjects' || $mode=='updateBatchExportObjects'){

	if($mode==$insertMode){

	}else{
	
	}

	$navigate=true;
	$navigateCount=$count+($mode==$insertMode?1:0);
	goto bypass;
}

//------------------------ Navbuttons head coding v1.50 -----------------------------
//object=gen_batches
if(!$sorter)$sorter='ID';
$navObject=$object.'_ID';
$updateMode='updateBatchExportObjects';
$insertMode='insertBatchExportObjects';
$deleteMode='deleteBatchExportObjects';
$insertType=1; //1=Save&New and Save&Close; 2 = Save and Save&New
#set these to 'disabled' if desired
$saveInitiallyDisabled='';
$saveAndNewInitiallyDisabled='';
$saveAndCloseInitiallyDisabled='';
//v1.4 change - some information about the coding
$navVer='1.50';
//v1.3 change - declare this function if you need to add parameters to the query string
$navQueryFunction='rfm_quickbooksexport_nav()';
//v1.3 change - deny transiting from Next to New mode (shutting off ability to insert)
$denyNextToNew=false;
//declare the query to get the idSet or subset, ordered by desired sort order - note that if you're using quasi resources, then be sure and filter them out.
$ids=q("SELECT ID FROM $object WHERE Type='export' AND SubType='QuickBooks' AND Batches_ID IS NULL ORDER BY $sorter",O_COL);

$nullCount=count($ids);
$j=0;
if($nullCount){
	foreach($ids as $v){
		$j++; //starting value=1
		if($j==$abs+$nav || (isset($$navObject) && $$navObject==$v)){
			$nullAbs=$j;
			//get actual primary key if passage by abs+nav
			if(!$$navObject) $$navObject=$v;
			break;
		}
	}
}else{
	$nullAbs=1;
}
if(strlen($$navObject)){
	if($a=q("SELECT * FROM $object WHERE Type='export' AND SubType='QuickBooks' AND Batches_ID IS NULL AND ID='".$$navObject."'",O_ROW)){
		$mode=$updateMode;
		@extract($a);
		//now get exports in this batch
		$exports=q("SELECT * FROM $objects WHERE Batches_ID='".$$navObject."'", O_ARRAY);
	}
}else{
	$mode=$insertMode;
	$nullAbs=$nullCount+1; //where we actually are right then
}
//--------------------------- end coding --------------------------------
gmicrotime('afterhead');

$PageTitle='Quickbooks Batch Manager';
$suppressForm=false;

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/properties_04_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo dynamic_title($PageTitle.' - '.$AcctCompanyName);?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" href="/console/rbrfm_admin.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<style type="text/css">
</style>

<script language="JavaScript" type="text/javascript" src="/Library/js/jquery.js"></script>
<script language="javascript" type="text/javascript" src="/Library/js/jquery.tabby.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/global_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/common_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/forms_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/loader_04_i1.js"></script>
<script language="JavaScript" type="text/javascript">
/* periwinkle coding 2.1 */
var thispage='<?php echo $thispage?>';
var thisfolder='<?php echo $thisfolder?>';
var ctime='<?php echo $ctime?>';
var PHPSESSID='<?php echo $PHPSESSID?>';
//for nav feature
var count='<?php echo $nullCount?>';
var ab='<?php echo $nullAbs?>';
var isEscapable=1;
AddOnkeypressCommand('PropKeyPress(e)'); //if not declared already

</script>
<!-- InstanceEndEditable -->
</head>

<body id="properties">
<form id="form1" name="form1" target="w2" method="post" action="/console/resources/bais_01_exe.php" onSubmit="return beginSubmit();" enctype="multipart/form-data">
<div id="header"><!-- InstanceBeginEditable name="top_nav" -->


<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->

<?php
require('components/comp_910_quickbooks_exportmanager_v100.php');
?>

<!-- InstanceEndEditable --></div>
<div id="footer"><!-- InstanceBeginEditable name="footer" -->
&nbsp;
<!-- InstanceEndEditable --></div>
</form>
<?php if(!$hideCtrlSection){ ?>
<div id="showTester" title="Javascript Tester" onClick="g('tester').style.display='block';">&nbsp;</div>
<div id="tester" >
	<a href="#" onClick="g('ctrlSection').style.display='block';return false;">Show Control Section</a><br />
	<textarea name="test" cols="65" rows="4" id="test">clear_form();</textarea><br />
	<input type="button" name="button" value="Test" onClick="jsEval(g('test').value);"><br />
	<textarea id="result" name="result" cols="65" rows="3" ></textarea>
</div>
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
//skip the page output
bypass:
?>