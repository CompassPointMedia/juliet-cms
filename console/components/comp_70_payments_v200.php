<?php

if(!$invoiceLink)$invoiceLink='systementry.php';

		$getAccount=array(
			'var'=>'UndepositedFundsAccounts_ID',
			'typeName'=>'Other Current Asset',
			'typeCategory'=>'Asset',
			'name'=>'Undeposited Funds',
			'description'=>'Orders paid by credit card, bank account or other method',			
		);
		//-------------- begin dickens ------------
		$var=$getAccount['var'];
		if(!$$var){
			if(!($_Types_ID_=q("SELECT ID FROM finan_accounts_types WHERE Name='".$getAccount['typeName']."' AND Category='".$getAccount['typeCategory']."'", O_VALUE))){
				$_Types_ID_=q("INSERT INTO finan_accounts_types SET Name='".$getAccount['typeName']."', Category='".$getAccount['typeCategory']."', CreateDate=NOW(), Creator='".sun()."'", O_INSERTID);
			}
			if(!($$var=q("SELECT ID FROM finan_accounts WHERE Accounts_ID IS NULL AND Name='".$getAccount['name']."' AND Types_ID=$_Types_ID_", O_VALUE))){
				$$var=q("INSERT INTO finan_accounts SET Name='".$getAccount['name']."', Description='".$getAccount['description']."', Notes='".($getAccount['notes'] ? addslashes($getAccount['notes']) : "Added automatically by ".end(explode('/',__FILE__)))."', Types_ID=$Types_ID, CreateDate=NOW(), Creator='".sun()."'", O_INSERTID);
			}
		}
		//-------------- end dickens ------------

if($Clients_ID){
	$qx['useRemediation']=false;
	ob_start();
	$sql="SELECT i.* FROM _v_x_finan_headers_master i WHERE
	(
	/* ------------ condition 1: outstanding balance --------------- */
	i.OriginalTotal > i.AmountAppliedTo 
	OR
	/* ----------------- condition 2: invoices paid by this payment ------------------ */
	".($mode==$updateMode ? "i.ID IN('".
	implode("','",q("SELECT t2.Headers_ID FROM
	finan_transactions t, finan_TransactionsTransactions tt, finan_transactions t2
	WHERE 
	t.Headers_ID='$Payments_ID' AND 
	t.ID=tt.ParentTransactions_ID AND
	tt.ChildTransactions_ID=t2.ID", O_COL)).
	"')" : '0')."
	) AND 
	i.Clients_ID='$Clients_ID' AND 
	i.HeaderType='Invoice' AND
	i.HeaderStatus='Current'";
	$invoices=q($sql, O_ARRAY, ERR_ECHO);
	$err=ob_get_contents();
	ob_end_clean();
	prn($qr);
	if($err){
		mail($developerEmail, 'Notice in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals('view _v_x_finan_headers_master attempted to be rebuilt'),$fromHdrBugs);
		$rebuild='CREATE OR REPLACE VIEW `_v_x_finan_headers_master` AS select `h`.`ID` AS `ID`,`h`.`HeaderType` AS `HeaderType`,`h`.`HeaderDate` AS `HeaderDate`,`h`.`HeaderNumber` AS `HeaderNumber`,`h`.`HeaderStatus` AS `HeaderStatus`, `h`.`ResourceType` AS `ResourceType`,`h`.`ResourceToken` AS `ResourceToken`,`h`.`SessionKey` AS `SessionKey`,`h`.`Clients_ID` AS `Clients_ID`,`h`.`Contacts_ID` AS `Contacts_ID`,`h`.`Accounts_ID` AS `HeaderAccounts_ID`,`h`.`Classes_ID` AS `HeaderClasses_ID`,`h`.`Notes` AS `Notes`,`h`.`CreateDate` AS `CreateDate`,`h`.`Creator` AS `Creator`,`h`.`EditDate` AS `EditDate`,`h`.`Editor` AS `Editor`,count(distinct `t`.`ID`) AS `LineItemCount`,`t`.`ID` AS `Transactions_ID`,sum(`t`.`Extension`) AS `OriginalTotal`,sum(`t`.`AmountApplied`) AS `AmountApplied`,(sum(`t`.`Extension`) + sum(`t`.`AmountApplied`)) AS `BalanceRemaining`,`t`.`Distributions` AS `Distributions`,`t`.`ChildTransactions_ID` AS `ChildTransactions_ID` from (`finan_headers` `h` join `_v_finan_transactions_distributions` `t`) where ((`h`.`ID` = `t`.`Headers_ID`) and (`h`.`Accounts_ID` <> `t`.`Accounts_ID`)) group by `h`.`ID`';
		q($rebuild);
		$invoices=q($sql, O_ARRAY);
	}
	//OK
}

if(!$refreshComponentOnly){
	?><style type="text/css">
	.hasInvoices{
		background-color:#ffebdc;
		}
	.pymts{
		border-collapse:collapse;
		}
	.pymts th{
		font-family:Georgia, "Times New Roman", Times, serif;
		font-weight:400;
		font-size:109%;
		border-bottom:1px solid black;
		padding:4px 5px 2px 4px;
		}
	.pymts td{
		padding:3px 5px 1px 4px;
		border:1px solid #ccc;
		}
	.pymts .invRow{
		border-bottom:1px solid #ccc;
		}
	.pymts .linkA a{
		}
	.pymts .linkA{
		background-color:cornsilk;
		}
	.pymts input.th1{
		padding-right:3px;
		text-align:right;
		}
	.pymts .total{
		font-size:111%;
		font-family:Georgia, "Times New Roman", Times, serif;
		}
	.focus td{
		background-color:lightgreen;
		}
	</style>



<script type="text/javascript" language="javascript">
$(function(){	$('.pymts .th1').numeric('.'); });
$(document).ready(function(){
	
});
function updateTotal(){
	if(typeof applied=='undefined')applied=document.getElementsByTagName('input');
	var fillTotal=0.00;
	$('input[type=text]').each(function(){
		try{
		if(!this.id.match(/^ApplyTo/))return;
		if(!$(this).val().length)return;
		var n=parseFloat(this.value);
		n=Math.round(n*100)/100;
		fillTotal+=n;
		nout=n+(parseInt(n)==n ? '.00':'');
		if(nout.match(/\.[0-9]$/))nout+='0';
		applied[i].value=nout;
		}catch(e){
			if(e.description)alert(i);
		}
	});
	/*
	return false;
	for(var i in applied){
		try{
		if(!applied[i].id.match(/^ApplyTo/))continue;
		if(!applied[i].value.length)continue;
		alert(applied[i].name+':'+applied[i].value);
		var n=parseFloat(applied[i].value);
		n=Math.round(n*100)/100;
		fillTotal+=n;
		nout=n+(parseInt(n)==n ? '.00':'');
		if(nout.match(/\.[0-9]$/))nout+='0';
		applied[i].value=nout;
		}catch(e){
			if(e.description)alert(i);
		}
	}
	*/
	fillTotal=Math.round(fillTotal*100)/100 + (parseInt(fillTotal)==fillTotal ? '.00':'');
	if(fillTotal.match(/\.[0-9]$/))fillTotal+='0';
	g('Total').value=fillTotal;
}
</script>
	<script language="javascript" type="text/javascript">
	var client='<?php echo $Clients_ID?>';
	function selectClientInvoices(n){
		if(detectChange && !confirm('You have started entering payment information and this will be lost if you switch clients.  Continue?'))return false;
		window.open('resources/bais_01_exe.php?mode=refreshComponent&component=<?php echo 'paymentsGUI:'.end(explode('/',__FILE__)).':'.md5(end(explode('/',__FILE__)).$MASTER_PASSWORD);?>&Clients_ID='+n,'w2');
	}
	$('.nbr').numeric({allow:"."});
	</script><?php
}
?>
<div id="paymentsGUI">
<input name="Accounts_ID" type="hidden" id="Accounts_ID" value="<?php echo $UndepositedFundsAccounts_ID;?>" />
Client: 
<?php
$a=q("SELECT
c.ID AS CID, c.Active, c.ClientName, COUNT(h.ID) AS Invoices
FROM finan_clients c LEFT JOIN _v_x_finan_headers_master h ON c.ID=h.Clients_ID AND h.HeaderType='Invoice' AND h.HeaderStatus='Current' AND h.OriginalTotal>h.AmountAppliedTo
WHERE 1
GROUP BY c.ID
HAVING COUNT(h.ID)>0 OR c.Active>0
ORDER BY IF(COUNT(h.ID)>0,1,2), c.ClientName", O_ARRAY_ASSOC);
?>
<select name="Clients_ID" id="Clients_ID" onfocus="client=this.value;" onchange="selectClientInvoices(this.value);dChge(this);" class="minimal">
<option value="">&lt;Select..&gt;</option>
<?php
//list clients with properties
if($a){
	$buffer=$i=0;
	foreach($a as $n=>$v){
		$i++;
		if($buffer!=($v['Invoices']>0?1:2)){
			$buffer=($v['Invoices']>0?1:2);
			if($i>1)echo '</optgroup>';
			?><optgroup label="<?php echo $buffer==1?'Outstanding Invoices':'No Invoices'?>"><?php
		}
		?><option value="<?php echo $n?>" class="<?php echo $buffer==1?'hasInvoices':''?>" <?php echo $Clients_ID==$n?'selected':''?>><?php echo h($v['ClientName']).($v['Invoices']>0?' ('.$v['Invoices'].')':'');?></option><?php
	}
	?></optgroup><?php
}
?>
</select>
<br>
Check number:
<input name="HeaderNumber" type="text" class="minimal" id="HeaderNumber" value="<?php echo h($HeaderNumber);?>" size="9" onchange="dChge(this);" />
<script language="javascript" type="text/javascript">
try{g('HeaderNumber').focus();}catch(e){}
</script>
payment type: 
<select name="Types_ID" id="Types_ID" onchange="dChge(this)" class="minimal">
	<option value="1" <?php echo $Types_ID==1?'selected':''?>>Check</option>
	<option value="2" <?php echo $Types_ID==2?'selected':''?>>Cash</option>
	<option value="3" <?php echo $Types_ID==3?'selected':''?>>Credit Card</option>
	<option value="4" <?php echo $Types_ID==4?'selected':''?>>Money Order</option>
</select>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br />
Amount: 
<input name="Amount" type="text" class="minimal" id="Amount" value="<?php if(count($Amounts))echo number_format(-array_sum($Amounts),2);?>" size="7" onchange="dChge(this);" />
&nbsp;&nbsp;&nbsp;
Date Received: 
<input name="HeaderDate" type="text" class="minimal" id="HeaderDate" value="<?php if(strlen($HeaderDate)){ echo t($HeaderDate,f_qbks);}else if($mode==$insertMode)echo date('n/j/Y');?>" size="12" onchange="dChge(this);" />
<br>
Memo: 
<input name="Notes" type="text" class="minimal" id="Notes" value="<?php echo h($Notes)?>" size="45" maxlength="255" onchange="dChge(this);" />
<br>
<br>
<table width="100%" border="0" cellspacing="0" class="pymts">
	<thead>
      <tr>
        <th class="tac">Pay</th>
        <th>Invoice #</th>
        <th class="tar">Inv. Amt.</th>
        <th>Date</th>
        <th class="tar">Prior Pymts.</th>
        <th><?php echo $mode==$insertMode? 'New Pymt.':'Amt Applied';?></th>
        <th>Bal. Due</th>
      </tr>
	</thead>
	<?php
	if($invoices){
		?><tfoot>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td class="tar total">Total:&nbsp;</td>
			<td><input name="Total" type="text" class="tar minimal" id="Total" size="6" onchange="dChge(this);" value="<?php if(count($Amounts))echo number_format(-array_sum($Amounts),2);?>" /></td>
			<td>&nbsp;</td>
		</tr>
		<tr><td colspan="100%"><span class="gray">Remaining unapplied amount: </span>
<input name="Unallocated" type="text" class="tar minimal nbr" id="Unallocated" value="<?php echo number_format($Unallocated,2);?>" size="5" maxlength="10" onchange="dChge(this);" /></td></tr>
		</tfoot>
		<tbody id="paymentsLineItems"><?php
		foreach($invoices as $n=>$v){
			?><tr class="invRow<?php echo $FocusInvoices_ID==$v['ID']?' focus':''?>">
				<td class="tac"><input type="checkbox" name="checkbox" value="1" onclick="g('ApplyTo<?php echo $v['ID']?>').value=(this.checked ? g('amt<?php echo $v['ID']?>').innerHTML.replace(',','') : ''); updateTotal(); dChge(this);" tabindex="-1" /></td>
				<td class="linkA"><a href="systementry.php?object=finan_headers&identifier=headers-standard&finan_headers_ID=<?php echo $v['ID'];?>" title="View this invoice information" onclick="return ow(this.href,'l2_leases','700,700');" tabindex="-1"><?php echo $v['HeaderNumber']?></a></td>
				<td class="tar"><?php echo number_format($v['OriginalTotal'],2);?></td>
				<td><?php echo date('n/j/Y',strtotime($v['HeaderDate']));?></td>
				<td class="tar"><?php 
				if($v['AmountAppliedTo']>0){
					echo number_format($v['AmountAppliedTo'],2);
				}else{
					?><em class="gray">(none)</em><?php
				}
				?></td>
				<td><input name="ApplyTo[<?php echo $v['ID']?>]" type="text" class="minimal" style="text-align:right;" id="ApplyTo<?php echo $v['ID']?>" size="6" onchange="dChge(this);updateTotal();" value="<?php if($Amounts[$v['ID']])echo number_format(-$Amounts[$v['ID']],2);?>" /></td>
				<td class="tar">
				<span id="amt<?php echo $v['ID']?>"><?php echo number_format($v['OriginalTotal'] - $v['AmountAppliedTo'],2);?></span>
				</td>
			</tr>
			<?php
		}
		?>
		</tbody><?php
	}else{
		?><tr><td colspan="100%"><em><?php echo $Clients_ID? 'No outstanding invoices listed for this client.':'First select a client from the list above'?></em></td></tr><?php
	}
	?>
</table>
</div>
