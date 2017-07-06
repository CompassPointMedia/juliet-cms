<?php
/*
todo
	see below for dataset-specific improvements that need to be made
	need a settable_parameter for the console OR e-commerce where the ITEM for an event is not proliferated and/or can be selected - this is an adminSetting not a user setting (?)


2012-02-08: List Events
This was created with a minimum number of lines, and the original complement of variables and functions is as follows:
	------------------------
	dataset='Event';
	datasetGroup='Event';
	datasetComponent='eventList';
	datasetWord='Event';
	datasetWordPlural='Events';
	datasetTable='_v_cal_events_extended';
	datasetTableIsView=true;
	tbodyScrollingThreshold=100000;
	datasetActiveHideControl=true;
	datasetFocusPage='events.php';
	datasetFocusQueryStringKey='Events_ID';
	function colConfig($param,$field='',$options=array()).. //for special cases, pretty standard so far
	$a=array(); //following the rules
	$availableCols[$datasetGroup][$modApType][$modApHandle]['scheme']=$a;
	------------------------
todo:
	search/filter tool that is workable and helpful
	spinner for long transitions
	improve color and styling
	preventDefault() for double clicks on rows
	can preventDefault() work for native FF hightlighting of td cells for control-clicking? yeah!
	context menus
	wrap buttons with a ^ option to the right as is the style nowadays	
	ability to import and export events 
	a way to configure this as a report stylistically vs. a tool
		i.e. a dataset does this, a report does that (or does not do that)
	* create this much automatically with the dataset creator
	bold a column like the name
	have columns save in state - they are not saving right now
	

*/
$dataset='Event';
$datasetGroup='Event';
$datasetComponent='eventList';
$datasetWord='Event';
$datasetWordPlural='Events';
$datasetTable='_v_cal_events_extended';
$datasetTableIsView=true;
$tbodyScrollingThreshold=100000;
$datasetActiveHideControl=true;
$datasetFocusPage='events.php';
$datasetFocusQueryStringKey='Events_ID';
function colConfig($param,$field='',$options=array()){
	global $record, $datasetID, $modApType, $modApHandle, $submode,$qr, $developerEmail, $fromHdrBugs;
	$param=strtolower($param);
	$a=$record;
	extract($a);
	extract($options);
	ob_start();
	switch($param){
		case 'contact':
			if($e=$ContactEmail){
				?><a href="mailto:<?php echo $e;?>"><?php
			}
			echo $ContactName ? $ContactName : $ContactEmail;
			if($e){
				?></a><?php
			}
			if($ContactPhone)echo '<br />'.$ContactPhone;
		break;
		case 'calendar':
			if($CalendarCount<2){
				echo $CalendarName;
			}else{
				echo implode(', ',q("SELECT CONCAT('<span class=\"background-color:#', c.ColorCode, ';padding:0px 7px;\">', c.Identifier, '</span>') FROM cal_cal c, cal_CalEvents ce WHERE c.ID=ce.Cal_ID AND Events_ID=".$$datasetID, O_COL));
			}
		break;
		case 'date':
			$s=($StartDate==$EndDate || $EndDate=='0000-00-00' || !$EndDate);
			echo t($StartDate, f_qbks).($s?'':' - '.t($EndDate));
		break;
		case 'time':
			$s=($StartTime==$EndTime || $EndTime=='00:00:00' || !$EndTime);
			if($StartTime!='00:00:00'){
				echo date('g:iA',strtotime($StartTime)).($s?'':' - '.date('g:iA',strtotime($EndTime)));
			}
		break;
		case 'name':
			echo $Name;
		break;
		case 'schedulenotes':
			?><span class="gray"><?php echo $ScheduleNotes;?></span><?php
		break;
		default:
	}
	$out=ob_get_contents();
	ob_end_clean();
	return $out;
}
$a=array(
	'Calendar'=>array(
		'method'=>'function',
		'fieldExpressionFunction'=>'colConfig("Calendar")',
		'sortable'=>false,
	),
	'StartDate'=>array(
		'header'=>'Date',
		'method'=>'function',
		'fieldExpressionFunction'=>'colConfig("Date")',
	),
	'StartTime'=>array(
		'header'=>'Time',
		'method'=>'function',
		'fieldExpressionFunction'=>'colConfig("Time")',
		'nowrap'=>'nowrap',
	),
	'Name'=>array(
		'method'=>'function',
		'fieldExpressionFunction'=>'colConfig("Name")',
		'width'=>'200',
	),
	'ScheduleNotes'=>array(
		'header'=>'Notes',
		'method'=>'function',
		'fieldExpressionFunction'=>'colConfig("ScheduleNotes")',
		'width'=>'150',
		'sortable'=>false,
	),
	/*
	goals:
	DONE	0. show_logical and ONLY show YES, not NO
	DONE	00. format with a $ as currency
	DONE	000. width of cell
	DONE	1. show number only if greater than zero else &nbsp;
	DONE	2. show date only if greater than zero else &nbsp;
	3. format as a condensed string
	4. *AND* have as a URL
	5. join the client - and proliferate what I've done in here throughout the system - Alexei to do this
	*/
	'AllowOnlinePayment'=>array(
		'header'=>'Pay<br />Online',
		/* example 0. */
		'datatype'=>'logical',
		'format'=>'YES',
	),
	'Cost'=>array(
		'datatype'=>'currency',
		'format'=>'$',
		'flag'=>'nozero',
	),
	'MaxEnrollments'=>array(
		'header'=>'Max<br />Attendees',
		'flag'=>'nozero',
	),
	'Deadline'=>array(
		
	),
	'URL'=>array(
	
	),
	'Contact'=>array(
		'method'=>'function',
		'fieldExpressionFunction'=>'colConfig("Contact")',
		'width'=>120,
	),
);
$availableCols[$datasetGroup][$modApType][$modApHandle]['scheme']=$a;

#OK, let's color it, set dataset_complexDataCSS in place as desired
#add some functions for add and update object in !refreshComponentOnly block
#add a new way to refresh the component by passing the componet file name itself (say on sort) - this triggers a change in the string passed - this pushes us to version 1.25 for dataset component.  NOTE this is all that is needed, the dataset component handles validation - essentially verifying that that component is asking for itself
$datasetFile=end(explode('/',__FILE__));

require($MASTER_COMPONENT_ROOT.'/dataset_generic_precoding_v104.php');

if(!$refreshComponentOnly){
	?><style type="text/css">
	<?php
	dataset_complexDataCSS(array(
		'datasetColorHeader_'=>'a37029',
		'datasetColorRowAlt_'=>'f1e8e2',
		'datasetColorSorted_'=>'wheat',
		'datasetColorHighlight_'=>'d3b8a5',
	));
	?>
	</style>
	<script language="javascript" type="text/javascript">
	function addEvent(){
		ow('events.php','l1_events','750,700',true);
		return false;
	}
	function openEvent(){
		for(var i in hl_grp['eventopt']){
			ow('events.php?Events_ID='+i.replace('r_',''),'l1_events','750,700');
			return false;
		}
	}
	</script><?php
}
require($MASTER_COMPONENT_ROOT.'/dataset_component_v124.php');


?>