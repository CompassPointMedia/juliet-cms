<?php
/*
2009-05-25
----------
areas to work on
	* most shipping issues will resolve is we simply hide shipping as an option
	* in some cases we have Manufacturer, others Manufactuers_ID - I WANT to have both cases to increase flexibility of my coding library
	* whether or not we can edit the order - and to what level i.e. can we edit the items or just the headers
	* need to print the order
	* what accounts to show (only AR or UDF - which is different from Quickbooks - we should have a color coding on this)


2009-03-30
----------
needs to work as a new entry interface and also an existing entry interface - got some of this from prog notes on Giocosa
header id declared here for call

//get items for combo

*/
if(!isset($headerIDObject))$headerIDObject='Orders_ID';
$headerID=$$headerIDObject;
$orderRows=q("SELECT t.* FROM finan_transactions t JOIN finan_headers h ON t.Headers_ID=h.ID WHERE h.ID='$headerID' AND h.Accounts_ID!=t.Accounts_ID ORDER BY t.Shipping_ID, t.Idx, t.ID", O_ARRAY);
?>
<?php ob_start();?>
<select name="Items_ID[]" id="Items_ID" onchange="dChge(this);itemMgr(this);" style="width:175px;">
<option value="">&nbsp;&nbsp;</option>
<?php
$items=q("SELECT ID, Name, SKU, Category FROM finan_items WHERE Active=1 ORDER BY Category, SKU", O_ARRAY_ASSOC);
$i=0;
foreach($items as $n=>$v){
	$i++;
	if($v['Category']!==$buffer){
		$buffer=$v['Category'];
		if($i>1){
			echo '</optgroup>';
		}
		?><optgroup label="<?php echo $buffer;?>"><?php
	}
	?><option value="<?php echo $n?>"><?php echo h($v['SKU'] . ' - ' . $v['Name'])?></option><?php
}
?></optgroup>
</select>
<?php
$out=ob_get_contents();
ob_end_clean();
$out=str_replace("\n",'',$out);
$out=str_replace("'","\'",$out);
?>
<script language="javascript" type="text/javascript">
var itemlist='<?php echo $out?>';
function writeList(id, idx){
	var str=itemlist;
	str=itemlist.replace('value="'+id+'"', 'value="'+id+'" selected');
	str=str.replace('id="Items_ID', 'id="Items_ID'+idx+'');
	document.write(str);
}
</script>
<style type="text/css">
#orderDetailWrap{
	max-width:900px;
	}
table.orderDetail{
	width:100%;
	}
.orderDetail tbody{
	overflow:scroll;
	height:250px;
	background-color:white;
	border-left:1px solid #000;
	border-right:1px solid #000;
	}
.orderDetail th{
	background-color:darkblue;
	color:white;
	text-align:left;
	font-weight:400;
	}
.orderTotal{
	}
.orderTotalText{
	}
.orderExtension{
	}
.orderDetail .alt{
	background-color:#dadada;
	}
.orderDetail td{
	border-right:1px dotted #666;
	}
</style>
<?php ob_start();?>
<div id="orderDetailWrap">
	<table class="orderDetail" cellpadding="0" cellspacing="0">
		<thead>
		<tr>
			<th width="10%">Item</th>
			<th width="10%">P/N</th>
			<th width="2%">Qty</th>
			<th>Description</th>
			<th class="tar" style="text-align:right">Rate</th>
			<?php if(false){ ?><th>Item</th><?php } ?>
			<th class="tar" style="text-align:right">Extension</th>
			<th>&nbsp;&nbsp;&nbsp;&nbsp;</th>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<td class="orderTotalText" colspan="<?php echo false ? 5 : 4?>">Order Total:</td>
			<td class="orderTotal">&nbsp;</td>
			<td class="orderTotal tar">${{ORDER_TOTAL_HERE}}</td>
			<td>&nbsp;</td>
		</tr>
		</tfoot>
		<tbody>
		<?php
		$maxRows=15;
		$newRowsBuffer=5;
		
		$rowsToShow=(count($orderRows) + $newRowsBuffer > $maxRows ? count($orderRows) + $newRowsBuffer : $maxRows);
		for($i=1; $i<=$rowsToShow; $i++){
			if(list(,$item)=each($orderRows)){
				$row='existing';
			}else{
				unset($item);
				$row='new';
			}
			?><tr id="i_<?php echo $i?>" class="<?php echo fmod($i,2)?'alt':''?>">
			<td>
			<script language="javascript" type="text/javascript">
			writeList('<?php echo $item['Items_ID']?>',<?php echo $i?>);
			</script>
			</td>
			<td><?php
			if($item){
				?><input name="ID[<?php echo $item['ID']?>" type="hidden" id="item<?php echo $item['ID']?>" value="<?php echo $v['ID']?>" size="6" /><?php
			}
			?><?php echo $item['SKU'];?></td>
			<td class="tar" width="2%"><?php echo n($item['Quantity'], intifpossible, blankifzero);?></td>
			<td><?php echo $item['Description']?></td>
			<td class="tar"><?php if($item)echo number_format(round(n($item['UnitPrice'],blankifzero),2),2)?></td>
			<?php if(false){ ?><td>class</td><?php } ?>
			<td class="tar orderExtension"><?php 
			$ext=round($item['Extension'],2);
			$total+=$ext;
			if($item)echo number_format($ext,2);
			?></td>
			<td>&nbsp;</td>
			</tr><?php
		
			if($rows>10)break;
		}
		
		?>
		</tbody>
	</table>
</div>
<?php
$out=ob_get_contents();
$out=str_replace('{{ORDER_TOTAL_HERE}}',number_format($total,2),$out);
ob_end_clean();
echo $out;
?>