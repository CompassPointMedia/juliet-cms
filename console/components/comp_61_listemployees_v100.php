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

$dataset='employees'; 					#more of a concept
$datasetComponent='employeesList';		#THIS physical component
$datasetGroup=$dataset; 					//as of 2010-04-04, this is not used
if(!$datasetWord)$datasetWord='Employee Listing';
if(!$datasetWordPlural)$datasetWordPlural='Employee Listings';
$datasetFocusPage='employees.php';
$datasetAddObjectJSFunction='ow(this.href,\'l1_employees\',\'800,700\',true);'; //this is because opening an object is not well developed yet
$datasetQueryStringKey='Employees_ID';
$datasetDeleteMode='deleteEmployees';

$datasetQuery=''; 							//this is left blank for list_members; needed because a view didn't contain the same data as a query!
$datasetTable='_v_employees';				//this can be a single MySQL table or a view
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
		case 'FullName':
			echo $record['FirstName'].' '.$record['LastName'];
		break;
		case 'Hours':
			$EmployeeHours=q("SELECT SUM(TotalHours) as Hours FROM finan_hours WHERE Employee_ID='".$record['ID']."' AND Week='".date('W')."'",O_VALUE);
			?>
			<a href="list_employeehours.php?Employee_ID=<?php echo $record['ID']?>" title="List all of <?php echo $record['FirstName']?>'s hours"><?php echo $EmployeeHours;?></a>
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
		'UserName'=>array(
		),
		'Full Name'=>array(
			'header'=>'Full Name',
			'method'=>'function',
			'fieldExpressionFunction'=>'prlist("FullName")',		
		),
		'Email'=>array(
		
		),
		'SocialSecurity'=>array(
			'header'=>'Social Security #',
		),
		'Hours'=>array(
			'header'=>'Hours This Week',
			'method'=>'function',
			'fieldExpressionFunction'=>'prlist("Hours")',
		),
		'PayAmount'=>array(
			'header'=>'Current Salary'
		),
	),
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
$datasetActiveUsage=false;
$datasetActiveActiveExpression='';
$datasetActiveInactiveExpression='';
$datasetActiveAllExpression='1';
$datasetActiveField='';
//allow this parameter to be passed remotely
/*
if(!isset($hideObjectInactiveControl))$hideObjectInactiveControl=false;
$datasetActiveControl='dischargeChild2(".$$Dataset_ID.", ".($$datasetActiveField?1:0).");';
$datasetActiveActivateTitle='Re-assign this foster child to a home';
$datasetActiveInactivateTitle='Discharge this foster child';
$datasetInternalFilter=(min($_SESSION['admin']['roles'])>ROLE_FOUNDATION_DIRECTOR ? "ID IN('".implode("','",list_children('keys'))."')" : '');
*/

$datasetShowDeletion=false;
/*
2010-01-26: modified getting and setting of these vars
*/
require($MASTER_COMPONENT_ROOT.'/dataset_generic_precoding_v103.php');


//html output items
if(!$refreshComponentOnly){
	?><style type="text/css">
	/* -- from filter gadget -- */
	<?php echo $filterGadgetCSS?>
	<?php
	dataset_complexDataCSS(array(
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