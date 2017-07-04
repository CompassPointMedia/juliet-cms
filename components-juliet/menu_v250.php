<?php
/* name=Menu version 2.5, horizontal; description=this goes one level down, got from Myen chant edgarden; */
/*
v 2.5 2013-07-29
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

$menuBgColor=			pJ_getdata('menuBgColor','#ccc');
$menuPaddingAround=		pJ_getdata('menuPaddingAround','7px 10px');
$menuFontFamily=		pJ_getdata('menuFontFamily');
$menuFontSize=			pJ_getdata('menuFontSize');
$menuTextColor=			pJ_getdata('menuTextColor');
$menuBlock=				pJ_getdata('menuBlock');

#default CSS
if(false){ ?><div style="display:none;"><style type="text/css"><?php } 
ob_start();?>
	.julietMenu li{
		display:inline;
		margin-right:20px;
		}
	.julietMenu li a{
		color:white;
		}
	.julietMenu li a:hover{
		text-decoration:none;
		}
	.buttonA{
		background-color:inherit;
		border:none;
		cursor:pointer;
		padding:1px 10px 1px 25px;
		background-image:url("/images/i/navbullet02.png");
		background-position:10px 5px;
		background-repeat:no-repeat;
		color:#A8968C;
		}
	#link2{
		position:relative;
		}
	.submenu{
		z-index:1000;
		background-color:#786962;
		color:white;
		-moz-opacity:.90;
		filter:alpha(opacity=90);
		opacity:.90;
		position:absolute;
		left:0px;
		top:25px;
		width:150px;
		text-align:left;
		font-weight:400;
		}
	.submenu div{
		cursor:pointer;
		padding:3px 12px;
		}
	.submenu .on{
		background-color:#133613; /* was gold */
		}
	.submenu .off{
		background-color:none;
		}
	.julietMenu li{
		position:relative;
		}
	#mainRegionIntro .submenu a{
		color:cornsilk;
		}
<?php if(false){ ?></style></div><?php }
$menuDefaultCSS=trim(ob_get_contents());
ob_end_clean();
$menuAdditionalCSS=pJ_getdata('menuAdditionalCSS',$menuDefaultCSS);
$menuAdditionalPHP=pJ_getdata('menuAdditionalPHP');
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

$pJLocalCSS[$handle]=trim($str)."\n".trim($menuAdditionalCSS);

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
	Additional CSS:<br />
	<textarea name="layout[menuAdditionalCSS]" cols="65" rows="8" id="menuAdditionalCSS" onchange="dChge(this);" class="tabby"><?php echo h($menuAdditionalCSS);?></textarea>
	<br />
	<br />
	Other block to store the menu item: <em class="gray">
	<input name="layout[menuBlock]" type="text" id="menuBlock" onchange="dChge(this);" value="<?php echo $menuBlock;?>" size="15" />
	(Optional)</em>
	</p>
	<?php
	get_contents_tabsection('styling');
	
	?>
	<h3>Custom PHP Coding</h3>
	<p class="gray">This will be eval'd once prior to the menu being calculated</p>
	<textarea rows="15" cols="65" class="tabby" name="layout[menuAdditionalPHP]" id="menuAdditionalPHP" onchange="dChge(this);"><?php echo h($menuAdditionalPHP);?></textarea>
	<?php
	
	get_contents_tabsection('advanced');
	
	tabs_enhanced(
		array(
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


if($navs=q("SELECT v.*, n.Name AS MenuName FROM _v_gen_nodes_hierarchy_nav v, gen_nodes n WHERE GroupNodes_ID=$Menus_ID AND n.ID=v.GroupNodes_ID", O_ARRAY_ASSOC)){

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
	?>
	<style type="text/css">
	/********* navigation *********/
	nav#site-navigation {float:right;}
	#site-navigation .nav-menu > ul {margin:0; list-style:none; padding:10px 0 0 18px; display:table !important;}
	#site-navigation .nav-menu > ul li {margin:0; padding:0; float:left; position:relative; z-index:99; display:table-cell; vertical-align:middle;}
	#site-navigation .nav-menu > ul > li a {font-size:14px; color:#000; text-transform:uppercase; display:block; padding:10px 10px 5px 10px;}
	#site-navigation .nav-menu > ul > li a:hover {color:#fff;}
	#site-navigation .nav-menu > ul > li:first-child a {padding-left:0; display:none;}
	#site-navigation .nav-menu > ul ul {margin:0; padding:0; padding-top:28px; width:180px; list-style:none; display:none; position:absolute; top:25px; left:10px;}
	#site-navigation .nav-menu > ul ul li {width:180px; float:left; display:block !important; display:inline;}
	#site-navigation .nav-menu > ul ul li a {font-size:12px;}
	#site-navigation .nav-menu > ul a {
		padding:0; float:none !important; float:left; display:block; color:#fff; text-decoration:none; height:auto !important; height:1%;}
	#site-navigation .nav-menu > ul li:hover li a, 
	#site-navigation .nav-menu > ul li.iehover li a {
		float:none; background:#000; color:#fff; text-align:left; padding:4px;
		}
	#site-navigation .nav-menu > ul li ul li ul {
		margin-top:-28px;
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
	</style>
	<nav id="site-navigation">
	<div class="nav-menu"><?php	

	function nav($Menus_ID,$id=NULL,$level=1,$options=array()){
		/*
		todo:
		get title and href
		options:
			value to <a> all non-page nodes as well
			
		*/
		extract($options);
		global $adminMode,$qr,$fl,$ln,$developerEmail,$fromHdrBugs;
		global $intersperseMenuOptions, $replaceExtendOption;
		
		if($a=q("SELECT n.*, m.Title, m.Description FROM _v_gen_nodes_hierarchy_nav n LEFT JOIN site_metatags m ON n.Pages_ID=m.Objects_ID WHERE n.GroupNodes_ID=$Menus_ID AND n.ParentNodes_ID ".(is_null($id)?'IS NULL':'='.$id), O_ARRAY_ASSOC)){

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
			
			if($fctn=$intersperseMenuOptions[is_null($id)?-255:$id]){
				//this is a function name and takes the set $a and either removes options from it, renames options, deletes options, or both
				$a=$fctn($a,$Menus_ID,$id,$level,$options);
			}
			
			if($adminMode)echo "\n";
			if($adminMode)echo str_repeat("\t",$level-1);
			if($adminMode)echo '   ';
			?><ul class="nlevel<?php echo $level;?><?php if($level>1)echo ' children';?>"><?php
			foreach($a as $n=>$v){
				if($adminMode)echo "\n";
				if($adminMode)echo str_repeat("\t",$level);
				
				if($fctn=$replaceExtendOption[$v['ID']]){
					$fctn($a,$n,$Menus_ID,$id,$level,$options);
				}else{
					?><li id="<?php echo $v['Pages_ID']?'n'.$v['Pages_ID']:'u'.$v['ID'];?>"><?php
					
					//handle link
					if($v['SystemName']=='{root_website_page}'){
						$href='/';
					}else if($href=$v['href']){
						//OK
					}else if($n=$v['PageName']){
						$href='/'.strtolower(str_replace(' ','-',$n));
					}else{
						$href='#';
					}
					
					?><a href="<?php echo $href;?>"<?php if($v['Title'])echo ' title="'.h($v['Title']).'"';?>><?php
	
					if($v['PageName']){
						echo $v['PageName'];
					}else{
						echo $v['NameT4'];
					}
	
					?></a><?php
					
					//modify options if needed
					$l=$level+1;
					nav($Menus_ID,$v['Nodes_ID'],$l,$options);
					?></li><?php
				}
			}
			if($adminMode)echo "\n";
			if($adminMode)echo str_repeat("\t",$level-1);
			if($adminMode)echo '  ';
			?></ul><?php
		}
	}
	nav($Menus_ID,NULL,1);
	
	?></div></nav>
	<script language="javascript" type="text/javascript">
	/**
	 * navigation.js
	 *
	 * Handles toggling the navigation menu for small screens.
	 * /
	( function() {
		var nav = document.getElementById( 'site-navigation' ), button, menu;
		if ( ! nav )
			return;
		button = nav.getElementsByTagName( 'h3' )[0];
		menu   = nav.getElementsByTagName( 'ul' )[0];
		if ( ! button )
			return;
	
		// Hide button if menu is missing or empty.
		if ( ! menu || ! menu.childNodes.length ) {
			button.style.display = 'none';
			return;
		}
	
		button.onclick = function() {
			if ( -1 == menu.className.indexOf( 'nav-menu' ) )
				menu.className = 'nav-menu';
	
			if ( -1 != button.className.indexOf( 'toggled-on' ) ) {
				button.className = button.className.replace( ' toggled-on', '' );
				menu.className = menu.className.replace( ' toggled-on', '' );
			} else {
				button.className += ' toggled-on';
				menu.className += ' toggled-on';
			}
		};
	} )();
	&*/
	</script><?php
	echo pJ_call_edit(array(
		'formNode'=>'menu',
		'level'=>ADMIN_MODE_DESIGNER,
		'location'=>'JULIET_COMPONENT_ROOT',
		'file'=>end(explode('/',__FILE__)),
		'label'=>'edit menu',
	));
	echo pJ_call_edit(array(
		'formNode'=>'layout',
		'level'=>ADMIN_MODE_DESIGNER,
		'location'=>'JULIET_COMPONENT_ROOT',
		'file'=>end(explode('/',__FILE__)),
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