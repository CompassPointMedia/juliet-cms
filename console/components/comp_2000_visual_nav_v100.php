<?php

/*
TEST:
$foo=array();
$x=&$foo[1];
$x=array(10=>a,11=>b,12=>c);
prn($foo);
exit;


2013-09-05
----------
this is the best way to visualize the nav.  the combo between gen_nodes and gen_nodes_hierarchy is probably as good a system as any for storing complex menu relationships.  This GUI is just starting, see todo list below:

todo
----
0. space out the bubbles correctly width wise
1. delete a node
2. move the page on one node to another node
3. move a node
4. re-prioritize a node
5. indicate page present or not
6. indicate multiple pages
7. change names of elements right here
8. pop-up for multiple pages or just for one even, to add a secondary page
10. ability to see the SEO and page visits right here
*/
//get the menu
$menus=q("SELECT n.*, COUNT(DISTINCT h.ID) AS count FROM gen_nodes n LEFT JOIN gen_nodes_hierarchy h ON n.ID=GroupNodes_ID WHERE n.Type='Group' GROUP BY n.ID ORDER BY n.Name", O_ARRAY_ASSOC);
if($Menus_ID){
	if(!$menus[$Menus_ID])exit('unable ot find nav id '.$Menus_ID);
}else if(count($menus)){
	foreach($menus as $n=>$v){
		if($v['count']>$count || !isset($count)){
			$count=$v['count'];
			$Menus_ID=$n;
		}
	}
}
?>
<select name="Menus_ID" id="Menus_ID" onchange="window.location='rsc_menu_gui.php?Menus_ID='+this.value;">
<?php
foreach($menus as $n=>$v){
	?><option value="<?php echo $n?>" <?php echo $n==$Menus_ID?'selected':''?>><?php echo h($v['Name']).($v['count']>0?' ('.$v['count'].')':'');?></option><?php
}
?>
</select><span class="gray">NOTE: numbers in parenthesis not equal to number of menu items</span><br />

<?php
if(false && 'version1'){
	function structure($Menus_ID,$id=NULL,$level=1,&$str=array(),$options=array()){
		extract($options);
		if($a=q("SELECT n.* FROM _v_gen_nodes_hierarchy_nav n WHERE n.GroupNodes_ID=$Menus_ID AND n.ParentNodes_ID ".(is_null($id)?'IS NULL':'='.$id), O_ARRAY_ASSOC)){
			foreach($a as $n=>$v){
				$string=($v['PageName']?$v['PageName']:($v['SystemName']?$v['SystemName']:$v));
				$str[$n]['p']=$string;
				
				//this is a double call
				if($b=q("SELECT n.ID, n.Nodes_ID FROM _v_gen_nodes_hierarchy_nav n WHERE n.GroupNodes_ID=$Menus_ID AND n.ParentNodes_ID='".$v['Nodes_ID']."'", O_ARRAY_ASSOC)){
					structure($Menus_ID,$v['Nodes_ID'],$level+1,&$str[$n]['n'],$options);
				}
			}
		}
	}
}

if(true && 'version2'){
	function structure($Menus_ID,$id=NULL,$level=1,&$str=array(),$options=array()){
		extract($options);
		if($a=q("SELECT n.* FROM _v_gen_nodes_hierarchy_nav n 
			WHERE n.GroupNodes_ID=$Menus_ID AND n.ParentNodes_ID ".(is_null($id)?'IS NULL':'='.$id), O_ARRAY_ASSOC)){
			foreach($a as $n=>$v){
				$string=$v['NameT4'];
				$str[$n]['n']=$string;
				if($v['Pages_ID'])$str[$n]['p'][]=$v['Pages_ID'];
				
				//this is a double call
				if($b=q("SELECT n.ID, n.Nodes_ID FROM _v_gen_nodes_hierarchy_nav n 
					WHERE n.GroupNodes_ID=$Menus_ID AND n.ParentNodes_ID='".$v['Nodes_ID']."'", O_ARRAY_ASSOC)){
					structure($Menus_ID,$v['Nodes_ID'],$level+1,&$str[$n],$options);
				}
			}
		}
	}
}
$structure=array();
structure($Menus_ID,NULL,1,$structure,array());
#prn($structure);

#prn('----------------',1);

$width=1;
function structure_width($structure,$node=NULL,$status=false,$parentStatus=false,$level=1){
	global $width;
	if($status)$parentStatus=true;
	unset($structure['n'],$structure['p']);
	if($c=count($structure)){
		if($status==true || $parentStatus==true){
			#prn('size on node '.$node.' is '.$c);
			$width+=$c-1;
		}
		foreach($structure as $n=>$v){
			$n==$node ? $status=true : $status=false;
			structure_width($v,$n,$status,$parentStatus,$level);
		}
	}
}

$nomWidth=150;
$nomHeight=25;

function structure_expansion($node,$array,$expansion=0,$flags=array()){
	if(empty($array))return;
	$_a=array_keys($array);
	unset($_a['n'],$_a['p']);
	prn('keys: '.implode(',',$_a));
	if(in_array($node,array_keys($array)))$flags['in']=true;
	$i=0;
	foreach($array as $n=>$v){
		if(preg_match('/[np]/',$n))continue;
		
		
		//no process nodes to right
		if($after)break;
		if($node==$n){
			prn('after');
			$after=true;
		}
		
		$i++;
		if($flags['in'])$expansion+=($i>1?1:0)+structure_expansion($n,$v,$expansion,$flags);
	}
	return $expansion;
}
#structure_expansion(17,$structure);


if(false && 'version1'){
	function nav($Menus_ID,$id=NULL,$level=1,$options=array()){
		global $width,$structure,$nomWidth,$nomHeight,$interface;
		/*
		todo:
		get title and href
		options:
			value to <a> all non-page nodes as well
			
		*/
		extract($options);
		global $_nav_,$adminMode,$qr,$fl,$ln,$developerEmail,$fromHdrBugs;
		global $intersperseMenuOptions, $replaceExtendOption;
		
		$adminMode=true;
	
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
			?><div class="nlevel<?php echo $level;?><?php if($level>1)echo ' children';?>"><?php
			$width=1;
			foreach($a as $n=>$v){
			
				if($adminMode)echo "\n";
				if($adminMode)echo str_repeat("\t",$level);
				
				if($fctn=$replaceExtendOption[$v['ID']]){
					$fctn($a,$n,$Menus_ID,$id,$level,$options);
				}else{
					if($interface=='gui'){
						$style='position:absolute;';
						//$style.='top:'.((floor($level/2)*75)+50).'px;';
						$style.='top:'.$nomHeight.'px;';
						$style.='left:'.($_nav_['levels'][$level]*$nomWidth).'px;';
						$width=1;
						structure_width($structure,$n,$status=false,$parentStatus=false,$level);
						$_nav_['levels'][$level]+=$width;
					}
					?><div id="<?php echo $v['Pages_ID']?'n'.$v['Pages_ID']:'u'.$v['ID'];?>" class="libox" style="<?php echo $style;?>"><?php
					
				
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
					nav($Menus_ID,$v['Nodes_ID'],$level+1,$options);
					?></div><?php
				}
	
			}
			if($adminMode)echo "\n";
			if($adminMode)echo str_repeat("\t",$level-1);
			if($adminMode)echo '  ';
			?></div><?php
		}
	}
}

function nav($Menus_ID,$id=NULL,$level=1,$options=array()){
	global $width,$structure,$nomWidth,$nomHeight,$interface;
	/*
	todo:
	get title and href
	options:
		value to <a> all non-page nodes as well
		
	*/
	extract($options);
	global $_nav_,$adminMode,$qr,$fl,$ln,$developerEmail,$fromHdrBugs;
	global $intersperseMenuOptions, $replaceExtendOption;
	
	$adminMode=true;

	if($a=q("SELECT n.*, m.Title, m.Description FROM _v_gen_nodes_hierarchy_nav n LEFT JOIN site_metatags m ON n.Pages_ID=m.Objects_ID WHERE n.GroupNodes_ID=$Menus_ID AND n.ParentNodes_ID ".(is_null($id)?'IS NULL':'='.$id), O_ARRAY_ASSOC)){
		#if($id==16)prn($a,1);
		
		//2013-07-24: better presentation of options - should make much more sense
		foreach($a as $n=>$v){
			//_v_gen_nodes_hierarchy_nav will show even if no options
			if(!strlen($n))return false;

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
		?><ul class="tiers nlevel<?php echo $level;?><?php if($level>1)echo ' children';?>"><?php
		$width=1;
		$expansions=0;
		foreach($a as $n=>$v){
		
			if($adminMode)echo "\n";
			if($adminMode)echo str_repeat("\t",$level);
			
			if($fctn=$replaceExtendOption[$v['ID']]){
				$fctn($a,$n,$Menus_ID,$id,$level,$options);
			}else{
				/*
				the spacing of an element is equal to nomWidth + the expansions of all previous elements in this group
				*/
				if($interface=='gui'){
					$style='position:absolute;';
					//$style.='top:'.((floor($level/2)*75)+50).'px;';
					$style.='top:'.$nomHeight.'px;';
					$style.='left:'.($_nav_['levels'][$level]*$nomWidth).'px;';
					$width=1;
					structure_width($structure,$n,$status=false,$parentStatus=false,$level);
					$_nav_['levels'][$level]+=$width;
				}
				?><li id="<?php echo $v['Pages_ID']?'n'.$v['Pages_ID']:'u'.$v['ID'];?>" class="libox" style="<?php echo $style;?>"><?php
				
			
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
				
				?><h3 class="nullTop nullBottom"><?php
				if($v['PageName']){
					echo $v['PageName'];
				}else{
					echo $v['NameT4'];
				}

				?></h3><?php
				if($v['ParentNodes_ID']){
					?>p=<?php echo $v['ParentNodes_ID'];?><br /><?php
				}
				?>
				n=<?php echo $v['Nodes_ID'];?><?php 
				if($v['Pages_ID']){
					?><a href="<?php echo $href;?>" target="_blank"<?php if($v['Title'])echo ' title="'.h($v['Title']).'"';?>><?php
					echo ' o='.$v['Pages_ID'];
					?></a> <a href="rsc_pagemanager_focus.php?Nodes_ID=<?php echo $v['Pages_ID'];?>" onclick="return ow(this.href,'l1_page','800,700');" title="Page Manager edit page">(e)</a><?php
				}
				
				?>
				<br />
				<a href="rsc_pagemanager_focus.php?disposition=2&pageUse=1&pageNav=<?php echo $v['Nodes_ID'];?>" onclick="return ow(this.href,'l1_page','800,700');" title="new node AND page"><strong style="color:#444;">new n:p</strong></a><br />
				<?php
				
				//modify options if needed
				nav($Menus_ID,$v['Nodes_ID'],$level+1,$options);
				?></li><?php
			}

		}
		if($adminMode)echo "\n";
		if($adminMode)echo str_repeat("\t",$level-1);
		if($adminMode)echo '  ';
		?></ul><?php
	}
	return true;
}

?>
<style type="text/css">
.libox{
	float:left;
	min-width:110px;
	border:1px solid darkred;
	border-radius:8px;
	}
.tiers{
	position:relative;border:1px dotted darkgreen;min-width:220px;min-height:100px;
	}
.tiers li{
	list-style:none;
	padding-left:4px;
	background-color:rgba(255,255,255,0.85);
	}
</style>
<div style="position:relative;border-right:1px solid #333;border-bottom:1px solid #333;width:500%;min-height:400px;background-image:url('/images/i/lines/grid_50x50.png');"><?php
$hasNav=nav($Menus_ID,NULL,1,array('interface'=>'gui'));
if(!$hasNav){
	?><h4>No navigation structure or pages for this menu</h4>
	<a href="rsc_pagemanager_focus.php?" onclick="return ow(this.href,'l1_page','800,600');">Create a page</a><br />
	<?php
}
?></div>