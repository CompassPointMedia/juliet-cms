<?php
function prod_url_evaluator($options=array()){
	/* 
	2012-08-22
	products/..
		1. citrus/all				category=citrus (show list view regardless of subcategory count) { ?sort=SubCategory implemented }
		2. citrus/tangerines		category=citrus & subcategory=tangerines
		3. citrus					category=citrus (show EITHER subcategory view, or list view if one subcategory)
		4. TANG-HS					id=ID[that SKU]
	
	*/
	extract($options);
	global $qr, $thisfolder, $thissubfolder, $thispage, $thisnode, $adminMode;
	
	switch(true){
		case strlen($thissubfolder) && strtolower($thispage)=='all' && $a=q("SELECT * FROM finan_items WHERE REPLACE(Category,'-',' ')='".addslashes(str_replace('-',' ',stripslashes($thissubfolder)))."'".($adminMode?'':' AND Active>0'), O_ARRAY):
			$Category=$thissubfolder;
			$page='list';
		break;
		case strlen($thissubfolder) && $a=q("SELECT * FROM finan_items WHERE REPLACE(Category,'-',' ')='".addslashes(str_replace('-',' ',stripslashes($thissubfolder)))."'".($thispage!='index'?" AND REPLACE(SubCategory,'-',' ')='".addslashes(str_replace('-',' ',stripslashes($thispage)))."'":'').($adminMode?'':' AND Active>0'), O_ARRAY):
			$Category=$thissubfolder;
			$SubCategory=($thispage!='index'?$thispage:'');
			$page=(q("SELECT COUNT(DISTINCT SubCategory) FROM finan_items WHERE (Type='' OR Type LIKE '%inventory%') AND Category='".addslashes($thissubfolder)."'".($adminMode?'':' AND Active>0'),O_VALUE)>1 ? 'subcategory' : 'list');
		break;
		case $a=q("SELECT * FROM finan_items WHERE REPLACE(Category,'-',' ')='".addslashes(str_replace('-',' ',stripslashes($thispage)))."'".($adminMode?'':' AND Active>0'), O_ARRAY):
			$Category=$a[1]['Category'];
			$page='list';
		break;
		case $a=q("SELECT * FROM finan_items WHERE SKU='".addslashes($thispage)."'".($adminMode ? '' : " AND Active>0"), O_ARRAY):
			$Category=$a[1]['Category'];
			$SubCategory=$a[1]['SubCategory'];
			$page='focus';
			$a=$a[1];
		break;
		case $a=q("SELECT * FROM finan_items WHERE SKU='".addslashes($thispage)."' OR REPLACE(Name,'-',' ')='".addslashes(str_replace('-',' ',stripslashes($thispage)))."'".($adminMode ? '' : " AND Active>0"), O_ARRAY):
			error_alert('undeveloped');
		break;
		default: return;
		
	}
	return array(
		'Category'=>$Category,
		'SubCategory'=>$SubCategory,
		'page'=>$page,
		'productListing'=>$a,
	);
}

/* name=Ecommerce Component; description=Created after more than components on 2012-04-03; */

/*
TO DO:
2012-08-05:
* "upload a better image" - based on the size requested vs. the size image we have

2012-08-01:
	* bread crumbs, All Products > Athletic Wear needs to be a {general::breadcrumbs} so that it can be referenced in CMSB but also so it can be placed by other components or etc. in various places

2012-08-05:
* cleaned up code a lot and moved to ecom_flex1_006juliet.php which uses get_contents_enhanced() for clarity

2012-06-03:
	* added $componentVersion
2012-04-03:
so starting with Encha ntedGar dens I have a bunch of images which loosely matches not the SKU but the name, and I want to present all the beautiful pictures heather took.  So I'm starting from scratch in many cases with an eye to assist Don and Annamarie.  There are several concepts I want to implement:
	* sometimes we have no product but we have images! As long as I know the folder I can product something
	* if I type in cyclamen - there are 9+ instances of this scattered across several manufacturers - show the products and show the pictures as best as possible
		if(i have a product that has an image and is "SET UP"){
			//show it
		}else{
			//do this search
		}
	* is products/groundcover a category OR subcategory? show it
	* there is great data in the signs that I want to pull in - show it 
	

*/
$handle='products';
$componentVersion=2.0;

require_once($FUNCTION_ROOT.'/function_get_image_v220.php');

//2012-03-10: pull parameters for this component file - note that this is in gen_nodes_settings.Settings vs. gen_templates_blocks.Parameters
if($Parameters=q("SELECT Settings FROM gen_nodes_settings WHERE Nodes_ID='".($_thisnode_ ? $_thisnode_ : $thisnode) ."'", O_VALUE)){
	$Parameters=unserialize(base64_decode($Parameters));
	if($Parameters[$handle])$pJ['componentFiles'][$handle]=$Parameters[$handle];
	/* nodes include: forms; data; format.  forms is unused right now, and data[default] means "across all pages" and is the only part developed */
}
//2012-03-21: -------------- porpoise code block -------------- 
/* added to synchronize with RelateBase */
foreach($consoleEmbeddedModules as $v){
	if($v['SKU']=='040'){
		if($a=$v['moduleAdminSettings']['settable_parameters']){
			foreach($a as $o=>$w){
				if(is_array($w)){
					if(preg_match('/^([a-z0-9_]+)\[([a-z0-9_]*)\]$/i',$o,$m)){
						$setParams[$handle][$m[1]][$m[2]]=$w[0];
						if($w['extract'])$GLOBALS[$m[1]][$m[2]]=$w[0];
					}else{
						$setParams[$handle][$o]=$w[0];
						if($w['extract'])$GLOBALS[$o]=$w[0];
					}
				}else{
					$GLOBALS[$o]=$w;
				}
			}
		}
	}else if($v['SKU']=='CGI-70'){
		$usemod=$v['moduleAdminSettings']['usemod'];
	}
}
//--------------------------- end porpoise code block ----------------------------------

//default variables
$categoryPath=pJ_getdata('categoryPath','category');
$categoryBackupPath=pJ_getdata('categoryBackupPath','products/thumb');
$categoryCols=pJ_getdata('categoryCols','3');
$categoryImgBoxWidth=pJ_getdata('categoryImgBoxWidth',175);
$categoryImgBoxHeight=pJ_getdata('categoryImgBoxHeight',175);
$categoryImgBoxMethod=pJ_getdata('categoryImgBoxMethod',2);

$subcategoryPath=pJ_getdata('subcategoryPath','subcategory');
$subcategoryBackupPath=pJ_getdata('subcategoryBackupPath','products/thumb');
$subcategoryCols=pJ_getdata('subcategoryCols','3');
$subcategoryImgBoxWidth=pJ_getdata('subcategoryImgBoxWidth',175);
$subcategoryImgBoxHeight=pJ_getdata('subcategoryImgBoxHeight',175);
$subcategoryImgBoxMethod=pJ_getdata('subcategoryImgBoxMethod',2);

$pageCategorySubcategory=pJ_getdata('pageCategorySubcategory');

$postAddDisplay=pJ_getdata('postAddDisplay','modalCartList');

$prodHideBreadCrumbs=pJ_getdata('prodHideBreadCrumbs');
$prodOutputOrderList=pJ_getdata('prodOutputOrderList');

//2012-08-05: list and focus image constraints for large and thumb images
$prodListImageDispositionLarge=pJ_getdata('prodListImageDispositionLarge','250x250');
$prodListImageBoxMethodLarge=pJ_getdata('prodListImageBoxMethodLarge',4);

#note: no gallery
$prodFocusImageDispositionLarge=pJ_getdata('prodFocusImageDispositionLarge','400x400');
$prodFocusImageBoxMethodLarge=pJ_getdata('prodFocusImageBoxMethodLarge',4);
$prodFocusImageDispositionThumb=pJ_getdata('prodFocusImageDispositionThumb','95x95');
$prodFocusImageBoxMethodThumb=pJ_getdata('prodFocusImageBoxMethodThumb',2);

$prodCategoryDisplayOrder=pJ_getdata('prodCategoryDisplayOrder','title,caption,description,image');
$prodSubcategoryDisplayOrder=pJ_getdata('prodSubcategoryDisplayOrder','title,caption,description,image');
$prodOutputOrderFocus=pJ_getdata('prodOutputOrderFocus','prodWrap,prodName,prodImgPanelLarge,prodImgPanelGallery,prodModel,prodSKU,prodDescription,prodPriceData,prodMoreInfo,prodDimensions,prodQty,prodAdd,prodAdded,prodAdminMode,prodPackageWording,prodCaption,prodRelatedItems,prodPackageData,prodWrapEnd,prodModalCartList');
if(!isset($prodShowDimensions))$prodShowDimensions=pJ_getdata('prodShowDimensions',true);

//default CSS
get_contents_enhanced('start'); ?>
<?php if(false){ ?><style type="text/css"><?php } ?>
.prodName{
	font-size:16px;
	font-weight:700;
	padding:10px 10px 10px 0;
	}
.prodCaption{
	margin:0px 0px 0px 25px;
	font-size:12px;
	font-weight:400;
	font-style:italic;
	}
.prodSKU{
	}
.prodDescription{
	font-weight:600;
	}
.on{
	float:left;
	margin:0px 5px 0px 0px;
	}
.off{
	float:left;
	margin:0px 5px 0px 0px;
	}
.prodPriceData{
	margin:10px 0px;
	font-size:14px;
	}
.prodAdminModeCtrl{
	position:absolute;
	right:0px;
	top:0px;
	opacity:.75;
	filter:alpha(opacity=75);
	border:1px solid #666;
	}
.prodAdminMode{
	position:absolute;
	right:0px;
	top:30px;
	opacity:.75;
	filter:alpha(opacity=75);
	border:1px solid #666;
	background-color:#fff;
	width:250px;
	height:250px;
	}
<?php if(false){ ?></style><?php } ?>
<?php
$n=get_contents_enhanced('noecho,cxlnextbuffer');
$pJLocalCSS[$handle.'-baseCSS']=
$prodDefaultCSS=pJ_getdata('prodDefaultCSS',$n);


//for local css links in head of document
if(false)$pJLocalCSSLinks[$handle]='/site-local/somefile.css';

//catalog_columns
$pageHandles['categoryPage']='main';
$pageHandles['subCategoryPage']='subcategory';
$pageHandles['productsPage']='list';
$pageHandles['singlePage']='focus';
//this is an environment setting, not stored
$wholesale=($_SESSION['cnx'][$acct]['wholesaleAccess']>=8 && $usemod['wholesaleToken']);
//this could be a setting
$qx['useRemediation']=true;


//default CSS
//this is a new method of having default CSS- should be modified quickly as this default css is going to evolve
if(false){ ?><div style="display:none;"><style type="text/css"><?php } 
ob_start();?>
#mainRegionCenterContentInset{
	display:none;
	}
table.loop1{
	/* width:570px; */
	border-collapse:collapse;
	}
.loop1 td.content{
	text-align:center;
	vertical-align:top;
	border:1px solid #EEE;
	}
.loop1 .content h2{
	/*
	font-weight:900;
	font-size:129%;
	color:#333;
	*/
	}
.prodAdminModeCtrl{
	position:absolute;
	right:0px;
	top:0px;
	float:right;
	}
.prodAdminModeCtrl a{
	display:block;
	opacity:.75;
	filter:alpha(opacity=75);
	padding:4px;
	background-color:darkgray;
	}
.prodAdminModeCtrl a:hover{
	opacity:1.0;
	filter:alpha(opacity=100);
	}
.prodAdminMode{
	position:absolute;
	right:0px;
	top:17px;
	opacity:.75;
	filter:alpha(opacity=75);
	border:1px solid #666;
	background-color:#fff;
	padding:15px;
	width:250px;
	height:250px;
	color:black;
	}
.prodAdminMode a{
	color:midnightblue;
	}

.prodImgPanel{
	float:right;
	margin:0px 0px 5px 12px;
	/* background-color:gold; */
	}
.prodPresentation{
	clear:both;
	}

table.data1{
	border-collapse:collapse;
	}
.data1 td{
	border:none;
	}

.horizontalLooper{
	}
.horizontalLooper td{
	padding:8px;
	}

.prodSlideControl{
	float:right;
	border:1px solid gold;
	margin:0px 0px 15px 15px;
	padding:10px;
	}
.prodSlideControl img{
	margin-bottom:5px;
	}
<?php if(false){ ?></style></div><?php }
$productsDefaultCSS=trim(ob_get_contents());
ob_end_clean();
//$pJLocalCSS[$handle]=pJ_getdata('productsDefaultCSS',$productsDefaultCSS);

for($__i__=1; $__i__<=1; $__i__++){ //---------------- begin i break loop ---------------

if($mode=='componentControls'){
	if($submode=='addcart' || $submode=='addcartAPI'){
		if(is_array($ID)){
			//we could do better than this
			foreach($ID as $n=>$v){
				if(!strlen($v)) error_alert('Please select all of the features for the product you selected');
			}
		}
		if(is_array($SKU_SUFFIX)){
			$SKU_SUFFIX='-'.implode('-',$SKU_SUFFIX);
		}else if(isset($SKU_SUFFIX)){
			$SKU_SUFFIX='-'.$SKU_SUFFIX;
		}
		if(isset($Description_PREFIX))$Description_PREFIX.=' - ';
		if(!$qty)$qty=1;
		for($i=1; $i<=$qty; $i++){
			shopping_cart($ID, 1, $options=array(
				/* courses must each be taken by individuals */
				'combineItems'=>false
			));
		}
		if(count($_SESSION['shopCart']['default']))
		foreach($_SESSION['shopCart']['default'] as $n=>$v){
			$total+=$v['Quantity'];
		}
		if($mode!=='addcartAPI'){
			?><script language="javascript" type="text/javascript">
			//change courses in cart value
			try{
			window.parent.g('orderCount').innerHTML='<?php echo $total?>';
			}catch(e){ }
			try{
			window.parent.g('added<?php echo $ID?>').style.visibility='visible';
			}catch(e){ }
			</script><?php
		}
		if($postAddDisplay=='modalCartList'){
			?>
			<div id="fill" style="border:1px solid #000;">
			  <p>Your Order</p>
			  <p class="gray">Take a moment to review your order; you can remove an item from your order by clicking &quot;remove&quot; on the left hand side. When you are finished, click Finished/Check Out, or Close to continue shopping.</p>
			  <?php
			  if(count($_SESSION['shopCart'])){
			  	?><table>
				<thead>
				<tr>
					<th>&nbsp;</th>
					<th>SKU</th>
					<th>Item</th>
					<th>Cost</th>
				</tr>
				</thead>
				<tbody>
				<?php
				$sum=0;
				foreach($_SESSION['shopCart']['default'] as $n=>$v){
					?><tr id="r_<?php echo $n;?>">
					<td>[<a href="/index_01_exe.php?location=JULIET_COMPONENT_ROOT&file=products.php&mode=componentControls&submode=deleteFromCart&idx=<?php echo $n;?>" target="w2">remove</a>]</td>
					<td><?php echo $v['SKU'];?></td>
					<td><?php echo $v['Name'];?></td>
					<td class="tar"><?php $sum+=$v['RetailPrice'];
					echo number_format($v['RetailPrice'],2);?></td>
					</tr><?php
				}
				?>
				<tr>
					<td colspan="3">&nbsp;</td>
					<td id="orderTotal"><?php echo number_format($sum,2);?>
				</tr>
				</tbody>
				</table><?php
			  }
			  ?>
			  <input type="button" name="Button" value="View/Check Out" onclick="window.location='<?php echo $shoppingCartURL;?>';" />
			  <input type="button" name="Button" value="Close" onclick="return deselect();" />
			</div>
			
			<script language="javascript" type="text/javascript">
			//alert(document.getElementById('fill').innerHTML);
			window.parent.g('modalCartList').innerHTML=document.getElementById('fill').innerHTML;
			window.parent.step1();
			</script>
			<?php
		}
	}else if($submode=='deleteFromCart'){
		unset($_SESSION['shopCart']['default'][$idx]);
		$sum=0;
		foreach($_SESSION['shopCart']['default'] as $v)$sum+=$v['RetailPrice'];
		?><script language="javascript" type="text/javascript">
		try{
		window.parent.g('r_<?php echo $idx?>').style.display='none';
		window.parent.g('orderTotal').innerHTML='<?php echo number_format($sum,2);?>';
		}catch(e){ }
		try{
		window.parent.g('orderCount').innerHTML='<?php echo count($_SESSION['shopCart']['default']);?>';
		}catch(e){ }
		</script><?php
	}
	break;
}
if($mode=='componentEditor'){
	//be sure and fulfill null checkbox fields
	/*
	2012-03-12: this is universal code which should be updated on ALL components.  The objective is that 
	
	*/
	
	if(!$default['prodHideBreadCrumbs'])$default['prodHideBreadCrumbs']='0';
	
	if($_thisnode_){
		/* ----------  this is a single-page, cross-block settings update ---------------  */
		!is_array($pJ['componentFiles'][$handle]) ? $pJ['componentFiles'][$handle]=array() : '';
		//now integrate the form post
		$pJ['componentFiles'][$handle]['data'][$formNode]=stripslashes_deep($_POST[$formNode]);
		$Parameters[$handle]['data'][$formNode]=$pJ['componentFiles'][$handle]['data'][$formNode];
	
		//unlike gen_templates_blocks, place as part of a larger array
		if($a=q("SELECT * FROM gen_nodes_settings WHERE Nodes_ID='$_thisnode_'", O_ROW)){
			//OK
		}else{
			q("INSERT INTO gen_ncdes_settings SET Nodes_ID='$_thisnode_', EditDate=NOW()");
		}
		q("UPDATE gen_nodes_settings SET Settings='".base64_encode(serialize($Parameters))."' WHERE Nodes_ID='$_thisnode_'");
		prn($qr);
	}else{
		error_alert(__LINE__);
		/* ----------  this is a cross-page, single-block settings update ---------------  */
		if($Parameters=q("SELECT Parameters FROM gen_templates_blocks WHERE Templates_ID=$Templates_ID AND Name='$pJCurrentContentRegion'", O_VALUE)){
			$a=unserialize(base64_decode($Parameters));
		}else{
			$a=array();
		}
		!is_array($pJ['componentFiles'][$handle]) ? $pJ['componentFiles'][$handle]=array() : '';
		foreach($a as $n=>$v){
			$pJ['componentFiles'][$handle][$n]=$v;
		}
		//now integrate the form post
		$pJ['componentFiles'][$handle]['data'][$formNode]=stripslashes_deep($_POST[$formNode]);
		q("UPDATE gen_templates_blocks SET Parameters='".base64_encode(serialize($pJ['componentFiles'][$handle]))."' WHERE Templates_ID='$Templates_ID' AND Name='$pJCurrentContentRegion'");
		prn($qr);
	}
	break;
}else if($formNode=='default' /* ok this is something many component files will contain */){
	error_alert('test');
	if(!$_thisnode_){
		//create a virtual page for now
		mail($developerEmail, 'Warning in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err='in products.php, a virtual node was created to handle calls from pages like /products/JTI-Tools  or  /products/main, where we need some place to store settings'),$fromHdrBugs);
		if($_thisnode_=q("SELECT ID FROM gen_nodes WHERE Name='{generic_ecommerce_placeholder}'", O_VALUE)){
			//OK
		}else{
			$_thisnode_=q("INSERT INTO gen_nodes SET Active=8, Name='{generic_ecommerce_placeholder}, Name='Generic Settings', Type='Object', CreateDate=NOW(), EditDate=NOW()",O_INSERTID);
			q("INSERT INTO gen_nodes_settings SET Nodes_ID=$_thisnode_");
		}
	}
	$nodeGroup=q("SELECT * FROM gen_nodes WHERE ID=$_thisnode_", O_ROW);
	?><h3 style="font-weight:400;color:black;">Page Group: <strong style="color:darkgreen;"><?php echo $nodeGroup['Name'];?></strong></h3>
	<p class="gray">
	NOTE: this form will go through massive change till a page group can be separately stored and be styled pretty much any way, including the layouts on Columbia.com and PacSun.com.
	</p><?php
	?><p>Products Display Main Setting</p>
	
	<h3>Main (Categories)</h3>
	<p>Category images folder:<em class="gray">(where category images are located)</em><br />
	<a href="/admin/file_explorer/?uid=<?php echo $handle;?>&folder=" onclick="return ow(this.href+g('default[categoryPath]').value,'l1_fex','700,700');" title="View with FEX"><img src="/images/i/fex104/bootleg_ms_folder.gif" width="19" align="absbottom" /></a> /images/
	<input name="default[categoryPath]" type="text" id="default[categoryPath]" value="<?php echo $categoryPath;?>" size="30" onchange="dChge(this);" />
	<br />
	Category images backup folder:<em class="gray">(this may be deprecated once all images are dynamic to a product)</em><br />
	<a href="/admin/file_explorer/?uid=<?php echo $handle;?>&folder=" onclick="return ow(this.href+g('default[categoryBackupPath]').value,'l1_fex','700,700');" title="View with FEX"><img src="/images/i/fex104/bootleg_ms_folder.gif" width="19" align="absbottom" /></a> /images/
	<input name="default[categoryBackupPath]" type="text" id="default[categoryBackupPath]" value="<?php echo $categoryBackupPath;?>" size="30" onchange="dChge(this);" />
	<br />
	Columns of main products categories: 
	<select name="default[categoryCols]" id="default[categoryCols]" onchange="dChge(this);">
	<?php 
	for($i=1;$i<=7;$i++){
		?>
		<option value="<?php echo $i;?>" <?php echo $i==$categoryCols?'selected':''?>><?php echo $i?></option>
		<?php
	}
	?>
	</select>
	<br />
	Category picture box width: 
	<input name="default[categoryImgBoxWidth]" type="text" id="default[categoryImgBoxWidth]" value="<?php echo $categoryImgBoxWidth;?>" size="4" onchange="dChge(this);" /> 
	<em class="gray">(integer value)</em>	<br />
	Category picture box height: 
	<input name="default[categoryImgBoxHeight]" type="text" id="default[categoryImgBoxHeight]" value="<?php echo $categoryImgBoxHeight;?>" size="4" onchange="dChge(this);" />
	<em class="gray">(integer value)</em><br />
	box method:
	<select name="default[categoryImgBoxMethod]" id="default[categoryImgBoxMethod]" onchange="dChge(this);">
		<option value="2" <?php echo $categoryImgBoxMethod==2?'selected':''?>>2-wall box (some cropping may occur)</option>
		<option value="4" <?php echo $categoryImgBoxMethod==4?'selected':''?>>4-wall box (shrink image within both dimensions above)</option>
	</select>
	<br />
	vertical layout of category content in each box: 
	<input name="default[prodCategoryDisplayOrder]" type="text" id="default[prodCategoryDisplayOrder]" value="<?php echo $prodCategoryDisplayOrder;?>" size="45" onchange="dChge(this);" /> <span class="gray">Separate by commas. Values are: title,caption,description,image</span><br />
	</p>
	<h3>Subcategories</h3>
	<p>Subcategory images folder:<em class="gray">(where subcategory images are located)</em><br />
	<a href="/admin/file_explorer/?uid=<?php echo $handle;?>&amp;folder=" onclick="return ow(this.href+g('default[categoryPath]').value,'l1_fex','700,700');" title="View with FEX"><img src="/images/i/fex104/bootleg_ms_folder.gif" width="19" align="absbottom" /></a> /images/
	<input name="default[subcategoryPath]" type="text" id="default[subcategoryPath]" value="<?php echo $subcategoryPath;?>" size="30" onchange="dChge(this);" />
	<br />
	Subcategory images backup folder:<em class="gray">(this may be deprecated once all images are dynamic to a product)</em><br />
	<a href="/admin/file_explorer/?uid=<?php echo $handle;?>&folder=" onclick="return ow(this.href+g('default[subcategoryBackupPath]').value,'l1_fex','700,700');" title="View with FEX"><img src="/images/i/fex104/bootleg_ms_folder.gif" width="19" align="absbottom" /></a> /images/
	<input name="default[subcategoryBackupPath]" type="text" id="default[subcategoryBackupPath]" value="<?php echo $subcategoryBackupPath;?>" size="30" onchange="dChge(this);" />
	<br />
	Columns of main products subcategories:
	<select name="default[subcategoryCols]" id="default[subcategoryCols]" onchange="dChge(this);">
		<?php 
		for($i=1;$i<=7;$i++){
		?>
		<option value="<?php echo $i;?>" <?php echo $i==$subcategoryCols?'selected':''?>><?php echo $i?></option>
		<?php
		}
		?>
	</select>
	<br />
	Subcategory picture box width:
	<input name="default[subcategoryImgBoxWidth]" type="text" id="default[subcategoryImgBoxWidth]" value="<?php echo $subcategoryImgBoxWidth;?>" size="4" onchange="dChge(this);" />
	<em class="gray">(integer value)</em> <br />
	Subcategory picture box height:
	<input name="default[subcategoryImgBoxHeight]" type="text" id="default[subcategoryImgBoxHeight]" value="<?php echo $subcategoryImgBoxHeight;?>" size="4" onchange="dChge(this);" />
	<em class="gray">(integer value)</em><br />
	box method:
	<select name="default[subcategoryImgBoxMethod]" id="default[subcategoryImgBoxMethod]" onchange="dChge(this);">
		<option value="2" <?php echo $subcategoryImgBoxMethod==2?'selected':''?>>2-wall box (some cropping may occur)</option>
		<option value="4" <?php echo $subcategoryImgBoxMethod==4?'selected':''?>>4-wall box (shrink image within both dimensions above)</option>
	</select>
	<br />
	<input name="default[prodSubcategoryDisplayOrder]" type="text" id="default[prodSubcategoryDisplayOrder]" value="<?php echo $prodSubcategoryDisplayOrder;?>" size="45" onchange="dChge(this);" /> <span class="gray">Separate by commas. Values are: title,caption,description,image</span><br />
	<br />
	<br />
	If this page lists a single set of products, select the Category:SubCategory combination:<br /> 
	<select name="default[pageCategorySubcategory]" id="default[pageCategorySubcategory]" onchange="dChge(this);">
		<option value="">&lt;Select..&gt;</option>
		<?php
		if($a=q("SELECT DISTINCT Category, SubCategory FROM finan_items WHERE ResourceType IS NOT NULL AND Category!='' ORDER BY Category, SubCategory", O_COL_ASSOC))
		foreach($a as $n=>$v){
			?><option value="<?php echo h($n.':'.$v);?>" <?php echo $pageCategorySubcategory==$n.':'.$v ? 'selected': ''?>><?php echo h($n . ' > '.$v);?></option><?php
		}
		?>
	</select>
	<br />
	<label><input type="checkbox" name="default[prodHideBreadCrumbs]" value="1" <?php echo $prodHideBreadCrumbs?'checked':''?> /> Hide bread crumbs in heading </label>
	<h3>Product List</h3>
	<p>Representative image box size: 
	<input name="default[prodListImageDispositionLarge]" type="text" id="default[prodListImageDispositionLarge]" value="<?php echo $prodListImageDispositionLarge;?>" size="10" onchange="dChge(this);" /> 
	<span class="gray">(example: 250x250 = width x height)</span><br />
	box method: 
	<select name="default[prodListImageBoxMethodLarge]" id="default[prodListImageBoxMethodLarge]" onchange="dChge(this);">
		<option value="2" <?php echo $prodListImageBoxMethodLarge==2?'selected':''?>>2-wall box (some cropping may occur)</option>
		<option value="4" <?php echo $prodListImageBoxMethodLarge==4?'selected':''?>>4-wall box (shrink image within both dimensions above)</option>
	</select>
	<br />
	<span class="gray">(note: thumbnails are not currently set up for Product List view, only on <a href="_juliet_.editor.php?<?php echo str_replace('formNode=default','formNode=focus',$QUERY_STRING)?>">Focus View</a>)</span>
	<br />
	<br />
	How to indicate an item has been added to order:
	<select name="default[postAddDisplay]" id="default[postAddDisplay]" onchange="dChge(this);">
		<option value="">(default method)</option>
		<option value="modalCartList" <?php echo $postAddDisplay=='modalCartList'?'selected':''?>>Pop-up shopping cart</option>
	</select>
	<br />
	<br />
	CSS for Entire Page Group:<br />
	<span class="gray">
	this should include css for category, subcategory, list and focus modes.  Note that pages have a body class added by those names respectively.  </span><br />
	<textarea name="default[prodDefaultCSS]" cols="55" rows="5" id="default[prodDefaultCSS]" onchange="dChge(this);"><?php echo h($prodDefaultCSS);?></textarea>
	<br />
	<script language="javascript" type="text/javascript">
	function showBlocks(){ g('showBlocksList').style.display='block'; return false; }
	</script>
	List Block Output Order (Advanced):<br />
	<span class="gray">List block elements in the order they should appear, separated by a comma.  Nesting is not currently allowed. <a href="#" onclick="return showBlocks();">Click here to see a list of block elements</a><br />
	<pre id="showBlocksList" style="display:none;border:1px dotted #333;padding:10px;">
	<em>Block Elements as of 8/5/2012:</em>
	
	prodWrap[open]
	prodPackageWording
	prodImgPanel
	prodImgPanelLarge
	prodImgPanelGallery
	prodName
	prodCaption
	prodModel
	prodSKU
	prodDescription
	prodRelatedItems
	prodPackageData
	prodPriceData
	prodQty
	prodMoreInfo
	prodAdd
	prodAdded
	prodAdminMode
	prodWrapEnd[close]
	</pre>
	<textarea name="default[prodOutputOrderList]" cols="45" rows="3" id="default[prodOutputOrderList]" onchange="dChge(this);"><?php echo h($prodOutputOrderList);?></textarea>
	</p>
	<?php
	break;
}else if($formNode=='focus'){
	?>
	<h3>Product Focus</h3>
	Focus View Block Output Order (Advanced):<br />
	<span class="gray">List block elements in the order they should appear, separated by a comma.  Nesting is not currently allowed. <a href="javascript:g('showBlocksList').style.display='block';">Click here to see a list of block elements</a><br />
	<pre id="showBlocksList" style="display:none;">
	<em>Block Elements as of 8/5/2012:</em>
	
	prodWrap[open]
	prodPackageWording
	prodImgPanel
		prodImgPanelLarge
		prodImgPanelGallery
	prodName
		prodCaption
	prodModel
	prodSKU
	prodDescription
	prodRelatedItems
	prodPackageData
	prodPriceData
	prodQty
	prodMoreInfo
	prodAdd
	prodAdded
	prodAdminMode
	prodWrapEnd[close]
	</pre>
	<textarea name="focus[prodOutputOrderFocus]" cols="45" rows="3" id="focus[prodOutputOrderFocus]" onchange="dChge(this);"><?php echo h($prodOutputOrderFocus);?></textarea>
	<br />
	Large image panel box size:
    <input name="focus[prodFocusImageDispositionLarge]" type="text" id="focus[prodFocusImageDispositionLarge]" value="<?php echo $prodFocusImageDispositionLarge;?>" size="10" onchange="dChge(this);" />
    <span class="gray">(example: 450x450 = width x height)</span><br />
box method:
	<select name="focus[prodFocusImageBoxMethodLarge]" id="focus[prodFocusImageBoxMethodLarge]" onchange="dChge(this);">
	  <option value="2" <?php echo $prodFocusImageBoxMethodLarge==2?'selected':''?>>2-wall box (some cropping may occur)</option>
	  <option value="4" <?php echo $prodFocusImageBoxMethodLarge==4?'selected':''?>>4-wall box (shrink image within both dimensions above)</option>
	</select>
	<br />
	Large image panel box size:
	<input name="focus[prodFocusImageDispositionThumb]" type="text" id="focus[prodFocusImageDispositionThumb]" value="<?php echo $prodFocusImageDispositionThumb;?>" size="10" onchange="dChge(this);" />
	<span class="gray">(example: 95x95 = width x height)</span><br />
	box method: 
	<select name="focus[prodFocusImageBoxMethodThumb]" id="focus[prodFocusImageBoxMethodThumb]" onchange="dChge(this);">
	  <option value="2" <?php echo $prodFocusImageBoxMethodThumb==2?'selected':''?>>2-wall box (some cropping may occur)</option>
	  <option value="4" <?php echo $prodFocusImageBoxMethodThumb==4?'selected':''?>>4-wall box (shrink image within both dimensions above)</option>
	</select>
	<br />
	<p>&nbsp;</p>
	<?php
	break;
}

$pJCurrentContentRegion=pJ_getdata('pJCurrentContentRegion','mainRegionCenterContent');

//------------- sample region $sampleBlock ---------
$block=pJ_getdata('productsMainContent','mainRegionCenterContent');
ob_start();

//call to edit
pJ_call_edit(array(
	'level'=>ADMIN_MODE_DESIGNER,
	'location'=>'JULIET_COMPONENT_ROOT',
	'file'=>end(explode('/',__FILE__)),
	'thisnode'=>($thisnode?$thisnode:NULL),
	'label'=>'Edit products page group',
));
if($thispage=='focus' || $pJDerivedThispage=='focus'){
	echo '&nbsp;&nbsp;';
	pJ_call_edit(array(
		'level'=>ADMIN_MODE_DESIGNER,
		'location'=>'JULIET_COMPONENT_ROOT',
		'file'=>end(explode('/',__FILE__)),
		'thisnode'=>($thisnode?$thisnode:NULL),
		'label'=>'Single Product Settings..',
		'formNode'=>'focus',
	));
}
if(!$refreshComponentOnly){
	?>
	<style type="text/css">
	a.selected{
		background-color:#1F75CC;
		color:white;
		z-index:100;
		}
	.messagepop{
		background-color:#FFFFFF;
		border:1px solid #999999;
		cursor:default;
		display:none;
		margin-top: 15px;
		position:absolute;
		text-align:left;
		width:394px;
		z-index:50;
		padding: 25px 25px 20px;
		}
	.messagepop p, .messagepop.div {
		border-bottom: 1px solid #EFEFEF;
		margin: 8px 0;
		padding-bottom: 8px;
		}
	</style>
	<script language="javascript" type="text/javascript">
	function deselect() {
		$("#modalCartList").slideFadeToggle(function(){});
		return false;
	}
	function step1(){
		$("#modalCartList").slideFadeToggle(function() {
			$("#email").focus();
		});
		return false;	
	}
	$.fn.slideFadeToggle = function(easing, callback) {
		return this.animate({ opacity: 'toggle', height: 'toggle' }, "fast", easing, callback);
	};
	<?php echo $productsJS;?>
	</script>
	<?php
}
?>
<span id="products" class="<?php echo $pJDerivedThispage ? $pJDerivedThispage : $thispage?>"><?php

$flows=array('main','subcategory','list','focus','search','pbsproducts','summary','catalog');
if(!in_array($pJDerivedThisPage,$flows) && !in_array($thispage,$flows)){
	//time to do an analysis of what we're going to need to present
	//mostly for MEG/cpm171
	if($thissubfolder){
		//not developed
	} /* else */
	if(false){
		if($a=q("SELECT
		i.*, 
		IF(Category='$thispage',$catMax, IF(Category LIKE '%$thispage%' OR '%$thispage%' LIKE 
		FROM finan_items i WHERE i.Active=1 AND i.ResourceToken IS NOT NULL AND 
		(
		i.SKU LIKE '%$thispage%' OR '%$thispage%' LIKE i.SKU OR
		i.Category LIKE '%$thispage%' OR '%$thispage%' LIKE i.Category OR
		i.SubCategory LIKE '%$thispage%' OR  '%$thispage%' LIKE i.SubCategory OR
		i.Name LIKE '%$thispage%' OR '%$thispage%' LIKE i.Name
		)
		", O_ARRAY)){
			
		}else{
			//NOTHING available for that
		}
	}
}
if(floor($pJDerivedThispage/1000)==8){
	mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($notice='old style ecommerce page value e.g. 8001'),$fromHdrBugs);
	$prodKeyTranslators=array('8001'=>'main');
	$pJDerivedThispage=$prodKeyTranslators[$pJDerivedThispage];
	if(!$pJDerivedThispage)exit('unable to identify ecommerce component, developer notified');
}
if($thisnode){
	$name=q("SELECT Name FROM gen_nodes WHERE ID=$thisnode", O_VALUE);
	$rootURL='/'.strtolower(str_replace(' ','-',$name));
}else{
	$rootURL='/products/'.$pageHandles['categoryPage'];
}
switch(true){
	case $pJDerivedThispage=='subcategory':
	case $thispage=='subcategory':
		$categoryDisposition='SubCategory';
		$categoryPath=$subcategoryPath;
		$categoryBackupPath=$subcategoryBackupPath;
		$categoryImgBoxWidth=$subcategoryImgBoxWidth;
		$categoryImgBoxHeight=$subcategoryImgBoxHeight;
	case $pJDerivedThispage=='main':
	case $thispage=='main':
		//Product Category Hub
		if(!$categoryDisposition)$categoryDisposition='Category';
		$hideFeaturedAvailable=true;
		require_once(str_replace('products.php','products.categories.php',__FILE__));
	break;
	case $pJDerivedThispage=='list' || $pJDerivedThispage==8003:
	case $thispage=='list' || $thispage==8003:
		require_once(str_replace('products.php','products.list.php',__FILE__));
		$headRegionTitle=$Category.($Category && $SubCategory?', ':'').$SubCategory;
	break;
	case $pJDerivedThispage=='focus':
	case $thispage=='focus':
		//focus product page coding
		require_once(str_replace('products.php','products.focus.php',str_replace($acct.'.','',__FILE__)));
		$headRegionTitle=$rdp['Name'];
	break;
	case $pJDerivedThispage=='search':
	case $thispage=='search':
		//Basic Search
	break;
	case $pJDerivedThispage=='pbsproducts':
	case $thispage=='pbsproducts': /* was 8010 */
		//Old products.php page from Paci fic Ba sin/Rus ch
		/** Bread Crumbs
		OK, here we go with another item, currently as of 2006-08-25 this page is receiving the following queries:
		
		1. Search:
			Search � (friendly) Textiles
		[2. Candle Sculpture is for summary.php]
		3. Candle Sculpture � Aquatic Luminary
		4. recognized jumpField : Quick Link � Animals Bird

		**/

		$baseWhere = "WHERE Active=1  AND ( RWB='B' OR RWB='".($_SESSION['cnx'][$MASTER_DATABASE]['wholesaleAccess']?'W':'R')."')";
		if($links_id && $SubCategory){
			//this is requests from the thumbnails on summary.php, i.e. combination of a link id AND a specific SubCategory
			$rd=q("SELECT Name AS LinkName, Clause, OrderBy, ImageLocation, TextContent FROM gen_links WHERE ID='$links_id'" ,O_ROW);
			@extract($rd);
			$sql="SELECT * FROM finan_items $baseWhere AND ".preg_replace('/^\s*WHERE\s+/i','',$Clause)." AND SubCategory='$SubCategory' ORDER BY ".(trim($OrderBy)? preg_replace('/^\s*ORDER BY\s+/i','',$OrderBy) : 'Priority, SKU ASC');
			?>
			<h2 class="lib_r02_s01"><a href="/products/8011?get=1&links_id=<?php echo $links_id?>"><?php echo htmlentities($rd['LinkName'])?></a> � <?php echo htmlentities(stripslashes($SubCategory))?></h2>
			<?php echo htmlentities($rd['TextContent']) . (trim($rd['TextContent']) ? '<br />' : '')?>
			<?php
		}elseif($Keywords){
			$sql="SELECT * FROM finan_items $baseWhere AND CONCAT(SKU, Keywords, Theme, Function, Category, SubCategory, Description, LongDescription) like '%" . addslashes($Keywords) . "%' ORDER BY Priority, SKU ASC";
			?>
			<h2 class="lib_r02_s01">Search � <?php echo htmlentities(stripslashes($Keywords))?></h2>
			<?php echo htmlentities($rd['TextContent']) . (trim($rd['TextContent']) ? '<br />' : '')?>
			<?php
		}else if($_GET[$quickJumpField1] || $_GET[$quickJumpField2]){
			//2006-06-14: this is hard-coded; function goes to select distinct sub-function, same for theme - no way to break this currently
			$fieldValue=$_GET[$quickJumpField1] . $_GET[$quickJumpField2];
			$fieldName=($_GET[$quickJumpField1] ? $quickJumpField1 : $quickJumpField2);
			$sql="SELECT * FROM finan_items $baseWhere AND $fieldName='" . $fieldValue . "' ORDER BY Priority, SKU ASC";
			?>
			<h2 class="lib_r02_s01">By <?php echo htmlentities($fieldName)?> � <?php echo htmlentities(stripslashes($fieldValue))?></h2>
			<?php echo htmlentities($rd['TextContent']) . (trim($rd['TextContent']) ? '<br />' : '')?>
			<?php
		}else{
			mail($developer,'Error in file '.__FILE__.' line '.__LINE__,'products.php did not get info to create a query',$fromHdrBugs);
			?><script language="javascript" type="text/javascript">
			setTimeout('window.location="index.php";', 5000);
			</script>
			<h3>There has been an unexpected error.  If you just added an item to your cart, it WAS added.  Now redirecting to home page.</h3>
			<?php
			exit;
		}
		$cols=1;
		$rows=1000;
		$startRow=1;
		$startCol=1;
		//or you can specify this:
		#$startPosition=1
		$maxRows=1000;
		
		//prelims
		if($cols < 1 || !is_int($cols)) exit('Col must be an integer value greater than zero');
		if(!$startPosn)$startPosn=($cols * ($startRow-1)) + $startCol;
		$count=0;
		
		if($records=q($sql, O_ARRAY)){
			?>
			<div class="lib_r02_s02"> Total of <?php echo count($records);?> matching <?php echo 'item'.(count($records)>1 ? 's. ':'. ');?> </div>
			<br />
			<a href="products_print.php?<?php echo $QUERY_STRING;?>" target="print"> <strong>Click here for a printer-friendly view</strong> </a><br />
			<br />
			<!-- Click here to <a href="mailto:?subject=<?php echo htmlentities($siteName)?> Catalog&amp;body=Here's a link you should check out!      http://209.128.113.83/products.php?<?php echo urlencode($QUERY_STRING);?>"> 
			email this exact page to friend</a> <br /> -->
			<?php
			if(count($records) >24){
				?>(over 24 items may take a few minutes to load)<?php 
				flush();
			}
			/*
			if($_SESSION['Login']){
				?><form action="Library/email_page.php" method="post" name="form3" target="email" id="form3" style="margin:0;">
				<input type="hidden" name="page" value="<?php echo $siteDomain . $PHP_SELF . '?'  .  htmlentities(stripslashes( $QUERY_STRING . '&override=edirrevo'));?>" />
				<input type="submit" name="Submit2" value="Click here to Email this page" />
				</form><?php
			}
			*/
			?>
			<table class="data1">
			<?php
			/***  Horizontal Looper v1.0 - 2006-09-01 - this looper is record-driven vs. row-column driven.  A row-column driven looper would be much smaller codewise ***/
			$col=0;
			$row=0;
			$cells=0;
			$startOffsetCells=0;
			$endOffsetCells=0;
			$count=0;
			while(list(,$rd)=each($records)){
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
										$hPosition='mid';
								}
								$startOffsetCells++; //total number of blank cells
								$col++;
								?><td>&nbsp;</td><?php
							}
						?></tr> <?php
					}
				}
				//begin a row
				if( $col==0 ){
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
								$hPosition='mid';
						}
						$startOffsetCells++;
						$col++;
						?><td>&nbsp;</td><?php
					}
				}
				//normal cells
				$col++;
				$cells++;
				$hPosition=($col==1 ? 'left' : ($col % $cols ==0 ? 'right' : 'mid'));
				?>
				<td valign="top"><?php /** content goes here **/ 
					?>
					<a name="r<?php echo $rd['ID']?>" id="r<?php echo $rd['ID']?>"></a>
					<?php
					if(count($_SESSION['shopCart']) && $addCart==1 && $rd['ID']==$ID){
						?><div style="background-color:#9c946b;padding:0px 12px 5px 12px;margin-bottom:10px;">
						<h3>This item has been added to your order.</h3>
						Your order now has a total of
						<?php
						$q=0;
						foreach($_SESSION['shopCart'] as $n=>$v){
							$v[1]>0 ? $q+=$v[1] : '';
						}
						echo $q . ' item' . ($q>1 ? 's' : '');
						?> items in it. <a href="<?php echo $shoppingCartURL;?>"><strong>View Order</strong></a><br />
						</div><?php
					}
					//-------------------------------------------------------------------
					//get the image information, first from /images/thumb, then cp from RelateBase file system; we know the foloder the images are stored in - config vars first
					$safeSKU=str_replace('"','',$rd['SKU']);
					$safeSKU=str_replace("'",'',$safeSKU);
					$safeSKU=strtolower($safeSKU);
					$idx='';
					switch(true){
						case $normalImage=$images[$safeSKU.'.jpg']['name']:
							$idx=$safeSKU.'.jpg';
						case $normalImage=$images[$safeSKU.'.gif']['name']:
							if(!$idx) $idx=$safeSKU.'.gif';
							//got it
							$imgSrc='images/thumb/'.$normalImage;
							$w=$images[$idx]['width'];
							$width=(!$w ? $thumb_width : ($w<180 ? $w : ($w>200 ? 180 : $w)));
						break;
						case $a=q("SELECT LocalFileName, VOSFileName FROM $MASTER_USERNAME.relatebase_files WHERE ID='".$rd[$thumbImageField]."' AND ID!=0", O_ROW, C_MASTER):
							@extract($a);
							if(file_exists($VOS_ROOT.'/'.$VOSFileName)){
								//copy file over from RelateBase - no constraints or resizing by default
								$x=$safeSKU.'.'.substr($VOSFileName,-3);
								copy($VOS_ROOT.'/'.$VOSFileName, 'images/thumb/'.$x);
								//add to the images array
								$images[$x]['name']=$x;
								$imgSrc='images/thumb/'.$x;
								$a=@getimagesize($imgSrc);
								$images[$x]['width']=$a[0];
								$images[$x]['height']=$a[0];
								$width=(!$a[0] ? $thumb_width : ($a[0]<180 ? $a[0] : ($a[0]>200 ? 180 : $a[0])));
								//notify admin
								if($sendRBFileCopyNotices){
									mail($adminEmail,'File copied over from RelateBase',
									str_replace("\t",'','Account: '.$MASTER_USERNAME.',
									Record ID: '.$rd['ID'].',
									SKU: '.$rd['SKU'].',
									RelateBase File Name: '.$LocalFileName), 'From: robots@'.$siteURL);
								}
							}else{
								//notify admin
								mail($superadminEmail,'Childless file record in RelateBase VFS',
								'Account: '.$MASTER_USERNAME.',
								Record: '.$rd['ID'].',
								Thumb Field: '.$rd[$thumbImageField],
								$fromHdrBugs);
							}
						break;
						default:
							//no image available
							$imgSrc='images/thumb/pna.jpg';
							$width=$thumb_width;
						
					}
					?>
					<table class="embed2" <?php if($addCart==1 && $rd['ID']==$ID)echo 'bgcolor="#9c946b;"';?> >
					  <tr>
						<td class="embed2_1"><a href="single.php?ID=<?php echo $rd['ID'];?>&amp;links_id=<?php echo $links_id?>&amp;SubCategory=<?php echo urlencode(stripslashes($SubCategory)); ?>&amp;Keywords=<?php echo urlencode($Keywords)?>&amp;Theme=<?php echo urlencode($Theme)?>&amp;Function=<?php echo urlencode($Function)?>" title="View this specific product"><img src="<?php echo $imgSrc?>" width="<?php echo $width?>" border="0" /></a></td>
						<td class="embed2_2" valign="top"><div class="rName" style="font-size:119%"><?php echo htmlentities($rd['Name']);?></div>
							<div class="rSKU"><?php echo htmlentities($rd['SKU']);?></div>
						  <?php echo ($price=number_format($_SESSION['cnx'][$MASTER_DATABASE]['wholesaleAccess']? $rd['WholesalePrice'] : $rd['UnitPrice'], 2)=='0.00'?'':$price);?> <br />
							<?php
								if($_SESSION['Login']=='guest' && $_SESSION['AcctStatus']==2 && $rd['PK']>1){
									$multiplier=$rd['PK'];
								}else{
									$multiplier=1;
								}
								$qs=array();
								if(trim($Theme))$qs[]='Theme='.urlencode($Theme);
								if(trim($Function))$qs[]='Function='.urlencode($Function);
								if(trim($SubCategory))$qs[]='SubCategory='.urlencode(stripslashes($SubCategory));
								if(trim($links_id))$qs[]='links_id='.$links_id;
								$qs=implode('&',$qs);
							if($price){
								?>
								<form action="index_01_exe.php?<?php echo $qs.'#r'.$rd['ID'];?>" method="post" name="form1" id="form1" target="w2" style="margin:0px;padding:0px;">
									<input type="submit" name="Add" value="Add" />
									<select class="formtext1" name="qty">
									<?php
									for($e=1;$e<=10;$e++){
										?>
										<option value="<?php echo $multiplier*$e?>"><?php echo $multiplier*$e;?></option>
										<?php
									}
									?>
									</select>
									<input type="hidden" name="mode" value="addcart" />
									<input type="hidden" name="ID" value="<?php echo $rd['ID']?>" />
									<?php
									if(trim($Keywords)){
										?>
										<input type="hidden" name="Keywords2" value="<?php echo htmlentities($Keywords)?>" />
										<?php
									}
									?>
								</form>
								<a href="products.php?<?php echo 'Keywords='.urlencode($Keywords).'&addCart=1&qty=1&ID='.$rd['ID'].'&'.$qs.'#r'.$rd['ID'];?>">Add to cart</a> | 
								<?php 
							}
							?>
							<a href="single.php?<?php echo $qs; ?>&amp;ID=<?php echo $rd['ID'];?>">View details</a>
							<?php
							if($price && $multiplier>1){
								?>
								<br />
								Minimum pack of <?php echo $rd['PK'];?> requested.
								<?php
							}
							?>
						</td>
					  </tr>
				  </table></td>
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
						?>
				<td>&nbsp;</td>
				<?php
					}
				}
				//end a row

				if( $col % $cols == 0){
					$col=0;
					?>
			  </tr>
			  <?php
					if($row>=$maxRows)break;
				}
			}
			?>
			</table>
			<?php
		}else{
			?>
			Sorry, nothing was found under the search term <?php echo stripslashes($Keywords);?>. Please try again or use the special links to the left side.
			<?php
		}
	break;
	case $pJDerivedThispage=='summary':
	case $thispage=='summary':
		//Old summary.php page from Paci fic Ba sin/Rus ch
		$images=get_file_assets($_SERVER['DOCUMENT_ROOT'].'/images/category');
		$baseWhere=" WHERE Active = 1 /* AND (RWB='B' OR RWB='".($_SESSION['cnx'][$MASTER_DATABASE]['wholesaleAccess']?'W':'R')."') */";
		if($links_id){
			$rd=q("SELECT Name as LinkName, Clause, OrderBy, ImageLocation, TextContent, Description FROM gen_links WHERE ID='$links_id'",O_ROW);
			@extract($rd);
			$sql="SELECT DISTINCT SubCategory FROM finan_items";
			$sql.=$baseWhere;
			$sql.=' AND '.preg_replace('/^\s*WHERE\s+/i','',$Clause);
			if($x=trim(preg_replace('/^\s*ORDER BY\s+/i','',$OrderBy)) && false /** 2006-07-10: sort by name for now **/){
				$sql.=' ORDER BY '.$x;	
			}else{
				$sql.=' ORDER BY SubCategory ASC';
			}
			?>
			<h2><?php echo htmlentities($rd['LinkName'])?></h2>
			<?php
			if($adminMode && false){
				?>
				<a title="Edit This Link" onClick="return ow(this.href,'l1_links','700,700');" href="resources/configure_links.php?Links_ID=<?php echo $links_id?>"><img src="images/i/edit2.gif" alt="edit" width="15" height="18" border="0" /></a>
				<?php
			}
			?>
			<span class="groupDescr">
			<?php
			if($Description){
				echo $Description;
			}else if($adminMode){
				?>Edit description text<?php
			}
			?>
			</span>
			<?php
		}else if($Keywords){
			//2006-06-14: this is currently not sent to this page - see products.php
			$sql="SELECT DISTINCT SubCategory FROM finan_items $baseWhere AND CONCAT(SKU, Name, Keywords, Category, SubCategory, Description) LIKE '%" . $Keywords . "%' ORDER BY SubCategory ASC";
			$displayMsg = 'Search by keyword: '. $Keywords . '<br />';
		}else if($_GET[$quickJumpField1] || $_GET[$quickJumpField2]){
			//this is also not currently sent to this page - see products.php
			$fieldValue=$_GET[$quickJumpField1] . $_GET[$quickJumpField2];
			$fieldName=($_GET[$quickJumpField1] ? $quickJumpField1 : $quickJumpField2);
			$sql="SELECT DISTINCT Sub$fieldName FROM finan_items $baseWhere AND Sub$fieldName='" . $fieldValue . "' ORDER BY Sub$fieldName ASC";
			$displayMsg = 'Search by '.strtolower($fieldName).': '. $fieldValue . '<br />';
		}else{
			mail('sam-git@compasspointmedia.com','On pbs, there was not a valid query method passed',$QUERY_STRING . ', FROM:' . $HTTP_REFERER, $fromHdrBugs);
			?><script language="javascript" type="text/javascript">window.location='/';</script>
			<?php
			exit;
		}
		$cols=3;
		$rows=1000;
		$startRow=1;
		$startCol=1;
		//or you can specify this:
		#$startPosition=1
		$maxRows=1000;
		
		//prelims
		if($cols < 1 || !is_int($cols)) exit('Col must be an integer value greater than zero');
		if(!$startPosn)$startPosn=($cols * ($startRow-1)) + $startCol;
		$count=0;
		if($test)prn($sql);
		if($records=q($sql, O_ARRAY)){
			?>
			<table class="loop1">
			  <?php
			/***  Horizontal Looper v1.0 - 2006-09-01 - this looper is record-driven vs. row-column driven.  A row-column driven looper would be much smaller codewise ***/
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
						?>
			  <tr>
				<?php
							for($j=1; $j<=$cols; $j++){
								switch($j){
									case 1:
										$hPosition='left';
										break;
									case $cols:
										$hPosition='right';
										break;
									default:
										$hPosition='mid';
								}
								$startOffsetCells++; //total number of blank cells
								$col++;
								?>
				<td>&nbsp;</td>
				<?php
							}
						?>
			  </tr>
			  <?php
					}
				}
				//begin a row
				if( $col==0 ){
					$row++;
					/**
					this is inaccurate and needs logic to differentiate further between "mid" and bottom
					**/
					$vPosition=($row==1 ? 'top' : (count($records)-$cells-$startOffsetCells < $cols || $row>=$maxRows ? 'bottom' : 'mid'));
					?>
			  <tr>
				<?php
				}
				//add initial padding cells
				if($count==1 && $startPad = ($startPosn - 1) % $cols  ){
					for($i=1; $i<=$startPad; $i++){
						switch(true){
							case $i==1:
								$hPosition='left';
								break;
							default:
								$hPosition='mid';
						}
						$startOffsetCells++;
						$col++;
						?>
				<td>&nbsp;</td>
				<?php
					}
				}
				//normal cells
				$col++;
				$cells++;
				$hPosition=($col==1 ? 'left' : ($col % $cols ==0 ? 'right' : 'mid'));
				?>
				<td valign="top"><?php /** content goes here **/ ?>
					<table align="center" class="embed1" <?php if($addCart==1 && $v['ID']==$_GET['ID']){echo ' bgcolor="#FFFFCC"';}?> >
					  <tr>
						<td valign="top" align="center"><?php
							//get the link url
							$ct=q("SELECT COUNT(*) AS ct FROM finan_items WHERE SubCategory = '".$v['SubCategory']."' AND Active=1 /* AND ( RWB='B' OR RWB='".($_SESSION['cnx'][$MASTER_DATABASE]['wholesaleAccess']?'W':'R')."') */",O_VALUE);
							if($ct==0){
								$url="#";
							}else if($ct==1){
								$ID=q("SELECT ID FROM finan_items WHERE SubCategory='".$v['SubCategory']."' AND Active=1 /* AND ( RWB='B' OR RWB='".($_SESSION['cnx'][$MASTER_DATABASE]['wholesaleAccess']?'W':'R')."') */", O_VALUE);
								$url="single.php?ID=".$ID;
							}else{
								$url="/products/8010?links_id=$links_id&SubCategory=".urlencode($v['SubCategory']);
							}
		
							//-------------------------------------------------------------------
							#FIND THE IMAGE FOR SubCategory WHETHER GIF, JPG, OR PNG EXTENSION
							$haveImage=false;
							$safeSubCategory=strtolower(preg_replace('/^[^- a-z0-9_]*$/i','',$v['SubCategory']));
							$safeSubCategory=str_replace('"','',$safeSubCategory);
							$safeSubCategory=str_replace("'",'',$safeSubCategory);
							foreach($images as $o=>$w){
								//remove the extension
								$safeName=preg_replace('/\.(gif|jpg|png)$/i','',$o);
								//remove all except the "normal" characters
								$safeName=strtolower(preg_replace('/^[^- a-z0-9_]*$/i','',$safeName));
								if($safeName==$safeSubCategory){
									$haveImage=true;
									$imgSrc='images/category/'.$w['name'];
									$w=$w['width'];
									$width=($w>180 ? 180 : ($w<150 ? 150 : ($w)));
									break;
								}
							}
							if(!$haveImage){
								$imgSrc='images/assets/spacer.gif';
								$width=150;
							}
							//-------------------------------------------------------------------
		
							if($adminMode){
								?>
							<br />
							<a title="Upload or edit this image" onClick="alert('This file should be named <?php echo strtolower(preg_replace('/[^a-z0-9]/i','',$v['SubCategory']))?>'); return ow(this.href,'l1_categories','700,700');" href="admin/file_explorer/index.php?uid=editcategories&folder=category"><img src="images/i/edit2.gif" alt="edit" width="15" height="18" border="0" /></a><br />
						  <?php
							}
							?>
						  <a href="<?php echo $url;?>" style="color:#CCC;">
							<?php
							?>
							<img id="cat_<?php echo strtolower(h($v['SubCategory']));?>" src="<?php echo $imgSrc?>" alt="<?php echo htmlentities($v['SubCategory']);?>" width="<?php echo $width?>" border="0" /> </a>
						  <h3><a href="<?php echo $url;?>" style="color:saddlebrown;"><?php echo htmlentities($v['SubCategory']);?></a></h3></td>
					  </tr>
				  </table></td>
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
						?>
				<td>&nbsp;</td>
				<?php
					}
				}
				//end a row
				if( $col % $cols == 0){
					$col=0;
					?>
			  </tr>
			  <?php
					if($row>=$maxRows)break;
				}
			}
			?>
    </table>
			<?php
		}else{
			?>
			<h4>Currently, no items in this category</h4>
			<?php
		}
	break;
	case $pJDerivedThispage=='catalog':
	case $thispage=='catalog':
		?><h1><a href="<?php echo $rootURL;?>" title="Main product listing">All Products</a> &raquo; Catalog</h1><?php
		CMSB('catalogintro');
		
		if($products=q("SELECT
			i.*, COUNT(DISTINCT t.ID) AS Used
			FROM finan_items i LEFT JOIN finan_transactions t ON i.ID=t.Items_ID
			WHERE 1 ".($adminMode && !$prodHideInactiveItems ? " AND i.Active=1":'')." AND
			ResourceType IS NOT NULL AND
			i.Active=1 AND  
			(i.Type='' OR i.Type='Non-inventory Part') 
			GROUP BY i.ID
			ORDER BY IF(i.Category='', 2,1), i.Category, IF(i.SubCategory='', 1,2), i.SubCategory, i.Name", O_ARRAY)){
			
			$prodImgArray=get_file_assets($_SERVER['DOCUMENT_ROOT'].'/images/products/thumb');
			
			
			$i=0;
			$cols=$prodSettings['prodCatalogColumns'];
			if(!$cols)$cols=3;
			$currentCol=0;
			$buffer1=md5(rand(1000,1000000));
			$buffer2=md5(rand(1000,1000000));
			//-------------- horiz looper --------------
			?>
			<table class="horizontalLooper"><?php
			foreach($products as $rdp){
				$i++;
				$currentCol= fmod($currentCol,$cols)+1;
				
				if($changeCategory=($rdp['Category'] !==$buffer1)){
					$buffer1=$rdp['Category'];
					if($row || $i==1){
						if($row && $currentCol<$cols && $currentCol>1){
							echo str_repeat('<td class="empty 0">&nbsp;</td>',$cols - $currentCol+1);
							echo '</tr>';
						}
						if(trim($buffer1)){
							?><tr class="trA"><td colspan="100%"><?php echo 'Category: '.$buffer1?></td></tr><?php
						}
						//reset
						$currentCol=1;
					}
				}else{
					$changeCategory=false;
				}
				if($changeSubCategory=($rdp['SubCategory'] !==$buffer2)){
					$buffer2=$rdp['SubCategory'];
					if($row || $i==1){
						if($row && $currentCol<$cols && $currentCol>1 && !$changeCategory){
							echo str_repeat('<td class="empty 1">&nbsp;</td>',$cols - $currentCol+1);
							echo '</tr>';
						}
						if(trim($buffer2)){
							?><tr class="trB"><td colspan="100%"><?php echo 'Subcategory: '.$buffer2?></td></tr><?php
						}
						//reset
						$currentCol=1;
					}
				}
				if($currentCol==1){
					$row++;
					?><tr class="tr1"><?php
				}
				
				?><td><?php
				//-------------- product here ------------
				$showMoreInfoButton=false;
				$prodLimitImageWidth=200;
				require($_SERVER['DOCUMENT_ROOT'].'/components/ecom_flex1_006juliet.php');
				
				//----------------------------------------
				?></td><?php
				
				if($currentCol==$cols){
					?></tr><?php
				}
			}
			//final close
			if($row && $currentCol<$cols){
				echo str_repeat('<td class="tr2 empty">&nbsp;</td>',$cols - $currentCol);
				echo '</tr>';
			}
			?></table><?php
			//-------------------------------------------
		}else{
			if($adminMode){
				?><p>There are currently no products showing.</p><?php
			}else{
				mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err='There are currently no products listed!'),$fromHdrBugs);
				?><p>There are currently no products listed!</p><?php
			}
		}
	break;
	default:
		switch(true){
			case $a=prod_url_evaluator():
				extract($a);
				require(str_replace('products.php','products.'.$page.'.php',str_replace($acct.'.','',__FILE__)));
			break;
			/*
			case $a=q("SELECT * FROM finan_items WHERE REPLACE(Category,'-',' ')='".addslashes(str_replace('-',' ',$thispage))."'".($adminMode ? '' : ' AND Active=1', O_ARRAY):
				require(str_replace('products.php','products.focus.php',str_replace($acct.'.','',__FILE__)));
			break;
			default:
				if($ID=q("SELECT ID FROM finan_items WHERE SKU='".addslashes($thispage)."' OR REPLACE(Name,'-',' ')='".addslashes(str_replace('-',' ',$thispage))."'".($adminMode ? '' : " AND Active=1"), O_VALUE)){
					//focus product page coding
					require(str_replace('products.php','products.focus.php',str_replace($acct.'.','',__FILE__)));
				}
			*/
		}
}
?></span><?php


$$block=ob_get_contents();
ob_end_clean();

$pJCurrentContentRegion=pJ_getdata('pJCurrentContentRegion','mainRegionCenterContent');

//------------- sample region $sampleBlock ---------
$navBlock=pJ_getdata('productsNavContent','mainRegionLeftContent');
ob_start();

if(true || pJ_getdata('prodNavDisplayMethod','thumbs')=='thumbs'){
	if($a=q("SELECT
	i.ID AS Items_ID,
	i.SKU, i.Name, i.Description, i.Category, i.SubCategory,
	ot.Tree_ID AS Tree_ID
	FROM finan_items i, relatebase_ObjectsTree ot
	WHERE i.ID=ot.Objects_ID AND ot.ObjectName='finan_items' AND ot.Relationship LIKE '%Primary Image%' AND
	i.Active=1", O_ARRAY_ASSOC)){
		foreach($a as $Items_ID=>$v){
			if($pictures[$v['Tree_ID']])continue;
			$pictures[$v['Tree_ID']]=true;
			?><a href="/products/<?php echo $v['SKU'].'?passnode='.$thisnode.('&ID='.$v['ID']).($v['Category']?'&Category='.urlencode($v['Category']):'').($v['SubCategory']?'&SubCategory='.urlencode($v['SubCategory']):'')?>" title="View this item - <?php echo h($v['Name']);?>"><?php
			$val=tree_image(array(
				'src'=>$v['Tree_ID'],
				'disposition'=>'95x95',
				'boxMethod'=>2,
				'style'=>'margin:1px 1px 0px 0px;',
			));
			?></a><?php
		}
	}
}else{
	//other methods
}

$$navBlock=ob_get_contents();
ob_end_clean();

//----- secondary block like a different nav menu (bonus could we declare an array and call a different CFile here? :) -----





}//---------------- end i break loop ---------------
?>