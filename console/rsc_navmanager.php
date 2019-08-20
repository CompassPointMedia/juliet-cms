<?php 
/*
*/
//identify this script/GUI
$localSys['scriptID']='generic';
$localSys['scriptVersion']='1.0';
$localSys['componentID']='main';


//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');

if($HierarchyNodes_ID)$Nodes_ID=q("SELECT Nodes_ID FROM gen_nodes_hierarchy WHERE ID=$HierarchyNodes_ID", O_VALUE);
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="../Templates/reports_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Menu Manager</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" href="/Library/css/cssreset01.css" type="text/css" />
<link rel="stylesheet" href="rbrfm_admin.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<style type="text/css">
/* local CSS styles */
</style>
<script language="JavaScript" type="text/javascript" src="/Library/js/jquery.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/global_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/common_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/forms_04_i1.js"></script>
<script language="JavaScript" type="text/javascript">
/* periwinkle coding */
var thispage='<?php echo $thispage?>';
var thisfolder='<?php echo $thisfolder?>';
var ctime='<?php echo $ctime?>';
var PHPSESSID='<?php echo $PHPSESSID?>';
//for nav feature
var count='<?php echo $nullCount?>';
var ab='<?php echo $nullAbs?>';

$('select').live('click',function(){
	var n=this.name.replace('pageNav[','').replace(']','');
	g('primary'+n).checked=true;
	$('#rename').load('resources/bais_01_exe.php',{'mode':'displayNavRename', 'Nodes_ID':this.value,'suppressPrintEnv':true})
});
</script>


<!-- following coding modified from ajaxloader.info - a long way to go to be modular -->
<style type="text/css">
</style>
<script language="javascript" type="text/javascript">
</script>

<!-- InstanceEndEditable -->
</head>

<body id="report">
<?php if(!$suppressForm){ ?>
<form action="../console/resources/bais_01_exe.php" method="post" enctype="multipart/form-data" name="form1" target="w2" id="form1" onsubmit="return beginSubmit();">
<?php }?>
<div id="header"><!-- InstanceBeginEditable name="top_nav" -->



<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->


<h1>Navigation Manager</h1>
<style type="text/css">
#rename{
	background-color:cornsilk;
	}
.spacer td{
	border-bottom:1px solid #ccc;
	padding-top:2px;
	padding-bottom:2px;
	}
.spacer .inactive td{
	background-color:#ddd;
	color:#222;
	vertical-align:top;
	}
</style>
<table width="100%" border="0" cellspacing="0" class="spacer">
  <thead>
    <tr>
      <th scope="col">Primary</th>
      <th scope="col">Menu Name</th>
      <th scope="col">Structure</th>
      </tr>
  </thead>
  <tbody>
    <?php
	if($navs=q("SELECT * FROM gen_nodes WHERE Type='Group' AND Category='Navigation Menu'", O_ARRAY)){
		ob_start();
		foreach($navs as $v){
			$rand[$v['ID']]=md5(rand(1,1000000));
			?><tr>
			  <td><input name="primary" type="radio" value="<?php echo $v['ID']?>" <?php echo ($Nodes_ID ? $rand[$v['ID']] : ($v['Active'] ? 'checked' : ''));?> onclick="g('Update').disabled=false; if(this.checked){g('Name').value='';}" id="primary<?php echo $v['ID'];?>" /></td>
			  <td><?php echo $v['Name']?></td>
		  <td>
			  <select name="pageNav[<?php echo $v['ID']?>]" size="7" id="pageNav[<?php echo $v['ID']?>]" style="width:300px;" onchange="dChge(this);">
				<?php
				/* query is fairly complex here */
				$i=0;
				if($nav=q("SELECT Nodes_ID, NameT1, NameT2, NameT3, NameT4 FROM _v_gen_nodes_hierarchy_nav WHERE GroupNodes_ID=".$v['ID']." ORDER BY ".($Nodes_ID ? "IF(Nodes_ID=$Nodes_ID,1,2)":'1'), O_ARRAY_ASSOC)){
					foreach($nav as $o=>$w){ 
						?><option value="<?php echo $o?>" <?php if($Nodes_ID==$o) echo $inMenu[$v['ID']]='selected';?>><?php echo h(
						$w['NameT1'] . 
						($w['NameT1'] ? ' > ':'') . $w['NameT2'] . 
						($w['NameT2'] ? ' > ':'') . $w['NameT3'] . 
						($w['NameT3'] ? ' > ':'') . $w['NameT4']
						);?></option><?php
					}
				}else{
					?><option value="">(no nodes created yet)</option><?php
				}
				?>
			  </select>
			  </td>
			  </tr><?php
		}
		$out=ob_get_contents();
		ob_end_clean();
		foreach($rand as $n=>$v){
			$out=str_replace($v,($inMenu[$n] ? 'checked' : ''),$out);
		}
		echo $out;
	}else{
		?>
		<tr>
			<td colspan="100%" class="ghost">No nav menus listed yet.  Click new nav menu below to create your first menu</td>
		</tr>
		<?php
	}
	?>
	<tr>
		<td id="rename" style="border-bottom:none;" colspan="100%">
		<?php
		if($Nodes_ID){
			@extract(q("SELECT Name, Description FROM gen_nodes WHERE ID='$Nodes_ID'",O_ROW));
			?>
			<label>Rename menu item: 
			<input onchange="dChge(this);" onfocus="g('Update').disabled=false; " name="newName" type="text" value="<?php echo $Name?>" /></label>
			<br />
			<label>
			Description : 
			<input name="Description" id="Description" type="text" onfocus="g('Update').disabled=false;" onchange="dChge(this);" value="<?php echo $Description?>" size="35" maxlength="255" />
			</label>
			<input name="Nodes_ID" id="Nodes_ID" type="hidden" value="<?php echo $Nodes_ID?>" />
		<?php
		}
		?>
		</td>
	</tr>
	<tr>
	<td colspan="100%">
	<label>
	<input name="primary" id="primary0" type="radio" value="-1" onclick="g('Update').disabled=false; if(this.checked)g('Name').focus();" /> 
	New menu</label>: 
	<input name="Name" type="text" id="Name" size="35" maxlength="75" tabindex="-1" onfocus="g('Update').disabled=false;" />
	<br />
	<br />
	<input type="submit" name="Submit" id="Update" value="Update" disabled="disabled" />
	<input name="Close" type="button" id="Close" value="Close" onclick="window.close();" />
	<input name="mode" type="hidden" id="mode" value="updateMenu" /></td>
	</tr>
  </tbody>
</table>

<!-- InstanceEndEditable --></div>
<div id="footer"><!-- InstanceBeginEditable name="footer" -->
&nbsp;
<!-- InstanceEndEditable --></div>
<?php if(!$suppressForm){ ?>
</form>
<?php }?>
<?php if(!$hideCtrlSection){ ?>
<div id="showTester" title="Javascript Tester" onclick="g('tester').style.display='block';">&nbsp;</div>
<div id="tester" >
	<a href="#" onclick="g('ctrlSection').style.display='block';return false;">Show Control Section</a><br />
	<textarea name="test" cols="65" rows="4" id="test">clear_form();</textarea><br />
	<input type="button" name="button" value="Test" onclick="jsEval(g('test').value);"><br />
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