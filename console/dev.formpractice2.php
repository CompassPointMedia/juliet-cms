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
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="file:///C|/Users/owner/Desktop/Compass Point Media/Hosted Accounts/Templates/reports_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Aircraft Checkout Questionnaire</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" href="/Library/css/cssreset01.css" type="text/css" />
<link rel="stylesheet" href="../rbrfm_admin.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<style type="text/css">


#maininfo{
	border:1px dotted;
	padding: 5px 30px;
}
#mainBody{
	padding:25px 50px;
	}
#basic form layout{
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
<form action="file:///C|/Users/owner/Desktop/Compass Point Media/Hosted Accounts/console/resources/bais_01_exe.php" method="post" enctype="multipart/form-data" name="form1" target="w2" id="form1" onsubmit="return beginSubmit();">
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
		  <h1 class="tac">Aircraft Checkout Questionnaire</h1>
          <p>Pilot's Name: [input:name size=20 ] Date: [input:name size=6 ]<br />
            Instructor s Name: [input:name size=20 ] Date: [input:name size=6 ]<br />
            Aircraft Type: [input:aircrafttype size=20 ] N#: [input:number size=10 ] <br />
            <br />
            * * * * * * * * *              * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *   * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * <br />
            <br />
        V speeds (Indicated Airspeeds) Check Scale: knots [input:knots size=3 ] MPH: [input:mph size=3 ]<br />
        </p>
          <table width="813" border="1">
            <tr>
              <td width="150">Vso [input:vso size=6 ] <br />
                Power off Stall<br />
                Configuration<br />
                (botton white arc) </td>
              <td width="146">Vx [input:vx size=6 ] <br />
              Best Angle of Climb Speed </td>
              <td width="139">Va [input:va size=6 ] <br />
              Maneuvering Speed </td>
              <td width="156">Vle [input:vle size=6 ] <br />
              Max Speed w. Gr Extd </td>
              <td width="188">Vmc [input:vmc size=6 ] <br />
              Min. Control Speed w. Critical Engine InopU </td>
            </tr>
            <tr>
              <td><p>Vs1 [input:vs1 size=6 ] <br />
                Minimum<br />
                Flight Speed<br />
              Flaps Up<br />
              (Bottom green arc)
</p>
                </td>
              <td>Vy [input:vy size=6 ] <br />
              Best Rate of Climb Speed </td>
              <td>Vno [input:vno size=6 ] <br />
                Max Speed<br />
                Normal<br />
                Operations<br />
                (Green Arc) </td>
              <td><p>Vne [input:vne size=6 ] <br />
                Never Exceed<br />
                Speed<br />
              (Red Line) </p>
                </td>
              <td>Vyse [input:vsyse size=6 ] <br />
                Best Rate of Climb w. Engine Inop (Blue Line) </td>
            </tr>
            <tr>
              <td>1.3 x Vso <br />
                Fence Speed<br />
                (Short Final Ref Spd) <br />
                (KCASx1.3=conv KIAS) </td>
              <td><p>Vfe [input:vfe size=6 ] <br />
                Max Flap<br />
                Ext. Speed<br />
              (\White Arc) </p>
                </td>
              <td>Vlo [input:vlo size=6 ] <br />
                Max Gear<br />
                Operation<br />
                Speed</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
          </table>
          <br />
        Best Glide Speed/Configuration: [textarea:bestguidespeed cols=50 rows=2 ]
        <p><br />
          <br />
          * * * * * * * * *
          * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * <br />
          <br />
          Useable Fuel: [input:useable fuelsize=10 ]
          <br />
          Fuel Grade: [input:fuelgrade size=10 ]<br />
          # Fuel Drains: [input:fueldrains size=10 ]<br />
          Locations: [input:locations size=10 ]
          <br />
          <br />
          Range: 
          (75%): [input:range size=10 ]
            
          (Optimum) [input:optimum size=10 ]<br />
          <br />
        </p>
          <table width="257" height="90" border="0">
            <tr>
              <td>Oil Capacity/Minimum:</td>
              <td>[input:oilcapacitymin size=10 ]</td>
            </tr>
            <tr>
              <td>Type/Viscosity:</td>
              <td>[input:typeviscosity size=10 ]</td>
            </tr>
          </table>
          <br />
          Tire Pressure: <br />
          <table width="190" border="0">
            <tr>
              <td width="40">Nose</td>
              <td width="198">[input:nose size=10 ]</td>
            </tr>
            <tr>
              <td>Mains</td>
              <td>[input:mains size=10 ]</td>
            </tr>
            <tr>
              <td>Tail</td>
              <td>[input:tail size=10 ]</td>
            </tr>
          </table>
          <p><br />
            <br />
            Empty Weight: [input:emptyweight size=10 ]<br />
          Gross: [input:gross size=10 ]<br />
            Useful load: [input:usefulload size=10]<br />
          Emply CG/Moment: [input:emptyCG size=10 ]<br />
          Gross Weight CG Range: [input:grossweight size=10 ]<br />
          Date of last CG [input:lastCGdate size=10 ]</p>
          <p>What are the unsafe gear indications?<br /> 
            [textarea:unsafehearindications cols=40 rows=3 ] <br />
          What is the procedure for emergency gear extension?<br /> 
          [textarea:grearextension cols=40 rows=3 ] <br />
          How do you detect carburetor/induction ice?<br /> 
          [textarea:carburetorinductionice cols=40 rows=3 ] <br />
          In the event of a carb/induction ice, what do you do? <br />
          [textarea:carbinductioniceevent cols=40 rows=3 ] <br />
              <br />
            <br />
          Calculate performance figures- 65% Power = 7500'<br />
        Stand. Temp:</p>
          <table width="200" border="1">
            <tr>
              <td>Manifold Pres.:</td>
              <td>[input:manifold size=10 ] </td>
              <td>RPM:</td>
              <td>[input:rpm size=10 ] </td>
            </tr>
            <tr>
              <td>GPH:</td>
              <td>[input:GPH size=10 ] </td>
              <td>TAS:</td>
              <td>[input:tas size=10 ] </td>
            </tr>
          </table>
          <p><br />    
            <br />
            Decribe the mixture leaning procedure for this aircraft:<br /> 
            [textarea:leaningprocudure cols=40 rows=3 ]<br />
            <br />
            What is the minimum runway for take-off with max no wind, sea level, standard temp?<br />
	  [textarea:minrunwaytakeoff cols=40 rows=3 ] <br />
	  <br />
	  Max weight, no wind, 5,000', 100&amp;deg;, 50' obstacle?<br />
	   
	  [textarea:maxweightnowind cols=40 rows=3 ] <br />
	  <br />
	  Describe the go-around Procedure: <br />
	  [textarea:goaroundprocedure cols=40 rows=3 ] </p>
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