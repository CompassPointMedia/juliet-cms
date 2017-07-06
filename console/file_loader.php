<?php 
//identify this script/GUI
$localSys['scriptID']='gen_access1';
$localSys['scriptVersion']='1.0';
$localSys['pageType']='Properties Window';


//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/properties_04_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $PageTitle='File loader';?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" href="/console/rbrfm_admin.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<style type="text/css">
</style>

<script language="JavaScript" type="text/javascript" src="/Library/js/jquery.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/global_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/common_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/forms_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/loader_04_i1.js"></script>
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
var isEscapable=1;
var isDeletable=1;
var isModal=1;
var talks=1; //whether this page broadcasts overall state changes which other pages listen for
var listens=0;

AddOnkeypressCommand("PropKeyPress(e)");

function uploadFile(n){
	if(!n)n=g('uploadFile_1').value+'';
	g('LocalFileName').value=n;
	g('form1').submit();
	g('status').style.display='block';
}

var saymode='';
function say(text){
	if(saymode!=='test')return;
	if(typeof this.i=='undefined')this.i=0;
	this.i++;
	$('#console').prepend('<div>'+new Date()+' ['+i+'] '+text+'</div>');
}
</script>

<!-- InstanceEndEditable -->
</head>

<body id="properties">
<form id="form1" name="form1" target="w2" method="post" action="/console/resources/bais_01_exe.php" onsubmit="return beginSubmit();" enctype="multipart/form-data">
<div id="header"><!-- InstanceBeginEditable name="top_nav" -->
<div class="printhide fr">
  <input type="button" name="Button" value="Close" onclick="window.close();" />
</div>
<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->




<h2>Upload a File</h2>
<p class="balloon1">Remember that depending on the size of your file, uploading can take anywhere from a few seconds to OVER AN HOUR for files of 100MB.  Leave this window open while the file is uploading; your file will show in the  file list as soon as it has been uploaded.</p>
<br />
	<input name="mode" type="hidden" id="mode" value="<?php echo $mode?$mode:'uploadFile';?>" />
	<input name="submode" type="hidden" id="submode" value="<?php echo $submode?>" />
	<?php if($subsubmode){ ?>
	<input name="subsubmode" type="hidden" id="subsubmode" value="<?php echo $subsubmode?>" />
	<?php } if($mode4){ ?>
	<input name="mode4" type="hidden" id="mode4" value="<?php echo $mode4?>" />
	<?php } ?>
	<?php
	if(count($_REQUEST)){
		foreach($_REQUEST as $n=>$v){
			if(substr($n,0,2)!=='cb' && substr($n,0,4)!=='_cb_')continue;
			if(!$setCBPresent && substr($n,0,2)=='cb'){
				$setCBPresent=true;
				?><!-- callback fields automatically generated --><?php
				echo "\n";
				?><input name="cbPresent" id="cbPresent" value="1" type="hidden" /><?php
				echo "\n";
			}
			if(is_array($v)){
				foreach($v as $o=>$w){
					echo "\t\t";
					?><input name="<?php echo str_replace('_cb_','',$n);?>[<?php echo is_numeric($o)? '': $o?>]" id="<?php echo $n?>[<?php echo is_numeric($o)? '': $o?>]" type="hidden" value="<?php echo stripslashes($w)?>" /><?php
					echo "\n";
				}
			}else{
				echo "\t\t";
				?><input name="<?php echo str_replace('_cb_','',$n);?>" id="<?php echo str_replace('_cb_','',$n);?>" type="hidden" value="<?php echo stripslashes($v)?>" /><?php
				echo "\n";
			}
		}
	}
	?>
	<?php
	if($a=q("SELECT VarKey FROM `bais_settings` WHERE username='{system}' AND VarNode='$CategoryGroup'", O_COL)){
		?>
		Category: 
		<select name="Category" id="Category">
		<option value="">&lt;Select..&gt;</option>
		<?php
		foreach($a as $v){
			?><option value="<?php echo $v?>" <?php echo preg_replace('/^[0-9]+ - /','',$Category)==preg_replace('/^[0-9]+ - /','',$v)?'selected':''?>><?php echo h(preg_replace('/^[0-9]+ - /','',$v));?></option><?php
		}
		?></select>
		<br />
		<?php
	}
	?>
	<h2>Choose a file from your computer:</h2>
	
	<em class="gray">(Upload begins as soon as you select a file)</em><br />
	<span id="uploadFileWrap"><input name="uploadFile_1" type="file" id="uploadFile_1" onchange="uploadFile(this.value);" /></span>
	
	&nbsp;&nbsp;
	<input name="LocalFileName" type="hidden"  id="LocalFileName" value="" />
	<div id="status" style="display:none;">
		Uploading..
		<img src="/images/i/ani-gif-bars-ltgreen.gif" alt="file upload in progress" width="220" height="19" /><br />
		<input type="button" name="Button" value="Cancel" onclick="if(confirm('This will cancel this upload.  Continue?'))window.close();" />	
	</div>


<!-- InstanceEndEditable --></div><div id="footer"><!-- InstanceBeginEditable name="footer" -->
<div id="console">

</div>
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
?>