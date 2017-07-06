<?php

$dataset='ItemPictures'; #more of a concept
$datasetComponent='ItemPicturesFocus'; #THIS physical component
$datasetGroup=$dataset;
if(!$datasetWord)$datasetWord='Picture';
if(!$datasetWordPlural)$datasetWordPlural='Pictures';
$modApType='embedded';
$modApHandle='first';

//standard fields
$availableCols[$datasetGroup][$modApType][$modApHandle]=array(
	'extra'=>array(
		'sqlPictureCollection'=>'SELECT 
a.Title, a.Description, b.ID, b.Tree_ID, b.Name, b.Name AS src
FROM relatebase_ObjectsTree a, relatebase_tree b 
WHERE a.Tree_ID=b.ID AND a.ObjectName=\'finan_items\' AND a.Objects_ID=\'$ID\'',
	)
);
$availableCols[$datasetGroup]=array_merge_accurate($availableCols[$datasetGroup], $mergeAvailableCols[$datasetGroup]);
extract($availableCols[$datasetGroup][$modApType][$modApHandle]['extra']);

$processAllObjects=true;
switch(true){
	case $submode=='addItemObject':
		//DO NOT update item or fulfill resourcetype - only when item submitted
		
		//cleanup - this globally cleans up ALL items
		q("DELETE FROM relatebase_ObjectsTree WHERE Tree_ID IN('".@implode("','",q("SELECT a.Tree_ID FROM relatebase_ObjectsTree a LEFT JOIN relatebase_tree b ON a.Tree_ID=b.ID WHERE b.ID IS NULL", O_COL))."')");
		prn($qr);
		if($qr['affected_rows'])mail($developerEmail,'error file '.__FILE__.', line '.__LINE__,get_globals('Item cleanup, remember I changed the structure'),$fromHdrBugs);

		//populate the picture fields
		#new setting
		$autoincPictureTitleTable='relatebase_ObjectsTree ot LEFT JOIN relatebase_tree t ON ot.Objects_ID='.$Items_ID.' AND ot.ObjectName=\'finan_items\' AND ot.Tree_ID=t.ID';
		$tdtable='ot';

		$Title=sql_autoinc_text($autoincPictureTitleTable, $tdtable.'.Title', 'Untitled Picture ', $options=array(
			'where'=>'Objects_ID='.$Items_ID.' AND ObjectName=\'finan_items\'',
			'returnLowerCase'=>false,
			'leftSep'=>' '
		));
		$Description='(no description - click to edit)';


		$Tree_ID=q("INSERT INTO relatebase_tree SET EditDate=NOW()", O_INSERTID);

		#new setting
		if($Relationship=='Primary Image'){
			q("UPDATE relatebase_ObjectsTree SET Relationship='Image' WHERE Relationship='Primary Image' AND ObjectName='finan_items' AND Objects_ID=$Items_ID");
		}else{
			
		}
		q("INSERT INTO relatebase_ObjectsTree SET 
		Objects_ID=$Items_ID, 
		ObjectName='finan_items', 
		Tree_ID=$Tree_ID, 
		Title='$Title', 
		Relationship='".($Relationship ? $Relationship : (q("SELECT COUNT(*) FROM relatebase_ObjectsTree WHERE ObjectName='finan_items' AND Objects_ID='$Items_ID' AND Relationship='Primary Image'", O_VALUE) ? 'Image' : 'Primary Image'))."',
		Description='$Description'");

		$file=array(
			'ID'=>$Tree_ID,
			'Title'=>$Title,
			'Description'=>$Description
		);
		?><div id="fill"><?php
		require($COMPONENT_ROOT.'/comp_15_itemobject_v102.php');
		?></div><?php
		?><script language="javascript" type="text/javascript">
		window.parent.g('itemObjects').innerHTML = <?php if($newframesontop){ ?>document.getElementById('fill').innerHTML+window.parent.g('itemObjects').innerHTML<?php }else{ ?>window.parent.g('itemObjects').innerHTML + document.getElementById('fill').innerHTML<?php }?>;
		//scrollto
		try{ window.parent.g('itemObjects').scrollTo(<?php echo $newframesontop ? 0 : 10000 ?>); }catch(e){ }
		</script><?php
		$processAllObjects=false;
	break;
	case $submode=='assignPicture':
		if(!preg_match('/imgInset_([0-9]+)/',$fOBoundToElement,$a)){
			mail($developerEmail,'error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
			error_alert('No id detected');
		}
		$ID=$a[1];
		if(!$fmwFile || !$fmwExt)error_alert('No file data passed!');
		//DO NOT update item and fulfill resourcetype

		//cleanup - this globally cleans up ALL items
		q("DELETE FROM relatebase_ObjectsTree WHERE Tree_ID IN('".@implode("','",q("SELECT a.Tree_ID FROM relatebase_ObjectsTree a LEFT JOIN relatebase_tree b ON a.Tree_ID=b.ID WHERE b.ID IS NULL", O_COL))."')");
		prn($qr);
		if($qr['affected_rows'])mail($developerEmail,'error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
		
		if(!($record=q("SELECT * FROM relatebase_tree WHERE ID='".$ID."'", O_ROW))){
			?><script language="javascript" type="text/javascript">
			window.parent.g('<?php echo $fOBoundToElement?>').parentNode.style.display='none';
			</script><?php
			mail($developerEmail,'error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
			error_alert('unable to find this object anymore');
		}
		$file=$fmwFile.'.'.$fmwExt;
		//---------------------------------- TREE CODING -------------------------------------------
	
		$Tree_ID=tree_build_path(
			'images/'.trim($fmwPath,'/'), 
			$options=array('lastNodeType'=>'folder', 'lastNodeValues'=>array('Description'=>'file node created by bais_01_exe.php:mode=assignPicture'))
		);
		if(preg_match('/untitled picture\s*[0-9]*/i',$record['Title'])){
			$updateTitle=", Title='".$file."'";
		}
		q("UPDATE relatebase_tree SET Tree_ID=$Tree_ID, Name='$file' WHERE ID='".$record['ID']."'");
		prn($qr);

		if($g=getimagesize($_SERVER['DOCUMENT_ROOT'].'/images/'.trim($fmwPath,'/').'/'.$file)){
			?><script language="javascript" type="text/javascript">
			var e=window.parent.g('<?php echo $fOBoundToElement?>').firstChild;
			e.src='/images/<?php echo trim($fmwPath,'/')?>/.thumbs.dbr/<?php echo $file;?>';
			e.setAttribute('filename','<?php echo $file?>');
			e.setAttribute('filepath','images/<?php echo $fmwPath?>');
			e.setAttribute('noimage',0);
			e.setAttribute('dims','<?php echo $g[0].','.$g[1]?>)');
			e.setAttribute('size','<?php echo filesize($_SERVER['DOCUMENT_ROOT'].'/images/'.trim($fmwPath,'/').'/'.$file);?>');
			
			//we successfully selected a file, now change to that default folder
			window.parent.fOdefaultFolder=window.parent.g('fmwPath').value;
			<?php if($updateTitle){ ?>
			//update title if this is a new file being created
			window.parent.g('<?php echo $fOBoundToElement?>').previousSibling.firstChild.nextSibling.innerHTML='<?php echo str_replace("'","\'",$file);?>';
			<?php }?>
			</script><?php
		}else{
			//the object could be a word or excel doc, etc. - need an icon
		}
		$processAllObjects=false;
	break;
	case $submode=='editItemPicturesFieldPrep':
		require($COMPONENT_ROOT.'/comp_15_itemobject_editorinput.php');
		?><script language="javascript" type="text/javascript">
		var i=window.parent.editinode.nextSibling;
		var value=i.innerHTML;
		var form=document.getElementById('editField').innerHTML;
		i.innerHTML=form;
		
		//start with the data as they see it
		window.parent.g('Content').value=value;
		window.parent.g('originalContent').value=value;
		//focus on the field
		window.parent.g('Content').focus();
		//not using this - would like cursor to go to start or finish depending on settings; window.parent.g('Content').select();
		</script><?php
		$processAllObjects=false;
	break;
	case $submode=='editItemPicturesField':
		q("UPDATE relatebase_ObjectsTree SET $field='$Content' WHERE Tree_ID='$Tree_ID' AND Objects_ID=$ID AND ObjectName='finan_items'");
		prn($qr);
		?><script language="javascript" type="text/javascript">
		window.parent.editinode.nextSibling.innerHTML='<?php echo str_replace("\r\n",'<br />',str_replace("'","\'",stripslashes($Content)))?>';
		</script><?php
		$processAllObjects=false;
	break;
	case $submode=='deleteItemPictures':
		q("DELETE FROM relatebase_ObjectsTree WHERE Objects_ID=$Items_ID AND ObjectName='finan_items' AND Tree_ID=$Tree_ID");
		if(q("SELECT COUNT(*) FROM relatebase_ObjectsTree WHERE Tree_ID=$Tree_ID", O_VALUE)){
			//used by other items - though with newer join system, relatebase_tree is really just a placeholder for Tree_ID; but it's still worthwhile because we could back-get info about a picture across several slides, HOWEVER what we're really after is to join Items to *TREE* - lose a table in the process.  Then we'd have finan_itemsTree with Items_ID=n, and Tree_ID=NULL for the new objects. We'd need an ID for the record and an Idx so we could distinguish between multiple NULLs.  All based on the premise that you have a picture in an item ONLY ONE TIME (which I think is a good one)
		}else{
			q("DELETE FROM relatebase_tree WHERE ID=$Tree_ID");
		}
		?><script language="javascript" type="text/javascript">
		window.parent.g('r_<?php echo $Tree_ID?>').style.display='none';
		</script><?php
		$processAllObjects=false;
	break;
	case $submode=='setIdx':
		if($functionVersions['set_priority']<1.10)error_alert('You must include a version of set_priority() greater than 1.00');
		set_priority(
			$Tree_ID, 
			$idxdir, 
			$idxabs, 
			($datasetSetIdxOptions ? $datasetSetIdxOptions : array(
				'IDField'=>'Tree_ID',
				'whereFilter'=>'Objects_ID='.$ID.' AND ObjectName=\'finan_items\'',
				'priorityTable'=>'relatebase_ObjectsTree',
				'priorityField'=>'Priority'
			))
		);
		$processAllObjects=true;
	break;
}

if($processAllObjects){
	//fmw is already in place
	ob_start();
	eval('$a=q("'.$sqlPictureCollection.'", O_ARRAY);');
	$err=ob_get_contents();
	ob_end_clean();
	if($err)mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
	
	if(!$refreshComponentOnly){
		?><style type="text/css">
		#itemObjects{
			height:350px;
			overflow:auto;
			border:1px solid #333;
			padding:10px;
			background-color:#fff;
			}
		.itemObject{
			min-width:350px;
			max-width:350px;
			float:left;
			border:1px solid #333;
			margin:0px 5px 5px 0px;
			padding:5px;
			background-color:#D1DCC9;
			}
		.itemObject .del{
			position:absolute;
			right:0px;
			top:0px;
			width:15px;
			height:15px;
			background-color:darkred;
			color:#fff;
			text-align:center;
			cursor:pointer;
			z-index:2000;
			}
		.itemObject .hl{
			background-color:#FFCCFF;
			color:#333;
			}
		.itemObject .title{
			border-bottom:1px dotted #333;
			font-weight:900px;
			position:relative;
			z-index:900;
			}
		.itemObject .albdesc{
			float:left;
			position:relative;
			z-index:500;
			}
		.itemObject .img{
			/* float:left; */
			margin:0px 5px 5px 0px;
			border:1px solid #ccc;
			padding:3px;
			}
		.itemObject .albIdx{
			float:left;
			border:1px solid #666;
			background-color:navajowhite;
			padding:0px 2px;
			cursor:pointer;
			margin-right:3px;
			}
		.editor{
			position:absolute;
			display:inline;
			right:20px;
			top:0px;
			cursor:pointer;
			padding:0px 5px;
			color:#333;
			font-size:smaller;
			z-index:995;
			border:1px solid #555;
			background-color:#fff;
			}
		.hl{
			border:1px solid #555;
			background-color:thistle;
			}
		</style>
		<script language="javascript" type="text/javascript">
		function newPicture(){
			window.open('resources/bais_01_exe.php?mode=<?php echo $mode?>&submode=editItemPicturesField&ID=<?php echo $ID?>', 'w2');
		}
		function assignPicture(){
			//close element - but we need a pending visual
			g('fileObject').style.visibility='hidden';
			g('submode').value='assignPicture';
			document.forms['form1'].submit();
			g('submode').value='';
			
			//close element - but we need a pending visual
			g('fileObject').style.visibility='hidden';
		}
		function edit(o,s){
			o.firstChild.style.display=(s==1?'inline':'none');
		}
		function edith(o,s){
			o.className=(s==1?'editor hl':'editor');
		}
		function editi(o,editorType,field){
			try{
			g('editField').innerHTML=g('Content').value;
			}catch(e){}
			var Tree_ID=getParentMatching(o,/r_[0-9]+/).id.replace('r_','');
			//old way: o.parentNode.parentNode.getAttribute('id').replace('r_','');
			if(typeof editorType=='undefined')editorType='input';
			editinode=o;
			window.open('resources/bais_01_exe.php?mode=<?php echo $mode; //editItemPicturesField; ?>&submode=editItemPicturesFieldPrep&table=relatebase_tree&field='+field+'&editorType='+editorType+'&Tree_ID='+Tree_ID+'&Items_ID=<?php echo $ID?>','w2');
		}
		function deleteItemPictures(o){
			if(!confirm('This will remove this frame and its description.  Are you sure?'))return false;
			var str=/r_[0-9]+/;
			var o=getParentMatching(o,str);
			window.open('resources/bais_01_exe.php?mode=<?php echo $mode; //manageItemPictures :?>&submode=deleteItemPictures&Items_ID=<?php echo $ID?>&Tree_ID='+o.getAttribute('id').replace('r_',''),'w2');
		}
		var editinode=null;
	
		function obj_rename(mode,o,event){
			//modified from FEX 1.0.4
			/* if(mode=='submit'){
				if(o.firstChild.value==o.firstChild.nextSibling.value){
					o.parentNode.innerHTML=o.firstChild.value;
					return false;
				}
				return true;
			}else */
			if(mode=='blur'){
				if(o.value==o.nextSibling.value){
					o.parentNode.innerHTML=o.value;
					return false;
				}
				g('submode').value='editItemPicturesField';
				g('form1').submit();
				g('submode').value='';
				return false;
			}else if(mode=='keypress'){
				if(event.keyCode==27 || (event.keyCode==13 && o.value==o.nextSibling.value)){
					o.parentNode.innerHTML=o.nextSibling.value;
					return false;
				}
			}
		}
		function viewslideshow(ID){
			var reg=/imgInset/
			var a=g('itemObjects').childNodes;
			var hasImgs=0;
			for(var i in a){
				if(!a[i].tagName)continue;
				o=null;
				if(a[i].tagName)o=getChildMatching(a[i],reg);
				if(o && o.firstChild.getAttribute('nofile')!='1' && o.firstChild.getAttribute('noimage')!='1'){
					hasImgs++;
					if(hasImgs>1)break;
				}
			}
			if(hasImgs>1){
				window.open('slideshow.php?method=item&Items_ID='+g('ID').value,'l2_slideshow','width=800,height=700,resizable');
			}else{
				alert('This item cannot be shown as a slideshow.  It contains '+(hasImgs==1?'only one picture':'no pictures yet'));
			}
			return false;
		}
		function indexRow(o,e){
			//modified from components/dataset_generic_v121
			var f=findPos(o)[1];
			f-=g('itemObjects').scrollTop;
			//http://forums.digitalpoint.com/showthread.php?t=11965
			if (document.documentElement && !document.documentElement.scrollTop){
				// IE6 +4.01 but no scrolling going on
				var s=0;
			}else if (document.documentElement && document.documentElement.scrollTop){
				// IE6 +4.01 and user has scrolled
				var s=(document.documentElement.scrollTop);
			}else if (document.body && document.body.scrollTop){
				// IE5 or DTD 3.2 
				var s=document.body.scrollTop;
			}
			f=e.clientY+s-f;
			var idxdir=(f>19/2 ? -1 : 1);
			var idxabs=(e.ctrlKey ? 1 : 0);
			var Tree_ID=o.id.replace('idx_','');
			window.open('resources/bais_01_exe.php?mode=<?php echo $mode; //manageItemPictures?>&submode=setIdx&ID=<?php echo $ID ? $ID : $Items_ID?>&Tree_ID='+Tree_ID+'&idxdir='+idxdir+'&idxabs='+idxabs,'w2');
		}
		function findPos(obj) {
			var curleft = curtop = 0;
			if(obj.offsetParent){
				do{
					curleft += obj.offsetLeft;
					curtop += obj.offsetTop;
				} while (obj = obj.offsetParent);
				return [curleft,curtop];
			}else{
				alert('Unable to position this');
			}
		}
		</script><?php
	}
	?>
	<div class="fr">
		<label><input name="newframesontop" type="checkbox" value="1" onclick="g('newframesontop').value=(this.checked?1:0);" /> Place new frames on top</label>
	</div>
	<a href="resources/bais_01_exe.php?mode=<?php echo $mode; //manageItemPictures?>&submode=addItemObject&Items_ID=<?php echo $Items_ID?>" target="w2" onclick="return newPicture();">New slide..</a> &nbsp;&nbsp; <a href="#" title="View the slideshow of the frames below" onclick="alert('Unavailable');return viewslideshow(<?php echo $ID?>);">View slideshow..</a> <br />
	<div id="itemObjects" refreshParams="ID:Items_ID">
	<?php
	if(count($a))
	foreach($a as $file){
		require($COMPONENT_ROOT.'/comp_15_itemobject_v102.php');
	}
	?></div><?php
}
if($submode=='setIdx'){
	?><script language="javascript" type="text/javascript">
	try{
	window.parent.g('itemObjects').innerHTML=document.getElementById('itemObjects').innerHTML;
	}catch(e){ if(e.description)alert(e.description + ':' + e.message); }
	</script><?php
}
?>