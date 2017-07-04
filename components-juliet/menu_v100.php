<?php
/* name=Basic Horizontal Menu; description=This menu only reads the top level of the default or specified menu; */
/*
2011-09-01
	* THE objective of this component is to have controls available to control itself, via a system/protocol that meshes in with juliet.
	* The goal of this *specific* component is to read a selected menu (default menu if not declared), and select a style and a vertical or horizontal orientation.
	* We start with some standardizations for a component, and I'm using OOP for the first time only for very rudimentary applications
		each component should have a unique handle; also a title and description.  This is used by Juliet in selection lists and etc.
	* there is also the concept of "bubble-up CSS" - i.e. this component hooking into the default css file (with acct.. at prefix) and declaring the css there by fwrite(); this is much cleaner than declaring css throughout.
	

todo
	multiple menus
	unique id's based on common sense
	css declared separately
	pages all work OK
	

*/

	//primary menu not technically defined - simply Active=1 means primary in this case

if(!$menuDef)
$menuDef=array(
	array(
		'settings'=>array(
			'navID'=>q("SELECT ID FROM gen_nodes WHERE Type='Group' AND Category='Navigation Menu' AND Active=1", O_VALUE),
			'displayMethod'=>'image',
			'maxLevel'=>1,
		),
	),
);
if(!$readComponentOnly){
	for($__i__=1; $__i__<=1; $__i__++){
	if(false /* $css=$pJ-something..[$handle]['css'] */){
		echo $css;
	}else{
		?><style type="text/css">
		.julietMenu li{
			display:inline;
			margin-right:20px;
			}
		</style><?php
	}
	?>
	<script language="javascript" type="text/javascript">
	
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
				'pages_id'=>$v['Pages_ID'],
				'pagename'=>$v['PageName'],
				'pagetype'=>$v['PageType'],
				'systemname'=>$v['SystemName'],
				'title'=>$v['Title'],
			);
		}
		?><ul id="navMenu<?php echo $settings['navID']?>" class="julietMenu"><?php
		echo "\n";
		if(!function_exists('create_menu_nav')){
		function create_menu_nav($nav, $level=1, $node){
			global $settings,$acct,$_SERVER['DOCUMENT_ROOT'];
			if(!$nav[$level][$node])return;
			foreach($nav[$level][$node] as $parentNode=>$v){

				$cognate=($v['systemname']=='{root_website_page}' ? 'home' : preg_replace('/[^-a-z]*/','',strtolower($v['pagename'])));
				$href=($v['systemname']=='{root_website_page}' ? '' : str_replace(' ','-',strtolower($v['pagename'])));

				?><li><?php
				echo "\n";
				if($v['pages_id']){
					?><a href="/<?php echo $href;?>" title="<?php echo h($v['title']);?>" id=""><?php
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
				if($v['pages_id']){
					?></a><?php
					echo "\n";
				}
				//submenu
				if($level+1 <= $settings['maxLevel']){
					prn('calling again');
					create_menu_nav($nav,$level+1,$parentNode);
				}
				?></li><?php
				echo "\n";
			}
		}} //---------------- end function create_menu_nav ------------
		create_menu_nav($nav,1,'root');
		
		?></ul><?php
		echo "\n";
	}
	
	} //------------- end break loop --------------
}

?>