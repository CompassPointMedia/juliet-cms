<?php
/*

ability to export and import label job settings!

todo
	2013-11-29
	just save - not OK and close
	same thing save and remain on insertMode
	instructions on how to set margins
	logical fields - logical address 2nd line will be a feature needed
	
	2013-11-28
	print window escapable
	we want to test one page, we want to preview with or without borders, we also can do @media=screen, borders there only - come up with something sensible on this.
	testing with one page means dummy data
	I don't know how FilterExactDuplicates is going to behave around page breaks and end of records - suspicious of it, need to test
	POLAR BEAR: document how this works in help.  also explain that labels may not always be repeatable, i.e. some records may not longer fit a criteria or may be deleted.
	interlock between labels and custom
		select a label = warn, load and detectchange
		change a paramter = set custom label
	
	
bugs
	print 2 or 3 of each label is not working - totally screwed up
	
		
*/
if(!function_exists('form_field_translator'))require($FUNCTION_ROOT.'/function_form_field_translator_v100.php');
$dataSources=array(
	'SQL Query', 
	'Import Batch Number', 
	'Pasted-In Data', 
	'Statuses', 
	'Individual Users', 
	'Static Labels'
);

if(!$Duplicate)$Duplicate=1;

if($mode=='PrintNow'){
	if(!($a=$_SESSION['labels'][$key]))exit('Unable to locate data; close window and try again');
	extract($a);
	extract($LabeltypesSettings);

	$labelTestMode=true;
	
	//following are needed to print correctly
	$discrepancyTopBottom=.125; #these get subtracted
	$discrepancyLeftRight=.125;
	$topMargin2ndPageFix=.4; #this is weird; if you need .5, in FF that creates a blank page - so -.1 of target seems to work
	
	$lead=$StartCol - 1 + ($StartRow - 1)*$cols;
	$ppi=$Pixels;

	if($stupidChromeFix){
		$widthFix=15; #screw Chrome, these are quite extreme and I doubt we'd be on layout
		$heightFix=10;
	}

	
	switch($DataSource){
		case 'SQL Query':
			$data=q($QueryContent,O_ARRAY);
			$recordCount=count($data)*$Duplicate;
		break;
		case 'Import Batch Number':
			$table=q("SELECT ObjectName FROM gen_batches_entries WHERE Batches_ID=$Batches_ID", O_VALUE);
			$data=q("SELECT t.* FROM gen_batches_entries e JOIN $table t ON e.Objects_ID=t.ID AND e.Batches_ID=$Batches_ID", O_ARRAY);
			$recordCount=count($data)*$Duplicate;
		break;
		case 'Pasted-In Data':
		case 'Statuses':
		case 'Individual Users':
			exit('That data source method is not developed yet');
		case 'Static Labels':
			$recordCount=$Quantity; //forget duplicate
		break;
	}
	if(!$recordCount)exit('No labels to print!');
	
	//Avery 5160 example
	if(false){
		$labelTop=.5;
		$labelLeft=.19;
		$labelWidth=2.63;
		$labelHeight=1.00;
		$labelMarginRight=.12;
		$labelMarginBottom=0;
		$rows=10;
		$cols=3;
		//following are needed to print correctly
		$discrepancyTopBottom=.125; #these get subtracted
		$discrepancyLeftRight=.125;
		$topMargin2ndPageFix=.4; #this is weird; if you need .5, in FF that creates a blank page - so -.1 of target seems to work
		if(true && 'stupid browser Chrome'){
			exit;
			$widthFix=10; #screw Chrome, these are quite extreme and I doubt we'd be on layout
			$heightFix=3;
		}
	}
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<title>Labels : '.$Name.'</title>
	<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />';
	?>
	<script language="javascript" type="text/javascript" src="/Library/js/jquery.js"></script>
	<script language="JavaScript" type="text/javascript" src="/Library/js/global_04_i1.js"></script>
	<script language="JavaScript" type="text/javascript" src="/Library/js/common_04_i1.js"></script>
	<script language="JavaScript" type="text/javascript" src="/Library/js/forms_04_i1.js"></script>
	<script language="JavaScript" type="text/javascript" src="/Library/js/loader_04_i1.js"></script>
	<script language="JavaScript" type="text/javascript" src="/Library/js/contextmenus_04_i1.js"></script>
	<script language="JavaScript" type="text/javascript" src="/Library/js/dataobjects_04_i1.js"></script>
	<script language="JavaScript" type="text/javascript">
	/* periwinkle coding */
	var thispage='<?php echo $thispage?>';
	var thisfolder='<?php echo $thisfolder?>';
	var browser='<?php echo $browser?>';
	var ctime='<?php echo $ctime?>';
	var PHPSESSID='<?php echo $PHPSESSID?>';
	//for nav feature
	var count='<?php echo $nullCount?>';
	var ab='<?php echo $nullAbs?>';
	</script>
	<style>
	body{
		margin:0px;
		padding:0px;
		}
	.page{
		margin-top:<?php echo ($labelTop-$discrepancyTopBottom)*$ppi;?>px;
		margin-right:<?php echo ($labelLeft-$discrepancyLeftRight)*$ppi;?>px;
		margin-bottom:<?php echo ($labelTop-$discrepancyTopBottom)*$ppi;?>px;
		margin-left:<?php echo ($labelLeft-$discrepancyLeftRight)*$ppi;?>px;
		}
	.label{
		margin-right:<?php echo $labelMarginRight*$ppi;?>px;
		margin-bottom:<?php echo $labelMarginBottom*$ppi;?>px;
		float:left;
		}
	.labelRight{ margin-right:0px; }
	.labelBottom{ margin-bottom:0px; }
	.space{
		margin:10px;
		}
	.pageBreakBefore{
		page-break-before:always;
		margin-top:<?php echo $labelTop*$ppi;?>px;
		}
	.topMargin2ndPageFix{
		width:90%;
		height:<?php echo $topMargin2ndPageFix*$ppi;?>px;
		}
	/* visual aids */
	@media screen{
		.label{
			width:<?php echo ($labelWidth*$ppi)-($ShowBorders?2:0)-$widthFix;?>px;
			height:<?php echo ($labelHeight*$ppi)-($ShowBorders?2:0)-$heightFix;?>px;
			}
	}
	@media print{
		.label{
			width:<?php echo ($labelWidth*$ppi)-($ShowBorders==2?2:0)-$widthFix;?>px;
			height:<?php echo ($labelHeight*$ppi)-($ShowBorders==2?2:0)-$heightFix;?>px;
			}
	}
	<?php if($ShowBorders==1)echo '@media screen{';?>
	<?php if($ShowBorders){ ?>
	.page{
		outline:1px dashed darkred;
		}
	.label{
		border:1px solid #666;
		border-radius:5px;
		}
	.space{
		outline:1px dotted #ccc;
		}
	<?php } ?>
	<?php if($ShowBorders==1)echo '}';?>
	<?php echo $TemplateCSS; ?>
	</style>
	</head>
	
	<body>
	<div class="page"><?php 
	//prepare template
	if(strip_tags($TemplateHTML)==$TemplateHTML){
		//treat as plaintext
		$TemplateHTML=nl2br($TemplateHTML);
	}
	unset($fields);
	if(preg_match_all('/\{([_a-zA-Z0-9]+)\}/',$TemplateHTML,$fields)){
		$fields=$fields[1];
	}
	$j=0;
	for($i=1; $i<=($lead + $recordCount); $i++){
		if($i>1 && !fmod($i-1,$rows*$cols)){
			?></div><div class="page pageBreakBefore"><?php
			if($topMargin2ndPageFix){
				?><div class="topMargin2ndPageFix"></div><?php
			}
		}
		if($i<=$lead){
			$output='<em class="gray printhide">(blank lead - will not print)</em>';
		}else{
			$j++;
			//------------ here we go! print the label ---------------
			switch($DataSource){
				case 'SQL Query':
				case 'Import Batch Number':
					$record=$data[ceil($j/$Duplicate)];
				break;
				#case 'Pasted-In Data':
				#case 'Statuses':
				#case 'Individual Users':
				case 'Static Labels':
					$record=array(); //i.e., no data, just static
				break;
			}
			$str=$TemplateHTML;
			if($fields){
				foreach($fields as $field){
					foreach($record as $o=>$w){
						if(strtolower($field)==strtolower($o)){
							$str=str_replace('{'.$field.'}',$w,$str);
						}
					}
				}
			}
			if($FilterExactDuplicates && $DataSource!=='Static Labels' && $onDuplicate==1){
				$strHash=md5(strtolower($str));
				if($hash[$strHash]){
					$recordCount=$recordCount-$Duplicate;
					continue;
				}else{
					$hash[$strHash]=true;
				}
			}
			$output=$str;
			//--------------------------------------------------------
			$onDuplicate=($onDuplicate+1<=$Duplicate?$onDuplicate+1:1);
		}
		?>
		<div class="label<?php if(!fmod($i,$cols))echo ' labelRight'; if(!fmod(ceil($i/$cols),$rows))echo ' labelBottom';?>"><div class="space">
		<?php 
		echo $output;?>
		</div></div><?php 
	}
	?></div><?php
	echo '</body></html>';
	eOK();
}else if($submode=='PrintNow'){
	if($ID && $StoreInHistory){
		$Labels_ID=$ID;
		unset($ID);
		$LabeltypesSettings=base64_encode(serialize($LabeltypesSettings));
		$sql=sql_insert_update_generic($MASTER_DATABASE,'gen_labels', 'INSERT',$options=array('setCtrlFields'=>true));
		$fl=__FILE__; $ln=__LINE__;
		prn($sql);
		q($sql, O_INSERTID);
	}
	$key=md5(base64_encode(serialize($_POST)));
	$_SESSION['labels'][$key]=stripslashes_deep($_POST);
	?><script language="javascript" type="text/javascript">
	window.parent.ow('/console/labels.php?mode=PrintNow&key=<?php echo $key;?>','l2_labels','1100,700');
	</script><?php
	eOK();
}else if(($mode==$insertMode || $mode==$updateMode || $mode==$deleteMode) && $_POST['navVer']){
	if($mode==$deleteMode)error_alert('delete not developed');
	/*
	POLAR BEAR: interesting application, we update if requested and that has a timestamp.  Print jobs are sub-records which might be "updated" as well.  So both the root record and the job have timestamps and we take the most recent one.
	*/
	unset($PrintComments);
	if($mode==$updateMode && !$ID)error_alert('ID not present, notify administrator');
	if(!$ID)unset($ID);
	$Labels_ID='PHP:NULL';
	$LabeltypesSettings=base64_encode(serialize($LabeltypesSettings));
	$sql=sql_insert_update_generic($MASTER_DATABASE,'gen_labels', $mode,$options=array('setCtrlFields'=>true));
	$fl=__FILE__; $ln=__LINE__;
	prn($sql);
	$temp=q($sql, O_INSERTID);
	if($mode==$insertMode)$ID=$temp;

	//handle callback
	if($cbPresent){
		callback(array("useTryCatch"=>false));
	}
	//navigate interface
	$navigate=true;
	$navigateCount=$count+($mode==$insertMode ? 1 : 0);

	goto compend;
}

if(!$refreshComponentOnly){
	?>
	<style type="text/css">
.optionBox{
	margin-top:15px;
	border:1px solid #ccc;
	border-radius:15px;
	padding:15px;
	}
	</style>
	<script language="javascript" type="text/javascript">
AddOnkeypressCommand("PropKeyPress(e)");
//var customDeleteHandler='deleteItem()';
$(document).ready(function(){
	$('#DataSource').change(function(){
		var n=this.value.replace(/[^a-z0-9]/gi,'');
		for(var i in dataSources){
			var o=dataSources[i].replace(/[^a-z0-9]/gi,'');
			$('#source'+o).fadeOut();
		}
		$('#source'+n).fadeIn();
	});
	$('#PrintNow').click(function(){
		var buffer=g('submode').value;
		g('submode').value='PrintNow';
		g('form1').submit();
		setTimeout('g(\'submode\').value=\''+buffer+'\';',1000);
	});
});
var dataSources=<?php echo json_encode($dataSources);?>;	
	</script>
	
	<?php
}
?><h1 class="nullTop">Label Job Manager</h1>

Name of job: <input name="Name" type="text" id="Name" onChange="dChge(this);" value="<?php echo h($Name);?>" size="45" maxlength="75" />
<br />

<?php 
ob_start();
?>
Select label data source from:
<select name="DataSource" id="DataSource" onChange="dChge(this);">
	<option value="">&lt;Select..&gt;</option>
	<?php foreach($dataSources as $source){ ?>
	<option value="<?php echo $source;?>" <?php echo $DataSource==$source?'selected':''?>><?php echo $source=='Static Labels'?'('.$source.')':$source;?></option>
	<?php }?>
</select>
<br />
<br />
<input type="hidden" name="FilterExactDuplicates" value="0" />
<label><input type="checkbox" name="FilterExactDuplicates" id="FilterExactDuplicates" value="1" <?php echo !isset($FilterExactDuplicates) || $FilterExactDuplicates==1?'checked':''?> onChange="dChge(this);" /> Filter exact duplicates <span class="gray">(do not print twice)</span></label><br />

<div id="sourceSQLQuery" class="optionBox" style="display:<?php echo $DataSource=='SQL Query'?'block':'none';?>">
	SQL Query:<br />
	<textarea rows="12" cols="75" name="QueryContent" id="QueryContent" onChange="dChge(this);"><?php echo h($QueryContent); ?></textarea>
	<br />
	<br />
	[<a href="#" id="testQuery">Test query</a>] [<a href="#" id="sqlHelp">SQL Help</a>]<br />
</div>
<div id="sourceImportBatchNumber" class="optionBox" style="display:<?php echo $DataSource=='Import Batch Number'?'block':'none';?>">
	Select an import batch: <select name="Batches_ID" id="Batches_ID" onChange="dChge(this);">
	<option value="">&lt;Select..&gt;</option>
	<?php if($a=q("SELECT b.*, e.ObjectName FROM gen_batches b JOIN gen_batches_entries e ON b.ID=e.Batches_ID GROUP BY b.ID ORDER BY b.ID", O_ARRAY_ASSOC))
	foreach($a as $n=>$v){
		?><option value="<?php echo $n;?>" <?php echo $n==$Batches_ID?'selected':''?>><?php echo 'Batch #'.$n.' ('.date('n/j/Y \a\t g:iA',strtotime($v['CreateDate'])).') from '.$v['ObjectName'];?></option><?php
	}
	?></select>
	
	</select>
</div>
<div id="sourcePastedInData" class="optionBox" style="display:<?php echo $DataSource=='Pasted-In Data'?'block':'none';?>">
	<strong>Paste data in here:</strong><br />
	<textarea rows="12" cols="75" name="PastedDataContent" id="PastedDataContent" onChange="dChge(this);"><?php echo h($PastedDataContent);?></textarea>
</div>
<div id="sourceStatuses" class="optionBox" style="display:<?php echo $DataSource=='Statuses'?'block':'none';?>">
	<span class="gray">(statuses not developed yet)</span>
</div>
<div id="sourceIndividualUsers" class="optionBox" style="display:<?php echo $DataSource=='Individual Users'?'block':'none';?>">
	Select users: <br />
	<select multiple="multiple" size="15" name="Contacts_ID[]" id="Contacts_ID">
	<?php
	if(!is_array($Contacts_ID))$Contacts_ID=explode(',',trim($Contacts_ID,','));
	if($a=q("SELECT c.ID, c.UserName, c.Active, c.LastName, c.FirstName, c.MiddleName, COUNT(h.ID) AS Orders FROM addr_contacts c LEFT JOIN finan_ClientsContacts cc ON c.ID=cc.Contacts_ID AND Type='Primary' LEFT JOIN finan_headers h ON cc.Clients_ID=h.Clients_ID GROUP BY c.ID HAVING c.Active>0 OR COUNT(h.ID)>0 ORDER BY IF(COUNT(h.ID)>0,1,2), c.LastName, c.FirstName", O_ARRAY)){
		foreach($a as $n=>$v){
			if($v['Orders']>0){
				if(!$orders){
					$orders=true;
					echo '<optgroup label="Have Orders">';
				}else{
				
				}
			}else{
				if(!$noorders){
					$noorders=true;
					if($orders)echo '</optgroup>';
					?><optgroup label="(No Orders)"><?php
				}
			}
			?><option value="<?php echo $v['ID'];?>" <?php echo in_array($v['ID'],$Contacts_ID)?'selected':''?>><?php echo $v['LastName'].', '.$v['FirstName'].($v['MiddleName']?' '.substr($v['MiddleName'],0,1):'').($v['Orders']>0?' ('.$v['Orders'].')':'');?></option><?php
		}
	}
	?></optgroup>
	</select>
</div>
<div id="sourceStaticLabels" class="optionBox" style="display:<?php echo $DataSource=='Static Labels'?'block':'none';?>">
	<h3>Static Labels</h3>
	<span class="gray">(All the same)</span>
	Specify a quantity to print: <input name="Quantity" type="text" id="Quantity" value="<?php echo $Quantity?$Quantity:10;?>" size="3" maxlength="3" />
	<br />

</div>
<?php

get_contents_tabsection('ljDataSource');

?>
Select Label: <select name="Labeltypes_ID" id="Labeltypes_ID" onChange="dChge(this);">
<option value="">&lt;Select..</option>
<?php
$selected=false;
if($a=q("SELECT * FROM aux_labels", O_ARRAY_ASSOC, $public_cnx))
foreach($a as $n=>$v){
	?><option value="<?php echo $n;?>" <?php if($n==$Labeltypes_ID)$selected='selected';?>><?php echo $v['Name'].' - '.h($v['Summary']);?></option><?php
}
?>
<option value="-1" <?php if(@array_sum($LabeltypesSettings)>0)echo 'selected';?>>(Custom Label Template)</option>
</select><br />
<br />
<?php
ob_start(); //form_field_translator
?>
<div class="optionBox">
  <h3>Label Page Dimensions </h3>
  <p><span class="gray">(All sizes in inches)</span><br />
  
  <table><tr><td style="padding:5px;">
    Page with: [input:pageWidth size=3 default=8.5 ]<br />
    Page height: [input:pageHeight size=3 default=11 ]<br />
    Label top corner: [input:labelTop size=3 ]<br />
    Label left corner: [input:labelLeft size=3 ]<br />
    Label width: [input:labelWidth size=3 ]<br />
    Label height: [input:labelHeight size=3 ] <br />
  </td><td style="padding:5px;">
	Label gutter right: [input:labelMarginRight size=3 ]<br />
	Label gutter bottom: [input:labelMarginBottom size=3 ]<br />
	Number of columns on page:
	<select name="LabeltypesSettings[cols]" id="LabeltypesSettings[cols]" onChange="dChge(this);">
	<?php
	for($i=1; $i<=50; $i++){
		?><option value="<?php echo $i;?>" <?php echo $i==$LabeltypesSettings['cols'] || ($i==2 && !isset($LabeltypesSettings['cols']))?'selected':'';?>><?php echo $i;?></option><?php
	}
	?>
	</select>
	<br />
	Number of rows on page:
	<select name="LabeltypesSettings[rows]" id="LabeltypesSettings[rows]" onChange="dChge(this);">
	<?php
	for($i=1; $i<=50; $i++){
		?><option value="<?php echo $i;?>" <?php echo $i==$LabeltypesSettings['rows'] || ($i==10 && !isset($LabeltypesSettings['rows']))?'selected':'';?>><?php echo $i;?></option><?php
	}
	?>
	</select>
	<br /> 
  </td></tr></table>
  </p>
</div>
<?php
$out=ob_get_contents();
ob_end_clean();
echo form_field_translator($out,array(
	'arrayString'=>'LabeltypesSettings',
));


get_contents_tabsection('ljTemplate');

?>
<h3>Layout CSS:</h3>
<p class="gray">(CSS will override default CSS and show once in the printed document)</p>

<textarea class="tabby" rows="3" cols="55" name="TemplateCSS" id="TemplateCSS" onChange="dChge(this);"><?php echo h($TemplateCSS); ?></textarea>
<br>
<br>
Layout HTML (use braces for <a href="#" onClick="return false;">variables</a>):<br>

<textarea class="tabby" rows="9" cols="75" name="TemplateHTML" id="TemplateHTML" onChange="dChge(this);"><?php echo h($TemplateHTML);?></textarea>
<br>

<?php
get_contents_tabsection('ljLayout');
?>
<h1>Print Ready</h1>
<p>Make sure you have everything in order before printing by using page preview!</p>
<p>Calibrate 1 inch to
  <input name="Pixels" type="text" id="Pixels" value="<?php echo $Pixels?$Pixels:96;?>" size="3" maxlength="3" /> 
  pixels (<a href="labels.calibrate.php" onClick="return ow(this.href,'l1_cal','800,600');">test this</a>)</p>
<p>
Start printing on row: <select name="StartRow" id="StartRow" onChange="dChge(this);">
<?php 
for($i=1; $i<=50; $i++){
	?><option value="<?php echo $i?>" <?php echo $StartRow==$i?'selected':''?>><?php echo $i;?></option><?php
}
?>
</select> and column: <select name="StartCol" id="StartCol" onChange="dChge(this);">
<?php 
for($i=1; $i<=6; $i++){
	?><option value="<?php echo $i?>" <?php echo $StartCol==$i?'selected':''?>><?php echo $i;?></option><?php
}
?>
</select>
<?php if(false){ ?><br />
Print: <select name="Duplicate" id="Duplicate" onChange="dChge(this);">
<option value="1">1</option>
<option value="2">2</option>
<option value="3">3</option>
</select> labels per record<br />
<?php } ?>
<br />
<br />
<?php if(false){ ?>
<label><input type="checkbox" name="SinglePageTest" value="1" onChange="dChge(this);" /> Run a single test page first</label>
&nbsp;&nbsp;

<label><input type="checkbox" name="AutoPrint" value="1" onChange="dChge(this);" /> Call document.print() automatically</label>
<?php } ?>
<br />
Print page and label borders: <select name="ShowBorders" id="ShowBorders" onChange="dChge(this);">
<option value="0">(Not at all)</option>
<option value="1" <?php echo $ShowBorders==1?'selected':''?>>In browser screen only</option>
<option value="2" <?php echo $ShowBorders==2?'selected':''?>>In browser screen and page printout</option>
</select>
<br />
<label><input type="checkbox" name="stupidChromeFix" id="stupidChromeFix" value="1" onChange="dChge(this);" />
Apply &quot;Stupid Chrome Fix&quot; for width and height</label>
<br />
<br />
<input name="PrintNow" type="submit" id="PrintNow" value="Print Now" />
&nbsp;&nbsp;<br />
<br />
<input type="hidden" name="StoreInHistory" value="0" />
<label>
<input name="StoreInHistory" type="checkbox" id="StoreInHistory" value="1" <?php echo $StoreInHistory?'checked':''?> onChange="dChge(this);" onClick="g('PrintComments').disabled=!this.checked; if(this.checked)g('PrintComments').focus();" />
Store this print job in history</label>
<br />
Comments for this print job <span class="gray">(optional)</span>: 
<input type="text" size="45" name="PrintComments" value="" id="PrintComments" onChange="dChge(this);" <?php echo !$StoreInHistory?'disabled':''?> /><br />
<br />
</p>
<?php

get_contents_tabsection('ljPrinting');


get_contents_tabsection('ljHistory');
?><div>
Notes on this print job:<br />
<p class="gray">Write any notes about layout or printer instructions here.</p>
<textarea rows="12" cols="75" name="Comments" id="Comments" onChange="dChge(this);"><?php echo h($Comments); ?></textarea>
</div><?php
get_contents_tabsection('ljNotes');

get_contents_tabsection('ljHelp');

tabs_enhanced(array(
	'ljDataSource'=>array(
		'label'=>'Data Source',
	),
	'ljTemplate'=>array(
		'label'=>'Label Type',
	),
	'ljLayout'=>array(
		'label'=>'Layout',
	),
	'ljPrinting'=>array(
		'label'=>'Print',
	),
	'ljHistory'=>array(
		'label'=>'History',
	),
	'ljNotes'=>array(
		'label'=>'Notes',
	),
	'ljHelp'=>array(
		'label'=>'Help',
	),
),
array('fade'=>true)
);

compend:
?>