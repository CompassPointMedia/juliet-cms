<?php 
/*
2012-12-19: a solid and flexible invoice/cash sale printer whcih can be pdf'd or printed; eventually let's house multiple invoices was with GLF


todo
2012-12-19
* soft-code address and etc.
* need a place for legal WRT the finan package or SOMETHING. And a system to display it
* INVOICE or CASH SALE in bold light gray letters, Impact font and
* Payment history
* Terms: when due
* Paid or Past Due stamp if applicable
* adjust the main h1 font
* smaller for the TH's
* page break perfect on multiple invoices (remember some will not print out)
* big page-break divider for media:screen with content:"page break"



window.location='utility.php_component_=systementry_finan_transactions_print.php&authKey=bd419912c3c3b9834abf794a54d8cd8f&ID=2,3,4,5';

*/
$f=end(explode('/',$REDIRECT_URL ? $REDIRECT_URL : $SCRIPT_FILENAME));
if($f=='bais_01_exe.php'){
	$key=md5(rand(1,1000000).time());
	$_SESSION['utility'][$key]=array(
		'_POST'=>stripslashes_deep($_POST),
		'_component_'=>end(explode('/',__FILE__)),
	);
	?><script language="javascript" type="text/javascript">
	window.parent.ow('/console/utility.php?key=<?php echo $key;?>','l1_printTransaction','800,700');
	</script><?php
	eOK();
}



if(false){ ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Invoices</title>
</head>
<body><?php }



//we are in utility and key and _POST have been extracted
$ids=explode(',',$ID);
if(count($ids)==1){
	$PageTitle='Invoice #'.$HeaderNumber;
}
?><style type="text/css"><?php
ob_start(); ?>
#working{
	}

.invoice{
	border:1px solid #ccc;
	padding:5px 15px;
	margin:10px 20px;
	}
.grid{
	border-collapse:collapse;
	border:1px solid #444;
	margin:15px 0px;
	}
.grid thead{
	background-color:#ddd;
	}
.grid td{
	padding:3px 4px 1px 5px;
	}
.grid tbody tr{
	border-bottom:1px dashed #666;
	}
.grid td.mid{
	border-right:1px solid #ccc;
	}
.grid tfoot td{
	border-bottom:none;
	}
<?php $css=ob_get_contents();
ob_end_clean(); ?></style>
<script language="javascript" type="text/javascript"><?php
ob_start();?>
var something=5;
<?php $js=ob_get_contents();
ob_end_clean(); ?></script><?php


ob_start();//-------------- top_nav --------
?><!-- nothing now -->
<?php
$top_nav=ob_get_contents();
ob_end_clean();

ob_start();//-------------- main_body --------
foreach($ids as $ID){
	if(empty($_POST)){
		@extract($a=q("SELECT * FROM _v_finan_invoices_cash_sales WHERE ID=$ID", O_ROW));
		if(empty($a))continue;//deleted invoice or something
		$sub=q("SELECT t.* FROM finan_headers h JOIN finan_transactions t ON h.ID=t.Headers_ID WHERE t.Headers_ID=$ID AND t.Accounts_ID!=h.Accounts_ID", O_ARRAY);
	}else{
		$sub=array_transpose($sub);
		//we don't have all this and need it
		@extract(q("SELECT * FROM finan_invoices WHERE Headers_ID=$ID", O_ROW));
	}
	if(empty($sub)){
		mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err='childless transaction'),$fromHdrBugs);
		echo '<div id="invoice'.$ID.'" class="invoice">error in invoice or transaction: no child records present in transactions_table</div>';
		continue;
	}
	//----------- begin invoice ----------
	?>
<div id="invoice<?php echo $ID;?>" class="invoice">
	<h1><?php
	if($n=$acctData['adminCompany']){
		echo $n;
	}else{
		echo $acctData['adminFirstName'] . ' ' . $acctData['adminLastName'];
	}
	?></h1>
	<div class="fr">
	<table class="inset1">
	<tr>
		<td><?php echo $HeaderType=='Invoice'?'Invoice #':'Receipt #'?></td>
		<td><strong><?php echo $HeaderNumber;?></strong></td>
	</tr>
	<tr>
		<td>Date</td>
		<td><strong><?php echo date('n/j/Y',strtotime($HeaderDate));?></strong></td>
	</tr>
	</table>
	</div>
  <p><?php echo $acctData['adminAddress'];?><br />
	<?php echo $acctData['adminCity']?>, <?php echo $acctData['adminState']. ' ' . $acctData['adminZip'];?><br />
	<?php echo $acctData['adminPhone']?> (p)<?php if($acctData['adminFax']){ ?>&nbsp;&nbsp;<strong style="font-size:larger;">&middot;</strong>&nbsp;&nbsp;<?php echo $acctData['adminFax'];?> (f)<?php } ?><br />
    <?php echo $acctData['adminEmail'];?><br />
    <br />
  </p>
	<div class="fl">
	<table class="inset2 billto">
	<tr>
	<td>Bill to:</td>
	</tr>
	<tr>
	<td>
	<?php 
	extract(q("SELECT CompanyName, PrimaryFirstName, PrimaryLastName, Address1, State, City, Zip FROM finan_clients WHERE ID=$Clients_ID", O_ROW));
	echo $CompanyName;?><br />
	<?php 
	if($PrimaryFirstName)echo 'attn: '. $PrimaryFirstName . ' '.$PrimaryLastName.'<br />';
	echo $Address1.($Address1 || $State ? ', ':'').$State.'&nbsp;&nbsp;'.$Zip.'<br />';
	?>
	</td>
	</tr>
	</table>
	</div>
	<?php 
	if($ShippingAddress!=$BillingAddress || $ShippingState!=$BillingState || $ShippingZip!=$BillingZip){
		?><div class="fl">
		<table class="inset2 shipto">
		<tr>
		<td>Ship to:</td>
		</tr>
		<tr>
		<td>
		<?php echo $ShippingCompany;?><br />
		<?php 
		if($ShippingFirstName)echo 'attn: '. $ShippingFirstName . ' '.$ShippingLastName.'<br />';
		echo $ShippingAddress.($ShippingAddress || $ShippingState ? ', ':'').$ShippingState.'&nbsp;&nbsp;'.$ShippingZip.'<br />';
		?>
		</td>
		</tr>
		</table>
		</div><?php
	}
	?>	
	
	<div class="cb"> </div>
	<table class="grid">
	<thead>
	<tr>
		<th class="tar">Qty</th>
		<th>P/N</th>
		<th>Description</th>
		<th class="tar">Hours</th>
		<th class="tar">Rate</th>
		<th class="tar">Ext</th>
	</tr>
	</thead>
	<?php ob_start(); ?>
	<tbody>
	<?php $i=0; $total=0; $extent=0;
	foreach($sub as $n=>$v){
		$a=$v;
		unset($a['Items_ID']);
		if(trim(implode('',$a)))$extent=$n;
	}
	foreach($sub as $n=>$v){
		//show all rows! unless totally blank
		if($n>$extent)break;
		
		$i++;
		?><tr id="r_<?php echo $v['ID'];?>" class="<?php echo fmod($i,2)?'alt':'';?>">
		<td class="tar mid"><?php echo rtrim(rtrim($v['Quantity'],'0'),'.');?></td>
		<td class="mid"><?php echo $v['SKU'];?></td>
		<td class="mid"><?php echo $v['Description'];?></td>
		<td class="tar mid"><?php echo $v['Hours'];?></td>
		<td class="tar mid"><?php echo @number_format(rtrim(rtrim($v['UnitPrice'],'0'),'.'),2);?></td>
		<td class="tar"><?php echo @number_format(rtrim(rtrim($v['Extension'],'0'),'.'),2);?></td>
		</tr><?php
		$total+=$v['Extension'];
	}
	?>
	</tbody>
	<?php $tbody=ob_get_contents();
	ob_end_clean();
	?>
	<tfoot>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td colspan="2" class="tar">TOTAL:</td>
	<td class="tar"><?php echo @number_format($total,2);?></td>
	</tfoot>
	<?php echo $tbody;?>
	</table>
	<div class="legal gray cb">
	  <p> Please make all checks payable to <strong><?php echo $acctData['adminCompany'];?></strong><br />
      Payment due in 10 days.  OUR POLICY IS NET 10 FOR ALL INVOICES.<br />
      Overdue invoices are subject to a 20.00 service charge and interest up to 5% per month<br />
	  <strong>THANK YOU FOR YOUR BUSINESS</strong> </p>
  </div>
  <div class="cb"> </div>
</div>

	<?php
	//------------ end invoice ----------
}
$main_body=ob_get_contents();
ob_end_clean();




if(false){ ?></body>
</html>
<?php
}
?>