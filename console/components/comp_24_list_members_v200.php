<?php
/*
Created 2010-04-14 by Samuel
This was pulled over from Giocosa Care's console for subcontractors, using the same dataset components

*/
$dataset='Members'; #more of a concept
$datasetComponent='memberList'; #THIS physical component
$datasetGroup=$dataset; //as of 2010-04-04, this is not used
if(!$datasetWord)$datasetWord='Member';
if(!$datasetWordPlural)$datasetWordPlural='Members';
$datasetFocusPage='members.php';
$datasetAddObjectJSFunction='ow(this.href,\'l1_members\',\'700,700\',true);';
$datasetQueryStringKey='Clients_ID';
$datasetDeleteMode='deleteMember';

$datasetQuery=''; //this is left blank for list_members; needed because a view didn't contain the same data as a query!
$datasetTable='_v_contacts_generic_v200'; //this can be a single MySQL table or a view
$datasetTableIsView=true;
$datasetActiveUsage=true;
$datasetFieldList='*';
$modApType='embedded';
$modApHandle='first';
$globalBatchThreshold=600;

$qx['tableList']=array_merge(
	$qx['tableList'],
	array('addr_contacts','finan_clients','finan_clients_statuses','finan_ClientsContacts','finan_ClientsCategories')
);
$tableTests=array(
	'addr_contacts'=>q("SELECT Salesreps_ID FROM addr_contacts LIMIT 1", O_VALUE)
);


if(!isset($hideObjectInactiveControl))$hideObjectInactiveControl=false;

/* 			-------------- added 2009-10-26 --------------			*/

//so, this completely declares what is available for the layout; see scheme below
/*
i.e. embedded means, part of the programs; user really has no access to this now 
i.e. first means, what I'm nicknaming this available columns set
*/
$availableCols[$datasetGroup][$modApType][$modApHandle]=array(
	'scheme'=>array(
		/*list these in order they would normally appear; analogous to Tbird's list of all inbox cols available */
		'RepCode'=>array(
			'method'=>'field',
			'fieldExpressionFunction'=>'RepCode',
			'header'=>'Rep',
			'orderBy'=>'RepCode $asc, LastName $asc, FirstName $asc',
			'visibility'=>COL_VISIBLE,
			'colposition'=>1
		),
		'Status'=>array(
			'sortable'=>true, /* the default */
			'sortTitle'=>'Sort by member status',
			/* this called AFTER $sort and $asc present but before the query, for sort=Status */
			'orderBy'=>'Status $asc, /* extra stuff is nice :) */ LastName $asc, FirstName $asc',
			'colposition'=>2
		),
		'CreateDate'=>array(
			'method'=>'function',
			'fieldExpressionFunction'=>'standard_cols(\'CreateDate\')',
			'sortable'=>true,
			'sortTitle'=>'Sort by record creation date',
			'header'=>'Created',
			'orderBy'=>'CreateDate $asc',
			'nowrap'=>true,
			'visibility'=>COL_VISIBLE,
			'colposition'=>3
		),
		'CompanyName'=>array(
			'header'=>'Company',
			'orderBy'=>'CompanyName $asc, LastName $asc, FirstName $asc',
			'visibility'=>COL_VISIBLE,
			'colposition'=>4
		),
		'Name'=>array(
			'method'=>'function',
			'fieldExpressionFunction'=>'h($LastName.\', \'.$FirstName)',
			'datatype'=>'name', /* not used yet :) */
			'format'=>'LNFN',
			'orderBy'=>'LastName $asc, FirstName $asc',
			'visibility'=>COL_VISIBLE,
			'colposition'=>5
		),
		'Email'=>array(
			'datatype'=>'link',
			'method'=>'function',
			'orderBy'=>'Email $asc, LastName $asc, FirstName $asc',
			'fieldExpressionFunction'=>'text_truncate($Email,15,array(
				"lengthMode"=>"letters"
			))',
			'visibility'=>COL_VISIBLE,
			'colposition'=>6
		),
		'Phones'=>array(
			'method'=>'function',
			'fieldExpressionFunction'=>'standard_cols(\'Phones\')',
			'sortable'=>false,
			'visibility'=>COL_VISIBLE,
			'colposition'=>7
		),
		'BusinessAddress'=>array(
			'nowrap'=>true,
			'method'=>'function',
			'header'=>'Bus. Address',
			'fieldExpressionFunction'=>'standard_cols(\'BusinessAddress\')',
			'sortable'=>true,
			'orderBy'=>'BusinessCountry $asc, BusinessState $asc, BusinessCity $asc, BusinessAddress $asc',
			'visibility'=>COL_VISIBLE,
			'colposition'=>8
		),
		'HomeAddress'=>array(
			'nowrap'=>true,
			'method'=>'function',
			'header'=>'Home Address',
			'fieldExpressionFunction'=>'standard_cols(\'HomeAddress\')',
			'sortable'=>true,
			'orderBy'=>'HomeCountry $asc, HomeState $asc, HomeCity $asc, HomeAddress $asc',
			'visibility'=>COL_VISIBLE,
			'colposition'=>9
		),
		'ClientAddress'=>array(
			'nowrap'=>true,
			'method'=>'function',
			'header'=>'Company Address',
			'fieldExpressionFunction'=>'standard_cols(\'ClientAddress\')',
			'sortable'=>true,
			'orderBy'=>'ClientCountry $asc, ClientState $asc, ClientCity $asc, ClientAddress $asc',
			'visibility'=>COL_VISIBLE,
			'colposition'=>10
		)
	)
);

/*
if(!isset($useStatusFilterOptions))$useStatusFilterOptions=true;
if(!isset($showSessionFilters[$dataset]) || $showSessionFilters[$dataset]){
	require($COMPONENT_ROOT.'/comp_01_filtergadget_v104.php');
}
*/

require($MASTER_COMPONENT_ROOT.'/dataset_generic_precoding_v102.php');

ob_start();

//filter gadget instance - first ever in comp_24_list_members_v200.php
$filterGadgetHideCSS=true;
$filterGadgetHideJS=true;
$filterGadgetCSSInternal=true;
$filterGadgetJSInternal=true;
require($MASTER_COMPONENT_ROOT.'/comp_01_filtergadget_v105.php');

?><div class="frb">
	<a href="/console/resources/bais_01_exe.php?mode=refreshComponent&component=<?php echo $datasetComponent?>&submode=exportDataset&suppressPrintEnv=1" target="w2">Export List</a>&nbsp;&nbsp;
	<a href="<?php echo $datasetFocusPage?>?cbFunction=refreshComponent&cbParam=fixed:<?php echo $datasetComponent?>" onclick="return <?php echo $datasetAddObjectJSFunction ? $datasetAddObjectJSFunction : 'add'.$dataset.'()'?>"><img src="/images/i/s/hlw-25x25-9EA9B4/plus.png" />&nbsp;Add <?php echo strtolower($datasetWord);?></a>&nbsp;
</div><?php
$datasetPreContent=get_contents();

if(!$refreshComponentOnly){
	?><style type="text/css">
	/* -- from filter gadget -- */
	<?php echo $filterGadgetCSS?>
	.frb{
		float:right;
		}
	.frb a{
		color:#000;
		}
	</style>
	<script language="javascript" type="text/javascript">
	/* -- from filter gadget -- */
	<?php echo $filterGadgetJS?>
	</script><?php
}
require($MASTER_COMPONENT_ROOT.'/dataset_component_v121.php');

?>