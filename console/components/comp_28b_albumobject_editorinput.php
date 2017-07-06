<?php
/* --------
javascript taken from FEX 1.0.4
keep tight
--------- */



?><div id="editField"><form action="resources/bais_01_exe.php" method="get" id="editForm" onsubmit="return obj_rename('submit',this);" target="w2"><?php
if($editorType=='textarea'){
	?><textarea name="Content" class="appearTextarea" style="width:272px;height:105px;" id="Content" onblur="obj_rename('blur',this)" onkeypress="obj_rename('keypress',this,event);"><?php echo $value?></textarea><?php
}else{
	?><input name="Content" type="text" id="Content" class="appearInput" value="<?php echo $value?>" onblur="obj_rename('blur',this)" onkeypress="obj_rename('keypress',this,event);" /><?php
}
?><input name="originalContent" type="hidden" id="originalContent" value="<?php echo $value?>" />

	<input name="mode" type="hidden" id="mode" value="manageAlbums" />
	<input name="submode" type="hidden" id="submode" value="editField" />
	<input name="field" type="hidden" id="field" value="<?php echo $field?>" />
	<input name="Albums_ID" type="hidden" id="Albums_ID" value="<?php echo $Albums_ID;?>" />
	<input name="ID" type="hidden" id="ID" value="<?php echo $ID?>" />
</form>
</div>