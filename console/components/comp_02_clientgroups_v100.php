<?php
//just need Client_ID
$objectWord='Group';
$objectWordPlural='Groups';
?>


<?php if(!$refreshComponentOnly){ ?>
<style type="text/css">
#clientGroup{
	border:1px dotted darkred;
	padding:15px;
	clear:both;
	}
</style>
<script type="text/javascript" language="javascript">
function removeClientGroup(Clients_ID, Groups_ID){
	if(!confirm('This will remove this <?php echo strtolower($objectWord)?> for this person.  Are you sure?'))return false;
	g('grp_'+Groups_ID).style.display='none';
	window.open('/console/resources/bais_01_exe.php?mode=removeClientGroup&ObjectName=finan_clients&Clients_ID='+Clients_ID+'&Groups_ID='+Groups_ID, 'w2');
	return false;
}
function addClientGroup(){
	ow('members_groups.php?Clients_ID='+g('ID').value+'&cbFunction=refreshComponent&cbParam=fixed:clientGroup','l1_grps','550,500');
}
/*
function refreshclientGroup(){
	window.open('/console/resources/bais_01_exe.php?mode=refreshClientGroup&Clients_ID='+ID, 'w2');
	return false;
}
*/
</script>
<?php }?>

<div id="clientGroup" refreshParams="Clients_ID:ID">
<strong><?php echo $objectWord?>(s)</strong>:<br />
<?php
if($a=q("SELECT g.ID, g.Name FROM addr_ContactsGroups cg, addr_groups g WHERE cg.Groups_ID=g.ID AND cg.Contacts_ID=$Clients_ID AND cg.Contacts_DataSource='finan_clients' ORDER BY g.Name", O_COL_ASSOC)){
	foreach($a as $n=>$v){
		?><span id="grp_<?php echo $n?>"><?php echo trim(h($v)) ? h($v) : '<em>(none)</em>'?> &nbsp; [<a title="remove this <?php echo strtolower($objectWord);?>" href="#" onClick="return removeClientGroup(<?php echo $Clients_ID?>,<?php echo $n?>);">remove</a>] <br /></span><?php
	}
}else{
	?><em>(no <?php echo strtolower($objectWordPlural);?> present)</em><br /><?php
}
?>
<input type="button" name="button" value="Edit <?php echo $objectWordPlural?>" onClick="addClientGroup()" />
</div>