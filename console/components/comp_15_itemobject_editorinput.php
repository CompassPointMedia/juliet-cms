<?php
/* --------
javascript taken from FEX 1.0.4
keep tight
--------- */



?><div id="editField"><?php
if($editorType=='textarea'){
	?><textarea name="Content" class="appearTextarea" style="width:272px;height:105px;" id="Content" onblur="obj_rename('blur',this)" onkeypress="obj_rename('keypress',this,event);"><?php echo $value?></textarea><?php
}else{
	?><input name="Content" type="text" id="Content" class="appearInput" value="<?php echo $value?>" onblur="obj_rename('blur',this)" onkeypress="obj_rename('keypress',this,event);" /><?php
}
?><input name="originalContent" type="hidden" id="originalContent" value="<?php echo $value?>" /><input name="submode" type="hidden" id="submode" value="editItemPicturesField" /><input name="field" type="hidden" id="field" value="<?php echo $field?>" /><input name="Tree_ID" type="hidden" id="Items_ID" value="<?php echo $Tree_ID;?>" /></div>