<?php
/* ---------------------------------------
2010-05-01
* added array datasetControlOptions - very crude; simply a list of <td> cells
* added string datasetPreContent - paragraph before the table which can be echoed
* help me locate which component; an identifying comment is placed in the component unless datasetHideComment=true


2010-04-07: version 1.21
------------------------
* added tbodyScrollingThreshold - if set, only scrolls when count>this var

2010-04-07: version 1.20
------------------------
[this was just placed in DAC Int as a test and the sub-totalling worked just fine]
* major change in how we get the record collection - now a while loop
* added descending headers and ascending cals (bottom)

2010-04-04: version 1.10
------------------------
The objective is to fork off the dataset->component into the complexData layout, and also a report layout.  Since reporting will be crucial to GiocosaCare, as well as in console-rbrfm, this will make effective design critical and will make this file the "engine" for much of my applications.
I note that this is the OUTPUT, not the settings; those are in parent objects and specifically dataset_generic_precoding versions 1.0x and above.  So the required settings for this component could be stored and pulled from a database.

The idea is to also introduce higher levels of specification so that I can just set baseLayout=report or =dataobject (like in Thunderbird inbox), and from there just override a few settings



2010-04-03: version 1.00
------------------------
* added Dataset_ID as 'ID' by default
* additional variables
	$datasetAddObjectJSFunction - e.g. 'ow(this.href,\'l1_reps\',\'700,700\');';
	$datasetQueryStringKey - e.g. 'Salesreps_ID';
	$datasetDeleteMode - e.g. 'deleteSalesrep';

2010-03-07: starting to parameterize this component.  We had started toward buffering the entire component, and in addition we need to add limiting.
* this component works in conjunction with another component which declares the parameters.  
* it is designed to work with a 2d SQL query and turn it into a pretty interface which has:
	sortability
	selectability of fields
	highlighting and record opening
	context menuing
	groupability
	batchability
	
This component is /the table/ and also the header and the count parenthesis, therefore is a part of a greater whole, some conventions:
	required vars:
		datasetComponent
		availableCols[$datasetGroup][$modApType][$modApHandle]['scheme']
		datasetWord
		datasetWordPlural
		hideObjectInactiveControl
		submode

batchability
------------
if I show less tan all the records for a particular condition, I need to know about it visually and be able to:
	move to next
	move to previous
	add next
	add previous
	show all
	change batch
	a visual bar would be like [+]---XXXXX-------------------- *
		dragging the XXXXX would quantum thebatch over
		clicking to the right of the batch would move to it
		ctrl-clicking to the right of the batch would add it
		same for the left
		+ means show all, toggles to show batch only (-) when I'm in show all
		* means show textual detail
		all this would go best at the top of the bar in a superheader, or the bottom tfoot
these vars can be stored in session or in settings to restore state
this component is not responsible for checking if the batch is currently within the scope of the recordset, but could be programmed to adjust if the count present is not toward the end.

I'm adding a 4th parameter in function get_navstats(), batches, which is the number of batches currently showing, and these "super vars" will be declared:
	nextGroupIndex (if not present, the next group is not available)
	nextGroupBatch
	prevGroupIndex (same here)
	prevGroupBatch
Iterating records
-----------------
By default the variable $recordsetIsRelative will be set to false; this means this recordset is absolute and if there is batching involved, we are going to bypass records not in any declared range

what I store
------------
_SESSION[userSettings][default{dataset}Batch]= (position,batch,batches) - as 76,15,3 would be position 76, 15 records/batch, and 3 batches (45 records), showing 76-120


we want batching to start happening above a sensible threshold, if it is allowed.  default would be to allow it
we normally do NOT want to batch data, only when it becomes unweildy due to size (or if we're on limited bandwidth or a blackberry etc.)
the applications here are analogous to facebook posts


$datasetBreakFields=array(
	1=>array(
		'column'=>'Country',
	),
	2=>array(
		'column'=>'State',
	),
	3=>array(
		'column'=>'City',
	)
);
$datasetCalcFields=array(
	array(
		'name'=>'Quotes',
		'calc'=>'sum'
	),
);
*/



if(!function_exists('dataset_functions'))require($FUNCTION_ROOT.'/group_dataset_functions_v100.php');

//default settings for this dataset->component
if(!$Dataset_ID)$Dataset_ID='ID';
if(!$datasetTheme)$datasetTheme='dataobject'; //like Thunderbird email list
if(!$datasetHighlightColor)$datasetHighlightColor='#6c7093';

if($datasetTheme=='dataobject'){
	if(!isset($tbodyScrollingThreshold))$tbodyScrollingThreshold=4;
	if(!$datasetTableClass)$datasetTableClass='complexData';
	if(!isset($scrollableTbodyRegion))$scrollableTbodyRegion=true;
	if(!isset($useHighlighting))$useHighlighting=true;
	if(!$footerDisposition)$footerDisposition='tabularControls';
	if(!isset($hideColumnSelection))$hideColumnSelection=false;
	if(!isset($hideSort))$hideSort=false;
}else if($datasetTheme=='report'){
	if(!$datasetTableClass)$datasetTableClass='standardReport';
	if(!isset($datasetShowBreaks))$datasetShowBreaks=false;
	if(!isset($scrollableTbodyRegion))$scrollableTbodyRegion=false;
	if(!isset($useHighlighting))$useHighlighting=false;
	if(!$footerDisposition)$footerDisposition='reportFooter';
	if(!isset($hideColumnSelection))$hideColumnSelection=true;
	if(!isset($hideSort))$hideSort=true;
}

if(!$refreshComponentOnly && $submode!=='exportDataset'){
	?>
	<style type="text/css">
	
	
	</style>
	<script language="javascript" type="text/javascript">
	<?php if($datasetTheme=='dataobject'){ ?>
	AssignMenu('^colOptions_<?php echo $dataset?>','optionsAvailableCols');
	function colOptions(){}
	function mgeCol(e,n){
		var posn=g('col'+n).className.indexOf('Visible');
		window.open('resources/bais_01_exe.php?mode=refreshComponent&component=<?php echo $datasetComponent?>&col='+n+'&visibility='+(posn> -1? 8 : 16),'w2');
	}
	hl_bg['<?php echo strtolower($datasetWord);?>opt']='<?php echo $datasetHighlightColor;?>';
	hl_baseclass['<?php echo strtolower($datasetWord);?>opt']='normal';
	hl_class['<?php echo strtolower($datasetWord);?>opt']='hlrow';

	<?php } ?>
	</script><?php
}
ob_start();
?>
<div id="<?php echo $datasetComponent?>" refreshparams="noparams">
	<?php
	if($submode!='exportDataset' && !$datasetHideComment){
		echo '<!-- component: ';
		foreach(get_included_files() as $v){
			if(preg_match('#components/comp_#',$v))$file=$v;
		}
		echo end(explode('/',$file));
		echo ' -->';
	}
	ob_start(); //--- buffer heading ---
	?>
	<h3><?php echo $datasetWordPlural;?> (<span id="<?php echo $datasetComponent?>_count"><?php echo $count;?></span>) <?php 
	if($inBatching || $limitClause){	
		echo 'Showing '.$position . '-'.($position+$currentRecordset-1 <= $count ? $position+$currentRecordset-1 : $count);
	}
	?></h3>
	<?php
	echo $componentHeading=get_contents();
	
	//text content before dataset->component
	ob_start();
	if($datasetPreContent)echo $datasetPreContent;
	echo $datasetPreContent=get_contents();
	?>
	<div id="optionsAvailableCols" class="menuskin1" style="width:150px;" onmouseover="hlght2(event)" onmouseout="llght2(event)" onclick="executemenuie5(event)" precalculated="colOptions();">
		<?php
		foreach($availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'] as $handle=>$scheme){
			if(isset($scheme['visibility']) && $scheme['visibility']<COL_AVAILABLE)continue;
			?><div id="col<?php echo $handle?>" class="menuitems colOpt<?php echo !isset($scheme['visibility']) || $scheme['visibility']==COL_VISIBLE?'Visible':'Hidden'?>" command="mgeCol(event,'<?php echo $handle?>');" status="Show or hide this column"><?php echo $scheme['header'] ? $scheme['header'] : $handle;?></div><?php
		}
		?>
	</div>
	<input type="hidden" name="noparams" id="noparams" value="" />
	<table border="0" cellspacing="0" cellpadding="0" class="<?php echo $datasetTableClass?>" style="clear:both;">
		<?php ob_start(); //--- buffer thead --- ?>
		<thead>
			<tr>
				<?php
				if($datasetControlOptions){ 
					$countVisible=0;
					foreach($datasetControlOptions as $n=>$v){
						$countVisible++;
						//filter hidden controls
						if(isset($v['show']) && !$v['show'])continue;
						//filter controls not made for left hand position
						if(isset($v['position']) && strtolower($v['position'])!=='left')continue;
					}
					if($countVisible){
						?>
						<!-- additional controls -->
						<th colspan="<?php echo $countVisible?>">&nbsp;</th><?php
					}
				}
				?>
				<!-- control cells -->
				<?php if(!$hideObjectInactiveControl){ ?>
				<th id="toggleActive" class="activetoggle"><a title="Hide or show inactive <?php echo strtolower($datasetWordPlural);?>" href="javascript:toggleActive('<?php echo $datasetComponent?>',hideInactive<?php echo $dataset?>);">&nbsp;&nbsp;</a></th>
				<?php } ?>
				<?php if(!$hideEditControls){ ?>
				<th>&nbsp;</th>
				<?php }?>
				<?php
				
				//----------- column headers ----------------
				$cols=0;
				foreach($availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'] as $handle=>$scheme){
					if(isset($scheme['visibility']) && $scheme['visibility']<COL_VISIBLE)continue;
					$cols++;
					?><th nowrap="nowrap" <?php echo $scheme['sortable'] || !isset($scheme['sortable']) ? 'sortable="1"' : ''?> class="<?php echo $sort==$handle ? 'sorted'.strtolower($asc):''?><?php echo $cols==$visibleColCount?' last':''?>"><?php 
						if($cols==$visibleColCount && !$hideColumnSelection){
							echo '<table width="100%"><tr><td style="padding:0px;background:none;">';
						}
						//link tag for sort
						if(($scheme['sortable'] || !isset($scheme['sortable'])) && !$hideSort){ 
							?><a href="resources/bais_01_exe.php?mode=refreshComponent&component=<?php echo $datasetComponent?>&sort=<?php echo $handle?>&dir=<?php echo !$dir || ($sort==$handle && $dir=='-1') ? 1 : -1?>" target="w2" title="<?php echo $scheme['title'];?>"><?php
						}
						//output header
						echo strlen($scheme['header']) ? $scheme['header'] : preg_replace('/([a-z])([A-Z])/','$1 $2',$handle);
						
						//close link tag
						if(($scheme['sortable'] || !isset($scheme['sortable'])) && !$hideSort){ ?></a><?php }

						//select cols icon
						if($cols==$visibleColCount && !$hideColumnSelection){ 
							echo '</td><td style="text-align:right;padding:0px;background:none;">&nbsp;';
							?>
							<a id="colOptions_<?php echo $dataset?>" class="colOptionsAnchor" title="Select and organize columns for this view" style="padding:0px;" href="javascript:;" onclick="hidemenuie5(event,1);showmenuie5(event,1)" oncontextmenu="return false;">&nbsp;&nbsp;&nbsp;&nbsp;</a>
							<?php
							echo '</td></tr></table>';
						}
					?></th><?php
				}
				?>
			</tr>
		</thead>
		<?php 
		echo $componentThead=get_contents();
		ob_start(); //--- buffer tfoot ---
		?><tfoot>
			<tr valign="top">
			<td colspan="100%"><?php
			if($footerDisposition=='tabularControls'){
				if($inBatching || $limitClause){
					$url='resources/bais_01_exe.php?mode=refreshComponent&component='.$datasetComponent.'&batch=';
					?><style type="text/css">
					.bottomNavCtrls .unavailable{
						color:#ccc;
						}
					</style>
					<div class="bottomNavCtrls fr">
					<?php
					//prn($navStats[$dataset]);
					?>
					<a title="Go to the previous set of records (swap with these records)" 
					href="<?php echo $url?><?php echo ($navStats[$dataset]['prevGroupIndex'] ? $navStats[$dataset]['prevGroupIndex'] : $navStats[$dataset]['prevIndex']).','.$currentBatch;?>" 
					class="<?php echo !$navStats[$dataset]['prevGroupIndex'] ? 'unavailable':''?>" 
					onclick="return <?php echo $navStats[$dataset]['prevGroupIndex'] ? 'true':'false';?>;"
					target="w2"
					>go to previous</a> | 
					<a 
					title="Add the previous <?php echo $currentBatch?> records to this list" 
					href="<?php echo $url?><?php echo $navStats[$dataset]['prevGroupIndex'] ? $navStats[$dataset]['prevGroupIndex'].','.$currentBatch.','.($batches+1) : '';?>"
					class="<?php echo !$navStats[$dataset]['prevGroupIndex'] ? 'unavailable':''?>"
					onclick="return <?php echo $navStats[$dataset]['prevGroupIndex'] ? 'true':'false';?>;"
					target="w2"
					>add previous</a> | 
					<a 
					title="Go to the next set of records (swap with these records)" 
					href="<?php echo $url?><?php echo ($navStats[$dataset]['nextGroupIndex'] ? $navStats[$dataset]['nextGroupIndex'] : $navStats[$datset]['nextIndex']).','.$currentBatch;?>"
					class="<?php echo !$navStats[$dataset]['nextGroupIndex'] ? 'unavailable':''?>"
					onclick="return <?php echo $navStats[$dataset]['nextGroupIndex'] ? 'true':'false';?>;"
					target="w2"
					>go to next</a> | 
					<a 
					title="Add the next <?php echo $currentBatch?> records to this list" 
					href="<?php echo $url?><?php echo $navStats[$dataset]['nextGroupIndex'] ? $navStats[$dataset]['thisIndex'].','.$currentBatch.','.($batches+1):'';?>"
					class="<?php echo !$navStats[$dataset]['nextGroupIndex'] ? 'unavailable':''?>"
					onclick="return <?php echo $navStats[$dataset]['nextGroupIndex'] ? 'true':'false';?>;"
					target="w2"
					>add next</a> | 
					<?php
					if(true){
						?>
						<a 
						title="Expand the list to all records in the database (may be slow)" 
						href="<?php echo $url?><?php echo '0,0,0';?>"
						target="w2"
						>add all</a> | 
						<?php
					}else{
					
					}
					?>
					<a 
					title="Clear settings for which records are being shown"
					href="<?php echo $url?><?php echo '0,0,0';?>"
					target="w2"
					>clear</a>
					</div><?php
				}
				?><a href="<?php echo $datasetFocusPage?>?cbFunction=refreshComponent&cbParam=fixed:<?php echo $datasetComponent?>" onclick="return <?php echo $datasetAddObjectJSFunction ? $datasetAddObjectJSFunction : 'add'.$dataset.'()'?>"><img src="<?php echo $datasetImagesRelativePath?>/images/i/add_32x32.gif" width="32" height="32">&nbsp;Add <?php echo strtolower($datasetWord);?>..</a><?php
			}else{
			
			}
			?></td>
			</tr>
		</tfoot><?php
		$componentTfoot=get_contents();
		if(!$hideComponentTfoot)echo $componentTfoot;
		ob_start(); //--- buffer tbody ---
		?>
		<tbody id="<?php echo $datasetComponent?>_tbody" <?php if($browser!=='IE' && $scrollableTbodyRegion){?> style="overflow-y:scroll;overflow-x:hidden;height:350px;" <?php } ?>>
		<?php
		$datasetOutput='';
		if($records){
			$i=0;
			$j=0;
			while(true){
				if(is_array($nextRecord)){
					$record=$nextRecord;
					@$nextRecord=current(each($records));
				}else{
					@$record=current(each($records));
					@$nextRecord=current(each($records));
				}
				if(!$record)break;
				//--------- begin new while loop kernel -----------

				//apply any filters here

				//handle batching
				$j++;
				if($inBatching && 
					($j<$position || 
					 $j>=($position + ($navStats[$dataset]['batch'] * ($batches ? $batches : 1))))
					){
					continue;
				}

				$i++;
				if($i==1 && $datasetShowBreaks){
					if(!is_array($optionsTop))$optionsTop=array('section'=>'top');
					dataset_breaks_calcs($optionsTop);
				}

				//get permissions
				extract($record);
				$deletable=true;
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
				
				$hNormalCoding='h(this,\''.strtolower($datasetWord).'opt\',0,0,event);';
				$hContextMenuCoding='h(this,\''.strtolower($datasetWord).'opt\',0,1,event);';
				$focusViewURL=$datasetFocusPage.'?'.($datasetQueryStringKey ? $datasetQueryStringKey : $datasetWordPlural.'_ID').'='.$$Dataset_ID;
				?><tr id="r_<?php echo $$Dataset_ID?>" onclick="<?php if($useHighlighting)echo $hNormalCoding;?>" ondblclick="<?php if($useHighlighting)echo $hNormalCoding;?>open<?php echo $dataset?>();" oncontextmenu="<?php if($useHighlighting)echo $hContextMenuCoding;?>" class="normal<?php echo fmod($i,2)?' alt':''?>" deletable="<?php echo $deletable?>" active="<?php echo $Active?>">
					<?php 
					//2010-05-01: introduced for special select controls and the like
					if($datasetControlOptions){ 
						foreach($datasetControlOptions as $n=>$v){
							//filter hidden controls
							if(isset($v['show']) && !$v['show'])continue;
							//filter controls not made for left hand position
							if(isset($v['position']) && strtolower($v['position'])!=='left')continue;
							if($v['eval']){
								eval(' ?>'.$v['eval'].'<?php ');
							}else{
								?><td>[<a href="#" onclick="return getThis()">select</a>]</td><?php
							}
						}
					}
					?>
					<?php if(!$hideObjectInactiveControl){ ?>
					<td id="r_<?php echo $$Dataset_ID?>_active" title="Make this <?php echo strtolower($datasetWord);?> <?php echo $Active ? 'in':''?>active" onclick="toggleActiveObject('<?php echo $dataset?>',<?php echo $$Dataset_ID?>,'<?php echo $datasetComponent?>');" class="activetoggle"><?php
					if(!$Active){
						?><img src="<?php echo $datasetImagesRelativePath?>/images/i/garbage2.gif" width="18" height="21" align="absbottom" /><?php
					}else{
						?>&nbsp;<?php
					}
					?></td>
					<?php } ?>
					<?php
					if(!$hideEditControls){
						?><td nowrap="nowrap"><?php
						if($showDeletion){
							if($deletable){
								?><a title="Delete this <?php echo strtolower($datasetWord);?>" href="resources/bais_01_exe.php?mode=<?php echo $datasetDeleteMode ? $datasetDeleteMode : 'delete'.$datasetWord?>&<?php echo $datasetQueryStringKey ? $datasetQueryStringKey : $datasetWordPlural.'_ID';?>=<?php echo $$Dataset_ID?>" target="w2" onclick="if(!confirm('This will permanently delete this <?php echo strtolower($datasetWord)?>\'s record.  Are you sure?'))return false;">&nbsp;<img src="<?php echo $datasetImagesRelativePath?>/images/i/del2.gif" alt="delete" width="16" height="18" border="0" /></a><?php
							}else{
								?>&nbsp;<img src="<?php echo $datasetImagesRelativePath?>/images/i/spacer.gif" width="18" height="18" /><?php
							}
							?>&nbsp;&nbsp;<?php
						}
						?><a title="Edit this <?php echo strtolower($datasetWord);?> information" href="<?php echo $focusViewURL?>" onclick="return ow(this.href,'l1_<?php echo strtolower($datasetWord);?>','700,700');return false;"><img src="<?php echo $datasetImagesRelativePath?>/images/i/edit2.gif" width="15" height="18" border="0"></a><?php 
						?>&nbsp;</td><?php
					}
					//--------------- user columns coding added 2009-10-27 --------------
					$colPosition=0;
					if(!$lastHandle)
					foreach($availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'] as $handle=>$scheme){
						//get last column
						if($scheme['visibility']>=COL_VISIBLE)$lastHandle=$handle;
					}
					foreach($availableCols[$datasetGroup][$modApType][$modApHandle]['scheme'] as $handle=>$scheme){
						if(isset($scheme['visibility']) && $scheme['visibility']<COL_VISIBLE)continue;
						$colPosition++;
						unset($echoed,$out);
						//-------- here is the kernel logic for how we present the fields --------
						if(!$scheme['method'] || $scheme['method']=='field'){
							$out=$record[$scheme['fieldExpressionFunction'] ? $scheme['fieldExpressionFunction'] : $handle];
							switch($scheme['datatype']){
								case 'email':
								case 'url':
								case 'linkable':
									if(!function_exists('make_clickable_links'))require_once($FUNCTION_ROOT.'/function_make_clickable_links_v100.php');
									if($scheme['format']=='noformat')break;
									if($submode!=='exportDataset')$out=make_clickable_links($out);
									break;
								case 'date':
									if($scheme['format']=='noformat')break;
									//we'll assume the export wants the reformat as well
									if($scheme['format']){
										//not developed, this would be the format like F js etc. we use
									}else{
										$out=t($out, (strlen($out)==10?f_qbks:f_dspst), $scheme['thisyear']);
									}
									break;
								case 'time':
									$out=date('g:iA',strtotime($out));
									break;
								case 'logical':
									if($scheme['format']=='noformat')break;
									if(strlen($scheme['format'])){
										$out=output_logical($out,$scheme['format']);
									}
								case '':
									
									/* 2009-12-15: improved default field handling */
									if(!$dataSourceExplained){
										$dataSourceExplained=q("EXPLAIN $datasetTable", O_ARRAY);
										foreach($dataSourceExplained as $n=>$v){
											$dataSourceExplained[$v['Field']]=$v;
											unset($dataSourceExplained[$n]);
										}
									}
									if(!($v=$dataSourceExplained[$handle]))break;
									preg_match('/^([a-z]+)/i',$v['Type'],$a);
									if($a[1]=='date'){
										$out=($out=='0000-00-00' ? '' : date('m/d/Y',strtotime($out)));
									}else if($a[1]=='datetime'){
										$out=t($out, (strlen($out)==10?f_qbks:f_dspst), thisyear);
									}else if($a[1]=='time'){
										//assume balls is a null for now
										$out=($out=='00:00:00' || $out=='00:00' || is_null($out) ? '' : date('g:iA',strtotime($out)));
									}
									break;
							}
						}else if($scheme['method']=='function'){
							ob_start();
							eval('$out='.rtrim($scheme['fieldExpressionFunction'],';').';');
							$echoed=ob_get_contents();
							ob_end_clean();
							if($echoed && !$echonotified){
								$echonotified=true;
								mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
							}
						}
						if($submode=='exportDataset'){
							//$datasetOutput
							$datasetOutput.=str_replace('"','""',$out)."\t";
							continue;
						}else{
							//handle 1)wrap, 2)addt'l classes 3)overflow of data at some point
							unset($mergeAttribs,$colAttribs);
							if($scheme['colattribs']){
								foreach($scheme['colattribs'] as $n=>$v){
									$n=strtolower($n);
									if($n=='class' || ($n=='nowrap' && $v)){
										$mergeAttribs[$n]=$v;
									}else{
										$colAttribs[$n]=$v;
									}
								}
							}
							?><td <?php echo $scheme['nowrap'] || $mergeAttribs['nowrap']?'nowrap':''?> class="<?php echo $sort==$handle ? 'sorted':''?><?php echo $handle==$lastHandle?' last':'' ?><?php if($mergeAttribs['class'])echo ' '.$mergeAttribs['class']?>"<?php if($colAttribs)foreach($colAttribs as $n=>$v)echo ' '.$n.'="'.$v.'"';?>><?php
							echo strlen($out) ? $out : '&nbsp;';
							?></td><?php
						}
					}
					//---------------------------------------------------------------------
					?>
				</tr><?php

				//2010-04-07: new ascending subtotals and descending headers
				if($datasetShowBreaks){
					if(!is_array($optionsMid))$optionsMid=array('section'=>'mid');
					dataset_breaks_calcs($optionsMid);
				}

				//--------- end new while loop kernel -----------
				if(!$nextRecord)break;
			}

			//2010-04-07: additional code to close out
			if($datasetShowBreaks){
				if(!is_array($optionsBottom))$optionsBottom=array('section'=>'bottom');
				dataset_breaks_calcs($optionsBottom);
			}

		}else{
			//no records
		}
		?></tbody>
		<?php
		$componentTbody=get_contents();
		if($tbodyScrollingThreshold && $tbodyScrollingThreshold>$i)$componentTbody=str_replace('style="overflow-y:scroll;overflow-x:hidden;height:350px;"','',$componentTbody);
		echo $componentTbody;
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