<?php 
/*
NOTES this page:
2011-04-05
----------
have a toggle for active/inactive and also for updating the menu node (relocating to a different place).  we're coming along just fine here :)
Ospreys was not showing title and description from site_metatags when I viewed it; fix this
the gen_nodes_hierarchy.Rlx field was used once and is already improper. finish the other two radio options in insertMode on pagemanager_focus to handle, overall, the following:
	1. nav menu nodes need indexing
	2. pages also need indexing for multiple menus
	3. a page needs to be indicated as the primary page for this node, and interlock the others
work the menu coding - this should start to get interesting and powerful :)

*WORK THROUGH THE CONCEPT AS IN A NAV MENU OF WHETHER OR NOT A HIGHER-LEVEL NODE HAS A LINK OR NOT - SIMILAR TO STM*

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
<title><?php echo h($adminCompany);?> Page Manager</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" type="text/css" href="rbrfm_admin.css" />
<link rel="stylesheet" type="text/css" href="/Library/css/DHTML/dynamic_04_i1.css" />
<link rel="stylesheet" type="text/css" href="/Library/css/DHTML/data_04_i1.css" />
<style>
/** CSS Declarations for this page **/
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

<?php 
//js var user settings
js_userSettings();
?>
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
/*
todo
----

*/

?>

<h1>Page Manager</h1>
<style type="text/css">
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
<table width="100%" border="0" cellspacing="0" class="spacer">
  <thead>
    <tr>
      <th scope="col">&nbsp;</th>
      <th scope="col">Navigation</th>
      <th scope="col">&nbsp;</th>
      <th scope="col">Act.</th>
      <th scope="col">Page</th>
      <th scope="col">Title</th>
      <th scope="col">Description</th>
      <th scope="col">Content</th>
      <th scope="col">Stats</th>
    </tr>
  </thead>
  <tfoot>
    <tr>
      <td colspan="101"><a href="rsc_pagemanager_focus.php" title="view the editor form for this page" onclick="return ow(this.href,'l1_pagemanager','701,702');">Add page..</a> </td>
    </tr>
  </tfoot>
  <tbody>
    <?php
	if($pages=q("SELECT 
		n.ID,
		h.Rlx,
		n.Active,
		n.Name AS NodeName,
		n.CreateDate,
		n.EditDate,
		m.Title,
		m.Description,
		m.Keywords,
		GREATEST(n.EditDate, m.EditDate) AS EditDate
		FROM gen_nodes n LEFT JOIN site_metatags m ON n.ID=m.Objects_ID
		LEFT JOIN gen_nodes_hierarchy h ON n.ID=h.Nodes_ID
		WHERE 
		n.Type='Object' AND n.Category='Website Page' GROUP BY n.ID", O_ARRAY, C_MASTER)){
		foreach($pages as $page){
			if(!$havenav){
				$havenav=true;
				$nav=q("SELECT v.*, n.Name AS MenuName FROM _v_gen_nodes_hierarchy_nav v, gen_nodes n WHERE n.ID=v.GroupNodes_ID ORDER BY GroupNodes_ID", O_ARRAY_ASSOC);
			}
			//this is eventually a library with multiple installs even from outside people
			if($page['NodeName']=='{root_website_page}'){
				$visibleNodeName='Home Page';
				$linkNodeName='index';
			}else{
				$linkNodeName=$visibleNodeName=$page['NodeName'];
			}
			$selectedNavNodes=q("SELECT h1.ID, h1.ID FROM gen_nodes_hierarchy h1, gen_nodes_hierarchy h2 WHERE h1.Nodes_ID=h2.ParentNodes_ID AND h2.Nodes_ID='".$page['ID']."'", O_COL_ASSOC);
			?><tr id="r_<?php echo $page['ID'];?>" class="<?php echo !$page['Active']?'inactive':''?>">
			  <td nowrap="nowrap"><?php
			  //prn($qr);
			  //prn($selectedNavNodes);
			  ?>
			  [<a href="resources/bais_01_exe.php?mode=deletePage&Nodes_ID=<?php echo $page['ID'];?>" onclick="if(!confirm('This will PERMANENTLY DESTROY this page.  Continue?'))return false;" target="w2" title="delete this page">delete</a>]
			   &nbsp;&nbsp;			  
			  [<a href="rsc_pagemanager_focus.php?Nodes_ID=<?php echo $page['ID'];?>" title="view the editor form for this page" onclick="return ow(this.href,'l1_pagemanager','701,702');">edit</a>]			  </td>
			  <td>
			  <?php 
			  ?>
			  <select name="pageNav[<?php echo $ID?>]" id="pageNav[<?php echo $ID?>]" onchange="dChge(this);" style="max-width:225px;">
				<option value="">(none)</option>
				<!-- <option value="{RBADDNEW}">&lt;Add new..&gt;</option> -->
				<?php
				/* query is fairly complex here */
				$i=0;
				if($nav)
				foreach($nav as $n=>$v){ 
					$i++;
					if($v['MenuName']!==$buffer){
						if($i>1)echo '</optgroup>';
						?><optgroup label="<?php echo $v['MenuName']?>"><?php
						$buffer=$v['MenuName'];
					}
					?><option value="<?php echo $n?>" <?php 
					echo $selectedNavNodes[$n] ? 'selected' : '';
					?>><?php echo h(
					$v['NameT1'] . 
					($v['NameT1'] ? ' > ':'') . $v['NameT2'] . 
					($v['NameT2'] ? ' > ':'') . $v['NameT3'] . 
					($v['NameT3'] ? ' > ':'') . $v['NameT4']
					);?></option><?php
				}
				?>
				</optgroup>
			  </select></td>
			  <td <?php echo $page['Rlx']=='Secondary'?'class="tc" style="color:white; background-color:dimgray;" title="this page is a SECONDARY page on at least one menu item"':''?>><?php echo $page['Rlx']=='Secondary' ? 2 : '&nbsp;'?></td>
			  <td><input name="Active[<?php echo $n?>]" type="checkbox" id="Active[<?php echo $n?>]" value="1" <?php if($page['Active'])echo 'checked';?> /></td>
			  <td nowrap="nowrap"><a href="http://<?php echo $HTTP_HOST?>/<?php echo str_replace(' ','-',strtolower($linkNodeName))?>" target="_blank"><?php echo $visibleNodeName?></a></td>
			  <td><?php echo $page['Title']?></td>
			  <td><?php 
			  $wordCount=15;
			  $a=explode(' ',$page['Description']);
			  for($i=0;$i<$wordCount;$i++)echo ($i>0?' ':'').$a[$i];
			  if(count($a)>$wordCount)echo '...';
			  ?></td>
			  <td>&nbsp;</td>
			  <td>&nbsp;</td>
			</tr><?php
		}
	}else{
		?>
		<tr>
			<td colspan="101" class="ghost">No pages listed in the page manger.  Click Add Page below to create your first page</td>
		</tr>
		<?php
	}
	?>
  </tbody>
</table>

<!-- InstanceEndEditable -->
	<div class="cbsm"> </div>
	</div>
	<div id="footer">
	<!-- InstanceBeginEditable name="footer" --><!-- #BeginLibraryItem "/Library/rbrfm_footer.lbi" -->&copy;2008-<?php echo date('Y');?> RelateBase Services Inc. - 
<a href="/" target="_blank" title="View index page of your website">view site</a> | 
<a href="http://www.compasspoint-sw.com/mediawiki/index.php?title=RelateBase_Ecommerce_Console:RBRFM:Public_Documentation" target="helpme">WIKI</a><!-- #EndLibraryItem -->

<!-- InstanceEndEditable -->
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