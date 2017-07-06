<?php
/*
2010-02-01 11:50 - #1 filled out the following fields
*/
$dataset='clienthours'; 					#more of a concept
$datasetComponent='clienthoursList';		#THIS physical component
$datasetGroup=$dataset; 					//as of 2010-04-04, this is not used
if(!$datasetWord)$datasetWord='Client Hours Listing';
if(!$datasetWordPlural)$datasetWordPlural='Clients Hours Listing';
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
if($Client_ID)$datasetInternalFilter="Client_ID='$Client_ID'";
$datasetActiveUsage=false;

$datasetTheme='';
$footerDisposition='tabularControls'; 		//however, the footer needs to show to nav a large batch like this
$datasetHideFooterAddLink=true;
$hideColumnSelection=false;
//#3 populated this form after I created the view

function hrlist($param){
	global $record;
	ob_start(); 
	switch($param){
		case 'BillableFlag':
			echo $record['BillableFlag']?'Yes':'No';
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
		'Employee_ID'=>array(
			'header'=>'Employee',
			'method'=>'function',
			'fieldExpressionFunction'=>'hrlist("Employee_ID")',		
			'width'=>'200px',
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
