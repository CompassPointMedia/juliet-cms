<?php
$Templates_ID=1;
$pJCurrentContentRegion='topRegionNav';
if(!function_exists('pJ'))require($FUNCTION_ROOT.'/group_pJ_v100.php');


/* name=Menu version 2.5, horizontal; description=this goes one level down, got from Myen chant edgarden; */
/*
v 2.6 2013-12-09
* integrated the menu style with rounded corners I saw at Austin Swing Syndicate.org
* this came about so I could improve my console menus and their appearance, as well as in a non-juliet environment
	how do you get the link
	how would you minimally express a compiled array
	etc
	etc
	
	todo (see ASS)
	* there is a gap below horiz and above vertical
	* highlighting on sub sub menus is the parent ul and all li's - ugh
	* images not present
	* what about really long file names/links
	
v 2.5 2013-07-29
2013-09-23
* read the comment from same date in v2.1 - about active/inactive
* main goal of this = components talk to pages and modify the menu - any part of the menu.
* pulled over successful multi-leve menu from www.joseluissalon.com - non-jquery script, and css is pretty straightforward

	todo
	* test if loss of node = loss of link
	* tabs-out in adminMode - make this easy
	


2012-08-22
* pages named e.g. Prices/Order will be linked as /prices-order
2012-07-12 - version 2.1
	* see same date in query - removed where Category=Website Page; saying type=Object is the same thing.  I would like to sunset Category entirely
	* version 2.1 represents a major shift based on the premise that we are recursing nodes, not pages, and getting the primary page if present.  Even if a page is not present, the structure is still there.  Good example is a root menu item that is just an anchor for the 1st level fly-out.
	NOTE that normally there are two records in gen_nodes_hierarchy, a ParentNode -> ChildNode record, and a ChildNode -> PrimaryPage record.  In the case described above, the 2nd record is not needed.
2012-03-09
	*so I am putting an edit link to the generic component editor in place, at adminMode=ADMIN_MODE_DESIGNER, by a function pJ_call_edit()

todo
	multiple menus
	unique id's based on common sense
	css declared separately
	pages all work OK
2011-09-01
	* THE objective of this component is to have controls available to control itself, via a system/protocol that meshes in with juliet.
	* The goal of this *specific* component is to read a selected menu (default menu if not declared), and select a style and a vertical or horizontal orientation.
	* We start with some standardizations for a component, and I'm using OOP for the first time only for very rudimentary applications
		each component should have a unique handle; also a title and description.  This is used by Juliet in selection lists and etc.
	* there is also the concept of "bubble-up CSS" - i.e. this component hooking into the default css file (with acct.. at prefix) and declaring the css there by fwrite(); this is much cleaner than declaring css throughout.
	

*/
$handle='navMenu';
$tabVersion=3;


//pull parameters for this component file
if($Parameters=q("SELECT Parameters FROM gen_templates_blocks WHERE Templates_ID=$Templates_ID AND Name='$pJCurrentContentRegion'", O_VALUE)){
	$pJ['componentFiles'][$handle]=unserialize(base64_decode($Parameters));
	/* nodes include: forms; data; format.  forms is unused right now, and data[default] means "across all pages" and is the only part developed */
}
for($__i__=1; $__i__<=1; $__i__++){ //---------------- begin __i__ break loop ---------------

/*
2012-03-09: this is an example of precedence confusion; there are many different things happening - we need to extract key field data for site display but also we are passing this to a exe page which is loading different data- THINK!!! and improve on this
*/
//allow passage of a different Menus_ID by query - this could cause trouble
if(!$Menus_ID)$Menus_ID=pJ_getdata('Menus_ID',q("SELECT ID FROM gen_nodes WHERE Type='Group' LIMIT 1",O_VALUE));
$menuHideInactiveUsingChildObjects=pJ_getdata('menuHideInactiveUsingChildObjects','1');

$menuBgColor=			pJ_getdata('menuBgColor','#ccc');
$menuPaddingAround=		pJ_getdata('menuPaddingAround','7px 10px');
$menuFontFamily=		pJ_getdata('menuFontFamily');
$menuFontSize=			pJ_getdata('menuFontSize');
$menuTextColor=			pJ_getdata('menuTextColor');
$menuBlock=				pJ_getdata('menuBlock');

#default CSS

//messing with a working model here
$puroCSSDim1=0; //was -28px;


if(false){ ?><div style="display:none;"><style type="text/css"><?php } 
ob_start();?>
/********* navigation *********/
nav#site-navigation {float:right;}
#site-navigation .nav-menu > ul {margin:0; list-style:none; padding:10px 0 0 18px; display:table !important;}
#site-navigation .nav-menu > ul li {margin:0; padding:0; float:left; position:relative; z-index:99; display:table-cell; vertical-align:middle;}
#site-navigation .nav-menu > ul > li a {font-size:14px; color:#000; text-transform:uppercase; display:block; padding:10px 10px 5px 10px;}
#site-navigation .nav-menu > ul > li a:hover {color:#fff;}
#site-navigation .nav-menu > ul > li:first-child a {padding-left:0; display:none;}
#site-navigation .nav-menu > ul ul {margin:0; padding:0; padding-top:<?php echo $puroCSSDim1;?>px; width:180px; list-style:none; display:none; position:absolute; top:25px; left:10px;}
#site-navigation .nav-menu > ul ul li {width:180px; float:left; display:block !important; display:inline;}
#site-navigation .nav-menu > ul ul li a {font-size:12px;}
#site-navigation .nav-menu > ul a {
	padding:0; float:none !important; float:left; display:block; color:#fff; text-decoration:none; height:auto !important; height:1%;}
#site-navigation .nav-menu > ul li:hover li a, 
#site-navigation .nav-menu > ul li.iehover li a {
	float:none; background:#000; color:#fff; text-align:left; padding:4px;
	}

#site-navigation .nav-menu > ul li ul li ul {
	margin-top:<?php echo $puroCSSDim1;?>px; /* was -28px */
	}
#site-navigation .nav-menu > ul li:hover li a:hover, 
#site-navigation .nav-menu > ul li:hover li:hover a, 
#site-navigation .nav-menu > ul li.iehover li a:hover, 
#site-navigation .nav-menu > ul li.iehover li.iehover a {
	background:#333;
	}
#site-navigation .nav-menu > ul li:hover li:hover li a, 
#site-navigation .nav-menu > ul li.iehover li.iehover li a {
	background:#333;
	}
#site-navigation .nav-menu > ul li:hover li:hover li a:hover,
#site-navigation .nav-menu > ul li:hover li:hover li:hover a, 
#site-navigation .nav-menu > ul li.iehover li.iehover li a:hover, 
#site-navigation .nav-menu > ul li.iehover li.iehover li.iehover a {
	background:#454545;
	}
#site-navigation .nav-menu > ul li:hover li:hover li:hover li a, 
#site-navigation .nav-menu > ul li.iehover li.iehover li.iehover li a {
	background:#454545;
	}
#site-navigation .nav-menu > ul li:hover li:hover li:hover li a:hover, 
#site-navigation .nav-menu > ul li.iehover li.iehover li.iehover li a:hover {
	background:#666;
	}
#site-navigation .nav-menu > ul ul ul, 
#site-navigation .nav-menu > ul ul ul ul {
	display:none; position:absolute; top:0; left:180px;
	}
#site-navigation .nav-menu > ul li:hover ul ul, 
#site-navigation .nav-menu > ul li:hover ul ul ul, 
#site-navigation .nav-menu > ul li.iehover ul ul, 
#site-navigation .nav-menu > ul li.iehover ul ul ul {
	display:none;
	}
#site-navigation .nav-menu > ul li:hover ul, 
#site-navigation .nav-menu > ul ul li:hover ul, 
#site-navigation .nav-menu > ul ul ul li:hover ul, 
#site-navigation .nav-menu > ul li.iehover ul, 
#site-navigation .nav-menu > ul ul li.iehover ul, 
#site-navigation .nav-menu > ul ul ul li.iehover ul {
	display:block;
	}
<?php if(false){ ?></style></div><?php }
$menuDefaultCSS=trim(ob_get_contents());
ob_end_clean();
$menuCSS=pJ_getdata('menuCSS',$menuDefaultCSS);
$menuAdditionalPHP=pJ_getdata('menuAdditionalPHP');
$menuTheme=pJ_getdata('menuTheme','puroCSS');

$puroCSS=array(
	'navWrapBefore'=>'<nav id="site-navigation">
	<div class="nav-menu">',
	'navWrapAfter'=>'</div></nav>',
);
$triplestep=array(
	'navWrapBefore'=>'
<link rel="stylesheet" href="/Library/css/navmenu.menus-default.css">

<script src="/Library/js/jq/navmenu.warp.js"></script>
<script src="/Library/js/jq/navmenu.responsive.js"></script>
<script src="/Library/js/jq/navmenu.accordionmenu.js"></script>
<script src="/Library/js/jq/navmenu.dropdownmenu.js"></script>
<script src="/Library/js/jq/navmenu.template.js"></script>
	
	<div id="menubar" class="grid-block">
						<nav id="menu">',
	'navWrapAfter'=>'</nav></div>',
	'ulWrapBefore'=>array(
		2=>'<div style="display: none; overflow: hidden;" class="dropdown columns1"><div style="overflow: hidden;"><div><div class="dropdown-bg"><div><div class="width100 column">',
		
	),
	'ulClass'=>array(
		1=>'menu menu-dropdown',
		2=>'level2',
		3=>'level3',
	),
	'liClass'=>'level{level} item{id}',
	'aClass'=>'level{level}',
	'ulWrapAfter'=>array(
		2=>'</div></div></div></div></div></div>',
	),
	'aInsideBefore'=>'<span>',
	'aInsideAfter'=>'</span>',
);

$str='';
if($menuBgColor || $menuPaddingAround || $menuFontFamily || $menuFontSize){
	$str.='.julietMenu{'."\n";
	if($n=$menuBgColor)				$str.='background-color:'.$n.';'."\n";
	if($n=$menuPaddingAround)		$str.='padding:'.$n.';'."\n";
	if($n=$menuFontFamily)			$str.='font-family:'.$n.';'."\n";
	if($n=$menuFontSize)			$str.='font-size:'.$n.';'."\n";
	$str.='}'."\n";
}
if($n=$menuTextColor){
	$str.='.julietMenu a{'."\n";
	$str.='color:'.$n.';'."\n";
	$str.='}';
}

$pJLocalCSS[$handle]=trim($str)."\n".trim($menuCSS);

if($mode=='componentEditor'){
	if($Parameters=q("SELECT Parameters FROM gen_templates_blocks WHERE Templates_ID=$Templates_ID AND Name='$pJCurrentContentRegion'", O_VALUE)){
		$a=unserialize(base64_decode($Parameters));
	}else{
		$a=array();
	}
	!is_array($pJ['componentFiles'][$handle]) ? $pJ['componentFiles'][$handle]=array() : '';
	foreach($a as $n=>$v){
		$pJ['componentFiles'][$handle][$n]=$v;
	}
	if($submode=='priority' && $pages=q("SELECT 
		n.ID FROM gen_nodes n, gen_nodes_hierarchy h
		WHERE 
		n.ID=h.Nodes_ID AND h.GroupNodes_ID='".$Menus_ID."' AND
		n.Type='Node' /* ** I removed this 2012-10-08 - don't think I need it ** AND n.Category='Website Page' */ GROUP BY n.ID", O_ARRAY, C_MASTER)){
		$temp=time();

		//create a table
		q("CREATE TABLE temp_$temp SELECT n.ID, '1' AS Priority
		FROM gen_nodes n, gen_nodes_hierarchy h
		WHERE 
		n.ID=h.Nodes_ID AND h.GroupNodes_ID='".$Menus_ID."' AND
		n.Type='Node' /* AND n.Category='Website Page' */ GROUP BY n.ID", C_MASTER);
		
		q("ALTER TABLE temp_$temp CHANGE Priority Priority MEDIUMINT(4) UNSIGNED NOT NULL");
		
		//update 
		if($p=$pJ['componentFiles'][$handle]['data']['menu']['priority']){
			foreach($p as $ID=>$Priority)q("UPDATE temp_$temp SET Priority=$Priority WHERE ID=$ID");
		}else{
			$i=0;
			foreach(q("SELECT ID FROM temp_$temp", O_COL) as $ID){
				$i++;
				q("UPDATE temp_$temp SET Priority=$i WHERE ID=$ID");
			}
		}
		#prn(q("SELECT * FROM temp_$temp", O_COL_ASSOC));
		#prn($qr,1);

		//call function
		if(!function_exists('set_priority'))require($FUNCTION_ROOT.'/function_set_priority_v110.php');
		set_priority($id, $dir, $abs, array( 'priorityTable'=>'temp_'.$temp,'debug'=>true ));

		//update
		$pJ['componentFiles'][$handle]['data']['menu']['priority']=q("SELECT * FROM temp_$temp", O_COL_ASSOC);
	
		//delete table
		q("DROP TABLE temp_$temp");
		q("UPDATE gen_templates_blocks SET Parameters='".base64_encode(serialize($pJ['componentFiles'][$handle]))."' WHERE Templates_ID='$Templates_ID' AND Name='$pJCurrentContentRegion'");
		
		//reload the order
		$formNodeSub='loadPages';
		goto priorityJump;
	}
	//now integrate the form post
	$pJ['componentFiles'][$handle]['data'][$formNode]=stripslashes_deep($_POST[$formNode]);
	q("UPDATE gen_templates_blocks SET Parameters='".base64_encode(serialize($pJ['componentFiles'][$handle]))."' WHERE Templates_ID='$Templates_ID' AND Name='$pJCurrentContentRegion'");
	prn($qr);
	break; //------------ __i__ break loop ---------------
}else if($formNode=='menu' /* ok this is something many component files will contain */){
	?>
	<script language="javascript" type="text/javascript">
	var Menus_ID=<?php echo $Menus_ID?$Menus_ID:"''";?>;
	function loadPages(o){
		if(!o.value)return false;
		if(o.value=='{RBADDNEW}'){
			alert('not developed; just need to add callback string');
			return false;
			return ow('/console/rsc_menumanager.php?cbFunction=etc','l1_menus','700,700');
		}
		window.open('/_juliet_.editor.php?handle=<?php echo $handle;?>&location=<?php echo 'JULIET_COMPONENT_ROOT';?>&file=<?php echo end(explode('/',__FILE__));?>&formNode=<?php echo $formNode;?>&formNodeSub=loadPages&Menus_ID='+o.value, 'w2');
		g('loadPagesStatus').style.display='inline';
	}
	function move(d,id,event){

		if(typeof event == "undefined")event = window.event;
		window.open(qs({
		'additional':'submode=priority&Menus_ID='+Menus_ID+'&dir='+d+'&id='+id+'&abs='+(event.ctrlKey?1:0),
		'page':'/index_01_exe.php',
		}),'w2');
		return false;
	}
	</script>
	<p>
	Menu to use: 
	<select name="menu[Menus_ID]" id="Menus_ID" onchange="dChge(this);return loadPages(this);">
	<option value="">&lt;Select..&gt;</option>
	<?php
	if($a=q("SELECT ID, Name FROM gen_nodes WHERE Type='Group' ORDER BY Name", O_COL_ASSOC))
	foreach($a as $n=>$v){
		?><option value="<?php echo $n?>" <?php echo $Menus_ID==$n?'selected':''?>><?php echo h($v);?></option><?php
	}
	?>
	<option value="{RBADDNEW}">&lt;Add new menu..&gt;</option>
	</select> &nbsp; <span id="loadPagesStatus" style="display:none;"><img src="/images/i/ani/ani-fb-orange.gif" alt="loading.." width="16" height="11" /></span>
	</p>
	<?php
	//2012-05-19 - after priorities in a different block, process this
	priorityJump:
	?>

	<div id="menuPageList">
	<table width="100%" border="0" cellspacing="0" class="spacer">
	  <thead>
		<tr>
		  <th scope="col" class="tac" style="font-weight:400;">test</th>
		  <th scope="col" class="tac" style="font-weight:400;">down</th>
		  <th scope="col" class="tac" style="font-weight:400;">up</th>
		  <th scope="col" class="tac" style="font-weight:400;">show</th>
		  <th scope="col">Navigation</th>
		  <th scope="col">&nbsp;</th>
		  <th scope="col">Page</th>
		  <th scope="col">Title</th>
		</tr>
	  </thead>
	  <tbody>
		<?php
		/* 2012-07-12 this is before we transitioned from pages to nodes */
		$pages=q("SELECT 
			n.ID,
			n.Active,
			h.Rlx,
			n.Name AS NodeName,
			n.CreateDate,
			n.EditDate,
			m.Title,
			m.Description,
			m.Keywords,
			GREATEST(n.EditDate, m.EditDate) AS EditDate
			FROM gen_nodes n LEFT JOIN site_metatags m ON n.ID=m.Objects_ID, gen_nodes_hierarchy h
			WHERE 
			n.ID=h.Nodes_ID AND h.GroupNodes_ID='".$Menus_ID."' AND
			n.Type='Object' /* 2012-07-12: AND n.Category='Website Page' */ GROUP BY n.ID", O_ARRAY, C_MASTER);
		$nodes=q("SELECT
		h.Nodes_ID AS ID,
		h.Pages_ID,
		na.Active,
		'' AS Rlx,
		h.NameT4 AS NodeName,
		n.Name,
		n.CreateDate,
		n.EditDate,
		m.Title,
		m.Description,
		m.Keywords,
		GREATEST(n.EditDate, m.EditDate) AS EditDate
		FROM _v_gen_nodes_hierarchy_nav h 
		LEFT JOIN gen_nodes na ON h.Nodes_ID=na.ID
		LEFT JOIN gen_nodes n ON h.Pages_ID=n.ID 
		LEFT JOIN site_metatags m ON n.ID=m.Objects_ID 
		WHERE GroupNodes_ID='".$Menus_ID."'", O_ARRAY, C_MASTER);
		#prn($pages);
		#prn($nodes,1);
		
		if($nodes){
			if($a=$pJ['componentFiles']['navMenu']['data']['menu']['priority']){
				foreach($nodes as $n=>$v){
					$nodes[$n]['priority']=(($w=$a[$v['ID']]) ? $w : 1);
				}
				#prn($nodes,1);
				$nodes=subkey_sort($nodes,'priority');
			}
			$i=0;
			foreach($nodes as $node){
				$i++;
				if(!$havenav){
					$havenav=true;
					$nav=q("SELECT v.*, n.Name AS MenuName FROM _v_gen_nodes_hierarchy_nav v, gen_nodes n WHERE n.ID=v.GroupNodes_ID ORDER BY GroupNodes_ID", O_ARRAY_ASSOC);
				}
				//this is eventually a library with multiple installs even from outside people
				if($node['NodeName']=='{root_website_page}'){
					$visibleNodeName='Home Page';
					$linkNodeName='index';
				}else{
					$linkNodeName=$visibleNodeName=$node['Name'];
				}
				$selectedNavNodes=q("SELECT h1.ID FROM gen_nodes_hierarchy h1, gen_nodes_hierarchy h2 WHERE h1.Nodes_ID=h2.ParentNodes_ID AND h2.ParentNodes_ID='".$node['ID']."'", O_COL);
				?><tr id="r_<?php echo $node['ID'];?>" class="<?php echo !$node['Active']?'inactive':''?>">
		  		<td class="tac">&nbsp;</td>
				<td class="tac"><?php if(count($nodes)>$i){ ?><a href="#" onclick="return move(-1,<?php echo $node['ID'];?>,event);" title="move this item down"><?php } ?><img src="/images/i/arrows/blue_tri_desc.gif" width="14" height="14" alt="down" <?php if(count($nodes)==$i)echo 'style="opacity:.25;" onclick="alert(\'You cannot move this item down any further\');"';?> /><?php if(count($nodes)>$i){ ?></a><?php } ?></td>
				<td class="tac"><?php if($i>1){ ?><a href="#" onclick="return move(1,<?php echo $node['ID'];?>,event);" title="move this item up"><?php } ?><img src="/images/i/arrows/blue_tri_asc.gif" width="18" height="14" alt="up" <?php if($i==1)echo 'style="opacity:.25;" onclick="alert(\'You cannot move this item up any further\');"';?> /><?php if($i>1){ ?></a><?php } ?></td>
				  <?php
				if(!isset($activePages)){
				  	//$activePages=pJ_getdata('activePages');
					//this is a big-time HACK:
					$activePages=$pJ['componentFiles'][$handle]['data']['menu']['activePages'];
				}
				  ?>
				  <td class="tac">
				  <input name="menu[activePages][<?php echo $node['ID'];?>]" type="checkbox" id="active<?php echo $node['ID']?>" value="1" <?php echo /*pJ_getdata(array('field'=>'activePages','subKey'=>$node['ID']))*/$activePages[$node['ID']] || !count($activePages) ? 'checked' : '';?> onchange="dChge(this);" />				  </td>
				  <td>
				  <?php if($node['Pages_ID']){ ?>
				  <a href="/console/rsc_pagemanager_focus.php?Nodes_ID=<?php echo $node['Pages_ID'];?>" title="click to edit this page in console" onclick="return ow(this.href,'l1_pagemanager','700,700');">
				  <?php } ?>
					<?php
					if($nav)
					foreach($nav as $n=>$v){ 
						echo @in_array($n, $selectedNavNodes) ? h(
						 $v['NameT1'] . 
						($v['NameT1'] ? ' > ':'') . $v['NameT2'] . 
						($v['NameT2'] ? ' > ':'') . $v['NameT3'] . 
						($v['NameT3'] ? ' > ':'') . $v['NameT4']
						) : '';
					}
					?>
				  <?php if($node['Pages_ID']){ ?>
				  </a>
				  <?php } ?>				  </td>
				  <td <?php echo $node['Rlx']=='Secondary'?'class="tc" style="color:white; background-color:dimgray;" title="this page is a SECONDARY page on at least one menu item"':''?>><?php echo $node['Rlx']=='Secondary' ? 2 : '&nbsp;'?></td>
				  <td nowrap="nowrap"><a href="http://<?php echo $_SERVER['HTTP_HOST']?>/<?php echo str_replace(' ','-',strtolower($linkNodeName))?>" target="_blank" onclick="if(confirm('This will change location of the opening page. Continue?'))window.opener.location=this.href; return false;"><?php echo $visibleNodeName?></a></td>
				  <td><?php echo $node['Title']?></td>
				</tr><?php
			}
		}else{
			?>
			<tr>
				<td colspan="104" class="ghost">No pages listed for this menu</td>
			</tr>
			<?php
		}
		?>
	  </tbody>
	</table>
	</div>
	<?php

	if($formNodeSub=='loadPages'){
		?><script language="javascript" type="text/javascript">
		window.parent.g('menuPageList').innerHTML=document.getElementById('menuPageList').innerHTML;
		window.parent.g('loadPagesStatus').style.display='none';
		window.parent.Menus_ID=<?php echo $Menus_ID;?>;
		</script><?php
	}

	break; //------------ __i__ break loop ---------------
}else if($formNode=='layout'){
	ob_start();
	?><p>
	Background color: <input name="layout[menuBgColor]" type="text" id="menuBgColor" onchange="dChge(this);" value="<?php echo $menuBgColor;?>" size="9" />
	<br />
	Menu text color:  
	<input name="layout[menuTextColor]" type="text" id="menuTextColor" onchange="dChge(this);" value="<?php echo $menuTextColor;?>" size="9" />
	 <br />
	Padding around menu items: 
	<input name="layout[menuPaddingAround]" type="text" id="menuPaddingAround" onchange="dChge(this);" value="<?php echo $menuPaddingAround;?>" size="12" />
	<br />
	Menu font family: 
	<input name="layout[menuFontFamily]" type="text" id="menuFontFamily" onchange="dChge(this);" value="<?php echo $menuFontFamily;?>" />
	<br />
	Menu font size: 
	<input name="layout[menuFontSize]" type="text" id="menuFontSize" onchange="dChge(this);" value="<?php echo $menuFontSize;?>" size="9" />
	 <br />
	<br />
	Menu CSS:<br />
	<p class="gray">
	NOTE: As of menu 2.5 and above, the CSS for menus can be quite complex.  You may use other styling fields but in version 3.0 or better, "themes" will be used which will abstract one layer from the individual CSS.
	</p>
	<textarea name="layout[menuCSS]" cols="65" rows="8" id="menuCSS" onchange="dChge(this);" class="tabby"><?php echo h($menuCSS);?></textarea>
	<br />
	<br />
	Other block to store the menu item: <em class="gray">
	<input name="layout[menuBlock]" type="text" id="menuBlock" onchange="dChge(this);" value="<?php echo $menuBlock;?>" size="15" />
	(Optional)</em>
	</p>
	<?php
	get_contents_tabsection('styling');
	?><div>
	<input type="hidden" name="layout[menuHideInactiveUsingChildObjects]" value="0" />
	<label><input type="checkbox" name="layout[menuHideInactiveUsingChildObjects]"  id="layout[menuHideInactiveUsingChildObjects]" value="1" onchange="dChge(this);" <?php echo $menuHideInactiveUsingChildObjects || !isset($menuHideInactiveUsingChildObjects)?'checked':''?> /> Hide inactive nodes based on no active objects underneath</label><br />
	<p class="gray">(NOTE: (Technically [and currently] this will hide nodes that have no active direct children, even if the <u>node</u> record is not set to inactive)</p>
	
	Menu theme: <input name="layout[menuTheme]" type="text" id="menuTheme" onchange="dChge(this);" value="<?php echo $menuTheme;?>" size="9" />
	<br />

	</div><?php	
	get_contents_tabsection('Options');
	?>
	<h3>Custom PHP Coding</h3>
	<p class="gray">This will be eval'd once prior to the menu being calculated</p>
	<textarea rows="15" cols="65" class="tabby" name="layout[menuAdditionalPHP]" id="menuAdditionalPHP" onchange="dChge(this);"><?php echo h($menuAdditionalPHP);?></textarea>
	<?php
	
	get_contents_tabsection('advanced');
	
	tabs_enhanced(
		array(
			'Options'=>array(
				'label'=>'Options',
			),
			'styling'=>array(
				'label'=>'Styling',
			),
			'advanced'=>array(
				'label'=>'Advanced'
			),
		) 
	);
	
	break; //------------ __i__ break loop ---------------
}
if($menuBlock)ob_start();
if($navs=q("SELECT v.*, n.Name AS MenuName FROM _v_gen_nodes_hierarchy_nav v, gen_nodes n WHERE GroupNodes_ID=$Menus_ID AND n.ID=v.GroupNodes_ID AND (n.Active=8 OR n.Active IS NULL)", O_ARRAY_ASSOC)){
	if($menuAdditionalPHP){
		ob_start();
		eval($menuAdditionalPHP);
		$err=ob_get_contents();
		ob_end_clean();
	}
	
	//2013-07-24: better presentation of options - should make much more sense
	foreach($navs as $n=>$v){
		$start=(is_null($v['Pri3'])?4:(is_null($v['Pri2'])?3:(is_null($v['Pri1'])?2:1)));
		$j=0;
		for($i=$start; $i<=4; $i++){
			$j++;
			$navs[$n]['key'.$j]=$v['Pri'.$i];
		}
		for($i=$j+1; $i<=4; $i++){
			$navs[$n]['key'.$i]='';
		}
		$navs[$n]['key0']=$v['MenuName'];
		$navs[$n]['jat']=$j;
	}
	$navs=subkey_sort($navs,array('key0','key1','key2','key3','key4'));
	unset($navs2);
	foreach($navs as $n=>$v)$navs2[$v['ID']]=$v;
	$navs=$navs2;
	unset($navs2);

	if($layout=='table'){
		?><style type="text/css">
		.yat{
			border-collapse:collapse;
			}
		.yat td{
			border:1px solid #666;
			}
		</style><table class="yat">
		<?php
		echo '</div></div>';
		$i=0;
		foreach($navs as $v){
			$i++;
			if($i==1){
				?><thead><tr><?php
				foreach($v as $o=>$w){
					if($o=='SystemName' || preg_match('/key0|GroupNodes_ID|Active|jat|PageType|MenuName/',$o))continue;
					?><th><?php echo $o;?></th><?php
				}
				?></tr></thead><?php
			}
			?><tr><?php
			foreach($v as $o=>$w){
				if($o=='SystemName' || preg_match('/key0|GroupNodes_ID|Active|jat|PageType|MenuName/',$o))continue;
				?><td><?php echo $w?></td><?php
			}
			?></tr><?php
		}
		?>
		</table><?php
		exit;
	}
	function nav($Menus_ID,$id=NULL,$level=1,$options=array()){
		/*
		todo:
		get title and href
		options:
			value to <a> all non-page nodes as well
			
		*/
		extract($options);
		global $adminMode,$qr,$fl,$ln,$developerEmail,$fromHdrBugs;
		global $intersperseMenuOptions, $replaceExtendOption, $menuHideInactiveUsingChildObjects,$menuTheme;
		
		//2013-12-10
		@$theme=$GLOBALS[$menuTheme];
		@extract($theme);
		
		if($options['alternate_data']){
			$a=$options['alternate_data'];
			unset($options['alternate_data']);
		}else{
			$a=q("SELECT n.*, m.Title, m.Description FROM _v_gen_nodes_hierarchy_nav n LEFT JOIN site_metatags m ON n.Pages_ID=m.Objects_ID WHERE (n.Active=8 OR n.Active IS NULL) AND n.GroupNodes_ID=$Menus_ID AND n.ParentNodes_ID ".(is_null($id)?'IS NULL':'='.$id), O_ARRAY_ASSOC);
		}
		if(count($a)){
			if($fctn=$intersperseMenuOptions[is_null($id)?-255:$id]){
				/*
				2013-11-13: improved for handling e-commerce items as a menu
				this takes $a and removes nodes, renames nodes, deletes nodes, or adds nodes
				since nodes may represent pages which do not exist, it must also globalize the option for an alternate data source for the next call of nav();
				*/
				$a=$fctn($a,$Menus_ID,$id,$level,$options);
			}
			//2013-07-24: better presentation of options - should make much more sense
			foreach($a as $n=>$v){
				$start=(is_null($v['Pri3'])?4:(is_null($v['Pri2'])?3:(is_null($v['Pri1'])?2:1)));
				$j=0;
				for($i=$start; $i<=4; $i++){
					$j++;
					$a[$n]['key'.$j]=$v['Pri'.$i];
				}
				for($i=$j+1; $i<=4; $i++){
					$a[$n]['key'.$i]='';
				}
				$a[$n]['key0']=$v['MenuName'];
				$a[$n]['jat']=$j;
			}
			$a=subkey_sort($a,array('key0','key1','key2','key3','key4'));
			foreach($a as $n=>$v)$a2[$v['ID']]=$v;
			$a=$a2;

			if($adminMode)echo "\n";
			if($adminMode)echo str_repeat("\t",$level-1);
			if($adminMode)echo '   ';
			
			//theme
			if($level==1)echo $navWrapBefore;
			//ul wrap			
			echo str_replace('{level}',$level,str_replace('{id}',$id,is_array($ulWrapBefore) ? $ulWrapBefore[$level] : $ulWrapBefore));
			
			$class='nlevel'.$level;
			if($level>1)$class.=' children';
			$class.=' '.$id;
			if(is_array($ulClass)){
				if($ulClass[$level])$class.=' '.str_replace('{level}',$level,str_replace('{id}',$id,trim($ulClass[$level])));
			}else if($ulClass){
				$class.=' '.str_replace('{level}',$level,str_replace('{id}',$id,trim($ulClass)));
			}
			echo "\n";
			?><ul class="<?php echo $class;?>"><?php echo "\n";
			foreach($a as $n=>$v){
				if($adminMode)echo "\n";
				if($adminMode)echo str_repeat("\t",$level);
				if($fctn=$replaceExtendOption[$v['ID']]){
					$fctn($a,$n,$Menus_ID,$id,$level,$options);
				}else{
					$key=md5(rand(1,1000000));
					$key2=md5(rand(1,1000000));
					$id=($v['Pages_ID']?'n'.$v['Pages_ID']:'u'.$v['ID']);
					ob_start();//ob_h, how I love this function..
					echo "\t";
					?><li id="<?php echo $id;?>" <?php echo $key;?>><?php

					//handle link
					if(strlen(trim($v['URL']))){ //added 2013-12-10
						$href=$v['URL'];
					}else if($v['SystemName']=='{root_website_page}'){
						$href='/';
					}else if($href=$v['href']){
						//OK
					}else if($n=$v['PageName']){
						$href='/'.strtolower(str_replace(' ','-',$n));
					}else{
						$href='#';
					}
					$title=($v['Title'] && !$menuSuppressTitles ? ' title="'.h($v['Title']).'"':'');
					if(is_array($aClass)){
						$class=str_replace('{level}',$level,str_replace('{id}',$id,trim($aClass[$level])));
					}else{
						$class=str_replace('{level}',$level,str_replace('{id}',$id,trim($aClass)));
					}
					if($class)$class=' class="'.$class.$key2.'"';
					
					?><a href="<?php echo $href;?>"<?php if($href=='#' || !$href)echo ' style="cursor:default;"';?><?php echo $title; echo $class;?>><?php
					
					//theme
					echo $aInsideBefore;
					
					if($v['PageName']){
						echo $v['PageName'];
					}else{
						echo $v['NameT4'];
					}
					
					//theme
					echo $aInsideAfter;
	
					?></a><?php
					
					//modify options if needed
					$l=$level+1;
					$parent=nav($Menus_ID,$v['Nodes_ID'],$l,$options);
					?></li><?php
					$out=ob_get_contents();
					ob_end_clean();
					
					if(is_array($liClass)){
						if($liClass[$level])$class=str_replace('{level}',$level,str_replace('{id}',$id,trim($liClass[$level])));
					}else{
						$class=str_replace('{level}',$level,str_replace('{id}',$id,trim($liClass)));
					}
					if($parent)$class.=trim(' parent');
					//modify <li> key
					$out=str_replace($key,'class="'.$class.'"',$out);
					//modify <a> key
					$out=str_replace($key2,$parent?' parent':'',$out);
					$out=str_replace('class=" parent"','class="parent"',$out);
					$out=str_replace('class=""','',$out);
					echo $out;
				}
			}
			if($adminMode)echo "\n";
			if($adminMode)echo str_repeat("\t",$level-1);
			if($adminMode)echo '  ';
			?></ul><?php echo "\n";
			
			//ul wrap end			
			echo is_array($ulWrapAfter) ? $ulWrapAfter[$level] : $ulWrapAfter;

			//theme
			if($level==1)echo $navWrapAfter;

			return true; //parent
		}
	}
	nav($Menus_ID,NULL,1);
    $ffl = __FILE__;
    $ffl = explode('/',$ffl);
    $ffl = end($ffl);
	echo pJ_call_edit(array(
		'formNode'=>'menu',
		'level'=>ADMIN_MODE_DESIGNER,
		'location'=>'JULIET_COMPONENT_ROOT',
		'file'=>$ffl,
		'label'=>'edit menu',
	));
	echo pJ_call_edit(array(
		'formNode'=>'layout',
		'level'=>ADMIN_MODE_DESIGNER,
		'location'=>'JULIET_COMPONENT_ROOT',
		'file'=>$ffl,
		'label'=>'edit layout',
	));
	echo "\n";


}


if($menuBlock){
	$$menuBlock=ob_get_contents();
	ob_end_clean();
}

}//---------------- end __i__ break loop ---------------

?>