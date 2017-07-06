<?php
/*
2009-06-03
* This was imported over from FOG/KFD (1.02) and made completely generic; note the settings declared below
* submission passes vars dataset and datasetTable which are needed when building the query filters for refresh
* component receives itself and does its own updating

*/

if(!$dataset)$dataset='Member'; //replaces all instances of this word; e.g. Member, Item, Article, Calevent or Calendar
if(!$datasetTable)$datasetTable='addr_contacts';

//statuses of the dataset - originally derived from finan_clients_statuses for client dataset
if(!isset($useStatusFilterOptions))$useStatusFilterOptions=true;
if(!$statusWord)$statusWord='Status';
if(!$statusFilterIDField)$statusFilterIDField='ID';
if(!$statusFilterNameField)$statusFilterNameField='Name';
if(!$statusFilterTable)$statusFilterTable='finan_clients_statuses';
if(!$statusFilterQueryWhere)$statusFilterQueryWhere='1';
if(!$statusFilterQueryOrder)$statusFilterQueryOrder='ORDER BY ID';
if(!$statusFilterDefaultShown)$statusFilterDefaultShown=2; //e.g. top two statuses


//component receives its own mode - presume we are in an exe page
if($mode==($filterGadgetMode ? $filterGadgetMode : 'updateDatasetFilters')){
	/*
	2009-06-03 list of variables being used:
	---------------------------------------
	$mode='updateDatasetFilters'
	$dataset
	$datasetTable
	$statusFilterIDField
	$statusFilterNameField
	$statusFilterTable 
	$statusFilterQueryWhere
	$useStatusFilterOptions
	*/
	//identify dataset
	if(!$dataset)error_alert('No dataset passed');
	if(!$datasetTable)error_alert('No dataset table passed');
	if($useStatusFilterOptions){
		if(!count($Statuses_ID))error_alert('select at least one '.($statusWord ? strtolower($statusWord) : 'status'));
		foreach(q("SELECT $statusFilterIDField, $statusFilterNameField FROM $statusFilterTable WHERE $statusFilterQueryWhere $statusFilterQueryOrder", O_COL_ASSOC) as $n=>$v){
			q("REPLACE INTO bais_settings SET UserName='".($filterGadgetUserName ? $filterGadgetUserName : sun()."', vargroup='$dataset', varnode='filter".$dataset."Status', varkey=$n, varvalue=".(in_array($n,$Statuses_ID)?1:0));
			$_SESSION['userSettings']['filter'.$dataset.'Status:'.$n]=(in_array($n,$Statuses_ID)?1:0);
		}
	}
	$_SESSION['special']['filterQuery'][$dataset]=array();
	unset($sqlQueries);
	if(count($querytext)){
		foreach($querytext as $v){
			if(!trim($v))continue;
			if(!($x=parse_query(stripslashes($v),$datasetTable)))error_alert('Your query "' . str_replace("'","\\\'",$v) . '" is not understood');
			$_SESSION['special']['filterQuery'][$dataset][]=stripslashes($v);
			$sqlQueries[]=$x;
		}
	}
	$_SESSION['special']['filterQueryJoin'][$dataset]=$joinInclusive;
}

//this block moved below the block just above
if($useStatusFilterOptions){
	unset($inStatusSet);
	if(is_array($_SESSION['userSettings']))
	foreach($_SESSION['userSettings'] as $n=>$v){
		if(preg_match('/^filter'.$dataset.'Status:([0-9]+)/i',$n,$a)){
			if(!$v)continue;
			$inStatusSet[]=$a[1];
		}
	}
	if(!count($inStatusSet)){
		//-------- be default we expect at least one status to be shown if using statuses ------------
		mail($developerEmail, 'notice file '.__FILE__.', line '.__LINE__.', initial declaration of top '.$statusFilterDefaultShown.' filters',get_globals(),$fromHdrNotices);
		if($a=q("SELECT $statusFilterIDField FROM $statusFilterTable WHERE $statusFilterQueryWhere ORDER BY $statusFilterIDField DESC LIMIT $statusFilterDefaultShown", O_COL)){
			foreach($a as $v){
				q("REPLACE INTO bais_settings SET UserName='".($filterGadgetUserName ? $filterGadgetUserName : sun()."', vargroup='$dataset', varnode='filter".$dataset."Status', varkey='$v', varvalue=1");
				$_SESSION['userSettings']['filter'.$dataset.'Status:'.$v]=1;
				$inStatusSet[]=$v;
			}
		}else{
			mail($developerEmail, 'error file '.__FILE__.', line '.__LINE__.', using filterStatus but unable to initially set top '.$statusFilterDefaultShown.' filters',get_globals(),$fromHdrBugs);
		}
	}
}

if(!$refreshComponentOnly){
	?><style type="text/css">
	#filterButton{
		position:relative;
		cursor:pointer;
		}
	#filterMain{
		<?php echo 'visibility:hidden;';?>
		position:absolute;
		z-index:25;
		right:0px;
		width:345px;
		border:1px solid #000;
		padding:15px;
		background-color:OLDLACE;
		}
	#filterGadgetIcon{
		}
	</style>
	<script language="javascript" type="text/javascript">
	var ssS=false; var ssF=false;
	function saveSearch(){
		g('filterMain').style.visibility='visible';
		setTimeout('hidefilterMain()',1000);
	}
	function hidefilterMain(){
		ssS || ssF ? setTimeout('hidefilterMain()',1000) : g('filterMain').style.visibility='hidden';
	}
	function filterUpdateReady(){
		g('updateFilters').disabled=false;
	}
	function addFilterRow(o){
		var str='<div class="filterRow">';
		str+='<input name="querytext[]" type="text" onfocus="ssF=true;fgbuffer=this.value;" onblur="ssF=false;" onkeyup="if(this.value!==fgbuffer)g(\'updateFilters\').disabled=false;" value="" size="35" maxlength="255" />';
		str+='<input tabindex="-1" title="Add another filter criteria" type="button" value="+" onclick="addFilterRow(this)" class="filterCtrl" style="width:24px;" />';
		if(o.value=='+'){
			if(o.previousSibling.value==''){
				alert('Enter a value for this row first');
				o.previousSibling.focus();
				return;
			}
			//store dynamically entered values - see below for how used
			n=g('filterRows').childNodes;
			var refill=[];
			for(i=0; i<n.length; i++){
				refill[i]=n[i].firstChild.value;
			}
			o.value='-';
			o.title='Remove this filter criteria';
			o.parentNode.parentNode.innerHTML+=str;
			//refill the values - not sure why they disappear
			for(i=0; i<refill.length; i++) n[i].firstChild.value=refill[i];
			if(i)g('joinInclusive').disabled=false;
		}else if(o.value=='-'){
			o.parentNode.style.display='none';
			o.parentNode.innerHTML='';
		}else{
			if(o.id=='clearFilters')g('filterRows').innerHTML=str;
			try{
			g('hdr-ctrls').innerHTML='searching..';
			}catch(e){}
			o.form.submit();
		}
	}
	</script><?php
}
?>
<div id="filterGadget">
	<div id="filterButton" onmouseover="ssS=true;" onmouseout="ssS=false;" onclick="saveSearch()" title="Modify and update filters (which records are shown)"><img id="filterGadgetIcon" src="/images/i/filter1.jpg" width="31" height="31" alt="filter" class="noghost" /> Filters 
		<div id="filterMain" onmouseover="ssS=true;" onmouseout="ssS=false;" >
			<form name="filters" id="filters" action="resources/bais_01_exe.php" method="post" target="w2" style="display:inline;">
				<?php if($useStatusFilterOptions){ ?>
				<div class="status">
					<div class="statusword"><?php echo $statusWord?></div>
					<?php 
					$sql="SELECT $statusFilterIDField, $statusFilterNameField FROM $statusFilterTable WHERE $statusFilterQueryWhere $statusFilterQueryOrder";
					foreach(q($sql, O_COL_ASSOC) as $n=>$v){
						?><label>
						<input name="Statuses_ID[]" type="checkbox" id="Statuses_ID[<?php echo $n?>]" value="<?php echo $n?>" <?php echo $_SESSION['userSettings']['filter'.$dataset.'Status:'.$n]?'checked':''?> onfocus="ssF=true;" onblur="ssF=false;" onchange="filterUpdateReady()" /> <?php echo h($v)?>
						</label><br /><?php
					}
					?>
				</div>
				-and-<br />
				<?php } ?>
				<?php
				$imax=count($_SESSION['special']['filterQuery'][$dataset]);
				?><div id="filterRows"><?php
				for($i=1;$i<=$imax+1;$i++){
					?><div class="filterRow"><input name="querytext[]" type="text" onfocus="ssF=true;fgbuffer=this.value" onblur="ssF=false;" onkeyup="if(this.value!==fgbuffer)g('updateFilters').disabled=false;" value="<?php echo h($_SESSION['special']['filterQuery'][$dataset][$i-1])?>" size="35" maxlength="255" /><input tabindex="-1" title="<?php echo $i==$imax+1?'Add another filter criteria':'Remove this filter criteria'?>" type="button" value="<?php echo $i==$imax+1?'+':'-'?>" onclick="addFilterRow(this)" class="filterCtrl" style="width:24px;" />
					</div><?php
				}
				?></div>
				<label><input type="checkbox" name="joinInclusive" id="joinInclusive" <?php echo $imax==0?'disabled':''?> value="1" <?php echo strtolower($_SESSION['special']['filterQueryJoin'][$dataset])=='or'?'checked':''?> onchange="filterUpdateReady()" />	ANY of these search conditions
				</label>
				<br /> 
				<input type="submit" name="updateFilters" id="updateFilters" value="Update" disabled="disabled" onfocus="ssF=true;" onblur="ssF=false;" onchange="filterUpdateReady()" />
				<input type="button" name="clearFilters" id="clearFilters" value="Clear" onfocus="ssF=true;" onblur="ssF=false;" onclick="addFilterRow(this)" />
				<input type="hidden" name="mode" id="mode.filters" value="<?php echo $filterGadgetMode ? $filterGadgetMode : 'updateDatasetFilters'?>" />
				<input type="hidden" name="dataset" id="dataset" value="<?php echo $dataset?>" />
				<input type="hidden" name="datasetTable" id="datasetTable" value="<?php echo $datasetTable?>" />
	
				<input type="hidden" name="useStatusFilterOptions" id="useStatusFilterOptions" value="<?php echo $useStatusFilterOptions?>" />
				<input type="hidden" name="statusFilterIDField" id="statusFilterIDField" value="<?php echo $statusFilterIDField?>" />
				<input type="hidden" name="statusFilterNameField" id="statusFilterNameField" value="<?php echo $statusFilterNameField?>" />
				<input type="hidden" name="statusFilterTable" id="statusFilterTable" value="<?php echo $statusFilterTable?>" />
				<input type="hidden" name="statusFilterQueryWhere" id="statusFilterQueryWhere" value="<?php echo $statusFilterQueryWhere?>" />

				<input type="hidden" name="component" id="component" value="<?php echo $datasetComponent?>" />
			</form>
		</div>
	</div>
</div>