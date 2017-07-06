<?php
$dataset='Member';
$datasetComponent='memberList';
$datasetGroup=$dataset; //Member
$datasetWord='Member';
$datasetWordPlural='Clients';
$datasetFocusPage='members.php';
//$datasetArchitecture=NULL; not used
if(!$datasetTable)$datasetTable='_v_contacts_generic_20090901_v103';
$datasetTableIsView=true;
$datasetActiveUsage=true;
$datasetFieldList='*';
$modApType='embedded';
$modApHandle='first';
/* 			-------------- added 2009-10-26 --------------			*/

//so, this completely declares what is available for the layout; see scheme below
/*
i.e. embedded means, part of the programs; user really has no access to this now 
i.e. first means, what I'm nicknaming this available columns set
*/
$availableCols[$datasetGroup][$modApType][$modApHandle]=array(
	'version'=>1.0,
	'description'=>'This is the first array implementation of relatebase_views and _views_items',
	'scheme'=>array(
		/*list these in order they would normally appear; analogous to Tbird's list of all inbox cols available */
		'RepCode'=>array(
			'method'=>'field',
			'fieldExpressionFunction'=>'RepCode',
			'header'=>'Rep',
			'orderBy'=>'RepCode $asc, LastName $asc, FirstName $asc',
			'visibility'=>COL_VISIBLE,
			'colposition'=>1
		),
		'StatusHandle'=>array(
			'method'=>'field', /* the default */
			'fieldExpressionFunction'=>'StatusHandle', /* the default */
			'format'=>'default', /* use field attributes themselves*/
			'datatype'=>'text', /* could be email, phone number, URL, link, popup - conflicts possible */
			'sortable'=>true, /* the default */
			'sortTitle'=>'Sort by member status',
			'header'=>'Status',
			/* this called AFTER $sort and $asc present but before the query, for sort=Status */
			'orderBy'=>'StatusHandle $asc, /* extra stuff is nice :) */ LastName $asc, FirstName $asc',
			/* ------- etc., etc., etc. -------- */
			'colorCoding'=>NULL,
			'visibility'=>COL_VISIBLE,
			'colposition'=>2
		),
		'CreateDate'=>array(
			'method'=>'function',
			'fieldExpressionFunction'=>'member_cols(\'CreateDate\')',
			'sortable'=>true,
			'sortTitle'=>'Sort by record creation date',
			'header'=>'Created',
			'orderBy'=>'CreateDate $asc',
			'nowrap'=>true,
			'visibility'=>COL_VISIBLE,
			'colposition'=>3
		),
		'CompanyName'=>array(
			'header'=>'Company',
			'orderBy'=>'CompanyName $asc, LastName $asc, FirstName $asc',
			'visibility'=>COL_VISIBLE,
			'colposition'=>4
		),
		'Name'=>array(
			'method'=>'function',
			'fieldExpressionFunction'=>'h($LastName.\', \'.$FirstName)',
			'datatype'=>'name', /* not used yet :) */
			'format'=>'LNFN',
			'orderBy'=>'LastName $asc, FirstName $asc',
			'visibility'=>COL_VISIBLE,
			'colposition'=>5
		),
		'Email'=>array(
			'datatype'=>'email',
			'orderBy'=>'Email $asc, LastName $asc, FirstName $asc',
			'visibility'=>COL_VISIBLE,
			'colposition'=>6
		),
		'Phones'=>array(
			'method'=>'function',
			'fieldExpressionFunction'=>'member_cols(\'Phones\')',
			'sortable'=>false,
			'visibility'=>COL_VISIBLE,
			'colposition'=>7
		),
		'BusinessAddress'=>array(
			'method'=>'function',
			'header'=>'Bus. Address',
			'fieldExpressionFunction'=>'member_cols(\'BusinessAddress\')',
			'sortable'=>true,
			'orderBy'=>'BusinessCountry $asc, BusinessState $asc, BusinessCity $asc, BusinessAddress $asc',
			'visibility'=>COL_VISIBLE,
			'colposition'=>8
		),
		'HomeAddress'=>array(
			'method'=>'function',
			'header'=>'Home Address',
			'fieldExpressionFunction'=>'member_cols(\'HomeAddress\')',
			'sortable'=>true,
			'orderBy'=>'HomeCountry $asc, HomeState $asc, HomeCity $asc, HomeAddress $asc',
			'visibility'=>COL_VISIBLE,
			'colposition'=>9
		),
		'ClientAddress'=>array(
			'method'=>'function',
			'header'=>'Company Address',
			'fieldExpressionFunction'=>'member_cols(\'ClientAddress\')',
			'sortable'=>true,
			'orderBy'=>'ClientCountry $asc, ClientState $asc, ClientCity $asc, ClientAddress $asc',
			'visibility'=>COL_VISIBLE,
			'colposition'=>10
		)
	)
);
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
if(!isset($qx['useRemediation']))$qx['useRemediation']=true;
$qx['tableList']=array_merge($qx['tableList'], array('addr_contacts','finan_clients','finan_clients_statuses','finan_ClientsContacts','finan_ClientsCategories'));
$hideObjectInactiveControl=false;

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
			case $cell=='ClientAddress':
				$str.=$record['Address1'].($colPosition==$visibleColCount?'&nbsp;&nbsp;&nbsp;':'').'<br />';
				if(trim($record['Address2']))$str.=$record['Address2'].($colPosition==$visibleColCount?'&nbsp;&nbsp;&nbsp;':'').'<br />';
				$str.=$record['City'].', '.$record['State'].'&nbsp;&nbsp;'.$record['Zip'];
				$str.=(strtolower($record['Country'])!=='us' && strtolower($record['Country'])!=='usa' ? '&nbsp;&nbsp;'.$record['Country'] : '').($colPosition==$visibleColCount?'&nbsp;&nbsp;&nbsp;':'');
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

require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/snippets/dataset_generic_precoding_v100.php');

if(!$refreshComponentOnly){
	ob_start(); //--- buffer CSS ---
	?><style type="text/css">
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
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/snippets/dataset_component_v100.php');
?>