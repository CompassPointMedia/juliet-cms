<?php
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<title>Select Email Columns</title>

<link rel="stylesheet" href="/Library/css/common/i1.css" type="text/css"/>
<link rel="stylesheet" href="/Library/css/tables/i1.css" type="text/css"/>
<link rel="stylesheet" href="/Library/css/properties/properties_i1_v100.css" type="text/css"/>
<link rel="stylesheet" href="/Library/css/properties/properties_i1_v100_text.css" type="text/css"/>
<link rel="stylesheet" href="/Library/css/layers/layer_engine_v100.css" type="text/css"/>

<!--
<link id="cssCommon" href="/Library/css2/1/common_i1_v200.css" rel="stylesheet" type="text/css" />
<link id="cssProperties" href="/Library/css2/2/properties_i1_v200.css" rel="stylesheet" type="text/css" />
<link id="cssDataObjects" href="/Library/css2/2/properties_i1_v200.css" rel="stylesheet" type="text/css" />
<link id="cssContextMenus" href="/Library/css2/4/contextmenus_i1_v200.css" rel="stylesheet" type="text/css" />
-->
<script src="/Library/js2/common_i1_v200.js" type="text/javascript"></script>
<script src="/Library/js2/properties_i1_v200.js" type="text/javascript"></script>
<script src="/Library/js2/dataobjects_i1_v200.js" type="text/javascript"></script>
<script src="/Library/js2/contextmenus_i1_v200.js" type="text/javascript"></script>
<script src="mail.js" type="text/javascript"></script>
<script>
var isEscapable=2;
</script>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body style="background-image:none;background-color:menu;padding:5px 3px 5px 3px;">
<form action="mail_profile_01_exe.php?mode=selectEmailCols" target="w3x1" method="post">
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
	$fileString=$VOS_ROOT."/$cc/tmp_mailprofile".$Profiles_ID.'.txt';
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
	<h3>Select the column or columns which contain email addreses
      <br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<input name="Submit" type="submit" id="ctrlOK" <?php echo !count($sessionCols)?'disabled':''?> value="Use Selected Column">
&nbsp;&nbsp;<input name="Submit2" type="submit" onClick="window.close();return false;" value="Cancel">
	<br />
	</h3>
	<table class="data1" style="background-color:beige;" border="0" cellspacing="0" cellpadding="0">
		<?php
//there are two methods of getting data, one from query, one from imported file
if($RecipientSource=='import'){
	for($i=1;$i<=$rowsPresent;$i++){
		if($i>$numRows){break;}
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
				<?php
			?><thead style="background-color:darkseagreen;"><tr><?php
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
if($colNames){
	foreach($colNames as $n=>$v){
		?><input type="hidden" name="colNames[<?php echo $n?>]" value="<?php echo $v?>">
  <?php
	}
}
?>
  (Only the first 
  <?php echo $numRows?>
  of these records are shown for sampling) 
  <input type="hidden" name="Profiles_ID" value="<?php echo $Profiles_ID?>">
</form>
<div class="controlSection" id="ctrlSection" style="display:none;"><iframe name="w3x1"></iframe></div>
</body>
</html>
