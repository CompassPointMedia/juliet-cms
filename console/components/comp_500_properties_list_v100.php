<?php
/*
Created 2010-08-26 by Samuel - copied over from comp_50_children_list_v200.  

the objectives on this view are 
1. CHANGE THE COLORS!!! tired of the same old thing
	b. color coding to indicate status
2. to show categorization.
3. why are we not sorting?
4. I want a report on who's taken that training
the categories for classification=annual vs. classifcation=pre|inservice are huge differences
how many times taken


*/

$dataset='properties'; 					#more of a concept
$datasetComponent='propertiesList';		#THIS physical component
$datasetGroup=$dataset; 					//as of 2010-04-04, this is not used
if(!$datasetWord)$datasetWord='Property Listing';
if(!$datasetWordPlural)$datasetWordPlural='Property Listings';
$datasetFocusPage='focus_properties.php';
$datasetAddObjectJSFunction='ow(this.href,\'l1_properties\',\'800,700\',true);'; //this is because opening an object is not well developed yet
$datasetQueryStringKey='Properties_ID';
$datasetDeleteMode='deleteFeaturedProperty';

$datasetQuery=''; 							//this is left blank for list_members; needed because a view didn't contain the same data as a query!
$datasetTable='re1_properties';				//this can be a single MySQL table or a view
$datasetTableIsView=true;
$datasetArrayType=O_ARRAY_ASSOC;			//added 2010-05-10 - this allows for non-standard left-column-equals-primary-key constructions; default=O_ARRAY_ASSOC
$datasetFieldList='*';
$modApType='embedded';
$modApHandle='first';
$globalBatchThreshold='10000';
$tbodyScrollingThreshold=20;


$datasetTheme='';
$footerDisposition='tabularControls'; 		//however, the footer needs to show to nav a large batch like this
$datasetHideFooterAddLink=true;
$hideColumnSelection=false;

//2010-06-03: for gf_children this is not used initially
$datasetShowBreaks=false;
/*
$datasetBreakFields=array(
	1=>array(
		'column'=>'Office',
		'blank'=>'not specified'
	),
	2=>array(
		'column'=>'HomeName',
		'blank'=>'not specified'
	)
);
*/

$focusViewDeviceFunction='';							#not used initially
$datasetAdditionalClassFunction='';						#not used initially
$datasetOverrideSort='';								#not used initially

function prlist($param){
	global $record;
	ob_start();
	switch($param){
		case 'PostDate':
			echo date('m/d \a\t g:iA',strtotime($record['PostDate']));
		break;
		case 'Price':
			if($record['Price']){
				echo number_format($record['Price'],2);
			}else{
				echo '--';
			}
		break;
		case 'Description':
			$a=explode(' ',strip_tags($record['Description']));
			for($i=0;$i<=25;$i++){
				echo ' '.$a[$i];
			}
			if(count($a)>25)echo '...';
		break;
		case 'OnPage':
			echo str_replace(',',',<br />',$record['ShowCategory']);
		break;
		case 'Pictures':
			if(is_dir($_SERVER['DOCUMENT_ROOT'].'/images/slides/'.$record['Handle'])){
				if($fp=opendir($_SERVER['DOCUMENT_ROOT'].'/images/slides/'.$record['Handle'])){
					while(false!==($file=readdir($fp))){
						if(!preg_match('/(gif|jpg|png)$/i',$file))continue;
						$files++;
					}
				}
			}
			?>[<a href="../admin/file_explorer/?folder=slides/<?php echo $record['Handle']?>" title="View Images" onClick="return ow(this.href,'l1_propertyimages','800,800');"><?php echo $files . ' pictures';?>]</a><?php
		break;
		case 'Order':
			?>[<a title="sort order for pictures" onClick="return ow(this.href,'l1_sort','700,700');" href="focus_properties_order.php?Properties_ID=<?php echo $record['ID']?>">order</a>]<?php
		break;
		case 'Web':
			if($b=q("SELECT * FROM re1_properties_domain WHERE ID='".$record['ID']."'", O_ROW)){
				$style='style="background-color:lightgreen;"';
			}else{
			}
			?><div style="<?php echo $style?>"><a title="configure site domain" onClick="return ow(this.href,'l1_property','800,700');" href="focus_featured_properties_domain.php?Properties_ID=<?php echo $record['ID']?>&cbFunction=refreshList"><?php
			echo $b?'Yes':'No';
			?></a></div><?php
		break;
		case 'Priority':
			?>
			<a href="list_properties.php?ID=<?php echo $record['ID']?>&dir=1"><img src="../images/i/red-up-toggle.jpg" alt="Move Up" /></a><br />
			<a href="list_properties.php?ID=<?php echo $record['ID']?>&dir=-1"><img src="../images/i/red-down-toggle.jpg" alt="Move Down" /></a>
			<?php
		break;
	}
	$out=ob_get_contents();
	ob_end_clean();
	return $out;
}

//declare the properties of the dataset->component
$availableCols[$datasetGroup][$modApType][$modApHandle]=array(
	'scheme'=>array(
		'Priority'=>array(
			'header'=>'',
			'method'=>'function',
			'fieldExpressionFunction'=>'prlist("Priority")',
		),
		'Status'=>array(
		
		),
		'PostDate'=>array(
			'header'=>'Added',
			'method'=>'function',
			'fieldExpressionFunction'=>'prlist("PostDate")',		
		),
		'MLSNumber'=>array(
		
		),
		'PropertyName'=>array(
			'header'=>'title',
		),
		'Address'=>array(
		
		),
		'City'=>array(
		
		),
		'Price'=>array(
			'method'=>'function',
			'fieldExpressionFunction'=>'prlist("Price")',		
		),
		'Description'=>array(
			'method'=>'function',
			'fieldExpressionFunction'=>'prlist("Description")',		
		),
		'OnPage'=>array(
			'method'=>'function',
			'fieldExpressionFunction'=>'prlist("OnPage")',		
		),
		'Pictures'=>array(
			'method'=>'function',
			'fieldExpressionFunction'=>'prlist("Pictures")',
			'nowrap'=>true,
		),
		'Order'=>array(
			'header'=>'Sorting',
			'method'=>'function',
			'fieldExpressionFunction'=>'prlist("Order")',		
		),
		'Web'=>array(
			'header'=>'<img src="/images/i/globe_32.png" alt="link" title="dedicated website" width="32" height="32" />',
			'method'=>'function',
			'fieldExpressionFunction'=>'prlist("Web")',		
		),
	)
);

$datasetCustomAttributes=array(
	/* 'transferrable'=>'recordAttributes("transferrable")' */
);
function recordAttributes($n){
	//left over from list_children view
	global $record;
	if($n=='transferrable')return $record['Assigned'];
}

//2010-06-03 converted active/inactive to discharged/in care
$datasetActiveUsage=true;
$datasetActiveActiveExpression='Active=1';
$datasetActiveInactiveExpression='Active=0';
$datasetActiveAllExpression='1';
$datasetActiveField='Active';
//allow this parameter to be passed remotely
/*
if(!isset($hideObjectInactiveControl))$hideObjectInactiveControl=false;
$datasetActiveControl='dischargeChild2(".$$Dataset_ID.", ".($$datasetActiveField?1:0).");';
$datasetActiveActivateTitle='Re-assign this foster child to a home';
$datasetActiveInactivateTitle='Discharge this foster child';
$datasetInternalFilter=(min($_SESSION['admin']['roles'])>ROLE_FOUNDATION_DIRECTOR ? "ID IN('".implode("','",list_children('keys'))."')" : '');
*/

$datasetShowDeletion=true;
/*
2010-01-26: modified getting and setting of these vars
*/
require($MASTER_COMPONENT_ROOT.'/dataset_generic_precoding_v103.php');


//html output items
if(!$refreshComponentOnly){
	?><style type="text/css">
	/* -- from filter gadget -- */
	<?php echo $filterGadgetCSS?>
	.frb{
		float:right;
		margin-left:5px;
		}
	.flb{
		float:left;
		margin-right:8px;
		}
	.frb a, .flb a{
		color:#000;
		}
	<?php
	dataset_complexDataCSS(array(
		'datasetColorHeader_'=>'114d12',
		'datasetColorRowAlt_'=>'cddccd',
		'datasetColorSorted_'=>'wheat',
	));
	?>
	</style>
	<script language="javascript" type="text/javascript">
	/* -- from filter gadget -- */
	<?php echo $filterGadgetJS?>

	hl_bg['childopt']='#6c7093';
	hl_baseclass['childopt']='normal';
	hl_class['childopt']='hlrow';
	//hl_txt['childopt']='';
	//declare the ogrp.handle.sort value even if blank
	ogrp['childopt']=new Array();
	ogrp['childopt']['sort']='';
	ogrp['childopt']['rowId']='';
	AssignMenu('^r_([0-9]+)$', 'childOptions');
	AssignMenu('^optionsChildren$', 'optionsChildrenMenu');
	AssignMenu('^reportsChildren$', 'reportsChildrenMenu');
	</script><?php
}
require($MASTER_COMPONENT_ROOT.'/dataset_component_v123.php');
?>