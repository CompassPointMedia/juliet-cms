<?php
/*
built from scratch 2011-10-01: been a while since I hvae used dataset component so here we go;



*/
$dataset='Members';
$datasetComponent='memberList';
$datasetTable='_v_contacts_generic_v200';
$datasetGroup=$dataset;

$modApType='embedded';
$modApHandle='first';
$availableCols[$datasetGroup][$modApType][$modApHandle]['scheme']=array(
	'Status'=>array(
		'visibility'=>COL_AVAILABLE,
		'fieldExpressionFunction'=>'colConfig("members:Status")',
	),
	'NameCompany'=>array(
		'label'=>'Name/Company',
		'fieldExpressionFunction'=>'colConfig("members:NameCompany")',
	),
	'Phones'=>array(
		'fieldExpressionFunction'=>'colConfig("members:Phones")',
	),
	'Email'=>array(
		'fieldExpressionFunction'=>'colConfig("members:Email")',
	),
	'Address'=>array(
		'fieldExpressionFunction'=>'colConfig("members:Address")',
	),
	'BusAddress'=>array(
		'visibility'=>COL_AVAILABLE,
		'fieldExpressionFunction'=>'colConfig("members:BusAddress")',
	),
	'UserName'=>array(
	),
	'Invoices'=>array(
		'visibility'=>COL_AVAILABLE,
		'fieldExpressionFunction'=>'colConfig("members:Invoices")',
	),
	'Categories'=>array(
		'fieldExpressionFunction'=>'colConfig("members:Categories")',
	),
);
function colConfig($param,$field='',$options=array()){
	global $record, $submode,$qr, $developerEmail, $fromHdrBugs, $modApType, $modApHandle;
	$a=$record;
	extract($a);
	extract($options);
	ob_start();
	switch($param){
		case 'Status':
			echo $Status;
		break;
		case 'NameCompany':
			//compare companyname, clientname, and person name
		break;
		case 'Phones':
			if($CellPhone)$e[]=$CellPhone.' (m)';
			if($HomePhone)$e[]=$HomePhone.' (h)';
			if($BusPhone)$e[]=$BusPhone.' (w)';
			if($HomeFax)$e[]=$HomeFax.' (f)';
			echo count($e) ? implode('<br />',$e) : '&nbsp;';
		break;
		case 'Email':
			if($Email){
				?><a href="mailto:<?php echo $Email?>"><?php echo $Email?></a><?php
			}else{
				?><em class="gray">(none)</em><?php
			}
		break;
		case 'Address':
			if($HomeAddress)$f[]=$HomeAddress;
			if($HomeCity || $HomeState || $HomeZip)$f[]= $HomeCity . ($HomeCity && $HomeState ? ', ':'') . (!$HomeCountry || strtolower(substr($HomeCountry,0,2))=='us' ? $HomeState : '') . ($HomeCountry && strtolower(substr($HomeCountry,0,2))!='us' ? ' &nbsp;'.$HomeCountry:'');
			if(count($f))echo implode('<br />',$f);
		break;
		case 'BusAddress':
			if($BusinessAddress)$f[]=$BusinessAddress;
			if($BusinessCity || $BusinessState || $BusinessZip)$f[]= $BusinessCity . ($BusinessCity && $BusinessState ? ', ':'') . (!$BusinessCountry || strtolower(substr($BusinessCountry,0,2))=='us' ? $BusinessState : '') . ($BusinessCountry && strtolower(substr($BusinessCountry,0,2))!='us' ? ' &nbsp;'.$BusinessCountry:'');
			if(count($f))echo implode('<br />',$f);
		break;
		case 'Invoices':
			if($InvoiceCount){
				echo $InvoiceCount.' invoice'.($InvoiceCount>1?'s':'').', last on '.date('n/j/Y',strtotime($LastInvoiceDate));
			}else{
				echo '&nbsp;';
			}
		break;
		case 'Categories':
			echo $Category.($CategoryCount>1 ? ' and '.($CategoryCount-1).' more' : '');
		break;
	}
	$out=ob_get_contents();
	ob_end_clean();
	return $out;
}

//html output items
if(!$refreshComponentOnly){
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
	<?php
	if(function_exists('dataset_complexDataCSS'))dataset_complexDataCSS(array(
		'datasetColorHeader_'=>'114d12',
		'datasetColorRowAlt_'=>'cddccd',
		'datasetColorSorted_'=>'wheat',
	));
	?>
	</style>
	<script language="javascript" type="text/javascript">
	/* -- from filter gadget -- */
	<?php echo $filterGadgetJS?>
	</script>
	<?php
}

require($MASTER_COMPONENT_ROOT.'/dataset_generic_precoding_v104.php');



require($MASTER_COMPONENT_ROOT.'/dataset_component_v124.php');


?>