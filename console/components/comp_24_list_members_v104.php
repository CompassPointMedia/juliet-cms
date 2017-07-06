<?php

/* 
2009-10=27
----------
* first step at variable columns, we list ALL availableColumns as an array
	availableColumns > datasetgroup > type='embedded' > nickname='first' > 'scheme'
Datasetgroup could be members, or could be a subset of members, OR COULD OVERLAP INTO OTHER DATASETS.  Embedded means user has no access to this scheme, and 'first' is the nickname (first developed :).
We then declare defaultObjectsOrder as an associative array where they key references the scheme, and the value is currently set to 1 (later, this array will allow account or user/session configuration of the columns).  If not declared, the component will declare it.
There is no protocol on visibility i.e. which columns are "system" columns.  But eventually we want to have a "fields" column in the upper right which when clicked allows the following things:
	1. show or hide the column by checking or unchecking
	2. configure the column by right clicking on that label
	3. prioritize the column up or down
	4. change the "virtual" column handle.  In this way I can get an exact CSV export for programs needing a specific import format

* as of now, submode=='dataExport' has not been synched in with the above, i.e. the CSV dump still outputs all fields in the view order.



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

if(!function_exists('member_cols')){
	function member_cols($cell){
		global $record,$colCount,$defaultObjectsOrder;
		switch(true){
			case $cell=='BusinessAddress' || $cell=='HomeAddress' || $cell=='ClientAddress':
				$cog=($cell=='HomeAddress' ? 'Home' : ($cell=='HomeAddress' ? 'Home' : 'Client'));
				$str.=$record[$cog.'Address'].($colCount==count($defaultObjectsOrder)?'&nbsp;&nbsp;&nbsp;':'').'<br />';
				$str.=$record[$cog.'City'].', '.$record[$cog.'State'].'&nbsp;&nbsp;'.$record[$cog.'Zip'];
				$str.=(strtolower($record[$cog.'Country'])!=='us' && strtolower($record[$cog.'Country'])!=='usa' ? '&nbsp;&nbsp;'.$record[$cog.'Country'] : '').($colCount==count($defaultObjectsOrder)?'&nbsp;&nbsp;&nbsp;':'');
			break;
			case $cell=='Phones':
				if($record['HomePhone'])$str.=$record['HomePhone'] . '(H)<br />';
				if($record['HomeMobile'])$str.= $record['HomeMobile'] . '(M)<br />';
				if($record['Pager'])$str.= $record['Pager'] . '(P/V)<br />';
				if($record['BusPhone'])$str.= $record['BusPhone'] . '(W)<br />';
			break;
			case $cell=='Email':
				if($record['Email']){
					$str='<a href="mailto:'.$record['Email'].'">'.$record['Email'].'</a>';
				}
				if($record['AlternateEmail']){
					$str.='<br /><a href="mailto:'.$record['AlternateEmail'].'">'.$record['AlternateEmail'].'</a>';
				}
			break;
			case $cell=='CreateDate':
				$str=t($record['CreateDate'], f_dspst, thisyear);
			break;
		}
		return $str;
	}
}

$qx['useRemediation']=true;
$qx['tableList']=array_merge($qx['tableList'], array('addr_contacts','finan_clients','finan_clients_statuses','finan_ClientsContacts','finan_ClientsCategories'));
$hideObjectInactiveControl=false;


//this allows tables to be created/remediated before the view is created
$tableCounts=array(
	'addr_contacts'=>q("SELECT Suffix FROM addr_contacts", O_VALUE),
	'finan_clients'=>q("SELECT CompanyName FROM finan_clients", O_VALUE),
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
if(!$datasetTable)$datasetTable='_v_contacts_generic_20090901_v103';
$datasetTableIsView=true;
$datasetActiveUsage=true;
$datasetFieldList='*';


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







/* 			-------------- added 2009-10-26 --------------			*/

$datasetGroup=$dataset; //Members
//so, this completely declares what is available for the layout; see scheme below
/*
i.e. embedded means, part of the programs; user really has no access to this now 
i.e. first means, what I'm nicknaming this available columns set
*/
$availableColumns[$datasetGroup]['embedded']['first']=array(
	'version'=>1.0,
	'description'=>'This is the first array implementation of relatebase_views and _views_items',
	'scheme'=>array(
		/*list these in order they would normally appear; analogous to Tbird's list of all inbox cols available */
		'RepCode'=>array(
			'method'=>'field',
			'fieldFormulaFunction'=>'RepCode',
			'header'=>'Rep',
			'orderBy'=>"RepCode $asc, LastName $asc, FirstName $asc"
		),
		'StatusHandle'=>array(
			'method'=>'field', /* the default */
			'fieldFormulaFunction'=>'StatusHandle', /* the default */
			'format'=>'default', /* use field attributes themselves*/
			'datatype'=>'text', /* could be email, phone number, URL, link, popup - conflicts possible */
			'sortable'=>true, /* the default */
			'sortTitle'=>'Sort by member status',
			'header'=>'Status',
			/* this called AFTER $sort and $asc present but before the query, for sort=Status */
			'orderBy'=>"StatusHandle $asc, /* extra stuff is nice :) */ LastName $asc, FirstName $asc",
			/* ------- etc., etc., etc. -------- */
			'colorCoding'=>NULL
		),
		'CreateDate'=>array(
			'method'=>'function',
			'fieldFormulaFunction'=>'member_cols(\'CreateDate\')',
			'sortable'=>true,
			'sortTitle'=>'Sort by record creation date',
			'header'=>'Created',
			'orderBy'=>"CreateDate $asc",
			'nowrap'=>true
		),
		'CompanyName'=>array(
			'method'=>'field',
			'header'=>'Company',
			'orderBy'=>"CompanyName $asc, LastName $asc, FirstName $asc"
		),
		'Name'=>array(
			'method'=>'formula',
			'fieldFormulaFunction'=>'h($LastName.\', \'.$FirstName)',
			'datatype'=>'name', /* not used yet :) */
			'format'=>'LNFN',
			'orderBy'=>"LastName $asc, FirstName $asc"
		),
		'Email'=>array(
			'method'=>'formula',
			'fieldFormulaFunction'=>'member_cols(\'Email\')',
			'datatype'=>'email',
			'orderBy'=>"Email $asc, LastName $asc, FirstName $asc"
		),
		'Phones'=>array(
			'method'=>'function',
			'fieldFormulaFunction'=>'member_cols(\'Phones\')',
			'sortable'=>false
		),
		'BusinessAddress'=>array(
			'method'=>'function',
			'header'=>'Bus. Address',
			'fieldFormulaFunction'=>'member_cols(\'BusinessAddress\')',
			'sortable'=>true,
			'orderBy'=>"BusinessCountry $asc, BusinessState $asc, BusinessCity $asc, BusinessAddress $asc"
		),
		'HomeAddress'=>array(
			'method'=>'function',
			'header'=>'Address',
			'fieldFormulaFunction'=>'member_cols(\'HomeAddress\')',
			'sortable'=>true,
			'orderBy'=>"HomeCountry $asc, HomeState $asc, HomeCity $asc, HomeAddress $asc"
		),
		'ClientAddress'=>array(
			'method'=>'function',
			'header'=>'Address',
			'fieldFormulaFunction'=>'member_cols(\'ClientAddress\')',
			'sortable'=>true,
			'orderBy'=>"ClientCountry $asc, ClientState $asc, ClientCity $asc, ClientAddress $asc"
		)
	)
);
//this is the part that needs to be made dynamic
if(!$defaultObjectsOrder)$defaultObjectsOrder=array(
	'StatusHandle'=>1,
	'CompanyName'=>1,
	'Name'=>1,
	'Email'=>1,
	'Phones'=>1,
	'HomeAddress'=>1
);
//redeclare datasetLayout in dynamic fields/order - all the attributes are present in availableColumns
foreach($defaultObjectsOrder as $handle=>$stat){
	$datasetLayout[$handle]=$availableColumns[$datasetGroup]['embedded']['first']['scheme'][$handle];
}




if(!$sort)$sort='Name';
$orderBy=$availableColumns[$datasetGroup]['embedded']['first']['scheme'][$sort]['orderBy'];
if(!$orderBy)$orderBy=1;

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
					?><th <?php echo $v['sortable'] || !isset($v['sortable']) ? 'sortable="1"' : ''?> <?php echo $sort==$n ? 'class="sorted"':''?>><?php if($v['sortable'] || !isset($v['sortable'])){ 
						//link tag for sort
						?><a href="resources/bais_01_exe.php?mode=refreshComponent&component=<?php echo $datasetComponent?>&sort=<?php echo $n?>&dir=<?php echo !$dir || ($sort==$n && $dir=='-1') ? 1 : -1?>" target="w2" title="<?php echo $v['title'];?>"><?php }?>
						<?php echo strlen($v['header']) ? $v['header'] : $n?>
						<?php 
						//close link tag
						if($v['sortable'] || !isset($v['sortable'])){ ?></a><?php }
					?></th><?php
				}
				?>
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
		foreach($records as $record){
			//apply any filters here
			$i++;
			//get permissions
			extract($record);
			$access=q("SELECT b.Name, 'Y' FROM addr_ContactsAccess a, addr_access b WHERE a.Access_ID=b.ID AND a.Contacts_ID='$Contacts_ID'", O_COL_ASSOC);
			$deletable=($r=q("SELECT COUNT(*) FROM finan_invoices WHERE Clients_ID=$ID", O_VALUE) ? false : true);
			if($submode=='exportDataset'){
				if(!$headerOutput){
					$datasetOutput.='"'.implode('","',$recordCols).'"';
					$headerOutput=true;
				}
				$str='';
				foreach($record as $w){
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
					?>&nbsp;<img src="../images/i/spacer.gif" width="18" height="18" /><?php
				}
				?>&nbsp;&nbsp;<a title="Edit this member's information" href="members.php?Clients_ID=<?php echo $ID?>" onClick="return ow(this.href,'l1_members','700,700');return false;"><img src="../images/i/edit2.gif" width="15" height="18" border="0"></a>&nbsp;</td>
				<?php
				//--------------- user columns coding added 2009-10-27 --------------
				$colCount=0;
				foreach($defaultObjectsOrder as $handle=>$stat){
					if(is_array($stat)){
						//not developed
						continue;
					}else{
						//deals with visibility
						if(!$stat)continue;
					}
					if(!($scheme=$availableColumns[$datasetGroup]['embedded']['first']['scheme'][$handle])){
						//error
						continue;
					}
					$colCount++;
					//-------- here is the kernel logic for how we present the fields --------
					if(!$scheme['method'] || $scheme['method']=='field'){
						$out=$record[$scheme['fieldFormulaFunction'] ? $scheme['fieldFormulaFunction'] : $handle];
					}else if($scheme['method']=='formula' || $scheme['method']=='function'){
						eval('$out='.rtrim($scheme['fieldFormulaFunction'],';').';');
					}
					if($submode=='exportDataset'){
						//$datasetOutput
						$datasetOutput.=str_replace('"','""',$out)."\t";
						continue;
					}else{
						//handle 1)wrap, 2)addt'l classes 3)overflow of data at some point
						?><td <?php echo $scheme['nowrap']?'nowrap':''?> <?php echo $sort==$handle ? 'class="sorted"':''?>><?php
						echo strlen($out) ? $out : '&nbsp;';
						?></td><?php
					}
				}
				//---------------------------------------------------------------------
				?>
			</tr><?php
		}
		?></tbody>
	</table>
</div>
<?php
if($submode=='exportDataset')ob_end_clean();
?>