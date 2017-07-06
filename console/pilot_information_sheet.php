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
#basicformlayout{
	width:900px;
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

<div id="basicformlayout">
  <div id="page1">
		<div id="maininfo">
		  <h1 class="tac">Pilot Information Sheet</h1>
		  <p>INSTRUCTOR NAME: [input:instructorname size=10 ] DATE:
[input:date size=10 ]<br /> 
How did you hear about Texas State Aviation? [input:findouttxaviation size=10 ] <br />
If applicable
:
TSU
ID#:
[input:tsuid size=10 ] ACC ID#: [input:accide size=10 ]<br /> 
If applicable

owned aircraft info: Aircraft N#: [input:aircraftnumber size=10 ] Hangar #: [input:hanger size=10 ] <br />
LAST NAME: [input:lastname size=10 ] FIRST: [input:firstname size=10 ] M.I: [input:middlename size=10 ] <br />
LOCAL ADDRESS: [input:localaddress size=30 ]<br />
CITY: [input:city size=10 ] STATE: [input:state size=10 ] ZIP: [input:zip size=10 ] <br />
DAY PHONE: [input:dayphone size=10 ] EVENING PHONE: [input:eveningphone size=10 ] MOBILE PHONE: [input:mobilephone size=10 ] EMAIL:
[input:email size=15 ] DRIVERS LICENSE: STATE
<select>
	<option value="">Select</option
	><option value="AL">Alabama</option>
	<option value="AK">Alaska</option>
	<option value="AZ">Arizona</option>
	<option value="AR">Arkansas</option>
	<option value="CA">California</option>
	<option value="CO">Colorado</option>
	<option value="CT">Connecticut</option>
	<option value="DE">Delaware</option>
	<option value="DC">District Of Columbia</option>
	<option value="FL">Florida</option>
	<option value="GA">Georgia</option>
	<option value="HI">Hawaii</option>
	<option value="ID">Idaho</option>
	<option value="IL">Illinois</option>
	<option value="IN">Indiana</option>
	<option value="IA">Iowa</option>
	<option value="KS">Kansas</option>
	<option value="KY">Kentucky</option>
	<option value="LA">Louisiana</option>
	<option value="ME">Maine</option>
	<option value="MD">Maryland</option>
	<option value="MA">Massachusetts</option>
	<option value="MI">Michigan</option>
	<option value="MN">Minnesota</option>
	<option value="MS">Mississippi</option>
	<option value="MO">Missouri</option>
	<option value="MT">Montana</option>
	<option value="NE">Nebraska</option>
	<option value="NV">Nevada</option>
	<option value="NH">New Hampshire</option>
	<option value="NJ">New Jersey</option>
	<option value="NM">New Mexico</option>
	<option value="NY">New York</option>
	<option value="NC">North Carolina</option>
	<option value="ND">North Dakota</option>
	<option value="OH">Ohio</option>
	<option value="OK">Oklahoma</option>
	<option value="OR">Oregon</option>
	<option value="PA">Pennsylvania</option>
	<option value="RI">Rhode Island</option>
	<option value="SC">South Carolina</option>
	<option value="SD">South Dakota</option>
	<option value="TN">Tennessee</option>
	<option value="TX">Texas</option>
	<option value="UT">Utah</option>
	<option value="VT">Vermont</option>
	<option value="VA">Virginia</option>
	<option value="WA">Washington</option>
	<option value="WV">West Virginia</option>
	<option value="WI">Wisconsin</option>
	<option value="WY">Wyoming</option>
</select>
NUMBER: [input:driverslicenenumber size=10 ] SOCIAL SECURITY # (Optional): [input:socialsecuritynumber size=10 ]
  PILOT CERTIFICATE #:
		    [input:pilotcertificatenumber size=10 ] CERTIFICATES & RATINGS HELD: [textarea:certificatesandratings cols=60 rows=2 ] MEDICAL CLASS: [input:medicalclass size=10 ]
		    DATE ISSUED: [input:dateissued size=10 ] TOTAL TIME: [input:totaltime size=10 ]
		    INSTRUMENT TIME: [input:instrumenttime size=10 ]
		    COMPLEX TIME: [input:complextime size=10 ] MULTI
		    -
		    ENGINE TIME: [input:multenginetime size=10 ] TOTAL TIME IN THE PAST 90 DAYS: [input:totaltime size=10 ] LAST FLIGHT REVIEW
		    : [input:lastflightreview size=10 ]
		    LAST INSTR
		    .
		    PROF
		    .
		    CHECK
		    : [input:lastinstprofcheck size=30 ] <br />
            <br />
	      </p>
		 <div id="sectiontwo"> 
		  <table width="200" border="0">
            <tr>
              <td>AIRCRAFT FLOWN: </td>
              <td>TOTAL HOURS </td>
              <td>PAST 90 DAYS </td>
              <td>LAST CHECKOUT </td>
            </tr>
            <tr>
              <td><p>DA20C1</p>
              </td>
              <td>[input:totalhours1 size=6 ] </td>
              <td>[input:past90days1 size=6 ] </td>
              <td>[input:lastcheckout1 size=6] </td>
            </tr>
            <tr>
              <td>C172P</td>
              <td>[input:totalhours2 size=6 ]</td>
              <td>[input:past90days2 size=6 ] </td>
              <td>[input:lastcheckout2 size=6]</td>
            </tr>
            <tr>
              <td>C172R-180</td>
              <td>[input:totalhours3 size=6 ]</td>
              <td>[input:past90days3 size=6 ] </td>
              <td>[input:lastcheckout3 size=6]</td>
            </tr>
            <tr>
              <td>Piper PA28-180 </td>
              <td>[input:totalhours4 size=6 ]</td>
              <td>[input:past90days4 size=6 ] </td>
              <td>[input:lastcheckout4 size=6]</td>
            </tr>
            <tr>
              <td>Columbia 350 </td>
              <td>[input:totalhours5 size=6 ]</td>
              <td>[input:past90days5 size=6 ] </td>
              <td>[input:lastcheckout5 size=6]</td>
            </tr>
            <tr>
              <td>Citabria</td>
              <td>[input:totalhours6 size=6 ]</td>
              <td>[input:past90days6 size=6 ] </td>
              <td>[input:lastcheckout6 size=6]</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>[input:totalhours7 size=6 ]</td>
              <td>[input:past90days7 size=6 ] </td>
              <td>[input:lastcheckout7 size=6]</td>
            </tr>
          </table>
		  </div>
		  <p>  <br />
  <br />
  <div>
		    IN CASE OF EMERGENCY NOTIFY: [input:emergencycontact size= 30] RELATIONSHIP: [input:relationship size=10 ] <br />
		    ADDRESS: [input:econtactaddress size=60 ] <br />
		    CITY: [input:econtactcity size=10 ] STATE: <select>
	<option value="">Select</option
	><option value="AL">Alabama</option>
	<option value="AK">Alaska</option>
	<option value="AZ">Arizona</option>
	<option value="AR">Arkansas</option>
	<option value="CA">California</option>
	<option value="CO">Colorado</option>
	<option value="CT">Connecticut</option>
	<option value="DE">Delaware</option>
	<option value="DC">District Of Columbia</option>
	<option value="FL">Florida</option>
	<option value="GA">Georgia</option>
	<option value="HI">Hawaii</option>
	<option value="ID">Idaho</option>
	<option value="IL">Illinois</option>
	<option value="IN">Indiana</option>
	<option value="IA">Iowa</option>
	<option value="KS">Kansas</option>
	<option value="KY">Kentucky</option>
	<option value="LA">Louisiana</option>
	<option value="ME">Maine</option>
	<option value="MD">Maryland</option>
	<option value="MA">Massachusetts</option>
	<option value="MI">Michigan</option>
	<option value="MN">Minnesota</option>
	<option value="MS">Mississippi</option>
	<option value="MO">Missouri</option>
	<option value="MT">Montana</option>
	<option value="NE">Nebraska</option>
	<option value="NV">Nevada</option>
	<option value="NH">New Hampshire</option>
	<option value="NJ">New Jersey</option>
	<option value="NM">New Mexico</option>
	<option value="NY">New York</option>
	<option value="NC">North Carolina</option>
	<option value="ND">North Dakota</option>
	<option value="OH">Ohio</option>
	<option value="OK">Oklahoma</option>
	<option value="OR">Oregon</option>
	<option value="PA">Pennsylvania</option>
	<option value="RI">Rhode Island</option>
	<option value="SC">South Carolina</option>
	<option value="SD">South Dakota</option>
	<option value="TN">Tennessee</option>
	<option value="TX">Texas</option>
	<option value="UT">Utah</option>
	<option value="VT">Vermont</option>
	<option value="VA">Virginia</option>
	<option value="WA">Washington</option>
	<option value="WV">West Virginia</option>
	<option value="WI">Wisconsin</option>
	<option value="WY">Wyoming</option>
</select> ZIP: [input:econtactzip size=10 ] <br />
		    DAY PHONE: [input:edayphone size=10 ] EVENING PHONE: [input:eeveningphone size= 10 ] </p>
		</div>
		</div>
</div>
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