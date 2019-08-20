<?php 
//identify this script/GUI
$localSys['scriptID']='generic';
$localSys['scriptVersion']='1.0';
$localSys['componentID']='main';



//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');!strlen($Profiles_ID)?$Profiles_ID=0:'';
if($x=$_SESSION['mail'][$acct]['templates'][$Profiles_ID]['EmailColumns']){
	$a=explode(',',$x);
	if(is_array($a)){
		foreach($a as $v){
			$v=trim(preg_replace('/Col(umn)*/i','',$v));
			if($v)$sessionCols[$v]=1;
		}
	}
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="../../Templates/reports_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>View Email Columns</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" href="/Library/css/cssreset01.css" type="text/css" />
<link rel="stylesheet" href="../rbrfm_admin.css" type="text/css" />
<link id="cssDHTML" rel="stylesheet" href="../../Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<style type="text/css">
/* local CSS styles */
.data1 td{
	border-bottom:1px dotted #ccc;
	padding:2px 4px 1px 4px;
	}
.data1 th{
	text-align:left;
	padding:2px 4px 1px 4px;
	}
</style>

<script language="JavaScript" type="text/javascript" src="../../Library/js/global_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="../../Library/js/common_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="../../Library/js/forms_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="../../Library/js/loader_04_i1.js"></script>
<script src="mail.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
/* periwinkle coding */
var thispage='<?php echo $thispage?>';
var thisfolder='<?php echo $thisfolder?>';
var ctime='<?php echo $ctime?>';
var PHPSESSID='<?php echo $PHPSESSID?>';
//for nav feature
var count='<?php echo $nullCount?>';
var ab='<?php echo $nullAbs?>';

var isEscapable=2;

</script>

<!-- InstanceEndEditable -->
</head>

<body id="report">
<?php if(!$suppressForm){ ?>
<form action="../../console/resources/bais_01_exe.php" method="post" enctype="multipart/form-data" name="form1" target="w2" id="form1" onsubmit="return beginSubmit();">
<?php }?>
<div id="header"><!-- InstanceBeginEditable name="top_nav" -->


<?php
$mode='updateMailProfile';
$submode='selectEmailCols';
?>
<input name="mode" type="hidden" id="mode" value="<?php echo $mode?>" />
<input name="submode" type="hidden" id="submode" value="<?php echo $submode?>" />
<input name="componentID" type="hidden" id="componentID" value="<?php echo $localSys['componentID']?>" />
<input type="hidden" name="Profiles_ID" value="<?php echo $Profiles_ID?>" />
<input name="SessionToken" type="hidden" id="SessionToken" value="<?php echo $SessionToken?>" />
<?php
if(count($_REQUEST)){
	foreach($_REQUEST as $n=>$v){
		if(substr($n,0,2)=='cb'){
			if(!$setCBPresent){
				$setCBPresent=true;
				?><!-- callback fields automatically generated --><?php
				echo "\n";
				?><input name="cbPresent" id="cbPresent" value="1" type="hidden" /><?php
				echo "\n";
			}
			if(is_array($v)){
				foreach($v as $o=>$w){
					echo "\t\t";
					?><input name="<?php echo $n?>[<?php echo is_numeric($o)? '': $o?>]" id="<?php echo $n?>[<?php echo is_numeric($o)? '': $o?>]" type="hidden" value="<?php echo urlencode(stripslashes($w))?>" /><?php
					echo "\n";
				}
			}else{
				echo "\t\t";
				?><input name="<?php echo $n?>" id="<?php echo $n?>" type="hidden" value="<?php echo stripslashes($v)?>" /><?php
				echo "\n";
			}
		}
	}
}
?>


<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->


<?php
if($query){
	$result=q(stripslashes($query));
	$rowsPresent=mysqli_num_rows($result);
}

if($RecipientSource=='import'){
	//get the file resource and some basic information
	switch(true){
		case ($ImportType=='csv'):
			$sep=',';
		break;
		case ($ImportType=='tab'):
			$sep="\t";
		break;
		default:
			$sep=',';
		break;
	};
	$fileString=$_SERVER['DOCUMENT_ROOT']."/tmp/tmp_mailprofile".$Profiles_ID.'.txt';
	$fp=fopen($fileString,'r') or die('Unable to open imported file');
	if($ImportHeaders==1){
		$r=fgetcsv($fp,4000,$sep);
		$headers=$r;
	}
	$rowsPresent = count(file($fileString))-$ImportHeaders;
}
$numRows=10;
$maxchars=20;
?>
<h3>Select the column or columns which contain email addreses</h3>

<input name="Submit" type="submit" id="ctrlOK" <?php echo !count($sessionCols)?'disabled':''?> value="Use Selected Column">
&nbsp;&nbsp;
<input name="Submit" type="button" onClick="window.close();" value="Cancel">
<br />
<table class="data1" style="background-color:beige;" border="0" cellspacing="0" cellpadding="0">
<?php
//there are two methods of getting data, one from query, one from imported file
if($RecipientSource=='import'){
	for($i=1;$i<=$rowsPresent;$i++){
		if($i>$numRows)break;
		$data=fgetcsv($fp,4000,$sep);
		if($i==1){
			//declare the colgroup
			?><colgroup><?php
			for($j=1;$j<=count($data);$j++){
				echo "<col id=col$j ". ($sessionCols[$j]?"style='background-color:lightsteelblue'":'') . ">";
			}
			?></colgroup>
				<?php
			?><thead style="background-color:darkseagreen;"><tr><?php
			for($j=1;$j<=count($data);$j++){
				?>
					<th nowrap><input name="emailCol[]" type="checkbox" id="emailCol[]" onClick="if(this.checked==true){g('ctrlOK').disabled=false; g('col'+this.value).style.backgroundColor='lightsteelblue'; }else{ g('col'+this.value).style.backgroundColor='beige';}" value="<?php echo $j?>" <?php echo $sessionCols[$j]?checked:''?>>
					Column-<?php echo $j?><br />
	<?php echo htmlentities($headers[$j-1])?>
					</th>
					<?php
			}
			?></tr></thead><?php
		}
		?><tr><?php
		for($j=1;$j<=count($data);$j++){
			//show the data
			$idx=$j-1;
			if(strlen($data[$idx])<=$maxchars){
				$title='';
				$s=htmlentities($data[$idx]);
			}else{
				$title=' title="'.$data[$idx].'"';
				$s=htmlentities( substr($data[$idx],0,$maxchars-3) ).' ..';
			}
			?><td nowrap<?php echo $title;?>><?php echo $s?></td><?php
		}
		?></tr><?php
	}
}else{
	!$rowsPresent?$rowsPresent=1:'';
	for($i=1;$i<=$rowsPresent;$i++){
		if($i>$numRows){break;}
		$data=mysqli_fetch_array($result);
		if($i==1){
			foreach($data as $n=>$v){
				$ii++;
				if(!is_numeric($n)){
					$colNames[$ii/2]=$n;
				}
			}
			//declare the colgroup
			?>
			<colgroup><?php
			for($j=1;$j<=count($data)/2;$j++){
				echo "<col id=col$j ". ($sessionCols[$j]?"style='background-color:lightsteelblue'":'') . ">";
			}
			?></colgroup>
			<thead style="background-color:darkseagreen;"><tr><?php
			for($j=1;$j<=count($data)/2;$j++){
				?>
					<th nowrap><input name="emailCol[]" type="checkbox" id="emailCol[]" onClick="if(this.checked==true){g('ctrlOK').disabled=false;  g('col'+this.value).style.backgroundColor='lightsteelblue'; }else{ g('col'+this.value).style.backgroundColor='beige';}" value="<?php echo $j?>" <?php echo $sessionCols[$j]?checked:''?>>
					Column-<?php echo $j?><br />
	<?php echo htmlentities($colNames[$j])?>
					</th>
					<?php
			}
			?></tr></thead><?php
		}
		?><tr><?php
		for($j=1;$j<=count($data)/2;$j++){
			//show the data
			$idx=$j-1;
			if(strlen($data[$idx])<=$maxchars){
				$title='';
				$s=htmlentities($data[$idx]);
			}else{
				$title=' title="'.$data[$idx].'"';
				$s=htmlentities( substr($data[$idx],0,$maxchars-3) ).' ..';
			}
			?><td nowrap<?php echo $title;?>><?php echo $s?></td><?php
		}
		?></tr><?php
	}
}
?>
</table>
<?php
//declare colNames as a js array
if($colNames)
foreach($colNames as $n=>$v){
	?><input type="hidden" name="colNames[<?php echo $n?>]" value="<?php echo $v?>"><?php
}
?>
(Only the first <?php echo $numRows?> of these records are shown for sampling) 
<input type="hidden" name="Profiles_ID" value="<?php echo $Profiles_ID?>">

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