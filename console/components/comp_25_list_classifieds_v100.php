<?php
/*
2009-06-08
* used the coding from comp_24_list_members_v100.php
* actual stock: I moved this over from HBCEO because it had a clean interface with a beginning of filters.  I am integrating this in with the new dataset style of component



*/

if($submode=='exportDataset')ob_start(); //------- for handling CSV export ---------


$dataset='Ad';
$datasetComponent='adList';
$datasetArchitecture=NULL;
$datasetTable='_v_publisher_ads_flat_with_order';
$datasetActiveUsage=false;
if(!isset($datasetActiveActiveExpression))$datasetActiveActiveExpression='Active=1';
if(!isset($datasetActiveInactiveExpression))$datasetActiveInactiveExpression='Active=0';
//$datasetFieldList=''; - unused

$datasetLayout=array(
	'Approved'=>array(
		'header'=>'Appr.',
		'sortable'=>1,
		'title'=>'Sort by approval status'
	),
	'ID'=>array(
		'header'=>'Req#',
		'sortable'=>1,
		'title'=>'Sort by request number'
	),
	'StartDate'=>array(
		'header'=>'Run From',
		'sortable'=>1,
		'title'=>'Sort by starting date'
	),
	'EndDate'=>array(
		'header'=>'Run To',
		'sortable'=>1,
		'title'=>'Sort by ending date of ad'
	),
	'LastName,FirstName'=>array(
		'header'=>'Name',
		'sortable'=>1,
		'title'=>'Sort by last and first name'
	),
	'Category'=>array(
		'header'=>'Category',
		'sortable'=>1,
		'title'=>'Sort by category'
	),
	'cost'=>array(
		'header'=>'Cost',
		'sortable'=>0
	),
	'billed'=>array(
		'header'=>'Billed',
		'sortable'=>0
	),
	'content'=>array(
		'header'=>'Content',
		'sortable'=>0
	),
	'actions'=>array(
		'header'=>'Actions',
		'sortable'=>0
	)
);

//-------------------- begin generic coding --------------------
if(!isset($datasetActiveActiveExpression))$datasetActiveActiveExpression='a.Active=1';
if(!isset($datasetActiveInactiveExpression))$datasetActiveInactiveExpression='a.Active=0';
if(!isset($datasetActiveAllExpression))$datasetActiveAllExpression='1';
if($sort){
	q("REPLACE INTO bais_settings SET UserName='".sun()."', 
	vargroup='".$dataset."',varnode='default".$dataset."Sort',varkey='',varvalue='$sort'");
	q("REPLACE INTO bais_settings SET UserName='".sun()."', 
	vargroup='".$dataset."',varnode='default".$dataset."SortDirection',varkey='',varvalue='".($dir?$dir:1)."'");
	$_SESSION['userSettings']['default'.$dataset.'Sort']=$sort;
	$_SESSION['userSettings']['default'.$dataset.'SortDirection']=($dir?$dir:1);
}else{
	$sort=$userSettings['default'.$dataset.'Sort'];
	$dir=$userSettings['default'.$dataset.'SortDirection'];
}
/* filter for inactive members */
$datasetActive=($userSettings['hideInactive'.$dataset]==-1? $datasetActiveInactiveExpression :  ($userSettings['hideInactive'.$dataset]==1 ? $datasetActiveActiveExpression : '1'));
$asc=($dir==-1?'DESC':'ASC');

//set where clause including statuses and filter pairs
unset($filterQueries);
if($filterOverride){
	$filterQueries=$filterOverride;
}else if($sqlQueries){
	 $filterQueries=' AND (' . implode( (strtolower($_SESSION['special']['filter'.$dataset.'QueryJoin'])=='or' ? ' OR ' : ' AND '), $sqlQueries) . ')';
}else if(count($_SESSION['special']['filterQuery'][$dataset])){
	foreach($_SESSION['special']['filterQuery'][$dataset] as $v){
		if($x=parse_query($v,$datasetTable)) $sqlQueries[]=$x;
	}
	if($sqlQueries) $filterQueries=' AND (' . implode( (strtolower($_SESSION['special']['filter'.$dataset.'QueryJoin'])=='or' ? ' OR ' : ' AND '), $sqlQueries) . ')';
}
//-------------------- end generic coding --------------------

//set in session because we have a report on this component
if($_REQUEST['show']){
	$show=$_SESSION['special']['adsShow']=$_REQUEST['show'];
}else if($_SESSION['special']['adsShow']){
	$show=$_SESSION['special']['adsShow'];
}else{
	$show='pending';
}
$showclause=($show=='all' ? '' : ($show=='pending' ? ' AND Approved=0' : ' AND Approved=1'));

if(!$sort)$sort='1';

ob_start();
$records=q("SELECT * FROM _v_publisher_ads_flat_with_order WHERE 1 ".
$showclause.
($useStatusFilterOptions && count($inStatusSet) ? " AND c.Statuses_ID IN(".implode(',',$inStatusSet).")":'').
($datasetActiveUsage==true ? " AND $datasetActive" : '').
"$filterQueries 
ORDER BY $sort $asc $limit", O_ARRAY, ERR_ECHO);
$err=ob_get_contents();
ob_end_clean();

$recordCols=$qr['cols'];


if($err){
	unset($_SESSION['userSettings']['default'.$dataset.'Sort'],	$_SESSION['userSettings']['default'.$dataset.'SortDirection']);
	q("DELETE FROM bais_settings WHERE 
	UserName='".sun()."' AND
	vargroup='".$dataset."' AND 
	(varnode='default".$dataset."Sort' OR varnode='default".$dataset."SortDirection')");
	mail($developerEmail,'error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
	?><script language="javascript" type="text/javascript">
	var loc=window.parent.location;
	window.parent.location=loc.replace(/&*sort=[^&]*/,'').replace(/&*dir=[^&]*/,'');
	</script><?php
}


if(!$refreshComponentOnly){ 
	?>
	<style type="text/css">
	#switchboardconsole{
	
		}
	#<?php echo $datasetComponent?> table td{
		border-bottom:none;
		}
	#<?php echo $datasetComponent?> table .alt{
		background-color:#EEE;
		}
	</style>
	<script language="javascript" type="text/javascript">
	function takeaction(o,id,node){
		if(o.value=='')return false;
		if(o.value=='delete' && !confirm('Delete the '+(node=='seminar' ? 'seminar purchase':'test')+' permanently?  This cannot be undone!')){
			o.selectedIndex=0;
			return false;
		}
		//window.open('resources/bais_01_exe.php?mode=actions&submode='+o.value+'&Tests_ID='+id+'&node='+node, 'w2');
	}
	hl_bg['adsopt']='#557';
	//hl_txt['adsopt']='';
	//declare the ogrp.handle.sort value even if blank
	ogrp['adsopt']=new Array();
	ogrp['adsopt']['sort']='';
	ogrp['adsopt']['rowId']='';
	ogrp['adsopt']['highlightGroup']='adsopt';
	AssignMenu('^r_[0-9]+$', 'adsOptions');
	function adsoptionsPre(){
		for(var j in hl_grp['adsopt'])j=j.replace('r_','');
		g('ado2').className=(g('r_'+j).getAttribute('approved')=='1' ? 'menuitems mndis' : 'menuitems');
	}
	function addAd(){
		ow('classifieds.php?cbFunction=refreshComponent&cbParam=fixed:<?php echo $datasetComponent;?>','l1_ads','700,700',true);
		return false;
	}
	function openAd(){
		for(var j in hl_grp['adsopt'])j=j.replace('r_','');
		ow('classifieds.php?cbFunction=refreshComponent&cbParam=fixed:<?php echo $datasetComponent;?>&Ads_ID='+j,'l1_ads','700,700');
	}
	function deleteAd(n){
		if(!confirm('This will permanently delete this ad.  Are you sure?'))return false;
		g('r_'+n).style.display='none';
		window.open('resources/bais_01_exe.php?mode=deleteAd&Ads_ID='+n,'w2');
		return false;
	}
	/*
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
	*/
	</script>
	<div id="adsOptions" class="menuskin1" style="z-index:1000;" onMouseOver="hlght2(event)" onMouseOut="llght2(event)" onClick="executemenuie5(event)" precalculated="adsoptionsPre()">
		<div id="ado1" default="1" style="font-weight:900;" class="menuitems" command="openAd()" status="Show Information and Edit this ad">Edit ad</div>
		<div id="ado2" class="menuitems" command="takeaction('approve');" status="Approve this ad">Approve Ad</div>
		<hr class="mhr"/>
		<div id="fho2" class="menuitems" command="takeaction('action');" status="Delete this ad">Delete</div>
	</div>	
	
	
	<?php
}
?>
<div id="<?php echo $datasetComponent;?>" refreshparams="noparams">
	<div id="classifiesshowoptions" style="float:right;" class="additional">
		<a href="list_classifieds.php?show=all">show all</a>	&nbsp;&nbsp;|&nbsp;&nbsp;
		<a href="list_classifieds.php?show=pending">show pending </a>	&nbsp;&nbsp;|&nbsp;&nbsp;
		<a href="list_classifieds.php?show=approved">show approved </a></div>
	<h2>Ads: Classifieds</h2>
	<input type="hidden" id="noparams" value="1" />
	<table class="data1" cellpadding="0" cellspacing="0">
		<?php //------------------------- begin generic THEAD/TFOOT coding --------------------- ?>
		<thead>
			<tr>
				<!-- control cells -->
				<th id="toggleActive" style="display:<?php echo $userSettings['hideInactive'.$dataset] ? 'none' : 'table-cell'?>;"><a title="Hide or show inactive <?php echo strtolower($dataset);?>" href="javascript:toggleActive();">^</a></th><th>&nbsp;</th><?php
				//----------- column headers ----------------
				foreach($datasetLayout as $n=>$v){
					?><th id="hdr-<?php echo $n?>" <?php echo $v['sortable'] ? 'sortable="1"' : ''?>><?php if($v['sortable']){ 
						//link tag for sort
						?><a id="a-<?php echo $n?>" href="resources/bais_01_exe.php?mode=refreshComponent&component=<?php echo $datasetComponent?>&sort=<?php echo $n?>&dir=<?php echo !$dir || ($sort==$n && $dir=='-1') ? 1 : -1?>" target="w2" title="<?php echo $v['title'];?>"><?php }?>
						<?php echo $v['header']?>
						<?php 
						//close link tag
						if($v['sortable']){ ?></a><?php }
					?></th><?php
				}
				//keeps right text from being obscured
				?>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="100%"><a href="classifieds.php?cbFunction=refreshComponent&cbParam=fixed:adList" title="Create a new classified ad" onclick="return ow(this.href,'l1_ads','700,700',true);">New classified ad..</a></td>
			</tr>
		</tfoot>
		<?php //------------------------- end generic coding --------------------- ?>
		<tbody style="overflow-y:scroll;height:350px;">
		<?php
		if($records){
			$i=0;
			foreach($records as $v){
				extract($v);
				$deletable=1;
				$i++;

				if($submode=='exportDataset'){
					if(!$headerOutput){
						$datasetOutput.='"'.implode('","',$recordCols).'"';
						$headerOutput=true;
					}
					$str='';
					foreach($v as $w){
						$quote=(preg_match('/[,"]/',$w) ? '"' : '');
						$str.=$quote . str_replace('"', '""', $w). $quote.',';
					}
					$datasetOutput.=($datasetOutput ? "\n" : '').rtrim($str,',');
					continue; //no HTML output
				}


				?><tr id="r_<?php echo $Ads_ID?>" onclick="h(this,'adsopt',0,0,event);" ondblclick="h(this,'adsopt',0,0,event);open<?php echo $dataset?>();" oncontextmenu="h(this,'adsopt',0,1,event);" <?php if(!fmod($i,2))echo 'class="alt"';?>  deletable="<?php echo $deletable?>" approved="<?php echo $Approved?>" class="<?php echo !fmod($i,2)?'alt':''?>">
					<?php if(!$userSettings['hideInactive'.$dataset]){ ?>
					<td id="r_<?php echo $Ads_ID?>_active" title="Make this <?php echo $dataset?> <?php echo $Active ? 'in':''?>active" onclick="toggleActiveObject(<?php echo $Ads_ID?>);" active="<?php echo $Active?>"><?php
					if(!$Active){
						?><img src="../images/i/garbage2.gif" width="18" height="21" align="absbottom" /><?php
					}else{
						?>&nbsp;<?php
					}
					?></td>
					<?php } ?>
	
	
					<td nowrap="nowrap"><?php
					if($deletable){
					?><a title="Delete this ad" href="#" target="w2" onclick="return deleteAd(<?php echo $Ads_ID?>)">&nbsp;<img src="../images/i/del2.gif" alt="delete" width="16" height="18" border="0" /></a><?php
					}else{
						?>&nbsp;<img src="../images/i/spacer.gif" width="18" height="18" /><?php
					}
					?>&nbsp;&nbsp;<a title="Edit this ad" href="classifieds.php?Ads_ID=<?php echo $Ads_ID?>" onClick="return ow(this.href,'l1_ads','700,700');return false;"><img src="../images/i/edit2.gif" width="15" height="18" border="0"></a>&nbsp;[<a title="view this ad on the site" target="_blank" href="<?php echo $classifiedsAdsFocusPage?>?Ads_ID=<?php echo $Ads_ID?>&authToken=<?php echo md5($MASTER_PASSWORD.'key'.$Ads_ID);?>">view</a>]</td>
					<!-- user columns -->
					<td nowrap="nowrap"><?php echo $Approved?'Y':'N';?></td>
					<td nowrap="nowrap"><?php echo $ID;?></td>
					<td nowrap="nowrap"><?php echo date('m/d',strtotime($StartDate));?></td>
					<td nowrap="nowrap"><?php echo date('m/d',strtotime($EndDate));?></td>
					<td nowrap="nowrap"><?php echo $FirstName . ($MiddleName?' '.$MiddleName : '') . ' '.$LastName;?></td>
					<td nowrap="nowrap"><?php echo $Category;?></td>
					<td><?php 
					if(!is_null($Extension)){
						$billed=true;
						echo number_format($Extension,2);
					}else{
						$billed=false;
						//calculate base + xtra words * #weeks
						$a=trim(strip_tags($Content));
						$a=preg_split('/[ ,]+/',$a);
						$cost=($basePrice + (count($a)-$baseWords>0 ? count($a)-$baseWords : 0)*$pricePerWordExtra) * ceil((strtotime($EndDate) - strtotime($StartDate))/(3600*24*7));
						echo number_format($cost,2);
					}
					?></td>
				<td>
				<?php echo $billed ? 'Y' : 'N';?>
				</td>
				<td title="<?php echo h(strip_tags($Content));?>"><?php
				preg_match('/<span[^>]+>(.*)<\/span>/i',$Content,$a);
				echo strip_tags($a[1]);
				?></td>
				<td>
				<select name="action" onchange="takeaction(this,<?php echo $Ads_ID?>,'classifieds');">
					<option value="">choose..</option>
					<option value="approve">Approve ad</option>
					<option value="decline">Decline ad</option>
					<option value="hide">Hide this ad</option>
					<option value="show">Unhide this ad</option>
					<option value="extend">Extend this ad..</option>
					<option value="delete">Delete (CAREFUL!)</option>
				</select>
				</td>
				</tr><?php
			}
		}
		?>
		</tbody>
	</table>
</div>
<?php
if($submode=='exportDataset')ob_end_clean();
?>