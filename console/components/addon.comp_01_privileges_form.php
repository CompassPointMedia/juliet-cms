<?php
if($addonInstalled){
	?>
	<h2>Add-on successfully installed</h2>
	<input type="button" name="Button" value="Close" onclick="window.close();" />
	<br>
	<?php
}else{
	?>
	<h2><?php echo $Name?></h2>
	<p>
	<?php echo $Description?>
	</p>
	
	<input name="addonSettings[Name]" type="hidden" id="addonSettings[Name]" value="<?php echo $mode==$insertMode ? $Name : $addonSettings['Name']?>">
	<input name="addonSettings[Description]" type="hidden" id="addonSettings[Description]" value="<?php echo h($mode==$insertMode ? $Description : $addonSettings['Description']);?>">
	<br>
	<label><input type="checkbox" name="addonSettings[Disabled]" value="1" <?php echo $addonSettings['Disabled']?'checked':''?> onchange="dChge(this);" />
	Disable this add-on </label><br>
	<br>
Label for permissions: 
<input name="addonSettings[FieldLabel]" type="text" id="FieldLabel" value="<?php echo h($addonSettings['FieldLabel'])?>" size="25" maxlength="45" onchange="dChge(this);" />
	<br />
	Help Description: <br />
<textarea name="addonSettings[FieldDescription]" cols="45" rows="2" id="FieldDescription" onchange="dChge(this);"><?php echo h($addonSettings['FieldDescription'])?></textarea>
	<br />
	<br />
	Place field on tab: <strong>Contacts</strong><br>
	Join Type: 
<select name="addonSettings[JoinType]" id="JoinType" onchange="toggleJoin(this,<?php echo $i?>);dChge(this);">
	<option value="manyToMany" <?php echo $addonSettings['JoinType']=='manyToMany'?'selected':''?>>Many to many</option>
</select>
	<br />
	<label>
	<input name="addonSettings[AllowAddNew]" type="checkbox" id="AllowAddNew" value="1" <?php echo $addonSettings['AllowAddNew']?'checked':''?> onchange="dChge(this);" />
	Allow add-new</label>
	<br />
	<br />
	
	Values table name: 
	<input name="addonSettings[ValueTableName]" type="text" id="ValueTableName" value="<?php echo h($addonSettings['ValueTableName'])?>" onchange="dChge(this);" />
	<br />
	Values table primary key: 
	<input name="addonSettings[ValueTablePK]" type="text" id="ValueTablePK" value="<?php echo h($addonSettings['ValueTablePK'])?>" size="7" onchange="dChge(this);" />
	<br />
	Values table label field: 
	<input name="addonSettings[ValueTableLabel]" type="text" id="ValueTableLabel" value="<?php echo h($addonSettings['ValueTableLabel'])?>" onchange="dChge(this);" />
	<br />
	Join table name: 
	<input name="addonSettings[JoinTable]" type="text" id="JoinTable" value="<?php echo h($addonSettings['JoinTable'])?>" onchange="dChge(this);" />
	<br />
	Join table foreign key for members table (addr_contacts): 
	<input name="addonSettings[JoinTableFKLocal]" type="text" id="JoinTableFKLocal" value="<?php echo h($addonSettings['JoinTableFKLocal'])?>" onchange="dChge(this);" />
	<br />
	Join table foreign key for values table: 
	<input name="addonSettings[JoinTableFKRemote]" type="text" id="JoinTableFKRemote" value="<?php echo h($addonSettings['JoinTableFKRemote'])?>" onchange="dChge(this);" />
	<br />
	<label>
	<input name="addonSettings[AllowMultiple]" type="checkbox" id="AllowMultiple" value="1" <?php echo $addonSettings['AllowMultiple']?'checked':''?> onchange="dChge(this);" />
	Allow multiple</label>
	<br />
	<label>
	<input name="addonSettings[AllowBlankOnUpdates]" type="checkbox" id="AllowBlankOnUpdates" value="1" <?php echo $addonSettings['AllowBlankOnUpdates']?'checked':''?> onchange="dChge(this);" />
	Allow no values </label> 
	on update
	<?php
	if($mode==$insertMode){
		?><script language="javascript" type="text/javascript">
		detectChange=1;
		</script>
<?php
	}
}
?>

