<?php
/*
---------------- FULL CSS AND JAVASCRIPT FILES NEEDED TO RUN A DATASET COMPONENT -------------------


-----------------------------------------------------------------------------------------------------

things that need updating on a dataset component
	add and remove columns from the array
	be able to write php code for the generic function
	be able to set attributes			|
	be able to remove attributes		|- ,, for these, do so without constant reposting
	..and edit attributes				|
	can we set the toggleParameter (default=Active)
	can we delete
	styling and highlighting
	focus view controls
	grouping and subtotals/totals
	prioritizing field
	
	registeredComponents created on the fly
	datasetHex then sent to a dataset component table vs the components folder
		
	do we show or hide the count and range shown
	* I want to be able to load the next range with an ani like on facebook - IN the row structure, and only if no more range left
	* have function php code be color coded
	* have the tabbed editor abs positioned over the dataset component and eventually free-floating



$Location - can be either a 32 char hex string or a file path

*/

//initial settings
$authorized=true;
$editing=true;
if(!$testLimit)$testLimit=50;

//------- tabs coding --------
$__tabs__['datasetCreator']['tabSet']=array(
	'Data Source'	=>'dcDataSource',
	'Columns'	=>'dcColumns',
	'Headers/Breaks'	=>'dcHeadersBreaks',
	'Controls'	=>'dcControls',
	'Appearance'	=>'dcAppearance',
	'Output'	=>'dcOutput',
	'Everything Else'	=>'dcMisc',
	'Help'	=>'dcHelp',
);
//refresh component coding
$refresh=preg_replace('/^&*Location=[^&]*/','',$QUERY_STRING);
$refresh.=($refresh?'&':'').'Location='.md5(time().rand(1,1000000));

$tables=q("SHOW TABLES",O_COL);
if($datasetTable=='{RBADDNEW}')unset($datasetTable);
$insertMode='insertDatasetComponent';
$updateMode='updateDatasetComponent';
$deleteMode='deleteDatasetComponent';


function dataset_sufficient_parameters(){
	global $Location, $datasetTable, $datasetQuery;
	//see if sufficient data to present the dataset
	if(preg_match('/^[a-f0-9]{32}$/i',$Location)){
		return ($datasetTable || $datasetQuery);
	}
	//for built datasets, presume OK
	return true;
}

if(!$refreshComponentOnly){
	?><style type="text/css">
	#showHideCreator{
		cursor:pointer;
		}
	.objectWrapper{
		background-color:lightsteelblue;
		}
	.datasetCol{
		border-bottom:1px solid #000;
		padding:10px;
		margin-bottom:10px;
		position:relative;
		}
	.datasetColTools{
		position:absolute;
		right:0px;
		top:0px;
		}
	</style>
	<script type="text/javascript" language="javascript">
	function UpdateFields(n){
		if(n=='{RBADDNEW}'){
			ow('https://75.125.10.34:2087/3rdparty/phpMyAdmin/tbl_create.php?db=<?php echo $MASTER_DATABASE?>&table='+prompt('Enter table name','')+'&num_fields='+prompt('Enter number of fields',''),'l1_addtable','700,700');
			return false;
		}
		window.open('/gf5/console/components/parkertest.php?mode=availableCols&table='+g('datasetTable').value, 'w2');
	}
	function showHide(o,n,showText,hideText){
		if(g(n).style.display=='none'){
			g(n).style.display='block';
			o.firstChild.src='/images/i/plusminus-minus.gif';
			o.firstChild.nextSibling.innerHTML=hideText;
		}else{
			g(n).style.display='none';
			o.firstChild.src='/images/i/plusminus-plus.gif';
			o.firstChild.nextSibling.innerHTML=showText;
		}
	}
	function showHideCreator(o){
		if(g('creator').style.display=='none'){
			g('creator').style.display='block';
			g('showHideCreatorImg').src='/images/i/plusminus-minus.gif';
			g('showHideText').innerHTML='hide creator';
		}else{
			g('creator').style.display='none';
			g('showHideCreatorImg').src='/images/i/plusminus-plus.gif';
			g('showHideText').innerHTML='show creator';
		}
	}
	function loadNewCols(){
		g('submode').value='loadNewCols';
		g('formDataset').submit();
		g('submode').value='';
		return false;
	}
	function manageCols(o,i,a){
		if(a=='delete' && !confirm('Are you sure you want to remove this column?'))return false;
		g('submode').value=a;
		g('idx').value=i;
		return false;
	}
	</script>
	<?php	
}


?><div id="datasetCreator"><?php
if($authorized && $editing){

	//process updates
	if($modePassed){
		for($i=1; $i<=1; $i++){ //------------------ break loop -------------------
		//first, merge the post with session vars
		if(!$_SESSION['special']['datasets'][$Location]){
			$_SESSION['special']['datasets'][$Location]=array( 'starttime'=>time(),	);
		}
		$_SESSION['special']['datasets'][$Location]=array_merge_accurate($_SESSION['special']['datasets'][$Location], stripslashes_deep($_POST));
		
		if($submode=='insertColumnBelow'){
			break;
		}
		if($submode=='deleteColumn'){
			break;
		}
		if($submode=='loadNewCols'){
			//we need one record in the dataset query to get this
			if($datasetTable){
				$a=q("SELECT * FROM $datasetTable LIMIT 1", O_ROW);
			}else if($datasetQuery){
				ob_start();
				$a=q(preg_replace('/LIMIT\s+[ ,0-9]+$/i','',stripslashes($datasetQuery)).' LIMIT 1', O_ROW, ERR_ECHO);
				$err=ob_get_contents();
				ob_end_clean();
				if($err) error_alert('your syntax is incorrect for the string dataset query; click the test link to test out the query');
			}else{
				error_alert('To create initial columns you need either a string query or table selected (Data Source tab)');
			}
			if(!$a)error_alert('Currently you need at least one record to be returned from you table or query to create initial columns');
			if(!$qr['output']){
				mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals($err='unable to get qr.output in building the initial columns for a dataset'),$fromHdrBugs);
				error_alert($err);
			}
			//unset previous availableCols
			unset($_SESSION['special']['datasets'][$Location]['availableCols'][$datasetGroup][$modApType][$modApHandle]['scheme']);
			foreach($qr['output'] as $n=>$v){
				$_SESSION['special']['datasets'][$Location]['availableCols'][$datasetGroup][$modApType][$modApHandle]['scheme'][$n]=array();
			}
			break;
		}
		} //--------------------- end break loop ------------------------
	}

	if($Location){
		if(preg_match('/^[a-f0-9]{32}$/i',$Location)){
			if(!$_SESSION['special']['datasets'][$Location]){
				$_SESSION['special']['datasets'][$Location]=array( 'starttime'=>time(),	);
			}
			$mode=$insertMode;
		/* NOTE this will eventually transition to pulling the codebase from a database table */
		}else if(@$a=file($Location)){
			//avoid calling precoding/comp out of the codebase
			$datasetFetchSettings=true;
			$i=0;
			foreach($a as $n=>$v){
				$i++;
				if(preg_match('/dataset_generic_precoding_v|dataset_component_v/',$v) && !strstr($v,'datasetFetchSettings')){
					$a[$n]='if(!$datasetFetchSettings)'.$v;
				}
			}
			eval('?>'.implode('',$a));
			
			$mode=$updateMode;
		}else{
			error_alert('Unable to find the referenced dataset component or resource '.$Location);
		}
		//note that the session array has been updated from the _POST in the modePassed section above
		@extract($_SESSION['special']['datasets'][$Location]);
		//dataset has now been live-modified
	}else{
		//create quasi dc
		$Location=md5(time().rand(1,100000));
		$_SESSION['special']['datasets'][$Location]=array( 'starttime'=>time(), );
		@extract(stripslashes_deep($_POST));
		$mode=$insertMode;
		//default form will now be presented
	}

	//2011-02-15: last step; for building the component as a physical file for now
	$datasetOutputHex='$datasetHex=\''.base64_encode(serialize($_SESSION['special']['datasets'][$Location]));
	
	//form with all variables
	?>
	<form id="formDataset" name="formDataset" target="w2" method="post" action="resources/bais_01_exe.php">
	<input type="hidden" name="Location" value="<?php echo h($Location);?>" id="Location" />
	<input type="hidden" name="modePassed" id="modePassed" value="<?php echo $mode?>" />
	<input type="hidden" name="submode" id="submode" value="" />
	<input type="hidden" name="idx" id="idx" value="" />
	<a href="<?php echo $thispage.'?'.$refresh?>">refresh/new component</a><br />
	Description: 
	<input name="datasetDescription" type="text" id="datasetDescription" value="<?php echo h($datasetDescription);?>" size="45" maxlength="255" />
	<br />
	Creator: 
	<input name="datasetAuthor" type="text" id="datasetAuthor" value="<?php echo $datasetAuthor ? h($datasetAuthor) : $_SESSION['firstName'] . ' ' . $_SESSION['lastName']?>" />
	<br />
	Contact email: 
	<input name="datasetEmail" type="text" id="datasetEmail" value="<?php echo $datasetEmail ? h($datasetEmail) : $_SESSION['email']?>" />
	<br />

	<span id="showHideCreator" onclick="showHideCreator(this);"><img src="/images/i/plusminus-minus.gif" id="showHideCreatorImg" /><span id="showHideText">hide creator</span></span>
	<div id="creator" class="objectWrapper">
	<?php
	//-------------------------- tabs ------------------------
	ob_start();
	?>
	<input name="rows_datasource" type="radio" value="tabular" <?php echo (!$datasetTable && !$datasetQuery) || $datasetTable ? 'checked':''?> />
	Tabular source of data: 
	<select id="datasetTable" name="datasetTable" onchange="UpdateFields(this.value);">
		<option value="">--Select--</option>
		<?php 
		foreach($tables as $v){
			?><option value="<?php echo $v?>" <?php echo $datasetTable==$v?'selected':''?>><?php echo $v;?></option><?php
		}
		?>
		<option value="{RBADDNEW}">&lt;Add new table..</option>
	</select>
	
	<label>Is this a view? <input type="checkbox" value="1" name="datasetTableIsView" /></label>
	<br />
	<input name="rows_datasource" type="radio" value="string" <?php echo $datasetQuery?'checked':''?> /> 
	String Query: [<a title="test this query" onclick="return ow(this.href+escape(g('datasetQuery').value),'l1_testquery','800,700');" href="http://relatebase-rfm.com:2086/3rdparty/phpMyAdmin/import.php?db=<?php echo $MASTER_DATABASE?>&table=addr_contacts&show_query=1&token=10d89ed8d5e21128ad702281edf6ac57&sql_query=">test</a>]<br />
	<textarea name="datasetQuery" cols="60" rows="10" id="datasetQuery"><?php echo h($datasetQuery);?></textarea>
	<p>
	Dataset Delete Mode : <input name="datasetDeleteMode" type="text" id="datasetDeleteMode" value="<?php echo $datasetDeleteMode?>" />
	Dataset Field List : <input name="datasetFieldList" type="text" id="datasetFieldList" value="<?php echo $datasetFieldList?>" />
	</p>

	<p>
	Dataset Handle : <input name="dataset" type="text" id="dataset" value="<?php echo $dataset;?>" /><br />
	Component Handle : <input name="datasetComponent" type="text" id="datasetComponent" value="<?php echo $datasetComponent;?>" />	
	</p>
	<p>
	Name Of Dataset : <input name="datasetWord" type="text" id="datasetWord" value="<?php echo $datasetWord;?>" /><br />
	Plural Name Of Dataset : <input name="datesetWordPlural" type="text" id="datasetWordPlural" value="<?php echo $datasetWordPlural?>"/>
	</p>
	<p>
	Dataset Focus Page : <input name="datasetFocusPage" type="text" id="datasetFocusPage" value="<?php echo $datasetFocusPage?>" />
	Dataset Query Key Field : <input name="datasetQueryStringKey" type="text" id="datasetQueryStringKey" value="<?php echo $datasetQueryStringKey?>" /><br />
	</p>
	<p>
	Where clause filter : <input type="text" name="datasetInternalFilter" id="datasetInternalFilter" value="<?php echo $datasetInternalFilter?>" /><br />
	Batch Threshold : <input type="text" name="globalBatchThreshold" id="globalBatchThreshold" value="<?php echo $globalBatchThreshold?>" /><br />
	Scrolling Threshold : <input type="text" name="tbodyScrollingThreshold" id="tbodyScrollingThreshold" value="<?php echo $tbodyScrollingThreshold?>" /><br />
	
	</p>
	<p>
	Dataset Array Type : 
	<select name="datasetArrayType">
		<option value="<?php echo O_ARRAY_ASSOC?>" <?php echo $datasetArrayType==O_ARRAY_ASSOC?'selected':''?>>Associative Array</option>
		<option value="<?php echo O_ARRAY?>" <?php echo $datasetArrayType==O_ARRAY?'selected':''?>>Array</option>
		<option value="<?php echo O_ROW?>" <?php echo $datasetArrayType==O_ROW?'selected':''?>>Row</option>
		<option value="<?php echo O_COL?>" <?php echo $datasetArrayType==O_COL?'selected':''?>>Column</option>
	</select>
	<input type="hidden" id="zeroVal" value="1" />	
	</p>
	<?php 
	//------------------------------- tab 1 ---------------------------------
	get_contents_tabsection('dcDataSource');
	?>
	<div style="height:500px; overflow:scroll; border:1px solid #000; background-color:white; padding:5px;">
	<?php
	prn($Location);
	$i=0;
	if($a=$availableCols[$datasetGroup][$modApType][$modApHandle]['scheme']){
		foreach($a as $n=>$v){
			$i++;
			$v['key']=$n;
			?>
		<div id="col_<?php echo $i?>" class="datasetCol">
			<div class="datasetColTools">
				<a href="#" onclick="return manageCols(this,<?php echo $i?>,'insertColumnBelow');" title="insert a column below this one"><div style="float:left;"><img src="/images/i/antset.insertbelow.png" width="28" height="28" /></div></a>
				&nbsp;&nbsp;
				<a href="#" onclick="return manageCols(this,<?php echo $i?>,'delete');" title="delete this column"><div style="float:left;"><img src="/images/i/antset.delete.png" width="28" height="28" /></div></a>
			</div>
			Column name: 
		  <input name="availableCols[<?php echo $i?>][key]" type="text" id="availableCols[<?php echo $i?>][key]" value="<?php echo h($v['key']);?>" />
		  <br />
		  Show column as: 
		  <select name="aCols[<?php echo $i?>][fieldExpressionFunction]" id="aCols[<?php echo $i?>][fieldExpressionFunction]">
			<option <?php echo $v['method']=='field'?'selected':''?> value="field">Single Field</option>
			<option <?php echo $v['method']=='expression'?'selected':''?> value="expression">SQL Expression</option>
			<option <?php echo $v['method']=='function'?'selected':''?> value="function">PHP Code</option>
		  </select>
		  <br />
		  Code to evaluate:<br />
		  <textarea name="aCols[<?php echo $i?>][formula]" cols="45" rows="3" id="aCols[<?php echo $i?>][formula]"><?php echo h($v['formula']);?></textarea>
		  <br />
			</div>
		<?php
		}
	}else{
		?><div id="noCols">
		No columns declared<br />
		<a title="create initial availableCols array" onclick="<?php if($availableCols){ ?>if(!confirm('This will reset the list of columns and use the &quot;natural&quot; columns of the query.  Use remove and insert columns if you wish to modify the existing columns.  Continue?'))return false; <?php } ?>return loadNewCols();" href="#">create initial columns</a>		</div>
		<?php
	}
	?>
	<span style="cursor:pointer;" onclick="showHide(this, 'rawAvailableCols', 'show raw coding..','hide raw coding..');"><img src="/images/i/plusminus-plus.gif" style="padding-right:7px;" /><span>show raw coding..</span></span>
	
	<div id="rawAvailableCols" style="display:none;">
	<?php
	if($a)prn($a);
	?>
	</div>
	</div>
	<?php 
	//------------------------------- tab 1b  ---------------------------------
	get_contents_tabsection('dcColumns');
	?>
	
	<?php 
	//------------------------------- tab 2 ---------------------------------
	get_contents_tabsection('dcHeadersBreaks');
	?>
	
	<?php 
	//------------------------------- tab 3 ---------------------------------
	get_contents_tabsection('dcControls');
	?>
	Style of Output: 
	<select name="datasetTheme">
		<option value="Report" <?php echo strtolower($datasetTheme)=='report'?'selected':'';?>>Report</option>
		<option value="" <?php echo $datasetTheme==''?'selected':'';?>>Table</option>
	</select>
	
	<?php 
	//------------------------------- tab 4 ---------------------------------
	get_contents_tabsection('dcAppearance');
	?>
	coding output only visible after update button submitted<br />
	<textarea name="datasetOutputHex" cols="50" rows="4" id="datasetOutputHex"><?php echo h($datasetOutputHex);?></textarea><br />
	
	<?php 
	//------------------------------- tab 4b ---------------------------------
	get_contents_tabsection('dcOutput');
	?>
	
	<?php 
	//------------------------------- tab 5 ---------------------------------
	get_contents_tabsection('dcMisc');
	?>
	
	<?php 
	//------------------------------- tab 6 ---------------------------------
	get_contents_tabsection('dcHelp');
	?>
	
	<?php
	//----------------------- compile tabs --------------------------
	$tabWidth=700;
	require($MASTER_COMPONENT_ROOT.'/comp_tabs_v220.php');
	?>
	</div>
	<br />
	
	<div id="availableCols">
	</div>
	<?php 
	/*
	$datasetAddObjectJSFunction='ow(this.href,\'l1_employees\',\'800,700\',true);'; //this is because opening an object is not well developed yet
	$modApType='embedded';
	$modApHandle='first';
	$datasetActiveUsage=false;
	$hideColumnSelection=false; //however, we need to show column selection still
	$footerDisposition='tabularControls'; //however, the footer needs to show to nav a large batch like this
	$datasetHideFooterAddLink=true;
	$hideColumnSelection=false;
	$datasetShowDeletion=false; //no deletion needed on report
	$datasetShowBreaks=true;
	$datasetBreakFields=array(
		1=>array(
			'column'=>'Week',
			'blank'=>'not specified',
			'header'=>'Week #',
		),
		2=>array(
			'column'=>'Employee_ID',
			'blank'=>'not specified',
			'header'=>'Employee',
		)
	);
	*/
	?>
	<p>
	<input type="submit" name="Submit" value=" Update ">
	</p>
	</form>
	
	<?php
	//error checking if variables are sufficient to create dataset
}
if(!$editing || dataset_sufficient_parameters()){
	//do it
	//to show the dataset
	$datasetQueryValidation=md5($MASTER_PASSWORD);
	
	require($MASTER_COMPONENT_ROOT.'/dataset_generic_precoding_v103test.php');
	require($MASTER_COMPONENT_ROOT.'/dataset_component_v123test.php');
}
?></div><?php


if($modePassed){
	?><script language="javascript" type="text/javascript">
	window.parent.g('datasetCreator').innerHTML=document.getElementById('datasetCreator').innerHTML;
	</script><?php
}

?>