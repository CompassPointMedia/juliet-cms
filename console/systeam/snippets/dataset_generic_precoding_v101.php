<?php
/*
2010-04-03 - v1.01
* changed variable sqlQueries (an internal value) over to filterExpressions - much better term
* see logic below for filters, simplified but still allowed flexibility.  No effect on anything external

2010-03-07
* this snippet produces no output except a javascript if needed (see below) but sets variables needed for the dataset component 
* added batch setting storage as position,batch,batches where batches=number of batches present

* passed variable inventory
	datasetGroup
	dataset
	datasetTable
	moduleConfig['dataobjects'][dataset] - where is this declared ??
	[userSettings] - from bais_settings table
	dir
	sort
	limit
	batch
	col
	hideInactive
	filterOverride
	filterExpressions

	updateDatasetFilters
	useStatusFilterOptions
	statusFilterIDField
	statusFilterNameField
	statusFilterTable 
	statusFilterQueryWhere
	* filterGadgetUserName
	statusWord
	
	availableCols.datasetGroup.modApType.modApHandle
		
	
 ---- */

//this is stored in rbase_AccountModules.Settings for the Account (e.g. cpm103)
@extract($moduleConfig['dataobjects'][$dataset]);

if(!isset($datasetActiveActiveExpression))$datasetActiveActiveExpression='Active=1';
if(!isset($datasetActiveInactiveExpression))$datasetActiveInactiveExpression='Active=0';
if(!isset($datasetActiveAllExpression))$datasetActiveAllExpression='1';
if(!isset($globalBatchThreshold))$globalBatchThreshold=50;
if(!isset($showDeletion))$showDeletion=true;
if(!isset($allowBatching))$allowBatching=true;

define('QEHANDLE_CONTINUE',4);

//handle sort
if($sort){
	q("REPLACE INTO bais_settings SET UserName='".($_SESSION['systemUserName'] ? $_SESSION['systemUserName'] : $GLOBALS['PHP_AUTH_USER'])."', 
	vargroup='".$dataset."',varnode='default".$dataset."Sort',varkey='',varvalue='$sort'");
	q("REPLACE INTO bais_settings SET UserName='".($_SESSION['systemUserName'] ? $_SESSION['systemUserName'] : $GLOBALS['PHP_AUTH_USER'])."', 
	vargroup='".$dataset."',varnode='default".$dataset."SortDirection',varkey='',varvalue='".($dir?$dir:1)."'");
	$_SESSION['userSettings']['default'.$dataset.'Sort']=$sort;
	$_SESSION['userSettings']['default'.$dataset.'SortDirection']=($dir?$dir:1);
}else if($sort=$userSettings['default'.$dataset.'Sort']){
	$dir=( $userSettings['default'.$dataset.'SortDirection'] ? $userSettings['default'.$dataset.'SortDirection'] : 1);
	q("REPLACE INTO bais_settings SET UserName='".($_SESSION['systemUserName'] ? $_SESSION['systemUserName'] : $GLOBALS['PHP_AUTH_USER'])."', 
	vargroup='".$dataset."',varnode='cols".$dataset."Sort',varkey='',varvalue='$sort'");
}

//handle sort direction
$asc=($dir==-1?'DESC':'ASC');

//handle batch -> limit
$defaultBatch=$globalBatchThreshold;
if(preg_match('/^([0-9]+),([0-9]+)(,([0-9]+))$/',$limit,$a)){
	//this is a passed parameter and is not permanently stored
	$limitClause='LIMIT '.($a[1]-1).', '.($a[2] * ($a[3]?$a[3]:1));
	$position=$a[1];
	$currentRecordset=$a[2] * ($a[3]?$a[3]:1);
	$currentBatch=$a[2];
	$batches=($a[3]?$a[3]:1);
}else{
	unset($limitClause);
	if($batch=='0,0,0'){
		q("DELETE FROM bais_settings WHERE UserName='".($_SESSION['systemUserName'] ? $_SESSION['systemUserName'] : $GLOBALS['PHP_AUTH_USER'])."' AND 
		vargroup='".$dataset."' AND varnode='default".$dataset."Batch' AND varkey='' AND varvalue='$batch'");
		unset($_SESSION['userSettings']['default'.$dataset.'Batch']);
		unset($batch,$position,$currentRecordset);
	}else if($batch){
		q("REPLACE INTO bais_settings SET UserName='".($_SESSION['systemUserName'] ? $_SESSION['systemUserName'] : $GLOBALS['PHP_AUTH_USER'])."', 
		vargroup='".$dataset."',varnode='default".$dataset."Batch',varkey='',varvalue='$batch'");
		$_SESSION['userSettings']['default'.$dataset.'Batch']=$batch;
	}else if($batch=$userSettings['default'.$dataset.'Batch']){
		q("REPLACE INTO bais_settings SET UserName='".($_SESSION['systemUserName'] ? $_SESSION['systemUserName'] : $GLOBALS['PHP_AUTH_USER'])."', 
		vargroup='".$dataset."',varnode='default".$dataset."Batch',varkey='',varvalue='$batch'");
	}else{
		//batch is indeterminate, we may still batch based on size of recordset
	}
	if($batch){
		$a=explode(',',$batch);
		$limitClause='LIMIT '.($a[0]-1).', '.($a[1] * ($a[2]?$a[2]:1));
		$position=$a[0];
		$currentRecordset=$a[1] * ($a[2]?$a[2]:1);
		$currentBatch=$a[1];
		$batches=($a[2]?$a[2]:1);
	}
}

//handle column selection
if($col){
	q("REPLACE INTO bais_settings SET UserName='".($_SESSION['systemUserName'] ? $_SESSION['systemUserName'] : $GLOBALS['PHP_AUTH_USER'])."', 
	vargroup='".$dataset."',varnode='".$dataset."ColVisibility',varkey='".$col."',varvalue='".($visibility ? $visibility : COL_VISIBLE)."'");
	$_SESSION['userSettings'][$dataset.'ColVisibility:'.$col]=($visibility ? $visibility : COL_VISIBLE);
}

//filter for inactive
if(isset($hideInactive)){
	//update settings and environment
	q("REPLACE INTO bais_settings SET UserName='".($_SESSION['systemUserName'] ? $_SESSION['systemUserName'] : $GLOBALS['PHP_AUTH_USER'])."', varnode='hideInactive$dataset',varkey='',varvalue='$hideInactive'");
	$_SESSION['userSettings']['hideInactive'.$dataset]=$hideInactive;
	if($submode=='exportDataset'){
		?><script language="javascript" type="text/javascript">
		hideInactive<?php echo $dataset?>=<?php echo $hideInactive?>;
		window.parent.hideInactive<?php echo $dataset?>=<?php echo $hideInactive?>;
		</script><?php	
	}
}
$datasetActive = ( $userSettings['hideInactive'.$dataset]==1 ? $datasetActiveActiveExpression : ( $userSettings['hideInactive'.$dataset]==-1 ? $datasetActiveInactiveExpression : $datasetActiveAllExpression ));

//set where clause including statuses and filter pairs
/*
1. set temporary query:
	a. pass page.php?filterOverride=Region='Duluth'&Position='Rep' (must be urlencoded)
	b. pass page.php?filters[]=Region='Duluth'&filters[]=Position='Rep'
	c. to OVERRIDE session filters, just pass filterOverride=''
2. set permanent query: pass updateDatasetFilters=1, pass either filters[] (querystring) or querytext[] (from filter gadget)
3. clear query: just pass updateDatasetFilters=1, with no other variables; session filter queries will be unset



*/
unset($filterQuery, $filterExpressions, $validFilterExpressions);
if(isset($filterOverride)){
	//temporary, query_string-based method to filter the data (long string method)
	$filterQuery=$filterOverride;
}else if($updateDatasetFilters || count($filters) || count($querytext)){
	//form post only from filter gadget - passing set of fields "querytext"
	//or, collection of $filters in query string
	$filterExpressions=(count($filters) ? $filters : $querytext);
	if(count($filterExpressions)){
		foreach($filterExpressions as $v){
			if(!trim($v))continue;
			if(!($x=parse_query(stripslashes($v),$datasetQuery ? $datasetQuery : $datasetTable))){
				if($datasetQueryErrorHandling==QEHANDLE_CONTINUE){
					continue;
				}else{
					error_alert($err='Your query "' . str_replace("'","\\\'",$v) . '" is not understood');
				}
			}
			$validFilterExpressions[]=stripslashes($x);
		}
	}
	if(count($validFilterExpressions)) $filterQuery=' AND (' . implode( (strtolower($_SESSION['special']['filterQueryJoin'][$dataset])=='or' ? ' OR ' : ' AND '), $validFilterExpressions) . ')';
	if($updateDatasetFilters){
		//this resets them
		$_SESSION['special']['filterQuery'][$dataset]=$validFilterExpressions;
		$_SESSION['special']['filterQueryJoin'][$dataset]=$joinInclusive;
	}
}else if(count($_SESSION['special']['filterQuery'][$dataset])){
	foreach($_SESSION['special']['filterQuery'][$dataset] as $v){
		//double-verify this
		if($x=parse_query($v,$datasetQuery ? $datasetQuery : $datasetTable)) $validFilterExpressions[]=$x;
	}
	if($validFilterExpressions) $filterQuery=' AND (' . implode( (strtolower($_SESSION['special']['filterQueryJoin'][$dataset])=='or' ? ' OR ' : ' AND '), $validFilterExpressions) . ')';
}


//status filter options - best place to put this that I can figure
if($updateDatasetFilters && $useStatusFilterOptions){
	if(!count($Statuses_ID))error_alert('select at least one '.($statusWord ? strtolower($statusWord) : 'status'));
	foreach(q("SELECT $statusFilterIDField, $statusFilterNameField FROM $statusFilterTable WHERE $statusFilterQueryWhere $statusFilterQueryOrder", O_COL_ASSOC) as $n=>$v){
		q("REPLACE INTO bais_settings SET UserName='".($filterGadgetUserName ? $filterGadgetUserName : ($_SESSION['systemUserName'] ? $_SESSION['systemUserName'] : $GLOBALS['PHP_AUTH_USER']))."', vargroup='$dataset', varnode='filter".$dataset."Status', varkey=$n, varvalue=".(in_array($n,$Statuses_ID)?1:0));
		$_SESSION['userSettings']['filter'.$dataset.'Status:'.$n]=(in_array($n,$Statuses_ID)?1:0);
	}
}

//if($test)prn($mergeAvailableCols[$datasetGroup][$modApType][$modApHandle]['scheme']);

#1. merge override settings
if($mergeAvailableCols[$datasetGroup][$modApType][$modApHandle]){
	if(!function_exists('array_merge_accurate'))require_once($FUNCTION_ROOT.'/function_array_merge_accurate_v100.php');
	$availableCols[$datasetGroup][$modApType][$modApHandle]=array_merge_accurate($availableCols[$datasetGroup][$modApType][$modApHandle], $mergeAvailableCols[$datasetGroup][$modApType][$modApHandle]);
}
//if($test)prn($availableCols[$datasetGroup][$modApType][$modApHandle]['scheme']);
#2 assign column order, AND set visibility
$visibleColCount=0;
#prn('after');
#prn($_SESSION['userSettings']);
foreach($availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'] as $n=>$v){
	if($v['colposition']>$maxcolposition)$maxcolposition=$v['colposition'];
	if(isset($_SESSION['userSettings'][$dataset.'ColVisibility:'.$n])){
		$availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'][$n]['visibility']=$_SESSION['userSettings'][$dataset.'ColVisibility:'.$n];
	}
	if(
		!isset($availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'][$n]['visibility']) || 
		$availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'][$n]['visibility']>=COL_VISIBLE){
		$visibleColCount++;
	}
}
#prn($visibleColCount);
//prn($availableCols);
//error_alert(3);

#3 clean-up for column order
$maxcolposition=0;
foreach($availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'] as $n=>$v){
	if($v['colposition'])continue;
	$maxcolposition++;
	$availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'][$n]['colposition']=$maxcolposition;
}

#4 sort by column order
if(!function_exists('subkey_sort'))require($FUNCTION_ROOT.'/function_array_subkey_sort_v300.php');
$availableCols[$datasetGroup][$modApType][$modApHandle]['scheme']=subkey_sort($availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'],'colposition');

if(!$sort){
	foreach($availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'] as $set=>$v){
		if($v['visibility']>=COL_AVAILABLE){
			$sort=$set;
			break;
		}
	}
}
//if the orderBy variable is not there, we presume the array key is a sortable field name or expression
@eval('$orderBy="'.($availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'][$sort]['orderBy'] ? $availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'][$sort]['orderBy'] : $sort).'";');
if(!$orderBy)$orderBy=1;
if(!preg_match('/\b(ASC|DESC)\b/i',$orderBy))$orderBy.=' '.$asc;

//run the query
$records=q(
	($datasetQuery ? $datasetQuery : "SELECT $datasetFieldList FROM $datasetTable WHERE 1 ").
	($useStatusFilterOptions && count($inStatusSet) ? " AND Statuses_ID IN(".implode(',',$inStatusSet).")":'').
	($datasetActiveUsage==true ? " AND $datasetActive" : '').
	$filterQuery.
	" ORDER BY $orderBy $limitClause", O_ARRAY_ASSOC, ERR_ECHO
);
$recordCols=$qr['cols'];
//get count
if($limitClause){
	$count=q(
		($datasetQuery ? preg_replace('/\bSELECT\b(.|\s)+\bFROM\b/i','SELECT COUNT(*) FROM ',$datasetQuery) : "SELECT COUNT(*) FROM $datasetTable WHERE 1 ").
		($useStatusFilterOptions && count($inStatusSet) ? " AND Statuses_ID IN(".implode(',',$inStatusSet).")":'').
		($datasetActiveUsage==true ? " AND $datasetActive" : '').
		$filterQuery, O_VALUE, ERR_ECHO
	);
	$recordsetIsRelative=!($count==count($records));
}else{
	$count=count($records);
	$recordsetIsRelative=false;
}
//logic to trigger batching
if($limitClause){
	//OK- we have position, currentBatch, currentRecordset and batches
}else if($allowBatching && $count > $globalBatchThreshold){
	$inBatching=true;
	//we have the query below the batch, how am I going to do this
	$position=1;
	$currentBatch=$globalBatchThreshold;
	$currentRecordset=$globalBatchThreshold;
	$batches=1;
}
$navStats[$dataset]=get_navstats(
	$count,
	$position ? $position : 1, 
	$currentBatch, 
	($batches?$batches:1)
);
?>