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
<title><?php echo h($adminCompany);?> :: List Members or Clients</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" type="text/css" href="rbrfm_admin.css" />
<link rel="stylesheet" type="text/css" href="/Library/css/DHTML/dynamic_04_i1.css" />
<link rel="stylesheet" type="text/css" href="/Library/css/DHTML/data_04_i1.css" />
<style>
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

	<?php 
	/*if(isset($membersUseStatusFilterOptions))$useStatusFilterOptions=$membersUseStatusFilterOptions;
	require('components/comp_55_list_visits_v100.php');*/
	$visits=q("SELECT
		b.Visitors_ID, b.PagesViewed, b.FirstViewTime,b.LastViewTime, c.Contacts_ID, c.Ips_ID, CONCAT(e.Name, f.Name,g.Name,h.Name,'?',i.Name) AS PageViewed, d.EntryTime AS PageEntryTime, k.Name AS Useragent_Name, l.IPAddress
		FROM stats_VisitorsPageserved a
		LEFT JOIN stats_visits b ON b.ID=a.Visits_ID
		LEFT JOIN stats_visitors c ON c.ID = a.Visitors_ID
		LEFT JOIN stats_pageserved d ON d.ID = a.Pageserved_ID
		LEFT JOIN stats_protocols e ON e.ID = d.Schemes_ID
		LEFT JOIN stats_domains f ON f.ID = d.Domains_ID
		LEFT JOIN stats_paths g ON g.ID = d.Paths_ID
		LEFT JOIN stats_files h ON h.ID = d.Files_ID
		LEFT JOIN stats_querystrings i ON i.ID = d.Querystrings_ID
		LEFT JOIN stats_machines j ON c.Machines_ID=j.ID  
		LEFT JOIN stats_useragents k ON j.Last_Useragents_ID=k.ID
		LEFT JOIN stats_ips l ON c.Ips_ID=l.ID ORDER BY l.IPAddress",O_ARRAY);
		?>
		<table>
	<?php 	
	foreach($visits as $n=>$v){
		(preg_match('/Firefox/',$v['Useragent_Name'])?$browser='Firefox':
		(preg_match('/bot/i',$v['Useragent_Name'])?$browser='Bot':
		(preg_match('/Safari/',$v['Useragent_Name'])?$browser='Safari':
		(preg_match('/MSIE/',$v['Useragent_Name'])?$browser='Internet Explorer':
		(preg_match('/Chrome/',$v['Useragent_Name'])?$browser='Chrome':
		'Unknown')))));
		?>
		<thead>
			<tr>
				<th>
			<?php if ($v['IPAddress']!=$oldIP) {
			echo '<h1>'.$v['IPAddress'].'</h1><br />';
			echo $browser=='Bot'? 'Is a '.$browser.'<br />' : 'Using '.$browser.'<br />';
		?>
				<?php echo 'Session Started At '.$v['FirstViewTime'].'<br />';
				echo $v['LastViewTime']=='0000-00-00 00:00:00'? '': 'Session Ended At '.$v['LastViewTime']; 
				?>
				</th>
				<th>Viewed</th>
				<th>Time Of Viewing</th>
			</tr>
		</thead>
		<?php
		}
		?>
		<tbody>
		<tr>
			<td></td>
			<td><?php echo $v['PageViewed']; ?></td>
			<td><?php echo $v['PageEntryTime']; ?></td>
			<?php $oldIP=$v['IPAddress'];?>
		</tr>
		</tbody>
	<?php 
	}
	?>
	</table>
	<?php
	/* 
	Array
(
  [Visitors_ID] => 1
  [PagesViewed] => 1
  [FirstViewTime] => 2010-04-26 11:16:42
  [LastViewTime] => 0000-00-00 00:00:00
  [Contacts_ID] => 0
  [Ips_ID] => 1
  [PageViewed] => https://dev.jeanzinner.com/Templates/JAZ_v100.dwt.php?
  [PageEntryTime] => 2010-04-26 11:16:42
  [Useragent_Name] => Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3 (.NET CLR 3.5.30729)
  [IPAddress] => 66.90.129.2
)
*/
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