<?php
/* name=Menu version 2, horizontal; description=this goes one level down, got from Myen chant edgarden; */
/*


2012-03-09

so I am putting an edit link to the generic component editor in place, at adminMode=ADMIN_MODE_DESIGNER, by a function pJ_call_edit()

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

//pull parameters for this component file
if($Parameters=q("SELECT Parameters FROM gen_templates_blocks WHERE Templates_ID=$Templates_ID AND Name='$pJCurrentContentRegion'", O_VALUE)){
    $pJ['componentFiles'][$handle]=unserialize(base64_decode($Parameters));

    /* nodes include: forms; data; format.  forms is unused right now, and data[default] means "across all pages" and is the only part developed */
}
for($__i__=1; $__i__<=1; $__i__++){ //---------------- begin __i__ break loop ---------------

    /*
    2012-03-09: this is an example of precedence confusion; there are many different things happening - we need to extract key field data for site display but also we are passing this to a exe page which is loading different data- THINK!!! and improve on this
    */
    if(!$Menus_ID)$Menus_ID=pJ_getdata('Menus_ID');

    $menuBgColor=pJ_getdata('menuBgColor','#ccc');
    $menuPaddingAround=pJ_getdata('menuPaddingAround','7px 10px');
    #default CSS
    if(false){ ?><div style="display:none;"><style type="text/css"><?php }
    ob_start();
    ?>
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
    $str='';
    if($n=$menuBgColor)				$str.='.julietMenu{ background-color:'.$n.'; }'."\n";
    if($n=$menuPaddingAround)		$str.='.julietMenu{ padding:'.$n.'; }'."\n";

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
            n.Type='Object' AND n.Category='Website Page' GROUP BY n.ID", O_ARRAY, C_MASTER)){
            $temp=time();

            //create a table
            q("CREATE TABLE temp_$temp SELECT n.ID, '1' AS Priority
            FROM gen_nodes n LEFT JOIN site_metatags m ON n.ID=m.Objects_ID, gen_nodes_hierarchy h
            WHERE 
            n.ID=h.Nodes_ID AND h.GroupNodes_ID='".$Menus_ID."' AND
            n.Type='Object' AND n.Category='Website Page' GROUP BY n.ID", C_MASTER);

            q("ALTER TABLE temp_$temp CHANGE Priority Priority MEDIUMINT(4) UNSIGNED NOT NULL");

            #prn(q("SELECT * FROM temp_$temp", O_ARRAY));
            prn($qr);

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
        //------------ __i__ break loop ---------------
        break;

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
					'page':'/gf5/console/resources/bais_01_exe.php',
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
                if($pages=q("SELECT 
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
                    n.Type='Object' AND n.Category='Website Page' GROUP BY n.ID", O_ARRAY, C_MASTER)){
                    if($a=$pJ['componentFiles']['navMenu']['data']['menu']['priority']){
                        foreach($pages as $n=>$v){
                            $pages[$n]['priority']=(($w=$a[$v['ID']]) ? $w : 1);
                        }
                        $pages=subkey_sort($pages,'priority');
                    }
                    $i=0;
                    foreach($pages as $page){
                        $i++;
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
                        $selectedNavNodes=q("SELECT h1.ID FROM gen_nodes_hierarchy h1, gen_nodes_hierarchy h2 WHERE h1.Nodes_ID=h2.ParentNodes_ID AND h2.Nodes_ID='".$page['ID']."'", O_COL);
                        ?><tr id="r_<?php echo $page['ID'];?>" class="<?php echo !$page['Active']?'inactive':''?>">
                        <td class="tac"><?php if(count($pages)>$i){ ?><a href="#" onclick="return move(-1,<?php echo $page['ID'];?>,event);" title="move this item down"><?php } ?><img src="/images/i/arrows/blue_tri_desc.gif" width="19" height="14" alt="down" <?php if(count($pages)==$i)echo 'style="opacity:.25;" onclick="alert(\'You cannot move this item down any further\');"';?> /><?php if(count($pages)>$i){ ?></a><?php } ?></td>
                        <td class="tac"><?php if($i>1){ ?><a href="#" onclick="return move(1,<?php echo $page['ID'];?>,event);" title="move this item up"><?php } ?><img src="/images/i/arrows/blue_tri_asc.gif" width="18" height="14" alt="up" <?php if($i==1)echo 'style="opacity:.25;" onclick="alert(\'You cannot move this item up any further\');"';?> /><?php if($i>1){ ?></a><?php } ?></td>
                        <?php
                        if(!isset($activePages)){
                            //$activePages=pJ_getdata('activePages');
                            //this is a big-time HACK:
                            $activePages=$pJ['componentFiles'][$handle]['data']['menu']['activePages'];
                        }
                        ?>
                        <td class="tac">
                            <input name="menu[activePages][<?php echo $page['ID'];?>]" type="checkbox" id="active<?php echo $page['ID']?>" value="1" <?php echo /*pJ_getdata(array('field'=>'activePages','subKey'=>$page['ID']))*/$activePages[$page['ID']] || !count($activePages) ? 'checked' : '';?> onchange="dChge(this);" />				  </td>
                        <td>
                            <a href="/console/rsc_pagemanager_focus.php?Nodes_ID=<?php echo $page['ID'];?>" title="click to edit this page in console" onclick="return ow(this.href,'l1_pagemanager','700,700');">
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
                                ?></a>				  </td>
                        <td <?php echo $page['Rlx']=='Secondary'?'class="tc" style="color:white; background-color:dimgray;" title="this page is a SECONDARY page on at least one menu item"':''?>><?php echo $page['Rlx']=='Secondary' ? 2 : '&nbsp;'?></td>
                        <td nowrap="nowrap"><a href="http://<?php echo $_SERVER['HTTP_HOST']?>/<?php echo str_replace(' ','-',strtolower($linkNodeName))?>" target="_blank" onclick="if(confirm('This will leave this page. Continue?'))window.opener.location=this.href; return false;"><?php echo $visibleNodeName?></a></td>
                        <td><?php echo $page['Title']?></td>
                        </tr><?php
                    }
                }else{
                    ?>
                    <tr>
                        <td colspan="103" class="ghost">No pages listed for this menu</td>
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
        //------------ __i__ break loop ---------------
        break;

    }else if($formNode=='layout'){
        ?>
        <p>
            Background color: <input name="layout[menuBgColor]" type="text" id="menuBgColor" onchange="dChge(this);" value="<?php echo $menuBgColor;?>" size="9" />
            <br />
            Padding around menu items:
            <input name="layout[menuPaddingAround]" type="text" id="menuPaddingAround" onchange="dChge(this);" value="<?php echo $menuPaddingAround;?>" size="12" />
            <br />
            Additional CSS:<br />
            <textarea name="layout[menuAdditionalCSS]" cols="65" rows="8" id="menuAdditionalCSS" onchange="dChge(this);"><?php echo h($menuAdditionalCSS);?></textarea>
        </p>
        <?php
        //------------ __i__ break loop ---------------
        break;

    }

    if(!$menuDef)
        $menuDef=array(
            array(
                'settings'=>array(
                    'navID'=>q("SELECT * FROM gen_nodes WHERE Type='Group' AND Category='Navigation Menu' ORDER BY IF(ID='$Menus_ID',1,2)", O_VALUE),
                    'displayMethod'=>'image',
                    'maxLevel'=>1,
                ),
            ),
        );


    ?>
    <script language="javascript" type="text/javascript">
		var killmenu={};
		function menu(n){
			g(n).style.visibility='visible';
			setTimeout('hideMenu(\''+n+'\')',500);
		}
		function hideMenu(n){
			killmenu[n] ? g(n).style.visibility='hidden' : setTimeout('hideMenu(\''+n+'\')',500);
		}
		function hl(o,s){
			o.className=(s?'on':'off');
		}
    </script>
    <?php
    foreach($menuDef as $n=>$v){
        //this is a menu - and we can do multiple
        $settings=$v['settings'];
        if(!($a=q("SELECT n.*, m.Title FROM _v_gen_nodes_hierarchy_nav n LEFT JOIN site_metatags m ON n.Pages_ID=m.Objects_ID WHERE n.GroupNodes_ID='".$settings['navID']."'",O_ARRAY)))continue;
        unset($nav);
        foreach($a as $v){
            $idx=(is_null($v['Pri1']) ? (is_null($v['Pri2']) ? (is_null($v['Pri3']) ? 1 : 2) : 3) : 4);
            $nav[$idx][$v['ParentNodes_ID'] ? $v['ParentNodes_ID'] : 'root'][$v['Nodes_ID']]=array(
                'id'=>$v['Nodes_ID'],
                'active'=>$v['Active'],
                'name'=>$v['NameT4'],
                'priority'=>$v['Pri4'],
                'Pages_ID'=>$v['Pages_ID'],
                'pagename'=>$v['PageName'],
                'pagetype'=>$v['PageType'],
                'systemname'=>$v['SystemName'],
                'title'=>$v['Title'],
                'children'=>q("SELECT n.*, m.Title FROM _v_gen_nodes_hierarchy_nav n LEFT JOIN site_metatags m ON n.Pages_ID=m.Objects_ID WHERE n.GroupNodes_ID='".$settings['navID']."' AND ParentNodes_ID='".$v['Nodes_ID']."'", O_ARRAY),
            );
        }
        ?>
        <span id="navMenu<?php echo $settings['navID']?>" class="navMenu"><ul class="julietMenu"><?php
            echo "\n";
            // ------------- create_menu_nav ----------------
            if(!function_exists('create_menu_nav')){
                function create_menu_nav($nav, $level=1, $node){
                    global $pJ, $settings, $acct;
                    $cf=$pJ['componentFiles']['navMenu']['data'];

                    if(!$nav[$level][$node])return;

                    if($cf['menu']['priority']){
                        foreach($nav[$level][$node] as $n=>$v){
                            $nav[$level][$node][$n]['priority']=(($w=$cf['menu']['priority'][$v['Pages_ID']]) ? $w : 1);
                        }
                        $nav[$level][$node]=subkey_sort($nav[$level][$node],'priority');
                    }
                    foreach($nav[$level][$node] as $parentNode=>$v){
                        if(!$v['active'])continue;
                        $cognate=($v['systemname']=='{root_website_page}' ? 'home' : preg_replace('/[^-a-z]*/','',strtolower($v['pagename'])));
                        $href=($v['systemname']=='{root_website_page}' ? '' : str_replace(' ','-',strtolower($v['pagename'])));

                        if(isset($cf['menu']['activePages']) && !$cf['menu']['activePages'][$v['Pages_ID']])continue;

                        ?><li><?php
                        echo "\n";
                    if($v['Pages_ID']){
                        ?><a href="/<?php echo $href;?>" title="<?php echo h($v['title']);?>" id="n<?php echo $v['Pages_ID'];?>" <?php if($v['children']){ ?>onmouseover="killmenu['n_<?php echo $v['Pages_ID'];?>']=false;menu('n_<?php echo $v['Pages_ID'];?>');" onmouseout="killmenu['n_<?php echo $v['Pages_ID'];?>']=true;"<?php } ?>><?php
                    }
                        if($settings['displayMethod']=='image'){
                            //show an image here
                            if($g=@getimagesize($img=$_SERVER['DOCUMENT_ROOT'].'/images/juliet/'.$acct.'.'.$cognate.'.png')){
                                ?><img src="<?php echo str_replace($_SERVER['DOCUMENT_ROOT'],'',$img);?>" <?php echo $g[3];?> alt="<?php echo h($v['name']);?>" /><?php
                            }else{
                                echo $v['name'];
                            }
                        }else{
                            echo $v['name'];
                        }
                    if($v['Pages_ID']){
                        ?></a><?php
                        echo "\n";
                    }
                        //submenu
                        /* don't think this was ever used
                        if($level+1 <= $settings['maxLevel']){
                            prn('calling again');
                            create_menu_nav($nav,$level+1,$parentNode);
                        }
                        */
                        if($b=$v['children']){
                            echo "\n";
                            ?><div id="n_<?php echo $v['Pages_ID'];?>" class="submenu" style="visibility:hidden;" onmouseover="killmenu['n_<?php echo $v['Pages_ID'];?>']=false;" onmouseout="killmenu['n_<?php echo $v['Pages_ID'];?>']=true;"><?php


                            if($cf['menu']['priority']){
                                foreach($b as $o=>$w){
                                    $b[$o]['priority']=(($x=$cf['menu']['priority'][$w['Pages_ID']]) ? $x : 1);
                                }
                                $b=subkey_sort($b,'priority');
                            }
                            /*
                                        if($cf['menu']['priority']){
                                            foreach($nav[$level][$node] as $n=>$v){
                                                $nav[$level][$node][$n]['priority']=(($w=$cf['menu']['priority'][$v['pages_id']]) ? $w : 1);
                                            }
                                            $nav[$level][$node]=subkey_sort($nav[$level][$node],'priority');
                                        }
                        */

                            foreach($b as $o=>$w){
                                echo "\n\t";
                                ?><div class="off" onmouseover="hl(this,true);" onmouseout="hl(this,false);"><a title="<?php echo h($w['Title']);?>" href="/<?php echo str_replace(' ','-',strtolower($w['NameT4']));?>"><?php echo $w['NameT4'];?></a></div><?php
                            }
                            echo "\n";
                            ?></div><?php
                        }
                        ?></li><?php
                        echo "\n";
                    }
                }
            }
            // ------------- end create_menu_nav ----------------
            create_menu_nav($nav,1,'root');
        ?></ul></span><?php

        pJ_call_edit(array(
            'formNode'=>'menu',
            'level'=>ADMIN_MODE_DESIGNER,
            'location'=>'JULIET_COMPONENT_ROOT',
            'file'=>end(explode('/',__FILE__)),
            'label'=>'edit menu',
        ));
        if($adminMode)echo '&nbsp;&nbsp;';
        pJ_call_edit(array(
            'formNode'=>'layout',
            'level'=>ADMIN_MODE_DESIGNER,
            'location'=>'JULIET_COMPONENT_ROOT',
            'file'=>end(explode('/',__FILE__)),
            'label'=>'edit layout',
        ));

        echo "\n";
    }
}//---------------- end __i__ break loop ---------------
