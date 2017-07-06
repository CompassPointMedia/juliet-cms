<?php
//identify this script/GUI
$localSys['scriptGroup']='';
$localSys['scriptID']='generic';
$localSys['scriptVersion']='1.0';
$localSys['pageType']='Properties Window';



//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');
if(!$Albums_ID)exit('no album id passed');

if($g=q("SELECT a.Name, a.Location, c.*, a.Description AS AlbumDescription FROM ss_albums a, ss_AlbumsPictures b, ss_pictures c WHERE a.ID=b.Albums_ID AND b.Pictures_ID=c.ID AND a.ResourceType IS NOT NULL AND c.ResourceType IS NOT NULL AND Tree_ID IS NOT NULL AND a.ID=$Albums_ID", O_ARRAY)){
	$i=0;
	foreach($g as $n=>$v){
		$i++;
		$v['idx']=$i;
		$a=explode('/',trim(tree_id_to_path($v['Tree_ID']),'/'));
		$v['name']=array_pop($a);
		$v['path']=trim(implode('/',$a),'/');
		$v['ext']=end(explode('.',$v['name']));
		if($v['size']=filesize($_SERVER['DOCUMENT_ROOT'].'/'.$v['path'].'/'.$v['name'])){
			//OK
		}else{
			continue;
		}
		if($a=@getimagesize($_SERVER['DOCUMENT_ROOT'].'/'.$v['path'].'/'.$v['name'])){
			$v['width']=$a[0];
			$v['height']=$a[1];
			$v['mime']=$a['mime'];
		}else{
			continue;
		}
		$maxWidth=($v['width']>$maxWidth ? $v['width'] : $maxWidth);
		$maxHeight=($v['height']>$maxHeight ? $v['height'] : $maxHeight);
		$ssPictures[strtolower($v['name'])]=$v;
	}
}
$record=current($ssPictures);



?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Slideshow : <?php echo $record['Name']?></title>

<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" type="text/css" href="rbrfm_admin.css" />
<link rel="stylesheet" type="text/css" href="/Library/css/DHTML/dynamic_04_i1.css" />
<style type="text/css">
body{
	color:white;
	background-color:black;
	}
</style>

<script src="/Library/js/global_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/common_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/forms_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/loader_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/console/console.js" language="javascript" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
/* periwinkle coding 2.1 */
var thispage='<?php echo $thispage?>';
var thisfolder='<?php echo $thisfolder?>';
var browser='<?php echo $browser?>';
var ctime='<?php echo $ctime?>';
var PHPSESSID='<?php echo $PHPSESSID?>';
//for nav feature
var count='<?php echo $nullCount?>';
var ab='<?php echo $nullAbs?>';
var isEscapable=1;
var isDeletable=0;
var isModal=0;
var talks=0; //whether this page broadcasts overall state changes which other pages listen for
var listens=0;

window.moveTo(0,0);
window.resizeTo(screen.width,screen.height);


AddOnkeypressCommand("PropKeyPress(e)");

</script>
</head>

<body>
	<?php
	//------------ working code snippet 2009-08-23 --------------- 
	$ssShowGallery=true;
	$ssShowControls=true;
	$ssIntegrateText=false;
	$ssThumbsCreate=true;
	$ssThumbsWidth=60;
	$ssThumbsHeight=60;
	$ssGalleryFrameSize=8;
	$ssUseDynamicTitles=false;
	$ssUseDynamicDescriptions=false;
	$ssGalleryCreateThumbs=true;
	$ssReindexDelay=6000;
	$ssGalleryTitle='';
	$fsPathElevator='../';
	?><script language="javascript" type="text/javascript">
	var maxWidth=<?php echo $maxWidth;?>;
	var maxHeight=<?php echo $maxHeight;?>;
	</script><?php
	$boxMode=3;
	$boxWidth=500;
	$boxHeight=500;
	$ssFolder=$_SERVER['DOCUMENT_ROOT'] .'/images/slides';
	if(!is_dir($ssFolder) && !mkdir($ssFolder))exit('unable to create a folder name slides in images');

	$ssFolder.='/gallery_thumbs';
	if(!is_dir($ssFolder) && !mkdir($ssFolder))exit('unable to create a folder named gallery_thumb in slides');

	$ssFolder.='/album_'.$Albums_ID;
	if(!is_dir($ssFolder) && !mkdir($ssFolder))exit('unable to create a thumb gallery folder album_'.$Albums_ID);
	//so we can move things around
	$ssRewrite=true;
	require($MASTER_COMPONENT_ROOT.'/comp_slideshow_v131.php');
	?>
	<style type="text/css">
	#ssGallery{
		background-color:darkred;
		width:155px;
		float:right;
		margin:0px 0px 20px 25px;
		background-color:#000;
		text-align:center;
		}
	#ssControls{
		position:relative;
		top:25px;
		left:0px;
		z-index:1000;
		}
	#ssControls #ctrlBg{
		background-color:#786962;
		-moz-opacity:0.7;
		opacity:0.7;
		height:20px;
		width:100%;
		}
	#ssControls #ctrlFg{
		position:absolute;
		left:0px;
		top:0px;
		text-align:center;
		}
	#ssControls span{
		padding-left:10px;
		}
	.slidePresentation{
		background-color:black;
		}
	.slidePresentation .main{
		height:350px;
		}
	.slidePresentation td{
		border:1px solid white;
		}
	#ly1, #ly2{
		margin-top:-20px;}
	</style>
	<h2><?php echo $record['Name']?></h2>
	<table class="slidePresentation" width="100%" border="0" cellspacing="4" cellpadding="5">
		<tr>
			<td colspan="2" style="border:none;">&nbsp;</td>
		</tr>
		<tr>
			<td id="ssCell" class="main"><?php 
			$out=str_replace($ssGallery,'',$ssOutput);
			$out=str_replace($ssControls,'',$out);
	
			//place controls over the pictures absolutely
			$out=str_replace('<div id="ssComponent">','<div id="ssComponent">'.$ssControls,$out);
			echo $out;
			?></td>
			<td width="175px"><?php echo $ssGallery?></td>
		</tr>
	</table>
	<script language="javascript" type="text/javascript">
	function resizely12(){
		var cellWidth=g('ssCell').offsetWidth-20;
		var cellHeight=g('ssCell').offsetHeight-20;
		document.write('<style type="text/css">#ly1,#ly2{width:'+cellWidth+'px; height:'+cellHeight+'px;}</style>');
	}
	resizely12();
	window.onresize=resizely12();
	</script>
	<div class="cb">&nbsp;</div>
</body>
</html>
