<?php 
/****
2006-07-16: OK so I have editable regions saving to the database as well as the subject line, big jump ahead.  NOW, eventually when we change templates we need to flush out all of those old editable areas vars in the db on save.


this page is going to be quite a work.  First thing I need to do is pull all of the profile data I might need relating to composition.
HOT OFF THE PRESS: I realize now that most people will want to store mailers for the purpose of the RECIPIENTS, not the template,  AND then store all of the different form letters they have.  This means this must be implemented ASAP to make the product workable.

todo list in approx order:
we only need the plain text/html option for BLANK EMAILS - make a hidden field for templates - and consider other helpful options here but this is the main point.
	if HTML:
	convert new lines in my Editable Areas (the enter key) into <br /> tags

DONE	2. read from a template online
DONE	plain text disabled when using a template

3. enter and save text and have these options in place
store all of the subject lines I've used as a dropdown list
get regions as vars in array first (before HTML), then make closeable and openable
deal with the editability issue - not user friendly
analysis on relative urls present - "Check links"

when I make changes and submit, this needs to be visible in the MPM by an icon of some type

2006-07-13 at 12:10PM - OK so it's going into session but does all this work?????






****/
//identify this script/GUI
$localSys['scriptID']='generic';
$localSys['scriptVersion']='1.0';
$localSys['componentID']='main';



//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');require('mail_00_includes.php');

$RecipientSource='group';

//page-specific coding
$sql="SELECT
a.*,
b.Val AS ComplexQuery,
c.Val AS ManualList,
d.Val AS EmailColumns,
e.Val AS BatchRecordComment,
f.Val AS Subject,
g.Val AS AttachmentList,
h.Val AS RequiredFields
FROM relatebase_mail_profiles a LEFT JOIN relatebase_mail_profiles_vars b
ON a.ID = b.Profiles_ID AND b.Name='ComplexQuery'
LEFT JOIN relatebase_mail_profiles_vars c
ON a.ID = c.Profiles_ID AND c.Name='ManualList'
LEFT JOIN relatebase_mail_profiles_vars d
ON a.ID = d.Profiles_ID AND d.Name='EmailColumns'
LEFT JOIN relatebase_mail_profiles_vars e
ON a.ID = e.Profiles_ID AND e.Name='BatchRecordComment'
LEFT JOIN relatebase_mail_profiles_vars f
ON a.ID = f.Profiles_ID AND f.Name='Subject'
LEFT JOIN relatebase_mail_profiles_vars g
ON a.ID = g.Profiles_ID AND g.Name='AttachmentList'
LEFT JOIN relatebase_mail_profiles_vars h
ON a.ID = h.Profiles_ID AND h.Name='RequiredFields'
WHERE a.ID = '$Profiles_ID'";
if(strlen($Profiles_ID)){
	if(!($a=q($sql, O_ROW))) exit("Mail profile $Profiles_ID has been deleted or renamed (or you have logged out and logged in under another account.  Close this window, go to Admin Page > Mail Management, and reopen this window");

}else{
	if(!$SessionToken){
		exit('Malformed querystring request; need either a Profiles_ID value or SessionToken');
	}
	$Profiles_ID=quasi_resource_generic($acct, 'relatebase_mail_profiles', $SessionToken);
	if(q("SELECT ResourceType FROM $acct.relatebase_mail_profiles WHERE ID='$Profiles_ID'", O_VALUE)=='1'){
		//this is from a saved document which has not been refreshed - virtual Profiles_ID

	}else{
		//this is a new document

	}
}
if($a){
	foreach($a as $n=>$v)$a[$n]=htmlentities($v);
	@extract($a);
}
//these ALWAYS override, these are the "RAM memory settings" the user wants:
#prn($_GET);
$Composition=($_GET['Composition']=='true' ? 1 : 0);
$TemplateMethod=($_GET['TemplateMethod']=='true' ? 'url' : 'file');

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="../../Templates/reports_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Compose Email :: RelateBase Mail Profile Tool</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" href="/Library/css/cssreset01.css" type="text/css" />
<link rel="stylesheet" href="../rbrfm_admin.css" type="text/css" />
<link id="cssDHTML" rel="stylesheet" href="../../Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<style type="text/css">
/* local CSS styles */
</style>

<script language="JavaScript" type="text/javascript" src="../../Library/js/global_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="../../Library/js/common_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="../../Library/js/forms_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="../../Library/js/loader_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="../../Library/js/contextmenus_04_i1.js"></script>
<script src="mail.js" type="text/javascript"></script>
<script id="jslocal" language="JavaScript" type="text/javascript">
/* periwinkle coding */
var thispage='<?php echo $thispage?>';
var thisfolder='<?php echo $thisfolder?>';
var browser='<?php echo $browser?>';
var ctime='<?php echo $ctime?>';
var PHPSESSID='<?php echo $PHPSESSID?>';
//for nav feature
var count='<?php echo $nullCount?>';
var ab='<?php echo $nullAbs?>';

var page='compose.php';
var Profiles_ID='<?php echo $Profiles_ID?>';
var isEscapable=2;
function dChgeThisPage(x){
	detectChange=x;
	if(x==1)g('CompositionFinished').disabled=false;
}
function viewComposition(){
	var x=document.forms['form1'].target+'';
	var y=document.forms['form1'].mode.value;
	var z=document.forms['form1'].action+'';
	document.forms['form1'].target='l3_compositionwindow';
	document.forms['form1'].mode.value='showtemplate';
	document.forms['form1'].action='mail_profile_07_preview.php';
	ow('','l3_compositionwindow','850,600');
	document.forms['form1'].submit();
	//reset form
	document.forms['form1'].target=x;
	document.forms['form1'].mode.value=y;
	document.forms['form1'].action=z;
}
function expand(id, expand){
	var erows=g('a_'+id).rows;
	if(expand){
		if(erows < 30)g('a_'+id).rows=erows+7;
	}else{
		if(erows > 8){
			g('a_'+id).rows-=7;
		}else{
			g('a_'+id).rows=1;
		}
	}
}
</script>

<!-- InstanceEndEditable -->
</head>

<body id="report">
<?php if(!$suppressForm){ ?>
<form action="../../console/resources/bais_01_exe.php" method="post" enctype="multipart/form-data" name="form1" target="w2" id="form1" onsubmit="return beginSubmit();">
<?php }?>
<div id="header"><!-- InstanceBeginEditable name="top_nav" -->
  <h2>Compose Email</h2>
<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->


<p>Mailer Profile: <?php echo $Name ? htmlentities($Name) : '(untitled-1)'?></p>
<p>
Type: <?php echo $Composition==0 ? 'No template' : ($TemplateMethod=='file' ? 'Template, from RelateBase File ../sidwod.dwt' : 'Template, from URL: '.$TemplateLocationURL)?>
<?php
if($Composition==1 && $TemplateMethod=='url'){
	if(!strstr($QUERY_STRING,'refreshOrigUrl')){
		$qs= $QUERY_STRING . '&refreshOrigUrl=1';
	}else{
		preg_match('/refreshOrigUrl=([01])/',$QUERY_STRING,$a);
		$refState=$a[1];
		$qs= preg_replace('/refreshOrigUrl=[01]/','refreshOrigUrl='.($a[1]==1 ? 0 : 1),$QUERY_STRING);
	}
	?>&nbsp;&nbsp;&nbsp;<input type="button" name="Submit2" value="<?php echo $refreshOrigUrl==1?'Cancel ':''?>Refresh" onclick="alert('<?php echo $refreshOrigUrl==0 ? 'This will load the original Editable Areas (Regions) from the template.  To restore any changes you made, click this button again' : 'Returning to any changes you made'?>');window.location='compose.php?<?php echo $qs?>';" /><?php
}
?>
</p>
 
<fieldset style="width:50%;">
<legend> Send Email as</legend>
<?php
if($Composition==1){
	$HTMLOrText=1;
	$HTMLOrTextDisabled=true;
}else if(!isset($HTMLOrText)){
	$HTMLOrText=1;
}
?>
<input type="radio" name="HTMLOrText" value="0" onClick="textType(0);" <?php echo !$HTMLOrText ?'checked' : ''?> <?php echo $HTMLOrTextDisabled ? 'disabled' : ''?> onchange="dChgeThisPage(1);"> Plain Text &nbsp;&nbsp;&nbsp;
<input name="HTMLOrText" type="radio" value="1" onClick="textType(1);" <?php echo $HTMLOrText ? 'checked' : ''?> onchange="dChgeThisPage(1);"> HTML
</fieldset>
<br />
<?php
//get the template
if($Composition==1 && !$TemplateLocationURL){
	?>
<br />
<br />
No template selected
<?php
}else{
	?>
	<input type="button" id="ctrlView" name="nullSub3" value="Preview Composition" <?php echo $Composition==1?'':disabled?> onClick="viewComposition()">
	<input type="submit" id="CompositionFinished" name="nullSub1" value="Composition Finished" />
	&nbsp;&nbsp; 
	<input type="submit" id="ctrlCXL" name="nullSub2" value="&nbsp;Cancel&nbsp;" onClick="window.close(); return false;" />
	<br />
	<br />
	<strong>Subject Line of Email</strong>:<br />
	<input type="text" name="Subject" size="50" value="<?php
	if($x=$_SESSION['mail'][$acct]['templates'][$Profiles_ID]['subj']){
		$subj= $x;
	}else if($a=q("SELECT Val FROM relatebase_mail_profiles_vars WHERE Profiles_ID='$Profiles_ID' AND Name='Subject'" ,O_ROW)){
		//get it from the database
		$subj= $a['Val'];
	}
	echo htmlentities($subj);
	?>" onchange="dChgeThisPage(1)"/>
	<hr/>
	<?php
	$conditionColor['fromnetwork']='#CCF2CB';
	$conditionColor['fromthissession']='#FFFFE0';
	$conditionColor['fromsavedvalues']='#D8EAEF';

	if($Composition==1){
		?>
		<div id="editableAreasLegend"><strong>Editable Areas (Color Coded)</strong><br />
		  <span style="border:1px solid #666;background-color:<?php echo $conditionColor['fromnetwork']?>; width:18px; height:18px;padding:0px 8px;margin-top:10px;">&nbsp;</span> From Network (unedited)<br />
		  <span style="border:1px solid #666;background-color:<?php echo $conditionColor['fromthissession']?>; width:18px; height:18px;padding: 0px 8px;margin-top:10px;">&nbsp;</span> From This Session<br />
		  <span style="border:1px solid #666;background-color:<?php echo $conditionColor['fromsavedvalues']?>; width:18px; height:18px;padding: 0px 8px;margin-top:10px;">&nbsp;</span> From Saved Values<br />
		</div>
		<?php
	}else{
		?>
		<strong>Email Body</strong>
		<?php
	}
	?>
	<strong><?php echo $Composition==1  ? 'Editable Areas' : 'Email Body'?></strong>:<br />
	<?php
	//we have three cases:
	#1. An integer value of a file from the VOS file system
	#2. A URL
	#3. A _blankregion tag 
	/**********
	other error checking: if there are no editable regions on the URL, we need to tell them
	note that if the user selects a template with areas a,b, and c, then changes to a template with areas a,d, and e, then area a will show on the second template.  These are stored in session until the user saves the profile at which time the regions will be pulled and unused areas destroyed.
	
	**********/
	if($Composition==0){ //i.e. blank
		$str="<!-- #"."BeginEditable \"_blank_email\" -->Enter your text here..<!-- #"."EndEditable -->";
	}else{
		if($TemplateMethod=='file'){
			exit('VOS file method not developed');
		}else if($TemplateMethod=='url'){ //i.e. a URL
			$str=implode('',file($TemplateLocationURL));
			//echo "<pre>";
			///echo htmlentities($str);
		}
	}
	
	//----------------------------- get and display editable regions -------------------------
	#note this code is also in index.php with a few mods
	//match editable region
	$templateType='Dreamweaver';
	if($templateType=='Dreamweaver 4.0' || $templateType=='Dreamweaver'){
		// DW 4.0
		$start='/<!--\s*(#|Template)'.'BeginEditable\s+(name=)*"([^"]+)"\s*-->/i';
		$stop='/<!--\s*(#|Template)'.'EndEditable\s*-->/i';
	}else if($templateType=='Dreamweaver 6.0+'){
	
	}else if($templateType=='XML Region'){
		//idea here is the tag name, e.g. div|span|p, containing the attribute, e.g. name= or var= etc.
	
	}
	
	$buff=$str;
	while(true){
		//here we toggle through and get the editable regions - much more reliable than regex
		$exp=(!$exp || $exp==$stop ? $start : $stop);
		if(preg_match($exp,$buff,$m)){
			$from=strstr($buff,$m[0]);
			$buff=substr($from, strlen($m[0])-strlen($from));
			if($exp==$start){
				//parse the name of the region
				$name=$m[3];
				//buffer the right string for later
				$buff2=$buff;
			}else{
				//must be stop
				$body=substr($buff2, 0, strlen($buff2) - strlen($buff) - strlen($m[0]));
				//keys are lowercase by convention
				$regions[strtolower($name)]=$body;
			}
		}else{
			break;
		}
		$i++;
		if($i>100){
			//notify admin loop failed
			break;
		}
	}
	if($regions){
		foreach($regions as $editableName=>$body){
			//fill with the existing SESSION, Db, or HTML in that precedence
			if($refreshOrigUrl!=='1' && 
				isset($_SESSION['mail'][$acct]['templates'][$Profiles_ID]['r'][$editableName])){
				//we have the editableArea from session
				$region=$_SESSION['mail'][$acct]['templates'][$Profiles_ID]['r'][$editableName];
				$condition='fromthissession';
			}else if($refreshOrigUrl!=='1' && $db=q("SELECT Val FROM relatebase_mail_profiles_vars WHERE Name='EditableArea' AND Ky='$editableName' AND Profiles_ID='$Profiles_ID'", O_ROW)){
				//the row is "set", i.e. we use it even if it is blank
				$region=$db['Val'];
				$condition='fromsavedvalues';
			}else{
				$region=$body;
				$condition='fromnetwork';
			}
			?>
			<br />
			<h3>
			<input type="button" name="Submit" value=" + " onclick="expand('<?php echo $editableName?>',1);" />
			<input type="button" name="Submit" value=" - " onclick="expand('<?php echo $editableName?>',0);" />
			&nbsp;&nbsp;<strong><?php echo str_replace('_',' ',strtoupper($editableName))?></strong>		  </h3>
			<textarea name="regions[<?php echo $editableName;?>]" id="a_<?php echo $editableName?>" cols="65" rows="5" style="background-color:<?php echo $conditionColor[$condition]?>;"><?php echo trim(htmlentities($region));?></textarea>
			<?php
		}
	}
}
?>
<input name="mode" type="hidden" id="mode" value="composeEmail">
<input name="Profiles_ID" type="hidden" id="Profiles_ID" value="<?php echo $Profiles_ID?>">
<input name="TemplateMethod" type="hidden" id="TemplateMethod" value="<?php echo $TemplateMethod?>" />
<input name="TemplateLocationURL" type="hidden" id="TemplateLocationURL" value="<?php echo $TemplateLocationURL?>">
<input name="SessionToken" type="hidden" id="SessionToken" value="<?php echo $SessionToken?>" />


<!-- InstanceEndEditable --></div>
<div id="footer"><!-- InstanceBeginEditable name="footer" -->&nbsp;&nbsp;<!-- InstanceEndEditable --></div>
<?php if(!$suppressForm){ ?>
</form>
<?php }?>
<?php if(!$hideCtrlSection){ ?>
<div id="showTester" title="Javascript Tester" onclick="g('tester').style.display='block';">&nbsp;</div>
<div id="tester" >
	<a href="#" onclick="g('ctrlSection').style.display='block';return false;">Show Control Section</a><br />
	<textarea name="test" cols="65" rows="4" id="test">clear_form();</textarea><br />
	<input type="button" name="button" value="Test" onclick="jsEval(g('test').value);"><br />
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
page_end();
?>