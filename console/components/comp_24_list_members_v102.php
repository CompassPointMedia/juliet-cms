<?php
$qx['useRemediation']=true;
$qx['tableList']=array('addr_contacts','finan_clients','finan_clients_statuses','finan_ClientsContacts');

/* 
2009-07-03
----------
* major change - altered the primary key to Clients_ID vs. Contacts_ID.

2009-06-03
----------
* NOTE - we have been doing e.g. hideInactiveMember=0|1 where we need to have showActive=-1|0|1 (three options) - IMPLEMENT THIS CHANGE SOMETIME!
ACTUALLY WE SHOULD IMPLEMENT BOTH SYSTEMS AS NEEDED

2009-06-03
----------
* modified logic for IN(statuses) in the query to prevent bluescreen when no query set
* NOTE this is using the CONTACTS_id as the refrence for opening the focus view 
* made a significant code block generic (see below), and solidified around the concept of "dataset" as the name for that set of data
* handled bais_settings so that if not systemUserName we presume PHP_AUTH_USER
version 2.00 I am developing some standards for list view components in this - through changing the Chamber structure to a <?php echo $adminClientName?> Structure, here are their fields:
Name
Call#
Shift
[email]
Phones
Title
Access To
Joined Dept

pre 2009-06:
	modified and componentized from comp_14_memberstbody.php 

*/

if($submode=='exportDataset')ob_start(); //------- for handling CSV export ---------

$dataset='Member';
$datasetComponent='memberList';
$datasetArchitecture='11'; //one to one
$datasetTable='finan_clients';
$datasetActiveUsage=true;
$datasetFieldList='a.ID, c.*, c.ID AS Contacts_ID, a.ID, d.Name AS StatusName, d.ColorCode, d.StatusHandle'.($useStatusFilterOptions ? ', a.Statuses_ID':'');

$datasetLayout=array(
	'statusname'=>array(
		'header'=>'Status',
		'sortable'=>1,
		'title'=>'Sort by member status'
	),
	'name'=>array(
		'header'=>'Name',
		'sortable'=>1,
		'title'=>'Sort by member name'
	),
	'email'=>array(
		'header'=>'Email',
		'sortable'=>0,
	),
	'phones'=>array(
		'header'=>'Phones',
		'sortable'=>0
	),
	'address'=>array(
		'header'=>'Address',
		'sortable'=>0
	)
);

//-------------------- begin generic coding --------------------
if(!isset($datasetActiveActiveExpression))$datasetActiveActiveExpression='a.Active=1';
if(!isset($datasetActiveInactiveExpression))$datasetActiveInactiveExpression='a.Active=0';
if(!isset($datasetActiveAllExpression))$datasetActiveAllExpression='1';
if($sort){
	q("REPLACE INTO bais_settings SET UserName='".($_SESSION['systemUserName'] ? $_SESSION['systemUserName'] : $GLOBALS['PHP_AUTH_USER'])."', 
	vargroup='".$dataset."',varnode='default".$dataset."Sort',varkey='',varvalue='$sort'");
	q("REPLACE INTO bais_settings SET UserName='".($_SESSION['systemUserName'] ? $_SESSION['systemUserName'] : $GLOBALS['PHP_AUTH_USER'])."', 
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

switch(true){
	case $sort=='address':
		$records=q("SELECT
		$datasetFieldList 
		FROM finan_clients a 
		LEFT JOIN finan_ClientsContacts cc ON a.ID=cc.Clients_ID AND cc.Type='Primary'
		LEFT JOIN addr_contacts c ON cc.Contacts_ID=c.ID
		LEFT JOIN finan_clients_statuses d ON a.Statuses_ID=d.ID
		WHERE a.ResourceType IS NOT NULL ".
		($useStatusFilterOptions && count($inStatusSet) ? " AND a.Statuses_ID IN(".implode(',',$inStatusSet).")":'').
		($datasetActiveUsage==true ? " AND $datasetActive" : '').
		"$filterQueries
		GROUP BY a.ID
		ORDER BY c.HomeState $asc, c.HomeCity $asc, c.HomeAddress $asc $limit", O_ARRAY_ASSOC);
	break;
	default: //Name
		$records=q("SELECT
		$datasetFieldList 
		FROM finan_clients a 
		LEFT JOIN finan_ClientsContacts cc ON a.ID=cc.Clients_ID AND cc.Type='Primary'
		LEFT JOIN addr_contacts c ON cc.Contacts_ID=c.ID
		LEFT JOIN finan_clients_statuses d ON a.Statuses_ID=d.ID
		WHERE a.ResourceType IS NOT NULL ".
		($useStatusFilterOptions && count($inStatusSet) ? " AND a.Statuses_ID IN(".implode(',',$inStatusSet).")":'').
		($datasetActiveUsage==true ? " AND $datasetActive" : '').
		"$filterQueries
		GROUP BY a.ID
		ORDER BY c.LastName $asc, c.FirstName $asc $limit", O_ARRAY_ASSOC);
}
$recordCols=$qr['cols'];
if(!$refreshComponentOnly){
	?><style>
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
	.data1 th{
		color:#FFF;
		font-weight:400;
		text-align:left;
		}
	.data1 th a{
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
	hl_bg['memopt']='#6c7093';
	//hl_txt['memopt']='';
	//declare the ogrp.handle.sort value even if blank
	ogrp['memopt']=new Array();
	ogrp['memopt']['sort']='';
	ogrp['memopt']['rowId']='';
	ogrp['memopt']['highlightGroup']='memopt';
	AssignMenu('^r_([0-9]+)$', 'memberOptions');
	function memoptionsPre(){
		for(var j in hl_grp['memopt'])j=j.replace('r_','');
		
	}
	function addMember(){
		ow('members.php?cbFunction=refreshComponent&cbParam=fixed:<?php echo $datasetComponent;?>','l1_members','700,700',true);
		return false;
	}
	function openMember(){
		for(var j in hl_grp['memopt'])j=j.replace('r_','');
		ow('members.php?cbFunction=refreshComponent&cbParam=fixed:<?php echo $datasetComponent?>&Clients_ID='+j,'l1_members','700,700');
	}

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
	</script>
	<div id="memberOptions" class="menuskin1" style="z-index:1000;" onMouseOver="hlght2(event)" onMouseOut="llght2(event)" onClick="executemenuie5(event)" precalculated="memoptionsPre()">
		<div id="fho1" default="1" style="font-weight:900;" class="menuitems" command="openMember()" status="Show Information and Edit this member">Edit member</div>
		<div id="fho3" class="menuitems" command="memberAction(event, 'report');" status="Transaction Report">Transaction Report</div>
		<hr class="mhr"/>
		<div id="fho2" class="menuitems" command="memberAction(event, 'delete');" status="Delete this member">Delete</div>
	</div>	
	<?php
}
?>
<div id="<?php echo $datasetComponent?>" refreshparams="noparams">
	<input type="hidden" name="noparams" id="noparams" value="" />
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="data1" style="clear:both;">


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

			<tr valign="top">
			<td colspan="100%"><a href="members.php?cbFunction=refreshComponent&cbParam=fixed:<?php echo $datasetComponent?>" onClick="return add<?php echo $dataset?>();"><img src="../images/i/add_32x32.gif" width="32" height="32">&nbsp;Add <?php echo strtolower($dataset)?>..</a></td>
			</tr>
		</tfoot>
		<?php //------------------------- end generic coding --------------------- ?>


		<tbody id="<?php echo $datasetComponent?>_tbody" <?php if($browser=='Moz')echo 'style="overflow-y:scroll;overflow-x:auto;height:350px;"';?>>
		<?php
		$datasetOutput='';
		if($records)
		foreach($records as $v){
			//apply any filters here
			$i++;
			//get permissions
			extract($v);
			$access=q("SELECT b.Name, 'Y' FROM addr_ContactsAccess a, addr_access b WHERE a.Access_ID=b.ID AND a.Contacts_ID=$Contacts_ID", O_COL_ASSOC);
			$deletable=($r=q("SELECT COUNT(*) FROM finan_invoices WHERE Clients_ID=$ID", O_VALUE) ? false : true);
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
			
			
			
			?><tr id="r_<?php echo $ID?>" onclick="h(this,'memopt',0,0,event);" ondblclick="h(this,'memopt',0,0,event);open<?php echo $dataset?>();" oncontextmenu="h(this,'memopt',0,1,event);" <?php if(!fmod($i,2))echo 'class="alt"';?>  deletable="<?php echo $deletable?>">
				<?php if(!$userSettings['hideInactive'.$dataset]){ ?>
				<td id="r_<?php echo $ID?>_active" title="Make this <?php echo $dataset?> <?php echo $Active ? 'in':''?>active" onclick="toggleActiveObject(<?php echo $ID?>);" active="<?php echo $Active?>"><?php
				if(!$Active){
					?><img src="../images/i/garbage2.gif" width="18" height="21" align="absbottom" /><?php
				}else{
					?>&nbsp;<?php
				}
				?></td>
				<?php } ?>


				<td nowrap="nowrap"><?php
				if($deletable){
					?><a title="Delete this member" href="resources/bais_01_exe.php?mode=deleteClient&Clients_ID=<?php echo $ID?>" target="w2" onClick="if(!confirm('This will permanently delete this member\'s record.  Are you sure?'))return false;">&nbsp;<img src="../images/i/del2.gif" alt="delete" width="16" height="18" border="0" /></a><?php
				}else{
					?>&nbsp;<img src="../images/assets/spacer.gif" width="18" height="18" /><?php
				}
				?>&nbsp;&nbsp;<a title="Edit this member's information" href="members.php?Clients_ID=<?php echo $ID?>" onClick="return ow(this.href,'l1_members','700,700');return false;"><img src="../images/i/edit2.gif" width="15" height="18" border="0"></a>&nbsp;</td>
				<!-- user columns -->
				<td <?php echo $sort=='statushandle' ? 'class="sorted"':''?> style="<?php echo $ColorCode?'background-color:'.$ColorCode.';':''?>"><?php echo $StatusHandle;?></td>
				<td <?php echo $sort=='name' ? 'class="sorted"':''?>><?php echo $LastName.', '.$FirstName;?></td>
				<td><?php if($Email){
					?><a href="mailto:<?php echo $Email?>"><?php echo $Email;?></a><?php
				}
				if($Email2){
					?><br /><a href="mailto:<?php echo $Email2?>"><?php echo $Email2;?></a><?php
				}
				?></td>
				<td nowrap="nowrap"><?php 
				if($HomePhone)echo $HomePhone . '(H)<br />';
				if($HomeMobile)echo $HomeMobile . '(M)<br />';
				if($Pager)echo $Pager . '(P/V)<br />';
				if($BusPhone)echo $BusPhone . '(W)<br />';
				?></td>
				<td><?php echo $BusAddress?><br />
				<?php echo $BusCity . ', '.$BusState.'&nbsp;&nbsp;'.$BusZip?></td>
				<td>&nbsp;&nbsp;</td>


			</tr><?php
		}
		?></tbody>
	</table>
</div>
<?php
if($submode=='exportDataset')ob_end_clean();
?>