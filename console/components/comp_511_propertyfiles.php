<?php
if(!$Properties_ID){
	prn('no prop id');
	$Properties_ID=$ID;
}

//initial setup
$node=$_SERVER['DOCUMENT_ROOT'].'/images/documents/filesystem'; //however files can be located all over
if(!function_exists('create_thumbnail'))require($FUNCTION_ROOT.'/function_create_thumbnail_v200.php');
if(!is_dir($node) && !mkdir($node)){
	$msg='Unable to create system folder '.$_SERVER['DOCUMENT_ROOT'].'/images/documents';
	mail($developerEmail,'error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
	error_alert($msg);
}
if(!is_dir($node.'/.thumbs.dbr') && !mkdir($node.'/.thumbs.dbr')){
	$msg='Unable to create system folder '.$_SERVER['DOCUMENT_ROOT'].'/images/documents/.thumbs.dbr';
	mail($developerEmail,'error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
	error_alert($msg);
}


//controls
if($submode=='propFilesRefresh'){
	if(!($Folder_ID=q("SELECT o.ID FROM gen_objects o WHERE o.ParentObject='re1_properties' AND o.Rlx='Files and Documents' AND o.Objects_ID='$Properties_ID'", O_VALUE))){
		$Folder_ID=q("INSERT INTO gen_objects SET
		ParentObject='re1_properties',
		Objects_ID=$Properties_ID,
		Rlx='Files and Documents',
		CreateDate=NOW(),
		Creator='".sun()."'", O_INSERTID);
	}
	$Objects_ID=q("INSERT INTO gen_objects SET ParentObject='gen_objects', Objects_ID=$Folder_ID, CreateDate=NOW(), Creator='".sun()."'", O_INSERTID);
	q("INSERT INTO relatebase_ObjectsTree SET Objects_ID=$Objects_ID, ObjectName='gen_objects', Tree_ID=$Tree_ID, CreateDate=NOW(), Creator='".sun()."'");
}else if($submode=='deleteFile'){
	if($file=q("SELECT Name FROM relatebase_tree WHERE ID='$Tree_ID'", O_ROW)){
		$Objects_ID=q("SELECT Objects_ID FROM relatebase_ObjectsTree WHERE Tree_ID=$Tree_ID AND ObjectName='gen_objects'", O_VALUE);
		$path=$_SERVER['DOCUMENT_ROOT'].tree_id_to_path($Tree_ID);
		q("DELETE FROM relatebase_ObjectsTree WHERE Tree_ID=$Tree_ID");
		q("DELETE FROM gen_objects WHERE ID='$Objects_ID'");
		q("DELETE FROM relatebase_tree WHERE ID='$Tree_ID'");
		unlink($path);
		?><script language="javascript" type="text/javascript">
		window.parent.g('r_<?php echo $Tree_ID;?>').style.display='none';
		</script><?php
	}else{
		error_alert('unable to locate file for deletion',1);
	}
}


if($files=q("SELECT 
LCASE(t.Name) AS node,
ot.Tree_ID,
o2.Category,
t.Name,
t.FileSize,
t.FileWidth,
t.FileHeight,
IF(c.UserName IS NOT NULL, CONCAT(c.LastName,', ',c.FirstName), t.Creator) AS Creator,
t.CreateDate,
t.Editor,
t.EditDate
FROM
gen_objects o 
JOIN gen_objects o2 ON o.Rlx='Files and Documents' AND o2.Objects_ID=o.ID AND o2.ParentObject='gen_objects'
JOIN relatebase_ObjectsTree ot ON ot.Objects_ID=o2.ID AND ot.ObjectName='gen_objects'
JOIN relatebase_tree t ON ot.Tree_ID=t.ID
LEFT JOIN addr_contacts c ON t.Creator=c.UserName
WHERE
o.Objects_ID='$Properties_ID' AND
o.ParentObject='re1_properties'", O_ARRAY_ASSOC)){
	foreach($files as $n=>$v){
		$p=tree_id_to_path($v['Tree_ID']);
		if(!file_exists($_SERVER['DOCUMENT_ROOT'].($p))){
			mail($developerEmail,'file deleted on the fly in '.__FILE__,get_globals(),$fromHdrBugs);
			continue;
			//remove on the fly
			q("DELETE FROM relatebase_tree WHERE ID='".$v['Files_ID']."'");
			unset($files[$n]);
		}
	}
}
if(!$refreshComponentOnly){
	?><style type="text/css">
	.fileList{
		border-collapse:collapse;
		background-color:#fff;
		border:1px solid #000;
		}
	.fileList th{
		background-color:#ccc;
		color:#000;
		padding:3px 4px 1px 5px;
		}
	.fileList td{
		background-image:url("/images/i/grad/linedottedhoriz-444.png");
		background-position:center bottom;
		background-repeat:repeat-x;
		
		padding:1px 4px 1px 5px;
		}
	.fileList .catRow{
		background-image:url("/images/i/grad/linesolidhoriz-000.png");
		background-position:center bottom;
		background-repeat:repeat-x;
		}
	.fileList td.last{
		padding-right:20px;
		}
	.fileList .icon img{
		padding:2px;
		border:1px solid #ccc;
		margin:1px;
		}
	#propertyFiles{
		margin-bottom:20px;
		}
	</style>
	<script language="javascript" type="text/javascript">

	</script><?php
}
?>
<div id="propertyFiles" refreshparams="noparams">
	<input type="button" name="Button" value="Add a File or Brochure.." onclick="return ow('file_loader.php?_cb_Properties_ID=<?php echo $Properties_ID;?>&submode=componentControls&subsubmode=propFilesRefresh&CategoryGroup=PropertyDocumentationCategories&_cb_file=comp_511_propertyfiles.php&_cb_location=CONSOLE_COMPONENT_ROOT','l1_upload','450,400')" />
	<table class="fileList" width="100%">
	<thead>
	<tr>
	<th>&nbsp;</th>
	<th>&nbsp;</th>
	<th>Name</th>
	<th>Size</th>
	<th>Created</th>
	<th>by..</th>
	</tr>
	</thead>
	<tbody>
	<?php
	if($files){
		foreach($files as $n=>$v){
			$Key=current(explode('_',$v['Name']));
			if($v['Category']!==$catBuffer || !isset($catBuffer)){
				$catBuffer=$v['Category'];
				?><tr class="catRow">
				<td colspan="100%"><h3 class="nullBottom"><?php echo $v['Category']?></h3></td>
				</tr><?php
			}
			?><tr id="r_<?php echo $v['Tree_ID'];?>">
			<td valign="middle" align="center" class="icon"><?php
			$showThumb=false;
			if(preg_match('/(jpg|png|gif)$/i',$v['Name'])){
				if($gis=@getimagesize($_SERVER['DOCUMENT_ROOT'].'/images/documents/.thumbs.dbr/'.$v['Name'])){
					$showThumb=true;
					$width=$height=95;
				}else if(create_thumbnail(
					$_SERVER['DOCUMENT_ROOT'].'/images/documents/'.$v['Name'],
					$shrink='95,95', 
					$crop='', 
					$_SERVER['DOCUMENT_ROOT'].'/images/documents/.thumbs.dbr/'.$v['Name'])){
					$showThumb=true;
					$width=$height=95;
				}
			}
			if($showThumb){
				?><img src="/images/reader.php?Tree_ID=<?php echo $v['Tree_ID']?>&Key=<?php echo $Key?>&thumbnail=default" alt="thumbnail view" /><?php
			}else{
				?>&nbsp;<?php
			}
			?></td>
			<td>[<a href="resources/bais_01_exe.php?mode=downloadFile&Tree_ID=<?php echo $v['Tree_ID']?>&file=<?php echo urlencode($v['Name']);?>&suppressPrintEnv=1" target="w2">view</a>]&nbsp;[<a href="resources/bais_01_exe.php?mode=componentControls&submode=deleteFile&Properties_ID=<?php echo $Properties_ID;?>&file=<?php echo end(explode('/',__FILE__));?>&location=CONSOLE_COMPONENT_ROOT&Tree_ID=<?php echo $v['Tree_ID'];?>" target="w2" onclick="return confirm('Are you sure you want to delete this file?');">delete</a>]</td>
			<td><?php echo preg_replace('/^[a-f0-9]+_/i','',$v['Name']);?></td>
			<td><?php echo number_format($v['FileSize']/1024,2).'K';?></td>
			<td><?php echo date('n/j/y',strtotime($v['CreateDate']));?></td>
			<td class="last"><?php echo $v['Creator']?></td>
			</tr><?php
		}
	}else{
		?><tr>
		<td colspan="100%"><em style="color:#aaa;">No files present or viewable</em></td>
		</tr><?php
	}
	?>
	</tbody>
	</table>
</div>
<?php
if($refreshComponentOnly){
	?><script language="javascript" type="text/javascript">
	window.parent.opener.g('propertyFiles').innerHTML=document.getElementById('propertyFiles').innerHTML;
	</script><?php
}
?>