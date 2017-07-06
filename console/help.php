<?php 
/*
NOTES this page:

*/

//identify this script/GUI
$localSys['scriptID']='generic';
$localSys['scriptVersion']='1.0';
$localSys['componentID']='main';


//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');
$hideCtrlSection=false;

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/rbrfm_01.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo h($adminCompany);?> Albums Suite</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" type="text/css" href="rbrfm_admin.css" />
<link rel="stylesheet" type="text/css" href="/Library/css/DHTML/dynamic_04_i1.css" />
<link rel="stylesheet" type="text/css" href="/Library/css/DHTML/data_04_i1.css" />
<style type="text/css">
</style>

<script src="/Library/js/global_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/common_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/forms_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/loader_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/contextmenus_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/dataobjects_04_i1.js" language="JavaScript" type="text/javascript"></script>
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
var isEscapable=0;
var isDeletable=0;
var isModal=0;
var talks=0; //whether this page broadcasts overall state changes which other pages listen for
var listens=0;

function setID(id,category){
	g("ID").value=id;
	g("Category").value=category;
}


<?php 
//js var user settings
js_userSettings();
?>
var login=<?php echo $_SESSION['identity'] ? 1 : 0?>;
</script>

<!-- InstanceEndEditable -->
</head>

<body>
<div id="mainContainer">
	<!-- InstanceBeginEditable name="admin_top" --><!-- #BeginLibraryItem "/Library/rbrfm_adminmenu_basic_01.lbi" --><?php
require($_SERVER['DOCUMENT_ROOT'].'/console/rbrfm_adminmenu_basic_02.php');
?><!-- #EndLibraryItem --><!-- InstanceEndEditable -->
	<!-- InstanceBeginEditable name="top_region" --><!-- InstanceEndEditable -->
	<div id="leftInset">
	<!-- InstanceBeginEditable name="left_inset" --><!-- InstanceEndEditable -->
	</div>
	<div id="mainBody">
	<!-- InstanceBeginEditable name="main_body" -->



    <p>Here is a quick-and-easy overview of using the console:</p>
    <h2>Adding Members    </h2>
    <p>      go to www.kyle-networking.com/console/<br />
      username=cpm149<br />
      password=xxxxx<br />
      select Members &gt; Add New Member<br />
      fill out the information, note the following:<br />
      Customer name must be unique: e.g. The Maids Kyle Branch<br />
      Company name on Company tab can be e.g. The Maids, Inc. - can have more than one<br />
      Status: Event signup is NON-members, don't use this, instead select Current Approved Member<br />
      Company Tab<br />
      self-explanatory<br />
      Contact Tab<br />
      self-explanatory - be complete for mailings, emergency contacts etc.<br />
      Member Info<br />
      Other Org = other organizations they're part of<br />
      Web Page<br />
      self-explanatory - eventually this will have multiple sections<br />
      Web Fields<br />
      these are SEO fields for optimizing their web page and web page layout.<br />
      Settings<br />
      VERY IMPORTANT!<br />
      1) give them a password<br />
      2) select business categories or create new by listing separated by commas.  AND NOTICE that I have set up categories for leadership committee, membership committee, president, vice-president, secretary and treasurer</p>
    <p>click save and close, or save and new if adding multiple.  They are now in.</p>
    <h2>Viewing Members</h2>
    <p>Members &gt; List Members<br />
      from here you can delete or edit by the button on the left.  You can also select fields to view.  Select or hide fields by a small icon in the upper right corner of the table</p>
    <h2>Site Editor</h2>
    <p>go to http://www.kyle-networking.org/kylenetworking-event-calendar.php<br />
      for example (but eventually any page will be editable) go to bottom of page and click site editor<br />
      username=cpm149<br />
      password=xxxx<br />
      notice small + buttons; click on any date to add an event<br />
    try clicking on the 12th and setting up tomorrow's meeting - this should be self-explanatory</p>
    <h2>Optimizing site pages </h2>
    <p>Note the gray box upper left.  Click the +/- button to show or hide<br />
      then fill in the meta fields as desired to optimize the page - this needs done and I'll get around to it at some point<br />
      click the x button to leave site editor mode</p>
    <h2>Adding Events</h2>
    <p>can be done via the way I just described, or in the console Events &gt; Create New Event<br />
      I have set up 3 calendars<br />
      KNO Meeting Schedule - for regular and leadership meetings<br />
      Member Business Events - open houses, presentations, ribbon cuttings, blockbuster blow-em-out sales events at Henna Chevrolet, etc. etc.<br />
      Community Calendar - for all members; fundraisers, fish fries, and so on.</p>
    <h2>Adding Articles</h2>
    <p>in the console go to Content &gt; List Articles or &gt; Add New Article<br />
      this should be self-explanatory<br />
      IMPORTANT!! Notification tab is not fully developed; when finished you will be able to check &quot;Send this out to..&quot; and select committees, subgroups, &quot;all&quot; or individual members who will receive an email and link to the article or announcement</p>
    <p>&nbsp;</p>
    <h2>Managing member logos:</h2>
    <p>1. in the console click on Images &gt; file Explorer<br />
      2. username=cpm149, password=xxxx<br />
      3. double-click on logos<br />
      4. double-click on small<br />
      5. upload a file named srobinson.jpg for the maids (her username) - to verify a username, go to list members and open their record.  Click on the settings tab.  It'll tell you the username<br />
      (to upload click the white file icon at top and select a file from your computer)<br />
    </p>
    <div class="cb">&nbsp;</div>
<!-- InstanceEndEditable -->
	<div class="cbsm"> </div>
  </div>
	<div id="footer">
	<!-- InstanceBeginEditable name="footer" --><!-- #BeginLibraryItem "/Library/rbrfm_footer.lbi" -->&copy;2008-<?php echo date('Y');?> RelateBase Services Inc. - 
<a href="/" target="_blank" title="View index page of your website">view site</a> | 
<a href="http://www.compasspointmedia.com/mediawiki/index.php?title=RelateBase_Ecommerce_Console:RBRFM:Public_Documentation" target="helpme">WIKI</a><!-- #EndLibraryItem --><!-- InstanceEndEditable -->
	</div>
</div>

<div id="showTester" title="Javascript Tester" onClick="g('tester').style.display='block';">&nbsp;</div>
<div id="tester" >
	<a href="#" onClick="g('ctrlSection').style.display=op[g('ctrlSection').style.display]; return false;">iframes</a><br />
	<textarea name="test" cols="65" rows="4" id="test">clear_form();</textarea><br />
	<input type="button" name="button" value="Test" onClick="jsEval(g('test').value);"><br />
	<textarea id="result" name="result" cols="65" rows="3" ></textarea>
</div>
<?php if(!$hideCtrlSection){ ?>
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