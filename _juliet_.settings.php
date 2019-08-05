<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/config.php');
require_once($FUNCTION_ROOT.'/function_CSS_parser_v100.php');

if($adminMode<ADMIN_MODE_DESIGNER)exit('You do not have access to this page, or your session has expired');

//get a list of all possibilities - so far only used for mode=blockManager
$pJModalInclusion=true;
require($pJulietTemplate);

//OK we need this array declared in the template settings or parameters
$baseParameters=array(
	'body'=>array(
		'description'=>'This is the "canvas" of the webpage, or the entire viewing region in the browser window',
		'header'=>'Body',
	),
	'mainWrap'=>array(
		'description'=>'This is the entire page of the website',
		'header'=>'Main Wrapper',
		'hideLinkAttribs'=>true,
		'exclusion'=>array('color','font-family','font-size'),
	),
	'topRegion'=>array(
		'description'=>'',
		'header'=>'Top Region',
		'exclusion'=>array('color','font-family','font-size'),
	),
	'mainRegion'=>array(
		'description'=>'This is the middle section of your web page',
		'header'=>'Main Region',
	),
	'bottomRegion'=>array(
		'description'=>'',
		'header'=>'Bottom Region',
	),
);
$nativeTags=array('html','body');

if($submode=='blockManager'){
	$templateName=end(explode('/',$pJulietTemplate));
	$templateName=preg_replace('/\.php$/','',$templateName);
	if($Templates_ID=q("SELECT ID FROM gen_templates WHERE Name='$templateName'", O_VALUE)){
		//OK
	}else{
		error_alert('cannot locate template '.$pJulietTemplate);
	}
	//rewrite the component
	if($desiredAction=='{NON_PHP_EDITABLE}'){
		$Content='<?php CMSB(\'common(0):'.$block.'_common\');?>';
	}else if($desiredAction=='{CUSTOM_PHP_EDITABLE}'){
		$Content=stripslashes($FreeContent);
	}else if(strlen($desiredAction)){
		$Content='<?php require('.str_replace(':','.\'/',preg_replace('/\.php$/','',$desiredAction)).'.php'.'\');?>';
	}else{
		$Content='';
	}
	
	if($Blocks_ID=q("SELECT * FROM gen_templates_blocks WHERE Templates_ID='$Templates_ID' AND Name='$block'", O_VALUE)){
		prn($qr);
		q("UPDATE gen_templates_blocks SET 
		Position='$Position',
		Content='".addslashes($Content)."' 
		WHERE ID=$Blocks_ID");
		prn($qr);
	}else{
		$Blocks_ID=q("INSERT INTO gen_templates_blocks SET 
		Name='$block',
		Templates_ID=$Templates_ID,
		Position='$Position',
		Content='".addslashes($Content)."',
		CreateDate=NOW(),
		Creator='".($_SESSION['systemUserName'] ? $_SESSION['systemUserName'] : ($PHP_AUTH_USER ? $PHP_AUTH_USER : $MASTER_USERNAME))."'", O_INSERTID);
		prn($qr);
	}
	//rewrite the css
	if(file_exists($CSSFile=$_SERVER['DOCUMENT_ROOT'].'/site-local/'.$acct.'.'.$templateName.'.css')){
		$str=implode('',file($CSSFile));
	}else{ 
		$str=implode('',file($CSSFile=$_SERVER['DOCUMENT_ROOT'].'/site-local/'.'.'.$templateName.'.css'));
	}
	
	require_once($FUNCTION_ROOT.'/function_CSS_parser_v100.php');
	CSS_parser(implode('',file($CSSFile)));
	$a=$CSS_parser['declarations'];
	unset($CSS_parser);
	//notice we stripslashes as we are not going into a database
	CSS_parser(stripslashes($CSS));
	$b=$CSS_parser['declarations'];
	if($a && $b)
	foreach($b[1] as $o=>$w){
		$matchExisting=false;
		unset($oIdx);
		foreach($a[1] as $n=>$v){
			if($v==$w){
				$matchExisting=true;
				$oIdx=$o;
				$origStr=$str;
				$str=str_replace($a[0][$n],$b[0][$o],$str);
				if($origStr!==$str){
					prn("changing\n".$a[0][$n]."\nto\n".$b[0][$o]);
					$strChanged=true;
				}	
			}
		}
		if(!$matchExisting && strstr($b[1][$o],$block)){
			if($oIdx){
				//add it
				$origStr=$str;
				$str=str_replace($b[0][$oIdx],$b[0][$oIdx]."\n".$b[0][$o],$str);
				if($origStr!==$str)$strChanged=true;
			}else{
				$str.="\n".$b[0][$o];
				$strChanged=true;
			}
		}
	}
	if($strChanged){
		//back up file
		copy($CSSFile,str_replace('.css','_bk'.time().'.css',$CSSFile));

		//write new CSS		
		$fp=fopen($CSSFile,'w');
		fwrite($fp,$str,strlen($str));
	}
	?><script language="javascript" type="text/javascript">
	window.parent.detectChange=0;
	</script><?php
}else if($submode=='pageManager'){
	//rewrite the css
	if(file_exists($CSSFile=$_SERVER['DOCUMENT_ROOT'].'/site-local/'.$acct.'.'.$templateName.'.css')){
		$str=implode('',file($CSSFile));
	}else{ 
		$str=implode('',file($CSSFile=$_SERVER['DOCUMENT_ROOT'].'/site-local/'.'.'.$templateName.'.css'));
	}
	$str=trim($str);
	require_once($FUNCTION_ROOT.'/function_CSS_parser_v100.php');
	CSS_parser(implode('',file($CSSFile)));
	$a=$CSS_parser['declarations'];
	
	foreach($_POST as $n=>$v){
		if(!is_array($v))continue;
		$n= 
		/* replace descendents as added */
		str_replace('_a',' a', 
		str_replace('_a_hover',' a:hover', 
			$n
		));
		$n_ancestry=explode(' ',$n);
		$n=(in_array(strtolower($n_ancestry[0]),$nativeTags) ? '' : '#').$n;

		$block='';
		foreach($v as $o=>$w){
			if(!trim($w))continue;
			if($o=='attributes'){
				$w=preg_replace('/;\s*/',';'."\n\t",stripslashes($w));
				$block.="\t".trim($w,"\t")."\n";
			}else{
				$block.="\t".$o.':'.rtrim(stripslashes($w),';').';'."\n";
			}
		}
		$block=$n.'{'."\n".$block."\t".'}'."\n";
		unset($key);
		foreach($a[1] as $o=>$w){
			if($n==$w){
				$key=$o;
				break;
			}
		}
		if(strlen($key)){
			//make sure we don't lose any information here!
			$str=str_replace($a[0][$key], $block, $str);
		}else{
			//add the block - unlikely case if we developed the CSS page but possible
			$n_parent=explode(' ',$n);
			$key=array_search($n_parent[0],$a[1]);
			if(strlen($key)){
				$str=str_replace($a[0][$key], $block.$a[0][$key], $str);
			}else{
				/* put at the top; this may affect precedence */
				mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($notice='a css block region not in the current CSS file was inserted at the TOP of the CSS file'),$fromHdrBugs);
				$str=$block.(!$blockInserted ? '/* ----------- end inserted block ----------- */'."\n" : '').$str;
				$blockInserted=true;
			}
		}
	}
	if($blockInserted)$str='/* ------------------ block inserted '.date('n/j/Y @g:iA').' ------------------------ */'."\n".trim($str);

	copy($CSSFile,str_replace('.css','_bk'.time().'.css',$CSSFile));

	//write new CSS		
	$fp=fopen($CSSFile,'w');
	fwrite($fp,$str,strlen($str));
}else if($submode=='stylesheetManager'){
	$f=$_SERVER['DOCUMENT_ROOT'].'/site-local/'.$acct.'.'.$templateName.'.global.css';

	if(!trim($AdditionalGlobalCSS)){
		unlink($f);
	}else{
		if(file_exists($f)){
			//backup
			copy($f,str_replace('.css','.bk'.time().'.css',$f));
		}
		$fp=fopen($f,'w');
		fwrite($fp,stripslashes($AdditionalGlobalCSS),strlen(stripslashes($AdditionalGlobalCSS)));
		fclose($fp);
	}
}else if($submode=='codingManager'){
	$f=$_SERVER['DOCUMENT_ROOT'].'/site-local/'.$acct.'.'.$templateName.'.global.php';

	if(!trim($AdditionalGlobalPHP)){
		unlink($f);
	}else{
		$AdditionalGlobalPHP=trim($AdditionalGlobalPHP);
		$AdditionalGlobalPHP='<?php'."\n".$AdditionalGlobalPHP."\n".'?>';
		if(file_exists($f)){
			//backup
			copy($f,str_replace('.php','.bk'.time().'.php',$f));
		}
		$fp=fopen($f,'w');
		fwrite($fp,stripslashes($AdditionalGlobalPHP),strlen(stripslashes($AdditionalGlobalPHP)));
		fclose($fp);
	}
	$assumeErrorState=false;
	exit;
}else if($submode=='jsManager'){
	$f=$_SERVER['DOCUMENT_ROOT'].'/site-local/'.$acct.'.'.$templateName.'.global.js';

	if(!trim($AdditionalGlobalJS)){
		unlink($f);
	}else{
		$AdditionalGlobalJS=trim($AdditionalGlobalJS);
		if(file_exists($f)){
			//backup
			copy($f,str_replace('.js','.bk'.time().'.js',$f));
		}
		$fp=fopen($f,'w');
		fwrite($fp,stripslashes($AdditionalGlobalJS),strlen(stripslashes($AdditionalGlobalJS)));
		fclose($fp);
	}
	$assumeErrorState=false;
	exit;
}
if($submode=='blockManager' || $submode=='pageManager' || $submode=='stylesheetManager'){
	if($refreshOpener){ ?><script language="javascript" type="text/javascript">
		try{
		window.parent.detectChange=0;
		}catch(e){}
		var l=window.parent.opener.location+'';
		l=l.replace(/(&|\b)r=[.0-9]+/,'');
		l=l+(l.indexOf('?')!= -1 ? '' : '?') + (l.split('?')[1]?'&':'') + 'r=' + (Math.random()+'').substring(2,6);
		window.parent.opener.location=l;
		</script><?php
	}
	$assumeErrorState=false;
	exit;
}
/*
todo
	deal with current state - persist
	DONE	parse css
	
2011-08-30
	now parsing out css and called the block manager


*/
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo $mode=='pageManager'?'Page Manager':'Block Manager';?></title>

<link href="/Library/css/cssreset01.css" type="text/css" rel="stylesheet" />
<link href="/Library/css/_juliet_.settings.css" type="text/css" rel="stylesheet" />
<style type="text/css">
body{
	margin:5px 20px;
	}
.section{
	margin:5px 20px;
	border:1px solid #ccc;
	padding:10px;
	}
input.myform, textarea.myform, select.myform { 
	padding: 5px 10px; border-width: 2px; border-style: solid; color: #333; background: #fff; font-size: 14px;
	margin-top:2px;
}
input.myform:focus, textarea.myform:focus, select.myform:focus { 
	border-color: #de8800 
	}
input.myform, textarea.myform, select.myform {
    border-color: #99BD0C;
	}
input[type="submit"], input#submit, input.submit {
    background-color: #6D8709;
    color: #FFFFFF;
	}
select{
	margin-top:2px;
	}
form{
	}
.settingsNav{
	padding:0px;
	margin-bottom:2px;
	}
.settingsNav li{
	display:inline;
	margin-bottom:10px;
	list-style:none;
	border:1px solid #333;
	border-bottom:none;
	padding:2px 7px;
	margin:0px;
	margin-right:7px;
	}
.settingsNav li a{
	color:darkgreen;
	}
.settingsNav li.more{
	margin-left:30px;
	}
h2{
	clear:both;
	}
.underNav{
	border-top:1px solid #333;
	font-size:1px;
	height:1px;
	}
.hl{
	background-color:cornsilk;
	}
</style>
<script src="/Library/js/jquery.js" language="javascript" type="text/javascript"></script>
<script src="/Library/js/jquery.tabby.js" language="javascript" type="text/javascript"></script>
<script src="/Library/js/global_04_i1.js" language="javascript" type="text/javascript"></script>
<script src="/Library/js/common_04_i1.js" language="javascript" type="text/javascript"></script>
<script src="/Library/js/forms_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/loader_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
/* periwinkle coding */
var browser='<?php echo $browser;?>';
var count='<?php echo $nullCount?>';
var ab='<?php echo $nullAbs?>';
CMSBEditorURL='cms3.01.php';

var bufferBlock='';
function switchBlocks(o){
	if(detectChange && !confirm('You have made changes to the existing block; this action will discard those changes.  Continue?')){
		o.value=bufferBlock;
		return false;
	}
	var n=window.location+'';
	window.location=n.replace(/block=[a-z0-9]+/gi,'block='+o.value);
}
var isEscapable=1;
AddOnkeypressCommand('PropKeyPress(e)'); //if not declared already
</script>
</head>

<body>
<div id="mainWrap">
<form id="form1" name="form1" method="post" target="w2" action="/index_01_exe.php">
<?php
if(!$mode)$mode='blockManager';
$qs=$QUERY_STRING;
$qs=preg_replace('/&*mode=[^&]*/','',$qs);
$qs=$qs.(strlen($qs)?'&':'').'mode=';
?>
<ul class="settingsNav">
<li class="more <?php echo $mode=='pageManager'?'hl':''?>"><a href="_juliet_.settings.php?<?php echo $qs.'pageManager';?>">Page Manager</a></li>
<li class="more <?php echo $mode=='blockManager'?'hl':''?>"><a href="_juliet_.settings.php?<?php echo $qs.'blockManager';?>">Block Manager</a></li>
<li class="more <?php echo $mode=='stylesheetManager'?'hl':''?>"><a href="_juliet_.settings.php?<?php echo $qs.'stylesheetManager';?>">Additional CSS</a></li>
<li class="more <?php echo $mode=='jsManager'?'hl':''?>"><a href="_juliet_.settings.php?<?php echo $qs.'jsManager';?>">Additional JS</a></li>
<li class="more <?php echo $mode=='codingManager'?'hl':''?>"><a href="_juliet_.settings.php?<?php echo $qs.'codingManager';?>">PHP Coding</a></li>
</ul>
<div class="underNav"> </div>
<?php

if($mode=='blockManager'){
	if($Templates_ID=q("SELECT ID FROM gen_templates WHERE Name='".str_replace('.php','',end(explode('/',$pJulietTemplate)))."'",O_VALUE)){
		//ok
	}else{
		if(!($a=q("SELECT * FROM relatebase_template.gen_templates WHERE Name='".str_replace('.php','',end(explode('/',$pJulietTemplate)))."'",O_ROW, C_SUPER))){
			mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err='did not have the templates_id and could not find in relatebase_template'),$fromHdrBugs);
			error_alert($err);
		}
		$MasterTemplates_ID=$a['ID'];
		$sql='INSERT INTO gen_templates SET ';
		foreach($a as $n=>$v){
			if($n=='ID' || !strlen($v))continue;
			$sql.=$n.'=\''.addslashes($v).'\', ';
		}
		$Templates_ID=q(rtrim($sql,', '), O_INSERTID);
		$add=q("SELECT MAX(ID) FROM gen_templates_blocks",O_VALUE);
		foreach(q("SELECT * FROM relatebase_template.gen_templates_blocks WHERE Templates_ID=$MasterTemplates_ID", O_ARRAY, C_SUPER) as $v){
			$v['Templates_ID']=$Templates_ID;
			$sql='INSERT INTO gen_templates_blocks SET ';
			foreach($v as $o=>$w){
				if(!strlen($w))continue;
				if($o=='ID')$w+=$add;
				if($o=='Blocks_ID')$w+=$add;
				$sql.=$o.'=\''.addslashes($w).'\', ';
			}
			q(rtrim($sql,', '));
		}
	}
	if($a=q("SELECT Content, Parameters, Position FROM gen_templates_blocks WHERE Templates_ID='$Templates_ID' AND Name='$block'", O_ROW)){
		extract($a);
	}else{
		?><script language="javascript" type="text/javascript">
		setTimeout('window.location=\'/_juliet_.settings.php?mode=blockManager&block=mainWrap\';',2000);
		</script><?php
		exit('The block '. $block.' is not set up in table gen_templates_blocks for Juliet template '.$pJulietTemplate.' - redirecting to mainWrap');
	}
	?>
	<h2>Block Manager</h2>
	<div class="fr" style="display:none;">
	<img src="/images/i/juliet/blockmgr.png" width="404" height="117" /></div>
	<p class="gray">The following are options for this block which is shared across all pages of the site.  See the diagram to the right. Normally you would do this for the footer, top, and either a left or right region. See technical notes (developer's notes) at the bottom.  Select an option and adjust settings as necessary</p>
	Block Name: <select class="myform" name="block" id="block" onChange="switchBlocks(this);" onFocus="bufferBlock=this.value;">
	<?php
	//show selected block parameters
	recurse_array($templateDefinedBlocks,1,array('type'=>'options'));
	?>
	</select>
	<br />
	<?php 
	if($block==$primaryBlockName || $block==$secondaryBlockName || $block==$tertiaryBlockName){
		$label=($block==$primaryBlockName ? 'primary' : ($block==$secondaryBlockName ? 'secondary' : ($block==$tertiaryBlockName ? 'tertiary':'')));
		?><div class="balloon1">
		WARNING!! This is the <?php echo $label ?> content block for this template.  You SHOULD NOT select a desired component for this block!!  It will override all pages and you will not have any content on the site (and I am not sure how the system will really behave if you select this).
		</div><?php
	}
	?>
	Parent Block: <span style="font-size:139%;"><?php echo $recurse_array['parent'] ? $recurse_array['parent'] : '<em>none</em>';?></span><br />
	<br />
	<strong>Edit 
	CSS for this block:</strong><br />
	<p class="gray">NOTE: system uses a rough algorithm for determining matching CSS declarations: any declaration with #block in the ancestor is editable.  Each ancestry must be unique.  New declared ancestries will be added as close to the order specified as possible.  You cannot delete a declaration of a block once created; just remove all attributes to have the same effect</p>
	<p>
	<a href="http://en.wikipedia.org/wiki/Web_colors#X11_color_names" title="View web color words" onClick="return ow(this.href,'l1_colors','600,600');">web color list</a>&nbsp;&nbsp;
	<a href="/admin/file_explorer/?uid=blockmanager" onClick="return ow(this.href,'l1_fex','800,700');">File Explorer</a>
	</p>
	<textarea name="CSS" cols="60" rows="8" class="myform tabby" id="CSS"><?php
	//pull the CSS page, parse it, and place all declarations here
	//comments like /* */ are mapped; strings like 'asdf' or "asdf" are mapped - for safety
	//then we declare the rows one by one
	
	//get css declarations
	ob_start();
	CSS_parser(implode('',file('site-local/'.$pJCSSIncludedFile)));
	if($a=$CSS_parser['declarations'][1])
	foreach($a as $n=>$v){
		if(preg_match('/\b'.$block.'\b/',$v)){
			echo $CSS_parser['declarations'][0][$n]."\n";
		}
	}
	$out=ob_get_contents();
	ob_end_clean();
	echo trim($out);
	/*
	if($CSS_parser['declarations'][1])
	foreach($CSS_parser['declarations'][1] as $n=>$v){
		echo $n . ':' . $v . "\n";
		continue;
		if(preg_match('/\b'.$block.'\b/',$v)){
			echo $v . "\n\n";
		}
	}
	
	*/
	?>
	</textarea>
	<br />
	
	<script language="javascript" type="text/javascript">
	function componentInterlock(o){
		if(o.value=='{CUSTOM_PHP_EDITABLE}'){
			g('FreeContent').style.display='block';
			g('FreeContent').focus();
		}else{
			g('FreeContent').style.display='none';
		}
	}
	</script>
	Positioning or output for this element is 
	<?php
	$a=q("SHOW CREATE TABLE gen_templates_blocks", O_ROW);
	preg_match('/`Position` enum\(([^)]+)\)/i',$a['Create Table'],$a);
	if(!$a)exit('gen_templates_blocks.Position field is not value ENUM');
	$a=explode("','",trim($a[1],'\''));
	?>
	<select name="Position" class="myform" id="Position" onChange="if(this.value=='none')alert('This will suppress output of this block completely (across all pages unless specified otherwise); be sure this is what you intend');dChge(this);">
	<option value="">&lt;select..&gt;</option>
	<?php
	foreach($a as $v){
		?><option value="<?php echo $v?>" <?php echo $Position==$v?'selected':''?> <?php if($v=='none')echo 'style="font-style:italic;color:#666;"';?>><?php echo $v?></option><?php
	}
	?>
	</select>
	<br />
	<br />
	<strong>Select a component or content method for this block:</strong><br />
	<span class="gray">(will be used for this block across all pages on the site) </span><br />
<?php
require_once($FUNCTION_ROOT.'/function_pJprocess_folder.php');
pJprocess_folder(array(
	'files'=>get_file_assets($JULIET_COMPONENT_ROOT),
	'folder'=>'JULIET_COMPONENT_ROOT',
));
pJprocess_folder(array(
	'files'=>get_file_assets($COMPONENT_ROOT),
	'folder'=>'COMPONENT_ROOT',
	'requireDocumentation'=>true,
));
if(!strstr(trim($Content),"\n")){
	if(!strstr($Content,"\n")){
		//single call must be on one line
		if(strstr($Content,'$JULIET_COMPONENT_ROOT')){
			$desiredAction=str_replace('<?php require($JULIET_COMPONENT_ROOT.\'/','',$Content);
			$desiredAction=str_replace('\');?>','',$desiredAction);			
			$desiredActionLocation='$JULIET_COMPONENT_ROOT';
		}else if(strstr($Content,'$COMPONENT_ROOT')){
			$desiredAction=str_replace('<?php require($COMPONENT_ROOT.\'/','',$Content);
			$desiredAction=str_replace('\');?>','',$desiredAction);			
			$desiredActionLocation='$COMPONENT_ROOT';
		}else{
			//leave content as is
		}
	}
}
	?>
	<select name="desiredAction" class="myform" id="desiredAction" onChange="dChge(this);componentInterlock(this);" style="width:350px;">
	<option value="">(None)</option>
	<option value="{NON_PHP_EDITABLE}" <?php if(strstr($Content,'common(0)'))echo $selected='selected';?>>(CMSB Editable Region - No PHP)</option>
	<?php
	if($pJprocess_folder['output']){
		$start=true;
		foreach($pJprocess_folder['output'] as $n=>$v){
			$a=explode(':',$n);
			if($a[0]!=$buffer){
				if(!$start)echo '</optgroup>';
				$buffer=$a[0];
				echo '<optgroup label="'.($a[1]=='$JULIET_COMPONENT_ROOT' ? 'Juliet Components' : 'Global Components').'">';
				$start=false;
			}
			?><option value="<?php echo $n?>" <?php echo strtolower($n)==strtolower($desiredActionLocation.':'.$desiredAction)?$selected='selected':''?> <?php if(strstr($v,'{02}'))echo 'class="gray it"';?><?php if(substr($v,0,3)=='   ')echo ' style="padding-left:45px;"';?>><?php echo $v;?></option><?php
		}
		echo '</optgroup>';
	}
	?>
	<optgroup label="Advanced">
	<option value="{CUSTOM_PHP_EDITABLE}" <?php echo trim($Content) && !$selected?'selected':'';?>>Custom Coding (PHP allowed)</option>
	</optgroup>
	</select>
	<textarea name="FreeContent" cols="65" rows="10" class="myform tabby" id="FreeContent" style="display:<?php echo trim($Content) && !$selected?'block':'none';?>" onChange="dChge(this);"><?php echo h($Content);?></textarea>
	
	<?php
}else if($mode=='pageManager'){

	CSS_parser(implode('',file('site-local/'.$pJCSSIncludedFile)));
	$decs=$CSS_parser['declarations'];

	?>
	<h2>Page Manager</h2>
	<p>This controls the CSS ("styling") for the body and other key blocks/sections of your website.  It is not necessary to fill in values for every field.  Normally you will want to set background colors, font style and sizes (13px is normal, 12px or 14px are typicall smaller/larger values.<br />
	<p class="red">You are highly encouraged to know what you are doing first!  We recommend you do some Google searches on "basic CSS tutorials" before working with this form</p>
	<?php 
	$baseAttributes=array('color','background-color','font-size','font-family','text-decoration');
	foreach($baseParameters as $node=>$v){
		$x=array();
		$pound=($node=='body'?'':'#');
		if(strlen(array_search($pound.$node,$decs[1])))$x[array_search($pound.$node,$decs[1])]='';
		if(strlen(array_search($pound.$node.' a',$decs[1])))$x[array_search($pound.$node.' a',$decs[1])]='_a';
		if(strlen(array_search($pound.$node.' a:hover',$decs[1])))$x[array_search($pound.$node.' a:hover',$decs[1])]='_a_hover';
		foreach($x as $key=>$ext){
			//prn($key.':'.$ext);
			$a=preg_split('/[{}]/',trim($decs[0][$key]));
			if(count($a)==3){
				$a=trim($a[1]);
				$a=preg_split('/\s*;\s*/',$a);
				foreach($a as $w){
					$w=explode(':',$w);
					if(!$w[0] || !$w[1])continue;
					$GLOBALS[$node.$ext][strtolower(trim($w[0]))]=trim($w[1]);
					if(!in_array(strtolower(trim($w[0])), $baseAttributes))$GLOBALS[$node]['attributes'].=strtolower(trim($w[0])).':'.trim($w[1]).';'."\n";
				}
			}
		}
		if(true){
		?>
		<h3><?php echo $v['header'];?></h3>
		<?php if($v['description']){ ?>
		<p class="gray"><?php echo $v['description'];?></p>
		<?php } ?>
		<div class="section">
		<p>
		<span class="hideable" style="<?php echo in_array('color', $v['exclusion']) ? 'display:none;':'';?>">
		Text base color: 
		<input class="myform" name="<?php echo $node;?>[color]" type="text" id="<?php echo $node;?>[color]" value="<?php echo h($GLOBALS[$node]['color']);?>" onChange="dChge(this);" />
		<br />
		</span>
		Background color: 
		<input class="myform" name="<?php echo $node;?>[background-color]" type="text" id="<?php echo $node;?>[background-color]" value="<?php echo h($GLOBALS[$node]['background-color']);?>" onChange="dChge(this);" />
		<br />
		
		<span class="hideable" style="<?php echo in_array('font-family',$v['exclusion'])?'display:none;':'';?>">
		Font:
		<?php $sel=''; ?> 
		<select class="myform" name="<?php echo $node;?>[font-family]" id="<?php echo $node;?>[font-family]" onChange="dChge(this);">
		<option value="">(leave blank)</option>
		<option <?php echo strtolower($GLOBALS[$node]['font-family'])==strtolower('Arial, Helvetica, Sans-serif')?$sel='selected':'';?> value="Arial, Helvetica, Sans-serif">Arial, Helvetica, Sans-serif</option>
		<option <?php echo strtolower($GLOBALS[$node]['font-family'])==strtolower('Georgia, Times New Roman, Serif')?$sel='selected':'';?> value="Georgia, Times New Roman, Serif">Georgia, Times New Roman, Serif</option>
		<option <?php echo strtolower($GLOBALS[$node]['font-family'])==strtolower('Times New Roman, Serif')?$sel='selected':'';?> value="Times New Roman, Serif">Times New Roman, Serif</option>
		<option <?php echo strtolower($GLOBALS[$node]['font-family'])==strtolower('Verdana, Arial, Helvetica')?$sel='selected':'';?> value="Verdana, Arial, Helvetica">Verdana, Arial, Helvetica</option>
		<?php
		if(strlen($GLOBALS[$node]['font-family']) && !$sel){
			?><option value="<?php echo $GLOBALS[$node]['font-family'];?>" selected><?php echo h($GLOBALS[$node]['font-family']);?></option><?php
		}
		?>
		</select>
		<br />
		</span>
		
		<span class="hideable" style="<?php echo in_array('font-size',$v['exclusion'])?'display:none;':'';?>">
		Font size: 
		<input class="myform" name="<?php echo $node;?>[font-size]" type="text" id="<?php echo $node;?>[font-size]" onChange="dChge(this);" value="<?php echo h($GLOBALS[$node]['font-size']);?>" size="4" />
		<br />
		</span>

		Additional attributes:<br />
		<textarea class="myform" name="<?php echo $node;?>[attributes]" cols="45" rows="3" id="<?php echo $node;?>[attributes]" onChange="dChge(this);"><?php echo trim(h($GLOBALS[$node]['attributes']));?></textarea>
		</p>
		<?php if(!$v['hideLinkAttribs']){ ?>
		<h4>Links in normal state:</h4>
		<p>Color: 
		<input class="myform" name="<?php echo $node;?>_a[color]" type="text" id="<?php echo $node;?>_a[color]" value="<?php echo h($GLOBALS[$node.'_a']['color']);?>" onChange="dChge(this);" />
		<br />
		Underline: 
		<label>
		<input name="<?php echo $node;?>_a[text-decoration]" type="radio" value="" <?php echo !($GLOBALS[$node.'_a']['text-decoration']) ? 'checked':''?> onChange="dChge(this);" />
		default </label>
		&nbsp;&nbsp;
		<label>
		<input name="<?php echo $node;?>_a[text-decoration]" type="radio" value="none" <?php echo $GLOBALS[$node.'_a']['text-decoration']=='none' ? 'checked':''?> onChange="dChge(this);" />
		no </label>
		&nbsp;&nbsp;
		<label>
		<input name="<?php echo $node;?>_a[text-decoration]" type="radio" value="underline" <?php echo $GLOBALS[$node.'_a']['text-decoration']=='underline' ? 'checked':''?> onChange="dChge(this);" />
		yes</label>
		<br />
		Additional link attributes:<br />
		<textarea class="myform" name="<?php echo $node;?>_a[attributes]" cols="25" rows="2" id="<?php echo $node;?>_a[attributes]" onChange="dChge(this);"><?php echo h($GLOBALS[$node.'_a']['attributes']);?></textarea>
		</p>
		<h4>Links in hover state: </h4>
		<p>Color:
		<input class="myform" name="<?php echo $node;?>_a_hover[color]" type="text" id="<?php echo $node;?>_a_hover[color]" value="<?php echo h($GLOBALS[$node.'_a_hover']['color']);?>" onChange="dChge(this);" />
		<br />
		Underline:
		<label>
		<input name="<?php echo $node;?>_a_hover[text-decoration]" type="radio" value="" <?php echo !($GLOBALS[$node.'_a_hover']['text-decoration']) ? 'checked':''?> onChange="dChge(this);" />
		default </label>
		&nbsp;&nbsp;
		<label>
		<input name="<?php echo $node;?>_a_hover[text-decoration]" type="radio" value="none" <?php echo $GLOBALS[$node.'_a_hover']['text-decoration']=='none' ? 'checked':''?> onChange="dChge(this);" />
		no </label>
		&nbsp;&nbsp;
		<label>
		<input name="<?php echo $node;?>_a_hover[text-decoration]" type="radio" value="underline" <?php echo $GLOBALS[$node.'_a_hover']['text-decoration']=='underline' ? 'checked':''?> onChange="dChge(this);" />
		yes</label>
		<br />
		Additional link attributes:<br />
		<textarea class="myform" name="<?php echo $node;?>_a_hover[attributes]" cols="25" rows="2" id="<?php echo $node;?>_a_hover[attributes]" onChange="dChge(this);"><?php echo h($GLOBALS[$node.'_a_hover']['attributes']);?></textarea>
		<br />
		</p>
		<?php } ?>
	</div>
		<?php
		}
	}
}else if($mode=='jsManager'){
	?><h2>Javascript Section</h2>
	<p class="gray">This will be executed/available globally and is the last linked script in the &lt;head&gt; of the document</p>
	<?php
	if(file_exists($f=$_SERVER['DOCUMENT_ROOT'].'/site-local/'.$acct.'.'.$templateName.'.global.js')){
		$AdditionalGlobalJS=implode('',file($f));
		$AdditionalGlobalJS=trim($AdditionalGlobalJS);
		$AdditionalGlobalJS=preg_replace('/^<[?]php/i','',$AdditionalGlobalJS);
		$AdditionalGlobalJS=preg_replace('/[?]>$/','',$AdditionalGlobalJS);
		$AdditionalGlobalJS=trim($AdditionalGlobalJS);
	}
	?>
	<textarea name="AdditionalGlobalJS" cols="65" rows="15" class="myform tabby" id="AdditionalGlobalJS" onChange="dChge(this);"><?php echo h($AdditionalGlobalJS);?></textarea>
	<?php
}else if($mode=='codingManager'){
	?><h2>PHP Coding Section</h2>
	<p class="gray">This allows you to specify php variables pretty much before any components are called.  Obviously, be careful with this section.  If there are parse errors, the entire code block will fail and not be processed, so you be sure to avoid putting security stops in this file</p>
	<?php
	if(file_exists($f=$_SERVER['DOCUMENT_ROOT'].'/site-local/'.$acct.'.'.$templateName.'.global.php')){
		$AdditionalGlobalPHP=implode('',file($f));
		$AdditionalGlobalPHP=trim($AdditionalGlobalPHP);
		$AdditionalGlobalPHP=preg_replace('/^<[?]php/i','',$AdditionalGlobalPHP);
		$AdditionalGlobalPHP=preg_replace('/[?]>$/','',$AdditionalGlobalPHP);
		$AdditionalGlobalPHP=trim($AdditionalGlobalPHP);
	}
	?>
	<textarea name="AdditionalGlobalPHP" cols="65" rows="15" class="myform tabby" id="AdditionalGlobalPHP" onChange="dChge(this);"><?php echo h($AdditionalGlobalPHP);?></textarea>
	
	<?php
}else if($mode=='stylesheetManager'){
	if(file_exists($f=$_SERVER['DOCUMENT_ROOT'].'/site-local/'.$acct.'.'.$templateName.'.global.css')){
		$AdditionalGlobalCSS=implode('',file($f));
	}
	?>
	<h2>Additional Stylesheet  Manager</h2>
	<p>
	Enter any additional stylesheet settings as CSS code.  All settings here are global (apply to all pages).  Caution: if there is a conflict between this style and the primary stylesheet for the site, these styles will override the primary stylesheet rules.<br />
	<textarea name="AdditionalGlobalCSS" cols="65" rows="15" class="myform tabby" id="AdditionalGlobalCSS" onChange="dChge(this);"><?php echo h($AdditionalGlobalCSS);?></textarea>
	</p>
	<?php
}
?>
	<br />
	<input name="submode" type="hidden" id="submode" value="<?php echo $mode;?>" />
	<input name="_thispage" type="hidden" id="_thispage" value="<?php echo $_thispage?>" />
	<input name="_thisfolder" type="hidden" id="_thisfolder" value="<?php echo $_thisfolder;?>" />
	<input name="_thisnode" type="hidden" id="_thisnode" value="<?php echo $_thisnode;?>" />
	<br />
	<input type="submit" name="Submit" value="Submit" />
	<input type="button" name="Submit2" value="Close" onClick="window.close();" />
	
	<label><input name="refreshOpener" type="checkbox" id="refreshOpener" value="1" checked="checked" />
	Refresh the page</label>
	</p>
</form>
</div>
<div id="showTester" title="Javascript Tester" onClick="g('tester').style.display='block';">&nbsp;</div>
<div id="tester" >
	<a href="#" onClick="g('ctrlSection').style.display='block';return false;">Show Control Section</a><br />
	<textarea name="test" cols="65" rows="4" id="test">clear_form();</textarea><br />
	<input type="button" name="button" value="Test" onClick="jsEval(g('test').value);"><br />
	<textarea id="result" name="result" cols="65" rows="3" ></textarea>
</div>
<?php if(!$hideCtrlSection){ ?>
<div id="ctrlSection" style="display:none">
	<iframe name="w1" src="/Library/js/blank.htm"></iframe>
	<iframe name="w2" src="/Library/js/blank.htm"></iframe>
</div>
<?php } ?>
</body>
</html>
