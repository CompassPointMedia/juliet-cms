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
<title>Flight Review Questionnaire</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" href="/Library/css/cssreset01.css" type="text/css" />
<link rel="stylesheet" href="rbrfm_admin.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<style type="text/css">
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
<div id="flightReviewQuestionnaire">
  <div id="page1">
		<div id="mainInfo">	
		
		you can fill this in
		
		</div>
			<ol>
				<li>
					A flight review must contain [input:flightReviewRequiredGround size=3 ] ground instruction and [input:flightReviewRequiredFlight size=3 ] fight instruction. (hours) <br />
				</li>
				<li>The ground portion of the Flight Review must contain a comprehensive review of Part(s) [textarea:partsreview cols=50 rows=5 ] </li>
				<li>The flight portion of the Flight
Review
must contain these maneuvers [input:maneuver1 size=6 ]&nbsp;&nbsp;&nbsp; [input:maneuver2 size=6 ] &nbsp;&nbsp;&nbsp; [input:maneuver3 size=6 ]</li>
	            <li>What flights must be logged in a logbook?<br />
				[textarea:logbookflights cols=50 rows=5 ] </li>
	            <li>In order to carry passengers, you must have made [input:passengerflights size=3] landings in the category and
class within the previous [inputnumberofdays size= 3 ] days </li>
              <li>What information must a pilot familiarize him/herself with before each flight?[textarea: flightinformation cols=50 rows=5 ]</li>
	            <li>No person may pilot an aircraft within [input:hoursafteralcohol size=3] hours of consumption of any alcoholic
              beverage.</li>
              <li>What drugs can not be taken before a flight? [textarea:drugs cols=50 rows=5 ]</li>
              <li>            A parachute is necessary when a pilot is carrying a passenger, if a bank angle of [input:parachutedegree size=3 ]
            degrees or a nose up or down angle of [input:downangle size=3 ]degrees for any intentional maneuver
            is
            exceeded.            </li>
	          <li>Fuel reserves for VFR flight are: day [input:dayfuel size=6 ] night [input:nightfuel=6 ]</li>
	          <li>Where is a transponder (with Mode C) necessary?
	            <table width="200" border="1">
	              <tr>
	                <td>a. [input:transponderlocation1 size=6 ] </td>
	                <td>d. [input:transponderlocation4 size=6] </td>
                  </tr>
	              <tr>
	                <td><p>b. [input:transponderlocation2 size=6 ]</p></td>
	                <td>e. [input:transponderlocation5 size=6] </td>
                  </tr>
	              <tr>
	                <td>c. [input:transponderlocation3 size=6 ] </td>
	                <td>f. [input:transponderlocation6 size=6 ] </td>
                  </tr>
                </table>
	          </li>
	          <li>Oxygen is required above [input=oxygenaltitude size=3 ] feet regardless of the time flown at that altitude.            </li>
	          <li>What are your pitot static instruments?
            [textarea: staticinstruments cols=60 rows=5 ]&nbsp;&nbsp;&nbsp;Gyro instruments? [textarea:gyroinstruments cols=50 rows=5]            </li>
	          <li>An ELT is required if a training flight goes beyond [input:elt size=3 ] miles from your departure
            point and the aircraft is equipped to carry more than one person.            </li>
	          <li> When aircrafts are approaching head on, each aircraft shall alter their course to the [input: directionchange size=10 ]          </li>
	          <li>No person may perform aerobatics below [input:aerobaticsheight size=6 ] feet AGL.            </li>
	          <li> The minimum altitude over a congested area is [input:minaltitude size=6 ] feet above the highest obstacle
            within [input:horizontalfeet size=6 ] feet horizontally.            </li>
	          <li>Two
            -
            way radio communications are necessary within Class(es) [input:class1 size=3 ] &nbsp;[input:class2 size=3 ] &nbsp;input:class3 size=3 ] airspace.</li>
	          <li>The standard pattern at an airport without a control tower and no visual pattern markings is [input=standardpattern size=6 ]
            hand turns.</li>
	          <li>Clearance from ATC is necessary to penetrate Class(es)[textarea:atcclasses: cols=50 rows=5 ] airspace.</li>
	          <li>You may not operate in [textarea:areas cols=50 rows=5 ] areas without permission of the controlling agency.</li>
	          <li>You may not operate in Class A airspace under [input:classaairspace size=6 ] flight rules.</li>
	          <li>Basic VFR weather minimums in controlled airspace below 10,000 feet are [input:minimum size=6 ]
            visibility and [input=below size=6 ] below, [input:above size=6 ], and [input:horizontally size=6] horizontally from
            clouds.            </li>
			</ol>
  </div>
	<div id="page2">
      <ol start="24"> 
        <li>VFR minimums in Class G airspace, under 1200' feet, daytime, are [input:vfrmins size=3 ] mile(s)
          visibility and [input:cloudmins size=6 ] of clouds.</li>
        <li> Under Special VFR, daytime, you may operate with visibility at least [input:vfrdaytime size=3 ] miles and [input: vfrclouds size=6 ] of clouds when cleared by ATC.</li>
        <li>When is an instrument rating required to operate under Special VFR [textarea:instrumentratingspecialvfr cols=50 rows=5 ] </li>
        <li>When operating below 1
          8,000 feet MSL and above 3,000 AGL, you should cruise at
          [input:cruise size=6] thousands plus 500 feet on a mag course of 360 through 179 degrees, and
          [input:cruise2thousands plus 500 feet on a mag course of 180 through 359 degrees.</li>
        <li>When does your pilot certificate expire? [input: certificateexpire size=6 ]</li>
        <li>How long is your medical good for? [input:medicalccertificatetime size=6]</li>
        <li>What are the required documents that have to be on board for each flight
          within the U.S.
          ?
          Aircraft
          <table width="200" border="1">
              <tr>
                <td>[input:reqdoc1 size=6 ] </td>
              </tr>
              <tr>
                <td>[input:reqdoc2 size=6 ] </td>
              </tr>
              <tr>
                <td>[input:reqdoc3 size=6 ] </td>
              </tr>
              <tr>
                <td>[input:reqdoc4 size=6 ] </td>
              </tr>
            </table>
          ��������������������&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pilot
          &nbsp;&nbsp;
          <table width="200" border="1">
                    <tr>
                      <td>[input:pilotdoc1 size=6 ] </td>
                    </tr>
                    <tr>
                      <td>[input:pilotdoc2 size=6 ] </td>
                    </tr>
                    <tr>
                      <td>[input:pilotdoc3 size=6 ] </td>
                    </tr>
                    <tr>
                      <td>[input:pilotdoc4 size=6 ] </td>
                    </tr>
                  </table>
          �</li>
        <li>What is the distress or mayday transponder code? [textarea:maydaycode cols=60 rows=5 ] </li>
        <li>When should you use carburetor heat
          (if installed in the aircraft)
          ?
          ______________________
          ____________________
          _______________________________</li>
        <li>Why should you not use carburetor heat (if installed in the aircraft) while taxiing? [textarea: carburetorheat cols=60 rows=5 ] </li>
        <li> If you have a complete electrical
          failure, will your engine quit? [textarea:enginequit cols=60 rows=5 ] </li>
        <li>Spin recovery for this airplane requires : [textarea:spinrecovery cols=50 rows=5 ] </li>
        <li>You must always enter the traffic
          pattern at a 45 degree angle to the downwind. True / False. [input:trafficpattern size=6 ] </li>
        <li>The traffic pattern for this flight
          at this airport
          should be at [input:trafficpatternairportfeet size=6 ] MSL, and
          [input:aglfeet size=6 ] feet AGL.</li>
        <li>The most important thing to do in the event of engine failure is to maintain [input:enginefailurespeed size=6 ] speed.</li>
        <li>What is the distress radio frequency? [input:distressradio: size=6 ] </li>
        <li>What is &lsquo;Flight Watch&rsquo;? [textarea:flightwatch cols=60, rows=5 ] </li>
        <li>What is the &lsquo;Flight Watch&rsquo; frequency? [textarea:flightwatchfrequency cols=60, rows=5 ] </li>
        <li>What is the standard FSS frequency? [input:fss size=6] </li>
        <li>What is a &lsquo;TFR&rdquo;? [textarea:tfr cols=60 rows=5 </li>
        <li>How can you obtain information on TFR&rsquo;s? [textarea:infotfr cols=60, rows=5 ] </li>
        <li>What is the best glide speed of your plane?
          Aircraft type [input=glidespeed size=6 ]&nbsp;	            Best Glide Speed [input:bestglidespeed size=6 ] </li>
        <li>What is Va and why is it important? [textara:va cols=60 rows=5 ] </li>
        <li>What is the airport beacon colors/pattern for a
          CIVILIAN
          airport? [textarea:civilianplane cols=60 rows=5 ]&nbsp;	            MILITARY airport [textarea:militiaryplace cols=60 rows=5 ] </li>
        <li>When would you lean the mixture on the ground? [textarea: mixtureground cols=60 rows=5 ] </li>
        <li>What three factors affect density altitude? [input:factor1 size=6 ] [input:factor2 size=6] [input:factor3 size=6 ] </li>
      </ol>
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