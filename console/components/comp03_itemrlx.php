<style type="text/css">
#parentChildRlx{
	border:1px dotted DARKRED;
	padding:10px 20px;
	margin:5px 125px;
	clear:both;
	}
.pdisabled{
	color: #555;
	}
.pdisabled input{
	color:#555;
	}
.normal{
	color: inherit;
	}
.pdisabled input{
	color:inherit;
	}
</style>
<div id="parentChildRlx">
<?php
$p=q("SELECT * FROM finan_items WHERE ID=$ParentItems_ID", O_ROW);
$link=q("SELECT * FROM finan_ItemsItems WHERE ParentItems_ID=$ParentItems_ID AND ChildItems_ID=$Items_ID", O_ROW);
?>
<h3><img src="../images/i/package.gif" alt="package" width="15" height="14" />&nbsp;Package Info - <?php echo $p['SKU']?></h3>
<?php echo h($p['Name'])?>
<br />
Quantity: 
<select name="Quantity" id="Quantity" onChange="dChge(this);"><?php
for($i=1;$i<=24;$i++){
	?><option value="<?php echo $i?>" <?php echo $link['Quantity']==$i?'selected':''?>><?php echo $i?></option><?php
}
?>
</select>
<input name="ParentItems_ID" type="hidden" id="ParentItems_ID" value="<?php echo $ParentItems_ID?>">
<br>
<label><input type="checkbox" name="BonusItem" id="BonusItem" value="1" onChange="dChge(this);" <?php echo $link['BonusItem']?'checked':''?> onclick="mgBonusItem(this.checked)" /> 
Bonus Item</label>
<div id="BonusItemFields" class="<?php echo $link['BonusItem']?'normal':'pdisabled'?>">
Price this item as: 
<select name="PricingType" id="PricingType" onChange="dChge(this);mgPriceType(this.value);">
	<option value="Free" <?php echo $link['PricingType']=='Free'?'selected':''?>>Free</option>
	<option value="Price" <?php echo $link['PricingType']=='Price'?'selected':''?>>Price</option>
	<option value="Percent" <?php echo $link['PricingType']=='Percent'?'selected':''?>>Percent</option>
</select>
<span id="priceOrPercent">&nbsp;</span> <input name="PriceValue" type="text" id="PriceValue" onChange="dChge(this);" value="<?php echo $link['PriceValue']!=='0.00'?$link['PriceValue']:''?>" size="8" <?php echo $link['PricingType']=='Free' || !isset($link['PricingType'])?'disabled':''?> />
</div>
<script language="javascript" type="text/javascript">
function mgPriceType(n){
	if(n=='Price'){
		g('priceOrPercent').innerHTML='$';
		g('PriceValue').disabled=false;
		g('PriceValue').focus();
	}else if(n=='Percent'){
		g('priceOrPercent').innerHTML='%';
		g('PriceValue').disabled=false;
		g('PriceValue').focus();
	}else{
		g('priceOrPercent').innerHTML='&nbsp;';
		g('PriceValue').value='';
		g('PriceValue').disabled=true;
	}
	PriceValueState=g('PriceValue').disabled;
}
//store initial state
PriceValueState=g('PriceValue').disabled;
if(g('BonusItem').checked==false){
	g('PricingType').disabled=true;
	g('PriceValue').disabled=true;
}
function mgBonusItem(l){
	g('BonusItemFields').className=(l?'normal':'pdisabled');
	if(l){
		g('PricingType').disabled=false;
		g('PriceValue').disabled=PriceValueState;
		if(!g('PriceValue').disabled)g('PriceValue').focus();
	}else{
		g('PricingType').disabled=true;
		g('PriceValue').disabled=true;
	}
}
</script>
</div>