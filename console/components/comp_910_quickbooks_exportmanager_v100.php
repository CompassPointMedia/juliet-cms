<?php
if(!$refreshComponentOnly){
	?><style type="text/css">
	
	</style>
	<script language="javascript" type="text/javascript">
	
	</script><?php
}
ob_start(); //--------- begin tabs ---------
?>
<input name="navVer" type="hidden" id="navVer" value="<?php echo $navVer?>" />
<input name="object" type="hidden" id="object" value="<?php echo $object?>" />
<input name="navObject" type="hidden" id="navObject" value="<?php echo $navObject?>" />
<input name="nav" type="hidden" id="nav" />
<input name="navMode" type="hidden" id="navMode" value="" />
<input name="count" type="hidden" id="count" value="<?php echo $nullCount?>" />
<input name="abs" type="hidden" id="abs" value="<?php echo $nullAbs?>" />
<input name="insertMode" type="hidden" id="insertMode" value="<?php echo $insertMode?>" />
<input name="updateMode" type="hidden" id="updateMode" value="<?php echo $updateMode?>" />
<input name="deleteMode" type="hidden" id="deleteMode" value="<?php echo $deleteMode?>" />
<input name="mode" type="hidden" id="mode" value="<?php echo $mode?>" />
<input name="submode" type="hidden" id="submode" value="" />
<input name="componentID" type="hidden" id="componentID" value="<?php echo $localSys['componentID']?>" />
<input name="ID" type="hidden" id="ID" value="<?php echo $ID;?>" />
<input name="recordPKField" type="hidden" id="recordPKField" value="<?php echo $recordPKField[0]?>" />
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
					?><input name="<?php echo $n?>[<?php echo is_numeric($o)? '': $o?>]" id="<?php echo $n?>[<?php echo is_numeric($o)? '': $o?>]" type="hidden" value="<?php echo stripslashes($w)?>" /><?php
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
<?php
$exportObjects=array(
	'invoice','cash sale',
);

?>
<div id="exportSettings">
Export: 
  <select name="subtype" id="subtype" onchange="dChge(this);">
		<option value="transactions">Transactions</option>
  </select>
	<div id="exportObjects">
	  <input name="exportObjects[]" type="checkbox" id="exportObjects[]" value="invoice" <?php echo in_array('invoice',$exportObjects)?'checked':''?> />
	Invoices&nbsp;&nbsp;
	<input name="exportObjects[]" type="checkbox" id="exportObjects[]" value="cash sale" <?php echo in_array('invoice',$exportObjects)?'checked':''?> />
	Cash Sales </div>
	<br />
	<br />
	<input type="checkbox" name="checkbox" value="checkbox" /> 
	Export all dependent items
	<br />
	<?php
	if($a=q("SELECT * FROM _v_finan_invoices_cash_sales WHERE ToBeExported=1 AND Type IN('".implode('\',\'',$exportObjects)."')", O_ARRAY)){
		?><table class="yat"><thead>
		<tr>
			<th>&nbsp;</th>
			<th>&nbsp;</th>
			<th>Date</th>
			<th>Nbr.</th>
			<th>Customer</th>
			<th>Amount</th>
		</tr>
		</thead><tbody>
		<?php
		foreach($a as $v){
			?><tr>
			<td><input type="checkbox" name="selected[<?php echo $v['ID'];?>]" checked /></td>
			<td><?php echo $v['Type'];?></td>
			<td><?php echo date('n/j/Y',strtotime($v['HeaderDate']));?></td>
			<td class="tar"><?php echo $v['HeaderNumber'];?></td>
			<td><?php echo $v['ClientName'];?></td>
			<td class="tar">$<?php echo number_format($v['Extension'],2);?></td>
			</tr><?php
		}
		?></tbody></table><?php
	}
	
	?><br />

  <input type="submit" name="Submit" value="Export" />
</div>
<?php
get_contents_tabsection('export'); //---------------------------
?>	
history here
<?php
get_contents_tabsection('history'); //---------------------------
?>
help
<?php
get_contents_tabsection('help'); //---------------------------
tabs_enhanced(
	array(
		'export'=>array(
			'label'=>'Export'
		),
		'history'=>array(
			'label'=>'History'
		),
		'help'=>array(
			'label'=>'Help',
		),
	)
);
?>
