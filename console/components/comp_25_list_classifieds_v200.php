<?php

/*
Created 2010-05-03 by Samuel
seeded from list_albums, focus here is on non-field columns, icons for color overriding maybe, and things I can do with text

*/
$dataset='Ads';
$datasetComponent='adsList';
$datasetGroup=$dataset;
if(!$datasetWord)$datasetWord='Ad';
if(!$datasetWordPlural)$datasetWordPlural='Ads';
$datasetFocusPage='classifieds.php';
$datasetAddObjectJSFunction='ow(this.href,\'l1_classifieds\',\'700,700\',true);';
$datasetQueryStringKey='Ads_ID';
$datasetDeleteMode='deleteAd';

$datasetQuery=''; //this is left blank for list_members; needed because a view didn't contain the same data as a query!
$datasetTable='_v_publisher_ads_flat_improved';
$datasetTableIsView=true;
$datasetActiveUsage=true;
$datasetFieldList='*';
$modApType='embedded';
$modApHandle='first';
$globalBatchThreshold=500;

$availableCols[$datasetGroup][$modApType][$modApHandle]=array(
	'version'=>1.0,
	'description'=>'List of classifieds developed for Texas Express',
	'scheme'=>array(
		/*list these in order they would normally appear; analogous to Tbird's list of all inbox cols available */
		'ID' => array(
			'header'=>'Order#'
		),
		'OrderNumber' => array(
			'header'=>'Ref#'
		),
		'FullName' => array(
			'header'=>'Placed by',
			'nowrap'=>true,
			'method'=>'function',
			'fieldExpressionFunction'=>'$PlacedLastName . \', \' . $PlacedFirstName . ($PlacedMiddleName ? \' \'.substr($PlacedMiddleName,0,1):\'\')',
			'orderBy'=>'PlacedLastName $asc, PlacedFirstName $asc'
		),
		'PlacedEmail' => array(
			'header'=>'Email',
			'method'=>'function',
			'fieldExpressionFunction'=>'\'<a href="mailto:\'.$PlacedEmail.\'" title="\'.$PlacedEmail.\'"><img src="/images/i/email.gif" alt="email" width="19" height="18" /></a>\''
		),
		'CompanyName'  => array(
		
		),
		'Category' => array(
		
		),
		'Title' => array(
		
		),
		'Content' => array(
			'method'=>'function',
			'fieldExpressionFunction'=>'text_truncate($Content,10)'
		),
		'FirstRun'  => array(
		
		),
		'LastRun' => array(
		
		),
		'RunTotal'  => array(
		
		),
		'Approved' => array(
		
		),
		'RunMethod' => array(
		
		),
	)
);
$qx['useRemediation']=false;

$hideObjectInactiveControl=false;


//This first used on list_members and _events.php
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/snippets/dataset_generic_precoding_v101.php');


if(!$refreshComponentOnly){
	ob_start(); //--- buffer CSS ---
	?><style type="text/css">
	.complexData thead{						/* header, non-sorted */
		background-color:#ffa500;
		}
	.complexData th{
		color:#FFF;
		}
	.complexData th.sortedasc{					/* header, sorted ascending */
		background-color:#f29d00;
		}
	.complexData th.sorteddesc{					/* header, sorted descending */
		background-color:#f29d00;
		}
	.complexData tr{						/* row, normal color (default white) */
		}
	.complexData tr.alt{					/* row, alt color */
		background-color:#ffe9c2;
		}
	.complexData tr.alt td.sorted{			/* row-alt, col-sorted */
		background-color:#ffd994;
		}
	.complexData td.sorted{					/* row-normal, col-sorted */
		background-color:#fff6e5;
		}
	.hlrow td{							/* h() with new className change */
		background-color:#c0c0c0;
		}
	.hlrow td.sorted{
		background-color:#c6bdac;		/* highlight-sorted */
		}
	</style><?php
	echo $componentCSS=get_contents();
	ob_start(); //--- buffer JS ---
	?>
	<script type="text/javascript" language="javascript">
	//none
	</script><?php
	echo $componentJS=get_contents();
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/snippets/dataset_component_v120.php');
?>