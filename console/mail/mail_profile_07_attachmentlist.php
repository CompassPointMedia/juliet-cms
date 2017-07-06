<?php
if(!$refreshComponentOnly){
	?><script language="javascript" type="text/javascript">
	function manageFileAttachment(mode,id){
		if(mode=='add'){
			ow();
		}else if(mode=='delete'){
			ow();
		}else if(mode=='download'){
			ow();
		}
		return false;
	}	
	</script>
	<style type="text/css">
	
	</style><?php
}
?>
<div id="fileAttachments"> 
<table width="100%" class="fileAttachments">
	<thead> 
	<tr> 
	<th>&nbsp;</th>
	<th>&nbsp;</th>
	<th>File Name</th>
	<th>Size</th>
	<th>Created</th>
	<th>Logic</th>
	</tr>
	</thead>
	<?php
	if($files=q("SELECT 5", O_ARRAY)){
		foreach($files as $n=>$v){
			?><tr id="f_<?php echo $v['ID'];?>">
			<td>[<a href="#" onclick="return manageFileAttachment('delete',<?php echo $v['ID']?>);">x</a>]</td>
			<td><!-- icon for file type and link to view or download --><a href="#" onClick="return manageFileAttachment('download',<?php echo $v['ID']?>"></a>&nbsp;</td>
			<td><a href="#" onclick="return manageFileAttachment('download',<?php echo $v['ID'?>);"><?php echo $v['Name']?></a></td>
			<td>
			<?php
			//now we get the file size from the virtual folder
			$x= @filesize($VOS_ROOT.'/'.$acct.'/'.$rd[VOSFileName]);
			//convert to a readable format
			if($x==0){
				echo 'O KB';
			}else if($x<=1024){
				echo '1KB';
			}else if($x<=(1024*1024)){
				echo floor($x/1024).'KB';
			}else{
				echo number_format( $x/(1024*1024), 2).'MB';
			}
			?>
			</td>
			<td><?php echo t($v['CreateDate'], f_qbks);?></td>
			<td><?php echo $v['Logic'] ? $v['Logic'] : '&nbsp;';?></td>
			</tr><?php
		}
	}else{
		?><tr>
		<td colspan="100%"><span class="gray">No file attachments for this mail profile.  Click Get File.. below to add files</span></td>
		</tr><?php
	}
	?>
</table>
<input type="button" name="Submit" value="Get File .." onclick="manageFileAttachment('add');" />
</div>