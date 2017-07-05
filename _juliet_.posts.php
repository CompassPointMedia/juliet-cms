<?php
require($_SERVER['DOCUMENT_ROOT'].'/config.php');
/*
todo
	deal with current state - persist
	parse css


*/
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Posts</title>

<link href="/Library/css/cssreset01.css" type="text/css" rel="stylesheet" />
<link href="/site-local/_juliet_.settings.css" type="text/css" rel="stylesheet" />

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
</script>
</head>

<body>
<div id="mainWrap">
<?php
//get a list of all possibilities

$posts=q("SELECT * FROM system_poststorage ORDER BY ID DESC", O_ARRAY);

?>
<form id="form1" name="form1" method="post" target="w2" action="/index_01_exe.php">

<table cellpadding="5">
<thead>
<tr>
	<th>Submitted</th>
	<th>Mode</th>
	<th>Content</th>
</tr>
</thead>
<tbody>
<?php
if($posts)
foreach($posts as $post){
	extract($post);
	?><tr>
	<td><?php echo date('n/j/Y \a\t g:iA',strtotime($EditDate));?></td>
	<td><?php echo $Mode?></td>
	<td><textarea cols="70" rows="20"><?php 
	$b=unserialize(base64_decode($Content));
	print_r($b);
	?></textarea></td>
	</tr><?php
}
?>
</tbody>
</table>
<?php


?></form>
</div>
<div id="showTester" title="Javascript Tester" onclick="g('tester').style.display='block';">&nbsp;</div>
<div id="tester" >
	<a href="#" onclick="g('ctrlSection').style.display='block';return false;">Show Control Section</a><br />
	<textarea name="test" cols="65" rows="4" id="test">clear_form();</textarea><br />
	<input type="button" name="button" value="Test" onclick="jsEval(g('test').value);"><br />
	<textarea id="result" name="result" cols="65" rows="3" ></textarea>
</div>
<?php if(!$hideCtrlSection){ ?>
<div id="ctrlSection" style="display:none">
	<iframe name="w1" src="/blank.htm"></iframe>
	<iframe name="w2" src="/blank.htm"></iframe>
</div>
<?php } ?>
</body>
</html>
