<?php
if(!$refreshComponentOnly){
	?>
	<style type="text/css">
	#orderDetailShipping table{
		border-collapse:collapse;
		width:100%;
		}
	#orderDetailShipping td{
		padding:2px 5px 0px 5px;
		border-bottom:1px solid #ccc;
		background-color:white;
		}
	#orderDetailShipping th{
		background-color:SADDLEBROWN;
		color:white;
		text-align:left;
		padding:2px 5px 0px 5px;
		font-weight:400;
		}
	.mfrName{
		font-size:109%;
		}
	</style>
	<script type="text/javascript" language="javascript">
	hl_bg['orderoptshipping']='gold';
	hl_txt['orderoptshipping']='#777';
	//declare the ogrp.handle.sort value even if blank
	ogrp['orderoptshipping']=new Array();
	ogrp['orderoptshipping']['sort']='';
	ogrp['orderoptshipping']['rowId']='';
	ogrp['orderoptshipping']['highlightGroup']='orderoptshipping';
	
	function manageShippingOrder(action, o){
		//new (with objects), move (with objects), remove (with objects), 
		if(action=='edit'){
			ow(o.href,'l1_generic','550,700');
			return false;
		}
		var str='';
		for(var j in hl_grp['orderoptshipping'])str+=j.replace(/[^0-9]*/,'')+',';
		if(!str){
			alert('Select one or more items first');
			return false;
		}
		
		if(action=='move'){
			if(shippingArray.length<1){
				alert('You cannot move these items - nowhere to move them!  Select "Create New Shipping Order" instead');
				return false;
			}
			ow('shipping_orders_moveitems.php?Headers_ID=<?php echo $headerID?>&Items='+str+'&cbFunction=refreshComponent&cbParam=fixed:orderDetailShipping','l1_generic','550,200');
			return false;
		}else if(action==='new'){
			ow('shipping_orders.php?submode=new&Headers_ID=<?php echo $headerID?>&Items='+str+'&cbFunction=refreshComponent&cbParam=fixed:orderDetailShipping','l1_generic','550,700');
		}else if(action=='remove'){
			if(!confirm('This will remove these item(s) from this shipping package.  If it/they are the only or last item(s) in the package, the shipping package will also be deleted including tracking number, etc..  Continue?'))return false;
			window.open('resources/bais_01_exe.php?mode=removeItemsFromShippingOrder&Items='+str+'&cbFunction=refreshComponent&cbParam=fixed:orderDetailShipping','w2');	
		}
		return false;
	}
	</script><?php
}


if(!isset($headerIDObject))$headerIDObject='Orders_ID';
$headerID=$$headerIDObject;
$shippingRows=q("SELECT 
a.ID, 
a.SKU, 
a.Name, 
a.Quantity, 
a.Shipping_ID,
b.Shippers_ID,
b.Shipped,
b.ShipDate,
b.Method,
b.TrackingNumber,
b.ReceiptNumber,
c.Name AS ShipperName,
c.Manufacturers_ID AS SM_ID,
d.Weight, 
d.Length, 
d.Width, 
d.Depth, 
d.UM, 
d.PK, 
d.Manufacturers_ID,
e.Name As Manufacturer
FROM finan_transactions a 
LEFT JOIN finan_shipping b ON a.Shipping_ID=b.ID 
LEFT JOIN finan_shippers c ON b.Shippers_ID=c.ID
LEFT JOIN finan_items d ON a.Items_ID=d.ID
LEFT JOIN finan_manufacturers e ON d.Manufacturers_ID=e.ID
WHERE a.Headers_ID='$headerID'
ORDER BY a.Shipping_ID, a.Idx, a.ID", O_ARRAY);
?>
<div id="orderDetailShipping" refreshParams="Orders_ID:ID">
	<div class="fr">
	<a href="#" onclick="return manageShippingOrder('new');" title="Move the selected items to a new shipping package">[+]</a>  &nbsp;&nbsp; 
	<a href="#" onclick="return manageShippingOrder('remove');" title="Remove the selected items from this shipping package">[-]</a>  &nbsp;&nbsp; 
	<a href="#" title="Move the selected items to a different shipping package" onclick="return manageShippingOrder('move');">[>]</a></div>
	<table class="cb">
		<thead>
		<tr>
			<th>Qty</th>
			<th>P/N</th>
			<th>Item</th>
			<th>Manufacturer</th>
			<th>Weight</th>
			<th>&nbsp;</th>
		</tr>
		</thead>
		<!--
		<tfoot>
		<tr>
			<td class="shippingSummaryText" colspan="4">Shipping Summary:</td>
			<td class="shippingSummary">&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		</tfoot>
		-->
		<tbody>
		<?php
		foreach($shippingRows as $n=>$v){
			extract($v);

			if($n==1 || $shippingRows[$n-1]['Shipping_ID']!==$Shipping_ID){
				?><tr>
				<td colspan="100%"><?php
				if($Shipping_ID){
					$jsShippingArray[]=$Shipping_ID;
					if($SM_ID){
						if(!$sman[$SM_ID])$sman[$SM_ID]=q("SELECT ID, Name, Manufacturers_Img_Logo, City, State FROM finan_manufacturers WHERE ID=$SM_ID",O_ROW);
						$hasMfr=true;
					}else{
						$hasMfr=false;
					}
					?><span class="mfrName" title="Name and location of shipper"><a title="Edit this shipping method" href="shipping_orders.php?Headers_ID=<?php echo $headerID?>&cbFunction=refreshComponent&cbParam=fixed:orderDetailShipping&Shipping_ID=<?php echo $Shipping_ID?>" onclick="return manageShippingOrder('edit',this);"><?php echo $hasMfr ? $sman[$SM_ID]['Name'] : $ShipperName;?></a> <?php if($hasMfr)echo ' - ' . $sman[$SM_ID]['City'] . ($sman[$SM_ID]['City'] ? ', ':'') . $sman[$SM_ID]['State']?></span> <?php
					/* shipfix */
					echo $_settings['shipMethodArray'][$Method];
					if($TrackingNumber)echo ' Tracking #'.$TrackingNumber;
				}else if(is_null($Shipping_ID)){
					?><span class="mfrName noshipping">(listed &ldquo;no shipping required&rdquo;)</span><?php
				}else if($Shipping_ID=='0'){
					?><span class="mfrName noshippingassigned"><a title="Assign a shipping method to these items" href="shipping_orders.php?Headers_ID=<?php echo $headerID?>&cbFunction=refreshComponent&cbParam=fixed:orderDetailShipping" onclick="return manageShippingOrder('assignmethod');">(not assigned a shipping method)</a></span><?php
				}
				?></td>
				</tr><?php
			}
			?><tr id="rs_<?php echo $ID?>" onclick="h(this,'orderoptshipping',1,0,event);" oncontextmenu="h(this,'orderoptshipping',1,1,event);" class="dataObjectRow">
				<td><?php echo n($Quantity, intifpossible);?></td>
				<td><?php echo $SKU?></td>
				<td><?php echo $Name?></td>
				<td><?php echo $Manufacturer?></td>
				<td><?php echo n($Weight, intifpossible);?></td>
				<td>&nbsp;</td>
			</tr><?php
		}
		
		?>
		</tbody>
	</table>
	<script language="javascript" type="text/javascript">
	var shippingArray=[<?php echo @implode(',',$jsShippingArray)?>];
	try{
		//if a called component
		window.parent.shippingArray=[<?php echo @implode(',',$jsShippingArray)?>];
		window.parent.hl_grp['orderoptshipping']=[];
	}catch(e){ }
	</script>
</div>