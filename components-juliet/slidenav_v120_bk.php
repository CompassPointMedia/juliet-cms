<?php
/* name=Slideshow Plugin; description=First slide show edit component; */

//table creation for album and picture information (v1.00)
/*
2012-04-23:
	version 1.20 allows a CMSB section to be used to store the information - so it can be nested in a component itself
2012-03-26:
	todo
		build in the source of the picture - using longdesc, I should be able to edit an image using the original
		implement in reader, checking if there is a .orig file in .thumbs.dbr
		
		DONE	4-wall box needed
		build the size of the slideshow based on the individual pieces and gutters etc.
		DONE	SHOW the pictures that are being used including a slide show
		DONE	show title and description
		have a pinterest link and an edit link
		have a page for a picture - this picture is used on these pages: _______
		ultimately I want to be able to edit a SINGLE PICTURE behind a canvas, with ease
		editor
			on going from tb -> lr, /!\ transpose rows/colums?
			[x] show previous/next start/stop controls
			DONE	descriptions for each of the pictures
			DONE	ability to export this setting and import to another page..
		
2012-03-10:
this is now the 2nd editable component I have developed; first was cpm171.menu2.php.  The difference here is the storage.  menu2.php for My En chant edGarden is a cross page span (though we need a way to have exceptions for pages), but occuping a single block, AND compiled in the pJCurrentContentRegion (which is post-HTML output).  This component is for a single page, and is "cross-block" i.e. creates output for multiple pJCurrentContentRegion's.  Restated:
	Application			Component File Type					Storage				
	-----------			-------------------					------------------------
	per-page			cross-block							gen_nodes_settings.Settings -> array[$handle]
	per-block			cross-page							gen_templates_blocks.Parameters -> [entire array]

Obviously the biggest discrepancy is, not so much storage location, but the array layout.  Biggest concerns are this plus how to store multiple cross-block components on a page, or multiple cross-page component files in a block.
Also, all of these things are independent of RelateBase and modules..




*/


$handle='slideNav';

$sNavSystemThumbWidth=48;
$sNavSystemThumbHeight=48;

//2012-03-10: pull parameters for this component file - note that this is in gen_nodes_settings.Settings vs. gen_templates_blocks.Parameters
if($thissection){
	if($Parameters=q("SELECT Options FROM cmsb_sections WHERE Section='$thissection'", O_VALUE)){
		$Parameters=unserialize(base64_decode($Parameters));
		if($Parameters[$handle])$pJ['componentFiles'][$handle]=$Parameters[$handle];
		/* nodes include: forms; data; format.  forms is unused right now, and data[default] means "across all pages" and is the only part developed */
	}else{
		unset($pJ['componentFiles'][$handle]);
	}
}else{
	if($Parameters=q("SELECT Settings FROM gen_nodes_settings WHERE Nodes_ID='".($_thisnode_ ? $_thisnode_ : $thisnode) ."'", O_VALUE)){
		$Parameters=unserialize(base64_decode($Parameters));
		if($Parameters[$handle])$pJ['componentFiles'][$handle]=$Parameters[$handle];
		/* nodes include: forms; data; format.  forms is unused right now, and data[default] means "across all pages" and is the only part developed */
	}else{
		unset($pJ['componentFiles'][$handle]);
	}
}
//2012-03-12: this allows me to pass these values in the query string if desired.  Variables used below are as these local variables
if(!$sNavLayout)$sNavLayout=pJ_getdata('sNavLayout',1); //2012-03-27: basic layouts
if(!$sNavNavWidth)$sNavNavWidth=pJ_getdata('sNavNavWidth',300);
if(!$sNavSlideDelay)$sNavSlideDelay=pJ_getdata('sNavSlideDelay',5000);
//canvas
if(!$sNavCanvasSize)$sNavCanvasSize=pJ_getdata('sNavCanvasSize','default');
if(!$sNavCanvasWidth)$sNavCanvasWidth=pJ_getdata('sNavCanvasWidth',600);
if(!$sNavCanvasHeight)$sNavCanvasHeight=pJ_getdata('sNavCanvasHeight',450);
$sNavResizing=pJ_getdata('sNavResizing',0);
//thumbnail gallery
if(!$sNavShowThumbs)$sNavShowThumbs=pJ_getdata('sNavShowThumbs',1);
$sNavThumbWidth=pJ_getdata('sNavThumbWidth',95);
$sNavThumbHeight=pJ_getdata('sNavThumbHeight',95);
$sNavThumbResizing=pJ_getdata('sNavThumbResizing',2);
$sNavThumbRows=pJ_getdata('sNavThumbRows',7);
$sNavThumbCols=pJ_getdata('sNavThumbCols',2);
$sNavShowPagination=pJ_getdata('sNavShowPagination',1);

//spacing
$sNavCanvasThumbSpacing=pJ_getdata('sNavCanvasThumbSpacing',35);

//controls
$sNavShowControls=pJ_getdata('sNavShowControls',1);
$sNavControlHeight=pJ_getdata('sNavControlHeight',23);

if(!isset($sNavSlideAutoStart))$sNavSlideAutoStart=pJ_getdata('sNavSlideAutoStart',0);
$sNavAdditionalCSS=pJ_getdata('sNavAdditionalCSS');
//recognized region of the template installed for the project
if(!$sNavNavigationBlock)$sNavNavigationBlock=pJ_getdata('sNavNavigationBlock','mainRegionLeftIntro');
if(!$sNavSlideBlock)$sNavSlideBlock=pJ_getdata('sNavSlideBlock','mainRegionCenterContent');

if(!$slideShowPreText)$slideShowPreText=pJ_getdata('slideShowPreText',false);
if(!$slideShowPostText)$slideShowPostText=pJ_getdata('slideShowPostText',false);

//for local css links in head of document
$pJLocalCSSLinks[$handle]='/Library/css/galleriffic-2-2.css';

for($__i__=1; $__i__<=1; $__i__++){ //---------------- begin i break loop ---------------

if($mode=='componentEditor'){
	if($submode=='import'){
		$ImportString=trim($ImportString);
		if(!preg_match('/^[a-zA-Z0-9=]+$/',$ImportString))error_alert('The string you are attempting to import does not appear to be valid.  It must be a base 64-encoded serialized array');
	}
	if(!$default['sNavSlideAutoStart'])$default['sNavSlideAutoStart']=$_POST['default']['sNavSlideAutoStart']=0;
	if(!$default['sNavShowPagination'])$default['sNavShowPagination']=$_POST['default']['sNavShowPagination']='0';
	if(!$default['sNavShowThumbs'])$default['sNavShowThumbs']=$_POST['default']['sNavShowThumbs']='0';
	if(!$default['sNavShowControls'])$default['sNavShowControls']=$_POST['default']['sNavShowControls']='0';
	if(!$default['slideShowPreText'])$default['slideShowPreText']=$_POST['default']['slideShowPreText']='0';
	if(!$default['slideShowPostText'])$default['slideShowPostText']=$_POST['default']['slideShowPostText']='0';
	/*
	2012-03-12: this is universal code which should be updated on ALL components.  The objective is that 
	
	*/
	//2012-03-26: error checking - a first use
	if($default['sNavCanvasSize']=='size'){
		if(!preg_match('/^[0-9]+$/',$default['sNavCanvasWidth']) || $default['sNavCanvasWidth']<100 || !preg_match('/^[0-9]+$/',$default['sNavCanvasHeight']) || $default['sNavCanvasHeight']<100)error_alert('Canvas width and height must be valid numbers and they must both be over 100');
	}
	if(!preg_match('/^[0-9]+$/',$default['sNavThumbWidth']) || $default['sNavThumbWidth']<31 || !preg_match('/^[0-9]+$/',$default['sNavThumbHeight']) || $default['sNavThumbHeight']<31)error_alert('Thumbnail width and height must be valid numbers and they must both be over 31');
	if($submode=='export')ob_start();
	if($thissection){
		/* ----------  stored in cmsb_sections -------------- */
		!is_array($pJ['componentFiles'][$handle]) ? $pJ['componentFiles'][$handle]=array() : '';
		//now integrate the form post
		$pJ['componentFiles'][$handle]['data'][$formNode]=stripslashes_deep($_POST[$formNode]);
		$Parameters[$handle]['data'][$formNode]=$pJ['componentFiles'][$handle]['data'][$formNode];
	
		//unlike gen_templates_blocks, place as part of a larger array
		if(q("SELECT * FROM cmsb_sections WHERE Section='$thissection'", O_ROW)){
			//OK
		}else{
			q("INSERT INTO cmsb_sections SET Section='$thissection', EditDate=NOW()");
		}
		q("UPDATE cmsb_sections SET Options='".base64_encode(serialize($Parameters))."' WHERE Section='$thissection'");
		prn($qr);
	}else if($_thisnode_){
		/* ----------  this is a single-page, cross-block settings update ---------------  */
		!is_array($pJ['componentFiles'][$handle]) ? $pJ['componentFiles'][$handle]=array() : '';
		//now integrate the form post
		$pJ['componentFiles'][$handle]['data'][$formNode]=stripslashes_deep($_POST[$formNode]);
		$Parameters[$handle]['data'][$formNode]=$pJ['componentFiles'][$handle]['data'][$formNode];
	
		//unlike gen_templates_blocks, place as part of a larger array
		if(q("SELECT * FROM gen_nodes_settings WHERE Nodes_ID='$_thisnode_'", O_ROW)){
			//OK
		}else{
			q("INSERT INTO gen_nodes_settings SET Nodes_ID='$_thisnode_', EditDate=NOW()");
		}
		q("UPDATE gen_nodes_settings SET Settings='".base64_encode(serialize($Parameters))."' WHERE Nodes_ID='$_thisnode_'");
		prn($qr);
	}else{
		/* ----------  this is a cross-page, single-block settings update ---------------  */
		if($Parameters=q("SELECT Parameters FROM gen_templates_blocks WHERE Templates_ID=$Templates_ID AND Name='$pJCurrentContentRegion'", O_VALUE)){
			$a=unserialize(base64_decode($Parameters));
		}else{
			$a=array();
		}
		!is_array($pJ['componentFiles'][$handle]) ? $pJ['componentFiles'][$handle]=array() : '';
		foreach($a as $n=>$v){
			$pJ['componentFiles'][$handle][$n]=$v;
		}
		//now integrate the form post
		$pJ['componentFiles'][$handle]['data'][$formNode]=stripslashes_deep($_POST[$formNode]);
		$Parameters=$pJ['componentFiles'][$handle];
		q("UPDATE gen_templates_blocks SET Parameters='".base64_encode(serialize($Parameters))."' WHERE Templates_ID='$Templates_ID' AND Name='$pJCurrentContentRegion'");
		prn($qr);
	}
	if($submode=='import'){
		if($ImportMerge){
			$a=unserialize(base64_decode($ImportString));
			$ImportString=base64_encode(serialize(array_merge_accurate($Parameters,$a)));
		}else{
			//no action
		}
		switch(true){
			case strlen($thissection):
				q("UPDATE cmsb_sections SET Options='".$ImportString."' WHERE Section='$thissection'");
			break;
			case strlen($_thisnode_):
				q("UPDATE gen_nodes_settings SET Settings='".$ImportString."' WHERE Nodes_ID='$_thisnode_'");
			break;
			default:
				q("UPDATE gen_templates_blocks SET Parameters='".$ImportString."' WHERE Templates_ID='$Templates_ID' AND Name='$pJCurrentContentRegion'");
		}
		?><script language="javascript" type="text/javascript">
		alert('Your settings have been successfully imported.  Juliet will now reload this page');
		var l=window.parent.location+'';
		window.parent.location=l;
		</script><?php
	}
	if($submode=='export'){
		ob_end_clean();
		$str='-- Juliet version '.$pJVersion.', file '.end(explode('/',__FILE__)).'; exported '.date('n/j/Y \a\t g:iA').' - to re-import, paste the code below into the desired component ----'."\n";
		attach_download('', $str.base64_encode(serialize($Parameters)), str_replace('.php','',end(explode('/',__FILE__))).'_'.date('Y-m-d_his').'.txt');
	}
	break;
}else if($formNode=='default' /* ok this is something many component files will contain */){
	?>
	<h3>Overall Slideshow Features</h3>
	<style type="text/css">
	.submenu{
	border:1px solid gold;
	padding:15px;
	margin:5px 10px;
	}
	</style>
	<script language="javascript" type="text/javascript">
	var slideShowSource_buffer='<?php echo $sss=pJ_getdata('slideShowSource');?>';
	function slideShowSource(o){
		g('images').style.display=(o.value==''?'none':'block');
		g('folder').style.display=(o.value=='folder'?'block':'none');
	}
	function layout(o){
		g('slideShowLayout').src='/images/juliet/slideshow-'+o.value+'.jpg';
	}
	function showSubFolder(){
		g('subFolder').style.display='block';
	}
	</script>
	Slide show source: 
	<select name="default[slideShowSource]" id="default[slideShowSource]" onchange="dChge(this);slideShowSource(this);" style="width:350px;">
	<option value="">&lt; Select.. &gt;</option>
	<?php
	if($slides=q("SELECT a.ID, a.Name, a.Location, a.Active, COUNT(ap.Pictures_ID) PictureCount FROM ss_albums a LEFT JOIN ss_AlbumsPictures ap ON a.ID=ap.Albums_ID WHERE ResourceType=1 GROUP BY a.ID ORDER BY a.Location, a.Name", O_ARRAY_ASSOC)){
		?><optgroup label="Album From Console">
		<?php 
		foreach($slides as $n=>$v){
			?><option value="<?php echo $n?>" <?php echo $sss==$n?'selected':''?>><?php echo h($v['Name'] . ($v['Name'] && $v['Location'] ? ' - ':''). $v['Location'].($v['PictureCount'] ? ' ('.$v['PictureCount'].' pictures)':''));?></option><?php
		}
		?>
		</optgroup><?php
	}
	?>
	  <option <?php echo pJ_getdata('slideShowSource')=='folder'?'selected':''?> value="folder">Folder..</option>
    </select>
	
	<div id="folder" class="submenu" style="display:<?php echo pJ_getdata('slideShowSource')=='folder'?'block':'none';?>">
	Root folder:<br />
	<a href="/admin/file_explorer/?uid=<?php echo $handle;?>&folder=" onclick="return ow(this.href+g('default[rootFolder]').value,'l1_fex','700,700');" title="View with FEX"><img src="/images/i/fex104/bootleg_ms_folder.gif" width="19" align="absbottom" /></a> /images/<input name="default[rootFolder]" type="text" id="default[rootFolder]" value="<?php echo pJ_getdata('rootFolder');?>" size="30" onchange="dChge(this);" /> 
	&nbsp;&nbsp;
	[<a href="javascript:showSubFolder()">advanced..</a>]
	<?php
	$sfv=pJ_getdata('subFolderVariable');
	?>
	<div id="subFolder" style="display:<?php echo $sfv?'block':'none';?>">
		Sub folder variable&nbsp;&nbsp; $
		<input name="default[subFolderVariable]" type="text" id="default[subFolderVariable]" onchange="dChge(this);" value="<?php echo pJ_getdata('subFolderVariable');?>" size="12" />
		<em class="gray">(optional)</em><br />
		Sub folders to exclude  
		<input name="default[excludedSubFolders]" type="text" id="default[excludedSubFolders]" onchange="dChge(this)" value="<?php echo pJ_getdata('excludedSubFolders');?>" size="28" />
		<em class="gray">(optional, separate by commas)</em><br />
	</div>
	</div>
	<div id="images" class="submenu" style="display:<?php echo $sss?'block':'none';?>">
	<a href="javascript:alert('You are asking me to reload the images for this particular folder or album.  Currently I cannot do this; however you can save changes if needed, then hit F5 to refresh this way');">reload images..</a><br />
	<div id="images_sub">
	<?php
	if(is_numeric($sss)){
		//albums not developed
		?><em class="gray">album integration not developed yet</em><?php
	}else{
		//folder
		$path='images/'.pJ_getdata('rootFolder');
		if($ssImagesShow=get_file_assets($_SERVER['DOCUMENT_ROOT'].'/'.$path,array('positiveFilters'=>'\.(gif|jpg|png)$'))){
			?><table class="images">
			<thead><tr>
			<th>&nbsp;</th><th>Title</th><th>Description</th>
			</tr></thead><?php
			foreach($ssImagesShow as $n=>$v){
				$Tree_ID=tree_build_path($path.'/'.$v['name']);
				$src='Tree_ID='.$Tree_ID.'&Key='.md5($Tree_ID.$MASTER_PASSWORD).'&disposition='.$sNavSystemThumbWidth.'x'.$sNavSystemThumbHeight.'&boxMethod=2';
				?><tr>
				<td><a href="/<?php echo $path.'/'.$v['name']?>" title="View this picture full-size" onclick="return ow(this.href,'l2_pic','700,700');"><img src="/images/reader.php?<?php echo $src;?>" width="<?php echo $sNavSystemThumbWidth?>" height="<?php echo $sNavSystemThumbHeight?>" alt="img" /></a></td>
				<td><input type="text" maxlength="255" size="17" name="default[sNavImgTitle][<?php echo urlencode($n);?>]" id="default[sNavImgTitle][<?php echo urlencode($n);?>]" onchange="dChge(this);" value="<?php echo h(pJ_getdata(array('field'=>'sNavImgTitle','subKey'=>$n)));?>" /></td>
				<td>
				<textarea cols="30" rows="1" name="default[sNavImgDescription][<?php echo urlencode($n);?>]" id="default[sNavImgDescription][<?php echo urlencode($n);?>]" onchange="dChge(this);"><?php echo h(pJ_getdata(array('field'=>'sNavImgDescription','subKey'=>$n)));?></textarea>
				</td>
				</tr><?php
			}
			?></table><?php
		}else{
			?>No images in this folder<?php
		}
	}
	?>
	</div>

	</div>
	
	<br />
	<br />
	Block location of slide show: 
	<select name="default[sNavSlideBlock]" id="sNavSlideBlock" onChange="dChge(this);">
	<?php
	//show selected block parameters
	recurse_array($templateDefinedBlocks,1,array('type'=>'options','field'=>'sNavSlideBlock'));
	?>
	</select>
	<br />
	<div class="fr">
	<img id="slideShowLayout" src="/images/juliet/slideshow-<?php echo $sNavLayout;?>.jpg" />
	</div>
	Slideshow layout: 
	<select name="default[sNavLayout]" id="default[sNavLayout]" onchange="dChge(this);layout(this);" >
	<option <?php echo $sNavLayout==1?'selected':''?> value="1">Layout 1 - standard, thumbs on left</option>
	<option <?php echo $sNavLayout==2?'selected':''?> value="2">Layout 2 - thumbs below</option>
	<option <?php echo $sNavLayout==3?'selected':''?> value="3">Layout 3 - thumbs on right</option>
	<option <?php echo $sNavLayout==4?'selected':''?> value="4">Layout 4 - thumbs on top</option>
	</select>

	<h3 class="cb">Slide Canvas</h3>
	<strong>Canvas dimensions</strong>:<br />
	<label><input name="default[sNavCanvasSize]" type="radio" value="default" <?php echo $sNavCanvasSize=='default' || !$sNavCanvasSize ? 'checked' : '';?> />
	Let pictures determine this</label>
	<br />
	<label><input name="default[sNavCanvasSize]" type="radio" value="size" <?php echo $sNavCanvasSize=='size' ? 'checked' : '';?> /> 
	Size to..</label>
	<br /> 
	<div style="margin-left:25px;">
	  <input name="default[sNavCanvasWidth]" type="text" id="default[sNavCanvasWidth]" onchange="dChge(this);" value="<?php echo $sNavCanvasWidth;?>" size="5" /> 
	  pixels wide&nbsp;&nbsp;&nbsp;by &nbsp;&nbsp;&nbsp;
	  <input name="default[sNavCanvasHeight]" type="text" id="default[sNavCanvasHeight]" onchange="dChge(this);" value="<?php echo $sNavCanvasHeight;?>" size="5" />
	  pixels high <br />
	For pictures larger than this: 
	<select name="default[sNavResizing]" id="default[sNavResizing]" onchange="dChge(this);layout(this);" >
	  <option <?php echo $sNavResizing==4?'selected':''?> value="4">Shrink to fit canvas (may not fill both directions)</option>
	  <option <?php echo $sNavResizing==2?'selected':''?> value="2">Shrink until two walls fit, then center and crop</option>
    </select>
	<br />
	<span class="gray">features to be developed here: a) note you can edit for pans b) email me when an image has been resized c) choose background color for shrunk images d) do this only for (portrait|landscape) images e) add a watermark image</span>
	
	</div>
	<br />
	<h3 class="cb">Thumbnail gallery</h3>
	
	<label><input name="default[sNavShowThumbs]" type="checkbox" id="default[sNavShowThumbs]" value="1" onchange="dChge(this);" <?php echo $sNavShowThumbs || !isset($sNavShowThumbs)?'checked':''?> />
	Show the thumbnails</label><br />
	Size to..<br />
    <span style="margin-left:25px;">
    <input name="default[sNavThumbWidth]" type="text" id="default[sNavThumbWidth]" onchange="dChge(this);" value="<?php echo $sNavThumbWidth;?>" size="5" />
pixels wide&nbsp;&nbsp;&nbsp;by &nbsp;&nbsp;&nbsp;
<input name="default[sNavThumbHeight]" type="text" id="default[sNavThumbHeight]" onchange="dChge(this);" value="<?php echo $sNavThumbHeight;?>" size="5" />
pixels high <br />
When creating thumbnails:
<select name="default[sNavThumbResizing]" id="default[sNavThumbResizing]" onchange="dChge(this);layout(this);" >
  <option <?php echo $sNavThumbResizing==2?'selected':''?> value="2">Shrink until two walls fit, then center and crop</option>
  <option <?php echo $sNavThumbResizing==4?'selected':''?> value="1">Shrink to fit thumbnail dimensions</option>
</select>
<br />
Thumbnail rows (R): 
<input name="default[sNavThumbRows]" type="text" id="default[sNavThumbRows]" onchange="dChge(this);" value="<?php echo $sNavThumbRows;?>" size="5" />
<br />
Thumbnail columns (C):
<input name="default[sNavThumbCols]" type="text" id="default[sNavThumbCols]" onchange="dChge(this);" value="<?php echo $sNavThumbCols;?>" size="5" />
<br />
<label><input name="default[sNavShowPagination]" type="checkbox" id="default[sNavShowPagination]" value="1" <?php echo $sNavShowPagination?'checked':'';?> onchange="dChge(this);" />
Show pagination if number of thumbs exceeds RxC</label>
	<h3>Controls</h3>
	<p>
	  <label>
	  <input name="default[sNavShowControls]" type="checkbox" id="default[sNavShowControls]" value="1" <?php echo $sNavShowControls ?'checked':'';?> onchange="dChge(this);" />
Show controls (pause/play and next/previous) </label>
	  <br />
	  Play link text: 
	  <input name="default[sNavPlayLinkText]" type="text" id="default[sNavPlayLinkText]" onchange="dChge(this)" value="<?php echo pJ_getdata('sNavPlayLinkText','Play Slideshow');?>" size="28" />
	  <span class="gray">(these four fields can also be image HTML)</span>
	  <br />
	  Pause link text: <input name="default[sNavPauseLinkText]" type="text" id="default[sNavPauseLinkText]" onchange="dChge(this)" value="<?php echo pJ_getdata('sNavPauseLinkText','Pause Slideshow');?>" size="28" /><br />
	  Previous link text: <input name="default[sNavPrevLinkText]" type="text" id="default[sNavPrevLinkText]" onchange="dChge(this)" value="<?php echo pJ_getdata('sNavPrevLinkText','&lsaquo; Previous Photo');?>" size="28" /><br />
	  Next link text: <input name="default[sNavNextLinkText]" type="text" id="default[sNavNextLinkText]" onchange="dChge(this)" value="<?php echo pJ_getdata('sNavNextLinkText','Next Photo &rsaquo;');?>" size="28" />
	  <br />
	  Height of control: 
	  <input name="default[sNavControlHeight]" type="text" id="default[sNavControlHeight]" onchange="dChge(this);" value="<?php echo $sNavControlHeight;?>" size="5" />
	</p>
	<h3>Layout</h3>
	<p>Spacing between thumbs and slide canvas: 
	  <input name="default[sNavCanvasThumbSpacing]" type="text" id="default[sNavCanvasThumbSpacing]" onchange="dChge(this);" value="<?php echo $sNavCanvasThumbSpacing;?>" size="5" />
	  <span class="gray">(px)</span><br />
	  <br />
	  <br />
	  <br />
	  Slide delay (milliseconds):
	  <input name="default[sNavSlideDelay]" type="text" id="default[sNavSlideDelay]" onchange="dChge(this);" value="<?php echo $sNavSlideDelay;?>" size="5" />
	  <br />
    </p>
	<label><input type="checkbox" value="1" name="default[sNavSlideAutoStart]" id="sNavSlideAutoStart" <?php echo $sNavSlideAutoStart ? 'checked':''?> onchange="dChge(this);" /> Auto-start slideshow</label>
	<br />
	Border of slideshow: 
	<input name="default[sNavBorder]" type="text" id="default[sNavBorder]" onchange="dChge(this)" value="<?php echo pJ_getdata('sNavBorder');?>" size="20" /> 
	<em class="gray">(Use CSS values, e.g. 1px solid darkred)</em>
	<br />
	<p>Additional CSS:<br />
	  <textarea name="default[sNavAdditionalCSS]" cols="50" rows="5" id="default[sNavAdditionalCSS]"><?php echo h($sNavAdditionalCSS);?></textarea>
	  <br />
	</p>
	<label><input type="checkbox" value="1" name="default[slideShowPreText]" id="slideShowPreText" <?php echo $slideShowPreText ? 'checked':''?> onchange="dChge(this);" /> Include a CMS section before the slideshow</label><br />
	<label><input type="checkbox" value="1" name="default[slideShowPostText]" id="slideShowPostText" <?php echo $slideShowPostText ? 'checked':''?> onchange="dChge(this);" /> 
	Include a CMS section after the slideshow</label>
	<br />

	<br />
	<br />
	<?php
	break; //------------ i break loop ---------------
}else if($formNode=='folders'){
	?>
	<p>This is a folder editor form; options needed include:<br />
	delete a folder</p>
	<p>prioritize (order) folders <br />
	add pictures to a folder<br />
	the call_edit() function needs to be expanded to contextualize this form so &quot;that specific folder&quot; is presented.<br />
	</p>
	<?php
	break; //------------ i break loop ---------------
}


//----------------- handle passwords -----------------------------

if(pJ_getdata('slideShowSource')=='folder' && $subFolderVariable=pJ_getdata('subFolderVariable')){
	$rootFolder=pJ_getdata('rootFolder');
	if($pwstatus=='1'){
		?>
		  Please enter a password to access this slideshow:<br />
		<form method="get" name="form1" id="form1">
			<input type="password" id="PW" name="PW" /> <input type="submit" name="Submit" value="Submit" /><br />
			<input type="hidden" name="slide" value="<?php echo $$subFolderVariable?>" />
			<input type="hidden" name="protect" value="<?php echo $$subFolderVariable?>" />
			<input type="hidden" name="pwstatus" value="2" /> <!-- entering password -->
			<input type="hidden" name="protect" value="1" />
		</form>
		<?php
		exit;
	}else if($pwstatus=='2'){
		$hasPassword=false;
		$fp=opendir('images/'.$rootFolder);
		while(false!==($folder=readdir($fp))){
			if(!is_dir('images/'.$rootFolder.'/'.$folder))continue;
			if(md5($folder . $MASTER_PASSWORD)==$$subFolderVariable){
				$a=@file('images/'.$rootFolder.'/'.$folder.'/protected.txt');
				foreach($a as $b){
					$c=explode(':',$b);
					if(strtolower(trim($c[0]))=='password' || strtolower(trim($c[0]))=='passwords'){
						if($d=explode(',',trim($c[1]))){
							foreach($d as $e){
								$e=trim($e);
								if($e==$PW){
									$hasPassword=true;
									$_SESSION['special']['slidePasswords'][]=$PW;
									$_SESSION['special']['accessibleSlidePages'][$folder]=true;
								}
							}
						}
					}
				}
			}
		}
		if(!$hasPassword){
			exit('Your password is not valid for this folder - please go back');
		}else{
			header('Location: '.$thispage.'?slide='.$$subFolderVariable.'&protect='.$protect);
			?><script>
			window.location='<?php echo $thispage;?>?slide=<?php echo $$subFolderVariable?>&protect=<?php echo $protect?>';
			</script><?php
			exit;
		}
	}
	ob_start();
	if($excludedSubFolders=strtolower(pJ_getdata('excludedSubFolders'))){
		$excludedSubFolders=explode(',',$excludedSubFolders);
		foreach($excludedSubFolders as $n=>$v){
			if(!trim($v))unset($excludedSubFolders[$n]);
			$excludedSubFolders[$n]=trim($v);
		}
	}
	if(!$excludedSubFolders)$excludedSubFolders=array();

	$fp=opendir($_SERVER['DOCUMENT_ROOT'].'/images/'.$rootFolder);
	while(false!==($folder=readdir($fp))){
		if(!is_dir($_SERVER['DOCUMENT_ROOT'].'/images/slides/'.$folder))continue;
		if($folder=='.' || $folder=='..' || $folder=='.thumbs.dbr')continue;
		if(in_array(strtolower($folder),$excludedSubFolders))continue;
	
		$showFolder=true;
		
		$key=strtolower($folder);
		$title=$folder;
		
		//see if folder is restricted and change key-title
		if($a=@file($_SERVER['DOCUMENT_ROOT'].'/images/slides/'.$folder.'/protected.txt')){
			$hasTitle=false;
			foreach($a as $b){
				if(substr($b,0,1)=='#')continue;
				$c=explode(':',$b);
				
				//get visible title of the folder (folder name is masked)
				if(strtolower(trim($c[0]))=='title'){
					$title=trim($c[1]);
					$hasTitle=true;
				}
				
				//show/hide folder
				if(strtolower(trim($c[0]))=='hide') $showFolder=false;
				
				//get password(s) for this folder
				if(strtolower(trim($c[0]))=='password' || strtolower(trim($c[0]))=='passwords'){
					if($d=explode(',',trim($c[1]))){
						foreach($d as $e){
							$e=trim($e);
							if(!$e)continue;
							$passwords[$key][]=$e;
						}
					}
				}
			}
			if(!$hasTitle)$title='Untitled Slide '.$i++;
			$protected[$key]=$folder;
		}
		if($showFolder)$folders[$key]=$title;
	}
	if($folders){
		asort($folders);
		$thisprotected=false;
		foreach($folders as $key=>$folder){
			//override slide with a password protected slide request
			if(md5($protected[$key].$MASTER_PASSWORD)==$$subFolderVariable){
				$$subFolderVariable=$protected[$key];
				$slideTitle=$folder;
				$thisprotected=true;
			}else if(!$$subFolderVariable){
				//choose the first NON PASSWORD PROTECTED folder
				$$subFolderVariable=$slideTitle=$folder;
			}else if($$subFolderVariable==$folder){
				$slideTitle=$folder;
			}
	
			//get the folder
			if(strlen($protected[$key])){
				$thisslide = md5($protected[$key] . $MASTER_PASSWORD);
			}else{
				$thisslide = urlencode($folder);
			}
			//get protected status
			$protect=(strlen($protected[$key]) ? '1' : '0');
			
			//see if they can view or if they need to enter password
			$pwstatus=10; //no need for password
			if(strlen($protected[$key])){
				if(count($_SESSION['special']['slidePasswords'])){
					foreach($_SESSION['special']['slidePasswords'] as $j){
						if(count($passwords[$key]))
						foreach($passwords[$key] as $k){
							if($j==$k){
								$pwstatus=7; //password set
								$_SESSION['special']['accessibleSlidePages'][$protected[$key]]=true;
							}
						}
					}

				}
				if($pwstatus==10)$pwstatus=1; //needs to enter password for this slide
			}
			
			?><a href="<?php echo $thispage;?>?slide=<?php echo $thisslide ?>&protect=<?php echo $protect;?>&pwstatus=<?php echo $pwstatus?>" title="view this slide show"><?php echo $folder?></a><?php
			//edit link
			pJ_call_edit(array(
				'formNode'=>'folders',
				'level'=>ADMIN_MODE_DESIGNER,
				'thisnode'=>$thisnode,
				'location'=>'JULIET_COMPONENT_ROOT',
				'file'=>end(explode('/',__FILE__)),
				'parameters'=>array(
					'slide'=>$thisslide,
				),
			));
			?><br />
			<?php
		}
	}
	$$sNavNavigationBlock=ob_get_contents();
	ob_end_clean();
}
if($thisprotected && !$_SESSION['special']['accessibleSlidePages'][$$subFolderVariable]){
	?>You do not have access to this slide.  Most likely your session has timed out.  <a href="/<?php echo $thispage;?>">Click here to view all slides</a><?php
	exit;
}

ob_start();
?>
<script type="text/javascript" src="/Library/js/jquery.js"></script>
<script type="text/javascript" src="/Library/js/jquery.galleriffic.js"></script>
<script type="text/javascript" src="/Library/js/jquery.opacityrollover.js"></script>
<!-- We only want the thunbnails to display when javascript is disabled -->
<script type="text/javascript">document.write('<style>.noscript { display: none; }</style>');</script>

<?php
//edit link
pJ_call_edit(array(
	'level'=>ADMIN_MODE_DESIGNER,
	'location'=>'JULIET_COMPONENT_ROOT',
	'file'=>end(explode('/',__FILE__)),
	'thisnode'=>$thisnode,
	'thissection'=>$thissection,
));
if(pJ_getdata('slideShowPreText')){
	CMSB('slideShowPreText');
}
?><h1 class="galleryTitle"><?php
if(pJ_getdata('slideShowSource')=='folder' && $$subFolderVariable){
	echo $$subFolderVariable;
}
?></h1>
<div id="sNavContainer">
	<?php
	if(pJ_getdata('slideShowSource')=='folder'){
		$rootFolder=pJ_getdata('rootFolder');
		$path='images/'.$rootFolder.($$subFolderVariable ? '/'.$$subFolderVariable : '');
		$ssPictures=get_file_assets($_SERVER['DOCUMENT_ROOT'].'/'.$path);
		foreach($ssPictures as $n=>$v){
			$ssPictures[$n]['path']=$path;
			if(!preg_match('/\.(jpg|gif|png)$/i',$n))unset($ssPictures[$n]);
		}
		
		//create the database structure if not present, get title and description of pictures
		if($Albums_ID=q("SELECT ID FROM ss_albums WHERE Location='".addslashes($_SERVER['DOCUMENT_ROOT'].'/'.$path)."'",O_VALUE)){
			foreach($ssPictures as $n=>$v){
				if($a=q("SELECT Title, Description FROM ss_albums_pictures WHERE Albums_ID='$Albums_ID' AND FileName='".addslashes($v['name'])."'", O_ROW)){
					$ssPictures[$n]['title']=$a['Title'];
					$ssPictures[$n]['description']=$a['Description'];
				}
			}
		}
	}else if(is_numeric(pJ_getdata('slideShowSource'))){
		//get the pictures from the album
		$Albums_ID=pJ_getdata('slideShowSource');
		
		//handle these
		$Name;
		$AlbumDescription;
		$others; //array - other albums
	
		if($g=q("SELECT a.Name, a.Location, c.*, a.Description AS AlbumDescription FROM ss_albums a, ss_AlbumsPictures b, ss_pictures c WHERE a.ID=b.Albums_ID AND b.Pictures_ID=c.ID AND a.ResourceType IS NOT NULL AND c.ResourceType IS NOT NULL AND Tree_ID IS NOT NULL AND a.ID=$Albums_ID", O_ARRAY)){
			$i=0;
			foreach($g as $n=>$v){
				$i++;
				if($i==1){
					$album=$v;
				}
				$v['idx']=$i;
				$a=explode('/',trim(tree_id_to_path($v['Tree_ID']),'/'));
				if($v['size']=filesize($v['path'].'/'.$v['name'])){
					//OK
				}else{
					continue;
				}
				$v['name']=unhtmlentities(array_pop($a));
				$v['path']=trim(implode('/',$a),'/');
				$v['ext']=end(explode('.',$v['name']));
				$v['slideidx']=( ($x=parse_number($v['Title'])) ? $x : 10000 );
				$ssPictures[unhtmlentities(strtolower($v['name']))]=$v;
			}
		}
		$ssPictures=subkey_sort($ssPictures,'slideidx');
	}
	$maxWidth=$maxHeight=0;
	if($ssPictures /* removed this 2012-05-14; both canvas dimensions methods require maxWidth and MaxHeight && $sNavCanvasSize=='size' */){
		$canvasLandscape=floor($sNavCanvasWidth / $sNavCanvasHeight);
		foreach($ssPictures as $n=>$v){
			if($v['width']>$maxWidth)$maxWidth=$v['width'];
			if($v['height']>$maxHeight)$maxHeight=$v['height'];
			$landscape=floor($v['width']/$v['height']);
			if(!isset($allowedOverage))$allowedOverage=5;
			if($v['width'] - $sNavCanvasWidth > $allowedOverage || $v['height'] - $sNavCanvasHeight > $allowedOverage){
				if($sNavCanvasSize=='size')$ssPictures[$n]['resize']=true;
				if($landscape xor $canvasLandscape){
					//orientations do not match
					
				}
			}
		}
	}
	
	//ok here we do some cool math..
	if($sNavCanvasSize=='default'){
		$canvasWidth=($maxWidth ? $maxWidth : 500);
		$canvasHeight=($maxHeight ? $maxHeight : 375);
	}else{
		//size
		$canvasWidth=$sNavCanvasWidth;
		$canvasHeight=$sNavCanvasHeight;
	}
	$sNavThumbPadding=2;
	$sNavThumbSpacing=10;
	$sNavThumbBorder=1;
	
	if(!fmod($sNavLayout,2)){
		//bottom or top
		$sNavContainerWidth=$canvasWidth;
		//exit($sNavShowControls .':'.$sNavControlHeight.':'.$canvasHeight);
		$sNavContainerHeight=
			($sNavShowControls ? $sNavControlHeight : 0) +
			$canvasHeight +
			$sNavThumbRows * ($sNavThumbHeight + (2 * $sNavThumbPadding) + (2 * $sNavThumbBorder) + ($sNavThumbRows > 1 ? $sNavThumbSpacing * ($sNavThumbRows - 1) : 0));
		
	}else{
		//left or right
		$sNavContainerWidth=
			$canvasWidth + 
			$sNavGutter + 
			$sNavThumbCols * ($sNavThumbWidth + (2 * $sNavThumbPadding) + (2 * $sNavThumbBorder) + ($sNavThumbCols > 1 ? $sNavThumbSpacing * ($sNavThumbCols - 1) : 0));
	}
	
	//css for page header
	ob_start();
	?>
	<?php if(false){ ?><style type="text/css"><?php } ?>
	/* dimensional and layout attributes */
	#sNavContainer{
		border:<?php echo pJ_getdata('sNavBorder','none');?>;
		width:<?php echo $sNavContainerWidth;?>px;
		height:<?php echo $sNavContainerHeight;?>px;
		}
	div.slideshow a.advance-link, div.loader {
		width: <?php echo $canvasWidth;?>px;
		height: <?php echo $canvasHeight;?>px;
		}
	div.slideshow-container {
		height: <?php echo $canvasHeight;?>px;
		}
	span.image-caption {
		width: <?php echo $canvasWidth;?>px;
		left:0px;
		top:-100px;
		position: absolute;
		display: block;
		z-index:1000;
		color:white;
		}
	div#desc-background{
		width: <?php echo $canvasWidth;?>px;
		left:0px;
		top:-100px;
		position: absolute;
		background-color:black;

		-moz-opacity:.50;
		filter:alpha(opacity=50);
		opacity:.50;

		display: block;
		z-index:900;
		height:75px;
		}
	/*
	---------------- this was all my dimensions --------------------
	div.slideshow a.advance-link, div.loader {
		width: 550px;
		height: 502px;
		}
	div.slideshow-container {
		height: 502px;
		}
	span.image-caption {
		width: 550px;
		}
	div.caption-container {
		height: 75px;
		}
	----------------------------------------------------------------
	*/
	#thumbs{
		float:<?php echo $sNavLayout==1 /* left */ ? 'left' : ($sNavLayout==3 ? 'right' : /* top or bottom */ 'none');?>;
		}
	#gallery{
		float:<?php echo $sNavLayout==1 /* left */ ? 'right' : ($sNavLayout==3 ? 'left' : /* top or bottom */ 'none');?>;
		}
	div.slideshow span.image-wrapper{
		top: 0px;
		left: 0px;
		position: absolute;
		display: block;
		}
	div.loader {
		top: 0px;
		left: 0px;
		position: absolute;
		background-image: url('loader.gif');
		background-repeat: no-repeat;
		background-position: center;
		}
	<?php if(!$sNavShowPagination){ ?>
	div.pagination{
		display:none;
		}
	<?php } ?>

	div#controls{
		height:23px;
		}
	div.ss-controls {
		float: left;
		}
	div.nav-controls {
		float: right;
		}


	<?php if(false){ ?></style><?php } ?>
	<?php
	$pJLocalCSS[$handle.'_dimensions']=ob_get_contents();
	ob_end_clean();
	if($sNavAdditionalCSS)$pJLocalCSS[$handle]=$sNavAdditionalCSS;
	
	
	//HTML Layout #1
	ob_start();
	?>
	<div id="gallery" class="content">
		<?php if($sNavShowControls){ ?>
		<div id="controls" class="controls"></div>
		<?php } ?>
		<div class="slideshow-container">
			<div id="loading" class="loader"></div>
			<div id="slideshow" class="slideshow"></div>
		</div>
		<div id="caption" class="caption-container"><div id="desc-background"></div></div>
	</div>
	<?php
	$div1=ob_get_contents();
	ob_end_clean();

	//HTML Layout #2
	ob_start();
	?>
	<span id="thumbsWrap"><div id="thumbs" class="navigation" style="font-style:italic;">
		<ul class="thumbs noscript">
		<?php
		#print_r($ssPictures);
		#exit;
		if($ssPictures){
			foreach($ssPictures as $n=>$v){
				if($v['resize']){
					//use tree functions for standardization
					$Tree_ID=tree_build_path($v['path'].'/'.$v['name']);
					$href='/images/reader.php?Tree_ID='.$Tree_ID.'&Key='.md5($Tree_ID.$MASTER_PASSWORD).'&disposition='.$sNavCanvasWidth.'x'.$sNavCanvasHeight.($sNavResizing==2 ? '&boxMethod=2' : '');
				}else{
					$href='/'.$v['path'].'/'.$v['name'];
				}
				if($sNavShowThumbs && ($sNavThumbWidth!=95 || $sNavThumbHeight!=95 || $sNavThumbResizing!=1)){
					$Tree_ID=tree_build_path($v['path'].'/'.$v['name']);
					$src='/images/reader.php?Tree_ID='.$Tree_ID.'&Key='.md5($Tree_ID.$MASTER_PASSWORD).'&disposition='.$sNavThumbWidth.'x'.$sNavThumbHeight.($sNavThumbResizing==2 ? '&boxMethod=2' : '');
				}else{
					$src='/'.($v['path'] ? $v['path'].'/' : '').'.thumbs.dbr/'.$v['name'];
				}
				$descr=pJ_getdata(array('field'=>'sNavImgDescription','subKey'=>$n,'default'=>$n));
				?><li>
				<a class="thumb" href="<?php echo $href;?>" title="<?php echo $v['Title'];?>">
				<img src="<?php echo $src;?>" alt="<?php echo $descr ? h($descr) : $v['name'];?>" /></a>
				<div class="caption">
					<div class="image-title"><?php echo h(pJ_getdata(array('field'=>'sNavImgTitle','subKey'=>$n,'default'=>$n)));?></div>
					<div class="image-desc"><?php echo h($descr);?></div>
				</div>
				</li><?php
			}
		}else{
		
		}
		?>
		</ul>
	</div></span>
	<?php
	$div2=ob_get_contents();
	ob_end_clean();
	//now output
	if($sNavLayout==3){
		//thumbs on top
		echo $div2 . $div1;
	}else{
		echo $div1 . $div2;
	}
	?>
	<div style="clear: both;"></div>
</div>
<script type="text/javascript">
jQuery(document).ready(function($) {
	// We only want these styles applied when javascript is enabled
	//$('div.navigation').css({'width' : '<?php echo $sNavNavWidth;?>px'});
	$('div.content').css('display', 'block');

	// Initially set opacity on thumbs and add
	// additional styling for hover effect on thumbs
	var onMouseOutOpacity = 0.67;
	$('#thumbs ul.thumbs li').opacityrollover({
		mouseOutOpacity:   onMouseOutOpacity,
		mouseOverOpacity:  1.0,
		fadeSpeed:         'fast',
		exemptionSelector: '.selected'
	});
	
	// Initialize Advanced Galleriffic Gallery
	var gallery = $('#thumbs').galleriffic({
		delay:                     <?php echo $sNavSlideDelay;?>,
		numThumbs:                 <?php echo $sNavThumbRows * $sNavThumbCols;?>,
		preloadAhead:              10,
		enableTopPager:            true,
		enableBottomPager:         true,
		maxPagesToShow:            7,
		imageContainerSel:         '#slideshow',
		controlsContainerSel:      '#controls',
		captionContainerSel:       '#caption',
		loadingContainerSel:       '#loading',
		renderSSControls:          true,
		renderNavControls:         true,
		playLinkText:              '<?php echo pJ_getdata('sNavPlayLinkText','Play Slideshow');?>',
		pauseLinkText:             '<?php echo pJ_getdata('sNavPauseLinkText','Pause Slideshow');?>',
		prevLinkText:              '<?php echo pJ_getdata('sNavPrevLinkText','&lsaquo; Previous Photo');?>',
		nextLinkText:              '<?php echo pJ_getdata('sNavNextLinkText','Next Photo &rsaquo;');?>',
		nextPageLinkText:          'Next &rsaquo;',
		prevPageLinkText:          '&lsaquo; Prev',
		enableHistory:             false,
		autoStart:                 <?php echo $sNavSlideAutoStart ? 'true':'false';?>,
		syncTransitions:           true,
		defaultTransitionDuration: 900,
		onSlideChange:             function(prevIndex, nextIndex) {
			// 'this' refers to the gallery, which is an extension of $('#thumbs')
			this.find('ul.thumbs').children()
				.eq(prevIndex).fadeTo('fast', onMouseOutOpacity).end()
				.eq(nextIndex).fadeTo('fast', 1.0);
		},
		onPageTransitionOut:       function(callback) {
			this.fadeTo('fast', 0.0, callback);
		},
		onPageTransitionIn:        function() {
			this.fadeTo('fast', 1.0);
		}
	});
});
</script><?php


if(pJ_getdata('slideShowPostText')){
    CMSB('slideShowPostText');
}
$$sNavSlideBlock=ob_get_contents();
ob_end_clean();



switch(true){
	case $mode=='editdescription':
		if($password !==$MASTER_PASSWORD){
			?><script language="javascript" type="text/javascript">
			window.parent.g('password').value='';
			alert('Your password is not correct');
			</script><?php
			exit;
		}
		q($config['tables']['albums']);
		q($config['tables']['pictures']);
		if(!($Albums_ID=q("SELECT ID FROM ss_albums WHERE Location='$Location'", O_VALUE))){
			$Albums_ID=q("INSERT INTO ss_albums SET
			Location='$Location',
			CreateDate=NOW(),
			Creator='$MASTER_USERNAME',
			EditDate=NOW()", O_INSERTID);
		}
		if($ID=q("SELECT ID FROM ss_albums_pictures WHERE Albums_ID='$Albums_ID' AND FileName='$FileName'", O_VALUE)){
			q("UPDATE ss_albums_pictures SET
			Description='$Description' WHERE
			ID=$ID");
		}else{
			q("INSERT INTO ss_albums_pictures SET
			Albums_ID='$Albums_ID',
			FileName='$FileName',
			Description='$Description',
			CreateDate=NOW(),
			Creator='$MASTER_USERNAME'");
		}
		?>
		<div id="fill"><?php echo htmlentities(stripslashes($Description))?></div>
		<script language="javascript" type="text/javascript">
		//update static copy of description
		window.parent.g('description_<?php echo $Idx?>').innerHTML=document.getElementById('fill').innerHTML;
		</script>
		<?php
	break;
}


}//---------------- end i break loop ---------------
?>