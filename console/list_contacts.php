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
<title><?php echo h($adminCompany);?> :: List Contacts</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" type="text/css" href="rbrfm_admin.css" />
<link rel="stylesheet" type="text/css" href="/Library/css/DHTML/dynamic_04_i1.css" />
<link rel="stylesheet" type="text/css" href="/Library/css/DHTML/data_04_i1.css" />
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
	
	<h1>RelateBase Contact Manager</h1>
	<p>
	A "contact" is different from either a member or business.  All businesses have at least one contact; but not all contacts have a business or are a "member" of your organization.  </p>
	<p>In your database, there are two separate tables, one for either businesses or members (called clients), and another for contacts (appropriately called contacts).  If you add a member to your organization, the member and their business are stored in  the clients table and the individual is stored in the contacts table.  It is possible to have multiple contacts in one business (but only one primary contact).   If your site has an E-commerce module and someone purchases something online, that person is a &quot;member&quot; i.e. they are stored both in the client and the contacts table.  It is necessary to store them in the client table because they are now part of your financial system (they have made a purchase) - even though they are not a company per se. (Individual purchasers are stored in the clients table with their full name listed as the company name - similar to QuickBooks). </p>
	<h2>Storing Contacts Only</h2>
	<p>Contacts are people who you wish to communicate with, or people who have signed up on your website (either for a newsletter or to participate in a forum or comment on a blog), but who have not made any purchase. They are stored in the contacts table. You may wish to import your entire Microsoft Outlook or Yahoo/Google/Hotmail address book into the RelateBase Contact Manager. As we develop further mailing and communication tools, this is the place you want to be to further your organization or business!</p>
	<h2>Uniqueness Criteria</h2>
	<p>It is necessary that all contacts you import have at least an email (a name is not required in this case), or a name (an email is not required in this case). However, the following rules will be followed in importing records:</p>
	<ul>
	  <li>Email-only records will not be imported if the email exists in the contacts table</li>
      <li>Name-only records (no email) <u>WILL BE IMPORTED</u>; if the name matches an existing name (see Name Matching below), it will be used to update existing address information (this will overwrite what you have in your database!)</li>
      <li>Records with both an email and name will be treated as follows:
        <ul>
          <li>if both the name and email match, an update of information will be performed (see Name Matching below) </li>
          <li>if the email matches but the name does not, a new contact will be added. This assumes that two people share an email address (for example a husband and a wife)</li>
          <li>if the name matches but the email does not, a new contact will be added.  </li>
        </ul>
      </li>
    </ul>
	<h2>Name Matching </h2>
	<p>When comparing names for matches, the system allows for nickname variations for the first name. So Robert Duvall will match with Bob Duvall. Middle names follow these rules:</p>
	<ul>
	  <li>If the names are otherwise identical but one record has a middle name (or initial) and the other does not, they are considered a match  </li>
      <li>If the names are otherwise identical but one record has a letter such as J and the other has a full name such as Julian, they are considered a match </li>
	  <li>Middle names that differ, or whose first letters differ, are treated as two separate contacts </li>
	</ul>
	<p><strong><a href="list_contacts_actual.php">Go to Contacts List</a></strong></p>
	<p><strong>
	<a onclick="return ow(this.href,'l1_importmanager','800,700');" title="Import contact, items and other records into your RelateBase Console" href="importmanager.php?Data=Contacts">Import Contacts</a>	</strong></p>
	<?php 
	
	?>
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