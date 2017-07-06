<?php
//array is $file
//KEEP THESE TIGHT..
//-------------------- get the path ------------------
unset($tree_id_to_path);
if(file_exists($_SERVER['DOCUMENT_ROOT'].($file['src']=tree_id_to_path($file['ID']))) && strlen($file['src'])){
	$file['nofile']=0;
	$file['size']=round(filesize($_SERVER['DOCUMENT_ROOT'].$file['src'])/1024,2);
	if(count($tree_id_to_path))foreach($tree_id_to_path as $n=>$v){
		$file[$n]=$v;
	}
	if($g=@getimagesize($_SERVER['DOCUMENT_ROOT'].$file['src'])){
		$file['mime']=$g['mime'];
		$file['width']=$g[0];
		$file['height']=$g[1];
		$file['noimage']=0;
	}else{
		//this is a document, we need a representation
		$file['noimage']=1;
	}
}else{
	$file['nofile']=1;
}
?>
<div id="r_<?php echo $file['ID']?>" class="itemObject" style="position:relative;"><a name="picture<?php echo $file['ID']?>"></a><div class="del" title="Remove this frame from the item" onmouseover="this.className='del hl';" onmouseout="this.className='del';" onclick="deleteItemPictures(this);">x</div><div id="title_<?php echo $file['ID']?>" class="title" onmouseover="edit(this,1);" onmouseout="edit(this,0);"><div class="editor" style="display:none;" onmouseover="edith(this,1);" onmouseout="edith(this,0);" onclick="editi(this,'input','Title');">edit</div><div><?php echo trim($file['Title']) ? $file['Title'] : '&nbsp;';?></div></div><div id="imgInset_<?php echo $file['ID']?>-testing" class="img" onclick="hm_cxlseq=2;showmenuie5(event);"><img 
	src="<?php echo str_replace($file['Name'],'.thumbs.dbr/'.$file['Name'],$file['src']);?>" 
	alt="picture or file" 
	style="cursor:pointer;" 
	filename="<?php echo $file['Name']?>"
	filepath="<?php echo substr($file['src'],0,strlen($file['src'])-strlen($file['name']));?>" 
	size="<?php echo $file['size']?>" 
	nofile="<?php echo $file['nofile']?>" 
	noimage="<?php echo $file['noimage']?>" 
	dims="<?php echo $file['width'].','.$file['height']?>" 
	description=" " 
	mime="<?php echo $file['mime'];?>"
	/></div>
	<div class="albIdx"><img id="idx_<?php echo $file['ID']?>" src="/images/i/arrows/spinner-orange.png" title="Click to move this row up or down; hold down the Ctrl key to move to the top or bottom" width="14" height="19" onclick="indexRow(this,event);" /></div>
	<div id="desc_<?php echo $file['ID']?>" class="albdesc" onmouseover="edit(this,1);" onmouseout="edit(this,0);"><div class="editor" style="display:none;" onmouseover="edith(this,1);" onmouseout="edith(this,0);" onclick="editi(this,'textarea','Description');">edit</div><div><?php echo $file['Description'] ? $file['Description'] : '(description)';?></div></div>
	<div class="cb">&nbsp;</div>
</div>
