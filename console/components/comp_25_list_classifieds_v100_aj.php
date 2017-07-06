<?php
//-------------------- codeblock 002261 ----------------
/*

these were brought in from list_members to achieve the following:
* integrate the filter object, BUT use child region (Regions_ID) instead of status (Statuses_ID)
* handle direct export and sunset the previous menu option to export children
* standardize variables such as $dataset - even though in GiocosaCare they may not be used like the rbrfm components

*/
$dataset='Ad';							//not used exc. for filter gadget
$datasetComponent='adList'; 			//not used exc. for filter gadget
$datasetTable='_v_publisher_ads_flat_with_order'; 		//not used
$datasetTableIsView=true; 					//not used
$datasetActiveUsage=true; 					//not used
$useStatusFilterOptions=true;
$filterGadgetMode='refreshComponent'; 		//first used in this comp_50; all processing done in filtergadget
$filterGadgetUserName=$_SESSION['admin']['userName']; //first used in this comp_50
//------------------- end codeblock 002261 ------------------

//2009-10-08: special feature here, we have the "status" (office) where it is null
if(!q("SELECT COUNT(*) FROM bais_settings WHERE UserName='".$_SESSION['admin']['userName']."' AND vargroup='Ad' AND varnode='filterAdStatus' AND varkey='(None)'", O_VALUE)){
	q("REPLACE INTO bais_settings SET UserName='".$_SESSION['admin']['userName']."', vargroup='Ad', varnode='filterAdStatus', varkey='(None)', varvalue=1");
	$_SESSION['userSettings']['filterChildStatus:(None)']=1;
}

if($submode=='exportDataset')ob_start(); //------- for handling CSV export ---------

$hideObjectInactiveControl=false;

if($sort){
	q("REPLACE INTO bais_settings SET UserName='".$_SESSION['admin']['userName']."', vargroup='Ad',varnode='defaultAdSort',varkey='',varvalue='$sort'");
	q("REPLACE INTO bais_settings SET UserName='".$_SESSION['admin']['userName']."', vargroup='Ad',varnode='defaultAdSortDirection',varkey='',varvalue='".($dir?$dir:1)."'");
	$_SESSION['userSettings']['defaultAdSort']=$sort;
	$_SESSION['userSettings']['defaultAdSortDirection']=($dir?$dir:1);
}else{
	$sort=$userSettings['defaultAdSort'];
	$dir=$userSettings['defaultAdSortDirection'];
}

$ids=q("SELECT * FROM _v_publisher_ads_flat_with_order", O_ARRAY);

if(isset($hideInactive)){
	//update settings and environment
	q("REPLACE INTO bais_settings SET UserName='".$_SESSION['admin']['userName']."', varnode='hideInactiveAd',varkey='',varvalue='$hideInactive'");
	$_SESSION['userSettings']['hideInactiveAd']=$hideInactive;
	?><script language="javascript" type="text/javascript">
	hideInactiveAd=<?php echo $hideInactive?>;
	window.parent.hideInactiveAd=<?php echo $hideInactive?>;
	</script><?php
}
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

if(!$refreshComponentOnly){
	?><style type="text/css">
	.complexData thead{						/* header, non-sorted */
		background-color:#b4a99e;
		}
	.complexData th{
		color:#FFF;
		}
	.complexData th.sorted{					/* header, sorted */
		background-color:#a48e7c;
		background-image:url("/images/i/arrows/wht-arrow-sm-dn.png");
		background-position:4px center;
		background-repeat:no-repeat;
		}
	.complexData tr{						/* row, normal color (default white) */
		}
	.complexData tr.alt{					/* row, alt color */
		background-color:#faeaea;
		}
	.complexData tr.alt td.sorted{			/* row-alt, col-sorted */
		background-color:#f1d5c8;
		}
	.complexData td.sorted{					/* row-normal, col-sorted */
		background-color:#f6e8da;
		}
	.complexData td.activetoggle{
		background-color:#CCC;
		padding-left:3px;
		padding-right:3px;
		width:24px;
		}
	.complexData th.activetoggle{
		background-color:#666;
		}
	.complexData tfoot td{
		border:none;
		background-image:none;
		border-top:1px solid #000;
		}
	
	.hlrow td{							/* h() with new className change */
		background-color:#f6aaaa;
		}
	.hlrow td.sorted{
		background-color:#f69b91;		/* highlight-sorted (but can't differentiate :( */
		}
	@media screen{
		.complexData tbody{
			overflow-x:hidden;
			overflow-y:scroll;
			height:500px;
			}
		}
	@media print{
		.complexData tfoot{
			display:none;
			}
		}
	</style>
	<script type="text/javascript" language="javascript">
	hl_bg['chopt']='#6c7093';
	hl_baseclass['chopt']='normal';
	hl_class['chopt']='hlrow';
	//hl_txt['chopt']='';
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

<div class="menubar fr">
	<?php 
	if(!isset($useStatusFilterOptions))$useStatusFilterOptions=true;
	if(!isset($showSessionFilters[$dataset]) || $showSessionFilters[$dataset]){
		require('/home/rbase/lib/console-rbrfm/components/comp_01_filtergadget_v104.php');
	}
	?>
</div>
<div class="menubar fr">
	<a id="optionsAd" title="View Options" href="javascript:;" onclick="hidemenuie5(event,1);showmenuie5(event,1)" oncontextmenu="return false;"><img src="/images/i/home.jpg" alt="Ad" width="32" height="32" /> Options</a>&nbsp;&nbsp;
</div>
<div class="menubar fr">
	<a id="reportsAd" title="View Ad Reports" href="javascript:;" onclick="hidemenuie5(event,1);showmenuie5(event,1)" oncontextmenu="return false;"><img src="/images/i/addr_26x27.gif" width="26" height="27"> Reports</a>
</div>
<div id="<?php echo $datasetComponent;?>" refreshparams="noparams">
	<div id="classifiesshowoptions" style="float:right;" class="additional">
		<a href="list_classifieds.php?show=all">show all</a>	&nbsp;&nbsp;|&nbsp;&nbsp;
		<a href="list_classifieds.php?show=pending">show pending </a>	&nbsp;&nbsp;|&nbsp;&nbsp;
		<a href="list_classifieds.php?show=approved">show approved </a></div>
	<h2>Ads: Classifieds</h2>
	<input type="hidden" id="noparams" value="1" />
	<table cellspacing="0" cellpadding="0" class="complexData" style="clear:both;">
			<thead>
		<tr>
			<?php if(!$hideObjectInactiveControl){ ?>
			<th title="Hide or show inactive Ad" class="activetoggle"><a href="javascript:toggleActive('listAd',hideInactiveAd);">&nbsp;&nbsp;</a></th>
			<?php } ?>
			<th>&nbsp;&nbsp;</th>
			<th <?php echo $sort=='appr' ? 'class="sorted"':''?>><a href="resources/bais_01_exe.php?mode=refreshComponent&component=listAd&sort=appr&dir=<?php echo !$dir || ($sort=='appr' && $dir=='-1') ? 1 : '-1'; ?>" target="w2" title="Sort by Approval Status">Appr.</a></th>
			<th <?php echo $sort=='req' ? 'class="sorted"':''?>><a href="resources/bais_01_exe.php?mode=refreshComponent&component=listAd&sort=req&dir=<?php echo !$dir || ($sort=='req' && $dir=='-1') ? 1 : '-1'; ?>" target="w2" title="Sort by Request Number">Req. #</a></th>
			<th <?php echo $sort=='runFrom' ? 'class="sorted"':''?>><a href="resources/bais_01_exe.php?mode=refreshComponent&component=listAd&sort=runFrom&dir=<?php echo !$dir || ($sort=='runFrom' && $dir=='-1') ? 1 : '-1'; ?>" target="w2" title="Sort by Run From Date">Run From</a></th>
			<th <?php echo $sort=='runTo' ? 'class="sorted"':''?>><a href="resources/bais_01_exe.php?mode=refreshComponent&component=listAd&sort=runTo&dir=<?php echo !$dir || ($sort=='runTo' && $dir=='-1') ? 1 : '-1'; ?>" target="w2" title="Sort by Run To Date">Run To</a></th>
			<th <?php echo $sort=='name' ? 'class="sorted"':''?>><a href="resources/bais_01_exe.php?mode=refreshComponent&component=listAd&sort=name&dir=<?php echo !$dir || ($sort=='name' && $dir=='-1') ? 1 : '-1'; ?>" target="w2" title="Sort by Name">Name</a></th>
			<th <?php echo $sort=='category' ? 'class="sorted"':''?>><a href="resources/bais_01_exe.php?mode=refreshComponent&component=listAd&sort=category&dir=<?php echo !$dir || ($sort=='category' && $dir=='-1') ? 1 : '-1'; ?>" target="w2" title="Sort by Category">Category</a></th>
			<th <?php echo $sort=='cost' ? 'class="sorted"':''?>><a href="resources/bais_01_exe.php?mode=refreshComponent&component=listAd&sort=cost&dir=<?php echo !$dir || ($sort=='cost' && $dir=='-1') ? 1 : '-1'; ?>" target="w2" title="Sort by Cost">Cost</a></th>
			<th <?php echo $sort=='billed' ? 'class="sorted"':''?>><a href="resources/bais_01_exe.php?mode=refreshComponent&component=listAd&sort=billed&dir=<?php echo !$dir || ($sort=='billed' && $dir=='-1') ? 1 : '-1'; ?>" target="w2" title="Sort by Billed">Billed.</a></th>
			<th <?php echo $sort=='content' ? 'class="sorted"':''?>><a href="resources/bais_01_exe.php?mode=refreshComponent&component=listAd&sort=content&dir=<?php echo !$dir || ($sort=='content' && $dir=='-1') ? 1 : '-1'; ?>" target="w2" title="Sort by Content">Content</a></th>
			<th <?php echo $sort=='action' ? 'class="sorted"':''?>><a href="resources/bais_01_exe.php?mode=refreshComponent&component=listAd&sort=action&dir=<?php echo !$dir || ($sort=='action' && $dir=='-1') ? 1 : '-1'; ?>" target="w2" title="Sort by Action">Action</a></th>
		</tr>
		</thead>
		<tfoot>
			<tr><td colspan="100%"><a href="classifieds.php?cbFunction=refreshComponent&cbParam=fixed:adList" title="Create a new classified ad" onclick="return ow(this.href,'l1_ads','700,700',true);"><img src="/images/i/add_32x32.gif" width="32" height="32" border="0">&nbsp;New Classified Ad</a></td></tr>
		</tfoot>
		<tbody id="listAd_tbody">
		<?php
		$i=0;
		if($ids)
		foreach($ids as $v){
			$i++;
			if($submode=='exportDataset'){
				foreach($v as $o=>$w)$v[$o]=str_replace('"','""',$w);
				if($i==1)$datasetOutput='"'.implode('","',array_keys($v)).'"'."\n";
				$datasetOutput.='"'.implode('","',$v).'"'."\n";
				continue;
			}
			extract($v);
			/*
			if($_SESSION['admin']['roles'][ROLE_FOUNDATION_DIRECTOR] && !$HistoryCount && !$logs){
				$deletable=true;
			}else{
				$deletable=false;
			}
			$canWriteTreatmentPlans=(
				$_SESSION['admin']['roles'][ROLE_FOUNDATION_DIRECTOR] || 
				$_SESSION['admin']['roles'][ROLE_PROGRAM_DIRECTOR] || 
				$_SESSION['admin']['roles'][ROLE_CASE_MANAGER] || 
				$_SESSION['admin']['roles'][ROLE_THERAPIST] ? true : false
			);
			*/
			?><tr id="r_<?php echo $ID?>" onclick="h(this,'chopt',0,0,event);" ondblclick="h(this,'chopt',0,0,event);openAd();" oncontextmenu="h(this,'chopt',0,1,event);" class="normal<?php echo fmod($i,2)?' alt':''?>" deletable="<?php echo $deletable?>" active="<?php echo $Active?>">
				<?php if(!$hideObjectInactiveControl){ ?>
				<td id="r_<?php echo $ID?>_active" title="Make this Ad <?php echo $Active ? 'in':''?>active" onclick="toggleActiveObject('listAd',<?php echo $ID?>);" class="activetoggle"><?php
				if(!$Active){
					?><img src="/images/i/garbage2.gif" width="18" height="21" align="absbottom" /><?php
				}else{
					?>&nbsp;<?php
				}
				?></td>
				<?php } ?>
				<td nowrap="nowrap"><?php
				if($deletable){
					?><a title="Delete this Ad" href="resources/bais_01_exe.php?mode=deleteAd&amp;Ads_ID=<?php echo $ID?>&amp;cb=refresh" target="w2" onclick="if(!confirm('This will permanently delete this ad\'s record.  Are you sure?'))return false;"><img src="/images/i/del2.gif" alt="delete" width="16" height="18" border="0" /></a><?php
				}else{
					?><img src="/images/assets/spacer.gif" width="18" height="18" /><?php
				}
				?><a title="Edit this ad's information" href="classifieds.php?Ads_ID=<?php echo $ID?>" onclick="return ow(this.href,'l1_ads','700,700');return false;"><img src="/images/i/edit3.gif" width="15" height="18" border="0"></a>&nbsp;</td>
				<td nowrap="nowrap" <?php echo $sort=='appr' ? 'class="sorted"':''?>><?php echo $Approved?'Y':'N';?></td>
				<td nowrap="nowrap" <?php echo $sort=='req' ? 'class="sorted"':''?>><?php echo $ID;?></td>
				<td nowrap="nowrap" <?php echo $sort=='runFrom' ? 'class="sorted"':''?>><?php echo date('m/d',strtotime($StartDate));?></td>
				<td nowrap="nowrap" <?php echo $sort=='runTo' ? 'class="sorted"':''?>><?php echo date('m/d',strtotime($EndDate));?></td>
				<td nowrap="nowrap" <?php echo $sort=='name' ? 'class="sorted"':''?>><?php echo $FirstName . ($MiddleName?' '.$MiddleName : '') . ' '.$LastName;?></td>
				<td nowrap="nowrap" <?php echo $sort=='category' ? 'class="sorted"':''?>><?php echo $Category;?></td>
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
		?></tbody>
	</table>
</div>
<?php
if($submode=='exportDataset')ob_end_clean();
?>