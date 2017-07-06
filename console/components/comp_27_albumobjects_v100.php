<?php

$dataset='Pictures'; #more of a concept
$datasetComponent='picturesFocus'; #THIS physical component
$datasetGroup=$dataset;
if(!$datasetWord)$datasetWord='Picture';
if(!$datasetWordPlural)$datasetWordPlural='Pictures';
#N/A - $datasetFocusPage='members.php';
#N/A - $datasetAddObjectJSFunction='ow(this.href,\'l1_members\',\'700,700\',true);';
#N/A - $datasetQueryStringKey='Clients_ID';
#N/A - $datasetDeleteMode='deleteMember';

#N/A - $datasetQuery=''; //this is left blank for list_members; needed because a view didn't contain the same data as a query!
#N/A - $datasetTable='_v_contacts_generic_v200'; //this can be a single MySQL table or a view
#N/A - $datasetTableIsView=true;
#N/A - $datasetActiveUsage=true;
#N/A - $datasetFieldList='*';
$modApType='embedded';
$modApHandle='first';

/* 			-------------- added 2009-10-26 --------------			*/

//so, this completely declares what is available for the layout; see scheme below
/*
i.e. embedded means, part of the programs; user really has no access to this now 
i.e. first means, what I'm nicknaming this available columns set
*/
//this is first used by Jean Zinner: Title and Description fields moved to the JOIN table so title and descriptions can be different per album.. needed.
/* ---
$mergeAvailableCols['Pictures']['embedded']['first']=array(
	'extra'=>array(
		'sqlPictureCollection'=>"SELECT 
b.*, a.Title, a.Description, a.Priority
FROM ss_AlbumsPictures a, ss_pictures b 
WHERE a.Pictures_ID=b.ID AND b.ResourceType IS NOT NULL AND a.Albums_ID='$Albums_ID'
ORDER BY a.Lead",
	)
);
  --- */
//standard fields
$availableCols[$datasetGroup][$modApType][$modApHandle]=array(
	'extra'=>array(
		'sqlPictureCollection'=>'SELECT 
b.*, a.Description, a.Lead, IF(a.Title IS NOT NULL, a.Title, b.Title) AS Title
FROM ss_AlbumsPictures a, ss_pictures b 
WHERE a.Pictures_ID=b.ID AND b.ResourceType IS NOT NULL AND a.Albums_ID=\'$ID\'
ORDER BY a.Lead',
	)
);
$availableCols[$datasetGroup]=array_merge_accurate($availableCols[$datasetGroup], $mergeAvailableCols[$datasetGroup]);
extract($availableCols[$datasetGroup][$modApType][$modApHandle]['extra']);

$processAllObjects=true;
if($mode=='manageAlbums'){
	switch(true){
		case $submode=='addAlbumObject':
			//always update album and fulfill resourcetype
			q("UPDATE ss_albums SET Name='$Name', Location='$Location', ResourceType=1 WHERE ID=$ID");
			q("UPDATE ss_AlbumsPictures SET Description='$Description' WHERE Pictures_ID='$ID'");
			//cleanup - this globally cleans up ALL albums
			q("DELETE FROM ss_AlbumsPictures WHERE Pictures_ID IN('".@implode("','",q("SELECT a.Pictures_ID FROM ss_AlbumsPictures a LEFT JOIN ss_pictures b ON a.Pictures_ID=b.ID WHERE b.ID IS NULL", O_COL))."')");
			prn($qr);
			if($qr['affected_rows'])mail($developerEmail,'error file '.__FILE__.', line '.__LINE__,get_globals('Album cleanup, remember I changed the structure'),$fromHdrBugs);
	
			//populate the picture fields
			#new setting
			$autoincPictureTitleTable='ss_AlbumsPictures ap LEFT JOIN ss_pictures p ON ap.Albums_ID='.$ID.' AND ap.Pictures_ID=p.ID';
			$tdtable='ap';

			$Title=sql_autoinc_text($autoincPictureTitleTable, $tdtable.'.Title', 'Untitled Picture ', $options=array(
				'where'=>'Albums_ID='.$ID,
				'returnLowerCase'=>false,
				'leftSep'=>' '
			));
			$Description='(no description - click to edit)';
			
	
			$ResourceToken=substr(date('|YmdHis'),4).rand(10000,99999);
			$Pictures_ID=quasi_resource_generic(
				$MASTER_DATABASE, 
				'ss_pictures', 
				$ResourceToken, 
				/** remaining fields normally default to vector value **/ 
				$typeField='ResourceType', 
				$sessionKeyField='sessionKey', 
				$resourceTokenField='ResourceToken', 
				$primary='ID', 
				$creatorField='Creator', 
				$createDateField='CreateDate', 
				$cnx=C_MASTER,
				$titleDescriptionInJoinTable ? array() : array(
					'insertFields'=> array('Title'),
					'insertValues'=> array($Title)
				)
			);
			#new setting
			q("INSERT INTO ss_AlbumsPictures SET Description='$Description', Albums_ID=$ID, Pictures_ID=$Pictures_ID" .($titleDescriptionInJoinTable ? ", Title='$Title', Description='$Description'" : ''));

			//$file=q("SELECT p.ID, p.Tree_ID, $tdtable.Title, $tdtable.Description FROM ss_pictures p, ss_AlbumsPictures ap WHERE p.ID=ap.Albums_ID AND ap.Albums_ID=$Albums_ID AND p.ID=$Pictures_ID", O_ROW);
			$file=array(
				'ID'=>$Pictures_ID,
				'Title'=>$Title,
				'Description'=>$Description,
			);
			
			?><div id="fill"><?php
			require($COMPONENT_ROOT.'/comp_28_albumobject_v102.php');
			?></div><?php
			?><script language="javascript" type="text/javascript">
			window.parent.g('albumObjects').innerHTML = <?php if($newframesontop){ ?>document.getElementById('fill').innerHTML+window.parent.g('albumObjects').innerHTML<?php }else{ ?>window.parent.g('albumObjects').innerHTML + document.getElementById('fill').innerHTML<?php }?>;
			//scrollto
			try{ window.parent.g('albumObjects').scrollTo(<?php echo $newframesontop ? 0 : 10000 ?>); }catch(e){ }
			</script><?php
			$processAllObjects=false;
		break;
		case $submode=='assignPicture':
			if(!preg_match('/imgInset_([0-9]+)/',$fOBoundToElement,$a)){
				mail($developerEmail,'error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
				error_alert('No id detected');
			}
			if(!$fmwFile || !$fmwExt)error_alert('No file data passed!');
			//always update album and fulfill resourcetype
			q("UPDATE ss_albums SET Name='$Name', Location='$Location', ResourceType=1 WHERE ID=$ID");
			
			//cleanup - this globally cleans up ALL albums
			q("DELETE FROM ss_AlbumsPictures WHERE Pictures_ID IN('".@implode("','",q("SELECT a.Pictures_ID FROM ss_AlbumsPictures a LEFT JOIN ss_pictures b ON a.Pictures_ID=b.ID WHERE b.ID IS NULL", O_COL))."')");
			prn($qr);
			if($qr['affected_rows'])mail($developerEmail,'error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
			
			if(!($record=q("SELECT * FROM ss_pictures WHERE ID='".$a[1]."'", O_ROW))){
				?><script language="javascript" type="text/javascript">
				window.parent.g('<?php echo $fOBoundToElement?>').parentNode.style.display='none';
				</script><?php
				mail($developerEmail,'error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
				error_alert('unable to find this object anymore');
			}
			$file=$fmwFile.'.'.$fmwExt;
			//---------------------------------- TREE CODING -------------------------------------------
			/* the following works with the tree table to build to a node based on assumed root=images, then a path, then a file.  This could be done differently */
			
			$Tree_ID=tree_build_path(
				'images/'.trim($fmwPath,'/').'/'.$file, 
				$options=array('lastNodeType'=>'file', 'lastNodeValues'=>array('Description'=>'file node created by bais_01_exe.php:mode=assignPicture'))
			);
			if(preg_match('/untitled picture\s*[0-9]*/i',$record['Title'])){
				$updateTitle=", Title='".$file."'";
			}
			q("UPDATE ss_pictures SET ResourceType=1, Tree_ID=$Tree_ID WHERE ID='".$record['ID']."'");
	
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
		case $submode=='editFieldPrep':
			require($COMPONENT_ROOT.'/comp_28b_albumobject_editorinput.php');
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
		case $submode=='editField':
			if($titleDescriptionInJoinTable){
				q("UPDATE ss_AlbumsPictures SET $field='$Content' WHERE Pictures_ID='$ID' AND Albums_ID=$Albums_ID");
				q("UPDATE ss_pictures SET ResourceType=1 WHERE ID='$ID'");
			}else if($field=='Description'){
				q("UPDATE ss_AlbumsPictures SET $field='$Content' WHERE Pictures_ID='$ID' AND Albums_ID=$Albums_ID");
			}else{
				q("UPDATE ss_pictures SET ResourceType=1, $field='$Content' WHERE ID='$ID'");
			}
			prn($qr);
			?><script language="javascript" type="text/javascript">
			window.parent.g('editForm').parentNode.innerHTML='<?php echo str_replace("\r\n",'<br />',str_replace("'","\'",stripslashes($Content)))?>';
			</script><?php
			$processAllObjects=false;
		break;
		case $submode=='deleteObject':
			q("DELETE FROM ss_AlbumsPictures WHERE Albums_ID=$Albums_ID AND Pictures_ID=$Pictures_ID");
			if(q("SELECT COUNT(*) FROM ss_AlbumsPictures WHERE Pictures_ID=$Pictures_ID", O_VALUE)){
				//used by other albums - though with newer join system, ss_pictures is really just a placeholder for Tree_ID; but it's still worthwhile because we could back-get info about a picture across several slides, HOWEVER what we're really after is to join Albums to *TREE* - lose a table in the process.  Then we'd have ss_AlbumsTree with Albums_ID=n, and Tree_ID=NULL for the new objects. We'd need an ID for the record and an Idx so we could distinguish between multiple NULLs.  All based on the premise that you have a picture in an album ONLY ONE TIME (which I think is a good one)
			}else{
				q("DELETE FROM ss_pictures WHERE ID=$Pictures_ID");
			}
			?><script language="javascript" type="text/javascript">
			window.parent.g('r_<?php echo $Pictures_ID?>').style.display='none';
			</script><?php
			$processAllObjects=false;
		break;
		case $submode=='setIdx':
			if($functionVersions['set_priority']<1.10)error_alert('You must include a version of set_priority() greater than 1.00');
			set_priority(
				$Pictures_ID, 
				$idxdir, 
				$idxabs, 
				($datasetSetIdxOptions ? $datasetSetIdxOptions : array(
					'IDField'=>'Pictures_ID',
					'whereFilter'=>'Albums_ID='.$ID,
					'priorityTable'=>'ss_AlbumsPictures',
					'priorityField'=>'Priority'
				))
			);
			$processAllObjects=true;
		break;
	}
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
		#albumObjects{
			height:350px;
			overflow:auto;
			border:1px solid #333;
			padding:10px;
			background-color:#fff;
			}
		.albumObject{
			min-width:350px;
			max-width:350px;
			float:left;
			border:1px solid #333;
			margin:0px 5px 5px 0px;
			padding:5px;
			background-color:#D1DCC9;
			}
		.albumObject .del{
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
		.albumObject .hl{
			background-color:#FFCCFF;
			color:#333;
			}
		.albumObject .title{
			border-bottom:1px dotted #333;
			font-weight:900px;
			position:relative;
			z-index:900;
			}
		.albumObject .albdesc{
			float:left;
			position:relative;
			z-index:500;
			}
		.albumObject .img{
			/* float:left; */
			margin:0px 5px 5px 0px;
			border:1px solid #ccc;
			padding:3px;
			}
		.albumObject .albIdx{
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
			var buffer=g('mode').value;
			var buffer2=g('submode').value;
			g('mode').value='manageAlbums';
			g('submode').value='addAlbumObject';
			document.forms['form1'].submit();
			g('mode').value=buffer;
			g('submode').value=buffer2;
			return false;
		}
		function assignPicture(){
			var buffer=g('mode').value;
			var buffer2=g('submode').value;
			g('mode').value='manageAlbums';
			g('submode').value='assignPicture';
			document.forms['form1'].submit();
			g('mode').value=buffer;
			g('submode').value=buffer2;
			
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
			g('editForm').parentNode.innerHTML=g('Content').value;
			}catch(e){}
			var ID=getParentMatching(o,/r_[0-9]+/).id.replace('r_','');
			//old way: o.parentNode.parentNode.getAttribute('id').replace('r_','');
			if(typeof editorType=='undefined')editorType='input';
			editinode=o;
			window.open('resources/bais_01_exe.php?mode=manageAlbums&submode=editFieldPrep&table=ss_pictures&field='+field+'&editorType='+editorType+'&ID='+ID+'&Albums_ID=<?php echo $ID?>','w2');
		}
		function deleteObject(o){
			if(!confirm('This will remove this frame and its description.  Are you sure?'))return false;
			var str=/r_[0-9]+/;
			var o=getParentMatching(o,str);
			window.open('resources/bais_01_exe.php?mode=manageAlbums&submode=deleteObject&Albums_ID=<?php echo $ID?>&Pictures_ID='+o.getAttribute('id').replace('r_',''),'w2');
		}
		var editinode=null;
	
		function obj_rename(mode,o,event){
			//modified from FEX 1.0.4
			if(mode=='submit'){
				if(o.firstChild.value==o.firstChild.nextSibling.value){
					o.parentNode.innerHTML=o.firstChild.value;
					return false;
				}
				return true;
			}else if(mode=='blur'){
				if(o.value==o.nextSibling.value){
					o.parentNode.parentNode.innerHTML=o.value;
					return false;
				}
				o.parentNode.submit();
			}else if(mode=='keypress'){
				if(event.keyCode==27 || (event.keyCode==13 && o.value==o.nextSibling.value)){
					o.parentNode.parentNode.innerHTML=o.nextSibling.value;
					return false;
				}
			}
		}
		function viewslideshow(ID){
			var reg=/imgInset/
			var a=g('albumObjects').childNodes;
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
				window.open('slideshow.php?method=album&Albums_ID='+g('ID').value,'l2_slideshow','width=800,height=700,resizable');
			}else{
				alert('This album cannot be shown as a slideshow.  It contains '+(hasImgs==1?'only one picture':'no pictures yet'));
			}
			return false;
		}
		function indexRow(o,e){
			//modified from components/dataset_generic_v121
			var f=findPos(o)[1];
			f-=g('albumObjects').scrollTop;
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
			var Pictures_ID=o.id.replace('idx_','');
			window.open('resources/bais_01_exe.php?mode=manageAlbums&submode=setIdx&ID=<?php echo $ID ? $ID : $Albums_ID?>&Pictures_ID='+Pictures_ID+'&idxdir='+idxdir+'&idxabs='+idxabs,'w2');
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
	<a href="resources/bais_01_exe.php?mode=manageAlbums&submode=addAlbumObject&Albums_ID=<?php echo $Albums_ID?>" target="w2" onclick="return newPicture();">New slide..</a> &nbsp;&nbsp; <a href="#" title="View the slideshow of the frames below" onclick="return viewslideshow(<?php echo $ID?>);">View slideshow..</a> <br />
	<div id="albumObjects" refreshParams="ID:Albums_ID">
	<?php
	if(count($a))
	foreach($a as $file){
		require($COMPONENT_ROOT.'/comp_28_albumobject_v102.php');
	}
	?></div><?php
}
if($submode=='setIdx'){
	?><script language="javascript" type="text/javascript">
	try{
	window.parent.g('albumObjects').innerHTML=document.getElementById('albumObjects').innerHTML;
	}catch(e){ if(e.description)alert(e.description + ':' + e.message); }
	</script><?php
}
?>