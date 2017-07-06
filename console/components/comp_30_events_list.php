<?php
/*
copied from KFD comp_19
*/
$sortMode='sortEvents';
$dataobjectLayout=array(
	'date'=>array(
		'header'=>'Date',
		'sortable'=>1,
		'title'=>'Sort by starting date of event'
	),
	'name'=>array(
		'header'=>'Name',
		'sortable'=>1,
		'title'=>'Sort by event name'
	),
	'description'=>array(
		'header'=>'Description',
		'sortable'=>0
	),
	'location'=>array(
		'header'=>'Location',
		'sortable'=>1,
		'title'=>'Sort by event name'
	)
);






#in bais_01_exe.php
if($sort){
	q("REPLACE INTO bais_settings SET UserName='".$_SESSION['systemUserName']."', vargroup='calendar',varnode='defaultEventSort',varkey='',varvalue='$sort'");
	q("REPLACE INTO bais_settings SET UserName='".$_SESSION['systemUserName']."', vargroup='calendar',varnode='defaultEventSortDirection',varkey='',varvalue='".($dir?$dir:1)."'");
	$_SESSION['userSettings']['defaultEventSort']=$sort;
	$_SESSION['userSettings']['defaultEventSortDirection']=($dir?$dir:1);
}else{
	$sort=$userSettings['defaultEventSort'];
	$dir=$userSettings['defaultEventSortDirection'];
}
/* filter for inactive events */
$eventActive=($userSettings['hideInactiveEvents']? 'a.Active=1 AND ' : '1 AND ');
$asc=($dir==-1?'DESC':'ASC');
$in=array();
$sqlQueries=array();
$filterQueries='';
if(count($_SESSION['special']['filterEventQuery'])){
	foreach($_SESSION['special']['filterEventQuery'] as $v){
		if($x=parse_query($v,'cal_events')) $sqlQueries[]=$x;
	}
	if(count($sqlQueries))$filterQueries='(' . implode( (strtolower($_SESSION['special']['filterEventQueryJoin'])=='or' ? ' OR ' : ' AND '), $sqlQueries) . ') AND ';
}
if(!$fieldList)$fieldList='a.*, UNIX_TIMESTAMP(a.StartDate) AS StartDate, UNIX_TIMESTAMP(a.EndDate) AS EndDate';
switch(true){
	case $sort=='date':
		$ids=q("SELECT
		$fieldList
		FROM cal_events a
		WHERE ResourceType IS NOT NULL AND $eventActive $filterQueries 1
		ORDER BY a.StartDate $asc $limit", O_ARRAY_ASSOC);
	break;
	case $sort=='location':
		$ids=q("SELECT
		$fieldList
		FROM cal_events a
		WHERE ResourceType IS NOT NULL AND $eventActive $filterQueries 1
		ORDER BY a.State, a.City, a.Address $asc $limit", O_ARRAY_ASSOC);
	break;
	default:
		$sort='name';
		$ids=q("SELECT
		$fieldList
		FROM cal_events a
		WHERE ResourceType IS NOT NULL AND $eventActive $filterQueries 1
		ORDER BY a.Name $asc $limit", O_ARRAY_ASSOC);
}




if(!$refreshComponentOnly){
	?><style type="text/css">
	/** CSS Declarations for this page 
	#cbdff3 - darker
	#dae8f6 - lighter
	#9797bb - lighter blue
	**/
	.data1 td{
		/* background-color:#eef4fb; /*#9Ec4e9*/
		cursor:pointer;
		padding:2px 2px 1px 7px;
		}
	.data1 td.sorted{
		background-color:#dae8f6; /*#9Ec4e9*/
		color:#272727;
		}
		
	.data1 tr.alt{
		background-color:#cde0f3; /*#9Ec4e9*/
		}
	.data1 tr.alt td.sorted{
		background-color:#cbdff3; /*#9Ec4e9*/
		}
	.data1 thead{
		background-color:#006; /*#FEDFAE*/
		}
	.data1 a{
		color:DARKRED;
		}
	.data1 th, .data1 th a{
		vertical-align:bottom;
		color:#FFF;
		font-size:109%;
		font-weight:400;
		padding:4px 0px 0px 8px;
		}
	.data1 th{
		text-align:left;
		border-bottom:1px solid #000;
		}
	.data1 td{
		font-size:13px;
		border-bottom:none;
		/*border-bottom:1px dotted #333;*/
		}
	.data1 th.sorted{
		background-color:#9797bb;
		}
	
	</style>
	<script type="text/javascript" language="javascript">
	hl_bg['evopt']='#6c7093';
	//hl_txt['evopt']='';
	//declare the ogrp.handle.sort value even if blank
	ogrp['evopt']=new Array();
	ogrp['evopt']['sort']='';
	ogrp['evopt']['rowId']='';
	ogrp['evopt']['highlightGroup']='evopt';
	AssignMenu('^r_([0-9]+)$', 'eventOptions');
	
	
	function evoptionsPre(){
		for(var j in hl_grp['evopt'])j=j.replace('r_','');
		
	}
	function eventAction(event, action){
		for(var j in hl_grp['evopt'])j=j.replace('r_','');
		if(action=='delete'){
			if(!g('r_'+j).getAttribute('deletable')){
				if(confirm('This event cannot be deleted; it has transactions associated with it.  You must first delete all transactions.\n\nWould you like to see a transaction history report for this event?')){
					ow('transactionhistory.php?Events_ID='+j,'l1_transactionhistory','700,700');
				}
				return false;
			}
			if(!confirm('This will permanently delete this event\'s record.  Are you sure?'))return false;
			window.open('resources/bais_01_exe.php?mode=deleteEvent&Events_ID='+j,'w2');
		}else if(action=='report'){
			if(!j){
				alert('First click on a event record and highlight its row');
				return;
			}
			ow('transactionhistory.php?Events_ID='+j,'l1_transactionhistory','700,700');
		}
	}
	</script>
	<div id="eventOptions" class="menuskin1" style="z-index:1000;" onMouseOver="hlght2(event)" onMouseOut="llght2(event)" onClick="executemenuie5(event)" precalculated="evoptionsPre()">
		<div id="fho1" default="1" style="font-weight:900;" class="menuitems" command="openEvent()" status="Show Information and Edit this event">Edit event</div>
		<div id="fho3" class="menuitems" command="eventAction(event, 'report');" status="Report">Report</div>
		<hr class="mhr"/>
		<div id="fho2" class="menuitems" command="eventAction(event, 'delete');" status="Delete this event">Delete</div>
	</div><?php
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="data1" style="clear:both;">
	<thead>
		<tr>
			<?php
			//improved th's to a loop-through: td's still custom coded
			?><!-- control cells -->
			<th id="toggleActive" title="Hide or show inactive events" style="display:<?php echo $userSettings['hideInactiveEvents'] ? 'none' : 'table-cell'?>;"><a href="javascript:toggleActive();">^</a></th><th>&nbsp;</th><?php
			//----------- column headers ----------------
			foreach($dataobjectLayout as $n=>$v){
				?><th id="hdr-<?php echo $n?>" <?php echo $v['sortable'] ? 'sortable="1"' : ''?>><?php if($v['sortable']){ 
					//link tag for sort
					?><a id="a-<?php echo $n?>" href="resources/bais_01_exe.php?mode=<?php echo $sortMode?>&sort=<?php echo $n?>&dir=<?php echo !$dir || ($sort==$n && $dir=='-1') ? 1 : -1?>" target="w2" title="<?php echo $v['title'];?>"><?php }?>
					<?php echo $v['header']?>
					<?php 
					//close link tag
					if($v['sortable']){ ?></a><?php }
				?></th><?php
			}
			//keeps right text from being obscured
			?><th style="color:#FFF;font-weight:400;">&nbsp;&nbsp;&nbsp;&nbsp;</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td><a href="events.php?cbFunction=refreshComponent&cbParam=fixed:listEvents" onclick="return ow(this.href,'l1_events','700,700',true);"><img src="../images/i/add_32x32.gif" width="32" height="32">&nbsp;Add event..</a></td>
		</tr>
	</tfoot>
	<tbody id="listEvents" <?php if($browser=='Moz')echo 'style="overflow:scroll;height:350px;"';?>>
	<?php
	if($ids)
	foreach($ids as $v){
		//apply any filters here
		$i++;
		extract($v);
		$deletable=true;
		?><tr id="r_<?php echo $ID?>" onclick="h(this,'evopt',0,0,event);" ondblclick="h(this,'evopt',0,0,event);openEvent();" oncontextmenu="h(this,'evopt',0,1,event);" <?php if(!fmod($i,2))echo 'class="alt"';?>  deletable="<?php echo $deletable?>">
			<?php if(!$userSettings['hideInactiveEvents']){ ?>
			<td id="r_<?php echo $ID?>_active" title="Make this event <?php echo $Active ? 'in':''?>active" onclick="toggleActiveObject(<?php echo $ID?>);" active="<?php echo $Active?>"><?php
			if(!$Active){
				?><img src="../images/i/garbage2.gif" width="18" height="21" align="absbottom" /><?php
			}else{
				?>&nbsp;<?php
			}
			?></td>
			<?php } ?>
			<td nowrap="nowrap"><?php
			if($deletable){
				?><a title="Delete this event" href="resources/bais_01_exe.php?mode=deleteEvent&amp;Events_ID=<?php echo $ID?>&amp;cb=refresh" target="w2" onClick="if(!confirm('This will permanently delete this event.  Are you sure?'))return false;">&nbsp;<img src="../images/i/del2.gif" alt="delete" width="16" height="18" border="0" /></a><?php
			}else{
				?>&nbsp;<img src="../images/i/spacer.gif" width="18" height="18" /><?php
			}
			?>&nbsp;&nbsp;<a title="Edit this event" href="events.php?Events_ID=<?php echo $ID?>" onClick="return ow(this.href,'l1_events','770,700');return false;"><img src="../images/i/edit2.gif" width="15" height="18" border="0"></a>&nbsp;</td>

			<td nowrap="nowrap" <?php echo $sort=='date' ? 'class="sorted"':''?>><?php echo date('M j, Y',$StartDate) . ($EndDate && $EndDate!=$StartDate ? ' - '.date('M j, Y',$EndDate) : ''); ?></td>
			<td nowrap="nowrap" <?php echo $sort=='name' ? 'class="sorted"':''?>><?php echo $Name; ?></td>
			<td><?php echo $BriefDescription; ?></td>
			<td nowrap="nowrap" <?php echo $sort=='location' ? 'class="sorted"':''?>><?php 
			echo $Location ? $Location . '<br />' : '';
			if($Address && $City)echo $Address . ' - ' . $City;
			if($Zip)echo ', '.$Zip;
			?></td>
			<td>&nbsp;</td>
		</tr><?php
	}
	?></tbody>
</table>