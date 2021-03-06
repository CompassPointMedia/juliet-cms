<?php
/*
2010-06-23:
in this case I have created this component before the list view, for the focus view. In FOCUS VIEW, you should declare the following:

	$datasetFocusViewCall=true; //this will block the appropriate sections of code from outputting content

Integrating the following attributes for nodes in the view scheme:
	fieldvalues	=> array('Horse','Cat','Dog')
				or array('1'=>'Horse','2'=>'Cat','3'=>'Dog'); //NOTE! if assoc. array, keys must be declared as string
	defaultfieldvalue => '1" || 'Horse'
		- obviously this could be a pre-declared query using q().
*/
$dataset='Content'; 					#more of a concept
$datasetComponent='contentList'; 		#THIS physical component
$datasetGroup=$dataset; 				//as of 2010-04-04, this is not used
if(!$datasetWord)$datasetWord='Article';
if(!$datasetWordPlural)$datasetWordPlural='Articles';
$datasetFocusPage='focus_articles.php';
$datasetAddObjectJSFunction='ow(this.href,\'l1_articles\',\'800,700\',true);'; //this is because opening an object is not well developed yet
$datasetQueryStringKey='ID';
$datasetDeleteMode='deleteContent';

$datasetQuery=''; 						//this is left blank for list_members; needed because a view didn't contain the same data as a query!
$datasetTable='cms1_articles';			//this can be a single MySQL table or a view
$datasetTableIsView=false;
$datasetArrayType=O_ARRAY_ASSOC;		//added 2010-05-10 - this allows for non-standard left-column-equals-primary-key constructions; default=O_ARRAY_ASSOC
$datasetFieldList='*';
$modApType='embedded';
$modApHandle='first';
$globalBatchThreshold='10000';


$datasetTheme='';
$footerDisposition='tabularControls'; 	//however, the footer needs to show to nav a large batch like this
$datasetHideFooterAddLink=true;
$hideColumnSelection=false;

//2010-06-03: for gf_items this is not used initially
$datasetShowBreaks=false;
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

$focusViewDeviceFunction='';			#not used initially
$datasetAdditionalClassFunction='';		#not used initially
$datasetOverrideSort='';				#not used initially


//declare the properties of the dataset->component

$availableCols[$datasetGroup][$modApType][$modApHandle]=array(
	'scheme'=>array(
		'Category'=>array(
			'fieldvalues' => array('Article','Bulletin/Announcement','Press Release','Blog Entry','Forum Post','Video'),
		),
	)
);
if($_SERVER['DOCUMENT_ROOT']=='/home/cpm160/public_html'){
$availableCols[$datasetGroup][$modApType][$modApHandle]=array(
	'scheme'=>array(
		'Category'=>array(
			'fieldvalues' => array('Article','Sliding Article','Bulletin/Announcement','Press Release','Blog Entry','Forum Post','Video'),
		),
	)
);
}
/*
$datasetCustomAttributes=array(
	'transferrable'=>'recordAttributes("transferrable")'
);
function recordAttributes($n){
	global $record;
	if($n=='transferrable')return $record['Assigned'];
}
*/

//2010-06-03 converted active/inactive to discharged/in care
$datasetActiveUsage=true;

//allow this parameter to be passed remotely
if(!isset($hideObjectInactiveControl))$hideObjectInactiveControl=false;

$datasetShowDeletion=true;



/*
2010-06-05: filter gadget instance - note that for this to work we have some filter gadget parameters ABOVE the precoding because of the record query.  Yet we have the actual filter gadget BELOW the precoding.  This is because precoding needs certain parameters which are really related to the filter gadget, which sets in session or posts certain elements which the precoding depends on
*/
$useStatusFilterOptions=false;
$outputStatusFilterOptions=false;
$statusFilterField='';

require($MASTER_COMPONENT_ROOT.'/dataset_generic_precoding_v102.php');

ob_start();
$filterGadgetHideCSS=true;
$filterGadgetHideJS=true;
$filterGadgetCSSInternal=true;
$filterGadgetJSInternal=true;
$filterGadgetPassthroughFields=array('Fosterhomes_ID','passedResourceToken','cb');

if(!$datasetFocusViewCall)require($MASTER_COMPONENT_ROOT.'/comp_01_filtergadget_v105.php');

if(!$datasetFocusViewCall){
	//add button now at top using iconset
	?><div class="frb">
		<a href="<?php echo $datasetFocusPage?>?cbFunction=refreshComponent&cbParam=fixed:<?php echo $datasetComponent?>" onclick="return <?php echo $datasetAddObjectJSFunction ? $datasetAddObjectJSFunction : 'add'.$dataset.'()'?>"><img src="/images/i/s/hlw-25x25-9EA9B4/plus.png" style="margin-top:7px;" />&nbsp;Add <?php echo strtolower($datasetWord);?></a>&nbsp;
	</div>
	<!-- options button -->
	<div class="frb">
		<a id="optionsItems" title="View Options" href="javascript:;" onclick="hidemenuie5(event,1);showmenuie5(event,1)" oncontextmenu="return false;"><img src="/images/i/options1.png" alt="Foster Items" width="32" height="32" /> Options</a>&nbsp;&nbsp;
	</div>
	<!-- reports button -->
	<div class="frb">
		<a id="reportsItems" title="View Foster Items Reports" href="javascript:;" onclick="hidemenuie5(event,1);showmenuie5(event,1)" oncontextmenu="return false;"><img src="/images/i/addr_26x27.gif" width="26" height="27" style="margin-top:5px;" /> Reports</a>
	</div>
	
	
	<!-- context menus -->
	<div id="childOptions" class="menuskin1" style="z-index:1000;" onMouseOver="hlght2(event)" onMouseOut="llght2(event)" onclick="executemenuie5(event)" precalculated="childoptionsPre()">
		<div id="cho1" default="1" style="font-weight:900;" class="menuitems" command="openItem()" status="Edit this item">
		Edit Item</div>
		<div id="cho2" class="menuitems" command="itemAction(event, 'delete');" status="Delete this item">
		Delete</div>
	</div>
	<div id="optionsItemsMenu" class="menuskin1" style="z-index:1000;width:225px;" onMouseOver="hlght2(event)" onMouseOut="llght2(event)" onclick="executemenuie5(event)" precalculated="optionsItems();">
		<div id="oh01" style="font-weight:900;" class="menuitems" command="addItem();" status="Add a new item">
		New Item</div>
	</div>
	<div id="reportsItemsMenu" class="menuskin1" style="z-index:1000;" onMouseOver="hlght2(event)" onMouseOut="llght2(event)" onclick="executemenuie5(event)">
		<div id="rc02" nowrap="nowrap" class="menuitems" command="item_report('CSV');" status="Complete Export of the Current Data">
		Export Data as CSV (Spreadsheet)</div>
	</div>
	
	<?php
	
	//now save this for later output in the component
	$datasetPreContent=get_contents();
}

//html output items
if(!$refreshComponentOnly && !$datasetFocusViewCall){
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

	.complexData thead{						/* header, non-sorted */
		background-color:#b4a99e;
		}
	.complexData th{
		color:#FFF;
		}
	.complexData th.sorteddesc, .complexData th.sortedasc{					/* header, sorted */
		background-color:#a48e7c;
		background-color:#cd8023;
		}
	.complexData tr.alt{					/* row, alt color */
		background-color:#faeaea;
		}
	.complexData tr.alt td.sorted{			/* row-alt, col-sorted */
		background-color:#f1d5c8;
		}
	.complexData td.sorted{					/* row-normal, col-sorted */
		background-color:#f6e8da;
		}
	.hlrow td{							/* h() with new className change */
		background-color:#f6aaaa;
		}
	.hlrow td.sorted{
		background-color:#f69b91;		/* highlight-sorted (but can't differentiate :( */
		}
	</style>
	<script language="javascript" type="text/javascript">
	/* -- from filter gadget -- */
	<?php echo $filterGadgetJS?>

	hl_bg['itemopt']='#6c7093';
	hl_baseclass['itemopt']='normal';
	hl_class['itemopt']='hlrow';
	//hl_txt['itemopt']='';
	//declare the ogrp.handle.sort value even if blank
	ogrp['itemopt']=new Array();
	ogrp['itemopt']['sort']='';
	ogrp['itemopt']['rowId']='';
	AssignMenu('^r_([0-9]+)$', 'itemOptions');
	AssignMenu('^optionsItems$', 'optionsItemsMenu');
	AssignMenu('^reportsItems$', 'reportsItemsMenu');
	function itemoptionsPre(){
		for(var j in hl_grp['itemopt'])j=j.replace('r_','');
	}
	function homeHistory(j){
		var toldYa=false;
		var i=0;
		var h=g('r_'+j).getAttribute('homes');
		var homes=h.split(',');
		for(var k in homes){
			if(!homes[k])continue;
			i++;
			if(i>1 && !toldYa){
				toldYa=true;
				alert('This item has been assigned to multiple homes; opening a home history report for each home');
			}
			ow('homehistory.php?Fosterhomes_ID='+homes[k],'l1_homehistory','700,700','rand');
		}
	}
	function itemAction(event, action){
		for(var j in hl_grp['itemopt'])j=j.replace('r_','');
		if(!j){
			alert('Select a item first');
			return;
		}
		if(action=='delete'){
			if(!g('r_'+j).getAttribute('deletable')){
				if(confirm('This item cannot be deleted; he or she has been associated with a foster home.  You must first delete all assignment and progress notes/reports related to this item.\n\nWould you like to see a home history report report for this item?')){
					homeHistory(j);
				}
				return false;
			}
			if(!confirm('This will permanently delete this item from the database.  Are you sure?'))return false;
			window.open('resources/bais_01_exe.php?mode=deleteChild&Items_ID='+j+'&cb=refresh','w2');
		}else if(action=='report'){
			if(!j){
				alert('First click on a home and highlight its row');
				return;
			}
			homeHistory(j);
		}else if(action=='discharge'){
			if(!g('r_'+j).getAttribute('transferrable')){
				alert('This item cannot be discharged, most likely because they are not currently assigned to a home');
				return false;
			}
			ow('item_discharge.php?Items_ID='+j,'l1_discharge','550,600');
		}else if(action=='CMLog'){
			window.open('casemanager_logs.php?Assignment_ID='+g('r_'+j).getAttribute('assignmentid')+'&Items_ID='+j, 'l1_cmlogs', '700,400');
		}
	}
	function addChild(){
		ow('children.php','l1_children','700,700');
		return false;
	}
	function openChild(){
		for(var j in hl_grp['itemopt'])j=j.replace('r_','');
		ow('children.php?Items_ID='+j,'l1_children','700,700');
	}
	function transferChild(){
		for(var j in hl_grp['itemopt'])j=j.replace('r_','');
		if(!j){
			alert('Select a foster item to transfer first');
			return;
		}
		if(!g('r_'+j).getAttribute('transferrable')){
			alert('This item cannot be transferred, most likely because they do not have a current home assignment');
			return false;
		}
		ow('child_transfer.php?Items_ID='+j,'l2_transfer','550,600');
	}
	function dischargeChild(){
		for(var j in hl_grp['itemopt'])j=j.replace('r_','');
		if(!j){
			alert('Select a foster item to discharge first');
			return;
		}
		if(!g('r_'+j).getAttribute('transferrable')){
			alert('This item cannot be discharged, most likely because they are not currently assigned to a home');
			return false;
		}
		ow('item_discharge.php?Items_ID='+j,'l1_discharge','550,600');
	}
	function dischargeChild2(n,current){
		if(current){
			if(!confirm('Are you sure you want to discharge this item?'))return false;
			ow('item_discharge.php?Items_ID='+n,'l1_discharge','550,600');
		}else{
			alert('Not developed, please contact developer for assistance');
			return;
		}
	}
	function optionsItems(){
		g('oh02').innerHTML=(hideInactiveItems?'Show discharged foster children':'Hide discharged foster children');
		for(var j in hl_grp['itemopt'])j=j.replace('r_','');
		g('oh01b').className=g('oh01b').className.replace(/\s+mndis/,'')+(j ? '' : ' mndis');
		g('oh03').className=g('oh03').className.replace(/\s+mndis/,'')+(j ? '' : ' mndis');
		g('oh04').className=g('oh04').className.replace(/\s+mndis/,'')+(j ? '' : ' mndis');
	}
	function item_report(node){
		if(node=='CSV'){
			window.open('resources/bais_01_exe.php?mode=refreshComponent&component=<?php echo $datasetComponent?>&suppressPrintEnv=1&submode=exportDataset','w2');
			return;	
		}
	}
	</script><?php
}

if(!datasetFocusViewCall)require($MASTER_COMPONENT_ROOT.'/dataset_component_v121.php');
?>