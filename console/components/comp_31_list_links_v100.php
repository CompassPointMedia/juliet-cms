<?php
$qx['useRemediation']=true;
$qx['tableList']=array_merge($qx['tableList'], array('cmsb_links','cmsb_links_hits','cmsb_links_scans','cmsb_LinksLinks','_v_links_warranting_dependent_flat_20090913'));
$hideObjectInactiveControl=false;


//this allows tables to be created/remediated before the view is created
$tableCounts=array(
	'cmsb_links'=>q("SELECT COUNT(*) FROM cmsb_links", O_VALUE),
	'cmsb_links_hits'=>q("SELECT COUNT(*) FROM cmsb_links_hits", O_VALUE),
	'cmsb_links_scans'=>q("SELECT COUNT(*) FROM cmsb_links_scans", O_VALUE),
	'cmsb_LinksLinks'=>q("SELECT COUNT(*) FROM cmsb_LinksLinks", O_VALUE)
);

if($submode=='exportDataset')ob_start(); //------- for handling CSV export ---------

$dataset='Link';
$datasetComponent='linkList';
//$datasetArchitecture=NULL; not used
$datasetTable='_v_links_warranting_dependent_flat_20090913';
$datasetTableIsView=true;
$datasetActiveUsage=true;
$datasetFieldList='*';
$datasetLayout=array(
	'client'=>array(
		'header'=>'Client',
		'sortable'=>1,
		'title'=>'sort by client name'
	),
	'foundonpage'=>array(
		'header'=>'Found at',
		'sortable'=>1,
		'title'=>'sort by page where the link is located'
	),
	'linktopage'=>array(
		'header'=>'Links to',
		'sortable'=>1,
		'title'=>'sort by the URL of the link'
	),
	'hitsfrom'=>array(
		'header'=>'Hits',
		'sortable'=>1,
		'title'=>'sort by number of hits on this link'
	),
	'lastchecked'=>array(
		'header'=>'Last Checked',
		'sortable'=>1,
		'title'=>'sort by last check date for each link'
	),
	'status'=>array(
		'header'=>'Status',
		'sortable'=>1,
		'title'=>'sort by status'
	),
	'checkit'=>array(
		'header'=>'ck.',
		'sortable'=>0,
		'title'=>'run a check on this link'
	),
	'recip'=>array(
		'header'=>'Recip.',
		'sortable'=>1,
		'title'=>'whether or not the link has a parent reciprocal link'
	),
	'linktopage'=>array(
		'header'=>'Links to',
		'sortable'=>1,
		'title'=>'sort by the URL of the link'
	)
);
//this is stored in rbase_AccountModules.Settings for the Account (e.g. cpm103)
@extract($moduleConfig['dataobjects'][$dataset]);
if(!$datasetDefaultAddressSet)$datasetDefaultAddressSet='Home'; //Business|Client|Shipping

//-------------------- begin generic coding --------------------
if(!isset($datasetActiveActiveExpression))$datasetActiveActiveExpression='Active=1';
if(!isset($datasetActiveInactiveExpression))$datasetActiveInactiveExpression='Active=0';
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
$asc=($dir==-1?'DESC':'ASC');

/* filter for inactive */
if(isset($hideInactive)){
	//update settings and environment
	q("REPLACE INTO bais_settings SET UserName='".$_SESSION['admin']['userName']."', varnode='hideInactive$dataset',varkey='',varvalue='$hideInactive'");
	$_SESSION['userSettings']['hideInactive'.$dataset]=$hideInactive;
	?><script language="javascript" type="text/javascript">
	hideInactive<?php echo $dataset?>=<?php echo $hideInactive?>;
	window.parent.hideInactive<?php echo $dataset?>=<?php echo $hideInactive?>;
	</script><?php
}
$datasetActive = ( $userSettings['hideInactive'.$dataset]==1 ? $datasetActiveActiveExpression : ( $userSettings['hideInactive'.$dataset]==-1 ? $datasetActiveInactiveExpression : $datasetActiveAllExpression ));

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

//sorting
switch(true){
	case $sort=='client':
		$orderBy="ClientName $asc, PrimaryLastName $asc, PrimaryFirstName $asc";
	break;
	case $sort=='foundonpage':
		$orderBy="FoundOnPage $asc";
	break;
	case $sort=='linktopage':
		$orderBy="LinkToPage $asc";
	break;
	case $sort=='hitsfrom':
		$orderBy="Hits $asc, LastHitDate $asc";
	break;
	case $sort=='status':
		$orderBy="Status $asc";
	break;
	case $sort=='lastchecked':
		$orderBy="LastScanDate $asc";
	break;
	case $sort=='recip':
		$orderBy="ClientName $asc, PrimaryLastName $asc, PrimaryFirstName $asc";
	break;
	case $sort=='linktopage':
		$orderBy="WarrantingLinkCount $asc";
	break;
	default:
		$orderBy="CreateDate $asc";
}
$records=q(
	"SELECT $datasetFieldList FROM $datasetTable WHERE Direction='Incoming' ".
	($useStatusFilterOptions && count($inStatusSet) ? " AND Statuses_ID IN(".implode(',',$inStatusSet).")":'').
	($datasetActiveUsage==true ? " AND $datasetActive" : '').
	$filterQueries.
	" ORDER BY $orderBy $limit", O_ARRAY_ASSOC);

$recordCols=$qr['cols'];
if(!$refreshComponentOnly){
	?><style type="text/css">
	</style>
	<script type="text/javascript" language="javascript">
	function optionsDataset(){
		g('oh02').innerHTML=(hideInactive<?php echo $dataset?>?'Show inactive ':'Hide inactive ')+'<?php echo strtolower($dataset);?>s';
	}
	function reportsObjects(){
	}
	function exportobjects(node){
		if(node=='csv-all' && !confirm('This will export the list of '+dataojbect+'.  Continue?'))return;
		window.open('resources/bais_01_exe.php?suppressPrintEnv=1&mode=refreshComponent&component=<?php echo $datasetComponent?>&submode=exportDataset&object=<?php echo $dataset?>&node='+node,'w2');
	}
	function lnkoptionsPre(){
		for(var j in hl_grp['lnkopt'])j=j.replace('r_','');
	}
	function addLink(){
		ow('links.php?cbFunction=refreshComponent&cbParam=fixed:<?php echo $datasetComponent;?>','l1_links','700,700',true);
		return false;
	}
	function openLink(){
		for(var j in hl_grp['lnkopt'])j=j.replace('r_','');
		ow('links.php?cbFunction=refreshComponent&cbParam=fixed:<?php echo $datasetComponent?>&Links_ID='+j,'l1_links','700,700');
	}
	function linkAction(event, action){
		for(var j in hl_grp['lnkopt'])j=j.replace('r_','');
		if(action=='delete'){
			if(!g('r_'+j).getAttribute('deletable')){
				if(confirm('This link cannot be deleted; it has transactions associated with it.  You must first delete all transactions.\n\nWould you like to see a transaction history report for this link?')){
					ow('transactionhistory.php?Links_ID='+j,'l1_transactionhistory','700,700');
				}
				return false;
			}
			if(!confirm('This will permanently delete this link\'s record.  Are you sure?'))return false;
			window.open('resources/bais_02_01_exe.php?mode=deleteLink&Links_ID='+j,'w2');
		}else if(action=='report'){
			if(!j){
				alert('First click on a link record and highlight its row');
				return;
			}
			ow('transactionhistory.php?Links_ID='+j,'l1_transactionhistory','700,700');
		}
	}
	AssignMenu('^optionsLinks$', 'optionsLinksMenu');
	AssignMenu('^reportsLinks$', 'reportsLinksMenu');

	hl_bg['lnkopt']='#6c7093';
	hl_baseclass['lnkopt']='normal';
	hl_class['lnkopt']='hlrow';
	//declare the ogrp.handle.sort value even if blank
	ogrp['lnkopt']=new Array();
	ogrp['lnkopt']['sort']='';
	ogrp['lnkopt']['rowId']='';
	ogrp['lnkopt']['highlightGroup']='lnkopt';
	AssignMenu('^r_([0-9]+)$', 'linkOptions');
	</script>
	<div id="linkOptions" class="menuskin1" style="z-index:1000;" onMouseOver="hlght2(event)" onMouseOut="llght2(event)" onClick="executemenuie5(event)" precalculated="lnkoptionsPre()">
		<div id="fho1" default="1" style="font-weight:900;" class="menuitems" command="openLink()" status="Show Information and Edit this link">Edit link</div>
		<div id="fho3" class="menuitems" command="linkAction(event, 'report');" status="Transaction Report">Transaction Report</div>
		<hr class="mhr"/>
		<div id="fho2" class="menuitems" command="linkAction(event, 'delete');" status="Delete this link">Delete</div>
	</div>	
	<div id="optionsLinksMenu" class="menuskin1" style="z-index:1000;" onmouseover="hlght2(event)" onmouseout="llght2(event)" onclick="executemenuie5(event)" precalculated="optionsDataset();">
		<div id="oh01" style="font-weight:900;" class="menuitems" command="addLink();" status="Add a new link">New Link</div>
		<hr class="mhr"/>
		<div id="oh02" nowrap="nowrap" class="menuitems" command="toggleActive('<?php echo $datasetComponent?>',hideInactive<?php echo $dataset?>);" status="option2">Show Inactive <?php echo $dataset?>s</div>
	</div>
	<div id="reportsLinksMenu" class="menuskin1" style="z-index:1000;width:225px;" onmouseover="hlght2(event)" onmouseout="llght2(event)" onclick="executemenuie5(event)" precalculated="reportsObjects();">
		<hr class="mhr"/>
		<div id="or01" class="menuitems" command="exportobjects('csv');" status="Export CSV spreadsheet for these results">Export CSV spreadsheet for these results</div>
	</div><?php
}
?>
<div class="fr" style="background-color:aliceblue">
	<div class="fl">
		<a id="optionsLinks" title="View Options" href="javascript:;" onclick="hidemenuie5(event,1);showmenuie5(event,1)" oncontextmenu="return false;"><img src="../images/i/person3_28x30-woman-red-prof.gif" alt="Links" width="23" height="30" /> Options</a>&nbsp;&nbsp; 
	</div>
	<div class="fl">
		<a id="reportsLinks" title="View Report Options" href="javascript:;" onclick="hidemenuie5(event,1);showmenuie5(event,1)" oncontextmenu="return false;"><img src="../images/i/addr_26x27.gif" alt="Reports" width="23" height="30" /> Reports</a>&nbsp;&nbsp;
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
<div id="<?php echo $datasetComponent?>" refreshparams="noparams">
	<h3><?php echo $adminClientName?> Links (<span id="<?php echo $datasetComponent?>_count"><?php echo count($records);?></span>)</h3>
	<input type="hidden" name="noparams" id="noparams" value="" />
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="complexData" style="clear:both;">
		<?php //------------------------- begin generic THEAD/TFOOT coding --------------------- ?>
		<thead>
			<tr>
				<!-- control cells -->
				<?php if(!$hideObjectInactiveControl){ ?>
				<th id="toggleActive" class="activetoggle"><a title="Hide or show inactive <?php echo strtolower($dataset);?>" href="javascript:toggleActive('<?php echo $datasetComponent?>',hideInactive<?php echo $dataset?>);">&nbsp;&nbsp;</a></th>
				<?php } ?>
				<th>&nbsp;</th><?php
				//----------- column headers ----------------
				foreach($datasetLayout as $n=>$v){
					?><th id="hdr-<?php echo $n?>" <?php echo $v['sortable'] ? 'sortable="1"' : ''?> <?php echo $sort==$n ? 'class="sorted"':''?>><?php if($v['sortable']){ 
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
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tfoot>
			<tr valign="top">
			<td colspan="100%"><a href="links.php?cbFunction=refreshComponent&cbParam=fixed:<?php echo $datasetComponent?>" onClick="return add<?php echo $dataset?>();"><img src="../images/i/add_32x32.gif" width="32" height="32">&nbsp;Add <?php echo strtolower($dataset)?>..</a></td>
			</tr>
		</tfoot>
		<?php //------------------------- end generic coding --------------------- ?>


		<tbody id="<?php echo $datasetComponent?>_tbody" <?php if(count($records)>20){ ?>style="overflow-y:scroll;overflow-x:hidden;height:350px;"<?php } ?>>
		<?php
		$datasetOutput='';
		if($records)
		foreach($records as $v){
			//apply any filters here
			$i++;
			extract($v);
			//get permissions
			#not used, see comp_24_list_members_v103.php		
			
			
			?><tr id="r_<?php echo $ID?>" onclick="h(this,'memopt',0,0,event);" ondblclick="h(this,'lnkopt',0,0,event);open<?php echo $dataset?>();" oncontextmenu="h(this,'lnkopt',0,1,event);" class="normal<?php echo fmod($i,2)?' alt':''?>" deletable="<?php echo $deletable?>" active="<?php echo $Active?>">
				<?php if(!$hideObjectInactiveControl){ ?>
				<td id="r_<?php echo $ID?>_active" title="Make this <?php echo strtolower($dataset);?> <?php echo $Active ? 'in':''?>active" onclick="toggleActiveObject('<?php echo $dataset?>',<?php echo $ID?>,'<?php echo $datasetComponent?>');" class="activetoggle"><?php
				if(!$Active){
					?><img src="../images/i/garbage2.gif" width="18" height="21" align="absbottom" /><?php
				}else{
					?>&nbsp;<?php
				}
				?></td>
				<?php } ?>

				<td nowrap="nowrap"><?php
				if($deletable){
					?><a title="Delete this link" href="resources/bais_01_exe.php?mode=deleteClient&Links_ID=<?php echo $ID?>" target="w2" onClick="if(!confirm('This will permanently delete this link\'s record.  Are you sure?'))return false;">&nbsp;<img src="../images/i/del2.gif" alt="delete" width="16" height="18" border="0" /></a><?php
				}else{
					?>&nbsp;<img src="../images/i/spacer.gif" width="18" height="18" /><?php
				}
				?>&nbsp;&nbsp;<a title="Edit this link's information" href="links.php?Links_ID=<?php echo $ID?>" onClick="return ow(this.href,'l1_links','700,700');return false;"><img src="../images/i/edit2.gif" width="15" height="18" border="0"></a>&nbsp;</td>


				<!-- user columns -->
				<td><?php echo $ClientName . ($CompanyName && $PrimaryLastName?' - ':''). $PrimaryLastName . ', '.$PrimaryFirstName?></td>
				<td><a class="tableLink" title="View the page this link is found on" target="_blank" href="<?php echo $FoundOnPage?>"><?php echo preg_replace('#http(s*)://(www\.)*#i','',$FoundOnPage)?></a></td>
				<td><a class="tableLink" title="See what this link leads to" target="_blank" href="<?php echo $LinkToPage?>"><?php echo preg_replace('#http(s*)://(www\.)*#i','',$LinkToPage)?></a></td>
				<td><?php echo $Hits?></td>
				<td><?php if($LastScanDate!=='0000-00-00 00:00:00')echo date('m/d/Y g:iA',strtotime($LastScanDate));?></td>
				<td><?php echo $Status?></td>
				<td>ck now.</td>
				<td><?php if($WarrantingLinkCount){ ?>
				<a title="click to view the parent (outbound) link for this incoming link" href="#wl<?php echo $WarrantingLink?>">yes</a>
				<?php }?></td>
				<td>&nbsp;&nbsp;</td>
			</tr><?php
		}
		?></tbody>
	</table>
</div>
<?php
if($submode=='exportDataset')ob_end_clean();
?>