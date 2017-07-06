<?php

if($mode=='componentControls'){
	if($mode=='updateCategorySubcategory'){
		foreach($Category as $n=>$v){
			if($Category[$n] != $OriginalCategory[$n]){
				if(q("UPDATE finan_items SET Category = '".$Category[$n]."' WHERE Category = '".$OriginalCategory[$n]."'")){
					error_alert($OriginalCategory[$n] ." is being updated to " .$Category[$n]);
				}
			}
		}
	}else if($mode=='updateProductName'){
		foreach($Name as $n=>$v){
			if($Name[$n] != $OriginalName[$n] || $Sku[$n]!=$OriginalSku[$n] || $Category[$n]!=$OriginalCategory[$n] || $SubCategory[$n]!=$OriginalSubCategory[$n]){
				q("UPDATE finan_items SET Name = '".$Name[$n]."',
				Category='".$Category[$n]."',
				SubCategory='".$SubCategory[$n]."',
				Sku='".$Sku[$n]."'
				WHERE ID='".$ID[$n]."'");
				$updates++;
			}
		}
		if($updates)error_alert('Total of '.$updates.' performed');
	}
}

if(isset($_REQUEST['Category']) && isset($_REQUEST['SubCategory'])){
	?>
    <?php
		$url  = isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http';
		if($url)
		{
			$url .= '://' . $_SERVER['SERVER_NAME'];
			$url .= in_array( $_SERVER['SERVER_PORT'], array('80', '443') ) ? '' : ':' . $_SERVER['SERVER_PORT'];
			$url .= $_SERVER['REQUEST_URI'];
			$url_parsed = parse_url($url);
			#print_r($url_parsed);
			parse_str($url_parsed['query'], $url_parts);
		}
	?>
	Select a Category/Subcategory combination: <select>
	<?php 
	$track = 0;
	$combos=q("SELECT DISTINCT Category, SubCategory FROM finan_items ORDER BY Category, SubCategory", O_ARRAY);
	foreach($combos as $n=>$v){
		if($v['Category'] == $url_parts['Category'] && $v['SubCategory'] == $url_parts['SubCategory'])
		{
			$track++;
			?>
			<option value="<?php echo "selected=\"selected\" ";?>"><?php echo $v['Category']; ?>-><?php echo $v['SubCategory']; ?></option>
		<?php }
		else {?>
        <option value="<?php echo '';?>"><?php echo $v['Category']; ?>-><?php echo $v['SubCategory']; ?></option>
        <?php
		} }?>
        <?php
		if($track == 1)
		{ 				?>
			<option value="<?php echo "selected=\"selected\" ";?>"><?php echo $v['Category']; ?>-><?php echo $v['SubCategory']; ?></option>
		<?php } 
		?>
	</select>
	<?php
	//focus view	
		$query1=q("SELECT ID,Sku,Name,Category,SubCategory
		FROM finan_items
		WHERE Category = '".$url_parts['Category']."' and SubCategory = '".$url_parts['SubCategory']."'",O_ARRAY);
	?>
	
	<table>
	<tr>
		<th>Sku</th>
		<th>Name</th>
		<th>Category</th>
		<th>SubCategory</th>
	</tr>
	<?php
	foreach($query1 as $n=>$v){
		?>
	<tr>
		<td>
			<input name="Sku[]" value="<?php echo $v['Sku'];?>" type="text" size="20" />
			<input name="OriginalSku[]" value="<?php echo $v['Sku'];?>" type="hidden" />
			<input name="ID[]" value="<?php echo $v['ID'];?>" type="hidden" />
		</td>
		<td>
			<input name="Name[]" value="<?php echo $v['Name'];?>" type="text" size="50" />
			<input name="OriginalName[]" value="<?php echo $v['Name'];?>" type="hidden" />
		</td>
		<td>
			<input name="Category[]" value="<?php echo $v['Category'];?>" type="text" size="20" />
			<input name="OriginalCategory[]" value="<?php echo $v['Category'];?>" type="hidden" />
		</td>	
		<td>
			<input name="SubCategory[]" value="<?php echo $v['SubCategory'];?>" type="text" size="20" />
			<input name="OriginalSubCategory[]" value="<?php echo $v['SubCategory'];?>" type="hidden" />  
		</td>	
	</tr>
	<?php } ?>
	</table>
	
	<input type="submit" name="Submit" value="Update" />
	<input name="mode" type="hidden" id="mode" value="updateProductName" />
	<?php
}else{
	//list view
	?><form action="/console/resources/bais_01_exe.php" method="post" name="form1" target="w2" id="form1">
	<?php
	$query1=q("SELECT Category,SubCategory,count(*) as noofproducts
	FROM finan_items
	GROUP BY category,subcategory
	ORDER BY category,subcategory",O_ARRAY);
	?>
	<table>
	<tr>
		<th>Category</th>
		<th>SubCategory</th>
		<th>Nubmer of Products</th>
	</tr>
	<?php
	$i=0;
	$track2= '';
	foreach($query1 as $n=>$v){
		$i++;
		$track1 = $v['Category'];
		#$track2 = $track1;
		?><tr>
			<td>
			<?php if($track1 != $track2){ 
				 $track2 = $track1; ?>
				<input name="Category[]" value="<?php echo $v['Category'];?>" type="text" size="20" />
				<input name="OriginalCategory[]" value="<?php echo $v['Category'];?>" type="hidden" />
			 <?php } ?>			</td>
			<td>
			<input name="SubCategory[]" value="<?php echo $v['SubCategory'];?>" type="text" size="20" />
			<input name="OriginalSubCategory[]" value="<?php echo $v['SubCategory'];?>" type="hidden" />            </td>
			<td><a onclick="return ow(this.href,'l1_focus','800,700');" href="/console/items_categories_focus.php?Category=<?php echo $v['Category'] ?>&SubCategory=<?php echo $v['SubCategory'] ?>" title="click to view <?php echo $v['Category'].':'.$v['SubCategory'];?>"><?php echo $v['noofproducts'];?></a></td>		
		</tr><?php
	}
	?></table>
	<input type="submit" name="Submit" value="Update" />
	<input name="mode" type="hidden" id="mode" value="componentControls" />
	<input name="file" type="hidden" value="comp_catsubcat_v100.php" />
	<input name="location" type="hidden" value="CONSOLE_COMPONENT_ROOT" />
	<input name="submode" type="hidden" id="submode" value="updateCategorySubcategory" />
	</form>	
	<?php
}
?>