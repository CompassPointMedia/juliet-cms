<?php
/*
2010-02-01 11:50 - #1 filled out the following fields
*/
$dataset='Hour';
$datasetComponent='hourList';
$datasetGroup=$dataset; //Member
$datasetWord='Hour';
$datasetWordPlural='Hours';
$datasetFocusPage='hours.php';
if(!$datasetTable)$datasetTable='_v_hours_generic_v100';
$datasetTableIsView=true;
$datasetActiveUsage=false;
$datasetFieldList='*';
$modApType='embedded';
$modApHandle='first';
$getTable=q("SELECT COUNT(*) FROM finan_projects", O_VALUE);
//#3 populated this form after I created the view
$availableCols[$datasetGroup][$modApType][$modApHandle]=array(
	'version'=>1.0,
	'description'=>'This is the first array implementation of relatebase_views and _views_items',
	'scheme'=>array(
		/*list these in order they would normally appear; analogous to Tbird's list of all inbox cols available */
		'ClientName'=>array(
			'method'=>'field',
			'header'=>'Client Name',
			'visibility'=>COL_VISIBLE,
			'colposition'=>1
		),
		'ProjectName'=>array(
			'method'=>'field', /* the default */
			'sortTitle'=>'Sort by project name',
			'header'=>'Project',
			'orderBy'=>'ProjectName $asc, StartDay $asc',
			/* ------- etc., etc., etc. -------- */
			'visibility'=>COL_VISIBLE,
			'colposition'=>2
		),
		'StartDay'=>array(
			'method'=>'field',
			'sortable'=>true,
			'sortTitle'=>'Sort by Starting Day',
			'header'=>'Started',
			'orderBy'=>'StartDay $asc',
			'nowrap'=>true,
			'visibility'=>COL_VISIBLE,
			'colposition'=>3
		),
	)
);
if(!function_exists('get_contents')){
	require($FUNCTION_ROOT.'/function_get_contents_v100.php');
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/snippets/dataset_generic_precoding_v101.php');
if(!$refreshComponentOnly){
	ob_start(); //--- buffer CSS ---
	?><style type="text/css">
	</style>
	<?php
	echo $componentCSS=get_contents();
	ob_start(); //--- buffer JS ---
	?>
	<script type="text/javascript" language="javascript">
	function optionsDataset(){
		g('oh02').innerHTML=(hideInactive<?php echo $dataset?>?'Show inactive ':'Hide inactive ')+'<?php echo strtolower($datasetWordPlural);?>';
	}
	function reportsObjects(){
	}
	function exportobjects(node){
		if(node=='csv-all' && !confirm('This will export the list of '+dataojbect+'.  Continue?'))return;
		window.open('resources/bais_01_exe.php?suppressPrintEnv=1&mode=refreshComponent&component=<?php echo $datasetComponent?>&submode=exportDataset&object=<?php echo $dataset?>&node='+node,'w2');
	}
	function memoptionsPre(){
		for(var j in hl_grp['memopt'])j=j.replace('r_','');
	}
	function addHour(){
		ow('<?php echo $datasetFocusPage?>?cbFunction=refreshComponent&cbParam=fixed:<?php echo $datasetComponent;?>','l1_<?php echo strtolower($datasetWord);?>','700,700',true);
		return false;
	}
	function openMember(){
		for(var j in hl_grp['memopt'])j=j.replace('r_','');
		ow('<?php echo $datasetFocusPage?>?cbFunction=refreshComponent&cbParam=fixed:<?php echo $datasetComponent?>&Clients_ID='+j,'l1_<?php echo strtolower($datasetWord);?>','700,700');
	}
	function memberAction(event, action){
		for(var j in hl_grp['memopt'])j=j.replace('r_','');
		if(action=='delete'){
			if(!g('r_'+j).getAttribute('deletable')){
				if(confirm('This member cannot be deleted; it has transactions associated with it.  You must first delete all transactions.\n\nWould you like to see a transaction history report for this member?')){
					ow('transactionhistory.php?Clients_ID='+j,'l1_transactionhistory','700,700');
				}
				return false;
			}
			if(!confirm('This will permanently delete this member\'s record.  Are you sure?'))return false;
			window.open('resources/bais_02_01_exe.php?mode=deleteMember&Clients_ID='+j,'w2');
		}else if(action=='report'){
			if(!j){
				alert('First click on a member record and highlight its row');
				return;
			}
			ow('transactionhistory.php?Clients_ID='+j,'l1_transactionhistory','700,700');
		}
	}
	function colOptions(){
	}
	function mgeCol(e,n){
		var posn=g('col'+n).className.indexOf('Visible');
		window.open('resources/bais_01_exe.php?mode=refreshComponent&component=<?php echo $datasetComponent?>&col='+n+'&visibility='+(posn> -1? 8 : 16),'w2');
	}
	AssignMenu('^optionsMembers$', 'optionsMembersMenu');
	AssignMenu('^reportsMembers$', 'reportsMembersMenu');
	AssignMenu('^colOptions_<?php echo $dataset?>','optionsAvailableCols');

	hl_bg['memopt']='#6c7093';
	hl_baseclass['memopt']='normal';
	hl_class['memopt']='hlrow';
	//declare the ogrp.handle.sort value even if blank
	ogrp['memopt']=new Array();
	ogrp['memopt']['sort']='';
	ogrp['memopt']['rowId']='';
	ogrp['memopt']['highlightGroup']='memopt';
	AssignMenu('^r_([0-9]+)$', 'memberOptions');
	</script>
	<?php
	echo $componentJS=get_contents();
}


//#2 included this presentation snippet
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/snippets/dataset_component_v120.php');



?>
