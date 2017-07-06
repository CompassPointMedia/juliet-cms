<?php
/*
2009-03-30
----------
we want this page to have a base filter of all invoices and orders (but maybe payments and then it could be subset of a customer center) in a greater environment of purchase orders, checks and etc.

2009-03-28
----------
TODO:
2009-03-30
* js function client_edit() is archaic, replace with SMAAC interface and document

* why do orders not have a shipping state
* show shipping,pymt, and status cols
* color coding system
* logicize inactive - order are not "inactive"
* logicize deletable
* focus on the order
	* navigate
	* add new - make sure this is all modular
	* 
* 


if(!$finanTablesArchitecture)$finanTablesArchitecture='non-zero'; //non-zero or zero - zero balance is the "new" architecture I used with MGA 
if($finanTablesArchitecture=='non-zero'){
	/* --------
	make sure we have the required infrastructure for A/R, UDF and basic income and expense accounts + tax + shipping
	determine if we need to show shipping or not, and what is/are shipping items
	color coding for receipt vs. invoice
	payment status method - this is quite complex
	
	   -------- * /
}else{
	exit('Unable to display non-zero architecture yet')
}



*/

$finanObject='Invoices and Sales'; //note this specifically excludes payments, credit memos - everything exc. these two
$accountingSystemVersion='2.0';
$showComplexShipping=false; //if any complex shipping setups are present, it will be shown regardless of this value

/*
we want to get away from a.Category as it's not something relating to any normalized db strcuture.  Types_ID is temporary till we implement accounting system 3.0
we need a comparison alogrithm between finan_clients and shippingFirstName/LastName/Company
we avoid shipping status as this can be found through hard coding
we avoid billing status - sunsetted due to non-use and non-relational state
CCConfNumber - sunsetted, now using ReferenceNumber - however CCConfNumber has a pretty rich usage and logic so we need to separate in sc4.0
Referral can be sunsetted - again not good rlx structure
Term could remain but better relational to an additional module
comments, notes, clientmessage, invoicesummary - good but need precisely defined
*/

if(!isset($useOrderShipping))$useOrderShipping=false;

//2009-05-24 the difference in these could eventually be eliminated
$vargroup='orders';
$node='Order';

$component='orderList';
$dataobjectLayout=array(
	'date'=>array( /* new */
		'header'=>'Date',
		'sortable'=>1,
		'title'=>'Sort by order date'
	),
	'ordernumber'=>array(
		'header'=>'Order #',
		'sortable'=>1,
		'title'=>'Sort by order number'
	),
	'name'=>array( /* new */
		'header'=>'Name',
		'sortable'=>1,
		'title'=>'Sort by purchaser name'
	),
	/* --- shipping --- */
	'shippedto'=>($useOrderShipping ? array(
		'header'=>'Shipped To',
		'sortable'=>1,
		'title'=>'Sort by address shipped to'
	) : ''),

	'amount'=>array(
		'header'=>'Amount',
		'sortable'=>0,
		'title'=>'Sort by order amount'
	),

	/* --- shipping --- */
	'shipping'=>($useOrderShipping ? array(
		'header'=>'Shipping',
		'sortable'=>0
	) : ''),

	'payment'=>array(
		'header'=>'Payment',
		'sortable'=>0
	),
	'status'=>array(
		'header'=>'Status',
		'sortable'=>0
	)
);
if($sort){
	q("REPLACE INTO bais_settings SET UserName='".$_SESSION['admin']['userName']."', vargroup='orders',varnode='defaultOrderSort',varkey='',varvalue='$sort'");
	q("REPLACE INTO bais_settings SET UserName='".$_SESSION['admin']['userName']."', vargroup='orders',varnode='defaultOrderSortDirection',varkey='',varvalue='".($dir?$dir:1)."'");
	$_SESSION['userSettings']['defaultOrderSort']=$sort;
	$_SESSION['userSettings']['defaultOrderSortDirection']=($dir?$dir:1);
}else{
	$sort=$userSettings['defaultOrderSort'];
	$dir=$userSettings['defaultOrderSortDirection'];
}
/* filter for inactive orders */
$orderActive=($userSettings['hideInactiveOrders']? 'a.Active=1 AND ' : '1 AND ');

$asc=($dir==-1?'DESC':'ASC');
/*
//we don't have order statuses like client statuses, not used
$in=array();
if($statuses=q("SELECT ID FROM finan_clients_statuses", O_COL)){
	foreach($statuses as $v){
		if($userSettings['filterOrderStatus:'.$v])$in[]=$v;
	}
}
if(!count($in)) $in=q("SELECT ID FROM finan_clients_statuses ORDER BY ID DESC LIMIT 2", O_COL);
*/

$sqlQueries=array();
$filterQueries='';
if(count($_SESSION['special']['filterOrderQuery'])){
	foreach($_SESSION['special']['filterOrderQuery'] as $v){
		if($x=parse_query($v,'finan_invoices')) $sqlQueries[]=$x;
	}
	if(count($sqlQueries))$filterQueries='(' . implode( (strtolower($_SESSION['special']['filterOrderQueryJoin'])=='or' ? ' OR ' : ' AND '), $sqlQueries) . ') AND ';
}
/*
2009-04-03 Note these sorts are all root table fields, ^> it's OK to sort by that field, then by a.ID

*/
switch(true){
	case $sort=='ordernumber':
		$orderBy="InvoiceNumber $asc, a.ID $asc";
	break;
	case $sort=='name':
		$orderBy="ShippingLastName $asc, ShippingFirstName $asc, a.ID $asc";
	break;
	case $sort=='shippedto':
		$orderBy="ShippingCountry $asc, ShippingState $asc, ShippingCity $asc, a.ID $asc";
	break;
	default:
		$orderBy="InvoiceDate $asc, a.ID $asc";
}
exit('here');
$orderitems=q("SELECT 
a.ID,
a.Active,
a.CreateDate,
a.Creator,
a.InvoiceNumber,
a.InvoiceDate,
a.Clients_ID,
a.Billing_ID,
a.Accounts_ID,
a.Terms_ID,
a.Wholesale,
a.ShippingFirstName,
a.ShippingLastName,
a.ShippingCompany,
a.ShippingAddress,
a.ShippingAddress2,
a.ShippingCity,
a.ShippingState,
a.ShippingZip,
a.ShippingCountry,
a.PONumber,

h.Name As OrderType,

e.Extension

".($useOrderShipping ? ",
e.Shipping_ID,
f.Shipped,
f.Shippers_ID AS Shipper ":'')."

FROM `finan_invoices` a 
LEFT JOIN addr_contacts b ON a.Contacts_ID=b.ID
LEFT JOIN finan_accounts c ON a.Accounts_ID=c.ID
LEFT JOIN finan_billing d ON a.Billing_ID=d.ID,
finan_transactions e ".($useOrderShipping ? "
LEFT JOIN finan_shipping f ON e.Shipping_ID=f.ID,":',')."
finan_clients g,
finan_accounts h,
finan_accounts_types i

WHERE
$orderActive
a.ID=e.Invoices_ID AND 
a.Clients_ID=g.ID AND
a.Accounts_ID=h.ID AND
h.Types_ID=i.ID AND
i.Name IN('Accounts Receivable','Other Current Asset')

ORDER BY $orderBy", O_ARRAY);

if(!$refreshComponentOnly){
	?><style>
	.data1 td{
		/* background-color:#eef4fb; /*#9Ec4e9*/
		cursor:pointer;
		padding:2px 2px 1px 7px;
		}
	.data1 td.sorted{
		background-color:#dae8f6; /*#9Ec4e9*/
		color:#272727;
		}
	.data1 tr.alt{
		background-color:#cde0f3; /*#9Ec4e9*/
		}
	.data1 tr.alt td.sorted{
		background-color:#cbdff3; /*#9Ec4e9*/
		}
	.data1 thead{
		background-color:#006; /*#FEDFAE*/
		}
	.data1 a{
		color:DARKRED;
		}
	.data1 th{
		color:#FFF;
		font-weight:400;
		text-align:left;
		}
	.data1 th a{
		vertical-align:bottom;
		color:#FFF;
		font-size:109%;
		font-weight:400;
		padding:4px 0px 0px 8px;
		}
	.data1 th{
		text-align:left;
		border-bottom:1px solid #000;
		}
	.data1 td{
		font-size:13px;
		border-bottom:none;
		/*border-bottom:1px dotted #333;*/
		}
	.data1 th.sorted{
		background-color:#9797bb;
		}
	</style>
	<script type="text/javascript" language="javascript">
	hl_bg['orderopt']='#6c7093';
	//hl_txt['orderopt']='';
	//declare the ogrp.handle.sort value even if blank
	ogrp['orderopt']=new Array();
	ogrp['orderopt']['sort']='';
	ogrp['orderopt']['rowId']='';
	ogrp['orderopt']['highlightGroup']='orderopt';
	AssignMenu('^r_([0-9]+)$', 'orderOptions');
	var hideInactiveOrders=<?php echo $userSettings['hideInactiveOrders']?'1':'0'?>;
	function orderoptionsPre(){
		for(var j in hl_grp['orderopt'])j=j;
		var shipping=g(j).getAttribute('shipstatus').split(':');
		//shipping or shipping groups
		shipping[0]=parseInt(shipping[0]);
		shipping[1]=parseInt(shipping[1]);
		(g('oo2').innerHTML=(shipping[0]>1 ? 'Manage Shipping Groups' : (shipping[0]==1 ? 'Manage Shipping' : '(No Shipping Required)')));
		(g('oo2').className=(shipping[0]==0 ? 'menuitems mndis' : 'menuitems'));
		(g('oo3').innerHTML=(shipping[1]<shipping[0] || shipping[0]==0 ? 'Quick Mark Shipped' : 'Quick Mark Unshipped'));
		(g('oo3').className=(shipping[0]==0 ? 'menuitems mndis' : 'menuitems'));
	}
	function orderAction(event, action){
		for(var j in hl_grp['orderopt'])j=j.replace('r_','');
		if(action=='delete'){
			if(!g('r_'+j).getAttribute('deletable')){
				if(confirm('This order cannot be deleted; it has transactions associated with it.  You must first delete all transactions.\n\nWould you like to see a transaction history report for this order?')){
					ow('transactionhistory.php?Orders_ID='+j,'l1_transactionhistory','700,700');
				}
				return false;
			}
			if(!confirm('This will permanently delete this order\'s record.  Are you sure?'))return false;
			window.open('resources/bais_02_01_exe.php?mode=deleteOrder&Orders_ID='+j,'w2');
		}else if(action=='report'){
			if(!j){
				alert('First click on a order record and highlight its row');
				return;
			}
			ow('transactionhistory.php?Orders_ID='+j,'l1_transactionhistory','700,700');
		}
	}
	function toggleActiveObject(ID){
		var active=g('r_'+ID+'_active').getAttribute('active');
		g('r_'+ID+'_active').innerHTML=(active=='1' ? '<img src="../images/i/garbage2.gif" width="18" height="21" align="absbottom" />' : '&nbsp;');
		g('r_'+ID+'_active').title=('Make this order '+(active=='1' ? '':'in')+'active');
		g('r_'+ID+'_active').setAttribute('active', (active=='1'?'0':'1'));
		window.open('resources/bais_01_exe.php?mode=toggleActiveObject&node='+ID+'&table=finan_invoices&current='+active, 'w2');
	}
	function openOrder(){
		for(var j in hl_grp['orderopt'])j=j.replace('r_','');
		ow('orders.php?cbFunction=refreshComponent&cbParam=fixed:orderList&Orders_ID='+j,'l1_orders','700,700');
	}

	</script>
	<div id="orderOptions" class="menuskin1" style="z-index:1000;" onMouseOver="hlght2(event)" onMouseOut="llght2(event)" onClick="executemenuie5(event)" precalculated="orderoptionsPre()">
		<div id="oo1" default="1" style="font-weight:900;" class="menuitems" command="openOrder()" status="Show Information and Edit this order">Open</div>
		<hr class="mhr"/>
		<div id="oo2" class="menuitems" command="orderAction(event, 'manageshipping');" status="Manage which items are shipped and by what drop ship location (if applicable) or method">Manage Shipping Groups</div>
		<div id="oo3" class="menuitems" command="orderAction(event, 'markshipped');" status="Mark the entire order shipped (or unshipped)">Quick Mark Shipped</div>
		<hr class="mhr"/>
		<div id="oo4" class="menuitems" command="orderAction(event, 'delete');" status="Delete this order">Delete</div>
	</div>	
	<?php
}
?>
<div id="orderList" refreshparams="noparams">
	<input type="hidden" name="noparams" id="noparams" value="" />
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="data1" style="clear:both;">
		<thead>
			<tr>
				<?php
				//improved th's to a loop-through: td's still custom coded
				?><!-- control cells -->
				<th id="toggleActive" title="Hide or show inactive orders" style="display:<?php echo $userSettings['hideInactiveOrders'] ? 'none' : 'table-cell'?>;"><a href="javascript:toggleActive();">^</a></th><th>&nbsp;</th><?php
				//----------- column headers ----------------
				foreach($dataobjectLayout as $n=>$v){
					if(!$v)continue;
					?><th id="hdr-<?php echo $n?>" <?php echo $v['sortable'] ? 'sortable="1"' : ''?>><?php if($v['sortable']){ 
						//link tag for sort
						?><a id="a-<?php echo $n?>" href="resources/bais_01_exe.php?mode=sort&vargroup=<?php echo $vargroup?>&node=<?php echo $node?>&sort=<?php echo $n?>&dir=<?php echo !$dir || ($sort==$n && $dir=='-1') ? 1 : -1?>&component=<?php echo $component?>" target="w2" title="<?php echo $v['title'];?>"><?php }?>
						<?php echo $v['header']?>
						<?php 
						//close link tag
						if($v['sortable']){ ?></a><?php }
					?></th><?php
				}
				//keeps right text from being obscured
				?><th style="color:#FFF;font-weight:400;">&nbsp;&nbsp;&nbsp;&nbsp;</th>
			</tr>
		</thead>
		<?php
		if($settings['allowAddOrder']){
		?>
		<tfoot>
		<tr valign="top">
			<td colspan="<?php echo $userSettings['hideInactiveOrders']=='1'?8:9?>"><a href="orders.php?cbFunction=refreshList" onClick="return addOrder();"><img src="../images/i/add_32x32.gif" width="32" height="32">&nbsp;Add order..</a></td>
		</tr>
		</tfoot><?php
		}
		?>
		<tbody <?php if($browser=='Moz')echo 'style="overflow:scroll;height:250px;"';?>>
		<?php
		if($orderitems)
		foreach($orderitems as $idx=>$v){
			//apply any filters here
			//get permissions
			extract($v);
			
			//sums; eventually differentiate between products/services, shipping and tax
			$lineItems++;
			$OrderTotal+=round($Extension,2);
			
			//pre-calculation based on item rows
			if($Shipping_ID==='0' && !$UnshippedItemGroupsAssigned){
				$UnshippedItemGroupsAssigned=true;
				//first "zero" shipping item, create default shipping method; NOTE that non-shippables have shipping_id set to NULL
				if($shipper){
					//OK - gotten the first time
					$Shippers_ID=$shipper['ID'];
					$originZip=$shipper['OriginZip'];
				}else if($shipper=q("SELECT * FROM finan_shippers WHERE PrimaryShipper", O_ROW)){
					//OK
					$Shippers_ID=$shipper['ID'];
					$originZip=$shipper['OriginZip'];
				}else if($shipper=q("SELECT * FROM finan_shippers ORDER BY ID ASC LIMIT 1", O_ROW)){
					mail($developerEmail,'Shippers ID selected as lowest ID record',get_globals(), $fromHdrBugs);
					q("UPDATE finan_shippers SET PrimaryShipper=1 WHERE ID=".$shipper['ID']);
					$Shippers_ID=$shipper['ID'];
					$originZip=$shipper['OriginZip'];
				}else{
					mail($developerEmail,'New Shippers record created (default)',get_globals(), $fromHdrBugs);
					$Shippers_ID=q("INSERT INTO finan_shippers SET
					`PrimaryShipper` = b '1',
					`OriginTitle` = '".addslashes($adminCompany)."',
					`OriginAddress` = '".addslashes($adminAddress)."',
					`OriginCity` = '".addslashes($adminCity)."',
					`OriginState` = '".addslashes($adminState)."',
					`OriginZip` = '".addslashes($adminZip)."'", O_INSERTID);
					$originZip=$adminZip;
				}
				//create shipping order
				$tempShipping_ID=$Shipping_ID=q("INSERT INTO finan_shipping SET
				Shippers_ID=$Shippers_ID,
				OriginZip='$originZip',
				DestinationZip='$ShippingZip',
				Notes='Added by list_orders.php (comp_20) line ".__LINE__."'", O_INSERTID);
				
				//update non-assigned transactions
				q("UPDATE finan_transactions SET Shipping_ID=$Shipping_ID WHERE !Shipping_ID AND Invoices_ID='$ID' AND Payments_ID IS NULL /* note we need to clean up types */");
			}else if($Shipping_ID==='0' && $UnshippedItemGroupsAssigned){
				$Shipping_ID=$tempShipping_ID;
			}
			//only count non-null shipping orders
			if(!is_null($Shipping_ID))$shipStatus[$Shipping_ID]=($Shipped ? 1 : 0);
	
			//data output
			if($v['ID']==$orderitems[$idx+1]['ID'])continue;
			$i++;
			
			//this would be true if payments have been applied or shipment has taken place
			//NOTE 2009-04-03 - also consider if this a "anchor" order for futher subs that have been made
			$deletable=@(array_sum($shipStatus) /* any shipment made */ ? false : true);
			
			?><tr id="r_<?php echo $ID?>" onclick="h(this,'orderopt',0,0,event);" ondblclick="h(this,'orderopt',0,0,event);openOrder();" oncontextmenu="h(this,'orderopt',0,1,event);" <?php if(!fmod($i,2))echo 'class="alt"';?>  deletable="<?php echo $deletable?>" shipstatus="<?php ?>" active="<?php echo $Active?'1':'0'?>">
				<?php if(!$userSettings['hideInactiveOrders']){ ?>
				<td id="r_<?php echo $ID?>_active" title="Make this order <?php echo $Active ? 'in':''?>active" onclick="toggleActiveObject(<?php echo $ID?>);" active="<?php echo $Active?>"><?php
				if(!$Active){
					?><img src="../images/i/garbage2.gif" width="18" height="21" align="absbottom" /><?php
				}else{
					?>&nbsp;<?php
				}
				?></td>
				<?php } ?>
				<td nowrap="nowrap"><?php
				if($deletable){
					?><a title="Delete this order" href="resources/bais_01_exe.php?mode=deleteOrder&Orders_ID=<?php echo $ID?>" target="w2" onClick="if(!confirm('This will permanently delete this order\'s record.  Are you sure?'))return false;"><img src="../images/i/del2.gif" alt="delete" width="16" height="18" border="0" /></a><?php
				}else{
					?><img src="../images/i/spacer.gif" width="18" height="18" /><?php
				}
				?>&nbsp;&nbsp;<a title="Edit this order's information" href="orders.php?cbFunction=refreshComponent&cbParam=fixed:orderList&Orders_ID=<?php echo $ID?>" onClick="return ow(this.href,'l1_orders','700,700');"><img src="../images/i/edit2.gif" width="15" height="18" border="0"></a>&nbsp;</td>
				<!-- user columns -->
				<td <?php echo $sort=='date' ? 'class="sorted"':''?>><?php echo date('m/d/Y',strtotime($InvoiceDate));?></td>
				<td <?php echo $sort=='ordernumber' ? 'class="sorted"':''?>><?php echo $InvoiceNumber;?></td>
				<td <?php echo $sort=='name' ? 'class="sorted"':''?>><?php echo $ShippingLastName. ', '.$ShippingFirstName?></td>
				<?php if($useOrderShipping){ ?>
				<td <?php echo $sort=='shippedto' ? 'class="sorted"':''?>><?php
				echo $ShippingCity.', '.$ShippingState. '&nbsp;&nbsp;&nbsp;'.(strtolower($ShippingCountry)!=='usa' ? $ShippingCountry : '');
				?></td>
				<?php } ?>
				<td <?php echo $sort=='amount' ? 'class="sorted"':''?>>$<?php 
				echo number_format($OrderTotal,2);
				?></td>
				<?php if($useOrderShipping){ ?>
				<td><?php 
				if(!count($shipStatus)){
					//n/a
					?><div class="fr">not req'd.</div><?php
				}else if(count($shipStatus)==1){
					?><div class="fr"><?php echo ($shipStatus[0] ? 'not shipped' : 'shipped'); ?></div><?php
				}else{
					?><span title="Multiple shipping bills for this order">[m]</span>&nbsp;
					<div class="fr"><?php
					echo (array_sum($shipStatus)==count($shipStatus) ? 'fully ' : (array_sum($shipStatus)==0 ? 'partially ' : '')).' shipped';
					?></div><?php
				}
				?></td>
				<?php } ?>
				<td>payment</td>
				<td>status</td>
				<td>&nbsp;&nbsp;</td>
			</tr><?php
			
			//resets
			$lineItems=0;
			$OrderTotal=0;
			$UnshippedItemGroupsAssigned=false;
			$shipStatus=array();
			unset($tempShipping_ID);
		}
		?></tbody>
	</table>
</div>