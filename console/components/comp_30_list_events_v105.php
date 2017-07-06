<?php
$dataset='Event';
$datasetComponent='eventList';
$datasetGroup=$dataset; //Event
$datasetWord='Event';
$datasetWordPlural='Events';
$datasetFocusPage='events.php';
//$datasetArchitecture=NULL; not used
$datasetTable='_v_cal_events_extended';
$datasetTableIsView=true;
$datasetActiveUsage=true;
$datasetFieldList='*';
$modApType='embedded';
$modApHandle='first';

$datasetAddObjectJSFunction="ow(this.href,'l1_events','700,700',true);";
$tbodyScrollingThreshold=12;

/* 			-------------- added 2009-10-26 --------------			*/

//so, this completely declares what is available for the layout; see scheme below
/*
i.e. embedded means, part of the programs; user really has no access to this now 
i.e. first means, what I'm nicknaming this available columns set
*/
$availableCols[$datasetGroup][$modApType][$modApHandle]=array(
	'version'=>1.0,
	'description'=>'This was taken from members_list.php with no additional development',
	'scheme'=>array(
		/*list these in order they would normally appear; analogous to Tbird's list of all inbox cols available */
		'CreateDate'=>array(
			'method'=>'function',
			'fieldExpressionFunction'=>'dataset_cols(\'CreateDate\')',
			'sortable'=>true,
			'sortTitle'=>'Sort by record creation date',
			'header'=>'Created',
			'orderBy'=>'CreateDate $asc',
			'nowrap'=>true,
			'visibility'=>COL_VISIBLE,
			'colposition'=>3
		),
		'CalendarName'=>array(
			'header'=>'Calendar',
			'sortable'=>true,
			'orderBy'=>'CalendarName $asc, StartDate $asc',
			'visibility'=>COL_VISIBLE	
		),
		'Cal_ID'=>array(
			'visibility'=>COL_SYSTEM
		),
		'StartDate'=>array(
			'datatype'=>'date',
			'visibility'=>COL_VISIBLE
		),
		'EndDate'=>array(
			'datatype'=>'date',
			'visibility'=>COL_VISIBLE
		),
		'StartTime'=>array(
			'visibility'=>COL_VISIBLE
		),
		'EndTime'=>array(
			'visibility'=>COL_VISIBLE
		),
		'ScheduleNotes'=>array(
			'visibility'=>COL_VISIBLE
		),
		'Cost'=>array(
			'visibility'=>COL_VISIBLE
		),
		'AllowOnlinePayment'=>array(
			'visibility'=>COL_VISIBLE
		),
		'Deadline'=>array(
			'visibility'=>COL_VISIBLE
		),
		'Name'=>array(
			'visibility'=>COL_VISIBLE
		),
		'BriefDescription'=>array(
			'visibility'=>COL_VISIBLE
		),
		'URL'=>array(
			'visibility'=>COL_VISIBLE
		),
		'ContactName'=>array(
		),
		'ContactEmail'=>array(
		),
		'ContactPhone'=>array(
		),
		'Location'=>array(
		),
		'Address'=>array(
		),
		'City'=>array(
		),
		'State'=>array(
		),
		'Zip'=>array(
		)
	)
);
//this allows tables to be created/remediated before the view is created
$tableCounts=array(
	'cal_cal'=>q("SELECT * FROM cal_cal", O_VALUE),
	'cal_events'=>q("SELECT * FROM cal_events", O_VALUE)
);
$qx['useRemediation']=true;
$qx['tableList']=array_merge($qx['tableList'], array('cal_cal','cal_events'));
$hideObjectInactiveControl=false;


if(!function_exists('dataset_cols')){
	function dataset_cols($cell){
		global $record,$colPosition,$visibleColCount;
		switch(true){
			case $cell=='BusinessAddress' || $cell=='HomeAddress' || $cell=='ClientAddress':
				$cog=($cell=='HomeAddress' ? 'Home' : ($cell=='HomeAddress' ? 'Home' : 'Client'));
				$str.=$record[$cog.'Address'].($colPosition==$visibleColCount?'&nbsp;&nbsp;&nbsp;':'').'<br />';
				$str.=$record[$cog.'City'].', '.$record[$cog.'State'].'&nbsp;&nbsp;'.$record[$cog.'Zip'];
				$str.=(strtolower($record[$cog.'Country'])!=='us' && strtolower($record[$cog.'Country'])!=='usa' ? '&nbsp;&nbsp;'.$record[$cog.'Country'] : '').($colPosition==$visibleColCount?'&nbsp;&nbsp;&nbsp;':'');
			break;
			case $cell=='Phones':
				if($record['HomePhone'])$str.=$record['HomePhone'] . '(H)<br />';
				if($record['HomeMobile'])$str.= $record['HomeMobile'] . '(M)<br />';
				if($record['Pager'])$str.= $record['Pager'] . '(P/V)<br />';
				if($record['BusPhone'])$str.= $record['BusPhone'] . '(W)<br />';
			break;
			case $cell=='Email':
				if($record['Email']){
					$str='<a href="mailto:'.$record['Email'].'">'.$record['Email'].'</a>';
				}
				if($record['AlternateEmail']){
					$str.='<br /><a href="mailto:'.$record['AlternateEmail'].'">'.$record['AlternateEmail'].'</a>';
				}
			break;
			case $cell=='CreateDate':
				$str=t($record['CreateDate'], f_dspst, thisyear);
			break;
		}
		return $str;
	}
}
if(!function_exists('get_contents')){
	require($FUNCTION_ROOT.'/function_get_contents_v100.php');
}

//This first used on list_members and _events.php
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/snippets/dataset_generic_precoding_v101.php');


if(!$refreshComponentOnly){
	ob_start(); //--- buffer CSS ---
	?><style type="text/css">
	</style><?php
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
	function addMember(){
		ow('<?php echo $datasetFocusPage?>?cbFunction=refreshComponent&cbParam=fixed:<?php echo $datasetComponent;?>','l1_<?php echo strtolower($datasetWord);?>','700,700',true);
		return false;
	}
	function openMember(){
		for(var j in hl_grp['memopt'])j=j.replace('r_','');
		ow('<?php echo $datasetFocusPage?>?cbFunction=refreshComponent&cbParam=fixed:<?php echo $datasetComponent?>&Events_ID='+j,'l1_<?php echo strtolower($datasetWord);?>','700,700');
	}
	function eventAction(event, action){
		for(var j in hl_grp['memopt'])j=j.replace('r_','');
		if(action=='delete'){
			if(!g('r_'+j).getAttribute('deletable')){
				if(confirm('This event cannot be deleted; it has transactions associated with it.  You must first delete all transactions.\n\nWould you like to see a transaction history report for this event?')){
					ow('transactionhistory.php?Events_ID='+j,'l1_transactionhistory','700,700');
				}
				return false;
			}
			if(!confirm('This will permanently delete this event\'s record.  Are you sure?'))return false;
			window.open('resources/bais_02_01_exe.php?mode=deleteMember&Events_ID='+j,'w2');
		}else if(action=='report'){
			if(!j){
				alert('First click on a event record and highlight its row');
				return;
			}
			ow('transactionhistory.php?Events_ID='+j,'l1_transactionhistory','700,700');
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
	AssignMenu('^r_([0-9]+)$', 'eventOptions');
	</script><?php
	echo $componentJS=get_contents();
}
ob_start(); //--- buffer toolbar --
?>
<div id="componentToolbar_<?php echo $dataset?>" class="fr componentToolbar">
	<!-- toolbar buttons in divs -->
	<div class="fl">
		<a id="optionsMembers" title="View Options" href="javascript:;" onclick="hidemenuie5(event,1);showmenuie5(event,1)" oncontextmenu="return false;"><img src="../images/i/person3_28x30-woman-red-prof.gif" alt="Members" width="23" height="30" /> Options</a>&nbsp;&nbsp; 
	</div>
	<div class="fl">
		<a id="reportsMembers" title="View Report Options" href="javascript:;" onclick="hidemenuie5(event,1);showmenuie5(event,1)" oncontextmenu="return false;"><img src="../images/i/addr_26x27.gif" alt="Reports" width="23" height="30" /> Reports</a>&nbsp;&nbsp;
	</div>
	<div class="fl">
		<?php 
		if(!isset($useStatusFilterOptions))$useStatusFilterOptions=true;
		if(!isset($showSessionFilters[$dataset]) || $showSessionFilters[$dataset]){
			require($COMPONENT_ROOT.'/comp_01_filtergadget_v104.php');
		}
		?>
	</div>
</div>
<!-- toolbar context menus here -->
<div id="eventOptions" class="menuskin1" style="z-index:1000;" onMouseOver="hlght2(event)" onMouseOut="llght2(event)" onClick="executemenuie5(event)" precalculated="memoptionsPre()">
	<div id="fho1" default="1" style="font-weight:900;" class="menuitems" command="openMember()" status="Show Information and Edit this event">Edit event</div>
	<div id="fho3" class="menuitems" command="eventAction(event, 'report');" status="Transaction Report">Transaction Report</div>
	<hr class="mhr"/>
	<div id="fho2" class="menuitems" command="eventAction(event, 'delete');" status="Delete this event">Delete</div>
</div>	
<div id="optionsMembersMenu" class="menuskin1" style="z-index:1000;" onmouseover="hlght2(event)" onmouseout="llght2(event)" onclick="executemenuie5(event)" precalculated="optionsDataset();">
	<div id="oh01" style="font-weight:900;" class="menuitems" command="addMember();" status="Add a new event">New Member</div>
	<hr class="mhr"/>
	<div id="oh02" nowrap="nowrap" class="menuitems" command="toggleActive('<?php echo $datasetComponent?>',hideInactive<?php echo $dataset?>);" status="option2">Show Inactive <?php echo $dataset?>s</div>
</div>
<div id="reportsMembersMenu" class="menuskin1" style="z-index:1000;width:225px;" onmouseover="hlght2(event)" onmouseout="llght2(event)" onclick="executemenuie5(event)" precalculated="reportsObjects();">
	<hr class="mhr"/>
	<div id="or01" class="menuitems" command="exportobjects('csv');" status="Export CSV spreadsheet for these results">Export CSV spreadsheet for these results</div>
</div>
<?php 
echo $componentToolbar=get_contents();

require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/snippets/dataset_component_v120.php');
?>