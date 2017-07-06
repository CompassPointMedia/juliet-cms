<script type="text/javascript" language="javascript">
$(function(){
	$("#Employee_ID").change(function(){
		var html=$.post('../resources/bais_01_exe.php', {mode: "listWeekHours"}, function(data){$("#weekHours").html(data);});
	})
});
</script>
<?php
if(!$refreshComponentOnly){
	?><script language="javascript" type="text/javascript">
	function interlockProjects(o){
		window.open('resources/bais_01_exe.php?mode=getClientProjects&Clients_ID='+o.value, 'w2');
	}
	
	</script><?php
}
$Clients=q("SELECT ID, IF(ClientName, ClientName, CompanyName) FROM finan_clients ORDER BY IF(ClientName, ClientName, CompanyName)", O_COL_ASSOC);
if($initial=$Clients_ID){
	//OK
}else if($Clients){
	foreach($Clients as $n=>$v){
		$initial=$n;
		break;
	}
	reset($Clients);
}
$Projects=q("SELECT ID, Name FROM finan_projects WHERE Clients_ID='$initial'", O_COL_ASSOC);
$Items=q("SELECT ID, CONCAT(SKU, ' - ', Name) FROM finan_items ORDER BY Name", O_COL_ASSOC);
$Employees=q("SELECT ID, FirstName, LastName FROM finan_employees",O_ARRAY);
?>
<div class="fr" style="width:175px;background-color:oldlace;min-height:100px;">
	Status here (new, review, submitted, etc.)

</div>
Employee:
<select name="Employee_ID" id="Employee_ID" onchange="dChge(this);">
	<option value="">&lt;Select..&gt;</option>
	<?php
	foreach($Employees as $n=>$v){
		?>
		<option value="<?php echo $v['ID']?>" <?php echo $Employee_ID==$v['ID']?'selected':''?>><?php echo $v['FirstName'].' '.$v['LastName'];?></option>
		<?php
	}
	?>
	<option value="{RBADDNEW}">&lt;Add new..&gt;</option>
</select>
Client: 
<select name="Clients_ID" id="Clients_ID" onfocus="bufferClient=this.value" onchange="dChge(this);interlockProjects(this);">
	<option value="">&lt;Select..&gt;</option>
	<?php
	foreach($Clients as $n=>$v){
		?><option value="<?php echo $n?>" <?php echo $Clients_ID==$n?'selected':''?>><?php echo h($v);?></option><?php
	}
	?>
	<option value="{RBADDNEW}">&lt;Add new..&gt;</option>
</select>
<br />
Project: 
<span id="projectsWrap">
<select name="Projects_ID" id="Projects_ID" onfocus="bufferClient=this.value" onchange="dChge(this);">
	<option value="">&lt;Select..&gt;</option>
	<?php
	foreach($Projects as $n=>$v){
		?><option value="<?php echo $n?>" <?php echo $Projects_ID==$n?'selected':''?>><?php echo h($v);?></option><?php
	}
	?>
	<option value="{RBADDNEW}">&lt;Add new..&gt;</option>
</select>
</span>
<br />
Item: 
<select name="Items_ID" id="Items_ID" onfocus="bufferClient=this.value" onchange="dChge(this);">
	<option value="">&lt;Select..&gt;</option>
	<?php
	foreach($Items as $n=>$v){
		?><option value="<?php echo $n?>" <?php echo $Items_ID==$n?'selected':''?>><?php echo h($v);?></option><?php
	}
	?>
	<option value="{RBADDNEW}">&lt;Add new..&gt;</option>
</select>
<br />
<br />
Date worked: 
<img src="/images/i/calendar1.png" class="calIcon" alt="select date" title="click this to select a date" onclick="show_cal(this,this.nextSibling.id);" align="absbottom" />
<input name="StartTimeDay" type="text" id="StartTimeDay" value="<?php echo $StartTimeDay ? t($StartTimeDay, f_qbks) : date('m/d/Y');?>" onchange="dChge(this);" />
<br />
Time started: 
<input name="StartTimeHour" type="text" id="StartTimeHour" value="<?php echo $StartTimeHour ? t($StartTimeHour, f_qbks) : date('g:iA');?>" size="12" onchange="dChge(this);" />
<br />
Time completed:
<input name="EndTimeHour" type="text" id="EndTimeHour" value="<?php echo $EndTimeHour ? t($EndTimeHour, f_qbks) : date('g:iA');?>" size="12" onchange="dChge(this);" /> 
&nbsp;&nbsp;
<span>
<input name="TimerOn" type="checkbox" id="TimerOn" value="1" onclick="toggleTimer(this);" onchange="dChge(this);" />
Timer on </span><br />
Total:
<input name="TotalHours" type="text" id="TotalHours" value="<?php echo $TotalHours ? number_format($TotalHours,2) : ''?>" size="12" onchange="dChge(this);" />
&nbsp;&nbsp;&nbsp;
Total Billable: 
<input name="BillableHours" type="text" id="BillableHours" value="<?php echo $BillableHours ? number_format($BillableHours,2) : ''?>" size="12" onchange="dChge(this);" />
&nbsp;&nbsp;&nbsp;
Total Payable: 
<input name="PayableHours" type="text" id="PayableHours" value="<?php echo $PayableHours ? number_format($PayableHours,2) : ''?>" size="12" onchange="dChge(this);" />
<br />
<br />
<label>
<input name="BillableFlag" type="checkbox" id="BillableFlag" value="1" <?php echo $BillableFlag || !isset($BillableFlag) ? 'checked':''?> onchange="dChge(this);" />
Billable</label>
<br />
What was done:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Measurable Goals:<br />
<textarea name="Description" cols="55" rows="7" id="Description" onchange="dChge(this);"><?php echo h($Description);?></textarea>

<textarea name="Achievements" cols="55" rows="7" id="Achievements" onchange="dChge(this);"><?php echo h($Achievements);?></textarea>
<br />
<br />
<div id="weekHours">
</div>