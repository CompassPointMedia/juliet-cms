<?php
/* 
2009-09-01
----------
* integrated css style complexData in the new Library/css/DHTML/data_04_i1.css file - toggle active and inactive working, set active/inactive also

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

$qx['useRemediation']=true;
$qx['tableList']=array_merge($qx['tableList'], array('addr_contacts','finan_clients','finan_clients_statuses','finan_ClientsContacts','finan_ClientsCategories'));
$hideObjectInactiveControl=false;


//this allows tables to be created/remediated before the view is created
$tableCounts=array(
	'addr_contacts'=>q("SELECT COUNT(*) FROM addr_contacts", O_VALUE),
	'finan_clients'=>q("SELECT COUNT(*) FROM finan_clients", O_VALUE),
	'finan_ClientsContacts'=>q("SELECT COUNT(*) FROM finan_ClientsContacts", O_VALUE),
	'finan_clients_statuses'=>q("SELECT COUNT(*) FROM finan_clients_statuses", O_VALUE),
	'finan_ClientsCategories'=>q("SELECT COUNT(*) FROM finan_ClientsCategories", O_VALUE),
	'finan_items_categories'=>q("SELECT COUNT(*) FROM finan_items_categories", O_VALUE),
	'finan_invoices'=>q("SELECT COUNT(*) FROM finan_invoices", O_VALUE)
);

if($submode=='exportDataset')ob_start(); //------- for handling CSV export ---------

$dataset='Member';
$datasetComponent='memberList';
//$datasetArchitecture=NULL; not used
$datasetTable='_v_contacts_generic_20090901_v103';
$datasetTableIsView=true;
$datasetActiveUsage=true;
$datasetFieldList='*';

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
		'sortable'=>1,
	),
	'phones'=>array(
		'header'=>'Phones',
		'sortable'=>0
	),
	'address'=>array(
		'header'=>'Address',
		'sortable'=>1
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


switch(true){
	case $sort=='status':
		$orderBy="Status $asc";
	break;
	case $sort=='email':
		$orderBy="Email $asc";
	break;
	case $sort=='address':
		if(!$mdo['defaultAddressSet'])$mdo['defaultAddressSet']='Home';
		$orderBy=$datasetDefaultAddressSet."State $asc, ".$datasetDefaultAddressSet."City $asc, ".$datasetDefaultAddressSet."Address $asc";
	break;
	default: //Name
		$sort='name';
		$orderBy="LastName $asc, FirstName $asc";
}
$records=q(
	"SELECT $datasetFieldList FROM $datasetTable WHERE 1 ".
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

	AssignMenu('^optionsMembers$', 'optionsMembersMenu');
	AssignMenu('^reportsMembers$', 'reportsMembersMenu');



	hl_bg['memopt']='#6c7093';
	hl_baseclass['memopt']='normal';
	hl_class['memopt']='hlrow';
	//declare the ogrp.handle.sort value even if blank
	ogrp['memopt']=new Array();
	ogrp['memopt']['sort']='';
	ogrp['memopt']['rowId']='';
	ogrp['memopt']['highlightGroup']='memopt';
	AssignMenu('^r_([0-9]+)$', 'memberOptions');
	</script>
	<div id="memberOptions" class="menuskin1" style="z-index:1000;" onMouseOver="hlght2(event)" onMouseOut="llght2(event)" onClick="executemenuie5(event)" precalculated="memoptionsPre()">
		<div id="fho1" default="1" style="font-weight:900;" class="menuitems" command="openMember()" status="Show Information and Edit this member">Edit member</div>
		<div id="fho3" class="menuitems" command="memberAction(event, 'report');" status="Transaction Report">Transaction Report</div>
		<hr class="mhr"/>
		<div id="fho2" class="menuitems" command="memberAction(event, 'delete');" status="Delete this member">Delete</div>
	</div>	
	<div id="optionsMembersMenu" class="menuskin1" style="z-index:1000;" onmouseover="hlght2(event)" onmouseout="llght2(event)" onclick="executemenuie5(event)" precalculated="optionsDataset();">
		<div id="oh01" style="font-weight:900;" class="menuitems" command="addMember();" status="Add a new member">New Member</div>
		<hr class="mhr"/>
		<div id="oh02" nowrap="nowrap" class="menuitems" command="toggleActive('<?php echo $datasetComponent?>',hideInactive<?php echo $dataset?>);" status="option2">Show Inactive <?php echo $dataset?>s</div>
	</div>
	<div id="reportsMembersMenu" class="menuskin1" style="z-index:1000;width:225px;" onmouseover="hlght2(event)" onmouseout="llght2(event)" onclick="executemenuie5(event)" precalculated="reportsObjects();">
		<hr class="mhr"/>
		<div id="or01" class="menuitems" command="exportobjects('csv');" status="Export CSV spreadsheet for these results">Export CSV spreadsheet for these results</div>
	</div><?php
}
?>
<div class="fr" style="background-color:aliceblue">
	<div class="fl">
		<a id="optionsMembers" title="View Options" href="javascript:;" onclick="hidemenuie5(event,1);showmenuie5(event,1)" oncontextmenu="return false;"><img src="../images/i/person3_28x30-woman-red-prof.gif" alt="Members" width="23" height="30" /> Options</a>&nbsp;&nbsp; 
	</div>
	<div class="fl">
		<a id="reportsMembers" title="View Report Options" href="javascript:;" onclick="hidemenuie5(event,1);showmenuie5(event,1)" oncontextmenu="return false;"><img src="../images/i/addr_26x27.gif" alt="Reports" width="23" height="30" /> Reports</a>&nbsp;&nbsp;
	</div>
	<div class="fl">
		<?php 
		if(!isset($useStatusFilterOptions))$useStatusFilterOptions=true;
		if(!isset($showSessionFilters[$dataset]) || $showSessionFilters[$dataset]){
			require($COMPONENT_ROOT.'/comp_01_filtergadget_v103.php');
		}
		?>
	</div>
</div>
<div id="<?php echo $datasetComponent?>" refreshparams="noparams">
	<h3><?php echo $adminClientName?> Members (<span id="<?php echo $datasetComponent?>_count"><?php echo count($records);?></span>)</h3>
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
			<td colspan="100%"><a href="members.php?cbFunction=refreshComponent&cbParam=fixed:<?php echo $datasetComponent?>" onClick="return add<?php echo $dataset?>();"><img src="../images/i/add_32x32.gif" width="32" height="32">&nbsp;Add <?php echo strtolower($dataset)?>..</a></td>
			</tr>
		</tfoot>
		<?php //------------------------- end generic coding --------------------- ?>


		<tbody id="<?php echo $datasetComponent?>_tbody" style="overflow-y:scroll;overflow-x:hidden;height:350px;">
		<?php
		$datasetOutput='';
		if($records)
		foreach($records as $v){
			//apply any filters here
			$i++;
			//get permissions
			extract($v);
			$access=q("SELECT b.Name, 'Y' FROM addr_ContactsAccess a, addr_access b WHERE a.Access_ID=b.ID AND a.Contacts_ID='$Contacts_ID'", O_COL_ASSOC);
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
			
			
			
			?><tr id="r_<?php echo $ID?>" onclick="h(this,'memopt',0,0,event);" ondblclick="h(this,'memopt',0,0,event);open<?php echo $dataset?>();" oncontextmenu="h(this,'memopt',0,1,event);" class="normal<?php echo fmod($i,2)?' alt':''?>" deletable="<?php echo $deletable?>" active="<?php echo $Active?>">
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
					?><a title="Delete this member" href="resources/bais_01_exe.php?mode=deleteClient&Clients_ID=<?php echo $ID?>" target="w2" onClick="if(!confirm('This will permanently delete this member\'s record.  Are you sure?'))return false;">&nbsp;<img src="../images/i/del2.gif" alt="delete" width="16" height="18" border="0" /></a><?php
				}else{
					?>&nbsp;<img src="../images/assets/spacer.gif" width="18" height="18" /><?php
				}
				?>&nbsp;&nbsp;<a title="Edit this member's information" href="members.php?Clients_ID=<?php echo $ID?>" onClick="return ow(this.href,'l1_members','700,700');return false;"><img src="../images/i/edit2.gif" width="15" height="18" border="0"></a>&nbsp;</td>
				<!-- user columns -->
				<td <?php echo $sort=='status' ? 'class="sorted"':''?> style="<?php echo $ColorCode?'background-color:'.$ColorCode.';':''?>"><?php echo $StatusHandle;?></td>
				<td <?php echo $sort=='name' ? 'class="sorted"':''?>><?php echo $LastName.', '.$FirstName;?></td>
				<td <?php echo $sort=='email' ? 'class="sorted"':''?>><?php if($Email){
					?><a href="mailto:<?php echo $Email?>"><?php echo $Email;?></a><?php
				}
				if($AlternateEmail){
					?><br /><a href="mailto:<?php echo $AlternateEmail?>"><?php echo $AlternateEmail;?></a><?php
				}
				?></td>
				<td nowrap="nowrap"><?php 
				if($HomePhone)echo $HomePhone . '(H)<br />';
				if($HomeMobile)echo $HomeMobile . '(M)<br />';
				if($Pager)echo $Pager . '(P/V)<br />';
				if($BusPhone)echo $BusPhone . '(W)<br />';
				?></td>
				<td <?php echo $sort=='address' ? 'class="sorted"':''?>><?php 
				echo $v[$datasetDefaultAddressSet . 'Address']?><br />
				<?php echo $v[$datasetDefaultAddressSet . 'City'].', '.$v[$datasetDefaultAddressSet . 'State'].'&nbsp;&nbsp;'.$v[$datasetDefaultAddressSet . 'Zip']?></td>
				<td>&nbsp;&nbsp;</td>
			</tr><?php
		}
		?></tbody>
	</table>
</div>
<?php
if($submode=='exportDataset')ob_end_clean();
?>