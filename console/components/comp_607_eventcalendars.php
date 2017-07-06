<?php
/* copied from GiocosaCare 2010-12-18 */
if($mode=='editCalendars'){
	if($submode=='removeCalendar'){
		if(q("SELECT COUNT(*) FROM cal_events WHERE Cal_ID=$Cal_ID", O_VALUE))error_alert('This calendar has events associated with it and cannot be deleted');
		q("DELETE FROM cal_cal WHERE ID=$Cal_ID");
	}else{
		if($Cal_ID=$ID){
			if(!$Name)error_alert('Enter a name for the calendar');
			if(q("SELECT * FROM cal_cal WHERE Name='$Name' AND ID!=$ID", O_ROW))error_alert('This calendar name is already in use');
			q("UPDATE cal_cal SET
			Name='$Name',
			Identifier='$Identifier',
			ColorCode='$ColorCode',
			Description='$Description',
			EditDate=NOW(),
			Editor='".$_SESSION['admin']['userName']."'
			WHERE ID=$ID");
		}else{
			$Cal_ID=q("INSERT INTO cal_cal SET
			Name='$Name',
			Identifier='$Identifier',
			ColorCode='$ColorCode',
			Description='$Description',
			CreateDate=NOW(),
			Creator='".$_SESSION['admin']['userName']."'", O_INSERTID);
		}
	}
}
if(!$refreshComponentOnly){
	?>
	<style type="text/css">
	.color{
		color:#555;
		}
	.cals th{
		padding:3px 3px 1px 5px;
		background-color:#e6f0e6;
		border-bottom:1px solid #000;
		}
	.cals td{
		padding:3px 3px 1px 5px;
		border-bottom:1px dotted #333;
		}
	#calEntry{
		background-color:#e6f0e6;
		padding:15px;
		margin:5px 0px;
		width:450px;
		border:1px dotted #333;
		}
	</style>
	<script language="javascript" type="text/javascript">
	function editCal(n){
		if(typeof n=='undefined'){
			g('calStatus').innerHTML='Add New Calendar';
			g('Name').value='';
			g('Identifier').value='';
			g('ColorCode').value='';
			g('Description').value='';
			g('ID').value='';
			g('SubmitButton').value='Add Calendar';
		}else{
			g('calStatus').innerHTML='Edit Calendar';
			g('Name').value=g('name'+'_'+n).innerHTML;
			g('Identifier').value=g('id'+'_'+n).innerHTML;
			g('ColorCode').value=g('ccode'+'_'+n).innerHTML;
			g('Description').value=g('desc'+'_'+n).innerHTML;
			g('ID').value=n;
			g('SubmitButton').value='Update Calendar';
		}
		return false;
	}
	function getColor(o){
		o.className='';
		if(o.value=='(click to select)')o.value='';
		return false;
	}
	function removeCalendar(n){
		if(!confirm('This will remove this calendar.  Are you sure?'))return false;
		window.open('resources/bais_01_exe.php?mode=editCalendars&submode=removeCalendar&Cal_ID='+n,'w2');
		return false;
	}
	function showBGColor(){
		/*
		*/
		g('ColorCode').style.backgroundColor='#'+g('ColorCode').value;
		g('ColorCode').style.border='1 px solid #ccc';
		setTimeout('showBGColor();',500);
	}
	</script>
	<?php
}
?>
<div id="eventCalendars">
	<div id="calEntry">
	<h3 id="calStatus" class="nullBottom">Add New Calendar</h3>
    <div id="color-picker"></div>

	<p>Calendar Name: 
	<input name="Name" type="text" id="Name" size="35" />
	<br />
	Identifier (optional): 
	<input name="Identifier" type="text" id="Identifier" size="7" />
	<br />

	<div id="content">
	Color Code <em>(click to select)</em>: <input type="text" id="ColorCode" readonly="" name="ColorCode" class="color" style="cursor:pointer; padding-left:7px;" value="FFFFFF" />
	<div id="color-picker"></div>
	</div>
	<?php if(!$refreshComponentOnly){ ?>
	<script language="javascript" type="text/javascript">
	setTimeout('showBGColor()',500);
	</script>
	<?php } ?>
	<div class="cb">
	Description:<br />
	<textarea name="Description" cols="45" rows="2" id="Description"></textarea>
	</div>
	<input id="SubmitButton" type="submit" name="Submit" value="Add Calendar" />
	<input id="Submit" type="button" name="Submit2" value="Cancel" onclick="window.close()" />
	<input name="ID" type="hidden" id="ID" />
	<input name="mode" type="hidden" id="mode" value="editCalendars" />
	</p>
	</div>
    <h3 class="nullBottom">Existing Calendars</h3>
	<?php
	$calendars=q("SELECT
	a.*
	FROM cal_cal a 
	ORDER BY a.Name", O_ARRAY);
	?>
	<table class="cals">
	<thead>
	<tr>
	<th>&nbsp;</th>
	<th>Color</th>
	<th>Name</th>
	<th>Ident.</th>
	<th>Description</th>
	<th>Events</th>
	</tr>
	</thead>
	<?php
	if(!$calendars){
	?><tfoot><tr><td colspan="101"><em>(No calendars are set up.  Enter a calendar above)</em></td></tr></tfoot><?php
	}
	?>
	<tbody>
	<?php
	if($calendars)
	foreach($calendars as $calendar){
	extract($calendar);
	?><tr id="r<?php echo $ID?>">
		<td nowrap="nowrap">[<a href="#" onclick="return editCal(<?php echo $ID?>);" title="Edit the name and color for this calendar">edit</a>] &nbsp;&nbsp;[<a href="#" onclick="return removeCalendar(<?php echo $ID?>);" title="Remove this calendar (only allowed if events have not been added">remove</a>]</td>
		<td style="background-color:#<?php echo $ColorCode?>;color:#<?php echo $ColorCode?>;" id="ccode_<?php echo $ID?>"><?php echo $ColorCode?></td>
		<td nowrap="nowrap" id="name_<?php echo $ID?>"><?php echo $Name?></td>
		<td id="id_<?php echo $ID?>"><?php echo $Identifier?></td>
		<td id="desc_<?php echo $ID?>"><?php echo $Description?></td>
		<td id="count_<?php echo $ID?>" class="tac"><?php echo ($n=q("SELECT COUNT(*) FROM cal_events WHERE Cal_ID=$ID AND ResourceType IS NOT NULL", O_VALUE)) ? number_format($EventCount) : '&mdash;'?></td>
	</tr><?php
	}
	?>
	</tbody>
	</table>
</div>
<?php
if($mode=='editCalendars'){
	?><script language="javascript" type="text/javascript">
	window.parent.g('eventCalendars').innerHTML=document.getElementById('eventCalendars').innerHTML;
	</script><?php
}
?>