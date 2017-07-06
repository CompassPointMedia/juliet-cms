<?php
/*
todo
------------------------------
get the manufacturer showing
show the product generically using comp_ecom_flex
icon for image present - default images/product
	[ ]
[ ] [ ] - this = small&large, will take some work + does the model have an image
test in pacakge
report
export of items via iif
export of items via csv
dbl click
groupings by models etc - [+] to expand like on GF
*/

$qx['useRemediation']=true;
$qx['tableList']=array_merge($qx['tableList'], array('finan_items','finan_ItemsItems','finan_items_categories','finan_items_manufacturers','finan_items_categories','finan_items_subcategories', /* views: */ 'v_finan_items_mfr_account'));


$dataset='Item';
$datasetComponent='itemList';
$datasetTable='v_finan_items_mfr_account';
$datasetTableIsView=true;
$datasetActiveUsage=true;
$datasetFieldList='*';


$datasetLayout=array(
	'ispackage'=>array(
		'header'=>'Pk.',
		'sortable'=>1,
		'title'=>'Sort by item being a package'
	),
	'category'=>array(
		'header'=>'Category',
		'sortable'=>1,
		'title'=>'Sort by item category'
	),
	'groupleader'=>array(
		'header'=>'Lead',
		'sortable'=>1,
		'title'=>'Sort by item being a group leader'
	),
	'model'=>array(
		'header'=>'Model',
		'sortable'=>1,
		'title'=>'Sort by item model'
	),
	'sku'=>array(
		'header'=>'P/N',
		'sortable'=>1,
		'title'=>'Sort by part number (SKU)'
	),
	'name'=>array(
		'header'=>'Name',
		'sortable'=>1,
		'title'=>'Sort by item name'
	),
	'manufacturer'=>array(
		'header'=>'Mfr.',
		'sortable'=>1,
		'title'=>'Sort by item manufacturer'
	),
	'dealerprice'=>array(
		'header'=>'Cost',
		'sortable'=>1,
		'title'=>'Sort by your cost (purchase price)'
	),
	'retailprice'=>array(
		'header'=>'Retail',
		'sortable'=>1,
		'title'=>'Sort by retail price'
	),
	'discountprice'=>array(
		'header'=>'Discount',
		'sortable'=>1,
		'title'=>'Sort by discount price'
	)
);
//this is stored in rbase_AccountModules.Settings for the Account (e.g. cpm103)
@extract($moduleConfig['dataobjects'][$dataset]);

if($submode=='exportDataset')ob_start(); //------- for handling CSV export ---------


//-------------------- begin generic coding --------------------
if(!isset($datasetActiveActiveExpression))$datasetActiveActiveExpression='Active=1';
if(!isset($datasetActiveInactiveExpression))$datasetActiveInactiveExpression='Active=0';
if(!isset($datasetActiveAllExpression))$datasetActiveAllExpression='1';
if($sort){
	q("REPLACE INTO bais_settings SET UserName='".sun()."', 
	vargroup='".$dataset."',varnode='default".$dataset."Sort',varkey='',varvalue='$sort'");
	q("REPLACE INTO bais_settings SET UserName='".sun()."', 
	vargroup='".$dataset."',varnode='default".$dataset."SortDirection',varkey='',varvalue='".($dir?$dir:1)."'");
	$_SESSION['userSettings']['default'.$dataset.'Sort']=$sort;
	$_SESSION['userSettings']['default'.$dataset.'SortDirection']=($dir?$dir:1);
}else{
	$sort=$userSettings['default'.$dataset.'Sort'];
	$dir=$userSettings['default'.$dataset.'SortDirection'];
}
$asc=($dir==-1?'DESC':'ASC');

/* filter for inactive */
if(isset($hideInactive)){
	//update settings and environment
	q("REPLACE INTO bais_settings SET UserName='".$_SESSION['admin']['userName']."', varnode='hideInactive$dataset',varkey='',varvalue='$hideInactive'");
	$_SESSION['userSettings']['hideInactive'.$dataset]=$hideInactive;
	?><script language="javascript" type="text/javascript">
	hideInactive<?php echo $dataset?>=<?php echo $hideInactive?>;
	window.parent.hideInactive<?php echo $dataset?>=<?php echo $hideInactive?>;
	</script><?php
}
$datasetActive = ( $userSettings['hideInactive'.$dataset]==1 ? $datasetActiveActiveExpression : ( $userSettings['hideInactive'.$dataset]==-1 ? $datasetActiveInactiveExpression : $datasetActiveAllExpression ));

//set where clause including statuses and filter pairs
unset($filterQueries);
if($filterOverride){
	$filterQueries=$filterOverride;
}else if($sqlQueries){
	 $filterQueries=' AND (' . implode( (strtolower($_SESSION['special']['filter'.$dataset.'QueryJoin'])=='or' ? ' OR ' : ' AND '), $sqlQueries) . ')';
}else if(count($_SESSION['special']['filterQuery'][$dataset])){
	foreach($_SESSION['special']['filterQuery'][$dataset] as $v){
		if($x=parse_query($v,$datasetTable)) $sqlQueries[]=$x;
	}
	if($sqlQueries) $filterQueries=' AND (' . implode( (strtolower($_SESSION['special']['filter'.$dataset.'QueryJoin'])=='or' ? ' OR ' : ' AND '), $sqlQueries) . ')';
}
//-------------------- end generic coding --------------------


#delete
//filter for inactive items
$itemActive=($userSettings['hideInactiveItems']? ' AND Active=1' : '');



switch(true){
	case $sort=='ispackage':
		$orderBy="IF(b.Items_ID IS NULL, 0,IF(SUM(d.ChildItems_ID)>0, 2, 1)) $asc, a.Model $asc, a.SKU $asc";
	break;
	case $sort=='category':
		$orderBy="a.Category $asc, a.SubCategory $asc, a.SKU $asc";
	break;
	case $sort=='groupleader':
		$orderBy="a.GroupLeader $asc, a.Model $asc, a.SKU $asc";
	break;
	case $sort=='model':
		$orderBy="a.Model $asc, a.SKU $asc";
	break;
	case $sort=='sku':
		$orderBy="a.SKU $asc";
	break;
	case $sort=='name':
		$orderBy="a.Name $asc, a.SKU $asc";
	break;
	case $sort=='manufacturer':
		$orderBy="Manufacturer $asc";
	break;
	case $sort=='dealerprice':
		$orderBy="a.PurchasePrice $asc";
	break;
	case $sort=='retailprice':
		$orderBy="a.UnitPrice $asc, a.Name $asc";
	break;
	case $sort=='discountprice':
		$orderBy="a.UnitPrice2 $asc, a.Name $asc";
	break;
	default:
		$orderBy="a.SKU $asc";
}
//count, batch, limit
q("SELECT COUNT(*) FROM finan_items a LEFT JOIN finan_items_packages b ON a.ID=b.Items_ID
LEFT JOIN finan_transactions c ON a.ID=c.Items_ID
LEFT JOIN finan_ItemsItems d ON a.ID=d.ParentItems_ID
WHERE 1".
($useStatusFilterOptions && count($inStatusSet) ? " AND Statuses_ID IN(".implode(',',$inStatusSet).")":'').
($datasetActiveUsage==true ? " AND $datasetActive" : '').
$filterQueries.
($transSettings['itemGroupBy'] ? ' GROUP BY '.$transSettings['itemGroupBy'] : ' GROUP BY a.ID ').
" 
ORDER BY $orderBy", O_VALUE);
if(!$batch)$batch=500;
if(($count=$qr['count'])>$batch){
	if(!$idx)$idx=1;
	$limit="LIMIT ".(1+($idx-1)*500).", $batch";
}

$records=q(
"SELECT
a.ID,
a.Active,
a.SKU,
a.Type,
IF(b.Items_ID IS NULL, 0,IF(SUM(d.ChildItems_ID)>0, 2, 1)) AS IsPackage,
IF(COUNT(DISTINCT c.Items_ID), 0,1) AS IsDeletable,
COUNT(c.Items_ID) AS UseCount,
a.Category,
a.GroupLeader,
a.Model,
a.Name,
a.Description,
a.UnitPrice,
a.UnitPrice2,
a.WholesalePrice,
a.PurchasePrice,
a.Weight
FROM finan_items a LEFT JOIN finan_items_packages b ON a.ID=b.Items_ID
LEFT JOIN finan_transactions c ON a.ID=c.Items_ID
LEFT JOIN finan_ItemsItems d ON a.ID=d.ParentItems_ID
WHERE 1".
($useStatusFilterOptions && count($inStatusSet) ? " AND Statuses_ID IN(".implode(',',$inStatusSet).")":'').
($datasetActiveUsage==true ? " AND $datasetActive" : '').
$filterQueries.
($transSettings['itemGroupBy'] ? ' GROUP BY '.$transSettings['itemGroupBy'] : ' GROUP BY a.ID ').
" 
ORDER BY $orderBy $limit", O_ARRAY_ASSOC);
$recordCols=$qr['cols'];


if(!$refreshComponentOnly){
	?><style type="text/css">
	</style>
	<script type="text/javascript" language="javascript">
	hl_bg['itemopt']='#6c7093';
	hl_baseclass['itemopt']='normal';
	hl_class['itemopt']='hlrow';
	//declare the ogrp.handle.sort value even if blank
	ogrp['itemopt']=new Array();
	ogrp['itemopt']['sort']='';
	ogrp['itemopt']['rowId']='';
	ogrp['itemopt']['highlightGroup']='itemopt';
	AssignMenu('^ri(g|p)*_[0-9]+', 'itemsListOptionsA');
	
	function objectSort(o){
		showPending('sort');
		window.open(o.href,'w2');
		return false;
	}
	function showPending(region){
		g('hdr-ctrls').innerHTML='sorting..';
	}
	function precalcItemsList(evt){
		//get object
		var reg=/[^0-9]*/;
		for(j in hl_grp['itemopt'])trid=j;
		var IsDeletable=g(trid).getAttribute('isdeletable');
		var IsPackage=g(trid).getAttribute('ispackage');
		g('ilo4').className = (IsPackage ? 'menuitems' : 'menuitems mndis');
		g('ilo8').className=(IsDeletable ? 'menuitems' : 'menuitems mndis');
		g('ilo4').style.color = (IsPackage ? '#000' : '#777');
		g('ilo8').style.color=(IsDeletable ? '#000' : '#777');
	}
	function manage_items(action){
		//get object
		var reg=/[^0-9]*/;
		for(j in hl_grp['itemopt'])trid=j;
		var IsDeletable=g(trid).getAttribute('isdeletable');
		var IsPackage=g(trid).getAttribute('ispackage');
		switch(action){
			case 'open':
				ow('items.php?Items_ID='+trid.replace(reg,'')+'&cbFunction=refreshList', (IsPackage>0?'l1_itemspackage':'l1_items'), '700,700');
			break;
			case 'newitem':
			case 'newpackage':
				ow('items.php?cbFunction=refreshList' + (action=='newpackage'?'&IsPackage=1':''), (IsPackage>0?'l1_itemspackage':'l1_items'), '700,700');
			break;
			case 'additem':
				ow('package_items.php?Items_ID='+trid.replace(reg,'')+'&view=list&cbFunction=refreshList&cbParam=fixed:itemonly' + (action=='newpackage'?'&IsPackage=1':''), 'l1_packageitems','500,600');
			break;
			case 'qreport':
				alert('undeveloped');
			break;
			case 'exportcurrentcsv':
				alert('undeveloped');
			break;
			case 'exportcurrentiif':
				alert('undeveloped');
			break;
			case 'delete':
				if(!confirm('This cannot be reversed. Are you sure?'))return false;
				window.open('resources/bais_01_exe.php?mode=deleteItem&Items_ID='+trid.replace(reg,''),'w2');
				g(trid).style.display='none';
			break;
		}
	}
	</script>
	<div id="itemsListOptionsA" class="menuskin1" style="z-index:1000;" onMouseOver="hlght2(event)" onMouseOut="llght2(event)" onClick="executemenuie5(event)" precalculated="precalcItemsList(event)">
		<div id="ilo1" class="menuitems" command="manage_items('open');" status="Open the selected item" default="1" style="font-weight:900;">Open</div>
		<hr class="mhr"/>
		<div id="ilo2" class="menuitems" command="manage_items('newitem');" status="Add a new item to the database">New item..</div>
		<div id="ilo3" class="menuitems" command="manage_items('newpackage');" status="Add a new package to the database using at least one or more other items">New package..</div>
		<div id="ilo4" class="menuitems" command="manage_items('additem');" status="Add another item to this package">Add item to package</div>
		<hr class="mhr"/>
		<div id="ilo5" class="menuitems" command="manage_items('qreport');" status="Usage report (history) for this item">Quick report</div>
		<div id="ilo6" class="menuitems" command="manage_items('exportcurrentcsv');" status="Export the currently shown list of items in CSV format">Export items as CSV</div>
		<div id="ilo7" class="menuitems" command="manage_items('exportcurrentiif');" status="Export the currently shown list of items in IIF format">Export items as IIF</div>
		<div id="ilo8" class="menuitems" command="manage_items('delete');" status="Remove this item permanently">Delete</div>
	</div><?php
}
?>
<div class="fr" style="width:75px;">
	<a title="Change settings for your items" onclick="return ow(this.href,'l1_dosettings','700,700');" tabindex="-1" href="settings_dataobject.php?object=finan_items&cbFunction=refreshComponent&storagemethod=module">Settings</a>
</div>
<div class="fr" style="width:125px;">
	<?php require($COMPONENT_ROOT.'/comp_01_filtergadget_v104.php'); ?>
</div>

<div id="<?php echo $datasetComponent?>" refreshparams="noparams">
	<h3><?php echo $adminClientName?> Items (<span id="<?php echo $datasetComponent?>_count"><?php echo $count;?></span>)</h3>
	<input type="hidden" name="noparams" id="noparams" value="" />
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="complexData" style="clear:both;">
		<?php //------------------------- begin generic THEAD/TFOOT coding --------------------- ?>
		<thead>
			<tr>
				<!-- control cells -->
				<?php if(!$hideObjectInactiveControl){ ?>
				<th id="toggleActive" class="activetoggle"><a title="Hide or show inactive <?php echo strtolower($dataset);?>" href="javascript:toggleActive('<?php echo $datasetComponent?>',hideInactive<?php echo $dataset?>);">&nbsp;&nbsp;</a></th>
				<?php } ?>
				<th>&nbsp;</th><?php
				//----------- column headers ----------------
				foreach($datasetLayout as $n=>$v){
					?><th id="hdr-<?php echo $n?>" <?php echo $v['sortable'] ? 'sortable="1"' : ''?> <?php echo $sort==$n ? 'class="sorted"':''?>><?php if($v['sortable']){ 
						//link tag for sort
						?><a id="a-<?php echo $n?>" href="resources/bais_01_exe.php?mode=refreshComponent&component=<?php echo $datasetComponent?>&sort=<?php echo $n?>&dir=<?php echo !$dir || ($sort==$n && $dir=='-1') ? 1 : -1?>" target="w2" title="<?php echo $v['title'];?>"><?php }?>
						<?php echo $v['header']?>
						<?php 
						//close link tag
						if($v['sortable']){ ?></a><?php }
					?></th><?php
				}
				//keeps right text from being obscured
				?>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tfoot>
			<tr valign="top">
			<td colspan="100%"><?php
			if($count>$batch){
				?>
				<style type="text/css">
				.indices{
					display:inline;
					margin:0px;
					padding:0px;
					}
				.indices li{
					border:1px solid gold;
					padding:5px;
					display:inline-block;
					font-size:129%;
					margin:0px 3px;
					cursor:pointer;
					}
				li.on{
					background-color:papayawhip;
					}
				</style><div class="fr">
				<span><?php echo $batch?></span>
				<ul class="indices">
				<?php
				$qs=preg_replace('/&*idx=[0-9]+/','',$QUERY_STRING);
				$baseURL='/console/list_items.php?'.$qs;
				for($i=1; $i<=ceil($count/$batch); $i++){
					?><li class="<?php if($idx==$i)echo 'on';?>" onclick="window.location='<?php echo $baseURL . '&idx='.$i?>';" onmousover="this.className='on';" onmouseout="this.className='<?php if($idx==$i)echo 'on';?>';"><?php echo $i?></li><?php 
				}
				?>
				</ul>
				</div><?php
			}
			
			?><a href="items.php?cbFunction=refreshComponent&cbParam=fixed:<?php echo $datasetComponent?>" onclick="return ow(this.href,'l1_items','700,700');"><img src="../images/i/add_32x32.gif" width="32" height="32">&nbsp;Add <?php echo strtolower($dataset)?>..</a></td>
			</tr>
		</tfoot>
		<?php //------------------------- end generic coding --------------------- ?>

		<tbody id="<?php echo $datasetComponent?>_tbody" style="overflow-y:scroll;overflow-x:hidden;height:350px;">
		<?php
		$datasetOutput='';
		if($records){
			foreach($records as $v){
				//apply any filters here
				$i++;
				extract($v);
				
				?><tr id="ri<?php echo $IsPackage?'p':($IsGrouped?'g':'')?>_<?php echo $ID?>" onclick="h(this,'itemopt',0,0,event);" ondblclick="h(this,'itemopt',0,0,event);open<?php echo $dataset?>();" oncontextmenu="h(this,'itemopt',0,1,event);" class="normal<?php echo fmod($i,2)?' alt':''?>" deletable="<?php echo $deletable?>" active="<?php echo $Active?>">
					<?php if(!$hideObjectInactiveControl){ ?>
					<td id="r_<?php echo $ID?>_active" title="Make this <?php echo strtolower($dataset);?> <?php echo $Active ? 'in':''?>active" onclick="toggleActiveObject('<?php echo $dataset?>',<?php echo $ID?>,'<?php echo $datasetComponent?>');" class="activetoggle"><?php
					if(!$Active){
						?><img src="../images/i/garbage2.gif" width="18" height="21" align="absbottom" /><?php
					}else{
						?>&nbsp;<?php
					}
					?></td>
					<?php } ?>
	
					<td nowrap="nowrap"><?php
					if($deletable){
						?><a title="Delete this item" href="resources/bais_01_exe.php?mode=deleteItem&Items_ID=<?php echo $ID?>" target="w2" onClick="if(!confirm('This will permanently delete this member\'s record.  Are you sure?'))return false;">&nbsp;<img src="../images/i/del2.gif" alt="delete" width="16" height="18" border="0" /></a><?php
					}else{
						?>&nbsp;<img src="../images/i/spacer.gif" width="18" height="18" /><?php
					}
					?>&nbsp;&nbsp;<a title="Edit this item" href="items.php?Items_ID=<?php echo $ID?>" onclick="return ow(this.href,'l1_items','700,700');"><img src="../images/i/edit2.gif" width="15" height="18" border="0"></a>&nbsp;</td>

					<!-- user columns -->
					<td class="<?php echo $sort=='inpackage' ? 'sorted':''?>"><img src="/images/i/<?php echo ($IsPackage=='2' ? 'package' : ($IsPackage=='1' ? 'package_empty':'spacer')) . '.gif'?>" width="18" height="18" alt="package" border="0" /></td>
					 <?php echo $sort=='name' ? 'class="sorted"':''?>
					<td class="<?php echo $sort=='category' ? 'sorted':''?>" nowrap="nowrap"><?php echo h($Category)?></td>
					<td class="<?php echo $sort=='groupleader' ? 'sorted':''?>"><?php echo $GroupLeader ? 'Y' : ''?></td>
					<td class="<?php echo $sort=='model' ? 'sorted':''?>" nowrap="nowrap"><?php echo h($Model)?></td>
					<td class="<?php echo $sort=='sku' ? 'sorted':''?>" nowrap="nowrap"><?php echo h($SKU)?></td>
					<td class="<?php echo $sort=='name' ? 'sorted':''?>" style="width:275px;"><?php echo h($Name)?></td>
					<td class="<?php echo $sort=='manufacturer' ? 'sorted':''?>"><?php echo h($Manufacturer)?></td>
					<td class="tr<?php echo $sort=='dealerprice' ? ' sorted':''?>"><?php echo number_format($PurchasePrice,2)?></td>
					<td class="tr<?php echo $sort=='retailprice' ? ' sorted':''?>"><?php echo number_format($UnitPrice,2)?></td>
					<td class="tr<?php echo $sort=='discountprice' ? ' sorted':''?>"><?php echo $UnitPrice > 0 ? number_format($UnitPrice2,2):'--';?>&nbsp;&nbsp;&nbsp;</td>
				</tr><?php
			}
		}
		?></tbody>
	</table>
</div>
<?php
if($submode=='exportDataset')ob_end_clean();
?>