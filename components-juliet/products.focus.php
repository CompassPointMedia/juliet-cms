<?php
//Single Product Page
if(false){ //-----------------------
$prodImgFolder=get_file_assets('images/products/large','large');
$rdp=q("SELECT i.*, COUNT(DISTINCT t.ID) AS Used FROM finan_items i LEFT JOIN finan_transactions t ON i.ID=t.Items_ID WHERE i.Active=1 AND i.ID=$ID GROUP BY i.ID", O_ROW);
?>
<h2 class="prodLead">
<a title="Main list of products" href="<?php echo $pageHandles['categoryPage']?>">All Products</a>
<?php
if($Category){ 
?>
&raquo; <a title="Return to <?php echo stripslashes($Category)?>" href="<?php echo $pageHandles['subCategoryPage'];?>?Category=<?php echo urlencode(stripslashes($Category))?>"><?php echo stripslashes($Category)?></a><?php
}
if($SubCategory!='' && $SubCategory!=$Category){
?>
&raquo; <a title="All items in subcategory for this product" href="<?php echo $pageHandles['productsPage'];?>?Category=<?php echo urlencode(stripslashes($Category))?>&SubCategory=<?php echo urlencode(stripslashes($SubCategory))?>"><?php echo stripslashes($SubCategory);?></a>
<?php
}
?>
<?php if(!$Category && !$SubCategory){ ?>
&raquo; <?php echo $printedNameAbove=$rdp['Name'];?>
<?php }?> 
</h2>
<?php
$useProductDescriptions=3;
$prodImgFolder=get_file_assets('images/products/large','large');
$prodMainImgPath='images/products/large/';
#prn($prodImgFolder);
$showMoreInfoButton=false;
$relatedItemsHeading='Popular Related Items';
require($_SERVER['DOCUMENT_ROOT'].'/components/ecom_flex1_005juliet.php');
?>

<div id="prod0"></div>
<div id="added0" class="addedStatus" style="visibility:hidden;"></div>
<script language="javascript" type="text/javascript">
var currentProducts = new Array(0<?php if(count($currentProducts))echo ','. implode(',',$currentProducts)?>);
</script>
<?php
} //--------------------------------

if($links_id && $SubCategory){
	?><h2><a href="/products/8011?get=1&links_id=<?php echo $links_id?>"><?php echo h(q("SELECT Name FROM gen_links WHERE ID='$links_id'", O_VALUE))?></a> � <a href="/products/8010?links_id=<?php echo $links_id?>&SubCategory=<?php echo urlencode(stripslashes($SubCategory))?>"><?php echo h(stripslashes($SubCategory))?></a></h2><?php
}else if($Keywords){
	?><h2>Search � <a href="/products/8010?Keywords=<?php echo  urlencode($Keywords)?>"><?php echo h(stripslashes($Keywords))?></a></h2><?php
}else if($_GET[$quickJumpField1] || $_GET[$quickJumpField2]){
	$fieldValue=$_GET[$quickJumpField1] . $_GET[$quickJumpField2];
	$fieldName=($_GET[$quickJumpField1] ? $quickJumpField1 : $quickJumpField2);
	?><h2>By <?php echo h($fieldName)?> � <?php echo h(stripslashes($fieldValue))?></h2><?php
}else{
	if($Category || $SubCategory){
		//OK, clean up
		$Category=stripslashes($Category);
		$SubCategory=stripslashes($SubCategory);
	}else{
		extract(q("SELECT Category, SubCategory FROM finan_items WHERE ID='$ID'", O_ROW));
	}
	?><h2>
	<a href="/products/main" title="View all products">All Products</a>
	<?php
	if($Category){
		?> &raquo; <a href="/products/subcategory?Category=<?php echo urlencode($Category);?>" title="View this category"><?php echo $Category;?></a><?php
	}
	if($SubCategory){
		?> &raquo; <a href="/products/list?Category=<?php echo urlencode($Category);?>&SubCategory=<?php echo urlencode($SubCategory);?>" title="View this category"><?php echo $SubCategory;?></a><?php
	}
	?></h2><?php
}
$prodImgPath='/images/products/large';
$prodImgArray=get_file_assets($_SERVER['DOCUMENT_ROOT'].$prodImgPath);
$rdp=q("SELECT * FROM finan_items 
WHERE 
ID = '$ID' AND 
Active=1  AND 
( RWB='B' OR RWB='".($_SESSION['cnx'][$MASTER_DATABASE]['wholesaleAccess']?'W':'R')."')",O_ROW);
$useProductDescriptions=3;
$prodMainImgPath='/images/products/large/';
$prodSlideClickMessage='Click thumbnails to the right for more views of the item';
$showMoreInfoButton=false;
$relatedItemsHeading='Popular Related Items';
$prodSlidesUseThumbs=true;
$prodSlidesUseExtraLarge=true;
$prodLimitImageWidth=400;
$prodOutputOrder=array('prodWrap','prodName','prodImgPanelGallery','prodImgPanelLarge','prodModel','prodSKU','prodDescription','prodPriceData','prodMoreInfo','prodDimensions','prodQty','prodAdd','prodAdded','prodAdminMode','prodPackageWording','prodCaption',
'prodRelatedItems','prodPackageData','prodWrapEnd');
require($_SERVER['DOCUMENT_ROOT'].'/components/ecom_flex1_005juliet.php');
?>
<div id="prod0"></div>
<div id="added0" class="addedStatus" style="visibility:hidden;"></div>
<script>
var currentProducts=[0<?php if(count($currentProducts))echo ','. implode(',',$currentProducts)?>];
</script>