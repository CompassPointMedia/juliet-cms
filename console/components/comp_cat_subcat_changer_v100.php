<style type="text/css">
.box{
	border-collapse:collapse;
	}
.box a{
	color:midnightblue;
	}
.box td{
	border:1px dotted darkred;
	padding:10px;
	}
.box img{
	padding:2px;
	border:1px solid gold;
	}
.img{
	text-align:center;
	}
.imgCtrls{
	float:left;
	width:25px;
	}
.deleteButton{
	background-color:darkred;
	color:white;
	}
.addButton{
	background-color:darkblue;
	color:white;
	}
.SKUList{
	margin-bottom:6px;
	}
.subthumb{
	float:left;border:1px dotted #666;padding:4px;max-width:150px;font-size:12px;color:#999;margin:0px 3px 2px 0px;
	}
.catsubcat{
	border-collapse:collapse;
	}
.catsubcat td{
	border:1px dotted #666;
	padding:5px;
	vertical-align:top;
	}
.catsubcat h2, .catsubcat h3{
	margin:0px;
	padding:0px;
	}
.showMore{
	cursor:pointer;
	}
</style>
<div class="fr">
<a href="/admin/file_explorer/?uid=category&folder=category" onclick="return ow(this.href,'l1_fex','700,700');">View Category Folder (/images/category)</a><br />
<a href="list_categories_changer.php">View Category/Subcategory Changer</a></div>
<h1>Category/Subcategory Changer</h1>

<form name="form1" id="form1" method="post" action="/resources/index_01_exe.php">

<?php
$a=q("SELECT Category, SubCategory, COUNT(DISTINCT Subcategory) AS Subcategories, COUNT(DISTINCT ID) AS Products FROM finan_items GROUP BY Category, SubCategory ORDER BY Category, SubCategory", O_ARRAY);
?>
<table class="catsubcat">
<thead>
<tr>
	<th>Category</th>
	<th>Subcategory</th>
	<th>Products</th>
</tr>
</thead>
<tbody>
<?php
foreach($a as $v){
	extract($v);
	?><tr>
	<td><?php
	if(!$Category)$Category='(none)';
	if(!$SubCategory)$SubCategory='(none)';
	
	if($buffer1!=$Category){
		$buffer1=$Category;
		$newCategory=true;
		?><input type="text" name="Category[<?php echo h($Category);?>]" value="<?php echo h($Category);?>" /><?php
	}else{
		$newCategory=false;
		?>&nbsp;<?php
	}
	?></td>
	<td><?php
	if($buffer2!=$SubCategory || $newCategory){
		$buffer2=$SubCategory;
		?><input type="text" name="SubCategory[<?php echo h($Category);?>][<?php echo h($SubCategory);?>]" value="<?php echo h($SubCategory);?>" /><?php
	}else{
		?>&nbsp;<?php
	}
	?></td>
	<td>
	<?php
	echo '<em class="gray">'. $Products.' item'.($Products>1?'s':'').'</em><br />';
	$b=q("SELECT ID, SKU FROM finan_items WHERE Category='".addslashes(str_replace('(none)','',$Category))."' AND SubCategory='".addslashes(str_replace('(none)','',$SubCategory))."' ORDER BY SKU", O_ARRAY);
	$i=0;
	foreach($b as $w){
		$i++;
		if($i==4){
			?><span class="showMore gray" onclick="this.nextSibling.style.display='inline';this.style.display='none';">show <?php echo $Products-3;?> more..</span><span style="display:none;"><?php
		}
		?><a href="items.php?Items_ID=<?php echo $w['ID'];?>" title="View this item" onclick="return ow(this.href,'l1_items','800,700');"><?php echo $w['SKU'];?></a><br />
		<?php
	} 
	if($i>3){
		?></span><?php
	}
	?>	</td>	
	</tr><?php
}
?>
</tbody>
</table>

<input name="mode" type="hidden" id="mode" value="catsubcatchanger" />
</form>

<input type="submit" name="Submit" value="Submit" />
<label><input name="refresh" type="checkbox" id="refresh" value="1" checked="checked" /> Reload changes</label>
