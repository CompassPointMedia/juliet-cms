<?php
//see list_members.php for the first use of this component
if(!$dataset || !$datasetComponent){
	mail($developerEmail,'Error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
	error_alert('You did not declare one of the following variables: dataset or datasetComponent');
}
if(!$datasetWord || !$datasetWordPlural){
	mail($developerEmail,'Error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
	error_alert('You did not declare one of the following variables: dataset or datasetComponent');
}
if(!$datasetGroup)$datasetGroup=$dataset; //Member

/* 
2009-11-29
----------
* this file is "almost" generic vs. specific to members, the only excpetion is the toolbar, all specific settings above
Here are some items to do:
* improve toolbar handling
* DONE	abstract regions and name them properly
* error messages when minimum variables are not passed; debug mode with suggestsions
* separate columns grapically
* color themes available
* sort order more apparent
* grouping and collapsing available
* icons for headers, more robust features on cell data, logic on sorting by, say, email address by domain then person
* 

2009-11-28
----------
* I'm unclear as to whether datasetGroup or dataset are bigger
* redid it and lost var defaultObjectsOrder - all this can be figured from the compiled array of availableCols using subkeys

2009-10-27
----------
* first step at variable columns, we list ALL availableCols as an array
	availableCols > datasetgroup > type='embedded' > nickname='first' > 'scheme'
Datasetgroup could be members, or could be a subset of members, OR COULD OVERLAP INTO OTHER DATASETS.  Embedded means user has no access to this scheme, and 'first' is the nickname (first developed :).
There is a protocol on visibility i.e. disposition of columns:
	COL_VISIBLE=16
	COL_AVAILABLE=8
	COL_SYSTEM=4
	COL_HIDDEN=2
	COL_RESTRICTED=1

But eventually we want to have a "fields" column in the upper right which when clicked allows the following things:
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
------------
modified and componentized from comp_14_memberstbody.php 
*/

if(!function_exists('member_cols')){
	function member_cols($cell){
		global $record,$colPosition,$visibleColCount;
		switch(true){
			case $cell=='BusinessAddress' || $cell=='HomeAddress' || $cell=='ClientAddress':
				$cog=($cell=='HomeAddress' ? 'Home' : ($cell=='HomeAddress' ? 'Home' : 'Client'));
				$str.=$record[$cog.'Address'].($colPosition==$visibleColCount?'&nbsp;&nbsp;&nbsp;':'').'<br />';
				$str.=$record[$cog.'City'].', '.$record[$cog.'State'].'&nbsp;&nbsp;'.$record[$cog.'Zip'];
				$str.=(strtolower($record[$cog.'Country'])!=='us' && strtolower($record[$cog.'Country'])!=='usa' ? '&nbsp;&nbsp;'.$record[$cog.'Country'] : '').($colPosition==$visibleColCount?'&nbsp;&nbsp;&nbsp;':'');
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
if(!function_exists('get_contents')){
	require($FUNCTION_ROOT.'/function_get_contents_v100.php');
}


//-------------------- begin generic coding --------------------
if($submode=='exportDataset')ob_start(); //------- for handling CSV export ---------

//this is stored in rbase_AccountModules.Settings for the Account (e.g. cpm103)
@extract($moduleConfig['dataobjects'][$dataset]);

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
	q("REPLACE INTO bais_settings SET UserName='".sun()."', 
	vargroup='".$dataset."',varnode='cols".$dataset."Sort',varkey='',varvalue='$sort'");

	$sort=$userSettings['default'.$dataset.'Sort'];
	$dir=$userSettings['default'.$dataset.'SortDirection'];
}
$asc=($dir==-1?'DESC':'ASC');

//2009-11-28: added column selection
if($col){
	q("REPLACE INTO bais_settings SET UserName='".sun()."', 
	vargroup='".$dataset."',varnode='".$dataset."ColVisibility',varkey='".$col."',varvalue='".($visibility ? $visibility : COL_VISIBLE)."'");
	$_SESSION['userSettings'][$dataset.'ColVisibility:'.$col]=($visibility ? $visibility : COL_VISIBLE);
}

/* filter for inactive */
if(isset($hideInactive)){
	//update settings and environment
	q("REPLACE INTO bais_settings SET UserName='".sun()."', varnode='hideInactive$dataset',varkey='',varvalue='$hideInactive'");
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

#1. merge override settings
if($mergeAvailableCols[$datasetGroup][$modApType][$modApHandle]){
	if(!function_exists('array_merge_accurate'))require_once($FUNCTION_ROOT.'/function_array_merge_accurate_v100.php');
	$availableCols[$datasetGroup][$modApType][$modApHandle]=array_merge_accurate($availableCols[$datasetGroup][$modApType][$modApHandle], $mergeAvailableCols[$datasetGroup][$modApType][$modApHandle]);
}
#2 assign column order, AND set visibility
$visibleColCount=0;
foreach($availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'] as $n=>$v){
	if($v['colposition']>$maxcolposition)$maxcolposition=$v['colposition'];
	if($x=$_SESSION['userSettings'][$dataset.'ColVisibility:'.$n]){
		$availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'][$n]['visibility']=$x;
	}
	if($availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'][$n]['visibility']>=COL_VISIBLE)$visibleColCount++;
}
#3 clean-up for column order
foreach($availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'] as $n=>$v){
	if($v['colposition'])continue;
	$maxcolposition++;
	$availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'][$n]['colposition']=$maxcolposition;
}
#4 sort by column order
if(!function_exists('subkey_sort'))require($FUNCTION_ROOT.'/function_array_subkey_sort_v100.php');
$availableCols[$datasetGroup][$modApType][$modApHandle]['scheme']=subkey_sort($availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'],'colposition');

if(!$sort){
	foreach($availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'] as $sort){
		break;
	}
}
$orderBy=$availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'][$sort]['orderBy'];
if(!$orderBy)$orderBy=1;

$records=q(
	"SELECT $datasetFieldList FROM $datasetTable WHERE 1 ".
	($useStatusFilterOptions && count($inStatusSet) ? " AND Statuses_ID IN(".implode(',',$inStatusSet).")":'').
	($datasetActiveUsage==true ? " AND $datasetActive" : '').
	$filterQueries.
	" ORDER BY $orderBy $limit", O_ARRAY_ASSOC);
$recordCols=$qr['cols'];
if($componentRewrite){
	ob_start();
}
//-------------------- end generic coding --------------------
if(!$refreshComponentOnly){
	ob_start(); //--- buffer CSS ---
	?><style type="text/css">
	.colOptionsAnchor{
		width:15px;
		height:15px;
		cursor:pointer;
		background-image:url("/images/i/cols.gif");
		background-position:bottom left;
		background-repeat:no-repeat;
		}
	.colOptionsAnchor:hover{
		text-decoration:none;
		}
	.colOptVisible{
		padding-left:25px;
		background-image:url("/images/i/check1.png");
		background-position:0px 2px;
		background-repeat:no-repeat;
		}
	.colOptHidden{
		padding-left:25px;
		}
	</style>
	<?php
	echo $componentCSS=get_contents();
	ob_start(); //--- buffer JS ---
	?>
	<script type="text/javascript" language="javascript">
	function optionsDataset(){
		g('oh02').innerHTML=(hideInactive<?php echo $dataset?>?'Show inactive ':'Hide inactive ')+'<?php echo strtolower($datasetWordPlural);?>';
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
		ow('<?php echo $datasetFocusPage?>?cbFunction=refreshComponent&cbParam=fixed:<?php echo $datasetComponent;?>','l1_<?php echo strtolower($datasetWord);?>','700,700',true);
		return false;
	}
	function openMember(){
		for(var j in hl_grp['memopt'])j=j.replace('r_','');
		ow('<?php echo $datasetFocusPage?>?cbFunction=refreshComponent&cbParam=fixed:<?php echo $datasetComponent?>&Clients_ID='+j,'l1_<?php echo strtolower($datasetWord);?>','700,700');
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
	function colOptions(){
	}
	function mgeCol(e,n){
		var posn=g('col'+n).className.indexOf('Visible');
		window.open('resources/bais_01_exe.php?mode=refreshComponent&component=<?php echo $datasetComponent?>&col='+n+'&visibility='+(posn> -1? 8 : 16),'w2');
	}
	AssignMenu('^optionsMembers$', 'optionsMembersMenu');
	AssignMenu('^reportsMembers$', 'reportsMembersMenu');
	AssignMenu('^colOptions_<?php echo $dataset?>','optionsAvailableCols');

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
	<?php
	echo $componentJS=get_contents();
}
ob_start(); //--- buffer toolbar --
?>
<div id="componentToolbar_<?php echo $dataset?>" class="fr componentToolbar">
	<!-- toolbar buttons in divs -->
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
			require($COMPONENT_ROOT.'/comp_01_filtergadget_v104.php');
		}
		?>
	</div>
</div>
<!-- toolbar context menus here -->
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
</div>
<?php 
echo $componentToolbar=get_contents();
ob_start();
?>
<div id="<?php echo $datasetComponent?>" refreshparams="noparams">
	<?php
	ob_start(); //--- buffer heading ---
	?>
	<h3><?php echo $datasetWordPlural;?> (<span id="<?php echo $datasetComponent?>_count"><?php echo count($records);?></span>)</h3>
	<?php
	echo $componentHeading=get_contents();
	?>
	<div id="optionsAvailableCols" class="menuskin1" style="width:150px;" onmouseover="hlght2(event)" onmouseout="llght2(event)" onclick="executemenuie5(event)" precalculated="colOptions();">
		<?php
		foreach($availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'] as $n=>$v){
			if($v['visibility']<COL_AVAILABLE)continue;
			?><div id="col<?php echo $n?>" class="menuitems colOpt<?php echo $v['visibility']==COL_VISIBLE?'Visible':'Hidden'?>" command="mgeCol(event,'<?php echo $n?>');" status="Show or hide this column"><?php echo $v['header'] ? $v['header'] : $n;?></div><?php
		}
		?>
	</div>
	<input type="hidden" name="noparams" id="noparams" value="" />
	<table border="0" cellspacing="0" cellpadding="0" class="complexData" style="clear:both;">
		<?php ob_start(); //--- buffer thead --- ?>
		<thead>
			<tr>
				<!-- control cells -->
				<?php if(!$hideObjectInactiveControl){ ?>
				<th id="toggleActive" class="activetoggle"><a title="Hide or show inactive <?php echo strtolower($datasetWordPlural);?>" href="javascript:toggleActive('<?php echo $datasetComponent?>',hideInactive<?php echo $dataset?>);">&nbsp;&nbsp;</a></th>
				<?php } ?>
				<th>&nbsp;</th><?php
				//----------- column headers ----------------
				$cols=0;
				foreach($availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'] as $n=>$v){
					if($v['visibility']<COL_VISIBLE)continue;
					$cols++;
					?><th nowrap="nowrap" <?php echo $v['sortable'] || !isset($v['sortable']) ? 'sortable="1"' : ''?> class="<?php echo $sort==$n ? 'sorted':''?><?php echo $cols==$visibleColCount?' last':''?>"><?php if($v['sortable'] || !isset($v['sortable'])){ 
						//link tag for sort
						?><a href="resources/bais_01_exe.php?mode=refreshComponent&component=<?php echo $datasetComponent?>&sort=<?php echo $n?>&dir=<?php echo !$dir || ($sort==$n && $dir=='-1') ? 1 : -1?>" target="w2" title="<?php echo $v['title'];?>"><?php }?>
						<?php
						echo strlen($v['header']) ? $v['header'] : $n;
						?>
						<?php 
						//close link tag
						if($v['sortable'] || !isset($v['sortable'])){ ?></a><?php }
						if($cols==$visibleColCount){ 
							?><a id="colOptions_<?php echo $dataset?>" class="colOptionsAnchor" title="Select and organize columns for this view" href="javascript:;" onclick="hidemenuie5(event,1);showmenuie5(event,1)" oncontextmenu="return false;">&nbsp;&nbsp;</a><?php
						}
					?></th><?php
				}
				?>
			</tr>
		</thead>
		<?php 
		echo $componentThead=get_contents();
		ob_start(); //--- buffer tfoot ---
		?>
		<tfoot>
			<tr valign="top">
			<td colspan="100%"><a href="<?php echo $datasetFocusPage?>?cbFunction=refreshComponent&cbParam=fixed:<?php echo $datasetComponent?>" onClick="return add<?php echo $dataset?>();"><img src="../images/i/add_32x32.gif" width="32" height="32">&nbsp;Add <?php echo strtolower($datasetWord);?>..</a></td>
			</tr>
		</tfoot>
		<?php 
		echo $componentTfoot=get_contents();
		ob_start(); //--- buffer tbody ---
		?>
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
				<td id="r_<?php echo $ID?>_active" title="Make this <?php echo strtolower($datasetWord);?> <?php echo $Active ? 'in':''?>active" onclick="toggleActiveObject('<?php echo $dataset?>',<?php echo $ID?>,'<?php echo $datasetComponent?>');" class="activetoggle"><?php
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
				?>&nbsp;&nbsp;<a title="Edit this <?php echo strtolower($datasetWord);?> information" href="<?php echo $datasetFocusPage?>?Clients_ID=<?php echo $ID?>" onClick="return ow(this.href,'l1_<?php echo strtolower($datasetWord);?>','700,700');return false;"><img src="../images/i/edit2.gif" width="15" height="18" border="0"></a>&nbsp;</td>
				<?php
				//--------------- user columns coding added 2009-10-27 --------------
				$colPosition=0;
				foreach($availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'] as $handle=>$scheme){
					if($scheme['visibility']<COL_VISIBLE)continue;
					$colPosition++;
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
		<?php
		echo $componentTbody=get_contents();
		?>
	</table>
</div>
<?php
echo $componentDiv=get_contents();
if($submode=='exportDataset')ob_end_clean();
if($componentRewrite){
	$standardLayout=ob_get_contents();
	ob_end_clean();
}
?>