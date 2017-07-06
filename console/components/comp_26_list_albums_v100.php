<?php
/*
some things I'm implementing here
--------------------------------
std left controls
component style
best way to sort = just refreshComponent
callback on call edit and new

in focus view
-------------
quase resource
sub quasi resource :)
simple click to edit first like facebook/linkedin
add file widget


*/

if($submode=='exportDataset')ob_start(); //------- for handling CSV export ---------

$dataobject='albums'; //for filtergadget
$dataobjectTable='ss_albums';
$dataobjectTitle='Albums';

$dataset='Albums';

//sorting and direction
if($sort){
	q("REPLACE INTO bais_settings SET UserName='".$_SESSION['admin']['userName']."', vargroup='albums',varnode='defaultAlbumSort',varkey='',varvalue='$sort'");
	q("REPLACE INTO bais_settings SET UserName='".$_SESSION['admin']['userName']."', vargroup='$dataobject',varnode='defaultAlbumSortDirection',varkey='',varvalue='".($dir?$dir:1)."'");
	$_SESSION['userSettings']['defaultAlbumSort']=$sort;
	$_SESSION['userSettings']['defaultAlbumSortDirection']=($dir?$dir:1);
}else{
	$sort=$userSettings['defaultAlbumSort'];
	$dir=$userSettings['defaultAlbumSortDirection'];
}
$asc=($dir==-1?'DESC':'ASC');

//filter for inactive albums
$albumActive=($userSettings['hideInactiveAlbums']? ' AND Active=1' : '');


//2008-07-09: set where clause
$sqlQueries=array();
$filterQueries='';
if($filterOverride){
	if(!is_array($filterOverride))$filterOverride=array($filterOverride);
	foreach($filterOverride as $v){
		if($x=parse_query($v,$dataobjectTable)) $sqlQueries[]=$x;
	}
	if(count($sqlQueries))$filterQueries=' AND (' . implode( ' '.($filterQueryJoin ? $filterQueryJoin : 'AND').' ', $sqlQueries) . ')';
}else if(count($_SESSION['dataObjects']['filterQuery'][$dataobject])){
	foreach($_SESSION['dataObjects']['filterQuery'][$dataobject] as $v){
		if($x=parse_query($v,$dataobjectTable)) $sqlQueries[]=$x;
	}
	if(count($sqlQueries))$filterQueries=' AND (' . implode( ' '.$_SESSION['dataObjects']['filterQueryJoin'][$dataobject].' ', $sqlQueries) . ')';
}
$ids=array();
switch(true){
	case strtolower($sort)=='name':
		$ids=q("SELECT ID, 1 FROM $dataobjectTable
		WHERE ResourceType IS NOT NULL $filterQueries
		ORDER BY Name $asc, SKU $asc $limit", O_COL_ASSOC);
	break;
	case strtolower($sort)=='location':
		$ids=q("SELECT ID, 1 FROM $dataobjectTable
		WHERE ResourceType IS NOT NULL $filterQueries
		ORDER BY Location $asc $limit", O_COL_ASSOC);
	break;
	case strtolower($sort)=='picturecount':
		$ids=q("SELECT ID, 1 FROM $dataobjectTable
		WHERE ResourceType IS NOT NULL $filterQueries
		ORDER BY Count $asc $limit", O_COL_ASSOC);
	break;
	default:
		$ids=q("SELECT ID, 1 FROM $dataobjectTable
		WHERE ResourceType IS NOT NULL $filterQueries
		ORDER BY CreateDate $asc $limit", O_COL_ASSOC);
}
if(!$refreshComponentOnly){
	?><style type="text/css">
	/* generated with logical table generator 1.0 */
	table.data910{
		border-collapse:collapse;
		/*
		border-left:1px solid #ccc;
		border-right:1px solid #ccc;
		*/
		}
	.data910 td{
		/*border-bottom:1px dotted #333; */
		padding:1px 4px 0px 8px;
		cursor:pointer;
		}
	.data910 .alt{
		background-color:#eef;
		}
	.data910 th{
		text-align:left;
		}
	.data910 th{
		border:1px solid #fff;
		color:#fff;
		background-color:ORANGE;
		font-weight:400;
		padding:10px 4px 2px 8px;
		font-size:119%;
		}
	.data910 a{
		color:inherit;
		text-decoration:inherit;
		}
	h2.albumsList{
		font-size:119%;
		font-weight:400;
		}
	.data910 .tr{
		text-align:right;
		}
	</style>
	<script type="text/javascript" language="javascript">
	hl_bg['albumsListDataObject']='SILVER';
	hl_txt['albumsListDataObject']='WHITE';
	//declare the ogrp.handle.sort value even if blank
	ogrp['albumsListDataObject']=new Array();
	ogrp['albumsListDataObject']['sort']='';
	ogrp['albumsListDataObject']['rowId']='';
	ogrp['albumsListDataObject']['highlightGroup']='albumsListDataObject';
	AssignMenu('^ri(g|p)*_[0-9]+', 'albumsListOptionsA');
	
	function objectSort(o){
		showPending('sort');
		window.open(o.href,'w2');
		return false;
	}
	function showPending(region){
		g('hdr-ctrls').innerHTML='sorting..';
	}
	function precalcAlbumsList(evt){
		//get object
		var reg=/[^0-9]*/;
		for(j in hl_grp['albumsListDataObject'])trid=j;
		var IsDeletable=g(trid).getAttribute('isdeletable');
		var IsPackage=g(trid).getAttribute('ispackage');
	}
	function manage_albums(action){
		//get object
		var reg=/[^0-9]*/;
		for(j in hl_grp['albumsListDataObject'])trid=j;
		var IsDeletable=g(trid).getAttribute('isdeletable');
		var IsPackage=g(trid).getAttribute('ispackage');
		switch(action){
			case 'open':
				ow('albums.php?Albums_ID='+trid.replace(reg,'')+'&cbFunction=refreshList', (IsPackage>0?'l1_albumspackage':'l1_albums'), '700,700');
			break;
			case 'newalbum':
			case 'newpackage':
				ow('albums.php?cbFunction=refreshList' + (action=='newpackage'?'&IsPackage=1':''), (IsPackage>0?'l1_albumspackage':'l1_albums'), '700,700');
			break;
			case 'addalbum':
				ow('package_albums.php?Albums_ID='+trid.replace(reg,'')+'&view=list&cbFunction=refreshList&cbParam=fixed:albumonly' + (action=='newpackage'?'&IsPackage=1':''), 'l1_packagealbums','500,600');
			break;
			case 'qreport':
				alert('undeveloped');
			break;
			case 'exportcurrentcsv':
				alert('undeveloped');
			break;
			case 'exportcurrentiif':
				alert('undeveloped');
			break;
			case 'delete':
				if(!confirm('This cannot be reversed. Are you sure?'))return false;
				window.open('resources/bais_01_exe.php?mode=deleteAlbum&Albums_ID='+trid.replace(reg,''),'w2');
				g(trid).style.display='none';
			break;
		}
	}
	</script>
	<div id="albumsListOptionsA" class="menuskin1" style="z-index:1000;" onMouseOver="hlght2(event)" onMouseOut="llght2(event)" onClick="executemenuie5(event)" precalculated="precalcAlbumsList(event)">
		<div id="ilo1" class="menuitems" command="manage_albums('open');" status="Open the selected album" default="1" style="font-weight:900;">Open</div>
		<hr class="mhr"/>
		<div id="ilo2" class="menuitems" command="manage_albums('newalbum');" status="Add a new album to the database">New album..</div>
		<hr class="mhr"/>
		<div id="ilo6" class="menuitems" command="manage_albums('exportcurrentcsv');" status="Export the currently shown list of albums in CSV format">Export albums as CSV</div>
		<div id="ilo7" class="menuitems" command="manage_albums('exportcurrentiif');" status="Export the currently shown list of albums in IIF format">Export albums as IIF</div>
		<div id="ilo8" class="menuitems" command="manage_albums('delete');" status="Remove this album permanently">Delete</div>
	</div><?php
}
?>
<div id="listAlbums" refreshparams="noparams">
	<input type="hidden" name="noparams" id="noparams" value="" />

	<h2 id="albumCount" class="albumsList"><?php echo count($ids) ? count($ids) : 'No'?> albums showing</h2>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" id="albumsList" class="data910">
		<thead>
			<tr>
				<th id="hdr-ctrls" colspan="<?php echo $userSettings['hideInactive'.$dataset]?2:3?>">&nbsp;</th>
				<th>&nbsp;</th>
				<th>Created On</th>
				<th>Name</th>
				<th>Location</th>
				<th>Description</th>
				<th>Pictures</th>
			</tr>
		</thead>
		<tfoot class="footerClass">
		<tr>
			<td colspan="100%"><a title="Add a new album" onclick="return ow(this.href,'l1_albums','700,700',true);" href="albums.php">Add new album</a></td>
		</tr>
		</tfoot>
		<tbody id="albumsListTbody" <?php if($browser=='Moz')echo 'style="overflow:scroll;height:350px;"';?>>
		<?php
		if(count($ids)){
			foreach($ids as $ID=>$null){
				if($a=q("SELECT 
					a.Name, a.Description, a.Location, a.CreateDate, a.EditDate, COUNT(DISTINCT c.ID) AS Count
					FROM ss_albums a LEFT JOIN ss_AlbumsPictures b ON a.ID=b.Albums_ID LEFT JOIN ss_pictures c ON b.Pictures_ID=c.ID AND c.ResourceType IS NOT NULL
					WHERE a.ID=$ID", O_ROW)){
					//further filters here
					extract($a);
				}else{
					continue;
				}
				$i++;
				?><tr id="r_<?php echo $ID?>" onclick="h(this,'albumsListDataObject',0,0,event);" onDblClick="h(this,'albumsListDataObject',0,0,event);defaultMenuOption(event)" oncontextmenu="h(this,'albumsListDataObject',0,1,event);" class="<?php echo !fmod($i,2)?' alt':''?>">
				<?php if(!$userSettings['hideInactive'.$dataset]){ ?>
				<td id="r_<?php echo $ID?>_active" title="Make this record <?php echo $Active ? 'in':''?>active" onclick="toggleActiveObject(<?php echo $ID?>);" active="<?php echo $Active?>"><?php
				if(!$Active){
					?><img src="../images/i/garbage2.gif" width="18" height="21" align="absbottom" /><?php
				}else{
					?>&nbsp;<?php
				}
				?></td>
				<?php } ?>
				<td><a title="Delete this album" href="resources/bais_01_exe.php?mode=deleteAlbum&Albums_ID=<?php echo $ID?>" onclick="if(!confirm('This will permanently delete this album! Are you sure?'))return false; return ow(this.href,'w2','');" tabindex="-1"><img src="../images/i/del2.gif?>" /></a></td>
				<td><a title="Edit this album" href="albums.php?Albums_ID=<?php echo $ID?>" onClick="return ow(this.href,'l1_albums','700,700');"><img src="../images/i/edit2.gif" width="18" height="18" alt="edit" /></a></td>

				<td><?php
				//image here - create a thumbnail
				
				?></td>
				<td><?php echo date('F jS',strtotime($CreateDate));?></td>
				<td><?php echo $Name?></td>
				<td><?php echo $Location?></td>
				<td><?php echo $Description?></td>
				<td><?php echo $Count?></td>
	
				<td>&nbsp;&nbsp;</td>
				</tr>
				<?php
			}
		}
		?>
		</tbody>
	</table>
</div>
<?php
if($submode=='exportDataset')ob_end_clean();
?>