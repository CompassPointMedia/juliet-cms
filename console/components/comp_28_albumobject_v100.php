<?php
//array is $file
//KEEP THESE TIGHT..

//-------------------- get the path ------------------
if(!function_exists('tree_id_to_path')){
	function tree_id_to_path($n){
		global $tree_id_to_path;
		$row=q("SELECT Tree_ID, Name, Type FROM relatebase_tree WHERE ID='$n'", O_ROW);
		if($row['Type']=='file')$tree_id_to_path=$row;
		return ($row ? tree_id_to_path($row['Tree_ID']).'/'.$row['Name'] : '');
	}
}
unset($tree_id_to_path);
if(file_exists($_SERVER['DOCUMENT_ROOT'].($file['src']=tree_id_to_path($file['Tree_ID']))) && strlen($file['src'])){
	$file['nofile']=0;
	$file['size']=round(filesize($_SERVER['DOCUMENT_ROOT'].$file['src'])/1024,2);
	if(count($tree_id_to_path))foreach($tree_id_to_path as $n=>$v)$file[$n]=$v;
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
exit;
?><div id="r_<?php echo $file['ID']?>" class="albumObject"><div id="temp1" class="dummy"><div id="temp2" title="Click to delete this album frame" class="normal" onmouseover="this.className='hover';" onmouseout="this.className='normal'" onclick="deleteObject(<?php echo $file['ID']?>);">x</div></div><div id="title_<?php echo $file['ID']?>" class="title" onmouseover="edit(this,1);" onmouseout="edit(this,0);"><div id="temp3" class="editor" style="display:none;" onmouseover="edith(this,1);" onmouseout="edith(this,0);" onclick="editi(this,'input','Title');">edit</div><div id="temp4"><?php echo $file['Title']?></div></div><div id="imgInset_<?php echo $file['ID']?>-testing" class="img"><img 
	src="<?php echo str_replace($file['Name'],'.thumbs.dbr/'.$file['Name'],$file['src']);?>" 
	alt="picture or file" 
	onclick="hm_cxlseq=2;showmenuie5(event);" 
	style="cursor:pointer;" 
	filename="<?php echo $file['Name']?>"
	filepath="<?php echo substr($file['src'],0,strlen($file['src'])-strlen($file['name']));?>" 
	size="<?php echo $file['size']?>" 
	nofile="<?php echo $file['nofile']?>" 
	noimage="<?php echo $file['noimage']?>" 
	dims="<?php echo $file['width'].','.$file['height']?>" 
	description=" " 
	type="<?php echo $file['mime'];?>"
	/></div><div id="desc_<?php echo $file['ID']?>" class="albdesc" onmouseover="edit(this,1);" onmouseout="edit(this,0);"><div id="temp5" class="editor" style="display:none;" onmouseover="edith(this,1);" onmouseout="edith(this,0);" onclick="editi(this,'textarea','Description');">edit</div><div><?php echo $file['Description']?></div></div><div class="cb">&nbsp;</div></div>