<!-- begin nav menu created <?php echo date('n/j/Y \a\t g:iA') . ' '.end(explode('/',__FILE__));?> -->
<?php
/*
todo
setting for their default nav - backend interface
reliable fly-out menu

*/
$pageSetting['NavID']=q("SELECT ID FROM gen_nodes WHERE Type='Group' AND Category='Navigation Menu' AND Active=1", O_VALUE);
if($a=q("SELECT n.*, m.Title 
	FROM _v_gen_nodes_hierarchy_nav n 
	LEFT JOIN site_metatags m ON n.Pages_ID=m.Objects_ID 
	WHERE n.GroupNodes_ID='".$pageSetting['NavID']."'",O_ARRAY)){
	unset($nav);
	foreach($a as $v){
		$idx=(is_null($v['Pri1']) ? (is_null($v['Pri2']) ? (is_null($v['Pri3']) ? 1 : 2) : 3) : 4);
		$nav[$idx][$v['ParentNodes_ID'] ? $v['ParentNodes_ID'] : 'root'][$v['Nodes_ID']]=array(
			'id'=>$v['Nodes_ID'],
			'active'=>$v['Active'],
			'name'=>$v['NameT4'],
			'priority'=>$v['Pri4'],
			'pages_id'=>$v['Pages_ID'],
			'pagename'=>$v['PageName'],
			'pagetype'=>$v['PageType'],
			'systemname'=>$v['SystemName'],
			'title'=>$v['Title'],
		);
	}
	?><script type="text/javascript" language="JavaScript1.2" src="/components-juliet/stm31.js"></script>
	<script type="text/javascript" language="JavaScript1.2">
	<?php
	ob_start();
	?>
	//begin the menu
	beginSTM("menu1283055303","static","0","0","left","false","true","310","1000","0","250","","/images/i/blank.gif");
	<?php
	$out=ob_get_contents();
	ob_end_clean();
	echo str_replace("\t", '', $out);
	if(!function_exists('create_nav')){
	function create_nav($nav,$level=1,$node){
		if(!$nav[$level][$node])return;

		$haveActive=false;
		foreach($nav[$level][$node] as $parentNode=>$v){
			if($v['active'])$haveActive=true;
		}
		if(!$haveActive)return;

		//begin a menu
		ob_start();
		if($level==1){
			?>
			beginSTMB("auto","0","0","horizontally","/images/i/arrow_r.gif","7","7","0","3","#ffffff","","tiled","#000000","0","none","0","Normal","50","0","0","7","7","0","0","0","#7f7f7f","false","#000000","#000000","#000000","none");
			<?php
		}else{
			?>
			beginSTMB("auto","0","0","vertically","","0","0","0","3","#ffffff","","tiled","#000000","1","solid","0","Fade","50","0","0","0","0","0","0","2","#c8c8c8","false","#000000","#000000","#000000","simple");
			<?php
		}
		$out=ob_get_contents();
		ob_end_clean();
		echo str_replace("\t", '', $out);
		foreach($nav[$level][$node] as $parentNode=>$v){
			ob_start();
			//append menu item
			?>
			appendSTMI("false","<?php 
			//label
			echo str_replace(' ','&nbsp;',$v['name']);
			
			?>","left","middle","","","-1","-1","0","normal","#666666","#762938","","1","-1","-1","/images/i/blank.gif","/images/i/blank.gif","-1","-1","0","<?php 
			//advisory title
			echo h($v['title']);
			
			?>","<?php 
			//URL
			if($v['pagetype'] && strtolower($v['pagetype'])!=='default'){
				echo '/'.str_replace(':','/',$v['pagetype']);
			}else if($v['pagename']){
				echo '/' . str_replace(' ','-',preg_replace('/[^-a-z0-9 ]/i','',preg_replace('/\.php$/i','',$v['pagename'])));
			}else if($v['systemname']){
				echo get_systemname($v['systemname'],'url');
			}
			
			?>","_self","Tahoma, Arial, Verdana","10pt","#ffffff","normal","normal","none","Tahoma, Arial, Verdana","10pt","#ffffff","normal","normal","none","1","solid","#666666","#666666","#762938","#762938","#666666","#ffffff","#762938","#ffffff","<?php 
			//status bar text
			echo ($v['pagename'] ? '/' . str_replace(' ','-',$v['pagename']) : ($v['systemname'] ? get_systemname($v['systemname'],'url') : ''));
			
			?>","","","tiled","tiled");
			<?php
			$out=ob_get_contents();
			ob_end_clean();
			echo str_replace("\t", '', $out);
			//submenu
			create_nav($nav,$level+1,$parentNode);
		}
		ob_start();
		//end menu bar
		?>
		endSTMB();
		<?php
		$out=ob_get_contents();
		ob_end_clean();
		echo str_replace("\t", '', $out);
	}
	}
	ob_start();
	create_nav($nav,1,'root');
	//end the menu
	?>endSTM();<?php
	echo "\n";
	$out=ob_get_contents();
	ob_end_clean();
	echo $out;
	if(!$out){
		//mail site owner, nav not set up
		mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
	}
	?></script><!-- end nav menu --><?php
}

?>