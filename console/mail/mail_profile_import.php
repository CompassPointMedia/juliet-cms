<?php
if(strlen($sessionid)) session_id($sessionid);
session_start();
$sessionid ? '' : $sessionid = session_id();
$bufferDocument=true;

//----------------- Begin Properties window coding 1.0 ---------------
#currently no docs on this and should be - look in /admin/development/properties_v100.php
$localSys['scriptGroup']='mailer';
$localSys['scriptID']='MPM';
$localSys['scriptVersion']='2.1.0';
$localSys['modules']='ALL';//only mail module can access this page
$localSys['accessLevel']='User';
$localSys['pageType']='Properties Window';
$localSys['rootLocation']='/client/mail';
$localSys['rootFileName']='index.php';
$localSys['acctSwitchable']='0';
//-------------- End properties window coding 1.0 ---------------------



//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
//includes
require('../../admin/general_00_includes.php');
require('mail_00_includes.php');
$qx['defCnxMethod']=C_DEFAULT;


//connection changes, globals must be on
require('../../systeam/php/auth_v200.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html <?php echo isset($customDocType) ? $customDocTypeFlag : 'xmlns="http://www.w3.org/1999/xhtml"';?>><!-- #BeginTemplate "/Templates/properties_i1_v100.dwt" --><!-- DW6 -->
<head>
<!-- Last Edit: 2004-12-10 -->
<!-- #BeginEditable "doctitle" --> 
<title>Upload an Import File :: RelateBase Services</title>
<link rel="stylesheet" href="/Library/css/layers/layer_engine_v200.css" type="text/css"/>

<?php
$cg[1][CGPrefix]="uploadImportFile";
$cg[1][CGLayers]=Array('uploadImport', 'uploadImportHelp');
$cg[1][defaultLayer]="uploadImport";
$cg[1][layerScheme]=1;
$cg[1][schemeVersion]=2.1;
//this will generate JavaScript, all instructions are found in this file
require("../../Library/css/layers/layer_engine_v220.php");
?>
<link href="/Library/css2/1/common_i1_v200.css" rel="stylesheet" type="text/css" />
<link href="/Library/css2/2/properties_i1_v200.css" rel="stylesheet" type="text/css" />
<link href="/Library/css2/2/properties_i1_v200.css" rel="stylesheet" type="text/css" />
<link href="/Library/css2/4/contextmenus_i1_v200.css" rel="stylesheet" type="text/css" />

<!-- delete 2/28/07 
<link rel="stylesheet" href="/Library/css/common/i1.css" type="text/css"/>
<link rel="stylesheet" href="/Library/css/tables/i1.css" type="text/css"/>
<link rel="stylesheet" href="/Library/css/properties/properties_i1_v100.css" type="text/css"/>
<link rel="stylesheet" href="/Library/css/layers/layer_engine_v100.css" type="text/css"/>
<script src="/Library/js/global/global_i1_v100.js"></script>
<script src="/Library/js/common/common_i1_v100.js"></script>
<script src="/Library/js/p/<?php echo 'properties_events_v100.js'?>"></script>
<script src="/Library/js/p/properties_functions_v100.js"></script>
-->

<script src="/Library/js2/common_i1_v200.js" type="text/javascript"></script>
<script src="/Library/js2/properties_i1_v200.js" type="text/javascript"></script>
<script src="/Library/js2/dataobjects_i1_v200.js" type="text/javascript"></script>
<script src="/Library/js2/contextmenus_i1_v200.js" type="text/javascript"></script>
<script src="mail.js" type="text/javascript"></script>

<script>
isEscapable=2;
</script>
<!-- #EndEditable --> 
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<div id="propertiesHeader"> <img name="admin_region_logo" src="/images/assets/relatebase_logo_short.gif" border="0" width="254" height="43" alt="RelateBase Services"><br />
  &nbsp;&nbsp; 
  <select name="select" id="acctSelect" style="height:11px;" disabled>
    <?php foreach($_SESSION['cnx'] as $n=>$v){?>
    <option <?php if($v['acctName']==$_SESSION['currentConnection']){echo 'selected';}?> value="<?php echo $v[acctName]?>"> 
    <?php echo $v['company'] . ' (' . $v['acctName'] . ')';?>
    <?php }?>
  </select>
  <span id="plusminus" align="center" style="cursor:hand" onClick="g('tester').style.display=op[g('tester').style.display];g('test').select();return false;"><img src="/images/assets/dn_button_tester.gif" alt="Settings and JavaScript Tester"/></span> 
</div>
<!-- Javascript Executer -->
<div id="tester" style="display:none;background-color:khaki;border:1px solid #000000;padding: 5;">
  <style>
.xthinWire{
	border:1px solid #CCCCCC;
	font-size:11px;
	font-family:Verdana, Arial;
}
</style>
  <form action="" method="post">
    Settings and Javascript Tester <br />
    <a id="pageHelp" href="/client/help/help_v200.php?scriptID=<?php echo $localSys[scriptID]?>&scriptVersion=<?php echo $localSys[scriptVersion]?>&src=<?php echo urlencode($_SERVER['PHP_SELF'].'?'.$QUERY_STRING.'&component=')?>" onClick="newWindow(this.href,'l2_helpv200','width=650,height=500,resizable,scrollbars,status,menu');return false;">Page
    Help</a> &nbsp;&nbsp;&nbsp;&nbsp; <a href="#" onClick="sub_bugrpt(keyFormName);return false">Submit 
    Bug Report</a> &nbsp;&nbsp;&nbsp;&nbsp; <a href="#" onClick="g('ctrlSection').style.display=op[g('ctrlSection').style.display];return false">CtrlSection</a> 
    <textarea class="thinWire" name="ftest" cols="100%" rows="3" id="test"></textarea>
    <br />
    <input type="button" name="Submit" value="Test" onClick="return jsEval(g('test').value);">
    <br />
    <textarea class="thinWire" name="result" cols="100%" rows="3" id="result"></textarea>
  </form>
</div>
<div class="propertiesMainBody"><!-- #BeginEditable "main_body" --><div class="objectWrapper">
<div id="winTitle"></div>
<form action="mail_profile_01_exe.php" method="post" enctype="multipart/form-data" name="form1" target="w3" id="form1">
			<table width="375" border="0" cellspacing="0" cellpadding="0">
      <tr> 
		  <!-- this is a patch, won't have a consistent white top w/o this -->
        <td width="50">
          <table class="menu" border="0" cellspacing="0" cellpadding="0">
            <tr valign="bottom"> 
              <td> 
                <div id="uploadImportFile_i_null" style="border-bottom:1px solid white;<?php echo cg('ib','uploadImportFile','null');?>"> 
                  <table border="0" cellspacing="0" cellpadding="0">
                    <tr> 
                      <td class="xbc_">&nbsp;</td>
                    </tr>
                  </table>
                </div>
              </td>
				  <td> 
 <div id="uploadImportFile_a_uploadImport" style="<?php echo cg('ab','uploadImportFile','uploadImport',1);?>"> 
	<table border="0" cellspacing="0" cellpadding="0">
	  <tr> 
		 <td class="bul_"><img src="/images/b/uli.gif" /></td>
		 <td class="bt_"></td>
		 <td class="bur_"><img src="/images/b/uri.gif" /></td>
	  </tr>
	  <tr> 
		 <td class="bl_"></td>
		 <td class="xbc_" nowrap="nowrap">Upload Import File</td>
		 <td class="br_"></td>
	  </tr>
	</table>
 </div>
 <div id="uploadImportFile_i_uploadImport" style="border-bottom:1px solid white;<?php echo cg('ib','uploadImportFile','uploadImport',1);?>" onclick="hl_1('uploadImportFile',uploadImportFile,'uploadImport');"> 
	<table border="0" cellspacing="0" cellpadding="0">
	  <tr> 
		 <td class="bul_"><img src="/images/b/uli.gif" /></td>
		 <td class="bt_"></td>
		 <td class="bur_"><img src="/images/b/uri.gif" /></td>
	  </tr>
	  <tr> 
		 <td class="bl_"></td>
		 <td class="bc_" nowrap="nowrap">Upload Import File</td>
		 <td class="br_"></td>
	  </tr>
	</table>
 </div>
</td><td> 
 <div id="uploadImportFile_a_uploadImportHelp" style="<?php echo cg('ab','uploadImportFile','uploadImportHelp');?>"> 
	<table border="0" cellspacing="0" cellpadding="0">
	  <tr> 
		 <td class="bul_"><img src="/images/b/uli.gif" /></td>
		 <td class="bt_"></td>
		 <td class="bur_"><img src="/images/b/uri.gif" /></td>
	  </tr>
	  <tr> 
		 <td class="bl_"></td>
		 <td class="xbc_" nowrap="nowrap">Help</td>
		 <td class="br_"></td>
	  </tr>
	</table>
 </div>
 <div id="uploadImportFile_i_uploadImportHelp" style="border-bottom:1px solid white;<?php echo cg('ib','uploadImportFile','uploadImportHelp');?>" onclick="hl_1('uploadImportFile',uploadImportFile,'uploadImportHelp');"> 
	<table border="0" cellspacing="0" cellpadding="0">
	  <tr> 
		 <td class="bul_"><img src="/images/b/uli.gif" /></td>
		 <td class="bt_"></td>
		 <td class="bur_"><img src="/images/b/uri.gif" /></td>
	  </tr>
	  <tr> 
		 <td class="bl_"></td>
		 <td class="bc_" nowrap="nowrap">Help</td>
		 <td class="br_"></td>
	  </tr>
	</table>
 </div>
</td>
              <td> 
                <div id="uploadImportFile_i_null2" style="border-bottom:1px solid white;"> 
                  <table border="0" cellspacing="0" cellpadding="0">
                    <tr> 
                      <td class="xbc_">&nbsp;&nbsp;&nbsp;</td>
                    </tr>
                  </table>
                </div>
              </td>
            </tr>
          </table>
        </td>
        <td valign="bottom"> 
          <div style="border-bottom:1px solid white;">&nbsp;</div>
        </td>
      </tr>
    </table>
    <input name="mode" type="hidden" id="mode" value="uploadfile" />
      <input name="Profiles_ID" type="hidden" id="mode" value="<?php echo $Profiles_ID?>" />
      <div id="uploadImportFile_uploadImport" class="aArea" style="height:175;width:375;<?php echo cg('l','uploadImportFile','uploadImport',1)?>"> 
	Upload New Import File<br />
	<br />
   <span id="statusMessage">&nbsp;</span><br />
	<input type="file" name="importfile" onchange="d.currentFile.innerHTML='Selected file: '+this.value;" />
	<br />
	<span id="currentFile" style="font-size:10px"></span>
   <br />
	<br />
   <br />
   <input type="submit" id="ctrlOK" name="nullSub1" value="&nbsp;&nbsp;&nbsp;OK&nbsp;&nbsp;&nbsp;" onclick="d.statusMessage.innerHTML='Currently uploading file..';this.disabled=true;d.form1.submit();" />
   &nbsp;&nbsp;

   <input type="submit" id="nullSub2" name="nullSub2" value="&nbsp;Cancel&nbsp;" onclick="window.close();" />
&nbsp;&nbsp;   </div>

<div id="uploadImportFile_uploadImportHelp" class="aArea" style="height:175;width:375;<?php echo cg('l','uploadImportFile','uploadImportHelp')?>"> 
	Help
</div>
<!-- must be present for cg values to stick on post -->
<input id="uploadImportFile_status" type="hidden" name="nulluploadImportFile_status" value="<?php echo isset($_POST[nulluploadImportFile_status])?$_POST[nulluploadImportFile_status]:'uploadImport';?>" />
<div id="propertiesCtrl" align="right" style="width:375">
</div>
</form></div><!-- #EndEditable --></div>
<?php
if(!$hideCtrlSection){
	?><div class="controlSection" id="ctrlSection" style="display:<?php echo $testmode?'block':'none'?>;">
	<iframe src="/Library/js/blank.htm" name="w0"></iframe>
	<iframe src="/Library/js/blank.htm" name="w1"></iframe>
	<iframe src="/Library/js/blank.htm" name="w2"></iframe>
	<iframe src="/Library/js/blank.htm" name="w3"></iframe>
	</div><?php
}
?></body>
<!-- #EndTemplate --></html>
