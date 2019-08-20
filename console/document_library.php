<?php 
/*
2013-04-02: brought over from Fo Stex
todo:
I need to identify the user adn each have their own library - where is this? it's not in Fo Stex - and it may be in home base
	ANSWER: homebase and before that GLF
at this point I want to move FEX to the next level as follows:
	need ot identify TYPES of folders, and indicate if they are restricted
	recursing FEX should build relatebase_tree - and also delete things that are not in the tree
search needs to be in upper right
search needs to search dimensions of files and content of text and strip_php(strip_tags(html)) documents
we need a back button
this needs to have a box area
why not make this collapsible instead?
how would FEX tie in with this? maybe through symbolic links? forward or backward?


*/
//identify this script/GUI
$localSys['scriptGroup']='';
$localSys['scriptID']='generic';
$localSys['scriptVersion']='1.0';
$localSys['pageType']='Properties Window';


//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');
require_once($FUNCTION_ROOT.'/group_sE_v100.php');

function tree_id_to_path2($n, $options=array()){
	extract($options);
	global $tree_id_to_path;
	$row=q("SELECT Tree_ID, Name, Type FROM relatebase_tree WHERE ID='$n'", O_ROW);
	if($row['Type']=='file')$tree_id_to_path=$row;
	return ($row ? tree_id_to_path2($row['Tree_ID'],$options).'/'.($link?'<a href="'.$link.'?Folder_ID='.$n.'">':'').$row['Name'].($link?'</a>':'') : '');
}

$PageTitle='Document Manager';

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/properties_04_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo dynamic_title($PageTitle.' - '.$AcctCompanyName);?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" href="/console/rbrfm_admin.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<link href="/Library/ckeditor_3.4/_samples/sample.css" rel="stylesheet" type="text/css" />
<style type="text/css">
textarea.tabby:focus{
	border-style:dotted;
	}
.comment{
	cursor:pointer;
	border-bottom:1px dashed #666;
	}
#workSpace{
	clear:both;
	border:1px solid #ccc;
	padding:5px 10px;
	margin:7px 0px;
	}
.hover td{
	background-color:cornsilk;
	}
.normal td{
	background-color:none;
	}
</style>

<script language="JavaScript" type="text/javascript" src="/Library/js/jquery.js"></script>
<script language="javascript" type="text/javascript" src="/Library/js/jquery.tabby.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/jquery.bbq.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/global_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/common_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/forms_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/loader_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/dataobjects_04_i1.js"></script>
<script type="text/javascript" src="/Library/ckeditor_3.4/ckeditor.js"></script>
<script language="JavaScript" type="text/javascript">
/* periwinkle coding 2.1 */
var thispage='<?php echo $thispage?>';
var thisfolder='<?php echo $thisfolder?>';
var ctime='<?php echo $ctime?>';
var PHPSESSID='<?php echo $PHPSESSID?>';
//for nav feature
var count='<?php echo $nullCount?>';
var ab='<?php echo $nullAbs?>';
var isEscapable=1;
AddOnkeypressCommand('PropKeyPress(e)'); //if not declared already
</script>
<?php ob_start();//buffer form?>
<!-- InstanceEndEditable -->
</head>

<body id="properties">
<form id="form1" name="form1" target="w2" method="post" action="/console/resources/bais_01_exe.php" onSubmit="return beginSubmit();" enctype="multipart/form-data">
<div id="header"><!-- InstanceBeginEditable name="top_nav" -->
<?php
$out=preg_replace('/<form[^>]>/','',ob_get_contents());
ob_end_clean();
echo $out;
?>
<h1>Document Manager</h1>

<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->



<?php
if(!is_dir($_SERVER['DOCUMENT_ROOT'].'/images/documents') && !mkdir($_SERVER['DOCUMENT_ROOT'].'/images/documents'))exit('fail creating documents folder');
if(!is_dir($_SERVER['DOCUMENT_ROOT'].'/images/documents/library') && !mkdir($_SERVER['DOCUMENT_ROOT'].'/images/documents/library'))exit('fail creating documents/library folder');
if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/images/documents/library/.htaccess')){
	$a=explode('/',trim($_SERVER['DOCUMENT_ROOT'],'/'));
	$null=array_pop($a);
	$here=end($a);
	$str='AuthType Basic
AuthName "Site in development"
AuthUserFile "/home/'.$here.'/.htpasswds/public_html/images/documents/library/passwd"
require valid-user';
	if(!($fp=fopen($_SERVER['DOCUMENT_ROOT'].'/images/documents/library/.htaccess','w')))exit('unable to create file .htaccess');
	fwrite($fp,$str,strlen($str));
	fclose($fp);
	?><h2 class="red">.htaccess file created for /images/documents/library</h2><?php
}


$dir=opendir($_SERVER['DOCUMENT_ROOT'].'/images/documents/library');
?>
<div class="tar"><input type="button" onclick="javascript:window.close();" value="Close Window" /></div>
<?php
if($q){
	?>
	<h2>Search Results: <?php echo stripslashes($q);?></h2>
	<?php
	$q=stripslashes($q);
	//recursively build array
	function build_filenames($Tree_ID=4822, $q){
		global $build_filenames;
		if($a=q("SELECT ID, Tree_ID, Name, Title, Description, Type, IF(CreateDate, CreateDate, EditDate) AS CreateDate FROM relatebase_tree WHERE Tree_ID=$Tree_ID", O_ARRAY_ASSOC)){
			foreach($a as $n=>$v){
				if($v['Type']=='file'){
					if(
						preg_match('/'.$q.'/i',$v['Name']) ||
						preg_match('/'.$q.'/i',$v['Description']) ||
						preg_match('/'.$q.'/i',$v['Title'])){
						$build_filenames[$n]=array(
							'Name'=>$v['Name'],
							'Description'=>$v['Description'], 
							'Title'=>$v['Title'], 
							'Tree_ID'=>$v['Tree_ID'],
							'CreateDate'=>$v['CreateDate']
						);
					}
				}else{
					//folder
					build_filenames($n,$q);
				}
			}
		}
	}
	build_filenames(4822,$q);
	if(count($build_filenames)){
		?><table width="100%"><?php 
		foreach($build_filenames as $n=>$v){
			$extention=end(explode('.',$v['Name']));
			?><tr id="<?php echo 'link'.$n;?>" class="normal" onmouseover="this.className='hover';" onmouseout="this.className='normal';"><?php
			$name=explode('_',$v['Name']);
			?>
			<td>
			<img src="/images/i/_<?php echo $extention?>.gif" align="absbottom" />&nbsp;
			<a href="resources/bais_01_exe.php?file_ID=<?php echo $n;?>&mode=downloadRelativeFile&suppressPrintEnv=1" target="w2"><?php echo preg_replace('/('.$q.')/i','<strong style="font-size:larger;">$1</strong>',$name[1]?$name[1]:$name[0]);?></a>
			</td>
			<td align="right">
			<a href="resources/bais_01_exe.php?file_ID=<?php echo $n;?>&mode=downloadRelativeFile&suppressPrintEnv=1" target="w2">Download</a>
			</td>
			</tr>
			<tr>
			<td colspan="100%" style="padding-bottom:12px;padding-left:25px;">
			<div class="fl gray" style="width:150px;">
			Added: <?php echo date('n/j/Y @g:iA',strtotime($v['CreateDate']));?>
			</div>
			<img src="/images/i/1148-folder1.gif" width="16" height="16" /> Location: <strong><a href="document_library.php?Folder_ID=<?php echo $v['Tree_ID'];?>" title="Go to this folder directly"><?php
			echo tree_id_to_path($v['Tree_ID']);
			?></a></strong>
			</td>
			</tr><?php
		}
		?></table><?php
	}
	
	?><p><?php echo !count($build_filenames) ? 'No matches found..':''?> Search again:
	<?php echo '<form id="search" method="get">';?>
		<input type="text" name="q" id="q" value="" />
		<input type="submit" value="Search" />
	<?php echo '</form>';?>
	[<a href="document_library.php">Return to main folder</a>]
	</p><?php
}else if(!$Folder_ID){
	$folders=q("SELECT * FROM relatebase_tree WHERE Tree_ID='4822' ORDER BY Name",O_ARRAY);
	?>
	<br />
	<?php echo '<form id="form1" action="resources/bais_01_exe.php" method="post" target="w2">';?>
		<input type="text" name="folderName" value="(Enter Folder Name)" class="gray" onfocus="if(this.value=='(Enter Folder Name)'){this.value=''; this.className='';}" />
		<input type="hidden" name="mode" value="createRelativeFolder" />
		<input type="submit" value="Create a New Folder" />
		<input type="hidden" name="Tree_ID" value="4822" /> 
	<?php echo '</form>';?>
	<br />
	<?php echo '<form id="search" method="get">';?>
		<input type="text" name="q" id="q" value="" />
		<input type="submit" value="Search" />
	<?php echo '</form>';?>
	<br />
	<br />

	<table width="100%">
	<?php
	if($folders)
	foreach($folders as $n=>$v){
		if(strtolower($v['Type'])=='file'){
			$i++;
			$trid='folder'.$i;
		}else{
			$trid='link'.$n;
		}
		?><tr id="<?php echo $trid?>" class="normal" onmouseover="this.className='hover';" onmouseout="this.className='normal';"><?php
		if(strtolower($v['Type'])=='file'){
			$name=explode('_',$v['Name']);
			?>
			<td>
			<a href="resources/bais_01_exe.php?fileName=<?php echo $v['Name'];?>&mode=downloadRelativeFile&suppressPrintEnv=1" target="w2"><?php echo ($name[1]?$name[1]:$name[0]);?></a>
			</td>
			<td align="right">
			<a href="resources/bais_01_exe.php?file_ID=<?php echo $v['ID']?>&mode=deleteRelativeFile" target="w2" onclick="if(!confirm('Are you sure you want to delete this file?'))return false;">Delete</a>
			</td>
			<?php
		} else {
			?>
			<td>
			<img src="/images/i/1148-folder1.gif" width="16" height="16" />
			<a href="document_library.php?Folder_ID=<?php echo $v['ID']?>"><?php echo $v['Name'];?></a> 
			</td>
			<td align="right">
			<a href="resources/bais_01_exe.php?mode=deleteRelativeFile&Folder_ID=<?php echo $v['ID'];?>" onclick="if(!confirm('Are you sure you want to delete this folder? This will delete every folder and file inside of it.'))return false;" target="w2">Delete</a>
			</td>
			<?php
		}
		?></tr><?php
	}
	?>
	</table>
	<br />
	<br />

	<?php echo '<form name="form2" id="form2" action="resources/bais_01_exe.php" method="post" enctype="multipart/form-data" target="w2" style="margin-top:50px;">';?>
		Add a file:<br />
		<input type="file" name="localFile"/>
		<input type="hidden" name="mode" value="uploadRelativeFile" />
		<input type="hidden" name="Tree_ID" value="4822" /> 
		<button type="submit">Upload</button>
	<?php echo '</form>';?>
	<?php
} else {
	?>
	<h3><img src="/images/i/1148-folder1.gif" width="16" height="16" /> <?php echo tree_id_to_path2($Folder_ID,$options=array('link'=>'document_library.php'));?>/</h3>
	
	<?php echo '<form id="form1" action="resources/bais_01_exe.php" method="post" target="w2">';?>
		<input type="text" name="folderName" value="(Enter Folder Name)" class="gray" onfocus="if(this.value=='(Enter Folder Name)'){this.value=''; this.className='';}" />
		<input type="hidden" name="mode" value="createRelativeFolder" />
		<input type="submit" value="Create a New Folder" />
		<input type="hidden" name="Tree_ID" value="<?php echo $Folder_ID?>" /> 
	<?php echo '</form>';?>
	<?php echo '<form id="search" method="get">';?><br />
		<input type="text" name="q" id="q" value="" />
		<input type="submit" value="Search" />
	<?php echo '</form>';?>
	<br />
	<br />

	<?php
	$files=q("SELECT * FROM relatebase_tree WHERE Tree_ID='$Folder_ID' ORDER BY Type DESC",O_ARRAY);
	if(is_array($files)){
		?><table width="100%"><?php
		foreach($files as $n=>$v){
			if(strtolower($v['Type'])=='file'){
				$trid='link'.$n;
			}else{
				$i++;
				$trid='folder'.$i;
			}

			?><tr id="<?php echo $trid?>" class="normal" onmouseover="this.className='hover';" onmouseout="this.className='normal';"><?php
			$name=explode('_',$v['Name']);
			if(strtolower($v['Type'])=='file'){
				$extention=end(explode('.',$v['Name']));
				?><td>
				<img src="/images/i/_<?php echo $extention?>.gif" />
				<?php echo $name[1]?$name[1]:$name[0];?>
				<?php
				if(false && preg_match('/(pdf)$/',$v['Name'])){
					?><a href="<?php echo 'what did I have here?/'.$v['Name']?>" target="_blank">View In Browser</a><?php
				}else{
				
				}
				?>
				</td>
				<td align="right">
				<a href="resources/bais_01_exe.php?file_ID=<?php echo $v['ID'];?>&mode=downloadRelativeFile&suppressPrintEnv=1" target="w2">Download</a>
				&nbsp;
				<a href="resources/bais_01_exe.php?file_ID=<?php echo $v['ID']?>&mode=deleteRelativeFile" onclick="if(!confirm('Are you sure you want to delete this file?'))return false;" target="w2">Delete</a> 
				</td><?php
			}else{
				?><td>
				<img src="/images/i/1148-folder1.gif" width="16" height="16" />
				<a href="document_library.php?Folder_ID=<?php echo $v['ID']?>"><?php echo $v['Name'];?></a> 
				</td>
				<td align="right">
				<a href="resources/bais_01_exe.php?mode=deleteRelativeFile&Folder_ID=<?php echo $v['ID'];?>" onclick="if(!confirm('Are you sure you want to delete this folder? This will delete every folder and file inside of it.'))return false;" target="w2">Delete</a>
				</td><?php
			}
			?></tr><?php
		}
		?></table>
	<?php
	} else {
		echo 'This Folder Is Currently Empty';
	}
	?>
    <?php echo '<form name="form2" id="form2" action="resources/bais_01_exe.php" method="post" enctype="multipart/form-data" target="w2">';?> <br />
    <input type="file" name="localFile"/>
		<input type="hidden" name="mode" value="uploadRelativeFile" />
		<input type="hidden" name="Tree_ID" value="<?php echo $Folder_ID?>" /> 
		<button type="submit">Upload</button>
	<?php echo '</form>';?>
	<?php
}
ob_start(); //buffer end form
?>

<!-- InstanceEndEditable --></div>
<div id="footer"><!-- InstanceBeginEditable name="footer" -->
&nbsp;
<!-- InstanceEndEditable --></div>
</form>
<?php if(!$hideCtrlSection){ ?>
<div id="showTester" title="Javascript Tester" onClick="g('tester').style.display='block';">&nbsp;</div>
<div id="tester" >
	<a href="#" onClick="g('ctrlSection').style.display='block';return false;">Show Control Section</a><br />
	<textarea name="test" cols="65" rows="4" id="test">clear_form();</textarea><br />
	<input type="button" name="button" value="Test" onClick="jsEval(g('test').value);"><br />
	<textarea id="result" name="result" cols="65" rows="3" ></textarea>
</div>
<div id="ctrlSection" style="display:<?php echo $testModeC ? 'block':'none'?>">
	<iframe name="w1" src="/Library/js/blank.htm"></iframe>
	<iframe name="w2" src="/Library/js/blank.htm"></iframe>
	<iframe name="w3" src="/Library/js/blank.htm"></iframe>
	<iframe name="w4" src="/Library/js/blank.htm"></iframe>
</div>
<?php } ?>
</body>
<!-- InstanceEnd --></html><?php

$out=str_replace('</form>','',ob_get_contents());
ob_end_clean();
echo $out;

page_end();
//skip the page output
bypass:
?>