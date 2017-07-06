<?php if(!$refreshComponentOnly){ ?>
	<script type="text/javascript" language="javascript">
	hl_bg['pkgItemsGroup']='GOLD';
	hl_txt['pkgItemsGroup']='WHITE';
	//declare the ogrp.handle.sort value even if blank
	ogrp['pkgItemsGroup']=new Array();
	ogrp['pkgItemsGroup']['sort']='';
	ogrp['pkgItemsGroup']['rowId']='';
	ogrp['pkgItemsGroup']['highlightGroup']='pkgItemsGroup';
	function pkgItems(action){
		switch(action){
			case 'Open':
				alert('undeveloped');
			break;
			case 'EditPackageItem':
				alert('undeveloped');
			break;
			case 'RemoveFromPackage':
				alert('undeveloped');
			break;
			case 'DeleteItem':
				alert('undeveloped');
			break;
			case 'ViewinWebsite':
				alert('undeveloped');
			break;
		}
	}
	function removepackageitem(parent,child){
		if(g('packageItemCount').value<2 && !confirm('You are removing the last item from this package - packages do not show up on the site without AT LEAST ONE ITEM! Are you sure?')){
			return false;
		}else if(g('packageItemCount').value<3 && !confirm('This will leave only one item in the package, which may not display very well.  Are you sure?')){
			return false;
		}else if(!confirm('This will remove this item from this package (it will not delete the item).  Continue?'))return false;
		g('ip_'+child).style.display='none';
		window.open('resources/bais_01_exe.php?mode=removePackageItems&ParentItems_ID='+parent+'&Items_ID='+child,'w3');
		return false;
	}
	function mgPriceType(n){
		if(n=='No Price Change'){
			g('priceOrPercent').innerHTML='&nbsp;';
			g('PriceValue').disabled=true;
		}else if(n=='Specific Package Price'){
			g('priceOrPercent').innerHTML='$';
			g('PriceValue').disabled=false;
			g('PriceValue').focus();
		}else if(n=='Auto Discount'){
			g('priceOrPercent').innerHTML='%';
			g('PriceValue').disabled=false;
			g('PriceValue').focus();
		}
	}
	</script>
	<div id="pkgItemsOptions" class="menuskin1" style="z-index:1000;" onMouseOver="hlght2(event)" onMouseOut="llght2(event)" onClick="executemenuie5(event)" >
		<div id="pio1" class="menuitems" command="pkgItems('Open')" status="Open">Open</div>
		<div id="pio2" class="menuitems" command="pkgItems('EditPackageItem')" status="Edit Package Item">Edit Package Item</div>
		<div id="pio3" class="menuitems" command="pkgItems('RemoveFromPackage')" status="Remove From Package">Remove From Package</div>
		<hr class="mhr"/>
		<div id="pio4" class="menuitems" command="pkgItems('DeleteItem')" status="Delete Item">Delete Item</div>
		<hr class="mhr"/>
		<div id="pio5" class="menuitems" command="pkgItems('ViewinWebsite')" status="View in Website">View in Website</div>
	</div>
	<style type="text/css">
	#tablePkgItems{
		background-color:#fff;
		}
	#tablePkgItems th{
		background-color:cornsilk;
		}
	.yat td{
		border-bottom:1px solid #666;
		padding:3px 2px 1px 6px;
		}
	.yat th{
		padding:3px 2px 1px 6px;
		}
	.totals td{
		border-top:1px solid #666;
		}
	</style>
<?php } ?>
<!-- component object: pkgItems (<?php echo __FILE__?>) -->
<input type="hidden" name="ShowItemPicture" value="0" />
<label><input name="ShowItemPicture" type="checkbox" id="ShowItemPicture" value="1" <?php echo $ShowItemPicture || ($mode==$insertMode)?'checked':''?> onchange="dChge(this);" />
Use Package Picture when present</label>
<br />
<input name="HideLineItemPrices" type="hidden" value="0" />
<label>
<input name="HideLineItemPrices" type="checkbox" id="HideLineItemPrices" value="1" <?php echo $HideLineItemPrices=='1'?'checked':'';?> onchange="dChge(this);" /> 
Hide line item prices <span class="gray">(this will follow in the shopping cart)</span></label>
<br />
<input name="AutoUpdatePrice" type="hidden" value="0" />
<label>
<input name="AutoUpdatePrice" type="checkbox" id="AutoUpdatePrice" value="1" onchange="dChge(this);" <?php echo $AutoUpdatePrice?'checked':''?> /> Auto-update prices</label>
<br />
Overall Package Price: 
<select name="PricingType" id="PricingType" onchange="dChge(this);mgPriceType(this.value);">
	<option value="No Price Change">(No Price Change)</option>
	<option <?php echo strtolower($PricingType)=='specific package price'?'selected':''?> value="Specific Package Price">Specific Package Price</option>
	<option <?php echo strtolower($PricingType)=='auto discount'?'selected':''?> value="Auto Discount">Auto Discount</option>
</select>
<span id="priceOrPercent">&nbsp;</span> <input name="PriceValue" type="text" id="PriceValue" onChange="dChge(this);" value="<?php echo $PriceValue!=='0.00'?$PriceValue:''?>" size="8" <?php echo strtolower($PricingType)=='no price change' || !isset($PricingType) ? 'disabled':''?> /><br />
<br />
<script type="text/javascript">
if(false){
var sBasePath= '/Library/fckeditor4/';
var oFCKeditor = new FCKeditor('OverallLongDescription') ;
oFCKeditor.BasePath	= sBasePath ;
oFCKeditor.ToolbarSet = 'Transitional' ; //Transitional
oFCKeditor.Height = 120;
oFCKeditor.Config[ 'ToolbarLocation' ] = 'Out:xToolbar' ;
oFCKeditor.Value = '<?php
//output section text
$a=@explode("\n",$OverallLongDescription);
foreach($a as $n=>$v){
	$a[$n]=trim(str_replace("'","\'",$v));
}
echo implode('\n',$a);
?>';
oFCKeditor.Create() ;
}
</script>


<div id="pkgItems" refreshparams="ID"><table width="100%" border="0" cellspacing="0" cellpadding="0" id="tablePkgItems" class="yat">
    <thead>
    <tr>
        <th>&nbsp;</th>
        <th>Qty.</th>
        <th>Part#</th>
        <th>Idx</th>
        <th>Name</th>
        <th>Pricing</th>
        <th>Type</th>
        <th class="tac">Normal Price</th>
        <th class="tac">Sale Price </th>
    </tr>
    </thead>
    <tbody>
    <?php
    $a=q("SELECT a.*, b.*, c.Name, c.Description, c.Category, c.SubCategory, c.SKU, c.UnitPrice, c.UnitPrice2 FROM finan_items_packages a, finan_ItemsItems b, finan_items c WHERE a.Items_ID=b.ParentItems_ID AND b.ChildItems_ID=c.ID AND a.Items_ID='$ID' ORDER BY IF(BonusItem,2,1), Idx, c.SKU", O_ARRAY);
    if(count($a)){
		foreach($a as $ip)if($ip['BonusItem'])$hasBonus=true;
        foreach($a as $ip){
			if(!$hasBonus){
				//idle
			}else if($bonusRow<1){
				$bonusRow=1;
				?><tr><td colspan="102"><h3 class="nullBottom nullTop">Normal Items</h3></td></tr><?php
			}else if($ip['BonusItem'] && $bonusRow<2){
				$bonusRow=2;
				?><tr><td colspan="102"><h3 class="nullBottom nullTop">Bonus/Special Items</h3></td></tr><?php
			}
            ?><tr id="ip_<?php echo $ip['ChildItems_ID']?>" onclick="h(this,'pkgItemsGroup',0,0,event);" ondblclick="h(this,'pkgItemsGroup',0,0,event);defaultOptionSelect(event)" oncontextmenu="h(this,'pkgItemsGroup',0,1,event);">
            <td nowrap="nowrap">
			<a title="Remove this item from the package" href="#" onclick="return removepackageitem(<?php echo $ID?>,<?php echo $ip['ChildItems_ID']?>);" target="w3"><img src="/images/i/del2.gif" alt="remove" width="16" height="18" /></a>
			&nbsp;&nbsp;
			<a title="Edit this item" href="items.php?ParentItems_ID=<?php echo $ID?>&Items_ID=<?php echo $ip['ChildItems_ID']?>"><img src="/images/i/edit2.gif" alt="edit" width="15" height="18" /></a>			</td>
            <td><?php echo $ip['Quantity']?></td>
            <td><strong style="font-size:114%;"><?php echo $ip['SKU']?></strong></td>
            <td><?php echo $ip['Idx'];?></td>
            <td><?php
			echo $ip['Name'] . (strlen($ip['Description']) ? ' - ':'');
			$c=preg_split('/\s+/',$ip['Description']);
			for($i=1;$i<=15;$i++){
			   if(strlen($c[$i]))echo ' '.$c[$i];
			}
			if(count($c)>15)echo ' ...';
			?></td>
            <td class="tar"><?php
			if($ip['BonusItem']){
				if(strtolower($ip['PricingType'])=='free'){
					'FREE';
				}else if(strtolower($ip['PricingType'])=='price'){
					echo number_format($ip['PriceValue'],2);
				}else if(strtolower($ip['PricingType'])=='percent'){
					echo number_format($ip['PriceValue'],1);
				}
			}else{
				//calculate off of main package data
				
			}
			
			?></td>
            <td class="tar"><?php
			if(strtolower($ip['PricingType'])=='free'){
				echo '&nbsp;';
			}else if(strtolower($ip['PricingType'])=='price'){
				echo '<span style="font-weight:900;font-size:129%;">$</span>';
			}else if(strtolower($ip['PricingType'])=='percent'){
				echo '<span style="font-weight:900;font-size:129%;">%</span>';
			}
			?></td>
            <td class="tar"><?php
			if($ip['UnitPrice']!=0){
				echo number_format($ip['UnitPrice'],2);
				$totals['UnitPrice']+=$ip['UnitPrice'];
			}else{
				//calculate off of main package data
				echo '&nbsp;';
			}
			?></td>
            <td class="tar"><?php
			if($ip['UnitPrice2']!=0){
				echo number_format($ip['UnitPrice2'],2);
				$totals['UnitPrice2']+=$ip['UnitPrice2'];
			}else{
				//calculate off of main package data
				echo '&nbsp;';
			}
			?></td></tr><?php
        }
		?><tr class="totals">
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td class="tar">&nbsp;</td>
		<td class="tar"><?php echo number_format($totals['UnitPrice'],2);?></td>
		<td class="tar"><?php echo number_format($totals['UnitPrice2'],2);?></td>
		</tr><?php
    }
    ?>
    </tbody>
    <tfoot>
    <tr>
        <td colspan="9"><?php
		if($mode==$insertMode){
			?><h3>You must first save this package before you can items to it</h3><?php
		}else{
			?>
			[<a title="Select an existing item or product and add it to this package" href="items_select.php?ParentItems_ID=<?php echo $ID?>&cbFunction=refreshComponent&cbParam=fixed:pkgItems&PackagePart=1" onclick="return ow(this.href,'l1_items','700,700');">Select Item</a>] | 
			[<a title="Create a new item and add it to this package" href="items.php?ParentItems_ID=<?php echo $ID?>&cbFunction=refreshComponent&cbParam=fixed:pkgItems&PackagePart=1" onclick="return ow(this.href,'l1_items','700,700',true);">Create New Item</a>]
			<?php
		}
		?></td>
    </tr>
    </tfoot>
</table>
	
<input name="packageItemCount" type="hidden" id="packageItemCount" value="<?php echo count($a)?>" />
</div>