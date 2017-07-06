<?php
/*
2010-02-01 11:50 - #1 filled out the following fields
*/
$dataset='employeehours'; 					#more of a concept
$datasetComponent='employeehourList';		#THIS physical component
$datasetGroup=$dataset; 					//as of 2010-04-04, this is not used
if(!$datasetWord)$datasetWord='Employee Hours Listing';
if(!$datasetWordPlural)$datasetWordPlural='Employees Hours Listing';
$datasetFocusPage='hours.php';
$datasetAddObjectJSFunction='ow(this.href,\'l1_employees\',\'800,700\',true);'; //this is because opening an object is not well developed yet
$datasetQueryStringKey='Hours_ID';
$datasetDeleteMode='deleteHours';

$datasetQuery=''; 							//this is left blank for list_members; needed because a view didn't contain the same data as a query!
$datasetTable='finan_hours';				//this can be a single MySQL table or a view
$datasetTableIsView=false;
$datasetArrayType=O_ARRAY_ASSOC;			//added 2010-05-10 - this allows for non-standard left-column-equals-primary-key constructions; default=O_ARRAY_ASSOC
$datasetFieldList='*';
$modApType='embedded';
$modApHandle='first';
$globalBatchThreshold='10000';
$tbodyScrollingThreshold=20;
if($Employee_ID)$datasetInternalFilter="Employee_ID='$Employee_ID'";
$datasetActiveUsage=false;

$datasetTheme='report';
$hideColumnSelection=false; //however, we need to show column selection still
$footerDisposition='tabularControls'; //however, the footer needs to show to nav a large batch like this
$datasetHideFooterAddLink=true;
$hideColumnSelection=false;
$datasetShowDeletion=false; //no deletion needed on report
$datasetShowBreaks=true;
$datasetBreakFields=array(
	1=>array(
		'column'=>'Week',
		'blank'=>'not specified',
	),
	2=>array(
		'column'=>'Employee_ID',
		'blank'=>'not specified',
	)
);
$datasetCalcFields=array(
	array(
		'name'=>'TotalHours',
		'calc'=>'sum'
	),
);
//#3 populated this form after I created the view

function hrlist($param){
	global $record;
	ob_start(); 
	switch($param){
		case 'BillableFlag':
			echo $record['BillableFlag']?'Yes':'No';
		break;
		case 'Employee_ID':
			$Employee=q("SELECT FirstName,LastName FROM finan_employees WHERE ID='".$record['Employee_ID']."'",O_ROW);
			echo $Employee['FirstName'].' '.$Employee['LastName'];
		break;
		case 'Clients_ID':
			$Client=q("SELECT ClientName FROM finan_clients WHERE ID='".$record['Clients_ID']."'",O_VALUE);
			echo $Client;
		break;
		case 'Projects_ID':
			$Project=q("SELECT Name FROM finan_projects WHERE ID='".$record['Projects_ID']."'",O_VALUE);
			echo $Project;
		break;
		case 'Day':
			echo date('M d Y',strtotime($record['StartTime']));
		break;
		case 'Description':
			echo text_truncate($record['Description'],10);
		break;
	}
	$out=ob_get_contents();
	ob_end_clean();
	return $out;
}

$availableCols[$datasetGroup][$modApType][$modApHandle]=array(
	'version'=>1.0,
	'description'=>'',
	'scheme'=>array(
		'TotalHours'=>array(
			'header'=>'Total Hours',
			'width'=>'50px',
		),
		'Employee_ID'=>array(
			'header'=>'Employee',
			'method'=>'function',
			'fieldExpressionFunction'=>'hrlist("Employee_ID")',		
			'width'=>'150px',
		),
		'Clients_ID'=>array(
			'header'=>'Client',
			'method'=>'function',
			'width'=>'250px',
			'fieldExpressionFunction'=>'hrlist("Clients_ID")',		
		),
		'Projects_ID'=>array(
			'header'=>'Project',
			'method'=>'function',
			'fieldExpressionFunction'=>'hrlist("Projects_ID")',		
		),
		'Timesheets_ID'=>array(
			'header'=>'Time Sheet',
			'width'=>'20px',
		),
		'BillableFlag'=>array(
			'header'=>'Billable',
			'method'=>'function',
			'fieldExpressionFunction'=>'hrlist("BillableFlag")',		
		),
		'Day'=>array(
			'method'=>'function',
			'width'=>'75px',
			'fieldExpressionFunction'=>'hrlist("Day")',
		),
		'Description'=>array(
			'method'=>'function',
			'fieldExpressionFunction'=>'hrlist("Description")',
		),
		/*list these in order they would normally appear; analogous to Tbird's list of all inbox cols available */
		/*'StartDay'=>array(
			'method'=>'field',
			'sortable'=>true,
			'sortTitle'=>'Sort by Starting Day',
			'header'=>'Started',
			'orderBy'=>'StartDay $asc',
			'nowrap'=>true,
			'visibility'=>COL_VISIBLE,
			'colposition'=>3
		),*/
	)
); 

require($MASTER_COMPONENT_ROOT.'/dataset_generic_precoding_v103.php');


//#2 included this presentation snippet
require($MASTER_COMPONENT_ROOT.'/dataset_component_v123.php');



?>
