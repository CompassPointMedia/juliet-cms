<?php
/*><script>*/
session_start();
$localSys[scriptID]='mail_profile';
$localSys[scriptVersion]='1.0';



//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
//includes
require('../../admin/general_00_includes.php');
require('mail_00_includes.php');
$qx['defCnxMethod']=C_DEFAULT;


//connection changes, globals must be on
require('../../systeam/php/auth_v200.php');
!strlen($Profiles_ID)?$Profiles_ID=0:'';

if($x=$_SESSION['mail'][$acct]['templates'][$Profiles_ID][EmailColumns]){
	echo $x;
	exit;
	$a=explode(',',$x);
	if(is_array($a)){
		foreach($a as $v){
			$v=trim(preg_replace('/Col(umn)*/i','',$v));
			if($v)$sessionCols[$v]=1;
		}
	}
}


?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Select Email Columns</title>
<link rel="stylesheet" href="/Library/css/common/i1.css" type="text/css"/>
<link rel="stylesheet" href="/Library/css/tables/i1.css" type="text/css"/>
<link rel="stylesheet" href="/Library/css/properties/properties_i1_v100.css" type="text/css"/>
<link rel="stylesheet" href="/Library/css/layers/layer_engine_v100.css" type="text/css"/>
<script src="/Library/js/global/global_i1_v100.js"></script>
<script src="/Library/js/common/common_i1_v100.js"></script>
<script src="/Library/js/p/properties_events_v100.js"></script>
<script src="/Library/js/p/properties_functions_v100.js"></script><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<?php /*><script>*/
?>
</head>
<body style="background-image:none;background-color:menu;padding:5 3 5 3;">
<form action="mail_profile_01_exe.php?mode=selectEmailCols" target="w3test" method="POST">
  <?php
$fp=fopen($VOS_ROOT.'/'.$_SESSION['currentConnection'].'/tmp_mailprofile'.$Profiles_ID.'.txt','r') or die('Could not open tmp_mailprofile'.$Profiles_ID.'.txt for reading');
$numRows=10;
$maxchars=20;
$separator=',';
?>
  <h3>Select the column or columns which contain email addreses
      <br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<input name="Submit" type="submit" id="ctrlOK" disabled="true" value="Use Selected Column">
&nbsp;&nbsp;<input name="Submit2" type="submit" onClick="window.close();" value="Cancel">
    <br />
	</h3>
	<table class="data1" style="background-color:beige;" border="0" cellspacing="0" cellpadding="0">
		<?php
for($i=1;$i<=$numRows;$i++){
	$data=fgetcsv($fp,1500,$separator);
	if($i==1){
		//declare the colgroup
		?>
			<colgroup><?php
		for($j=1;$j<=count($data);$j++){
			echo "<col id=col$j>";
		}
		?></colgroup>
			<?php
		?><thead style="background-color:darkseagreen;"><tr><?php
		for($j=1;$j<=count($data);$j++){
			?>
				<th nowrap><input name="emailCol[]" type="checkbox" id="emailCol[]" onClick="if(this.checked==true){d.ctrlOK.disabled=false; eval( 'col'+this.value+'.style.backgroundColor=\'lightsteelblue\';' );}else{eval( 'col'+this.value+'.style.backgroundColor=\'beige\';' );}" value="<?php echo $j?>" <?php echo $sessionCols[$j]?checked:''?>>
				Column-<?php echo $j?></th>
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
?>
	</table>
(Only the first 
  <?php echo $numRows?>
  of these records are shown for sampling) 
  <input type="hidden" name="Profiles_ID" value="<?php echo $Profiles_ID?>">
</form>
<div class="controlSection" id="ctrlSection" style="display:none;"><iframe name="w3"></iframe></div>
</body>
</html>
