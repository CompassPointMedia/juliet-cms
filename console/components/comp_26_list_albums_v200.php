<?php
$dataset='Album';
$datasetComponent='albumList';
$datasetGroup=$dataset;
$datasetWord='Album';
$datasetWordPlural='Albums';
$datasetFocusPage='albums.php';
//$datasetArchitecture=NULL; not used
$datasetTable='_v_ss_albums_flat';
$datasetTableIsView=true;
$datasetActiveUsage=true;
$datasetFieldList='*';
$modApType='embedded';
$modApHandle='first';
$Dataset_ID='ID';


//added 2010-05-21:
$datasetSetIdx=true;
$datasetSetIdxPriorityTable='ss_albums';
$datasetSetIdxWhereFilter='ResourceType IS NOT NULL';


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
		'Name'=>array(
		
		),
		'Location'=>array(
		
		),
		'Description'=>array(
			'method'=>'function',
			'fieldExpressionFunction'=>'text_truncate($Description,55)'
		),
		'CreateDate'=>array(
			'header'=>'Created'
		),
		'PictureCount'=>array(
		
		),
		'LeadPictureName'=>array(
			'header'=>'Lead Picture',
		
		),
	)
);
//this allows tables to be created/remediated before the view is created
$tableCounts=array(
	'ss_albums'=>q("SELECT * FROM ss_albums", O_VALUE),
	'ss_pictures'=>q("SELECT * FROM ss_pictures", O_VALUE)
);
$qx['useRemediation']=true;
$qx['tableList']=array_merge($qx['tableList'], array('ss_albums','ss_pictures'));
$hideObjectInactiveControl=false;


//This first used on list_members and _events.php
require($MASTER_COMPONENT_ROOT.'/dataset_generic_precoding_v103.php');


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

require($MASTER_COMPONENT_ROOT.'/dataset_component_v122.php');
?>