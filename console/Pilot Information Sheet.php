<?php 
/*
The first Anna project
to do:
	differentiate the data fields between key fields and form specific fields
	work on page breaking
	work on selects
	
*/
//identify this script/GUI
$localSys['scriptID']='generic';
$localSys['scriptVersion']='1.0';
$localSys['componentID']='main';


//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');
if(!function_exists('form_field_translator'))require($FUNCTION_ROOT.'/function_form_field_translator_v100.php');


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="../Templates/reports_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Pilot Information Sheet</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" href="/Library/css/cssreset01.css" type="text/css" />
<link rel="stylesheet" href="rbrfm_admin.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<style type="text/css">


#maininfo{
	border:1px dotted;
	padding: 5px 30px;
}
#mainBody{
	padding:25px 50px;
	}
#flightReviewQuestionnaire{
	width:600px;
	border: solid 1px;
	padding: 60px;
	background-color:#FFFFCC
	}
</style>
<script language="JavaScript" type="text/javascript" src="/Library/js/jquery.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/global_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/common_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/forms_04_i1.js"></script>
<script language="JavaScript" type="text/javascript">
/* periwinkle coding */
var thispage='<?php echo $thispage?>';
var thisfolder='<?php echo $thisfolder?>';
var browser='<?php echo $browser?>';
var ctime='<?php echo $ctime?>';
var PHPSESSID='<?php echo $PHPSESSID?>';
//for nav feature
var count='<?php echo $nullCount?>';
var ab='<?php echo $nullAbs?>';
</script>

<!-- following coding modified from ajaxloader.info - a long way to go to be modular -->
<script language="javascript" type="text/javascript">
</script>
<!-- InstanceEndEditable -->
</head>

<body id="report">
<?php if(!$suppressForm){ ?>
<form action="../console/resources/bais_01_exe.php" method="post" enctype="multipart/form-data" name="form1" target="w2" id="form1" onsubmit="return beginSubmit();">
<?php }?>
<div id="header"><!-- InstanceBeginEditable name="top_nav" -->



<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->

<?php
ob_start(); //form_field_translator
?>

<div id="basic form layout">
  <div id="page1">
		<div id="maininfo">
		  <h1 class="tac">Pilot Information Sheet </h1>
		  <p><br />
		</div>
		INSTRUCTOR NAME: [input:firstname size=6 ] LAST NAME:[input:lastname size=6]
DATE:
___________________________
How did you hear about Texas State Aviation?
________________________________________________
If applicable
:
TSU
ID#:
_____
__________________
ACC ID#:
________________________________
If applicable
�
owned aircraft info
: Aircraft N#: _______________________
Hangar #:
_____________
LAST NAME: _______________________
FIRST: ____________________ M.I: _______________
LOC
AL ADDRESS: ____________________________________________________________________
CITY: __________________________ STATE: ____________________ ZIP: __________________
DAY PHONE: (___)
______________________ EVENING PHONE: (___)
____________________
___
MOBILE PHONE
: (____)
____________________
EMAIL
:
___________________________________
DRIVERS LICENSE: STATE ____#__________________
SOCIAL SECURITY # ______
-
____
-
______
(Optional)
PILOT CERTIFICATE #
_____________ CERTIFICAT
ES & RATINGS HELD: _______
___________
MEDICAL CLASS: _______________________
DATE ISSUED: ______________________________
TOTAL TIME: ________
INSTRUMENT TIME: __________
COMPLEX TIME: ________________
MULTI
-
ENGINE TIME: ________ TOTAL TIME IN TH
E PAST 90 DAYS: _____________________
_
LAST FLIGHT REVIEW
: __________________
LAST INSTR
.
PROF
.
CHECK
: ______
__________
AIRCRAFT FLOWN:
TOTAL HOURS
PAST 90 DAYS
LAST CHECKOUT
-
DA20C1
_____________
_____________
________________
-
C172P
_____________
_____________
________________
-
C172R
-
180
_____________
_____________
________________
-
P
iper
P
A
28R
-
180
_____________
_____________
________________
-
Columbia 350
__
___________
_____________
________________
-
Citabria
_____________
_____________
________________
-
______________
__
_____________
_____________
________________
IN CASE OF EMERGENCY NOTIFY: ___________________ RELATIONSHIP: _____________
ADDRESS: ___________________________________________________________________________
CITY: ____________________ STATE: ____________________ ZIP: _______________________
DAY PHONE: (___)
______________________ EVENING PHONE: (___)
_________________
_____
			<ol>
				<li></li>
			</ol>
  </div>
	<div id="page2"></div>
</div>

<?php
$form=ob_get_contents();
ob_end_clean();
echo form_field_translator($form, array(
	'arrayString'=>'_settings',
));
?>
  
<!-- InstanceEndEditable --></div>
<div id="footer"><!-- InstanceBeginEditable name="footer" -->
&nbsp;
<!-- InstanceEndEditable --></div>
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