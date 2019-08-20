<?php
//index for # times file called
$bais_01_exe++;

switch(true){
	default:
		$localSys['scriptID']='generic';
		$localSys['scriptVersion']='2.0';
		//no action
}
$localSys['build']=002;
$localSys['buildDate']='2010-05-14 15:05:00';
$localSys['buildNotes']='Really beginning to clean this up, moved all album management tasks to the component itself';
$localSys['componentID']='resourceexe';

$globalSetCtrlFields=true;


require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');

require_once($FUNCTION_ROOT.'/function_parse_address_v100.php');


//handle blank fills if present
if($blankFills)
foreach($blankFills as $n=>$v){
	if(preg_match('/^preg:(.+)/i',$v,$a)){
		$v=$a[1];
	}else{
		$v=str_replace('/','\/',$v);
		$v=str_replace('(','\(',$v);
		$v=str_replace(')','\)',$v);
	}
	if(isset($_POST[$n]))$$n=$_POST[$n]=preg_replace('/^'.$v.'$/','',$_POST[$n]);
}


if(!$suppressPrintEnv){
	if(count($_GET))prn($_GET);
	if(count($_POST))prn($_POST);
}
$navigate=false; //found at end of switch-case

//shutdown coding
if(!$shutdownRegistered){
	$shutdownRegistered=true;
	$assumeErrorState=true;
	register_shutdown_function('iframe_shutdown');
	if(!$suppressHTMLOutputBuffer)ob_start();
}

#if(false){
    #echo '<pre>';
    #$assumeErrorState=false;
    #echo "$mode\n";
    #print_r(get_included_files());
    #exit;
#}

switch(true){
	case $mode=='droptosub':
		//correct variants
		$NewCategory=trim($NewCategory);
		if($NewCategory && $case=q("SELECT Category FROM finan_items WHERE Category='$NewCategory'", O_VALUE))$NewCategory=$case;
		if(!$NewCategory && !$MergeCategory)error_alert('Select or enter a new parent category');
		
		q("UPDATE finan_items SET Category='".($NewCategory ? $NewCategory : $MergeCategory)."', SubCategory='$Category' WHERE Category='$OldCategory' AND SubCategory='$SubCategory'");
		prn($qr);
		if($cbPresent && $RefreshParentList) callback(array("useTryCatch"=>false));

		$_SESSION['special']['RefreshParentList']=($RefreshParentList?1:0);
		$_SESSION['special']['NewCategory']=$NewCategory;

		?><script language="javascript" type="text/javascript">
		window.parent.close();
		</script><?php
	break;
	case $mode=='raisefromsub':
		q("UPDATE finan_items SET Category='".($MergeCategory?$MergeCategory:$SubCategory)."', SubCategory='' WHERE Category='$Category' AND SubCategory='$SubCategory'");
		
		if($cbPresent && $RefreshParentList) callback(array("useTryCatch"=>false));
		$_SESSION['special']['RefreshParentList']=($RefreshParentList?1:0);
		?><script language="javascript" type="text/javascript">
		window.parent.close();
		</script><?php
	break;
	case $mode=='moveSubCategory':
		$NewCategory=trim($NewCategory);
		if(!$NewCategory)error_alert('Select a category to move this subcategory ('.$SubCategory.') to');
		q("UPDATE finan_items SET Category='$NewCategory' WHERE Category='$Category' AND SubCategory='$SubCategory'");
		prn($qr);
		q("UPDATE finan_items_subcategories SET Category='$NewCategory' WHERE Category='$Category' AND SubCategory='$SubCategory'");
		prn($qr);
		if($cbPresent && $RefreshParentList) callback(array("useTryCatch"=>false));

		$_SESSION['special']['RefreshParentList']=($RefreshParentList?1:0);
		?><script language="javascript" type="text/javascript">
		window.parent.close();
		</script><?php
	break;
	case $mode=='editCategory':
	case $mode=='editSubCategory':
		$field=str_replace('edit','',$mode);
		$originalField='Original'.$field;
		$iscontent=preg_replace('/<p[^>]+/i','',$Heading);
		$iscontent=preg_replace('/<\/p\s*>/i','',$iscontent);
		
		#if($NewName && q("SELECT COUNT(*) FROM finan_items WHERE $field='".$$field."'", O_VALUE))error_alert('The name you entered already exists in the '.strtolower($field) .' list for your products');
		#$new=($NewName ? $NewName : $MergeName);
		#if(!$new)error_alert('You must select a new name for this '.strtolower($field).' or select a '.strtolower($field).' to merge with');
		
		if($$field!==$$originalField || $MergeField){
			$value=($MergeField ? $MergeField : $$field);
			if(!$value)error_alert('You must specify a '.strtolower($field).' name or another '.strtolower($field).' to merge with');

			//----------- merge the name in finan_items_categories or finan_items_subcategories --------------
			#first purge orphans
			if(!isset($purgeOrphans))$purgeOrphans=false;
			if($purgeOrphans){
				if($a=q("SELECT a.Category, a.SubCategory
					FROM finan_items_subcategories a LEFT JOIN finan_items b ON a.Category=b.Category AND a.SubCategory=b.SubCategory WHERE b.ID IS NULL", O_COL_ASSOC)){
					foreach($a as $cat=>$subcat){
						q("DELETE FROM finan_items_subcategories WHERE Category='".addslashes($cat)."' AND SubCategory='".addslashes($subcat)."'");
					}
				}
				if($a=q("SELECT a.Name FROM finan_items_categories a LEFT JOIN finan_items b ON a.Name=b.Category WHERE b.ID IS NULL", O_COL)){
					foreach($a as $cat){
						q("DELETE FROM finan_items_categories WHERE Name='".addslashes($cat)."'");
					}
				}
			}
			#now merge
			if($field=='Category'){
				//category
				if($present=q("SELECT Heading, Name FROM finan_items_categories WHERE Name='$value'", O_ROW)){
					//delete old data - IL in the form of heading loss
					q("UPDATE finan_items_categories SET 
					Heading='".($iscontent ? $Heading : addslashes($present['Heading']))."', 
					Editor='".($_SESSION['admin']['userName'] ? $_SESSION['admin']['userName'] : 'system')."'
					WHERE Name='$value'");
					prn($qr);
					//alert: you might have lost some info
				}else{
					q("INSERT INTO finan_items_categories SET 
					Name='$value', Heading='$Heading', 
					Creator='".($_SESSION['admin']['userName'] ? $_SESSION['admin']['userName'] : 'system')."'");
				}
			}else{
				//subcategory


				if($present=q("SELECT Heading, Category, SubCategory FROM finan_items_subcategories WHERE Category='$Category' AND SubCategory='$value'", O_ROW)){
					//delete old data - IL in the form of heading loss
					q("UPDATE finan_items_subcategories SET 
					Heading='".($iscontent ? $Heading : addslashes($present['Heading']))."', 
					Editor='".($_SESSION['admin']['userName'] ? $_SESSION['admin']['userName'] : 'system')."'
					WHERE Category='$Category' AND SubCategory='$value'");
					prn($qr);
					//alert: you might have lost some info
				}else{
					q("INSERT INTO finan_items_subcategories SET 
					Category='$Category',
					SubCategory='$value', 
					Heading='$Heading', 
					Creator='".($_SESSION['admin']['userName'] ? $_SESSION['admin']['userName'] : 'system')."'");
				}
			}
			//-------------------------------------------------------------------------------------------------
			q("UPDATE finan_items SET $field='".$$field."' WHERE ".($field=='Category'?"Category='".$$originalField."'":"Category='$Category' AND SubCategory='".$$originalField."'"));
			prn($qr);
			$changedCategories=true;
		}else if($iscontent){
			if(q("SELECT * FROM ".($field=='Category'?'finan_items_categories':'finan_items_subcategories'). " WHERE ".($field=='Category'?"Name='".$$field."'":"Category='$Category' AND SubCategory='".$$field."'"), O_ROW)){
				q("UPDATE ".($field=='Category'?'finan_items_categories':'finan_items_subcategories')." SET Heading='$Heading' WHERE ".($field=='Category'?"Name='".$$field."'":"Category='$Category' AND SubCategory='".$$field."'"));
				prn($qr);
			}else{
				if($field=='Category'){
					q("INSERT INTO finan_items_categories SET 
					Name='$value', Heading='$Heading', 
					CreateDate=NOW(),
					Creator='".($_SESSION['admin']['userName'] ? $_SESSION['admin']['userName'] : 'system')."'");
				}else{
					q("INSERT INTO finan_items_subcategories SET 
					CreateDate=NOW(),
					Creator='".($_SESSION['admin']['userName'] ? $_SESSION['admin']['userName'] : 'system')."',
					Category='$Category',
					SubCategory='".$$field."', 
					Heading='$Heading'");
				}
				prn($qr);
			}
		}
		if($cbPresent && $RefreshParentList && $changedCategories) callback(array("useTryCatch"=>false));
		$_SESSION['special']['RefreshParentList']=($RefreshParentList?1:0);
		?><script language="javascript" type="text/javascript">
		window.parent.close();
		</script><?php
	break;
	case $mode=='insertArticle':
	case $mode=='updateArticle':
		//error checking
		if(!$Title)error_alert('You must include a title');
		if(!$Body)error_alert('The article must contain at least some content');

		if(!$Active)$Active='0';
		if(!$Private)$Private='0';
		if(($PostDate=strtotime($PostDate))==-1){
			error_alert('Please specify a valid posting date as mm/dd/yyyy hh:mm (AM or PM)');
		}else $PostDate=date('Y-m-d H:i:s',$PostDate);
		if(!$ID)unset($ID);

		$sql=sql_insert_update_generic($MASTER_DATABASE,'cms1_articles', ($mode=='insertArticle' ? 'INSERT INTO' : 'UPDATE'), '', $options);
		$fl=__FILE__; $ln=__LINE__;
		$x=q($sql, O_INSERTID);
		prn($qr);
		if($mode==$insertMode)$ID=$x;
		if($LeadArticle){
			q("UPDATE cms1_articles SET LeadArticle=0 WHERE ID!='$ID'");
		}
		if(!($Lead=q("SELECT ID FROM cms1_articles WHERE LeadArticle=1", O_VALUE))){
			q("UPDATE cms1_articles SET LeadArticle=1 WHERE Active=1 ORDER BY RAND() LIMIT 1");
		}
		$navigate=true;
		$navigateCount=$count+($mode==$insertMode?1:0);
	break;
	case $mode=='deleteArticle':
		q("DELETE FROM cms1_articles WHERE ID='".($Articles_ID ? $Articles_ID : $ID)."'");
		?><script>
		window.parent.location=window.parent.location+'';
		</script><?php
	break;
	case $mode=='insertItem':
	case $mode=='updateItem':
	case $mode=='deleteItem':
		if($submode=='addItemObject' ||
			$submode=='editItemPicturesFieldPrep' ||
			$submode=='editItemPicturesField' || 
			$submode=='assignPicture' || 
			$submode=='deleteItemPictures'){
			require($COMPONENT_ROOT.'/comp_15_itemobjects_v100.php');
			$assumeErrorState=false;
			exit;
		}
		if($mode=='deleteItem'){
			$a=q("SELECT a.*,
			IF(c.Items_ID IS NULL,0,1) AS IsPackage,
			COUNT(DISTINCT b.ID) AS count FROM finan_items a LEFT JOIN finan_transactions b ON a.ID=b.Items_ID LEFT JOIN finan_items_packages c ON a.ID=c.Items_ID WHERE a.ID=$Items_ID GROUP BY a.ID", O_ROW);
			if($a['count'])error_alert('This item has been used in purchases; it cannot be deleted');
			q("DELETE FROM finan_items WHERE ID=$Items_ID");
			q("DELETE FROM finan_items_packages WHERE Items_ID=$Items_ID");
			q("DELETE FROM finan_ItemsItems WHERE ParentItems_ID=$Items_ID OR ChildItems_ID=$Items_ID");
			q("DELETE FROM finan_items_related WHERE Parent_ID=$Items_ID OR Child_ID=$Items_ID");
			?><script language="javascript" type="text/javascript">
			window.parent.g('ri<?php echo $a['IsPackage'] ? 'p' : ''?>_<?php echo $Items_ID?>').style.display='none';
			</script><?php
			$assumeErrorState=false;
			exit;
		}
		//data conversion and error checking
		$Active=($Inactive?'0':'1');
		$OutOfStock=($OutOfStock?1:0);
		$PurchasePrice=str_replace(',','',$PurchasePrice);
		$UnitPrice=str_replace(',','',$UnitPrice);
		$UnitPrice2=str_replace(',','',$UnitPrice2);
		$WholesalePrice=str_replace(',','',$WholesalePrice);

		if($dupe=q("SELECT ID FROM finan_items WHERE SKU='$SKU'".($mode==$updateMode?" AND ID<>$ID":''),O_VALUE)){
			error_alert('This is a duplicate item number (SKU) - each item number must be unique');
		}

		$qx['useRemediation']=false;

		//invoked by import manager
		if($error_alert['errors'])break;

		//base query finan_items
		if($mode==$insertMode && $CreateMultipleItems){
			$sizes=explode(',',$SizeList);
			if($Colorcharts_ID > 0){
				$colors=q("SELECT Code FROM finan_colorcharts_colors WHERE Colorcharts_ID=$Colorcharts_ID", O_COL);
			}else if($Colorcharts_ID==-1){
				if(!trim($ColorChartValues))error_alert('Enter a list of colors');
				$colors=explode(',',$ColorChartValues);
				//create the chart
				$Colorcharts_ID=q("INSERT INTO finan_colorcharts SET CreateDate=NOW(), Creator='system', Name='$ColorChartName', Manufacturers_ID='$Manufacturers_ID'", O_INSERTID);
				foreach($colors as $color){
					q("INSERT INTO finan_colorcharts_colors SET Colorcharts_ID=$Colorcharts_ID, Code='$color', CreateDate=NOW()");
				}
				//repopulate selection list
				?><span id="colorchartwrap"><select name="Colorcharts_ID" id="Colorcharts_ID" onchange="toggleColorChart(this.value);dChge(this);">
					<option value="">Select a color chart..</option>
					<?php 
					if($colorcharts=q("SELECT ID, Name FROM finan_colorcharts ORDER BY Name", O_COL_ASSOC))
					foreach($colorcharts as $n=>$v){
						?><option value="<?php echo $n?>" <?php echo $Colorcharts_ID==$n?'selected':''?>><?php echo h($v)?></option><?php
					}
					?>
					<option value="-1">&lt;New color chart..&gt;</option>
				</select></span>
				<script language="javascript" type="text/javascript">
				window.parent.g('colorlist').style.display='none';
				window.parent.g('colorchartwrap').innerHTML=document.getElementById('colorchartwrap').innerHTML;
				window.parent.g('SizeList').value='';
				window.parent.g('ColorChartName').value='';
				window.parent.g('ColorChartValues').value='';
				window.parent.alert('A new color chart has been created for these items; you can now edit the color chart');
				</script><?php
			}else{
				error_alert('Select a color chart or enter a list of colors separated by a comma');
			}
			
			foreach($sizes as $size){
				foreach($colors as $color){
					$SKU=$_POST['SKU'].'-'.strtoupper($size).'-'.strtoupper($color);
					$sql=sql_insert_update_generic($MASTER_DATABASE,'finan_items', 'INSERT',$options=array('setCtrlFields'=>true));
					prn($sql);
					$fl=__FILE__; $ln=__LINE__;
					$x=q($sql, O_INSERTID);
					$Items_ID=($mode==$insertMode ? $x : $ID);
					q("INSERT INTO finan_ItemsColorcharts SET Items_ID=$Items_ID, Colorcharts_ID=$Colorcharts_ID");
				}
			}
		}else{
			$ResourceType=1;
			$sql=sql_insert_update_generic($MASTER_DATABASE,'finan_items', 'UPDATE', $options=array('setCtrlFields'=>true));
			$fl=__FILE__; $ln=__LINE__;
			q($sql);
			$Items_ID=$ID;
		}
		//handle additional relationships
		if($IsPackage){
			//CF's will mirror finan_items
			$sql=sql_insert_update_generic($MASTER_DATABASE,'finan_items_packages', ($x=q("SELECT CreateDate FROM finan_items_packages WHERE Items_ID=$Items_ID", O_VALUE) ? 'UPDATE' : 'INSERT INTO'), '', $options);
			prn($sql);
			q($sql);
			prn($qr);
			if($mode==$insertMode){
				?><script language="javascript" type="text/javascript">
				window.parent.location='/console/items.php?IsPackage=1&Items_ID=<?php echo $Items_ID?>&cbFunction=refreshList';
				</script><?php
				$assumeErrorState=false;
				exit;
			}
		}
		if($ParentItems_ID){
			if(!isset($BonusItem))$BonusItem='0';
			if(!isset($PricingType))$PricingType='';
			if(!isset($priceOrPercent))$priceOrPercent='';
			if(!isset($PriceValue))$PriceValue='';
			$ChildItems_ID=$Items_ID;
			$sql=sql_insert_update_generic($MASTER_DATABASE,'finan_ItemsItems', ($x=q("SELECT CreateDate FROM finan_ItemsItems WHERE ParentItems_ID=$ParentItems_ID AND ChildItems_ID=$Items_ID", O_VALUE) ? 'UPDATE' : 'INSERT INTO'), $options);
			q($sql);
			prn($qr);
		}
		//handle the external categorie(s)
		if($MASTER_DATABASE=='cpm112'){
			if($a=q("SELECT Hierarchy_ID FROM finan_ItemsHierarchy WHERE Items_ID='$ID'", O_COL)){
				foreach($a as $v)$existing[]=$v;
			}
			if($existing!=$Hierarchy_ID){
				error_alert('external category change',true);
				q("DELETE FROM finan_ItemsHierarchy WHERE ".($updateAcrossModel ? "Items_ID IN('".implode("','",($ModelIds=q("SELECT ID FROM finan_items WHERE Model='$Model'", O_COL)))."')":"Items_ID='$ID'"));
				prn($qr);
				foreach($Hierarchy_ID as $v){
					if($updateAcrossModel){
						$subq="SELECT ID, $v FROM finan_items WHERE Model='$Model'";
					}else{
						$subq="SELECT $ID, $v FROM finan_items LIMIT 1";
					}
					q("INSERT INTO finan_ItemsHierarchy(Items_ID, Hierarchy_ID) $subq");
					prn($qr);
				}
			}
			if($updateAcrossModel){
				if(!isset($_SESSION['special']['crossModelUpdateFields'])){
					$_SESSION['special']['crossModelUpdateFields']=$defaultCrossModelUpdateFields;
				}
				if(!in_array(true,$_SESSION['special']['crossModelUpdateFields'])){
					error_alert('You did not have any fields selected for your model cross-update.  Click on the attributes tab, and click "select" next to the checkbox "update certain changes for all items in this model"',true);
				}else{
					$sql="UPDATE finan_items SET ";
					foreach($_SESSION['special']['crossModelUpdateFields'] as $n=>$v){
						if(!$v)continue;
						$sql.=$n . "='".$$n."',";
					}
					$sql=rtrim($sql,',')." WHERE Model='$Model' AND Model!=''";
					if(!strlen($Model)){
						error_alert('You cannot cross-update for a blank model; no cross-updates were made',true);
					}else{
						q($sql);
					}
				}
			}
		}
		//handle callback
		if($cbPresent){
			callback(array("useTryCatch"=>false));
		}
		//navigate interface
		$navigate=true;
		$navigateCount=$count+($mode==$insertMode ? 1 : 0);
	break;
	case $mode=='toggleActiveObject':
		if(!$dataset)$dataset=$component;
		$update=array(
			'Members'=>array(
				'table'=>'addr_contacts',
				'keyfield'=>'ID',
				'base'=>'r'
			),
			'Album'=>array(
				'table'=>'ss_albums',
				'keyfield'=>'ID',
				'base'=>'r'
			),
			'Items'=>array(
				'table'=>'finan_items',
				'keyfield'=>'ID',
				'base'=>'r'
			),
			'Events'=>array(
				'table'=>'cal_events',
				'keyfield'=>'ID',
				'base'=>'r'
			),
		);
		if(!$update[$dataset]){
			mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
			error_alert('I am unable to toggle that list active/inactive');
		}
		extract($update[$dataset]);
		if(!$base)$base='r';
		q("UPDATE $table SET Active=".($current?0:1)." WHERE $keyfield=$node");
		prn($qr);
		?><script language="javascript" type="text/javascript">
		try{
			var g=window.parent.g;
			g('<?php echo $base?>_<?php echo $node?>_active').innerHTML=(<?php echo $current?> ? '<img src="/images/i/garbage2.gif" width="18" height="21" align="absbottom" />' : '&nbsp;');
			g('<?php echo $base?>_<?php echo $node?>_active').title=('Make this record '+(<?php echo $current?> ? '':'in')+'active');
			g('<?php echo $base?>_<?php echo $node?>').setAttribute('active', (<?php echo $current?> ? '0' : '1'));
			if(window.parent.hideInactive<?php echo $dataset?> && <?php echo $current?>){
				g('<?php echo $base?>_<?php echo $node?>').style.display='none';
				var c=parseInt(window.parent.g('<?php echo $datasetComponent?>_count').innerHTML)-1;
				window.parent.g('<?php echo $datasetComponent?>_count').innerHTML=c;
			}
		}catch(e){ if(e.description)alert(e.description); }
		</script><?php
	break;
	//----- NOTE! there is no break after toggleActive and sort but they go directly into refreshComponent --------
	case $mode=='toggleActive':
		#mode=toggleActive&node=Orders&hideInactive=1|0&component=orderList
		$hideInactive=($current=='1' ? 0 : 1);
		//update settings and environment
		q("REPLACE INTO bais_settings SET UserName='".$_SESSION['admin']['userName']."', varnode='hideInactive$node',varkey='',varvalue='$hideInactive'");
		$_SESSION['userSettings']['hideInactive'.$node]=$hideInactive;
		?><script language="javascript" type="text/javascript">
		window.parent.hideInactive<?php echo $node?>=<?php echo $hideInactive?>;
		</script><?php
	case $mode=='sort':
		#mode=sort &sort=colname &vargroup=orders &node=Order &dir=1 &component=orderList
		if($sort){
			q("REPLACE INTO bais_settings SET UserName='".$_SESSION['admin']['userName']."', vargroup='$vargroup',varnode='default".$node."Sort',varkey='',varvalue='$sort'");
			q("REPLACE INTO bais_settings SET UserName='".$_SESSION['admin']['userName']."', vargroup='$vargroup',varnode='default".$node."SortDirection',varkey='',varvalue='".($dir?$dir:1)."'");
			$_SESSION['userSettings']['default'.$node.'Sort']=$sort;
			$_SESSION['userSettings']['default'.$node.'SortDirection']=($dir?$dir:1);
		}
		// .. continue to next case ..
	case $mode=='refreshComponent':
		$registeredComponents['pkgItems']=$COMPONENT_ROOT.'/comp01_packageitems_v100.php';
		$registeredComponents['clientGroup']=$COMPONENT_ROOT.'/comp_02_clientgroups_v100.php';
		$registeredComponents['orderList']=$COMPONENT_ROOT.'/comp_20_orders_version200.php';
		$registeredComponents['memberList']=$COMPONENT_ROOT.'/comp_24_list_members_v200.php';
		$registeredComponents['eventList']=$COMPONENT_ROOT.'/comp_30_list_events_v105.php';
		$registeredComponents['adList']=$COMPONENT_ROOT.'/comp_25_list_classifieds_v100.php';
		$registeredComponents['itemList']=$COMPONENT_ROOT.'/comp_11_itemslist_v200.php';
		$registeredComponents['albumList']=$COMPONENT_ROOT.'/comp_26_list_albums_v200.php';
		$registeredComponents['addons']=$COMPONENT_ROOT.'/addon.addon_masterlist_v100.php';
		$registeredComponents['adsList']=$COMPONENT_ROOT.'/comp_25_list_classifieds_v200.php';
		$registeredComponents['propertiesList']=$COMPONENT_ROOT.'/comp_500_properties_list_v100.php';
		if(strstr($component,':')){
			$a=explode(':',$component);
			if(md5($a[1].$MASTER_PASSWORD)!=$a[2])error_alert('Improper key passage for dynamic file component call');
			//2012-02-07: the component has self-validated; create this node on the fly
			$registeredComponents[$a[0]]=$COMPONENT_ROOT.'/'.$a[1];
			//this relates output (div id) to the component
			$component=$a[0];
		}else{
			if(!$registeredComponents[$component])
				error_alert('Pardon me but I am unable to locate the component "'.$component.'" on the server.\n\nPlease go to '.__FILE__.' and make sure that $registeredComponents[\\\''.$component.'\\\'] has a value declared');
		}
		if(!file_exists($registeredComponents[$component]))
			error_alert('Pardon me but I am unable to locate the file: "'.$registeredComponents[$component].'"\n\nFrom: '.__FILE__.'\n\nPlease correct the value for $registeredComponents[\\\''.$component.'\\\'] to solve this problem');
		
		$refreshComponentOnly=true;
		require($registeredComponents[$component]);
		if($submode=='exportDataset'){
			//output CSV
			$assumeErrorState=false;
			$suppressNormalIframeShutdownJS=true;
	
			header("Content-Type: application/octet-stream");
			header('Content-Disposition: attachment; filename="'.$component.'['.count($records).']-@'.date('Y-m-d_H-i-s').'.csv"');
			echo $datasetOutput;
			exit;
		}else{
			?><script language="javascript" type="text/javascript">
			/*
			NOTE: the unset does not appear to be working when items have been deleted
			*/
			try{ var scrollY=window.parent.g('<?php echo $component?>_tbody').scrollTop; }catch(e){ }
			window.parent.g('<?php echo $component?>').innerHTML=document.getElementById('<?php echo $component?>').innerHTML;
			//attempt to reconstitute selected items for the group - store locally and unset existing since some may have been deleted
			var a=window.parent.hl_grp['<?php echo $component?>'];
			window.parent.hl_grp['<?php echo $component?>']=new Array();
			for(var j in a){
				try{ window.parent.g(j).onclick();	}catch(e){ }
			}
			try{ if(scrollY)window.parent.g('<?php echo $component?>_tbody').scrollTop=scrollY; }catch(e){ }
			<?php if($updateDatasetFilters){ ?>
			//added 2009-12-15: update filter gadget
			window.parent.g('filterGadget').innerHTML=document.getElementById('filterGadget').innerHTML;
			<?php }?>
			</script><?php
		}
	break;
	case $mode=='selectItemPackage':
		//error checking 1. cannot add an item to itself 2. cannot add an item that is itself a package
		
		
		foreach($_REQUEST as $n=>$v){
			if(substr($n,0,2)=='cb'){
				$query.='&'.$n.'='.$v;
			}
		}
		?><script language="javascript" type="text/javascript">
		var url='/console/items.php?Items_ID=<?php echo $ChildItems_ID?>&ParentItems_ID=<?php echo $ParentItems_ID?><?php echo $query?>';
		window.parent.location=url;
		</script><?php 
	break;
	case $mode=='removePackageItems':
		q("DELETE FROM finan_ItemsItems WHERE ParentItems_ID='$ParentItems_ID' AND ChildItems_ID='$Items_ID'");
		$left=q("SELECT COUNT(*) FROM finan_ItemsItems WHERE ParentItems_ID='$ParentItems_ID'", O_VALUE);
		?><script language="javascript" type="text/javascript">
		window.parent.g('packageItemCount').value=<?php echo $left?>;
		</script><?php
	break;
	case $mode=='updateDatasetFilters':
		//updated 2009-06-03, integrated the post process into the component itself
		//refresh filter box
		require('../components/comp_01_filtergadget_v104.php');
		if(!$component)mail($developerEmail, 'error file '.__FILE__.', line '.__LINE__.', component is not declared, no way to refreshComponent after update dataset filters',get_globals(),$fromHdrBugs);		
		?><script language="javascript" type="text/javascript">
		window.parent.g('filterGadget').innerHTML=document.getElementById('filterGadget').innerHTML;
		<?php if($component){ ?>
		window.location='bais_01_exe.php?mode=refreshComponent&component=<?php echo $component;?>';
		<?php }?>
		</script><?php
	break;
	case $mode=='updateDataobjectSettings':
		$object='finan_items';
		if($storagemethod=='module'){
			$joins=stripslashes_deep(array_transpose($joins));
			unset($b);
			foreach($joins as $v){
				if(trim($v['FieldLabel']) && !$v['DeleteFieldSetting'])$b[]=$v;
			}
			prn($b);
			$moduleConfig['dataobjects'][$object]['joins']=$b;
			//write to db
			$str=base64_encode(serialize($moduleConfig));
			$rand=md5(time().rand(1000,1000000));
			$ExtractConfig=preg_replace('/<serialized[^>]*>[^<]*<\/serialized>\s*/i',$rand,trim($ExtractConfig));
			if(!stristr($ExtractConfig,$rand))$ExtractConfig=$rand.$ExtractConfig;
			$ExtractConfig=str_replace($rand, '<serialized>'.$str.'</serialized>'."\n",$ExtractConfig);
			q("UPDATE rbase_modules a, rbase_modules_items b SET b.Source='".addslashes($ExtractConfig)."' WHERE a.ID=b.Modules_ID AND b.Types_ID=5 AND a.ID=$cartModuleId", C_SUPER);
			$navigate=true;
			$navigateCount=1;
		}else{
			error_alert('no storage method var');
		}
	break;
	case $mode=='updateSettings':
		//ExtractConfig is present by query from the module, and moduleConfig is present from unserializing it
		//so far I only have a node adminControls to work with
		prn($moduleConfig);
		
		$moduleConfig['adminControls']=stripslashes_deep($adminControls);
		$moduleConfig['settings']=stripslashes_deep($settings);
		
		
		//write to db
		$str=base64_encode(serialize($moduleConfig));
		$rand=md5(time().rand(1000,1000000));
		$ExtractConfig=preg_replace('/<serialized[^>]*>[^<]*<\/serialized>\s*/i',$rand,trim($ExtractConfig));
		if(!stristr($ExtractConfig,$rand))$ExtractConfig=$rand.(trim($ExtractConfig) ? "\n" : '').trim($ExtractConfig);
		$ExtractConfig=str_replace($rand, '<serialized>'.$str.'</serialized>'."\n",$ExtractConfig);
		q("UPDATE rbase_modules a, rbase_modules_items b SET b.Source='".addslashes($ExtractConfig)."' WHERE a.ID=b.Modules_ID AND b.Types_ID=5 AND a.ID=$cartModuleId", C_SUPER);
		prn($qr);
	break;
	/*
	case $mode=='insertIssue':
	case $mode=='updateIssue':
		//error checking - date received must be present, price present, quantity received and exp. date present
		//also Items_ID must be present
		
		$ExpirationDate=date('Y-m-d',strtotime($ExpirationDate));
		$DateReceived=date('Y-m-d',strtotime($DateReceived));
		
		$sql=sql_insert_update_generic($MASTER_DATABASE,'finan_items_issues', ($mode==$insertMode ? 'INSERT INTO' : 'UPDATE'), '', $options);
		$newID=q($sql,O_INSERTID);
		prn($qr);
		
		if($UnitPrice!==$UnitPriceOrig)q("UPDATE finan_items SET UnitPrice='$UnitPrice' WHERE ID=$Items_ID");
		prn($qr);

		if($cbPresent){
			callback(array("useTryCatch"=>false));
		}
		$navigateCount=$count+($mode==$insertMode ? 1 : 0);
		$navigate=true;	
	break;
	*/
	case $mode=='deleteOrder':
	case $mode=='insertOrder':
	case $mode=='updateOrder':
		if($mode=='deleteOrder'){
			//see if paid on
			if(q("SELECT COUNT(*) FROM finan_transactions WHERE Invoices_ID=$Orders_ID AND Payments_ID > 0", O_VALUE))
			error_alert('This order has had payments applied to it and cannot be deleted.  You must first unapply the payments to delete this order');
			$trans=q("SELECT ID, Shipping_ID FROM finan_transactions WHERE Invoices_ID=$Orders_ID", O_COL_ASSOC);
			foreach($trans as $n=>$v){
				if(q("SELECT * FROM finan_shipping WHERE ID='$v' AND ShipDate",O_ROW, ERR_SILENT))error_alert('This order shows it has been shipped either in full or in part and cannot be deleted.  You must first delete the shipping date(s) for the order to delete it');
			}
			foreach($trans as $n=>$v){
				if(q("SELECT * FROM mps_subscriptions a, mps_SubscriptionsTransactionsIssues b WHERE a.ID=b.Subscriptions_ID AND a.Transactions_ID=$n", O_ARRAY, ERR_SILENT))error_alert('This order is a subscription and is the basis for one or more subsequent fulfilled subscriptions.  It cannot be deleted without first deleting dependent orders');
			}
			q("DELETE FROM finan_invoices WHERE ID=$Orders_ID");
			q("DELETE FROM finan_transactions WHERE Invoices_ID=$Orders_ID");
			foreach($trans as $n=>$v)$a[]=$n;
			q("DELETE FROM mps_subscriptions WHERE Transactions_ID IN(".implode(',',$a).")", ERR_SILENT);
			?><script language="javascript" type="text/javascript">
			window.parent.g('r_<?php echo $Orders_ID?>').style.display='none';
			</script><?php
			$assumeErrorState=false;
			exit;		
		}
		//error checking
		if(!$Clients_ID)error_alert('Select a '.$settings['ClientWord']);
		$Active=1;
		if(!isset($Active))$Active='0';
		if(!isset($ToBeExported))$ToBeExported='0';
		if(strtotime($InvoiceDate)==-1 || strtotime($InvoiceDate)==false){
			error_alert('Enter a valid date for this order');
		}
		$InvoiceDate=date('Y-m-d',strtotime($InvoiceDate));
		if(!$InvoiceNumber)error_alert('Enter a valid order number');
		
		//NOTE; we need to integrate adminControls[modifyOrder] settings to what is modified
		if($mode==$insertMode)unset($ID);
		$sql=sql_insert_update_generic($MASTER_DATABASE,'finan_invoices', ($mode==$insertMode ? 'INSERT INTO' : 'UPDATE'), '', $options);
		$fl=__FILE__; $ln=__LINE__;
		$x=q($sql, O_INSERTID);
		prn($qr);
		if($mode==$insertMode)$ID=$x;
		
		$navigateCount=$count+($mode==$insertMode?1:0);
		$navigate=true;
		
		if($cbPresent)callback(array('useTryCatch'=>true));
	break;
	case $mode=='insertAd':
	case $mode=='updateAd':
	case $mode=='deleteAd':
		if($mode=='deleteAd'){
			$Orders_ID=q("SELECT Orders_ID FROM publisher_ads WHERE ID=$Ads_ID", O_VALUE);
			if(q("SELECT COUNT(*) FROM publisher_ads WHERE Orders_ID=$Orders_ID AND ID!='$Ads_ID'", O_VALUE)){
				//order has other ads
			}else q("DELETE FROM publisher_ads_orders WHERE ID=$Orders_ID");
			q("DELETE FROM publisher_ads WHERE ID=$Ads_ID");
			q("DELETE FROM publisher_ads_content WHERE Ads_ID=$Ads_ID");
			q("DELETE FROM publisher_ads_runs WHERE Ads_ID=$Ads_ID");
			break;
		}
		//error checking
		if(!$RunMethod)error_alert('Select a run method: One-time, RTFN, or RTFN No-bill');
		$Categories_ID=q("SELECT ID FROM publisher_ads_categories WHERE Name='$Categories_ID'", O_VALUE);
		if(!$Categories_ID)error_alert('Select a category');
		if(!trim($AdTitle) || !trim($Content))error_alert('Enter the ad content!');
		if(!$Clients_ID)error_alert('You need to select the contact for this ad');
		if(!$Weeks || !$StartDate)error_alert('Select start date and #weeks to run');
		$EndDate=date('Y-m-d', strtotime($StartDate) + ($Weeks)*(24*3600*7) -1);
		
		if($chargeNow){
			if(!$cardname || !$cardnumber)error_alert('Enter a name and card number - or uncheck "Charge this card now" on the Invoice Info tab');
			if(!$expmonth || !$expyear)error_alert('Enter a card expiration date - or uncheck "Charge this card now" on the Invoice Info tab');
			if(!$cardaddress || !$cardcity || !$cardstate || !$cardzip)error_alert('enter an address for the credit card - or uncheck "Charge this card now" on the Invoice Info tab');
		}
		if(!isset($Active))$Active='0';
		if(!isset($Approved))$Approved='0';

		ob_start();

		//pull contact-client data
		if(!($a=q("SELECT
			a.ID AS Contacts_ID,
			c.ID AS Clients_ID,
			c.ClientName AS ShippingCompany,
			a.FirstName AS ShippingFirstName,
			a.LastName AS ShippingLastName,
			c.ShippingAddress,
			c.ShippingCity,
			c.ShippingState,
			c.ShippingZip,
			c.ShippingCountry,
			a.FirstName AS BillingFirstName,
			a.LastName AS BillingLastName,
			IF(a.BusAddress!='',a.BusAddress,a.HomeAddress) AS BillingAddress,
			IF(a.BusCity!='',a.BusCity,a.HomeCity) AS BillingCity,
			IF(a.BusState!='',a.BusState,a.HomeState) AS BillingState,
			IF(a.BusZip!='',a.BusZip,a.HomeZip) AS BillingZip,
			IF(a.BusPhone!='',a.BusPhone,a.HomePhone) AS BillingPhone,
			a.Email AS BillingEmail,
			HomeZip /* -- used as LocationZip -- */

			FROM addr_contacts a, finan_ClientsContacts b, finan_clients c
			WHERE
			a.ID=b.Contacts_ID AND 
			b.Type='Primary' AND
			b.Clients_ID=c.ID AND 
			c.ID=$Clients_ID", O_ROW))){
			mail($developerEmail, 'error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
			error_alert('Unable to find clients_id - abnormal error, staff notified');
		}
		print_r($qr);
		extract($a);
		if($ChargeNow){
			//we are going to insert an order
			$Invoices_ID=q("INSERT INTO finan_invoices SET
			CreateDate =NOW(),
			Creator ='system',
			ToBeEmailed =1,
			InvoiceDate =NOW(),
			SessionKey ='".$_SESSION['sessionKey']."',
			Category ='Classified Ad',
			Clients_ID ='".addslashes($a['Clients_ID'])."',
			Accounts_ID =".FINAN_INVOICE_ACCOUNTS_ID.", /* in config.console.php */
			ShippingFirstName ='$FirstName',
			ShippingLastName ='$LastName',
			ShippingCompany ='".addslashes($a['ShippingCompany'])."',
			ShippingAddress ='".addslashes($a['ShippingAddress'])."',
			ShippingCity ='".addslashes($a['ShippingCity'])."',
			ShippingState ='".addslashes($a['ShippingState'])."',
			ShippingZip ='".addslashes($a['ShippingZip'])."',
			ShippingCountry ='".addslashes($a['ShippingCountry'])."',
			ShippingPhone ='".addslashes($a['ShippingPhone'])."',
			ShippingEmail ='".addslashes($a['ShippingEmail'])."',
			BillingFirstName ='".addslashes($a['BillingFirstName'])."',
			BillingLastName ='".addslashes($a['BillingLastName'])."',
			BillingAddress ='".addslashes($a['BillingAddress'])."',
			BillingCity ='".addslashes($a['BillingCity'])."',
			BillingState ='".addslashes($a['BillingState'])."',
			BillingZip ='".addslashes($a['BillingZip'])."',
			BillingPhone ='".addslashes($a['BillingPhone'])."',
			BillingEmail ='".addslashes($a['BillingEmail'])."',
			CCConfNumber ='$CCConfNumber',
			ClientMessage ='Thank you',
			InvoiceSummary ='Classified ad order',
			Contacts_ID=$Contacts_ID", O_INSERTID);
			print_r($qr);
			q("UPDATE finan_invoices SET InvoiceNumber=$Invoices_ID WHERE ID=$Invoices_ID");
			print_r($qr);

			if(!($printItem=q("SELECT * FROM finan_items WHERE ID=".FINAN_ITEM_PRINT, O_ROW))){
				mail($developerEmail,'error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
				error_alert('Unable to find line item for print media');
			}
			print_r($qr);
			if(!($onlineItem=q("SELECT * FROM finan_items WHERE ID=".FINAN_ITEM_ONLINE, O_ROW))){
				mail($developerEmail,'error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
				error_alert('Unable to find line item for online media');
			}
			print_r($qr);
		}
		
		
		
		if($mode==$updateMode){
			if($Billing_ID){
				q("UPDATE finan_billing SET
				Editor='system',
				Clients_ID='$Clients_ID',
				BillingNameOnCard='$cardname',
				BillingAddress='$cardaddress',
				BillingCity='$cardcity',
				BillingState='$cardstate',
				BillingZip='$cardzip',
				CCnumber='$cardnumber',
				CCexpyear='$expyear',
				CCexpmonth='$expmonth',
				CCbackthree='$cvv',
				Notes='added by usemod_01_exe.php line ".__LINE__."'
				WHERE ID=$Billing_ID", O_INSERTID);
				print_r($qr);
			}else{
				$Billing_ID=q("INSERT INTO finan_billing SET
				CreateDate=NOW(),
				Creator='system',
				Clients_ID='$Clients_ID',
				BillingNameOnCard='$cardname',
				BillingAddress='$cardaddress',
				BillingCity='$cardcity',
				BillingState='$cardstate',
				BillingZip='$cardzip',
				CCnumber='$cardnumber',
				CCexpyear='$expyear',
				CCexpmonth='$expmonth',
				CCbackthree='$cvv',
				Notes='added by usemod_01_exe.php line ".__LINE__."'", O_INSERTID);
				print_r($qr);
			}

			q("UPDATE publisher_ads SET
			Active=$Active,
			Categories_ID=$Categories_ID,
			Billing_ID='$Billing_ID',
			Approved=$Approved,
			RunMethod='$RunMethod',
			Clients_ID=$Clients_ID WHERE ID=$ID");
			print_r($qr);
			
			q("UPDATE publisher_ads_content SET
			Title='$AdTitle',
			Content='$Content',
			Editor='system'
			WHERE Ads_ID=$ID AND Type='default'", O_INSERTID);
			print_r($qr);
			
			//print
			if($ChargeNow){
				$idx++;
				$Transactions_ID=q("INSERT INTO finan_transactions SET
				CreateDate=NOW(),
				Creator='system',
				Idx=$idx,
				Invoices_ID=$Invoices_ID,
				Items_ID=".addslashes($printItem['ID']).",
				Accounts_ID=".addslashes($printItem['Accounts_ID']).",
				SKU='".addslashes($printItem['SKU'])."',
				Name='".addslashes($printItem['Name'])."',
				Description='".addslashes($printItem['Description'])."',
				Quantity=1,
				UnitPrice='".round($PriceStatic,2)."',
				Extension='".round($PriceStatic,2)."'",O_INSERTID);
				print_r($qr);
			}
			q("UPDATE publisher_ads_runs SET 
			".($Transactions_ID ? "Transactions_ID=$Transactions_ID," : '')."
			StartDate='$StartDate',
			EndDate='$EndDate'
			WHERE Ads_ID='$ID' AND Medias_ID=1 /* Print */");
			print_r($qr);

			//online
			if($ChargeNow){
				$idx++;
				$Transactions_ID=q("INSERT INTO finan_transactions SET
				CreateDate=NOW(),
				Creator='system',
				Idx=$idx,
				Invoices_ID=$Invoices_ID,
				Items_ID=".addslashes($onlineItem['ID']).",
				Accounts_ID=".addslashes($onlineItem['Accounts_ID']).",
				SKU='".addslashes($onlineItem['SKU'])."',
				Name='".addslashes($onlineItem['Name'])."',
				Description='".addslashes($onlineItem['Description'])."',
				Quantity=1,
				UnitPrice='0',
				Extension='0'",O_INSERTID);
				print_r($qr);
			}
			q("UPDATE publisher_ads_runs SET 
			".($Transactions_ID ? "Transactions_ID=$Transactions_ID," : '')."
			StartDate='$StartDate',
			EndDate='$EndDate'
			WHERE Ads_ID='$ID' AND Medias_ID=2 /* Online */");
			print_r($qr);
		}else{
			$Orders_ID=q("INSERT INTO publisher_ads_orders SET
			Notes=''", O_INSERTID);
			print_r($qr);
			
			$Billing_ID=q("INSERT INTO finan_billing SET
			CreateDate=NOW(),
			Creator='system',
			Clients_ID='$Clients_ID',
			BillingNameOnCard='$cardname',
			BillingAddress='$cardaddress',
			BillingCity='$cardcity',
			BillingState='$cardstate',
			BillingZip='$cardzip',
			CCnumber='$cardnumber',
			CCexpyear='$expyear',
			CCexpmonth='$expmonth',
			CCbackthree='$cvv',
			Notes='added by usemod_01_exe.php line ".__LINE__."'", O_INSERTID);
			print_r($qr);
			
			$Ads_ID=q("INSERT INTO publisher_ads SET 
			Types_ID=1 /* Classifieds */,
			Orders_ID='$Orders_ID',
			Categories_ID='$Categories_ID',
			Billing_ID='$Billing_ID',
			Approved=$Approved,
			RunMethod='$RunMethod',
			LocationZip='$HomeZip',
			Clients_ID='$Clients_ID'", O_INSERTID);
			print_r($qr);
			
			$Content_ID=q("INSERT INTO publisher_ads_content SET
			Title='$AdTitle',
			Content='$Content',
			CreateDate=NOW(),
			Creator='system',
			Ads_ID=$Ads_ID", O_INSERTID);
			print_r($qr);
			
			//print
			if($ChargeNow){
				$idx++;
				$Transactions_ID=q("INSERT INTO finan_transactions SET
				CreateDate=NOW(),
				Creator='system',
				Idx=$idx,
				Invoices_ID=$Invoices_ID,
				Items_ID=".addslashes($printItem['ID']).",
				Accounts_ID=".addslashes($printItem['Accounts_ID']).",
				SKU='".addslashes($printItem['SKU'])."',
				Name='".addslashes($printItem['Name'])."',
				Description='".addslashes($printItem['Description'])."',
				Quantity=1,
				UnitPrice='".round($PriceStatic,2)."',
				Extension='".round($PriceStatic,2)."'",O_INSERTID);
				print_r($qr);
			}
			q("INSERT INTO publisher_ads_runs SET
			Ads_ID=$Ads_ID,
			Transactions_ID=".($Transactions_ID ? $Transactions_ID : 'NULL').",
			StartDate='$StartDate',
			EndDate='$EndDate',
			Medias_ID=1 /* Print */");
			print_r($qr);
			
			//online
			if($ChargeNow){
				$idx++;
				$Transactions_ID=q("INSERT INTO finan_transactions SET
				CreateDate=NOW(),
				Creator='system',
				Idx=$idx,
				Invoices_ID=$Invoices_ID,
				Items_ID=".addslashes($onlineItem['ID']).",
				Accounts_ID=".addslashes($onlineItem['Accounts_ID']).",
				SKU='".addslashes($onlineItem['SKU'])."',
				Name='".addslashes($onlineItem['Name'])."',
				Description='".addslashes($onlineItem['Description'])."',
				Quantity=1,
				UnitPrice='0',
				Extension='0'",O_INSERTID);
				print_r($qr);
			}
			q("INSERT INTO publisher_ads_runs SET
			Ads_ID=$Ads_ID,
			Transactions_ID=".($Transactions_ID ? $Transactions_ID : 'NULL').",
			StartDate='$StartDate',
			EndDate='$EndDate',
			Medias_ID=2 /* Online */");
			print_r($qr);
		}
		$out=ob_get_contents();
		ob_end_clean();
		mail($developerEmail,'output',$out,$fromHdrBugs);
		if($cbPresent){
			callback(array("useTryCatch"=>false));
		}
		if($navMode=='insert'){
			?><script language="javascript" type="text/javascript">
			window.parent.g('Title').innerHTML=' ';
			window.parent.g('Heading').innerHTML=' ';
			</script><?php
		}
		//navigate interface
		$navigate=true;
		$navigateCount=$count+($mode==$insertMode ? 1 : 0);
	break;	
	case $mode=='insertClient':
	case $mode=='updateClient':
	case $mode=='deleteClient':
	
		$dataset='Member';
		if($mode=='deleteClient'){
			q("DELETE FROM finan_clients WHERE ID=$Clients_ID");
			$Contacts=q("SELECT Contacts_ID FROM finan_ClientsContacts WHERE Clients_ID=$Clients_ID", O_COL);
			if(count($Contacts)!==1){
				mail($developerEmail,'notice file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
			}
			q("DELETE FROM finan_ClientsContacts WHERE Clients_ID=$Clients_ID");
			q("DELETE FROM addr_contacts WHERE ID IN('".implode("','",$Contacts)."')");
			?><script language="javascript" type="text/javascript">
			window.parent.g('r_<?php echo $Clients_ID?>').style.display='none';
			</script><?php
			$assumeErrorState=false;
			exit;
		}
		/* We assume we have ID=Client ID, Contacts_ID is present; we will need to know if we have primary as the relationship.  See ../_todo.txt for 6/10/2009.  Also see ../_docs.txt for 6/11/2009
		
		*/
		//error checking: password on insert, unique company name if isset
		//if(isset($Statuses_ID) && !strlen($Statuses_ID))error_alert('Select a status');
		if($mode==$insertMode){
			//2013-02-15: these are the three I have developed
			$userNameTables=array('finan_clients'=>'UserName','addr_contacts'=>'UserName','bais_universal'=>'un_username');
			foreach($userNameTables as $n=>$v){
				ob_start();
				q("SELECT COUNT($v) FROM $n", O_VALUE, ERR_ECHO);
				$err=ob_get_contents();
				ob_end_clean();
				if(!$err)$tables[]=array('table'=>$n,'field'=>$v);		
			}
			unset($err);
			$newClientUserName=sql_autoinc_text($tables, NULL, array($FirstName,$LastName), array('where'=>$where));

			if(!isset($Password))$AutoCreatePassword=true;
			if($AutoCreatePassword){
				$Password=rand(100,999).'-'.rand(100,999);
				$PasswordMD5=md5($Password);
			}else{
				if(!strlen($Password))error_alert('A password is required (on the settings tab)');
				if($Password!==$Password2)error_alert('Passwords must match');
				$PasswordMD5=md5(stripslashes($Password));
			}
		}
		
		$PageName='default';
		$ShowEmailPublicly=($ShowEmailPublicly ? '1' : '0');
		if(!$ToBeExported)$ToBeExported='0';

		if(!function_exists('companynameformat')){
			function companynameformat($fn,$mn, $ln){
				return strtoupper($fn,0,1).strtolower($fn,1,100).($mn ? ' '.strtoupper($mn,0,1).strtolower($mn,1,100) : '').' '.strtoupper($ln,0,1).strtolower($ln,1,100);
			}
		}
		q("UPDATE finan_clients SET 
		ResourceType=1,
		Active=$Active,
		
		/* ------ if a quasi resource, username is not yet present -------- */
		".
		($mode==$insertMode ? 
		"UserName='$newClientUserName',
		Password='$Password',
		PasswordMD5='$PasswordMD5'," : 
		''
		)."
		
		".(isset($Terms_ID) ? "Terms_ID='$Terms_ID'," : '')."
		".(isset($Statuses_ID) ? "Statuses_ID='$Statuses_ID'," : '')."
		".(isset($ClientAccountNumber) ? "ClientAccountNumber='$ClientAccountNumber'," : '')."
		".($mode==$updateMode ? "Editor='".$_SESSION['systemUserName']."'," : '')."
		
		/* ----- client name='alias' of the company name, unique ----- */
		ClientName='".(isset($ClientName) ? $ClientName : (isset($Company) ? $Company : companynameformat($FirstName, $MiddleName, $LastName)))."',
		
		/* ----- full legal company name, NOT unique ------ */
		".
		($mode==$insertMode ? 
		("CompanyName='".(trim($Company) ? $Company : (isset($CompanyName) ? $CompanyName : companynameformat($FirstName, $MiddleName, $LastName)))."',") :
		(isset($CompanyName) ? "CompanyName='".$CompanyName."'," : '')
		)."
		
		".(isset($Category) || isset($defaultClientCategory) ? "Category='".(isset($Category) ? $Category : $defaultClientCategory)."'," : '')."
		/* logic needed here */
		Mobile='".(isset($Mobile) ? $Mobile : $HomeMobile)."',
		Email=".( trim(isset($Email) ? $Email : $PersonalEmail) ? '"'.(isset($Email) ? $Email : $PersonalEmail).'"' : 'NULL' ).",
		".(isset($EmailCC) ? "EmailCC='$EmailCC'," : '')."
		".(isset($ShowEmailPublicly) ? "ShowEmailPublicly='$ShowEmailPublicly'," : '')."

		".(isset($Phone) ? "Phone='$Phone'," : '')."
		".(isset($Phone2) ? "Phone2='$Phone2'," : '')."
		".(isset($Fax) ? "Fax='$Fax'," : '')."
		/* shipping address */
		".(isset($ShippingAddress) ? "ShippingAddress='$ShippingAddress'," : '')."
		".(isset($ShippingAddress2) ? "ShippingAddress2='$ShippingAddress2'," : '')."
		".(isset($ShippingCity) ? "ShippingCity='$ShippingCity'," : '')."
		".(isset($ShippingState) ? "ShippingState='$ShippingState'," : '')."
		".(isset($ShippingZip) ? "ShippingZip='$ShippingZip'," : '')."
		".(isset($ShippingCountry) ? "ShippingCountry='$ShippingCountry'," : '')."

		/* web addresses */
		".(isset($WebPage) ? "WebPage='$WebPage'," : '')."
		".(isset($ContactPage) ? "ContactPage='$ContactPage'," : '')."
		".(isset($LandingPage) ? "LandingPage='$LandingPage'," : '')."

		/* metatags */
		".(isset($MetaTitle) ? "MetaTitle='$MetaTitle'," : '')."
		".(isset($Description) ? "Description='$Description'," : '')."
		".(isset($Keywords) ? "Keywords='$Keywords'," : '')."
		".(isset($ShowCard) ? "ShowCard='$ShowCard'," : '')."

		Address1='".(isset($Address1) ? $Address1 : $BusAddress)."',
		Address2='".(isset($Address2) ? $Address2 : '')."',
		City='".(isset($City) ? $City : $BusCity)."',
		State='".(isset($State) ? $State : $BusState)."',
		".(isset($Country) ? "Country='$Country'," : '')."
		Zip='".(isset($Zip) ? $Zip : $BusZip)."'
		WHERE ID=$ID");
		
		//buffer
		$Clients_ID=$ID;

		//update web page
		$there=q("SELECT ID FROM finan_clients_websites WHERE ID='$ID'", O_VALUE);
		$sql=sql_insert_update_generic($MASTER_DATABASE,'finan_clients_websites', ($there ? 'UPDATE' : 'INSERT'));
		q($sql);
		prn($qr);

		//--------- data conversions ----------
		$ID=$Contacts_ID;
		$Company=(isset($Company) ? $Company : $CompanyName);
		if(!isset($Category) && isset($defaultContactCategory))$Category=$defaultContactCategory;
		$Email=( isset($PersonalEmail) ? $PersonalEmail : $Email);
		if(!trim($Email))$Email='PHP:NULL';

		if($Contacts_ID && $contact=q("SELECT * FROM addr_contacts WHERE ID='$Contacts_ID'", O_ROW)){
			//2009-11-25: ADDED THIS in order to control EEAT
			if($PersonalEmailVerified){
				$EnrollmentAuthToken='php:NULL';
				$EnrollmentAuthDuration=7;
			}else{
				$EnrollmentAuthToken=md5($contact['PasswordMD5'] . $MASTER_PASSWORD);
			}
			$sql=sql_insert_update_generic($MASTER_DATABASE, 'addr_contacts', 'UPDATE', $options=array(
				'allowHumanDatetimeConversion'=>1,
				'setCtrlFields'=>1));
			q($sql);
			prn($qr);
		}else{
			//get username - finan_clients is the seed source
			$UserName=$newContactUserName=( $mode==$insertMode ? $newClientUserName : q("SELECT UserName FROM finan_clients WHERE ID=$Clients_ID", O_VALUE) );
			if(q("SELECT UserName FROM addr_contacts WHERE UserName='$newContactUserName'", O_VALUE)){
				//usernames will be out of synch
				$UserName=$newContactUserName=sql_autoinc_text('addr_contacts','UserName', preg_replace('/[0-9]+$/','',$newContactUserName));
			}
			if($mode==$insertMode && isset($Password)){
				$PasswordMD5=md5(stripslashes($Password));
			}
			//2009-11-25: ADDED THIS in order to control EEAT
			if($PersonalEmailVerified){
				$EnrollmentAuthToken='php:NULL';
				$EnrollmentAuthDuration=7;
			}else{
				$EnrollmentAuthToken=md5($PasswordMD5 . $MASTER_PASSWORD);
			}
			unset($ID);
			$sql=sql_insert_update_generic($MASTER_DATABASE, 'addr_contacts', 'INSERT', $options=array(
				'allowHumanDatetimeConversion'=>1,
				'setCtrlFields'=>1));
			prn($sql);

			$Contacts_ID=q($sql, O_INSERTID, ERR_ECHO);
				
			//remove old 
			q("DELETE FROM finan_ClientsContacts WHERE Clients_ID=$Clients_ID AND Type='Primary'");
			prn($qr);
			
			//insert new
			q("INSERT INTO finan_ClientsContacts SET Clients_ID=$Clients_ID, Contacts_ID=$Contacts_ID, Type='Primary', Notes='No contact present, contact added'");
			prn($qr);
		}
		//check for username synch
		if(!($a=q("SELECT a.UserName, c.UserName AS ContactUserName, a.ID, c.ID as ContactID FROM finan_clients a LEFT JOIN finan_ClientsContacts b ON a.ID=b.Clients_ID AND b.Type='Primary' LEFT JOIN addr_contacts c ON b.Contacts_ID=c.ID", O_ROW)) || $a['UserName']!=$a['ContactUserName']){
			mail($developerEmail, 'error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
		}
		//2009-10-18: access if module present
		if($moduleConfig['1.0']['addons']){
			//remediate
			$qx['useRemediation']=true;
			$qx['tableList']=array('addr_access','addr_ContactsAccess');
			$values=q("SELECT * FROM addr_access", O_COL_ASSOC);
			q("DELETE FROM addr_ContactsAccess WHERE Contacts_ID=$Contacts_ID");
			foreach($Accesses as $n=>$v)q("INSERT INTO addr_ContactsAccess SET Contacts_ID=$Contacts_ID, Access_ID=$n");
			
		}
		
		//2013-07-08: delete and the reset their accesses
		q("DELETE FROM addr_ContactsAccess WHERE Contacts_ID=$Contacts_ID");
		prn($qr);
		if(count($access)){
			foreach($access as $n=>$v){
				q("INSERT INTO addr_ContactsAccess SET Contacts_ID=$Contacts_ID, Access_ID=$v");
				prn($qr);
			}
		}
		
		if($cbPresent){
			if($cbTable=='finan_clients' || $cbTable=='cms1_articles'){
				$cbValue=$ID;
				//format Fullman, Samuel - Compass Point Media
				if($CompanyName && strtolower($CompanyName)!==strtolower($FirstName . ' ' . $LastName) && strtolower($CompanyName)!==strtolower($FirstName . ' ' .$MiddleName.' ' .$LastName)){
					$cbLabel=$LastName. ', '.$FirstName.' - '.$CompanyName;
				}else{
					$cbLabel=$LastName. ', '.$FirstName;
				}
			}
			callback(array(
				"useTryCatch"=>false, 
				'cbRetainParent'=>($navMode=='insert' && !$cbSelect ? true : false)
			));
		}
		$navigateCount=$count+($mode==$insertMode?1:0);
		$navigate=true;
		$$object='Clients_ID';
		if($navMode=='insert'){
			//generate the next quasi resource and set things up for navigate() to work
			$$object=$ID=quasi_resource_generic($MASTER_DATABASE, 'finan_clients', substr(date('YmdHis').rand('10000','999999'),3,16));
			//set up parent sets array with new quasi resource ID
			?><script language="javascript" type="text/javascript">
			window.parent.sets['ID']='<?php echo $ID;?>';
			window.parent.ResourceToken='<?php echo $ResourceToken;?>';
			</script><?php
		}else if($navMode=='remain'){
			?><script language="javascript" type="text/javascript">
			window.parent.sets['Contacts_ID']='<?php echo $Contacts_ID;?>';
			</script><?php
		}
	break;
	case $mode=='addClientGroup':
		if(!$ObjectName)error_alert('Abnormal error, field objectname not present');
		if(!$ID)error_alert('Abnormal error, contact or client id not present');
		//make sure there is an id for each
		if(trim($NewGroup)){
			$NewGroup=explode(',',$NewGroup);
			foreach($NewGroup as $n=>$v){
				if(!trim($v))unset($NewGroup[$n]);
				$NewGroup[$n]=trim($v);
				if($a=q("SELECT ID FROM addr_groups WHERE Name='".trim($v)."'", O_VALUE)){
					//OK
				}else{
					//annotate existing array
					$Groups[]=q("INSERT INTO addr_groups SET CreateDate=NOW(), Creator='".sun()."', Name='".trim($v)."', Category='(Uncategorized)'", O_INSERTID);
				}
			}
		}
		$delete=q("SELECT Groups_ID, 1 FROM addr_ContactsGroups WHERE Contacts_ID=$ID AND Contacts_DataSource='$ObjectName'", O_COL_ASSOC);
		prn($qr);
		if(!$delete)$delete=array();
		foreach($Groups as $Groups_ID){
			if($delete[$Groups_ID]){
				//no real need to update
				unset($delete[$Groups_ID]);
			}else{
				q("INSERT INTO addr_ContactsGroups SET Contacts_ID='$ID', Contacts_DataSource='$ObjectName', Groups_ID='$Groups_ID', CreateDate=NOW(), Creator='".sun()."'");
			}
			prn($qr);
		}
		prn($Groups);
		if(count($delete)){
			q("DELETE FROM addr_ContactsGroups WHERE Contacts_ID=$ID AND Contacts_DataSource='$ObjectName' AND Groups_ID IN(".implode(',',array_keys($delete)).")");
			prn($qr);
		}
		if($cbPresent){
			$options=array('useTryCatch'=>false);
			callback($options);
		}
		?><script language="javascript" type="text/javascript">
		//window.parent.close();
		</script><?php
	break;
	case $mode=='removeClientGroup':
		if(!$ObjectName)error_alert('variable ObjectName not passed');
		q("DELETE FROM addr_ContactsGroups WHERE Contacts_ID='".($_REQUEST['Clients_ID'] ? $_REQUEST['Clients_ID'] : $_REQUEST['Contacts_ID'])."' AND Contacts_DataSource='$ObjectName' AND Groups_ID=$Groups_ID");
	break;
	case $mode=='insertAlbum':
	case $mode=='updateAlbum':
	case $mode=='deleteAlbum':
		if($mode=='deleteAlbum'){
			//translate for focus view Ctrl-D call vs. list view Objects_ID
			if(!$Albums_ID)$Albums_ID=$ID;
			q("DELETE FROM ss_albums WHERE ID='$Albums_ID'");
			if($pictures=q("SELECT Pictures_ID FROM ss_AlbumsPictures WHERE Albums_ID=$Albums_ID", O_COL)){
				q("DELETE FROM ss_AlbumsPictures WHERE Albums_ID=$Albums_ID");
				q("DELETE FROM ss_pictures WHERE ID IN('".implode("','",$pictures)."')");
			}
			?><script type="text/javascript" language="javascript">
			window.parent.g('r_<?php echo $Albums_ID?>').style.display='none';
			</script><?php

			$navigate=true;
			$navigateCount=1;
			break;
		}
		if(!trim($Name))error_alert('Enter a name for this album');
		//if we are saving this as a new album run this code first
		if($SaveAsNew){
			//user wants to save this album as a new one
			$a=q("SELECT * FROM ss_albums WHERE ID='$ID'", O_ROW);
			unset($a['ID']);
			$updateName = $a['Name']=stripslashes($Name) . ($Name==$OriginalName ? ' (2)' : '');
			$a['Location']=stripslashes($Location);
			$a['Description']=stripslashes($Description);
			$a['CreateDate']=date('Y-m-d H:i:s');
			$query="INSERT INTO ss_albums SET ";
			foreach($a as $n=>$v){
				$query.=$n . "='" . addslashes($v) . "', ";
			}
			$query=preg_replace('/, $/','',$query);
			$NewAlbums_ID=q($query, O_INSERTID);
			//If the Album has Pictures, get an array of these pictures ID
			if($pics=q("SELECT Pictures_ID FROM ss_AlbumsPictures WHERE Albums_ID='$ID'", O_COL)){
				foreach($pics as $OldPictures_ID){
					//ForEach of the Pictures in the Old Album, Copy their entry as new pictures. save the new ID
					$OldPicture = q("SELECT * FROM ss_pictures WHERE ID='".$OldPictures_ID."'", O_ROW);
					unset($OldPicture['ID']);
					$OldPicture['CreateDate']=date('Y-m-d H:i:s');
					$query="INSERT INTO ss_pictures SET ";
					foreach($OldPicture as $n=>$v){
						$query.=$n . "='" . addslashes($v) . "', ";
					}
					$query=preg_replace('/, $/','',$query);
					$NewPictures_ID=q($query, O_INSERTID);
					//now we need to join album to picture for each new picture we just created
					q("INSERT INTO ss_AlbumsPictures SET Description='".$Description."', Albums_ID='".$NewAlbums_ID."', Pictures_ID='".$NewPictures_ID."'");
					//update the id handles in the frames
					?><script language="javascript" type="text/javascript">
					window.parent.g('r_<?php echo $OldPictures_ID?>').setAttribute('ID','r_<?php echo $NewPictures_ID?>');
					window.parent.g('title_<?php echo $OldPictures_ID?>').setAttribute('ID','title_<?php echo $NewPictures_ID?>');
					window.parent.g('imgInset_<?php echo $OldPictures_ID?>').setAttribute('ID','imgInset_<?php echo $NewPictures_ID?>');
					window.parent.g('desc_<?php echo $OldPictures_ID?>').setAttribute('ID','desc_<?php echo $NewPictures_ID?>');
					</script><?php
				}	
			}
			?>
			<script language="javascript" type="text/javascript">
			alert('Your album has been copied to a new album');
			window.parent.detectChange=0;
			window.parent.g('ID').value=<?php echo $NewAlbums_ID;?>;
			window.parent.g('Name').value='<?php echo $updateName?>';
			window.parent.g('OriginalName').value='<?php echo $updateName?>';
			window.parent.g('SaveAsNew').checked=false;
			</script><?php
			$assumeErrorState=false;
			exit;
		}
	
		//fulfill record - insert or update record
		$ResourceType=1;
		$sql=sql_insert_update_generic($MASTER_DATABASE,'ss_albums', 'UPDATE', '', $option);
		$fl=__FILE__; $ln=__LINE__;
		q($sql);
		prn($qr);

		//foreign key value
		$Albums_ID=$ID;

		//handle callback
		if($cbPresent){
			callback(array("useTryCatch"=>false));
		}
		//navigate interface
		$navigate=true;
		$navigateCount=$count+($mode==$insertMode ? 1 : 0);
		$$object='Albums_ID';
		if($navMode=='insert'){
			//generate the next quasi resource and set things up for navigate() to work
			$ResourceToken=substr(date('|YmdHis'),4).rand(10000,99999);
			$$object=$ID=quasi_resource_generic($MASTER_DATABASE, 'ss_albums', $ResourceToken);
			//set up parent sets array with new quasi resource ID
			?><script language="javascript" type="text/javascript">
			window.parent.sets['ID']='<?php echo $ID;?>';
			window.parent.ResourceToken='<?php echo $ResourceToken;?>';
			</script><?php
		}else if($navMode=='remain'){
			?><script language="javascript" type="text/javascript">
			window.parent.sets['Albums_ID']='<?php echo $Albums_ID;?>';
			</script><?php
		}
	break;
	case $mode=='manageAlbums':
		//all previous coding is now in the same component
		require($COMPONENT_ROOT.'/comp_27_albumobjects_v100.php');
	break;
	case $mode=='manageItemPictures':
		//all previous coding is now in the same component
		require($COMPONENT_ROOT.'/comp_15_itemobjects_v100.php');
	break;
	case $mode=='updateEventTCA1';
	case $mode=='insertEventTCA1';
	case $mode=='deleteEventTCA1';
	case $mode=='insertEvent':
	case $mode=='updateEvent':
	case $mode=='deleteEvent':
		if($mode==$deleteMode){
			//translate for focus view Ctrl-D call vs. list view Objects_ID
			if(!$Events_ID)$Events_ID=$ID;

			if(q("SELECT * FROM finan_headers a, finan_transactions b, finan_items c WHERE a.ID=b.Headers_ID AND b.Items_ID=c.ID AND c.SKU='EventItem-$Events_ID'", O_ARRAY))error_alert('This event is used in online purchases; it cannot be deleted.  If you still wish to delete this event, right click on its row, select Report, and delete each purchase first');

			q("DELETE FROM cal_events WHERE ID=$Events_ID");
			q("DELETE FROM finan_items WHERE SKU='EventItem-$Events_ID'");

			?><script type="text/javascript" language="javascript">
			try{
			window.parent.g('r_<?php echo $Events_ID?>').style.display='none';
			}catch(e){}
			</script><?php
			$navigate=true;
			$navigateCount=1;
			break;
		}
		if($saveAsNew){
			unset($ID);
			?><script language="javascript" type="text/javascript">
			window.parent.g('saveAsNew').value='';
			window.parent.opener.location+='';
			</script><?php
		}
		if($settable_parameters['051']['calMultipleCalendars']){
			$inMultiple=true;
			$bufferCal_ID=$Cal_ID;
			if(!count($Cal_ID))error_alert('You must select at least one calendar for this event to be on');
			unset($Cal_ID);
		}

		//error checking
		
		
		//data conversion - modified 2009-08-15 to really use t() in a workable way
		if(!t($StartDate))error_alert('Enter a valid starting date');
		if(!t($Deadline, dironal))error_alert('Enter a correct payment deadline for the event (on the online payment tab), or just leave it blank');
		if(!t($EndDate, dironal))error_alert('Enter a correct ending date (or leave blank if it\'s just one day');
		if($EndDate==$StartDate)$EndDate='';
		if($EndDate && $StartDate>$EndDate)error_alert('You have the end date of the event before the start date!');
		if(!t($StartTime, dironal, f_tdb))error_alert('Enter a correct starting time for the event, or just leave it blank');
		if(!t($EndTime, dironal, f_tdb))error_alert('Enter a correct ending time for the event, or just leave it blank');
		if($Notify || $NotifyInvite){
			if(!$MailProfiles_ID)error_alert('Select a mail profile to use for sending the notification out; or uncheck Notify and Invite options');
			if(!($a=q("SELECT 
				IF(v.Val LIKE '%{events:Description%', 1,
				IF(v.Val LIKE '%{events:Invitation%', 2, 0)) AS Type,
				1 AS Val FROM relatebase_mail_profiles p JOIN relatebase_mail_profiles_vars v ON p.ID=v.Profiles_ID WHERE p.ID=$MailProfiles_ID AND (v.Val LIKE '%{events:Description%' OR v.Val LIKE '%{events:Invitation%')", O_COL_ASSOC))){
				if($NotifyInvite && !$a[2])error_alert('You selected notify and invite; however, the mail profile you have selected does not have {events:InvitationBasic} in the body.  See help tab for further assistance');
				if($NotifyInvite && !$a[1])error_alert('You selected notify by email; the mail profile you select needs to have {events:Description} in the body.  See help tab for further assistance');
			}
		}
		if(!$AllowOnlinePayment)$AllowOnlinePayment='0';
		if(!$AllowMultiplePurchases)$AllowMultiplePurchases='0';
		if(!$ShowOnlyDescription)$ShowOnlyDescription='0';
		$Active=($Inactive ? '0' : '1');
		$ResourceType='1';

		$Cost=trim($Cost);
		$costs=explode("\n",$Cost);
		$a=explode(":",$costs[0]);
		$OneCost=trim($a[count($a)-1]);
		
		$sql=sql_insert_update_generic($MASTER_DATABASE,'cal_events', ($saveAsNew ? 'INSERT INTO' : 'UPDATE'), '', $options);
		$newID=q($sql, O_INSERTID);
		prn($qr);
		if($saveAsNew){
			error_alert('Event has been copied successfully',1);
			$ID=$newID;
		}
		if($inMultiple){
			q("DELETE FROM cal_CalEvents WHERE Events_ID=$ID");
			foreach($bufferCal_ID as $v)q("INSERT INTO cal_CalEvents SET Events_ID=$ID, Cal_ID=$v");
		}
		if(count($attendance)){
			$modAttendance=false;
			foreach($attendance as $n=>$v){
				if($_attendance[$n]==$v)continue;
				$modAttendance=true;
				q("UPDATE addr_ContactsEvents SET Status=$v WHERE Events_ID=$ID AND Contacts_ID=$n");
			}
			if($modAttendance)error_alert('Attendance status(es) updated',1);
		}
		/* ----------------- 2013-07-16 ----------------------
		* standard for item entry is AllowOnlinePurchase=true
		* match criteria is SKU=EventItem-{ID} - not the best
		* it was updated today to provide for deletion, and prevent entry on insert if AllowOnlinePurchase=false
		*/
		//create the finan_item
		if($AllowOnlinePurchases){
			if($mode==$insertMode){
				//enter the item
				$Items_ID=q("INSERT INTO finan_items SET
				SKU='EventItem-$ID'
				Category='Web Events',
				Name='$Name',
				Description='$Name ".($BriefDescription ? ' - '.$BriefDescription : '')."',
				UnitPrice='$OneCost',
				Accounts_ID='$defaultCalEventsAccounts_ID',
				Type='Event participation',
				CreateDate=NOW(),
				Creator='".sun()."'", O_INSERTID);
				prn($qr);
			}else{
				if($Items_ID=q("SELECT ID FROM finan_items WHERE SKU='EventItem-$ID'", O_VALUE)){
					q("UPDATE finan_items SET
					Category='Web Events',
					Name='$Name',
					Description='$Name ".($BriefDescription ? ' - '.$BriefDescription : '')."',
					UnitPrice='$OneCost',
					Accounts_ID='$defaultCalEventsAccounts_ID',
					Type='Event participation',
					EditDate=NOW(),
					Editor='".sun()."',
					SKU='EventItem-$ID'
					WHERE ID=$Items_ID", O_INSERTID);
					prn($qr);
				}else{
					//enter the item
					$Items_ID=q("INSERT INTO finan_items SET
					Category='Web Events',
					Name='$Name',
					Description='$Name ".($BriefDescription ? ' - '.$BriefDescription : '')."',
					UnitPrice='$OneCost',
					Accounts_ID='$defaultCalEventsAccounts_ID',
					Type='Event participation',
					CreateDate=NOW(),
					Creator='".sun()."',
					SKU='EventItem-$ID'", O_INSERTID);
					prn($qr);
				}
			}
		}else{
			if($Items_ID=q("SELECT ID FROM finan_items WHERE SKU='EventItem-$ID'", O_VALUE)){
				if(q("SELECT COUNT(*) FROM finan_transactions WHERE Items_ID=$Items_ID", O_VALUE)){
					//just take it offline, don't delete it
					q("UPDATE finan_items SET
					Active=0,
					Name='$Name',
					Description='$Name ".($BriefDescription ? ' - '.$BriefDescription : '')."',
					UnitPrice='$OneCost',
					Type='Event participation',
					EditDate=NOW(),
					Editor='".sun()."'
					WHERE ID='$Items_ID'");
					prn($qr);
				}else{
					//delete it
					q("DELETE FROM finan_items WHERE ID=$Events_ID");
				}
			}
		}
		//----------------------------------------------------
		
		if(preg_match('/TCA1/',$mode)){
			//start toward a PS system and better - here we'd include instructor etc.
			if($mode==$insertMode){
				$Participants_ID=q("INSERT INTO cal_events_participants SET
				Events_ID=$ID,
				/* this is redundant in the form of Clients_ID in the event record */
				ObjectName='finan_clients',
				Objects_ID='".$_SESSION['cnx'][$acct]['defaultClients_ID']."',
				Relationship='Pilot in Command'", O_INSERTID);
				q("UPDATE cal_events SET Clients_ID='".$_SESSION['cnx'][$acct]['defaultClients_ID']."', EditDate=EditDate WHERE ID=$ID");
				//we should reload the calendar..
			}
		}
		
		if($InviteSend){
			//2010-12-17: this is part of a unified content management system, see my notes in the file cabinet.
			
			//this should not be sent out when the user is copying an event as a new event
			
			
			//create or find the default "mail profile" for this send action - "I am the profile that sends RSVPs on cal_events".  A profile could replace a lot of fields, or default a lot of the field values at least, of the fields on the Invitation tab.
			
			//create send batch - kind of a "ticket" on this send event.  Main issues we're concerned about are: 1. who sent to, 2. criteria that selected them at that time, 3. what was sent
			$Batches_ID=q("INSERT INTO relatebase_content_batches SET
			ContentObject='cal_events',
			ContentKey='$ID',
			Network='Email',
			FromName=' here I need to implement login via cgi into the console as an option ',
			FromEmail=' system variable ',
			ReplyToName=' enhancements as was in the mail sender ',
			ReplyToEmail=' ditto ',
			BounceName=' best be that person, OR could be an administrator ',
			BounceEmail=' ditto ',
			StartTime=NOW(),
			BatchNotes='Added via exe page on event ".($mode==$insertMode?'addition':'modification').", line ".__LINE__."',
			CreateDate=NOW(),
			Creator='".$_SESSION['systemUserName']."'", O_INSERTID);
			
			//who are we sending this out to? - as of 2010-12-17 still very clumsy
			$InviteList=q("SELECT ID FROM addr_contacts", O_COL);
			
			//join the batch ID to the contacts being sent; note that this doesn't discriminate against non-valid email addresses or blanks
			q("INSERT INTO relatebase_BatchesContacts(Batches_ID, Contacts_ID, Email, AuthToken, CreateDate, Creator)
			SELECT ($Batches_ID, ID, Email, MD5(RANDOM()), NOW(), '".$_SESSION['systemUserName']."') FROM addr_contacts WHERE ID IN($InviteList)
			/* for now we use the addr_contacts all-or-nothing filter */
			AND (NewsletterOK=1)");
			
			//send to processor for mailing
			require($CONSOLE_ROOT.'/components/comp_700_processor_v100.php');
		}else if($Notify || $NotifyInvite){
			//pull the profile by query and groom the array as request
			$a=q("SELECT * FROM _v_relatebase_mail_profiles WHERE ID=$MailProfiles_ID", O_ROW);
			$event=q("SELECT * FROM cal_events WHERE ID=$ID", O_ROW);
			foreach($event as $n=>$v){
				unset($event[$n]);
				$event[strtolower($n)]=$v;
			}
			$timeFieldMap=array(
				'startdate'=>'Date',
				'enddate'=>'Ending Date',
				'starttime'=>'Time',
				'endtime'=>'Ending Time',
			);
			$contentFields=array('Content','Subject');
			foreach($contentFields as $field){
				if(preg_match_all('/\{events:([a-z0-9]+)(:([^}]+))*\}/i',$a[$field],$m)){
					//prn($m);
					foreach($m[1] as $n=>$v){
						if(preg_match('/^invitation(.*)/i',$v,$m2)){
							//2013-12-15 we have no format for this yet
							if(!$NotifyInvite){
								$replace='';
							}else{
								$method=strtolower($m2[1]);
								if($method=='basic'){
									ob_start();
									$src='/index_01_exe.php?location=JULIET_COMPONENT_ROOT&file=calendar_v200.php&mode=componentControls&submode=eventHandle&open=1&Events_ID='.$ID.'&key=';
									$linkBase='/cgi/login?UN={UserName}&PW={PasswordMD5}&src='.urlencode($src);
									?><!-- Invitation widget from <?php echo end(explode('/',__FILE__));?> line <?php echo __LINE__;?> -->
									<span id="eventInvitation">
									<h3>Select an option</h3>
									<a class="button willAttend" href="<?php echo $linkBase . attendance_acc;?>">I will be attending this event</a><br />
									<a class="button mayAttend" href="<?php echo $linkBase . attendance_tent;?>">I may be attending this event</a><br />
									<a class="button willNotAttend" href="<?php echo $linkBase . attendance_dec;?>">I will not be attending this event</a><br />
									</span><?php
									$replace=str_replace("\t",'',ob_get_contents());
									ob_end_clean();
								}else{
									
								}
							}
							$a[$field]=str_replace($m[0][$n],$replace,$a[$field]);
						}else if(preg_match('/^startdate|enddate|starttime|endtime$/i',$v,$m2)){
							if(trim($event[strtolower($m2[0])],' 0-:')){
								$replace='<strong>'.$timeFieldMap[strtolower($m2[0])].'</strong>: '.
								date(
									strlen($event[strtolower($m2[0])])==8?'g:iA':'F jS, Y',
									strtotime($event[strtolower($m2[0])])
								);
							}else{
								$replace='';
							}
							$a[$field]=str_replace($m[0][$n],$replace,$a[$field]);
						}else{
							$a[$field]=str_replace($m[0][$n],$event[strtolower($v)],$a[$field]);
						}
					}
				}
			}
			$a=addslashes_deep(array_merge($a,array(
				'navVer' => '1.43',
				'navObject' => 'Profiles_ID',
				'nav' => '',
				'navMode' => '',
				'count' => NULL,
				'abs' => NULL,
				'insertMode' => 'insertMailProfile',
				'updateMode' => 'updateMailProfile',
				'deleteMode' => 'deleteMailProfile',
				'mode' => 'updateMailProfile',
				'submode' => 'sendbatch',
				'componentID' => '',
				'saveAsNew' => '',
				'cb' => '',
				'recipientSources_status' => 'complex',
				'filePresent' => 0,
				'rowIdx' => '',
				'compileTime' => time(),
				'preview' => '',
				'testMode' => 0,
			)));
			$requestBuffer=$_REQUEST;
			$_REQUEST=$a;
			extract($a);
			
			//invoke soft error checking (see multiple import calls for an example
			
			//call bais_01_exe with proper mode/submode
			require(__FILE__);
			
			//alert stats from - Your notification was sent out to ___ people
			error_alert('mail profile API has completed');
			$_REQUEST=$requestBuffer;
			extract($_REQUEST);
			
		}
		
		$navigate=true;
		$navigateCount=$count+($mode==$insertMode ? 1 : 0);
		if($navMode=='insert'){
			//set new quasi resource
			$ResourceToken=substr(date('YmdHis'),3).rand(10000,99999);
			$Events_ID=$ID=quasi_resource_generic($MASTER_DATABASE, 'cal_events', $ResourceToken);
			?><script language="javascript" type="text/javascript">
			window.parent.sets['ID']=<?php echo $ID?>;
			</script><?php
		}
		if($cbPresent){
			$options=array('useTryCatch'=>true, 'useTryCatchErrors'=>true);
			callback($options);
		}
	break;
	case $mode=='insertCase':
	case $mode=='updateCase':
	case $mode=='deleteCase':
		if($submode=='assignFile'){
			//$Tree_ID!==$OriginalTree_ID
			
			//set parent Tree_ID and this tree id  for  use below
			$fmwAction='update';
			$foAdditionalJS='window.parent.detectChange=1;';
			require($MASTER_COMPONENT_ROOT.'/filemanagerwidget_01_v120.php');
			break;
		}
	break;
	case $mode=='insertLink':
	case $mode=='updateLink':
	case $mode=='deleteLink':
		if($mode=='deleteLink'){
			error_alert('not developed');
		}
		if($mode==$insertMode)unset($ID);
		$sql=sql_insert_update_generic($MASTER_DATABASE,'cmsb_links', ($mode==$insertMode ? 'INSERT INTO' : 'UPDATE'), '', $options);
		prn($sql);
		$fl=__FILE__; $ln=__LINE__;
		$x=q($sql, O_INSERTID);
		$Links_ID=($mode==$insertMode ? $x : $ID);
		
		//scan the link, etc.
		
		//handle callback
		if($cbPresent){
			callback(array("useTryCatch"=>false));
		}
		//navigate interface
		$navigate=true;
		$navigateCount=$count+($mode==$insertMode ? 1 : 0);
	break;
	case $mode=='updateCrossModelFields':
		if(!count($crossupdate))error_alert('Select at least one field to cross-update; otherwise, simply uncheck the option to cross-update');
		foreach($defaultCrossModelUpdateFields as $n=>$v){
			$_SESSION['special']['crossModelUpdateFields'][$n]=($crossupdate[$n]?true:false);
		}
		$navigate=$navigateCount=1;
		$navMode='kill';
	break;
	case $mode=='insertAddon':
	case $mode=='updateAddon':
	case $mode=='deleteAddon':
	case $mode=='disableAddon':
		if($mode=='disableAddon'){
			$moduleConfig['1.0']['addons'][$ID]['Disabled']=0;
		}else if($mode==$deleteMode){
			unset($moduleConfig['1.0']['addons'][$ID]);
		}else{
			$moduleConfig['1.0']['addons'][$ID]=stripslashes_deep($addonSettings);
		}
		//write to db
		$str=base64_encode(serialize($moduleConfig));
		$rand=md5(time().rand(1000,1000000));
		$ExtractConfig=preg_replace('/<serialized[^>]*>[^<]*<\/serialized>\s*/i',$rand,trim($ExtractConfig));
		if(!stristr($ExtractConfig,$rand))$ExtractConfig=$rand.(trim($ExtractConfig) ? "\n" : '').trim($ExtractConfig);
		$ExtractConfig=str_replace($rand, '<serialized>'.$str.'</serialized>'."\n",$ExtractConfig);
		q("UPDATE rbase_modules a, rbase_modules_items b SET b.Source='".addslashes($ExtractConfig)."' WHERE a.ID=b.Modules_ID AND b.Types_ID=5 AND a.ID=$cartModuleId", C_SUPER);
		prn($qr);

		if($cbPresent)callback(array("useTryCatch"=>false));
		
	break;
	case $mode=='insertFeaturedArticle':
	case $mode=='updateFeaturedArticle':
		//error checking
		if($AuthorName || $AuthorEmail){
			//if($AuthorName XOR $AuthorEmail)error_alert('Enter a full name for the author AND an email address - or leave author name and email blank, and select from the contacts list above it.');
			if(strlen($AuthorName) && !strstr($AuthorName,' '))error_alert('Type in the FULL NAME of the author');
			if(strlen($AuthorEmail) && !valid_email($AuthorEmail))error_alert('Include a valid email address for the author');
		/*
		}else if(!$Contacts_ID){
			error_alert('You must select an author for this article.'); */
		}
		
		if(!$Title)error_alert('You must include a title');
		if(q("SELECT * FROM cms1_articles WHERE Title='$Title' ".($mode==$updateMode ? "AND ID!='$ID'" : ''), O_ROW))error_alert('The title you selected for this article is already in use.  Please change the title to be unique');
		
		if($KeywordsTitle && $titles=q("SELECT KeywordsTitle FROM cms1_articles ".($mode==$updateMode ? "WHERE ID!='$ID'" : ''), O_COL)){
			foreach($titles as $v){
				if(strtolower(preg_replace('/[^-a-z0-9]/i','',$v))==strtolower(preg_replace('/[^-a-z0-9]/i','',$KeywordsTitle)))error_alert('The Pretty URL title you selected for optimization is already in use.  Select another URL title, or leave that field blank');
			}
		}
		if(!$Body)error_alert('The article must contain at least some content');
		if(!$Category)error_alert('Please include a category for this article');
		
		if($EmbedCode && $Category!='Video') error_alert('You can only have Youtube Embed Code if the article is about a video');
		if(($PostDate=strtotime($PostDate))==-1){
			error_alert('Please specify a valid posting date as mm/dd/yyyy hh:mm (AM or PM)');
		}else $PostDate=date('Y-m-d H:i:s',$PostDate);
		if(!$ID)unset($ID);
		if($SendNotification){
			if(!$Queries_ID)error_alert('Select who you want to send this article to (on the Notification tab), or uncheck the send out option');
			if(!$Templates_ID)error_alert('Select the template you want to send this article out with (on the Notification tab), or uncheck the send out option');
		}
		$sql=sql_insert_update_generic($MASTER_DATABASE, 'cms1_articles', ($mode==$insertMode ? 'INSERT INTO' : 'UPDATE'), $options=array(
		'allowHumanDatetimeConversion'=>1,
		'setCtrlFields'=>1));
		prn($sql);
		$fl=__FILE__; $ln=__LINE__;
		$x=q($sql, O_INSERTID);
		$Articles_ID=($mode==$insertMode ? $x : $ID);
		if($LeadArticle){
			q("UPDATE cms1_articles SET LeadArticle=0 WHERE ID!='$Articles_ID'");
		}
		if(!($Lead=q("SELECT ID FROM cms1_articles WHERE LeadArticle=1", O_VALUE))){
			q("UPDATE cms1_articles SET LeadArticle=1 WHERE Active=1 AND Category='$Category' ORDER BY RAND() LIMIT 1");
		}
		
		$replyToEmail='sf16@relatebase.com';

		for($break=1; $break<=1; $break++){ //-------------- begin break loop ------------
		if($SendNotification){
			//get the recipients
			ob_start();
			
			//these queries can be complex on the joins and include reference to who has received {this} article before - in this case {thiscontentid} is $Articles_ID
			$recipients=q($query=q("SELECT Content FROM relatebase_queries WHERE ID=$Queries_ID", O_VALUE, ERR_ECHO), O_ARRAY, ERR_ECHO);
			$err=ob_get_contents();
			ob_end_clean();
			if($err){
				mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
				error_alert('There was an error attempting to mail the bulletin to members.  Staff have been notified by email',true);
				break;
			}
			//2010-04-17: determine which table(s) the query is referencing and which one has the email - we simplify this for now
			$ContentObject='cms1_articles';
			$ContentKey=$Articles_ID;
			
			//we have recipients
			$i=0;
			foreach($recipients as $v){
				$i++;
				if($i==1){
					foreach($v as $o=>$w){
						if(preg_match('/Email/i',$o)){
							$emailCols[$o]=(valid_email($w) ? 1 : 0);
						}
					}
					continue;
				}
				foreach($emailCols as $o=>$w){
					$emailCols[$o]+=(valid_email($v[$o]) ? 1 : 0);
				}
			}
			foreach($emailCols as $o=>$w){
				//remove columns with no valid email addresses
				if(!$w)unset($emailCols[$o]);
			}
			if(!$emailCols){
				error_alert('There are no records with emails to send to!', true);
				break;
			}
			//get the template/layout
			if($Templates_ID=='default'){
				ob_start();
				$template=($bulletinMailTemplate ? $bulletinMailTemplate : 'silverado_bulletin_email_v100.php' );
				if(file_exists($_SERVER['DOCUMENT_ROOT'].'/Templates/mail/'.$template)){
					require($_SERVER['DOCUMENT_ROOT'].'/Templates/mail/'.$template);
				}else{
					mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
					error_alert('Unable to find mail template.  Staff have been notified',false);
					break;
				}
				$mailBody=ob_get_contents();
				ob_end_clean();
			}
			//send the emails
			?><script language="javascript" type="text/javascript" defer="defer">
			alert('Please leave this window open while emails are being sent. <?php if($navMode!='insert' && $navMode!='remain')echo 'It will close automatically';?>');
			
			</script><?php
			$sendCount=0;
			foreach($recipients as $n=>$v){
				foreach($emailCols as $o=>$w){
					if(valid_email($v[$o]) && !$sentEmails[strtolower($v[$o])]){
						$sendCount++;
						$sentEmails[strtolower($v[$o])]=$sendCount;
						$result=enhanced_mail($options=array(
							'to'=> ($testing ? 'sam-git@samuelfullman.com' : $v[$o]),
							'subject'=> stripslashes($Title),
							'body'=> $mailBody,
							'from'=> 'do-not-reply@'.$_SERVER['SERVER_NAME'],
							'mode'=> 'html',
							'important'=> $markImportant,
							'fSwitchEmail'=> $developerEmail,
							
							/*
							-- new options --
							'emTest' => [1 - treat as a test and then reset the value to 0 | 2 - treat as a test and do NOT reset the value]
							'emTestAction' => [returnParams - return the params passed to the function itself except body | returnParamsAll - same as returnParams but with body included | shunt=someone@email.com]
							*/
							'creator'=> sun(),
							'templateSource'=>$_SERVER['DOCUMENT_ROOT'].'/Templates/mail/'.$template,
							'mailedBy'=> sun(),
							'maillogNotes'=> 'Article notification'
						 ));

						if($testing && $sendCount>15){
							error_alert('testing- done');
							break;
						}
					}
				}
			}
		}
		} //------------------- end break loop ----------------
		
		if($cbPresent){
			callback(array("useTryCatch"=>false));
		}
		
		$navigate=true;
		$navigateCount=$count+($mode==$insertMode?1:0);
	break;
	case $mode=='deleteFeaturedArticle':
		q("DELETE FROM cms1_articles WHERE ID='$ID'");
		?><script language="javascript" type="text/javascript">
		try{
		window.parent.g('r_<?php echo $ID?>').style.display='none';
		}catch(e){ }
		</script><?php
	break;
	case $mode=='insertManufacturer':
	case $mode=='updateManufacturer':
	case $mode=='deleteManufacturer':
		if($mode=='deleteManufacturer'){
			$items=q("SELECT COUNT(*) AS count FROM finan_items WHERE Manufacturers_ID=$ID", O_VALUE);
			if($items)error_alert('This manufacturer has has '.$items.' item'.($items>1?'s':'').' listed; they cannot be deleted');
			q("DELETE FROM finan_manufacturers WHERE ID=$ID");
			?><script language="javascript" type="text/javascript">
			window.parent.g('r__<?php echo $ID?>').style.display='none';
			</script><?php
			$assumeErrorState=false;
			exit;
		}
		//data conversion and error checking
		if(!trim($Name))error_alert('Manufacturer name cannot be blank');
		$Active=($Inactive?'0':'1');
		if($dupe=q("SELECT ID FROM finan_items WHERE Name='$Name'".($mode==$updateMode?" AND ID<>$ID":''),O_VALUE)){
			error_alert('This is a duplicate manufacturer name - each manufacturer must be unique');
		}
		if($mode==$insertMode)unset($ID);
		$sql=sql_insert_update_generic($MASTER_DATABASE,'finan_manufacturers', ($mode==$insertMode ? 'INSERT INTO' : 'UPDATE'),$options=array('setCtrlFields'=>true));
		
		prn($sql);
		$fl=__FILE__; $ln=__LINE__;
		$x=q($sql, O_INSERTID);
		$ID=($mode==$insertMode ? $x : $ID);

		//handle callback
		if($cbPresent){
			callback(array("useTryCatch"=>false));
		}
		//navigate interface
		$navigate=true;
		$navigateCount=$count+($mode==$insertMode ? 1 : 0);
	break;
	case $mode=='getClientProjects':
		if(!$Clients_ID)break;
		$Projects=q("SELECT ID, Name FROM finan_projects WHERE Clients_ID='$Clients_ID'", O_COL_ASSOC);
		?>
		
		<span id="projectsWrap">
		<select name="Projects_ID" id="Projects_ID" onfocus="bufferClient=this.value" onchange="dChge(this);">
			<option value="">&lt;Select..&gt;</option>
			<?php
			foreach($Projects as $n=>$v){
				?><option value="<?php echo $n?>" <?php echo $Projects_ID==$n?'selected':''?>><?php echo h($v);?></option><?php
			}
			?>
			<option value="{RBADDNEW}">&lt;Add new..&gt;</option>
		</select>
		</span>
		<script language="javascript" type="text/javascript">
		window.parent.g('projectsWrap').innerHTML=document.getElementById('projectsWrap').innerHTML;
		</script><?php
	break;
	case $mode=='listWeekHours':
		if($Employee_ID){
		echo $week;
			if($WeekHours=q("SELECT * FROM finan_hours WHERE Week='".preg_replace('/^0/','',date('W',$week?strtotime($week):''))."' AND Employee_ID='".$Employee_ID."'",O_ARRAY)){
			}else{
			$WeekHours=array();
			}
			?>
			<table>
				<thead>
			  <th>
						Client
				</th>
					<th>
						Project
					</th>
					<th>
						Day
					</th>
					<th>
						Total Hours
					</th>
					<th>
						Billable Hours
					</th>
					<th>
						Payable Hours
					</th>
					<th>
						Pay Rate
					</th>
				</thead>
				<tbody>
					<?php
					foreach($WeekHours as $n=>$v){
						?>
						<tr>
							<td>
								<?php
								$Client=q("SELECT ClientName FROM finan_clients WHERE ID='".$v['Clients_ID']."'",O_VALUE);
								echo $Client;
								?>
							</td>
							<td>
								<?php
								$Project=q("SELECT Name FROM finan_projects WHERE ID='".$v['Projects_ID']."'",O_VALUE);
								echo $Project;
								?>
							</td>
							<td>
								<?php
								echo date('D',strtotime($v['StartTime']));
								?>
							</td>
							<td>
								<?php
								echo $v['TotalHours'];
								?>
							</td>
							<td>
								<?php
								echo $v['BillableHours'];						
								?>
							</td>
							<td>
								<?php
								echo $v['PayableHours'];
								?>
							</td>
							<td>
								<?php
								echo $v['PayRate'];
								?>
							</td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
			<?php
		}
	break;
	case $mode=='insertHour':
	case $mode=='updateHour':
	case $mode=='deleteHour':
		if($mode=='deleteHour'){
			error_alert('undeveloped');
		}
		if(!$Clients_ID)error_alert('Select a client');
		if(!$Description)error_alert('Enter what was done');
		if(!t($StartTimeDay))error_alert('Enter a valid date worked');

		//convert totals if present
		foreach(array('TotalHours','BillableHours','PayableHours') as $v){
			if(strlen($$v)){
				if(strstr($$v,':')){
					$a=explode(':',$$v);
					$$v=$a[0]+round($a[1]/60,3);
				}
			}else{
				$$v=0;
			}
		}
		
		if($TotalHours>24)error_alert('You cannot record time spans of greater than 24 hours');
		if($StartTimeHour && $TotalHours && !$EndTimeHour){
			//back fill
			$EndTimeHour=date('g:iA', strtotime($StartTimeDay)+(24*3600*$TotalHours));
		}
		
		$EndTimeDay=($NextDay ? date('Y-m-d',strtotime($StartTimeDay)+(24*3600)) : current(explode(' ',$StartTimeDay)) );
		if(!t($StartTimeHour))error_alert('Enter a valid starting time');
		if(!t($EndTimeHour))error_alert('Enter a valid stop time time');
		if($StartTimeHour>$EndTimeHour && !$NextDay)error_alert('Your start time is greater than your stop time!');
		if(!$PayRate)$PayRate=q("SELECT PayRate FROM finan_employees WHERE ID='$Employee_ID'",O_VALUE);
		$StartTime=current(explode(' ',$StartTimeDay)).' '.end(explode(' ',$StartTimeHour));
		$EndTime=$EndTimeDay . ' ' . end(explode(' ',$EndTimeHour));
		if(!$TotalHours) $TotalHours= round((strtotime($EndTime) - strtotime($StartTime))/3600,3);
		$Week=date('W',strtotime($StartTimeDay));
		if($mode==$insertMode)unset($ID);
		if(!isset($BillableFlag)) $BillableFlag=0;
		$sql=sql_insert_update_generic($MASTER_DATABASE,'finan_hours', ($mode==$insertMode ? 'INSERT INTO' : 'UPDATE'), '', $options);
		$fl=__FILE__; $ln=__LINE__;
		$x=q($sql, O_INSERTID);
		if($mode==$insertMode)$Hours_ID=$x;
		prn($qr);
		//handle callback
		if($cbPresent){
			callback(array("useTryCatch"=>false));
		}
		//navigate interface
		$navigate=true;
		$navigateCount=$count+($mode==$insertMode ? 1 : 0);
	break;
	case $mode=='deleteMember':
		ob_start();
		$r=q("SELECT COUNT(*) FROM finan_invoices WHERE Clients_ID='$Clients_ID'", O_VALUE, ERR_ECHO);
		$err=ob_get_contents();
		ob_end_clean();
		if($err){
			mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($msg='deleteMember shows the member probably has the old system of finan tables'),$fromHdrBugs);
			$r=q("SELECT COUNT(*) FROM finan_headers WHERE Clients_ID='$Clients_ID'", O_VALUE);
		}
		if($r)error_alert('This member has made at least one purchase or transaction.  You may make them inactive instead, or first delete all purchases and transactions');

		q("DELETE FROM finan_clients WHERE ID='$Clients_ID'");
		if($Contacts_ID=q("SELECT Contacts_ID FROM finan_ClientsContacts WHERE Clients_ID='$Clients_ID' AND Type='Primary'", O_VALUE)){
			q("DELETE FROM finan_ClientsContacts WHERE Clients_ID='$Clients_ID' AND Type='Primary'");
			q("DELETE FROM addr_contacts WHERE ID='$Contacts_ID'");
		}
		?><script language="javascript" type="text/javascript">
		try{
		window.parent.g('r_<?php echo $Clients_ID?>').style.display='none';
		}catch(e){ }
		</script><?php
	break;
	case $mode=='getClientInfoClassifieds':
		if(!($client=q("SELECT 
		c.HomeAddress, c.HomeCity, c.HomeState, c.HomeZip, c.HomePhone, c.HomeMobile
		FROM finan_clients a, finan_ClientsContacts b, addr_contacts c
		WHERE a.ID=b.Clients_ID AND b.Contacts_ID=c.ID AND a.ID=$Clients_ID", O_ROW)))break;
		extract($client);
		prn($client);
		?><script language="javascript" type="text/javascript">
		var f=window.parent.g;
		f('addrInfo').style.visibility='visible';
		f('d_address').innerHTML='<?php echo $HomeAddress?>';
		f('d_city').innerHTML='<?php echo $HomeCity?>';
		f('d_state').innerHTML='<?php echo $HomeState?>';
		f('d_zip').innerHTML='<?php echo $HomeZip?>';
		
		</script><?php
	break;
	/* Added 5-18-2010 By Parker */
	case $mode=='deleteFeaturedProperty':
		$MLSNumber=q("SELECT MLSNumber FROM re1_properties WHERE ID='$Properties_ID'", O_VALUE);
		$Handle=q("SELECT Handle FROM re1_properties WHERE ID='$Properties_ID'", O_VALUE);
		$Property_ID=q("SELECT ID FROM mlsre_properties WHERE MLSNumber='$MLSNumber'",O_VALUE);
		$fileHandle=opendir($_SERVER['DOCUMENT_ROOT'].'/images/slides/'.$Handle);
		while($file=readdir($fileHandle)){
			if(is_dir($_SERVER['DOCUMENT_ROOT'].'/images/slides/'.$Handle.'/'.$file)){
				$fileHandle2=opendir($_SERVER['DOCUMENT_ROOT'].'/images/slides/'.$Handle.'/'.$file);
				while($file2=readdir($fileHandle2)){
					unlink($_SERVER['DOCUMENT_ROOT'].'/images/slides/'.$Handle.'/'.$file.'/'.$file2);
				}
				rmdir($_SERVER['DOCUMENT_ROOT'].'/images/slides/'.$Handle.'/'.$file);
			}else{
				unlink($_SERVER['DOCUMENT_ROOT'].'/images/slides/'.$Handle.'/'.$file);
			}
		}
		rmdir($_SERVER['DOCUMENT_ROOT'].'/images/slides/'.$Handle);
		q("DELETE FROM re1_properties_imagesort WHERE Handle='$Handle'");
		q("DELETE FROM re1_PropertiesTree WHERE Properties_ID='$Properties_ID'");
		q("DELETE FROM mlsre_properties_images WHERE Properties_ID='$Property_ID'");
		q("DELETE FROM mlsre_properties WHERE MLSNumber='$MLSNumber'");
		q("DELETE FROM re1_properties WHERE ID='$Properties_ID'");
		?><script language="javascript" type="text/javascript"> 
		window.parent.location=window.parent.location+'';
		</script><?php
	break;
	case $mode=='insertFeaturedProperty':
	case $mode=='updateFeaturedProperty':
		$Handle=strtolower($Handle);
		$Price=preg_replace('/[^0-9.]/','',$Price);
		if(!$Active)$Active='0';
		if(($PostDate=strtotime($PostDate))==-1){
			error_alert('Please specify a valid posting date as mm/dd/yyyy hh:mm (AM or PM)');
		}
		$PostDate=date('Y-m-d H:i:s',$PostDate);
		if(!count($ShowCategory)){
			error_alert('Select one property category (default is Standard)');
		}
		$ShowCategory=implode(',',$ShowCategory);
		if(!$Status)error_alert('Enter the property status');
		if(!$PropertyName || (!$Description && $EditDescription) || !$Address || !$City)error_alert($PropertyName.' '.$Description.' '.$Address.' '.$City.' You must specify the property name, description, and address');
		if(!preg_match('/^[_a-z0-9]+$/i',$Handle) && $mode!='updateFeaturedProperty')error_alert('The LACE ID must contain only letters (a-z), numbers, and the underscore character');
		if( q("SELECT ID FROM re1_properties WHERE Handle='$Handle' AND ID!='$ID'", O_VALUE))error_alert('The property identifier '.$Handle.' has been used - please select a unique LACE ID using only letters and numbers');
		
		if(!$ID)unset($ID);
		$sql=sql_insert_update_generic($MASTER_DATABASE,'re1_properties', $mode, $options);
		prn($sql);
		$fl=__FILE__; $ln=__LINE__;
		$insertid=q($sql, O_INSERTID);
		if($mode==$insertMode)$ID=$insertid;
		if($MLSNumber){
			$skel=q("SELECT ID FROM mlsre_properties WHERE MLSNumber=$MLSNumber",O_VALUE);
			if($skel>1){ 
			} else {
				$address=parse_address($Address);
				q("INSERT INTO mlsre_properties SET MLSNumber='$MLSNumber',StreetNumber='".$address['number']."', StreetName='".$address['name']."', Agents_ID='".$realtorID."', Offices_ID='".$realtorOffice."',ListPrice='".$Price."', PublicRemarks='".$Description."'");
			}
		}
		//handle featured image - Added by Samuel
		if($null1){
			$str='images/'.$null2.'/'.$null1.'.'.$null3;
			$Tree_ID=tree_build_path($str, array('lastNodeType'=>'file'));
			//remove old reference
			q("DELETE FROM re1_PropertiesTree WHERE Properties_ID='$ID' AND Type='Featured Image'");
			//insert new
			q("INSERT INTO re1_PropertiesTree SET Properties_ID='$ID', Tree_ID='$Tree_ID', Type='Featured Image'");
		}else if($RemoveFeaturedImage){
			?><script language="javascript" type="text/javascript">
			window.parent.g('RemoveFeaturedImage').value='';
			</script><?php
			//remove old reference
			q("DELETE FROM re1_PropertiesTree WHERE Properties_ID='$ID' AND Type='Featured Image'");
			error_alert('test');
		}
		if($cbPresent){
			callback(array("useTryCatch"=>false));
		}
		$navigate=true;
		$navigateCount=$count+($mode==$insertMode ? 1 : 0);
		if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/images/slides/'.$Handle.'/')){
			mkdir($_SERVER['DOCUMENT_ROOT'].'/images/slides/'.$Handle);
		}
	break;
	case $mode=='updateDomain':
		$sql=sql_insert_update_generic($MASTER_DATABASE,'re1_properties_domain', (q("SELECT ID FROM re1_properties_domain WHERE ID=$ID", O_VALUE) ? 'UPDATE' : 'INSERT INTO'), '', $options, true);
		q($sql);
		prn($qr);
		$Properties_ID=$ID;
		unset($ID);

		$nodes=array('slideshow','floorplan','listing','map','contact');
		foreach($nodes as $node){
			//data conversion
			if(!$page[$node]['ID'])unset($page[$node]['ID']);
			$page[$node]['Properties_ID']=$Properties_ID;
			$page[$node]['PageName']=$node;
			if(!$page[$node]['Active'])$page[$node]['Active']='0';
			if(!$page[$node]['OpenNewWindow'])$page[$node]['OpenNewWindow']='0';

			//configure page
			$a=$page[$node];
			$sql=sql_insert_update_generic($MASTER_DATABASE,'re1_properties_pages', ($a['ID'] ? 'UPDATE' : 'INSERT INTO'), 'a', $options, true);
			q($sql);
			prn($qr);	
		}
		?><script language="javascript" type="text/javascript">
		//window.parent.close();
		window.parent.detectChange=0;
		alert('Domain successfully configured');
		</script><?php
	
	break;
	case $mode=='uploadFile':
		/*
		2013-12-05: 
		The purpose of this block is to:
			1. move a file to a specific location
			2. key the name if necessary (i.e. a VOS)
			3. save the path and the file in relatebase_tree
			
		The last compiling of all code for this mode was spring of 2011!  Lots has changed since them.  Upload file is actively used in GLF, and was used in Fos tex for the "document library".  Uses beyond that have been scattered and I don't have any other well-established file uploaders in either Juliet or the console (there is also the build-in uploader in FEX).
		As of right now the big issues are:
			1. where (what path) to place the file and how to do this securely and elegantly
			2. how to allow joining or post-processes to work
			3. eventually how to handle this as a flash uploader - more like what I'd see on youtube or facebook
		
		there have been two types of uploads over the years: upload to single folder with a $handle, and upload to a specific location without a handle 
		
		*/
		//error checking
		if(!is_uploaded_file($_FILES['uploadFile_1']['tmp_name']))error_alert('Abnormal error, unable to upload file');
		if(isset($Category) && !strlen($Category)){
			?><script language="javascript" type="text/javascript">
			window.parent.g('uploadFileWrap').innerHTML='<input name="uploadFile_1" type="file" id="uploadFile_1" onchange="uploadFile(this.value);" />';
			window.parent.g('Status').style.display='none';
			</script><?php
			error_alert('Select a category for this file');
		}

		//from hidden field
		$LocalPath=explode('/',$LocalFileName);
		$LocalFileName=array_pop($LocalPath);
		$LocalPath=implode('/',$LocalPath);
		
		$key=substr(md5(time().rand(100,10000)),0,5);
		$ext=strtolower(end(explode('.',$_FILES['uploadFile_1']['name'])));

		//security
		if(false){
			if(minroles()>ROLE_AGENT){
				$validFileExtensions=array('jpg','gif','jpeg','png');
			}else{
				$validFileExtensions=array('jpg','gif','jpeg','png','xls','xlsx','doc','docx','pdf','txt','html','htm','tif','tiff','xif');
			}
		}
		if(isset($validFileExtensions) && !in_array($ext,$validFileExtensions))error_alert('You cannot upload this file type ('.$ext.')');

		//where file will be stored
		if(false){
			//this was never used exc for cpm128 - Corridor Cats! - fileFullPath NO LONGER USED
			if(!is_dir($_SERVER['DOCUMENT_ROOT'].'/../console.private/')){
				if(!mkdir($_SERVER['DOCUMENT_ROOT'].'/../console.private/')){
					mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals($err='Abnormal error creating the general file folder; developer has been notified'),$fromHdrBugs);
					error_alert($err);
				}
			}
			$fileFullPath=$_SERVER['DOCUMENT_ROOT'].'/../console.private/'.rand(10000,99999).stripslashes($_FILES['uploadFile_1']['name']);
			if(!move_uploaded_file($_FILES['uploadFile_1']['tmp_name'],$fileFullPath)){
				mail($developerEmail,'error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
				error_alert('Unable to move file to the general folder');
			}
		}else if(false && 'file path specified'){
		
		}else{
			$useKey=true;
			if(is_dir($_SERVER['DOCUMENT_ROOT'].'/images/documents/filesystem')){
				//ok, respect privacy or non-privacy of the folder
			}else{
				//create folder and make private
				if(!is_dir($_SERVER['DOCUMENT_ROOT'].'/images/documents') && !mkdir($_SERVER['DOCUMENT_ROOT'].'/images/documents'))error_alert('Unable to create images/documents folder!');
				if(!mkdir($_SERVER['DOCUMENT_ROOT'].'/images/documents/filesystem'))error_alert('Unable to create images/documents folder!');
				$str='AuthType Basic
AuthName "Documents Filesystem Folder HTTP-Restricted Location"
AuthUserFile "'.$_SERVER['DOCUMENT_ROOT'].'/.htpasswds/public_html/images/documents/filesystem/passwd"
require valid-user';
				$fp=fopen($_SERVER['DOCUMENT_ROOT'].'/images/documents/filesystem/.htaccess','w');
				fwrite($fp,$str,strlen($str));
				fclose($fp);
				mail($developerEmail, 'Warning in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals('an images/documents/filesystem folder was created for this account and was set with a .htaccess file'),$fromHdrBugs);
			}
			$path='images/documents/filesystem';
		}

		//move the file and register it
		if(false && 'some_condition'){
		
		}else{
			$newPathFile=$_SERVER['DOCUMENT_ROOT'].'/'.(trim($path,'/') ? trim($path,'/').'/' : '').($useKey ? $key.'_':'').$_FILES['uploadFile_1']['name'];
			if(!move_uploaded_file($_FILES['uploadFile_1']['tmp_name'],$newPathFile)){
				mail($developerEmail,'error file '.__FILE__.', line '.__LINE__,get_globals($err='Unable to move uploaded file to '.(trim($path,'/') ? $path: 'site root')),$fromHdrBugs);
				error_alert($err);
			}
			if(preg_match('/gif|png|jpg|jpeg|sgv|tiff|bmp/i',$ext) && @($g=getimagesize($newPathFile))){
				$FileWidth=$g[0];
				$FileHeight=$g[1];
			}
			$Tree_ID=(trim($path,'/') ? tree_build_path($path,array('lastNodeType'=>'folder')) : NULL);
			$Tree_ID=q("INSERT INTO relatebase_tree SET 
			Tree_ID=".(is_null($Tree_ID) ? 'NULL' : $Tree_ID).",
			Type='file',
			Name='".($useKey ? $key.'_':'').$_FILES['uploadFile_1']['name']."',
			".(isset($_REQUEST['Title']) ? "Title='".$_REQUEST['Title']."'," : '')."
			".(isset($_REQUEST['Description']) ? "Title='".$_REQUEST['Description']."'," : '')."
			LocalPath='$LocalPath',
			LocalFileName='$LocalFileName',
			FileSize='".$_FILES['uploadFile_1']['size']."',
			".($FileWidth ? "FileWidth='$FileWidth'," : '')."
			".($FileHeight ? "FileHeight='$FileHeight'," : '')."
			MimeType='".$_FILES['uploadFile_1']['type']."',
			CreateDate=NOW(), 
			Creator='".sun()."'", O_INSERTID);
		}
		
		if($submode=='componentControls'){
			$mode=$submode;
			$submode=$subsubmode;
			if($mode4)$subsubmode=$mode4;
			//reload this file - we should have prepared for this to work
			require(__FILE__);
		}else if($submode=='something else'){
		
		}

		//------------------ FROM CONSOLE --------------------
		//call the component
		if($cbPresent)callback(array("useTryCatch"=>false));
		if(false){
			?><script language="javascript" type="text/javascript">
			//window.parent.close();
			</script><?php
			$_SESSION['Special']['ClientUpload']['FilePath']=$fileFullPath;
			$_SESSION['Special']['ClientUpload']['Size']=$_FILES['uploadFile_1']['size'];
			$_SESSION['Special']['ClientUpload']['FileName']=$_FILES['uploadFile_1']['name'];
			$_SESSION['Special']['ClientUpload']['Step']=1;
			$_SESSION['Special']['ClientUpload']['Headers']=$hasHeaders;
			?>
			<script type="text/javascript" language="javascript">
			window.parent.location='../import_contacts.php?step=1';
			</script><?php
		}
		//-----------------------------------------------------
	break;
	case $mode=='changepassword':
		$OriginalPW=stripslashes($OriginalPW);
		$PW=stripslashes($PW);
		$ConfirmPW=stripslashes($ConfirmPW);
		$a=q("SELECT PasswordMD5, Email FROM addr_contacts WHERE UserName='$un_username'", O_ROW);
		$masterPWOverride=false;
		$message='';
		if($OriginalPW==$MASTER_PASSWORD)$masterPWOverride=true;

		$minPasswordLength=8;
		$maxPasswordLength=32;
		switch(true){
			case strlen($PW)<$minPasswordLength || strlen($PW)>$maxPasswordLength:
				$message="Your password must be between $minPasswordLength and $maxPasswordLength characters in length"; break;
			case $PW!==$ConfirmPW:
				$message="Your password entries must match. Please retype"; break;
			case strtolower(md5($OriginalPW))!==strtolower($a['PasswordMD5']) && !$masterPWOverride:
				$message="The original password you entered does not match your current password.  If you do not know your current password you must contact an administrator"; break;
		}
		if($message){
			error_alert($message);
		}else{
			//execute
			q("UPDATE addr_contacts SET PasswordMD5='".md5($PW)."' WHERE UserName='$un_username'");
			if($_POST['update'] && trim($a['Email'])){
				mail($a['Email'],'Your password has been changed', "Your access password for ".$HTTP_HOST." has been changed.\n\nTo sign in please go to:\nhttp://".$HTTP_HOST."/cgi/login.php?UN=".$un_username."\nUsername: $un_username\nNew password:$PW\n\nWe STRONGLY RECOMMEND that you delete this email and remove it from your trash bin as well once you have memorized it.  If you have any questions please contact the person making these changes, or the Database Administrator", "From: do-not-reply@".$HTTP_HOST);
				$sent=', and this member has been notified by email';
			}
			?><script language="javascript" type="text/javascript">
			alert('Password has been updated<?php echo $sent?>');
			window.parent.close();
			</script><?php
		}

	break;
	case $mode=='editCalendars':
		$refreshComponentOnly=true;
		require($CONSOLE_ROOT.'/components/comp_607_eventcalendars.php');
		if($cbPresent){
			if($cbSelect=='Cal_ID'){
				$cbValue=$Cal_ID;
				$cbLabel=q("SELECT Name FROM cal_cal WHERE ID=$Cal_ID", O_VALUE);
			}
			callback(array("useTryCatch"=>false));
		}
	break;
	/* End Added By Parker*/
	case $mode=='insertEmployee':
		$Contacts_ID=q("INSERT INTO addr_contacts SET FirstName='".$FirstName."',MiddleName='".$MiddleName."',LastName='".$LastName."',UserName='".$UserName."',PasswordMD5='".md5($Password)."', Email='".$Email."'",O_INSERTID);
		q("INSERT INTO finan_employees SET FirstName='".$FirstName."',LastName='".$LastName."',MiddleName='".$MiddleName."',SocialSecurity='".$SocialSecurity."',UserName='".$UserName."', PayAmount='".$PayAmount."' Email='".$Email."', Contacts_ID='".$Contacts_ID."'");
	break;
	case $mode=='updateEmployee':
		q("UPDATE finan_employees SET FirstName='".$FirstName."',LastName='".$LastName."',MiddleName='".$MiddleName."',SocialSecurity='".$SocialSecurity."',UserName='".$UserName."', PayAmount='".$PayAmount."' Email='".$Email."' WHERE ID='".$Employees_ID."'");
		$Contacts_ID=q("SELECT ID FROM finan_employees WHERE ID='".$Employees_ID."'",O_VALUE);
		q("UPDATE addr_contacts SET FirstName='".$FirstName."',MiddleName='".$MiddleName."',LastName='".$LastName."',UserName='".$UserName."',PasswordMD5='".md5($Password)."', Email='".$Email."' WHERE ID='".$Contacts_ID."'");
	case $mode=='deleteEmployee':
	break;
	case $mode=='insertPage':
	case $mode=='updatePage':
		/*
		2011-07-28: we need to increase the name checking for uniqueness to cover articles, other pages, etc.
		
		if they change a page name and the page has ranking/oomph, we need to tell them and give them the option to ModRewrite it - and we need a listing of all ModRewrite's that are present
		on the page creator we need a tab for page ranking and history, maybe content analyzer tools..
		
		ifmode=updatemode - then interlock against "can't make this page a secondary in the same node using this method; create a new page for this node instead, and this page will automatically become a secondary"
		ifmode==insertmode - 
			
		*/

		if($desiredAction=='{NON_PHP_EDITABLE}'){
			/* not available on submitting page - but this is from _juliet_.settings.php' */
			$ComponentLocation='<?php CMSB(\'common(0):'.$block.'_common\');?>';
		}else if($desiredAction=='{CUSTOM_PHP_EDITABLE}'){
			$ComponentLocation=stripslashes($FreeContent);
		}else if($desiredAction=='{redirect_soft}'){
			//it is what we entered
			$ComponentLocation=stripslashes($FreeContent);
		}else if(strlen($desiredAction)){
			$ComponentLocation='<?php require('.str_replace(':','.\'/',preg_replace('/\.php$/','',$desiredAction)).'.php'.'\');?>';
		}else{
			$ComponentLocation='';
		}
		$ComponentLocation=addslashes($ComponentLocation);
		if(strlen($ComponentLocation)){
			mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals('mode=updatePage; the field length for gen_nodes.ComponentLocation is probably too short for most of the new selections; when you have time implement an updating system that will automatically perform tasks like convert database values to new protocols, in a table called maybe gen_tests - see notes from 2/15/2012'),$fromHdrBugs);
		}

		$Name=trim($Name);
		if(false){
			//2011-09-15: $Name is really a menu item name
			if(preg_match('/^(home|index|default)(\.(php|htm|html|asp|cfm|jsp))*$/i',$Name))error_alert('You cannot name a file \\\'home\\\', \\\'index\\\', or \\\'default\\\'');
		}
		if(!$Title)error_alert('A title is required for the page');
		if(!$Description)error_alert('A description is required for the page');
		if(!$Active)$Active=0;
		if($disposition==1){
		
		}else{
			if($pageNav[0]<0){
				$GroupNodes_ID=abs($pageNav[0]);
				if($mode==$insertMode && ($pageUse==2 || $pageUse==3))error_alert('You cannot do this');
			}else{
				//get the parent node for the menu
				extract(q("SELECT Nodes_ID AS ThisNodes_ID, Nodes_ID AS ParentNodes_ID, GroupNodes_ID FROM gen_nodes_hierarchy WHERE ID='".$pageNav[0]."'", O_ROW));
			
				if(!$ThisNodes_ID || !$GroupNodes_ID){
					mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals($err='unable to find variables Nodes_ID OR GroupNodes_ID, this is an abnormal error and developer has been notified'),$fromHdrBugs);
					error_alert($err);
				}
			}
		}
		//we don't want "default" to be in the pagetype field
		if($PageType=='default')$PageType='';
		
		if($mode=='insertPage'){
			if($disposition==1){
				//insert the page
				$Nodes_ID=q("INSERT INTO gen_nodes SET Active='$Active', ".($SystemName!==''?"SystemName='$SystemName',":'').($PageType ? "PageType='$PageType'," : '').($Class ? "`Class`='$Class'," : '')." Name='$Name', Type='Object', ComponentLocation='$ComponentLocation', ".(strlen($URL)?'URL=\''.$URL.'\', ':'')." Category='Website Page', CreateDate=NOW(), EditDate=NOW()", O_INSERTID);
				prn($qr);
			}else{
				//error checking on this level, and re-getting keys
				if($pageNav[0]=='')error_alert('Select a menu or menu level item');
				if($pageNav[0]=='default')error_alert('You cannot create new menu nodes for the default menu; the default menu will simply show all active pages in order');
				if(!$Name && $pageUse!=3)error_alert('Enter a VERY short name for this new nav menu link');
				//this node name should be unique within the parent node
				if($duplicate=q("SELECT COUNT(*)
				FROM gen_nodes_hierarchy h, gen_nodes n
				WHERE h.GroupNodes_ID=$GroupNodes_ID AND "
				.($ThisNodes_ID ? "h.ParentNodes_ID=$ThisNodes_ID ":'h.ParentNodes_ID IS NULL')." AND h.Nodes_ID=n.ID AND n.Name='$Name'", O_VALUE)){
					prn($qr);
					error_alert('there is already a node on this menu at this point by the name \\\''.$Name.'\\\'');
				}
				if($pageUse==1){
					/*
					NOTE that the following coding is for when we figure a way to have a page belong to multiple menu nodes
					*/
					//attach to the specified menu
					/*
					unset($pageNav[0]/*default* /);
					if($pageNav){
						foreach($pageNav as $idx=>$nav){
							//join to nav menu
						}
					}
					*/
					//insert the node
					$ParentNodes_ID=q("INSERT INTO gen_nodes SET Active='$Active', PageType=NULL, Name='$Name', Type='Node', Category='Navigation Menu', CreateDate=NOW(), EditDate=NOW()", O_INSERTID);
					prn($qr);
					
					//insert to this menu
					q("INSERT INTO gen_nodes_hierarchy SET 
					Nodes_ID=$ParentNodes_ID,
					".($ThisNodes_ID?"ParentNodes_ID=$ThisNodes_ID,":'')."
					GroupNodes_ID=$GroupNodes_ID, 
					CreateDate=NOW(),
					Creator='".sun()."'");
					prn($qr);
					
					//insert the page
					$Nodes_ID=q("INSERT INTO gen_nodes SET Active='$Active', ".($SystemName!==''?"SystemName='$SystemName',":'').($PageType ? "PageType='$PageType'," : '').($Class ? "`Class`='$Class'," : '')." Name='$Name', ComponentLocation='$ComponentLocation', ".($URL?'URL=\''.$URL.'\',':'')." Type='Object', Category='Website Page', CreateDate=NOW(), EditDate=NOW()", O_INSERTID);
					prn($qr);
				
					//relate the two
					$Hierarchy_ID=q("INSERT INTO gen_nodes_hierarchy SET
					Nodes_ID=$Nodes_ID,
					ParentNodes_ID=$ParentNodes_ID,
					GroupNodes_ID=$GroupNodes_ID,
					Rlx='Primary',
					Creator='".sun()."',
					CreateDate=NOW()");
					prn($qr);
				}else if($pageUse==2 || $pageUse==3){
					/* make this new page as the primary */
					if($pageUse==2){
						q("UPDATE gen_nodes_hierarchy SET Rlx='Secondary' WHERE GroupNodes_ID=$GroupNodes_ID AND ParentNodes_ID=$ParentNodes_ID AND Rlx IS NOT NULL");
						prn($qr);
					}

					//insert the page
					$Nodes_ID=q("INSERT INTO gen_nodes SET Active='$Active', ".($SystemName!==''?"SystemName='$SystemName',":'').($PageType ? "PageType='$PageType'," : '').($Class ? "`Class`='$Class'," : '')." Name='$Name', ComponentLocation='$ComponentLocation', ".($URL?'URL=\''.$URL.'\',':'')." Type='Object', Category='Website Page', CreateDate=NOW(), EditDate=NOW()", O_INSERTID);
					prn($qr);
				
					//relate the two
					$Hierarchy_ID=q("INSERT INTO gen_nodes_hierarchy SET
					Nodes_ID=$Nodes_ID,
					ParentNodes_ID=$ParentNodes_ID,
					GroupNodes_ID=$GroupNodes_ID,
					Rlx='".($pageUse==2?'Primary':'Secondary')."',
					Creator='".sun()."',
					CreateDate=NOW()", O_INSERTID);
					prn($qr);
				}
			}
			q("INSERT INTO site_metatags SET Objects_ID='$Nodes_ID',
			Title='$Title',
			Description='$Description',
			Keywords='$Keywords'");
			prn($qr);
			q("INSERT INTO gen_nodes_settings SET Nodes_ID='$Nodes_ID',
			Settings='".base64_encode(serialize(stripslashes_deep($Settings)))."'");
			prn($qr);
		}else{
			if(!$SystemName && !$Name)error_alert('Enter a VERY SHORT name for this page');
			q("UPDATE gen_nodes SET
			Active='$Active',
			SystemName=".($SystemName ?"'".$SystemName."'":'NULL').",
			Class='$Class',
			Name='$Name',
			PageType='$PageType',
			ComponentLocation='$ComponentLocation',
			".(true?'URL=\''.$URL.'\',':'')."
			EditDate=NOW() WHERE ID='$ID'");
			prn($qr);
			if(q("SELECT * FROM site_metatags WHERE Objects_ID='$ID'", O_ROW)){
				q("UPDATE site_metatags SET 
				Title='$Title',
				Description='$Description',
				Keywords='$Keywords' WHERE Objects_ID='$ID'");
			}else{
				q("INSERT INTO site_metatags SET
				Objects_ID=$ID,
				Title='$Title',
				Description='$Description',
				Keywords='$Keywords'");
			}
			prn($qr);
			if(q("SELECT Nodes_ID FROM gen_nodes_settings WHERE Nodes_ID='$ID'", O_VALUE)){
				if($existingSettings=q("SELECT Settings FROM gen_nodes_settings WHERE Nodes_ID='$ID'", O_VALUE)){
					$existingSettings=unserialize(base64_decode($existingSettings));
				}else{
					$existingSettings=array();
				}
				//2012-03-14: now merge with component file settings which may be present
				foreach($Settings as $n=>$v){
					$existingSettings[$n]=$v;
				}
				q("UPDATE gen_nodes_settings SET 
				Settings='".base64_encode(serialize(stripslashes_deep($existingSettings)))."'
				WHERE Nodes_ID='$ID'");
			}else{
				q("INSERT INTO gen_nodes_settings SET
				Nodes_ID='$ID',
				Settings='".base64_encode(serialize(stripslashes_deep($Settings)))."'");
			}
			prn($qr);
			//if menu has changed, update the relationship
			$originalPageNav=explode(',',$originalPageNav);
			if($disposition==1){
				//clear any nav relationships
				q("DELETE FROM gen_nodes_hierarchy WHERE Nodes_ID='$ID'");
				prn($qr);
			}else{
				if($originalPageNav!==$pageNav){
					if($originalPageNav[0]=='default'){
						mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
						error_alert('not developed, developer has been notified');
						//insert page's relationship to the node, ThisNodes_ID is the key variable here
						q("INSERT INTO gen_nodes_hierarchy SET
						GroupNodes_ID=$GroupNodes_ID,
						".($ThisNodes_ID ? "ParentNodes_ID=$ThisNodes_ID,":'')."
						Nodes_ID=$ID,
						Rlx='Primary',
						CreateDate=NOW(),
						Creator='".sun()."'", O_INSERTID);
						prn($qr);
					}else{
						//insert a nav node if needed
						if(!$ThisNodes_ID){
							$ThisNodes_ID=q("INSERT INTO gen_nodes SET
							Active='$Active',
							Name='$Name',
							Type='Node',
							Category='Navigation Node',
							CreateDate=NOW()", O_INSERTID);
							prn($qr);
							
							q("INSERT INTO gen_nodes_hierarchy SET
							Nodes_ID='$ThisNodes_ID',
							ParentNodes_ID=NULL,
							GroupNodes_ID=$GroupNodes_ID,
							CreateDate=NOW(),
							Creator='".sun()."'", O_INSERTID);
							prn($qr);
						}
						if($a=q("SELECT ID FROM gen_nodes_hierarchy WHERE ID='".$originalPageNav[0]."'", O_ROW)){
							//update or CREATE the page relationship
							q("UPDATE gen_nodes_hierarchy SET
							GroupNodes_ID=$GroupNodes_ID,
							ParentNodes_ID=$ThisNodes_ID,
							".(strlen($Priority) ? "Priority=$Priority,":'')."
							EditDate=NOW()
							WHERE ID='".$originalPageNav[0]."'");
						}else{
							q("INSERT INTO gen_nodes_hierarchy SET
							Nodes_ID=$ID,
							GroupNodes_ID=$GroupNodes_ID,
							ParentNodes_ID=$ThisNodes_ID,
							Rlx='".($pageUse==2?'Primary':'Secondary')."',
							".(strlen($Priority) ? "Priority=$Priority,":'')."
							CreateDate=NOW(),
							Creator='".sun()."'", O_INSERTID);
						}
						prn($qr);
					}
					$pageNavManaged=true;
				}
				//this is the PAGE NODE as expressed in hierarchy
				$a=q("SELECT ID, ParentNodes_ID, Rlx FROM gen_nodes_hierarchy WHERE Nodes_ID=$ID", O_ROW);
				if($pageUse==2 && $a['Rlx']=='Secondary'){
					//this is the new primary
					error_alert('setting this page primary (and any other pages secondary)',1);
					q("UPDATE gen_nodes_hierarchy SET Rlx=IF(Nodes_ID=$ID,'Primary','Secondary') WHERE ParentNodes_ID=".$a['ParentNodes_ID'].' AND Rlx IS NOT NULL');
					prn($qr);
				}else if($pageUse==3 && $a['Rlx']=='Primary'){
					error_alert('I have done what you asked except setting this page as a secondary page; create another page as the primary page under this navigation node, OR update another page to primary under this navigation node if it is present.  This page is still the primary page for this navigation node!');
				}
			}
		}
		$navigate=true;
		$navigateCount=1;
		if($navMode=='insert'){
			$nav=q("SELECT v.*, n.Name AS MenuName FROM _v_gen_nodes_hierarchy_nav v, gen_nodes n WHERE n.ID=v.GroupNodes_ID ORDER BY GroupNodes_ID", O_ARRAY_ASSOC);
			?><span id="pageNavWrap">
			<select name="pageNav[<?php echo $ID?>]" id="pageNav[<?php echo $ID?>]" onchange="dChge(this);" style="max-width:225px;">
				<?php if($mode==$insertMode){ ?>
				<option value="">&lt;select..&gt;</option>
				<?php }else{ ?>
				<option value="default">(Default menu)</option>
				<?php } ?>
				<!-- <option value="{RBADDNEW}">&lt;Add new..&gt;</option> -->
				<?php
				/* query is fairly complex here */
				$i=0;
				if($nav)
				foreach($nav as $n=>$v){ 
					$i++;
					if($v['MenuName']!==$buffer){
						if($i>1)echo '</optgroup>';
						?><optgroup label="<?php echo $v['MenuName']?>">
						<option value="-<?php echo $v['GroupNodes_ID'];?>" style="background-color:aliceblue;">&lt;root menu item..&gt;</option><?php
						$buffer=$v['MenuName'];
					}
					?><option value="<?php echo $n?>" <?php 
					echo @in_array($n, $selectedNavNodes) ? 'selected' : '';
					?>><?php echo h(
					$v['NameT1'] . 
					($v['NameT1'] ? ' > ':'') . $v['NameT2'] . 
					($v['NameT2'] ? ' > ':'') . $v['NameT3'] . 
					($v['NameT3'] ? ' > ':'') . $v['NameT4']
					);?></option><?php
				}
				?>
				</optgroup>
			</select>
			</span>
			<script language="javascript" type="text/javascript">
			window.parent.g('pageNavWrap').innerHTML=document.getElementById('pageNavWrap').innerHTML;
			</script><?php		
		}
		if($refreshOpener){
			?><script language="javascript" type="text/javascript">
			try{
			window.parent.detectChange=0;
			}catch(e){}
			var l=window.parent.opener.location+'';
			l=l.replace(/(&|\b)r=[.0-9]+/,'');
			l=l+(l.indexOf('?')!= -1 ? '' : '?') + (l.split('?')[1]?'&':'') + 'r=' + (Math.random()+'').substring(2,6);
			window.parent.opener.location=l;
			</script><?php
		}
	break;	
	case $mode=='updateMenu':
		if($primary==-1){
			$mode='insert';
			if($id=q("SELECT ID FROM gen_nodes WHERE Name='$Name' AND Type='Group'", O_VALUE))error_alert('That ('.$Name.') is a duplicate menu name');
			$primary=q("INSERT INTO gen_nodes SET Name='$Name', Type='Group', Category='Navigation Menu', CreateDate=NOW()", O_INSERTID);
		}
		if($Nodes_ID){
			if(!trim($newName))error_alert('You are trying to rename a menu item.  The name of the menu item cannot be blank');
			if(!trim($Description))error_alert('You are trying to rename a menu item.  You must include a description');
			q("UPDATE gen_nodes SET Name='$newName', Description='$Description' WHERE ID='$Nodes_ID'");
			prn($qr);
			
			if(trim($newSubnavName)){
				$newNodes_ID=q("INSERT INTO gen_nodes SET Name='$newSubnavName', Description='$SubnavDescription', Type='Node', CreateDate=NOW()", O_INSERTID);
				prn($qr);
				q("INSERT INTO gen_nodes_hierarchy SET Nodes_ID=$newNodes_ID, ParentNodes_ID=$Nodes_ID, GroupNodes_ID='".q("SELECT GroupNodes_ID FROM gen_nodes_hierarchy WHERE Nodes_ID=$Nodes_ID", O_VALUE)."', CreateDate=NOW(), Creator='$acct'");
				prn($qr);
				error_alert('New menu sub-item created OK',1);
			}
			error_alert('Menu name and description updated OK',1);
		}
		q("UPDATE gen_nodes SET Active=IF(ID=$primary,8,0) WHERE Type='Group' AND Category='Navigation Menu'");
		?><script language="javascript" type="text/javascript">
		<?php if($mode=='insert'){ ?>
		window.parent.location+='';
		<?php }else if($Nodes_ID){ ?>
		try{
		window.parent.g('Nodes_ID').value='';
		window.parent.g('newName').value='';
		window.parent.g('Description').value='';
		}catch(e){ }
		<?php }else{ ?>
		//window.parent.close();
		<?php } ?>
		</script>
		<?php
	break;
	case $mode=='importManager':
		$refreshComponentOnly=true;
		require_once($_SERVER['DOCUMENT_ROOT'].'/console/components/comp_900_importmanager_v104.php');
	break;
	case $mode=='insertMailProfile':
	case $mode=='updateMailProfile':
	case $mode=='deleteMailProfile':
		$refreshComponentOnly=true;
		require_once($_SERVER['DOCUMENT_ROOT'].'/console/components/comp_1000_mailer_v100.php');
	break;
	case $mode=='displayNavRename':
		$Row=q("SELECT Name, Description FROM gen_nodes WHERE ID='$Nodes_ID'",O_ROW);
		extract($Row);
		?>
		Rename menu item: <input onchange="dChge(this);" onfocus="g('Update').disabled=false;" id="newName" name="newName" type="text" value="<?php echo $Name?>" />
		<br />
		Description: <input name="Description" id="Description" type="text" onfocus="g('Update').disabled=false;" onchange="dChge(this);" value="<?php echo $Description?>" size="35" maxlength="255" />
		<input name="Nodes_ID" id="Nodes_ID" type="hidden" value="<?php echo $Nodes_ID?>" /><br />
		<h3>Append sub-nav item (optional)</h3>
		<p class="gray">If the new name is not blank, a new sub-nav menu item WILL BE CREATED</p>
		New sub-nav item name: <input onchange="dChge(this);" id="newSubnavName" name="newSubnavName" type="text" value="" />
		<br />
		Description: <input name="SubnavDescription" id="SubnavDescription" type="text" onchange="dChge(this);" value="" size="35" maxlength="255" />
		<?php
	break;
	case $mode=='deleteTemplate':
	case $mode=='insertTemplate':
		if($mode=='deleteTemplate'){
			if(q("SELECT COUNT(*) FROM gen_templates_blocks WHERE Templates_ID=$Templates_ID", O_VALUE))error_alert('Unable to delete this template.  It has blocks associated with it.  Blocks must be deleted first.');
			q("DELETE FROM gen_templates WHERE ID=$Templates_ID");
			break;
		}
		if(!$Name || !$Description)error_alert('Enter a name AND description for this template');
		if(!preg_match('/^[a-z0-9_]+$/i',$Name))error_alert('Name can only contain a-z, 0-9, and an underscore (_) character');
		if(q("SELECT ID FROM gen_templates WHERE Name='$Name'", O_VALUE))error_alert('Duplicate template name');
		$Templates_ID=q("INSERT INTO gen_templates SET Name='$Name', Description='$Description'", O_INSERTID);
		?><script language="javascript" type="text/javascript">
		window.parent.location='/console/rsc_templates.php?focus=<?php echo $Templates_ID?>';
		</script><?php
	break;
	case $mode=='insertBlock':
	case $mode=='updateBlock':
	case $mode=='deleteBlock':
		if($mode=='deleteBlock')error_alert('undeveloped');
		
	break;
	case $mode=='insertCartSettings':
	case $mode=='updateCartSettings':
		//make sure you get ALL AdminSettings nodes that are being re-written
		$AdminSettings=unserialize(base64_decode(q("SELECT AdminSettings FROM rbase_modules WHERE ID=$cartModuleId", O_VALUE, C_SUPER)));
		$AdminSettings['_settings']=stripslashes_deep($_settings);
		$AdminSettings['customTemplateString']=stripslashes($customTemplateString);
		#prn($AdminSettings);
		#error_alert('look');
		q("UPDATE rbase_modules SET AdminSettings='".base64_encode(serialize($AdminSettings))."' WHERE ID=$cartModuleId", C_SUPER);
		if(strlen($_settings['defaultPackageFieldValue'])){
			ob_start();
			q("SELECT DISTINCT Package FROM finan_items", O_COL, ERR_ECHO);
			$err=ob_get_contents();
			ob_end_clean();
			if($err){
				q("ALTER TABLE `finan_items` ADD `Package` TINYINT( 2 ) NOT NULL DEFAULT '".$_settings['defaultPackageFieldValue']."' COMMENT 'Added ".date('Y-m-d')."' AFTER `PK`, ADD INDEX (`Package`)");
			}else{
				$a=q("SHOW CREATE TABLE finan_items", O_ARRAY);
				preg_match('/`Package`.+?DEFAULT \'([0-9]+)\'/i',$a[1]['Create Table'],$m);
				if($m[1]!=$_settings['defaultPackageFieldValue']){
					q("ALTER TABLE `finan_items` CHANGE `Package` `Package` TINYINT( 2 ) NOT NULL DEFAULT '".$_settings['defaultPackageFieldValue']."' COMMENT 'Updated ".date('Y-m-d')."'");
					error_alert('items table modified successfuly',1);
				}
			}
		}
		error_alert('Cart settings successfully updated');
	break;
	case $mode=='insertUsemodSettings':
	case $mode=='updateUsemodSettings':
		if(!count($consoleEmbeddedModules))error_alert('Unable to locate modules');
		if(!($a=$consoleEmbeddedModules[$Modules_ID]))error_alert('Unable to locate this module');
		$a=$a['moduleAdminSettings'];
		foreach($usemod as $n=>$v)$a['usemod'][$n]=$v;
		prn($a,1);
		q("UPDATE rbase_modules SET AdminSettings='".base64_encode(serialize($a))."' WHERE ID=$Modules_ID", C_SUPER);
		error_alert('User settings successfully updated');
	break;
	/* 2012-07-01 */
	case $mode=='xinsertUsemodSettings':
	case $mode=='xupdateUsemodSettings':
		$getModules_ID=q("SELECT
		m.ID AS Modules_ID,
		m.SKU,
		am.Settings, m.AdminSettings
		FROM rbase_account a, rbase_AccountModules am, rbase_modules m WHERE
		a.AcctName='$acct' AND 
		a.ID=am.Account_ID AND 
		am.Modules_ID=m.ID AND m.SKU='cgi-70'", O_VALUE, C_SUPER);
		if($getModules_ID!=$Modules_ID)error_alert('Unauthorized access');
		
		
		mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($msg='remember: as you build up the usemod form, make sure checkboxes are positively set to zero *HERE*'),$fromHdrBugs);
		foreach(array('proxyLoginAllow','proxyInsertAllow') as $v){
			if(!isset($usemod[$v]))$usemod[$v]=0;
		}
	
		//make sure you get ALL AdminSettings nodes that are being re-written
		$AdminSettings=unserialize(base64_decode(q("SELECT AdminSettings FROM rbase_modules WHERE ID=$Modules_ID", O_VALUE, C_SUPER)));
		$AdminSettings['usemod']=stripslashes_deep($usemod);
		q("UPDATE rbase_modules SET AdminSettings='".base64_encode(serialize($AdminSettings))."' WHERE ID=$Modules_ID", C_SUPER);
		error_alert('User account settings successfully updated');
	break;

	case $modePassed=='insertDatasetComponent':
	case $modePassed=='updateDatasetComponent':
	case $modePassed=='deleteDatasetComponent':
		$refreshComponentOnly=true;
		require($COMPONENT_ROOT.'/comp_1000_creator_v101.php');
	break;
	case $mode=='deletePage':
		if(q("SELECT LCASE(Type) FROM gen_nodes WHERE ID=$Nodes_ID",O_VALUE)!='object'){
			error_alert('Improper node call, this is not a page object');
		}
		//get object parent node(s)
		$objects=q("SELECT h.ParentNodes_ID, LCASE(n.Type) FROM gen_nodes_hierarchy h, gen_nodes n WHERE h.ParentNodes_ID=n.ID AND h.Nodes_ID='$Nodes_ID'", O_COL_ASSOC);
		prn($objects);
		prn($qr);
		
		//foreach node the object is part of, promote another node if present, OR delete the node
		if(count($objects))
		foreach($objects as $node=>$type){
			if($type!=='node'){
				mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err='not a node, see the code'),$fromHdrBugs);
				continue;
			}
			$a=q("SELECT h.ID FROM gen_nodes_hierarchy h, gen_nodes n WHERE h.Nodes_ID=n.ID AND n.Type='Object' AND h.ParentNodes_ID=$node AND n.ID !=$Nodes_ID", O_COL)/*no child objects*/;
			prn('a');
			prn($qr);
			$b=q("SELECT h.ID FROM gen_nodes_hierarchy h, gen_nodes n WHERE h.Nodes_ID=n.ID AND n.Type='Node' AND h.ParentNodes_ID=$node AND n.ID !=$Nodes_ID", O_COL)/*no child nodes*/;
			prn('b');
			prn($qr);
			if( !$a && !$b){
				prn('delete');
				q("DELETE FROM gen_nodes WHERE ID=$node");
				prn($qr);
				q("DELETE FROM gen_nodes_hierarchy WHERE Nodes_ID=$node");
				prn($qr);
			}
			if($a){
				prn('update');
				//bring this page to primary status
				q("UPDATE gen_nodes_hierarchy SET Rlx='Primary' WHERE ID='".current($a)."'");
				prn($qr);
			}
			if($b){
				//no action - these are sub-nodes
			}
		}
		//delete the object and assoc records
		ob_start();
		q("CREATE TABLE IF NOT EXISTS gen_nodes_deleted SELECT * FROM gen_nodes WHERE 0", ERR_ECHO);
		q("INSERT INTO gen_nodes_deleted SELECT * FROM gen_nodes WHERE ID='$Nodes_ID'", ERR_ECHO);
		q("DELETE FROM gen_nodes WHERE ID='$Nodes_ID'", ERR_ECHO);

		q("CREATE TABLE IF NOT EXISTS gen_nodes_settings_deleted SELECT * FROM gen_nodes_settings WHERE 0", ERR_ECHO);
		q("INSERT INTO gen_nodes_settings_deleted SELECT * FROM gen_nodes_settings WHERE Nodes_ID='$Nodes_ID'", ERR_ECHO);
		q("DELETE FROM gen_nodes_settings WHERE Nodes_ID='$Nodes_ID'", ERR_ECHO);

		q("CREATE TABLE IF NOT EXISTS gen_nodes_hierarchy_deleted SELECT * FROM gen_nodes_hierarchy WHERE 0", ERR_ECHO);
		q("INSERT INTO gen_nodes_hierarchy_deleted SELECT * FROM gen_nodes_hierarchy WHERE '$Nodes_ID' IN(Nodes_ID, ParentNodes_ID)", ERR_ECHO);
		q("DELETE FROM gen_nodes_hierarchy WHERE '$Nodes_ID' IN(Nodes_ID, ParentNodes_ID)", ERR_ECHO);

		q("CREATE TABLE IF NOT EXISTS cmsb_sections_deleted SELECT * FROM cmsb_sections WHERE Objects_ID=$Nodes_ID", ERR_ECHO);
		q("INSERT INTO cmsb_sections_deleted SELECT * FROM cmsb_sections WHERE Objects_ID=$Nodes_ID", ERR_ECHO);
		q("DELETE FROM cmsb_sections WHERE Objects_ID=$Nodes_ID", ERR_ECHO);
		$err=ob_get_contents();
		ob_end_clean();
		if($err){
			mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err='error in storing deleted nodes'),$fromHdrBugs);
		}
		?><script language="javascript" type="text/javascript">
		window.parent.g('r_<?php echo $_REQUEST['Nodes_ID'];?>').style.display='none';
		</script><?php	
	break;
	case $mode=='exportData':
		require($COMPONENT_ROOT.'/comp_901_exportmanager_v100.php');
	break;
	case $mode=='insertIssue':
	case $mode=='updateIssue':
	case $mode=='deleteIssue':
		if($mode==$deleteMode){
			if(false /* no-delete criteria */){
				error_alert('You cannot delete this record');
			}
			q("DELETE FROM per_issues WHERE ID=$Issues_ID");
			$navigate=true;
			$navigateCount=$count - 1;
			break;
		}
		//data conversion
		
		//error checking
		if(false)error_alert('Example error here');
		

		if($mode==$insertMode)unset($ID);
		$sql=sql_insert_update_generic($MASTER_DATABASE,'per_issues', $mode, '', $options);
		$assumeErrorState=false;
		$fl=__FILE__; $ln=__LINE__;
		$x=q($sql, O_INSERTID);
		prn($qr);
		if($mode==$insertMode)$ID=$x;
		$navigate=true;
		$navigateCount=$count + 1;
	break;
	case $mode=='catsubcatchanger':
		foreach($SubCategory as $Category=>$v){
			foreach($v as $SubCategory=>$new){
				$Category=str_replace('(none)','',$Category);
				$SubCategory=str_replace('(none)','',$SubCategory);
				q("UPDATE finan_items SET SubCategory='$new' WHERE Category='$Category' AND SubCategory='$SubCategory'");
			}
		}
		foreach($Category as $Cat=>$new){
			q("UPDATE finan_items SET Category='$new' WHERE Category='$Cat'");
		}
		if($refresh){
			require('../components/comp_cat_subcat_changer_v100.php');
			?><script language="javascript" type="text/javascript">
			window.parent.g('catsubcat').innerHTML=document.getElementById('catsubcat').innerHTML;
			</script><?php
		}
	break;
	case $mode=='insertObject':
	case $mode=='updateObject':
	case $mode=='deleteObject':
		require(str_replace('resources/bais_01_exe.php','systementry.php',__FILE__));
	break;
	//2012-12-16: note PLURAL values
	case $mode=='insertObjects':
	case $mode=='updateObjects':
	case $mode=='deleteObjects':
#--> NOTE: this is requiring the COMPONENT because the page is just a shell
		require(str_replace('resources/bais_01_exe.php','components/comp_1000_systementry_dataobject_v100.php',__FILE__));
	break;
	case $mode=='insertPayment':
	case $mode=='updatePayment':
	case $mode=='deletePayment':
#--> NOTE: here the page has coding that is important - this is inconsistent
		require(str_replace('resources/bais_01_exe.php','rfm_payments.php',__FILE__));
	break;
	case $mode=='listAdder':
		if($submode=='imageTags'){
			if($subsubmode=='deleteTag'){
				$result=q("DELETE FROM relatebase_ObjectsTree WHERE
				ObjectName='gen_tags' AND
				Objects_ID='".preg_replace('/[^0-9]/','',$Tags_ID)."' AND
				Tree_ID='".preg_replace('/[^0-9]+/','',$bindTo)."'");
			}else{
				if($Tags_ID){
					//OK
				}else{
					$Tags_ID=q("INSERT INTO gen_tags SET Name='$Name'", O_INSERTID);
				}
				if($bindTo){
					$result=q("INSERT INTO relatebase_ObjectsTree SET
					ObjectName='gen_tags',
					Objects_ID='".preg_replace('/[^0-9]/','',$Tags_ID)."',
					Tree_ID='".preg_replace('/[^0-9]+/','',$bindTo)."',
					CreateDate=NOW()");
				}
			}
			echo json_encode(array(
				'OK'=>($result ? true : false),
				'Tags_ID'=>$Tags_ID,
			));
		}else if($submode=='updateField'){
			if($bindTo)	$result=q("UPDATE $table SET $field='$string', EditDate=EditDate WHERE ID=$bindTo");
			echo json_encode(array(
				'OK'=>($result ? true : false),
				'ID'=>$bindTo,
				'table'=>$table,
				'field'=>$field,
			));
		}else if($submode=='getItemNamesByLetters'){
			/* kill it */
			if($bindTo)	$result=q("UPDATE finan_items SET Name='$string', EditDate=EditDate WHERE ID=$bindTo");
			echo json_encode(array(
				'OK'=>($result ? true : false),
				'ID'=>$bindTo,
			));
		}
		$assumeErrorState=false;
		$suppressNormalIframeShutdownJS=true;
		exit;
	break;
	case $mode=='listBuilder':
		$letters = preg_replace("/[^a-z0-9 ]/si","",$letters);
		if($submode=='getImageTagsByLetters'){
			if($res=q("SELECT ID, Name FROM gen_tags WHERE Name LIKE '".$letters."%' ORDER BY Name", O_ARRAY_ASSOC)){
				echo json_encode($res);
			}
		}else if($submode=='getItemNamesByLetters'){
			if($res=q("SELECT Name AS ID, Name FROM finan_items WHERE Name LIKE '".$letters."%' ORDER BY Name", O_ARRAY_ASSOC))echo json_encode($res);
		}else if($submode=='getItemCategoriesByLetters'){
			if($res=q("SELECT Category AS ID, Category AS Name FROM finan_items WHERE Category LIKE '".$letters."%' ORDER BY Category", O_ARRAY_ASSOC))echo json_encode($res);
		}else if($submode=='getItemActiveByLetters'){
			//2012-12-02: too short - will never be called!
			if($res=q("SELECT Active AS ID, Active AS Name FROM finan_items WHERE Active LIKE '".$letters."%' ORDER BY Active", O_ARRAY_ASSOC))echo json_encode($res);
		}

		$assumeErrorState=false;
		$suppressNormalIframeShutdownJS=true;
		exit;
	break;

	/* relative* modes added 2013-04-02 from Fo sTex */
	case $mode=='createRelativeFolder':	
		if($Tree_ID){	
			$newTree_ID=q("INSERT INTO relatebase_tree SET Tree_ID='$Tree_ID', Name='$folderName', Type='Folder', CreateDate=NOW(), Creator='".sun()."'", O_INSERTID);
			?><script language="javascript" type="text/javascript">
			window.parent.location=window.parent.location+'';
			</script><?php
		}
	break;
	case $mode=='uploadRelativeFile':
		$handle=substr(md5(time().rand(100,10000)),0,5).'_';
		$ext=strtolower(end(explode('.',$_FILES['localFile']['name'])));
		if(!preg_match('/^(gif|jpg|png|xls|xlsx|doc|docx|pdf|txt|html|htm|tif|tiff|xif)$/',$ext))error_alert($ext.' is not an allowed extension! The following are allowed: gif,jpg,png,xls,xlsx,doc,docx,pdf,txt,html,htm,tif,tiff,xif');
		if(!is_uploaded_file($_FILES['localFile']['tmp_name']))error_alert('Abnormal error, unable to upload file');

		$fileFullPath=$_SERVER['DOCUMENT_ROOT'].'/images/documents/library/'.$handle.stripslashes($_FILES['localFile']['name']);
		if(!move_uploaded_file($_FILES['localFile']['tmp_name'],$fileFullPath)){
			mail($developerEmail,'error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
			error_alert('Unable to move file to the general folder');
		}
		q("INSERT INTO relatebase_tree SET Tree_ID='$Tree_ID', Name='".$handle.$_FILES['localFile']['name']."', Type='file', CreateDate=NOW(), Creator='".sun()."'");
		?><script language="javascript" type="text/javascript">
		window.parent.location=window.parent.location+'';
		</script><?php
	break; 
	case $mode=='downloadRelativeFile':
		$file=q("SELECT * FROM relatebase_tree WHERE ID='$file_ID'",O_ROW);
		if(!file_exists($path=$_SERVER['DOCUMENT_ROOT'].'/images/documents/library/'.$file['Name']))error_alert('Unable to locate this file; it may have been deleted '.$path);
		$nameAs=explode('_',$file['Name']);
		unset($nameAs[0]);
		$nameAs=implode('_',$nameAs);
		header ("Accept-Ranges: bytes");  
		header ("Connection: close");  
		header ("Content-type: application/octet-stream");  
		header ("Content-Length: ". filesize($path));   
		header ("Content-Disposition: attachment; filename=\"".$nameAs."\"");
		ob_clean();
		readfile($path);
		$suppressNormalIframeShutdownJS=true;
		$assumeErrorState=false;
		exit;
	break;
	case $mode=='deleteRelativeFile':
		if($file_ID){
			$fileName=q("SELECT Name FROM relatebase_tree WHERE ID='$file_ID'",O_VALUE);
			if(file_exists($_SERVER['DOCUMENT_ROOT'].'/images/documents/library/'.$fileName)){
				if(unlink($_SERVER['DOCUMENT_ROOT'].'/images/documents/library/'.$fileName)) error_alert('Successfully Deleted',1);
				q("DELETE FROM relatebase_tree WHERE ID='$file_ID'");
			} else {
				error_alert('Was not able to delete file, file does not exist on the server');
			}
		}else if($Folder_ID){
			tree_delete_children($Folder_ID,$options=array('customFileRoot'=>$_SERVER['DOCUMENT_ROOT'].'/images/documents/library/'));
		}
		?><script language="javascript" type="text/javascript">
		window.parent.location=window.parent.location+'';
		</script><?php
	break;
	case $mode=='insertNode':
	case $mode=='updateNode':
	case $mode=='deleteNode':
		if($mode=='deleteNode')error_alert('not developed');
		if($originalPriority!==$Priority){
			foreach($Priority as $n=>$v)if(!preg_match('/^[1-9][0-9]*$/',$v))error_alert('At least one priority value is not an integer between 1 and 999 (1=left or top, 999=right or bottom');
			asort($Priority);
			$s=0;
			foreach($Priority as $n=>$v){
				$s++;
				q("UPDATE gen_nodes_hierarchy SET Priority=".($condense?$s:$v).", editdate=EditDate WHERE ID=$n");
			}
			//prevent this from getting in any future query
			unset($_POST['Priority'],$Priority);
		}
		if($Name!==$originalName){
			$sql="UPDATE gen_nodes SET Name='$Name' WHERE ID=$Nodes_ID";
			q($sql);
			prn($qr);
		}
		foreach($pageNav as $n=>$v)if(strlen(trim($v)))$p[$v]=$v;
		ksort($p);
		$pageNav=$p;
		if(strlen($originalPageNav)){
			foreach(explode(',',$originalPageNav) as $v)if(strlen(trim($v)))$op[$v]=$v;
			ksort($op);
			$originalPageNav=$op;
		}else{
			$originalPageNav=array();
		}
		if($originalPageNav!==$pageNav){
			foreach($pageNav as $n=>$v){
				if($originalPageNav[$n]){
					//update
					error_alert('update of hierarchy node not developed',1);
					unset($originalPageNav[$n]);
				}else{
					//insert
					if(!($pn=q("SELECT Nodes_ID FROM gen_nodes_hierarchy WHERE GroupNodes_ID=$GroupNodes_ID AND ID=$n",O_VALUE))){
						if($n>0)continue;
					}
					$Hierarchy_ID=q("INSERT INTO gen_nodes_hierarchy SET
					Nodes_ID='$Nodes_ID',
					ParentNodes_ID=".($n<0?'NULL':$pn).",
					GroupNodes_ID='$GroupNodes_ID',
					CreateDate=NOW(),
					Creator='".sun()."'", O_INSERTID);
					prn($qr);
				}
			}
			if(count($originalPageNav)){
				$a=q("SELECT * FROM gen_nodes_hierarchy WHERE ID IN(".implode(',',$originalPageNav).")", O_ARRAY);
				prn($a);
				foreach($originalPageNav as $v){
					if($v<0){
						q("DELETE FROM gen_nodes_hierarchy WHERE GroupNodes_ID=".($v*-1)." AND Nodes_ID=$Nodes_ID AND ParentNodes_ID IS NULL");
					}else{
						q("DELETE FROM gen_nodes_hierarchy WHERE ID=$v");
					}
					prn($qr);
				}
			}
		}
		if($move)error_alert('move not developed',1);
		$navigate=true;
		$navigateCount=10;
	break;
	case $mode=='updateTicket':
	case $mode=='insertTicket':
	case $mode=='deleteTicket':
		error_alert($submode);
	break;
	case $mode=='updateLabel':
	case $mode=='insertLabel':
	case $mode=='deleteLabel':
		require($COMPONENT_ROOT.'/comp_950_labels_v100.php');
	break;
	case $mode=='componentControls':
		//2013-11-23: refer to Juliet component - no redundancy of coding here
		$suppressPrintEnv=true; //already done
		require($_SERVER['DOCUMENT_ROOT'].'/index_01_exe.php');
	break;
	case $mode=='downloadFile':
		if(!q("SELECT ID FROM relatebase_tree WHERE ID=$Tree_ID AND Name='$file'", O_VALUE)){
			mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
			error_alert('File mismatch');
		}
		$path=$_SERVER['DOCUMENT_ROOT'].tree_id_to_path($Tree_ID);
		$nameAs=preg_replace('/^[a-z0-9]+_/i','',$file);
		header ("Accept-Ranges: bytes");  
		header ("Connection: close");  
		header ("Content-type: application/octet-stream");  
		header ("Content-Length: ". filesize($path));   
		header ("Content-Disposition: attachment; filename=\"$nameAs\"");
		
		readfile($path);
		$suppressNormalIframeShutdownJS=true;
		$assumeErrorState=false;
		eOK();
	break;
	default:
		mail($developerEmail,'error in file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
		error_alert('No mode submitted or mode '.$mode.' is unrecognized');
}
$assumeErrorState=false;

//navigation section - configured by each mode above
if($navigate && $navigateCount){
	navigate($navigateCount, $navigateOptions);
}
?>
