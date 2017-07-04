<?php
/***
Category Table
--------------
2012-05-29
* moved over to juliet exclusively
* now using reader and bona fide editor settings to modify this baby
* 

2011-08-14 forked over to 0.5 so for use with juliet's output buffering
	
2008-10-13 committed to a component for all sites
Horizontal Looper v2.0 - 2008-04-18
- this looper is record-driven vs. row-column driven.  A row-column driven looper would be much smaller codewise
***/

/*
following from pJ_getdata():
[sub]categoryPath, categoryBackupPath, categoryCols, 

*/

$path=($level=='subcategory'?$subcategoryPath:$categoryPath);


//main category image location
if(!is_dir($_SERVER['DOCUMENT_ROOT'].'/images/'.$path)){
	$a=explode('/',trim($path,'/'));
	$newFolder=$_SERVER['DOCUMENT_ROOT'].'/images';
	//duh.. build the folder if it's not present already
	foreach($a as $v){
		$newFolder.='/'.$v;
		if(is_dir($newFolder))continue;
		if(!mkdir($newFolder))echo 'unable to create new folder '.$newFolder;
	}
}
if(!$categoryImgs)$categoryImgs=get_file_assets($_SERVER['DOCUMENT_ROOT'].'/images/'.$path, array('positiveFilters'=>'\.(gif|jpg|png|svg)$'));
//layout
if(!$cols)$cols=($level=='subcategory'?$subcategoryCols:$categoryCols);
if(!$rows)$rows=10000;


if(!$startRow)$startRow=1;
if(!$startCol)$startCol=1;
//or you can specify this:
#$startPosition=1
if(!$maxRows)$maxRows=1000;



if(!$refreshComponentOnly){
	?><style type="text/css">
	table.loop1{
		width:100%;
		border-collapse:collapse;
		}
	.loop1 td.content{
		text-align:center;
		vertical-align:top;
		}
	.loop1 .content h2{
		font-weight:900;
		font-size:129%;
		color:#333;
		}
	</style>
	<?php
}

$records=q("SELECT i.Category, COUNT(*) AS Count, COUNT(DISTINCT i.SubCategory) AS Subs, c.Caption, c.Description 
FROM finan_items i LEFT JOIN finan_items_categories c ON i.Category=c.Name
WHERE i.Active=1 AND (i.Type='Non-inventory Part' OR i.Type IS NULL OR i.Type='') GROUP BY i.Category ORDER BY IF(c.Priority IS NULL, 1000, c.Priority), i.Category", O_ARRAY);
if($records){
	//prn($qr);
	//prn($records);
	?><table class="loop1"><?php
	if(!$startPosn)$startPosn=($cols * ($startRow-1)) + $startCol;
	$count=0;
	$col=0;
	$row=0;
	$cells=0;
	$startOffsetCells=0;
	$endOffsetCells=0;
	$count=0;
	while(list(,$v)=each($records)){
		//handle first row(s) and starting offset
		$count++;
		if($count==1 && $addRows = floor(($startPosn-1)/$cols)){
			//these are top offset rows, modify class and content as needed
			for($i=1; $i<=$addRows; $i++){
				$row++;
				$col=0;
				$vPosition=($i==1 ? 'top' : 'mid');
				?><tr><?php
					for($j=1; $j<=$cols; $j++){
						switch($j){
							case 1:
								$hPosition='left';
								break;
							case $cols:
								$hPosition='right';
								break;
							default:
								$hPosition='center';
						}
						$startOffsetCells++; //total number of blank cells
						$col++;
						?><td class="empty <?php echo $hPosition . ' '. $vPosition?>">&nbsp;</td><?php
					}
				?></tr><?php
			}
		}
		//begin a row
		if($col==0){
			$row++;
			/**
			this is inaccurate and needs logic to differentiate further between "mid" and bottom
			**/
			$vPosition=($row==1 ? 'top' : (count($records)-$cells-$startOffsetCells < $cols || $row>=$maxRows ? 'bottom' : 'mid'));
			?><tr><?php
		}
		//add initial padding cells
		if($count==1 && $startPad = ($startPosn - 1) % $cols  ){
			for($i=1; $i<=$startPad; $i++){
				switch(true){
					case $i==1:
						$hPosition='left';
						break;
					default:
						$hPosition='center';
				}
				$startOffsetCells++;
				$col++;
				?><td class="empty <?php echo $hPosition . ' ' . $vPosition?>">&nbsp;</td><?php
			}
		}
		//normal cells
		$col++;
		$cells++;
		$hPosition=($col==1 ? 'left' : ($col % $cols ==0 ? 'right' : 'mid'));
		
		//---------------------------- create the category description record ------------------------
		if($Categories_ID=q("SELECT ID FROM finan_items_categories WHERE Name='".addslashes($v['Category'])."'", O_VALUE)){
			//OK
		}else{
			//add it or we will not be able to sort
			$Categories_ID=q("INSERT INTO finan_items_categories SET CreateDate=NOW(), Creator='system', Name='".addslashes($v['Category'])."'", O_INSERTID);
		}

		?><td class="content <?php echo $hPosition . ' ' . $vPosition?>"><?php
		//------------------------------------ content here -------------------------------------------

		//get the number of products in this category && specials available
		$productSKUs=q("SELECT ID,SKU FROM finan_items i WHERE i.Active=1 AND (Type='Non-inventory Part' OR Type IS NULL OR Type='') AND Category='".addslashes($v['Category'])."'", O_COL_ASSOC);
		$SubCategoryCount=q("SELECT COUNT(DISTINCT SubCategory) FROM finan_items i WHERE i.Active=1 AND (Type='Non-inventory Part' OR Type IS NULL OR Type='') AND Category='".addslashes($v['Category'])."'", O_VALUE);
		$featured=q("SELECT COUNT(Featured) FROM finan_items i WHERE i.Active=1 AND (Type='Non-inventory Part' OR Type IS NULL OR Type='') AND Category='".addslashes($v['Category'])."' AND Featured=1", O_VALUE);
		
		
		$haveImage=$width=$height='';
		if($img=get_image($v['Category'],$categoryImgs)){
			$haveImage=$img['name'];
			$width=$img['width'];
			$height=$img['height'];
		}else if($productSKUs){
			foreach($productSKUs as $w){
				//get backup images
				if(!isset($categoryBackupImgs))$categoryBackupImgs=get_file_assets($_SERVER['DOCUMENT_ROOT'].'/images/'.$categoryBackupPath, array('positiveFilters'=>'\.(gif|jpg|png|svg)$'));
				if($img=get_image(strtolower($w), $categoryBackupImgs)){
					$width=$img['width'];
					$height=$img['height'];
					$haveImage=preg_replace('/[^a-z0-9]*/i','',($v['Category'])).'.'.$img['ext'];
					ob_start();
					$result=copy($_SERVER['DOCUMENT_ROOT'].'/images/'.$categoryBackupPath.'/'.$img['name'], $_SERVER['DOCUMENT_ROOT'].'/images/'.$path.'/'.$haveImage);
					$err=ob_get_contents();
					ob_end_clean();
					if($err){
						$email='Hi there,
						
						For category '.($v['Category']).', I was unable to copy picture /images/'.$categoryBackupPath.'/'.$img['name'].' to /images/'.$path.'/'.$haveImage.', here is the reply from the system:' . "\n". $err;
						$email.='

						Please forward this email to your site developer or point of contact.
						
						Sincerely,
						Compass Point Media Automated System
						
						
						
						[admin email comp_categorytable_03.php:02]';						
					}else{
						$email='Hi there,
						
						You did not have an image for the category "'.($v['Category']).'" on the category-listing page of your site '.$_SERVER['HTTP_HOST'].'.  A temporary image was found and copied: /images/'.$categoryBackupPath.'/'.$img['name'].' (item number '.$w.').  This picture may not be the right size and may not be the picture you really want to represent this category.
						
						To view File Explorer, go to:
						
						'.rtrim($siteURL,'/').'/admin/file_explorer/?uid=categoryfolder&folder='.$path.'&createFolder=1
						
						
						Sincerely,
						Compass Point Media Automated System
						[admin email comp_categorytable_03.php:01]';
					}
					mail($adminEmail,'Image '.($err?'unable to be ':'').'copied from products to category list for category "'.($v['Category']).'"',str_replace("\t",'',$email),$fromHdrBugs);
					break;
				}
			}
			if(!$haveImage){
				ob_start();
				print_r($productSKUs);
				$out=ob_get_contents();
				ob_end_clean();
				$out=preg_replace('/^Array\s*\(\s*/i','',trim($out));
				$out=preg_replace('/\s*\)\s*$/','',$out);
				$out=preg_replace('/ {2,}/','',$out);
				$out=str_replace("\t",'',$out);
				if(strlen($out)>200)$out=substr($out,0,200).' ... (more)';
				$email='Hi there,
				
				Your website is missing a category image for category *'.stripslashes($v['Category']).'*.  An attempt was made to use and copy a picture from products in this category (from folder '.$categoryBackupPath.') but no pictures were found for the products in this category.  Please sign in and upload a picture for this category.
				If you have pictures for products in this category, it is most likely due to the fact that your picture names do not match the SKU number (item number) of your products.  The products in this category were:
				
				'.$out.'
				
				Sincerely,
				Compass Point Media Automated System
				[admin email comp_categorytable_03.php:03]'."\n\n".get_globals();
				mail($developerEmail,'Image unable to be copied from products to category list for category "'.($v['Category']).'"',str_replace("\t",'',$email),$fromHdrBugs);
			}
		}

		//this works great for single.php - enhance its functionality for subcategory.php - use it for a featured product or one to bring to the top
		$singleProductID=q("SELECT ID FROM finan_items WHERE Category='".addslashes($v['Category'])."'", O_VALUE);
		?>
		<a href="/products/<?php echo count($productSKUs)==1?'focus': ($SubCategoryCount==1 ? 'list' : 'subcategory')?>?Category=<?php echo urlencode($v['Category']=='(uncategorized)' ? '' : $v['Category']);?>&SubCategory=<?php echo urlencode(q("SELECT DISTINCT SubCategory FROM finan_items i WHERE i.Active=1 AND (Type='Non-inventory Part' OR Type IS NULL OR Type='') AND Category='".addslashes($v['Category'])."'", O_VALUE));?>&ID=<?php echo $singleProductID;?>" title="See products for <?php echo stripslashes($v['Category'])?>"><?php
		//show product picture
		if($haveImage){
			$Tree_ID=tree_build_path('images/'.$path.'/'.$haveImage);
			$src='Tree_ID='.$Tree_ID.'&Key='.md5($Tree_ID.$MASTER_PASSWORD).'&disposition='.$categoryImgBoxWidth.'x'.$categoryImgBoxHeight.'&boxMethod='.$categoryImgBoxMethod;

			?><img alt="category image" src="/images/reader.php?<?php echo $src;?>" /><?php
		}else if($defaultNAImage && file_exists(rtrim($defaultNAImagePath,'/') . '/'.$defaultNAImage)){
			echo 'no image';
			
			?>
			<!--
			<img alt="category image unavailable" height="<?php echo $defaultNAImageHeight?>" width="<?php echo $defaultNAImageWidth?>" src="/<?php echo trim($defaultNAImagePath,'/') . '/'.$defaultNAImage?>" />
			-->
			<?php
		}else{
			//do nothing
			?>&nbsp;<?php
		}
		?>
		<h2><?php
		if($featured && !$hideFeaturedAvailable){
			?><span class="featuredAvailable"><img src="/images/i/star1.jpg" alt="Featured products available" /></span>&nbsp;<?php
		}
		echo $v['Category'];
		?></h2>
		</a>
		<?php
		if($adminMode){
			/*
			if(false && $allowCategoryRanking){
				?><div class="_editLink_1"><span class="ctrls">
				<img title="Move category UP (press the control key to move to ABSOLUTE top)" alt="move up" style="cursor:pointer" id="priority<?php echo $Categories_ID;?>+1" src="/images/i/red-up-toggle.jpg" onclick="setID2(<?php echo $Categories_ID;?>,event,1)" />
				<img title="Move category DOWN (press the control key to move to ABSOLUTE bottom)" alt="move down" style="cursor:pointer" id="priority<?php echo $Categories_ID;?>-1" src="/images/i/red-down-toggle.jpg" onclick="setID2(<?php echo $Categories_ID;?>,event,-1)" />
				</span>	Category Rank</div><?php
			}
			*/
			?><a class="_editLink_1" title="Edit images folder" onclick="return ow(this.href,'l1_subcategory','700,700');" href="/admin/file_explorer/?folder=category&uid=category"><img src="images/i/edit2.gif" alt="edit images" width="15" height="18">&nbsp;Edit Images</a><br><?php
		}
		//-----------------------------------------------------------------------------------------------------------
		?>
		</td>
		<?php
		//closing cells
		if($count==count($records) && !($cols==$col)){
			$lastCol=$col;
			for($i=1; $i<=($cols-$lastCol); $i++){
				switch($i){
					case $cols-$lastCol:
						$hPosition='right';
						break;
					default:
						$hPosition='mid';
				}
				$endOffsetCells++;
				$col++;
				?><td class="empty <?php echo $hPosition . ' '. $vPosition?>">&nbsp;</td><?php
			}
		}
		//end a row
		if( $col % $cols == 0){
			$col=0;
			?></tr><?php
			if($row>=$maxRows)break;
		}
	}
	?></table><?php
}else{
	?><h4>Currently, no items in this category</h4><?php
}
?>