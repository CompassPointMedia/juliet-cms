<?php
/*


#in bais_01_exe.php
#in comp02_itemslist.php

*/

$qx['useRemediation']=true;
$qx['tableList']=array_merge($qx['tableList'], array('finan_items','finan_ItemsItems','finan_items_categories','finan_items_manufacturers','finan_items_categories','finan_items_subcategories', /* views: */ 'v_finan_items_mfr_account'));

$dataobject='items'; //for filtergadget
$dataobjectTable='v_finan_items_mfr_account';
$dataobjectTitle='Items';


//sorting and direction
if($sort){
	q("REPLACE INTO bais_settings SET UserName='".$_SESSION['admin']['userName']."', vargroup='items',varnode='defaultItemSort',varkey='',varvalue='$sort'");
	q("REPLACE INTO bais_settings SET UserName='".$_SESSION['admin']['userName']."', vargroup='items',varnode='defaultItemSortDirection',varkey='',varvalue='".($dir?$dir:1)."'");
	$_SESSION['userSettings']['defaultItemSort']=$sort;
	$_SESSION['userSettings']['defaultItemSortDirection']=($dir?$dir:1);
}else{
	$sort=$userSettings['defaultItemSort'];
	$dir=$userSettings['defaultItemSortDirection'];
}
$asc=($dir==-1?'DESC':'ASC');

//filter for inactive items
$itemActive=($userSettings['hideInactiveItems']? ' AND Active=1' : '');


//2008-07-09: set where clause
$sqlQueries=array();
$filterQueries='';
if($filterOverride){
	if(!is_array($filterOverride))$filterOverride=array($filterOverride);
	foreach($filterOverride as $v){
		if($x=parse_query($v,$dataobjectTable)) $sqlQueries[]=$x;
	}
	if(count($sqlQueries))$filterQueries=' AND (' . implode( ' '.($filterQueryJoin ? $filterQueryJoin : 'AND').' ', $sqlQueries) . ')';
}else if(count($_SESSION['dataObjects']['filterQuery'][$dataobject])){
	foreach($_SESSION['dataObjects']['filterQuery'][$dataobject] as $v){
		if($x=parse_query($v,$dataobjectTable)) $sqlQueries[]=$x;
	}
	if(count($sqlQueries))$filterQueries=' AND (' . implode( ' '.$_SESSION['dataObjects']['filterQueryJoin'][$dataobject].' ', $sqlQueries) . ')';
}
$ids=array();
switch(true){
	case strtolower($sort)=='sku':
		$ids=q("SELECT ID, '' FROM $dataobjectTable
		WHERE 1 $itemActive $filterQueries
		ORDER BY SKU $asc $limit", O_COL_ASSOC);
	break;
	case strtolower($sort)=='name':
		$ids=q("SELECT ID, '' FROM $dataobjectTable
		WHERE 1 $itemActive $filterQueries
		ORDER BY Name $asc, SKU $asc $limit", O_COL_ASSOC);
	break;
	case strtolower($sort)=='wholesaleprice':
		$ids=q("SELECT ID, '' FROM $dataobjectTable
		WHERE 1 $itemActive $filterQueries
		ORDER BY WholesalePrice $asc $limit", O_COL_ASSOC);
	break;
	case strtolower($sort)=='unitprice':
		$ids=q("SELECT ID, '' FROM $dataobjectTable
		WHERE 1 $itemActive $filterQueries
		ORDER BY UnitPrice $asc $limit", O_COL_ASSOC);
	break;
	case strtolower($sort)=='model':
		$ids=q("SELECT ID, '' FROM $dataobjectTable
		WHERE 1 $itemActive $filterQueries
		ORDER BY Model $asc $limit", O_COL_ASSOC);
	break;
	case strtolower($sort)=='lead':
		$ids=q("SELECT ID, '' FROM $dataobjectTable
		WHERE 1 $itemActive $filterQueries
		ORDER BY GroupLeader $asc, Model $asc $limit", O_COL_ASSOC);
	break;
	default:
		$sort='category';
		$ids=q("SELECT ID, '' FROM $dataobjectTable
		WHERE 1 $itemActive $filterQueries
		ORDER BY Category $asc, SubCategory $asc $limit", O_COL_ASSOC);
}
if(!$refreshComponentOnly){
	?><style type="text/css">
	/* generated with logical table generator 1.0 */
	table.data910{
		border-collapse:collapse;
		/*
		border-left:1px solid #ccc;
		border-right:1px solid #ccc;
		*/
		}
	.data910 td{
		/*border-bottom:1px dotted #333; */
		padding:1px 4px 0px 8px;
		cursor:pointer;
		}
	.data910 .alt{
		background-color:#eef;
		}
	.data910 th{
		text-align:left;
		}
	.data910 th{
		border:1px solid #fff;
		color:#fff;
		background-color:ORANGE;
		font-weight:400;
		padding:10px 4px 2px 8px;
		font-size:119%;
		}
	.data910 a{
		color:inherit;
		text-decoration:inherit;
		}
	h2.itemsList{
		font-size:119%;
		font-weight:400;
		}
	.data910 .tr{
		text-align:right;
		}
	</style><?php
}
if(!$pathToComponents)$pathToComponents='.';
?>
<div class="fr" style="width:75px;">
	<a title="Change settings for your items" onclick="return ow(this.href,'l1_dosettings','700,700');" tabindex="-1" href="settings_dataobject.php?object=finan_items&cbFunction=refreshComponent&storagemethod=module">Settings</a>
</div>
<div class="fr" style="width:125px;">
	<?php require($pathToComponents.'/components/comp_01_filtergadget_v103.php'); ?>
</div>

<h2 id="itemCount" class="itemsList"><?php echo count($ids) ? count($ids) : 'No'?> items showing</h2>
<table width="100%" border="0" cellspacing="0" cellpadding="0" id="itemsList" class="data910" summary="Lists items for e-commerce with category and subcategory, grouping and packages">
    <thead>
    <tr>
        <th id="hdr-ctrls" colspan="3">&nbsp;</th>
        <th id="hdr-category" <?php echo $sort=='category' ? 'class="sorted"':''?> scope="col"><a id="a-category" href="resources/bais_01_exe.php?mode=sortItems&sort=category&dir=<?php echo !$dir || ($sort=='category' && $dir=='-1') ? 1 : '-1'; ?>" title="Sort by function" onclick="return objectSort(this);">Function</a></th>
		<th id="hdr-lead" <?php echo $sort=='lead' ? 'class="sorted"':''?> scope="col"><a id="a-lead" href="resources/bais_01_exe.php?mode=sortItems&sort=lead&dir=<?php echo !$dir || ($sort=='lead' && $dir=='-1') ? 1 : '-1'; ?>" title="Sort by grouping" onclick="return objectSort(this);">Lead</a></th>
        <th id="hdr-model" <?php echo $sort=='model' ? 'class="sorted"':''?> scope="col"><a id="a-model" href="resources/bais_01_exe.php?mode=sortItems&sort=model&dir=<?php echo !$dir || ($sort=='model' && $dir=='-1') ? 1 : '-1'; ?>" title="Sort by Model Number" onclick="return objectSort(this);">Model</a></th>
        <th id="hdr-sku" <?php echo $sort=='sku' ? 'class="sorted"':''?> scope="col"><a id="a-sku" href="resources/bais_01_exe.php?mode=sortItems&sort=sku&dir=<?php echo !$dir || ($sort=='sku' && $dir=='-1') ? 1 : '-1'; ?>" title="Sort by Part Number (SKU)" onclick="return objectSort(this);">P/N</a></th>
        <th id="hdr-name" <?php echo $sort=='name' ? 'class="sorted"':''?> scope="col"><a id="a-name" href="resources/bais_01_exe.php?mode=sortItems&sort=name&dir=<?php echo !$dir || ($sort=='name' && $dir=='-1') ? 1 : '-1'; ?>" title="Sort by item name" onclick="return objectSort(this);">Name (Description) </a></th>
        <th id="hdr-wholesaleprice" <?php echo $sort=='wholesaleprice' ? 'class="sorted"':''?> nowrap="nowrap" scope="col"><a id="a-wholesaleprice" href="resources/bais_01_exe.php?mode=sortItems&sort=wholesaleprice&dir=<?php echo !$dir || ($sort=='wholesaleprice' && $dir=='-1') ? 1 : '-1'; ?>" title="Sort by Dealer Price" onclick="return objectSort(this);">Dealer Price</a></th>
        <th id="hdr-unitprice" <?php echo $sort=='unitprice' ? 'class="sorted"':''?> nowrap="nowrap" scope="col"><a id="a-unitprice" href="resources/bais_01_exe.php?mode=sortItems&sort=unitprice&dir=<?php echo !$dir || ($sort=='unitprice' && $dir=='-1') ? 1 : '-1'; ?>" title="Sort by Retail Price" onclick="return objectSort(this);">Retail Price</a></th>
        <th>&nbsp;<img src="/images/i/cols.gif" alt="cols" width="10" height="9" border="0" /></th>
        </tr>
    <thead>
    <tfoot class="footerClass">
    <tr>
        <td colspan="8"><a title="Add a new item" onclick="return ow(this.href,'l1_items','700,700',true);" href="items.php">Add new item</a></td>
    </tr>
    </tfoot>
    <tbody id="itemsListTbody" <?php if($browser=='Moz')echo 'style="overflow:scroll;height:350px;"';?>>
    <?php
    if(count($ids)){
        foreach($ids as $ID=>$null){
			if($a=q("SELECT
				a.ID,
				a.SKU,
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
				a.Weight
				FROM $MASTER_DATABASE.$dataobjectTable a LEFT JOIN $MASTER_DATABASE.finan_items_packages b ON a.ID=b.Items_ID
				LEFT JOIN $MASTER_DATABASE.finan_transactions c ON a.ID=c.Items_ID
				LEFT JOIN $MASTER_DATABASE.finan_ItemsItems d ON a.ID=d.ParentItems_ID
				WHERE a.ID=$ID
				GROUP BY a.ID
				ORDER BY a.Type, a.Category, a.SubCategory, a.Name", O_ROW)){
				//further filters here
	            extract($a);
			}else{
				continue;
			}
			
			$i++;
            ?><tr id="ri<?php echo $IsPackage?'p':($IsGrouped?'g':'')?>_<?php echo $ID?>" onClick="h(this,'itemsListDataObject',0,0,event);" onDblClick="h(this,'itemsListDataObject',0,0,event);defaultMenuOption(event)" oncontextmenu="h(this,'itemsListDataObject',0,1,event);" ispackage="<?php echo $IsPackage?>" isdeletable="<?php echo $IsDeletable?>" class="<?php echo !fmod($i,2)?' alt':''?>">
            <td><a title="Delete this item" href="resources/bais_01_exe.php?mode=deleteItem&Items_ID=<?php echo $ID?>" onClick="if(!confirm('This will permanently delete this item! Are you sure?'))return false; return ow(this.href,'w2','');" tabindex="-1"><img src="/images/i/<?php echo $IsDeletable?'del2.gif':'spacer.gif'?>" border="0" /></a></td>
            <td><a title="Edit this item" href="items.php?Items_ID=<?php echo $ID?>" onClick="return ow(this.href,'l1_items','700,700');"><img src="/images/i/edit2.gif" width="18" height="18" alt="edit" border="0" /></a></td>
            <td><img src="/images/i/<?php echo ($IsPackage=='2' ? 'package' : ($IsPackage=='1' ? 'package_empty':'spacer')) . '.gif'?>" width="18" height="18" alt="package" border="0" /></td>
            <td nowrap="nowrap"><?php echo h($Category)?></td>
            <td><?php echo $GroupLeader ? 'Y' : ''?></td>
            <td><?php echo h($Model)?></td>
            <td><?php echo h($SKU)?></td>
            <td style="width:275px;"><?php echo h($Name)?></td>
            <td class="tr"><?php echo number_format($WholesalePrice,2)?></td>
            <td class="tr"><?php echo number_format($UnitPrice,2)?></td>
			<td>&nbsp;&nbsp;</td>
            </tr>
            <?php
        }
    }
    ?>
    </tbody>
</table>
<?php
if(!$refreshComponentOnly){
	?><script type="text/javascript" language="javascript">
	hl_bg['itemsListDataObject']='SILVER';
	hl_txt['itemsListDataObject']='WHITE';
	//declare the ogrp.handle.sort value even if blank
	ogrp['itemsListDataObject']=new Array();
	ogrp['itemsListDataObject']['sort']='';
	ogrp['itemsListDataObject']['rowId']='';
	ogrp['itemsListDataObject']['highlightGroup']='itemsListDataObject';
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
		for(j in hl_grp['itemsListDataObject'])trid=j;
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
		for(j in hl_grp['itemsListDataObject'])trid=j;
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
