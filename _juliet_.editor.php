<?php
/*
 * 2017-07-15 SF: So, this whole system of a component editor has a lot of very MVC-like features.
 * The _juliet_.editor.php page is basically a shell but bases on the logic that whatever component is called will be inside a form
 * Note that we call the $pJulietTemplate page so there's a little abstraction there.
 * There are component settings which are stored in the db
 */
require_once($_SERVER['DOCUMENT_ROOT'].'/config.php');
require_once($FUNCTION_ROOT.'/function_CSS_parser_v100.php');
$hasAdmin=false;
if($a=$_SESSION['cnx'][$acct]['accesses'])foreach($a as $v)if(preg_match('/^(admin|db admin)$/i',$v)){
	$hasAdmin=true;
	break;
}
if($adminMode<ADMIN_MODE_EDITOR /*1*/ && !$hasAdmin)exit('You do not have access to this page, or your session has expired');

//get a list of all possibilities - so far only used for mode=blockManager
$pJModalInclusion=true;
require($pJulietTemplate);


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Component File Editor</title>

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
	padding: 5px 10px;
    border-width: 2px;
    border-style: solid;
    color: #333;
    background: #fff;
    font-size: 14px;
	margin-top:2px;
}
input.myform:focus, textarea.myform:focus, select.myform:focus { 
	border-color: #de8800 
	}
input.myform, textarea.myform, select.myform {
    border-color: #99BD0C;
	}
input[type="submit"], input[type="button"], input#submit, input.submit {
    background-color: #6D8709;
    color: #FFFFFF;
	}
select{
	margin-top:2px;
	}
form{
	}



.spacer td{
	border-bottom:1px solid #ccc;
	padding-top:2px;
	padding-bottom:2px;
	}
.spacer .inactive td{
	background-color:#ddd;
	color:#222;
	}

</style>
<script src="/Library/js/jquery.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/jquery.tabby.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/global_04_i1.js" language="javascript" type="text/javascript"></script>
<script src="/Library/js/common_04_i1.js" language="javascript" type="text/javascript"></script>
<script src="/Library/js/forms_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/loader_04_i1.js" language="JavaScript" type="text/javascript"></script>

<link href="/Library/ckeditor_3.4/_samples/sample.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/Library/ckeditor_3.4/ckeditor.js"></script>

<script language="javascript" type="text/javascript">
/* periwinkle coding */
CMSBEditorURL='cms3.01.php';
var isEscapable=1;
AddOnkeypressCommand('PropKeyPress(e)');
</script>
</head>

<body>
<style type="text/css">
#exportAnchor{
	position:relative;
	}
#exportOptions{
	position:absolute;
	top:35px;
	right:0px;
	border:1px solid dimgray;
	padding:5px;
	width:280px;
	background-color:cornsilk;
	opacity:.80;
	}
.cxl{
	background-color:darkred;
	font-size:smaller;
	color:white;
	text-align:center;
	float:right;
	width:20px;
	cursor:pointer;
	}
</style>
<script language="javascript" type="text/javascript">
function exporter(n){
	if(n=='export'){
		g('submode').value='export';
		g('suppressPrintEnv').value='1';
		detectChange=0;
		g('form1').submit();
		g('suppressPrintEnv').value='';
		g('submode').value='';
		return false;
	}else if(n=='import'){
		g('exportOptions').style.visibility='visible';
		g('ImportString').focus();
	}else if(n=='importCxl'){
		g('exportOptions').style.visibility='hidden';
	}else if(n=='importGo'){
		g('submode').value='import';
		g('form1').submit();
		g('submode').value='';
		return false;
	}
}
</script>
<div id="mainWrap">
    <form id="form1" name="form1" method="post" target="w2" action="/index_01_exe.php">
        <div class="fr">
            <input type="button" name="Submit" value="Export" onclick="exporter('export');" />
            &nbsp;&nbsp;
            <div id="exportAnchor" style="float:right;"><input type="submit" name="Submit" value="Import.." onclick="return exporter('import');" />
                <div id="exportOptions" style="visibility:hidden;">
                    <div class="cxl" onclick="exporter('importCxl');">x</div>
                    <textarea name="ImportString" cols="35" rows="3" id="ImportString"><?php echo h($ImportString);?></textarea><br />
                    <label><input type="checkbox" name="ImportMerge" id="ImportMerge" value="1" checked="checked" onchange="dChge(this);if(!this.checked)alert('Warning! Unchecking this box will OVERWRITE all component settings versus merging over them.  Do not uncheck this box unless you are sure of what you are doing.  Additionally it is recommended that you export settings first in case you muck it up like always');" />Merge these settings over old settings</label> <span class="gray">(vs. overwriting old settings)</span><br />
                    <input type="submit" name="Submit" value="GO" onclick="return exporter('importGo');" />
                </div>
            </div>
        </div>
        <h1>Component Editor</h1>
        <?php
        if($mode=='viewSource'){
            ?>
            <?php
            $c=file($$location.'/'.$file);
            highlight_string(implode('',$c));
            ?><br />
            <?php

        }else{

            ?><br /><?php
            require($$location.'/'.$file);
            ?>
            <br />
            <div class="fr">
                <?php
                $a=stat($$location.'/'.$file);
                ?><span class="gray">File last modified <?php echo date('n/j/Y \a\t g:iA',$a['mtime']);?></span><br />
                <a href="_juliet_.editor.php?mode=viewSource&location=<?php echo $location;?>&file=<?php echo $file;?>" onclick="return ow(this.href,'l1_viewsource','900,500');" tabindex="-1">View source code</a>
            </div>
            <?php
        }
        ?>


        <input name="pJCurrentContentRegion" type="hidden" id="pJCurrentContentRegion" value="<?php echo $pJCurrentContentRegion;?>" />
        <input name="thissection" type="hidden" id="thissection" value="<?php echo $thissection;?>" />
        <input name="_thisnode_" type="hidden" id="_thisnode_" value="<?php echo $_thisnode_;?>" />
        <input name="handle" type="hidden" id="handle" value="<?php echo $handle;?>" />
        <input name="location" type="hidden" id="location" value="<?php echo $location;?>" />
        <input name="file" type="hidden" id="file" value="<?php echo $file?>" />
        <input name="formNode" type="hidden" id="formNode" value="<?php echo $formNode?$formNode:'default';?>" />
        <input name="mode" type="hidden" id="mode" value="componentEditor" />

        <!-- 2017-07-15 SF - these show to be set dynamically by JS -->
        <input name="submode" type="hidden" id="submode" />
        <input name="suppressPrintEnv" type="hidden" id="suppressPrintEnv" />

        <input type="submit" name="Submit" id="Submit" value="Submit" />
        <input type="button" name="Submit" value="Cancel" onclick="window.close();" />
        <a href="/admin/file_explorer/?uid=ced" onclick="return ow(this.href,'l1_fex','700,700');">FEX</a>
        </p>
        <label><input name="refreshOpener" type="checkbox" id="refreshOpener" value="1" checked="checked" /> Refresh the page </label>

    </form>
</div>

<?php
if($refreshOpener){
	?><script language="javascript" type="text/javascript">
	//try{
	var l=window.parent.opener.location+'';
	l=l.replace(/&*r=[.0-9]+/,'');
	l=l+(l.indexOf('?')!= -1 ? '' : '?') + '&r=' + Math.random();
	window.parent.opener.location=l;
	//}catch(e){ }
	</script><?php
}
?>
<span>
<div id="showTester" title="Javascript Tester" onclick="g('tester').style.display='block';">&nbsp;</div>
<div id="tester" >
	<a href="#" onclick="g('ctrlSection').style.display='block';return false;">Show Control Section</a><br />
	<textarea name="test" cols="65" rows="4" id="test">clear_form();</textarea><br />
	<input type="button" name="button" value="Test" onclick="jsEval(g('test').value);"><br />
	<textarea id="result" name="result" cols="65" rows="3" ></textarea>
</div>
    <?php if(!$hideCtrlSection){ ?>
        <div id="ctrlSection" style="display:none">
	<iframe name="w1" src="/Library/js/blank.htm"></iframe>
	<iframe name="w2" src="/Library/js/blank.htm"></iframe>
</div>
    <?php } ?>
</span>
</body>
</html>
