<?php 
if($dir){
	//get the record, we assume it's PRESENT and the Priority is unique
	$r=q("SELECT * FROM re1_properties_imagesort WHERE Handle='$Handle' AND Priority=$idx", O_ARRAY);
	if(count($r)!==1){
		mail($developerEmail,'bad sort present in file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
	}else{
		$r=$r[1];
	}
	//trade places
	q("UPDATE re1_properties_imagesort SET Priority=$idx WHERE Handle='$Handle' AND Priority=$idx + $dir");
	q("UPDATE re1_properties_imagesort SET Priority=$idx + $dir WHERE Handle='$Handle' AND Name='".addslashes($r['Name'])."' AND Priority=$idx");
	header('Location: focus_properties_order.php?Properties_ID='.$Properties_ID);
	exit;
}

//------------------------ Navbuttons head coding v1.41 -----------------------------
//change these first vars and the queries for each instance
$object='Properties_ID';
$recordPKField='ID'; //primary key field
$navObject='Properties_ID';
$updateMode='updateFeaturedProperty';
$insertMode='insertFeaturedProperty';
$insertType=1; //1=Save&New and Save&Close; 2 = Save and Save&New
#set these to 'disabled' if desired
$saveInitiallyDisabled='';
$saveAndNewInitiallyDisabled='';
$saveAndCloseInitiallyDisabled='';
//v1.4 change - some information about the coding
$navVer='1.41';
//v1.3 change - declare this function if you need to add parameters to the query string
$navQueryFunction=''; //'nav_query_add()';
//v1.3 change - deny transiting from Next to New mode (shutting off ability to insert)
$denyNextToNew=true;
//declare the query to get the idSet or subset, ordered by desired sort order - note that if you're using quasi resources, then be sure and filter them out.
$ids=q("SELECT ID FROM re1_properties WHERE 1 ORDER BY Priority",O_COL);
/*
(another good example more complex)
$ids=q("SELECT ID FROM `$cc`.finan_invoices WHERE Accounts_ID='$Accounts_ID' ORDER BY InvoiceDate, CAST(InvoiceNumber AS UNSIGNED)",O_COL);
*/

$nullCount=count($ids);
$j=0;
if($nullCount){
	foreach($ids as $v){
		$j++; //starting value=1
		if($j==$abs+$nav || (isset($$object) && $$object==$v)){
			$nullAbs=$j;
			//get actual primary key if passage by abs+nav
			if(!$$object) $$object=$v;
			break;
		}
	}
}else{
	$nullAbs=1;
}
//note the coding to on ResourceToken - this will allow a submitted page to come up again if the user Refreshes the browser
if(strlen($$object) /* || $Assns_ID=q("SELECT ID FROM sma_assns WHERE ResourceToken!='' AND ResourceToken='$ResourceToken' AND ResourceType IS NOT NULL", O_VALUE)*/){
	//get the record for the object
	if($a=q("SELECT * FROM re1_properties WHERE ID='".$$object."'",O_ROW)){
		$mode=$updateMode;
		@extract($a);
	}else{
		//object may have been deleted by another user, least perplexing approach is to present insert mode
		$mode=$insertMode;
		unset($$object);
		$nullAbs=$nullCount+1;
	}
}else{
	$mode=$insertMode;
	//$Assns_ID=$ID=quasi_resource_generic($MASTER_DATABASE, 'sma_assns', $ResourceToken, $typeField='ResourceType', $sessionKeyField='sessionKey', $resourceTokenField='ResourceToken', $primary='ID', $creatorField='Creator', $createDateField='CreateDate' /*, C_DEFAULT, $options */);

	$nullAbs=$nullCount+1; //where we actually are right then
}
//--------------------------- end coding --------------------------------
?>
<form id="form1" name="form1" target="w2" method="post" action="../resources/bais_01_exe.php" onSubmit="return beginSubmit();">
	<div id="mainBody">
		<h2>Order Pictures for Property <?php echo $Handle?></h2>
		<p>&nbsp;</p>
		<p>&nbsp; </p>
		<?php
		if(!function_exists('get_file_assets')) require($FUNCTION_ROOT.'/function_get_file_assets_v100.php');
		if($physicalFiles=get_file_assets($_SERVER['DOCUMENT_ROOT'].'/images/slides/'.$Handle.'/','large')){
			foreach($physicalFiles as $n=>$v){
				$p=q("SELECT Priority FROM re1_properties_imagesort WHERE Handle='$Handle' AND Name='".addslashes($n)."'", O_VALUE);
				$b[$n]=array($v['name'],($p ? $p : 1));
			}
		}else{
			$b=array();
			q("DELETE FROM re1_properties_imagesort WHERE Handle='$Handle'");
		}
		$maxIdx=q("SELECT MAX(Priority) FROM re1_properties_imagesort WHERE Handle='$Handle'", O_VALUE);
		//add any rows for imgs they added
		$addedRows=false;
		if($physicalFiles){
			foreach($physicalFiles as $v){
				if(!q("SELECT * FROM re1_properties_imagesort WHERE Handle='$Handle' AND Name='".addslashes($v['name'])."'", O_ROW)){
					$maxIdx++;
					$addedRows=true;
					q("REPLACE INTO re1_properties_imagesort SET Handle='$Handle', Name='".addslashes($v['name'])."', Priority=$maxIdx");
				}
			}
		}
		//refresh
		if($addedRows){
			
		}
		//now get keys from db
		if($a=q("SELECT LCASE(Name) AS NameKey, Name as NameValue, Priority FROM re1_properties_imagesort WHERE Handle='$Handle' ORDER BY Priority", O_ARRAY_ASSOC)){
			$i=0;
			foreach($a as $n=>$v){
				if(!$b[$n]){
					//if they have deleted an image since db write
					q("DELETE FROM re1_properties_imagesort WHERE Handle='$Handle' AND Name='".$n."'");
					continue;
				}
			}
		}else{
			//set natural order
			$i=0;
			foreach($b as $n=>$v){
				$i++;
				q("REPLACE INTO re1_properties_imagesort SET Handle='$Handle', Priority=$i, Name='".addslashes($v[0])."'");
				$b[$n][1]=$i;
			}
		}
		if(!function_exists('subkey_sort')) require($FUNCTION_ROOT.'/function_array_subkey_sort_v202.php');
		$b=subkey_sort($b,1);
		//now print table
		?>
		<table>
		  <?php
		$i=0;
		foreach($b as $n=>$v){
			if(!($a=getimagesize($_SERVER['DOCUMENT_ROOT'].'/images/slides/'.$Handle.'/'.$v[0])))continue;
			$i++;
			?><tr>
			<td><?php if($i>1){ ?>
			  <input type="button" name="Button" value="up" onClick="window.location='focus_properties_order.php?Properties_ID=<?php echo $Properties_ID?>&dir=-1&Handle=<?php echo $Handle?>&idx=<?php echo $v[1]?>';" />
			  <?php } ?></td>
				<td><?php if($i<count($b)){ ?>
					<input type="button" name="Button" value="down" onClick="window.location='focus_properties_order.php?Properties_ID=<?php echo $Properties_ID?>&dir=1&Handle=<?php echo $Handle?>&idx=<?php echo $v[1]?>';" />
					<?php }?></td>
				<td><a title="view full-sized image" href="../images/slides/<?php echo $Handle?>/<?php echo $v[0]?>" onclick="return ow(this.href,'l1_img','700,700');"><img src="../images/slides/<?php echo $Handle?>/.thumbs.dbr/<?php echo $v[0]?>" /></td></td>
				<td><?php echo $v[0]?></td>
			  </tr><?php
		}
		?>
		  </table>
	</div>
</form>