<?php
/*
2011-08-18:
	* we can pass in query string ?mainRegionCenterContent=hello and that puts content in place; this could also map to messages and stock content which itself has variables defined {|variables|} have not been dealt with in Juliet currently
2011-07-19: things are really flying in this file; look throughout for notes
	* when adminMode=A_M_Editor, common editable regions are not seen, but when = 2 they are
	* even if I develop this to where I get Don's site in place, what I WON'T have done is get it to where "what this region probably means" has a language to it - and this is necessary to talk themes between people.
*/

function pJCSS($r){
	/* created 2011-04-28 */
	global $pJCSS, $pJCurrentContentRegion;
	$pJCurrentContentRegion=$r;
	if($c=$pJCSS[$r][0])echo ' class="'.$c.'"';
	if($c=$pJCSS[$r][1])echo ' style="'.$c.'"';
}
if(false){
	function pJWrap($r){
		/* created 2011-04-28 */
		global $pJWrap, $pJWrap_status;
		if(!$pJWrap[$r])return;
		echo $pJWrap[$r][($pJWrap_status[$r] ? 1 : 0)];
		if(!$pJWrap_status[$r])$pJWrap_status[$r]=1;
	}
}
function recurse_array($a,$level=1,$options=array()){
	extract($options);
	if(!$type)$type='menu';
	if(!$field)$field='block';
	global $$field,$recurse_array;
	foreach($a as $n=>$v){
		if($n===0)continue;
		if($type=='menu'){
			?><div class="indented">
			<a href="#" onMouseOver="focusRegion(this,1)" onMouseOut="focusRegion(this,2)" onClick="return focusRegion(this,0);" title="Edit or configure this block of the template across all pages"><?php echo $n?></a> <!-- pJ.settings_toolbar.php-<?php echo $n;?> -->
			<?php if(count($v))recurse_array($v,$level+1,$options);?>
			</div><?php
		}else{
			if(false){ ?><select name="block"><?php }
			?><option value="<?php echo $n?>" <?php 
			if($$field==$n){
				$recurse_array['parent']=$recurse_array['previous'][$level-1];
				echo 'selected';
			}
			?>><?php echo str_repeat('&nbsp;',4*($level-1));?><?php echo $n?></option><?php
			$recurse_array['previous'][$level]=$n;
			if(count($v))recurse_array($v,$level+1,$options);
			if(false){ ?></select><?php }
		}
	}
}
function array_key_exists_deep_idle($n){return $n;}
function array_key_deep($a,$key,$options=array()){
	extract($options);
	/*
	options
		getParent
	*/
	if(!isset($caseInsensitive))$caseInsensitive=true;
	$function=($caseInsensitive?'strtolower':'array_key_deep_idle');
	global $test;
	
	if(!is_array($a))return;
	
	foreach($a as $n=>$v){
		//strlen($n)==strlen($key)
		if(strtolower($n)===strtolower($key)){
			return (isset($subKey) ? ($subSubKey ? $v[$subKey][$subSubKey] : $v[$subKey]) : 1);
		}else if($out=array_key_deep($v,$key,$options)){
			//this is really returning the results of the codeblock prior to this
			return $out;
		}
	}
}
function global_extractor($__file__,$options=array()){
	/*2012-04-21 SF - allows a file be be run inside this function as if it was public
	options
		return = string; use so calling function can also globalize
	*/
	extract($options);
	$a=array(
		'GLOBALS','_ENV','HTTP_ENV_VARS','AUTH_TYPE','HTTP_COOKIE','PHP_AUTH_PW','PHP_AUTH_USER','argv','_POST','HTTP_POST_VARS','_GET','HTTP_GET_VARS','_COOKIE','HTTP_COOKIE_VARS','_SERVER','HTTP_SERVER_VARS','_FILES','HTTP_POST_FILES','_REQUEST','SUPER_MASTER_USERNAME','SUPER_MASTER_PASSWORD','SUPER_MASTER_HOSTNAME','SUPER_MASTER_DATABASE','MASTER_DATABASE','MASTER_USERNAME','MASTER_HOSTNAME','MASTER_PASSWORD','a','n','v','HTTP_SESSION_VARS','_SESSION',
	);
	$str='global $';
	foreach($GLOBALS as $n=>$v){
		if(in_array($n,$a))continue;
		$str.=$n.',$';
	}
	$str=rtrim($str,',$').';';
	if($return=='string'){
		return $str;
	}else{
		eval($str);
		require($__file__);
	}
}
function pJVarParser($content,$m,$options){
	/* created 2012-04-03 SF. Options = the arguments that were passed in from CMSB, plus others eventually if given 
	
	{handle::var::subvar::subsubvar} where the vars are optional
	
	*/
	extract($options);
	global $test;
	global $pJ,$qr,$qx,$developerEmail,$fromHdrBugs,$Templates_ID;	
	//------------ code block 2094260 -----------
	if(!isset($_SESSION['pJ']['componentsRegisteredSession']) || $pJComponentsRegisteredRefresh){
		$_SESSION['pJ']['componentsRegisteredSession']=time();
		$pJ['componentsRegistered']=q("SELECT LCASE(c.Handle) AS handle, 0 AS compiled, tc.Settings, c.Location, c.ComponentFile, c.Description FROM gen_templates t, gen_TemplatesComponents tc, gen_components c WHERE t.ID=tc.Templates_ID AND tc.Components_ID=c.ID AND t.ID=$Templates_ID", O_ARRAY_ASSOC);
	}
	//--------------------------------------------
	for($i=0; $i<count($m[0]); $i++){
		$a=preg_split('/:+/',trim($m[0][$i],'{}: '));
		$handle=strtolower($a[0]);
		if(preg_match('/^(if|else|elseif|for|foreach|end)$/',$handle)){
			//logical constructions - not ready yet; for example {if::}     {elseif::}         {end::} - php coding would be replaced here and the assembly would be eval'd dynamically
			$eval=true;
			switch($handle){
				case 'if':
					$content=str_replace($m[0][$i],'<?php if('.$a[1].'){ ?>',$content);
					continue;
				case 'else':
					$content=str_replace($m[0][$i],'<?php }else{ ?>',$content);
					continue;
				case 'elseif':
					$content=str_replace($m[0][$i],'<?php }elseif('.$a[1].'){ ?>',$content);
					continue;
				case 'for':
					$content=str_replace($m[0][$i],'<?php for('.$a[1].'){ ?>',$content);
					continue;
				case 'foreach':
					$content=str_replace($m[0][$i],'<?php foreach('.$a[1].'){ ?>',$content);
					continue;
				case 'end':
					$content=str_replace($m[0][$i],'<?php } ?>',$content);
					continue;
			}
		}
		if(!$a[1])continue;
		if(!$pJ['componentsRegistered'][$handle]['compiled']){
			if(strlen($pJ['componentsRegistered'][$handle]['settings']))$pJ['componentsRegistered'][$handle]['settings']=unserialize(base64_encode($pJ['componentsRegistered'][$handle]['settings']));
			$pJ['componentsRegistered'][$handle]['compiled']=1;
		}
		if($var=$pJ['componentsRegistered'][$handle]['settings']['vars'][$a[1]]){
			//this is a variable that can be and has been set, simply retrieve it.
			if($pJ['componentsRegistered'][$handle]['settings']['vartypes'][$a[1]]==16){
				//not developed
			}else{
				$content=str_replace($m[0][$i],$var,$content);
			}
		}else if(@array_key_exists($handle, array_change_key_case($pJ['componentFiles']))){
			$content=str_replace(
				$m[0][$i],
				pJ_getdata(array('handle'=>$handle,'field'=>$a[1],'subKey'=>($a[2]?$a[2]:NULL))),
				$content
			);
		}else{
			$GLOBALS['var']=$a[1];
			if(isset($a[2]))$GLOBALS['subvar']=$a[2];
			if(isset($a[3]))$GLOBALS['subsubvar']=$a[3];
			if($pJ['componentsRegistered'][$handle]['ComponentFile']){
				ob_start();
				global_extractor(
					$GLOBALS[$pJ['componentsRegistered'][$handle]['Location']].'/'.
					$pJ['componentsRegistered'][$handle]['ComponentFile']
				);
				$content=str_replace($m[0][$i],ob_get_contents(),$content);
				ob_end_clean();
			}
		}
	}
	if($eval){
		ob_start();
		eval('?>'.$content.'<?php ');
		$eval=ob_get_contents();
		ob_end_clean();
		return $eval;
	}
	return $content;
}
function pJ_modify_document_callback($document){
	global $pJLocalCSS, $pJLocalCSSPlaceholder;
	if(count($pJLocalCSS))
	foreach($pJLocalCSS as $n=>$v){
		$document=str_replace(
			$pJLocalCSSPlaceholder,
			'/* ----------- Module CSS '.$n.' (back-added by callback) ------------ */'."\n".trim($v)."\n".$pJLocalCSSPlaceholder,
			$document
		);
	}
	return $document;
}
function pJ_parentnodes($id){
	global $pJBlocks;
	$i=0;
	while(true){
		$i++;
		foreach($pJBlocks as $n=>$v){
			/*if($v['ID']==$parentid){
				$collection[]=$v['Name'];
			}else */if($v['ID']==$id){
				$collection[]=$n;
				if(is_null($v['Blocks_ID'])){
					break(2);
				}else{
					$id=$v['Blocks_ID'];				
				}
				$parentid=$v['Blocks_ID'];
			}
		}
		if($i>15)exit('Error in function pJ_parentnodes, over 15 loops');
	}
	return array_reverse($collection);
}
function pJ_suppress_block($n){
	global $templateDefinedBlocks, $Settings;
	if(@in_array(strtolower($n), $Settings['BlockSuppressionOverride']))return false;
	return array_key_deep($templateDefinedBlocks,$n,array('subKey'=>0,'subSubKey'=>'position'))=='none';
}
/*
development up through "canteloupe" started 2011-07-18; getting to where I can design this on the fly
*/

/* ------------------------------- canteloupe ------------------------------- */
$qx['useRemediation']=true;
ob_start();
if(false){
	if(!($n=q("SELECT Value FROM gen_tests WHERE VarGroup='Juliet:Setup' AND Var='Initial'", O_VALUE))){
		//exit('system is attempting to do initial setup on line '.__LINE__.'. you must make sure any existing nodes or table values are not in place or are lost');
		//here we do all the things we need to - we make the assumption that none of these things have been done - so if any are partially there, the system will fail
		$t=time();
		q("CREATE TABLE cmsb_sections_$t SELECT * FROM cmsb_sections");
		q("CREATE TABLE gen_nodes_$t SELECT * FROM gen_nodes");
		q("CREATE TABLE gen_nodes_settings_$t SELECT * FROM gen_nodes_settings");
		q("CREATE TABLE gen_nodes_hierarchy_$t SELECT * FROM gen_nodes_hierarchy");
		q("CREATE TABLE gen_templates_$t SELECT * FROM gen_templates");
		q("CREATE TABLE gen_templates_blocks_$t SELECT * FROM gen_templates_blocks");
	
		q("TRUNCATE TABLE cmsb_sections");
		q("TRUNCATE TABLE gen_nodes");
		q("TRUNCATE TABLE gen_nodes_settings");
		q("TRUNCATE TABLE gen_nodes_hierarchy");
		q("TRUNCATE TABLE gen_templates");
		q("TRUNCATE TABLE gen_templates_blocks");
	
		if(!($a=q("SELECT * FROM relatebase_template.gen_templates WHERE Name='".str_replace('.php','',end(explode('/',$pJulietTemplate)))."'",O_ROW, C_SUPER))){
			mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err='did not have the templates_id and could not find in relatebase_template'),$fromHdrBugs);
			error_alert($err);
		}
		$MasterTemplates_ID=$a['ID'];
		$sql="INSERT INTO $acct.gen_templates SET ";
		foreach($a as $n=>$v){
			if($n=='ID' || !strlen($v))continue;
			$sql.=$n.'=\''.addslashes($v).'\', ';
		}
		$Templates_ID=q(rtrim($sql,', '), O_INSERTID);
		prn($qr);
		$add=q("SELECT MAX(ID) FROM $acct.gen_templates_blocks",O_VALUE);
		prn($qr);
		foreach(q("SELECT * FROM relatebase_template.gen_templates_blocks WHERE Templates_ID=$MasterTemplates_ID", O_ARRAY, C_SUPER) as $v){
			$v['Templates_ID']=$Templates_ID;
			$sql="INSERT INTO $acct.gen_templates_blocks SET ";
			foreach($v as $o=>$w){
				if(!strlen($w))continue;
				if($o=='ID')$w+=$add;
				if($o=='Blocks_ID')$w+=$add;
				$sql.=$o.'=\''.addslashes($w).'\', ';
			}
			q(rtrim($sql,', '));
			prn($qr);
		}
		q("INSERT INTO $acct.cmsb_sections SELECT * FROM relatebase_template.cmsb_sections WHERE ThisPage='common'",C_SUPER);
		prn($qr);
	
		
		/* 2012-02-16: this is also in _juliet_.settings.php */
		q("INSERT INTO gen_tests SET VarGroup='Juliet:Setup', Var='Initial', Value=1, CreateDate=NOW(), Creator='system'");
		q("SELECT COUNT(*) FROM gen_nodes");
		q("SELECT COUNT(*) FROM gen_nodes_hierarchy");
		q("SELECT COUNT(*) FROM gen_nodes_settings");
		q("INSERT INTO $acct.gen_nodes SELECT * FROM relatebase_template.gen_nodes", C_SUPER, ERR_SILENT);
		q("INSERT INTO $acct.gen_nodes_hierarchy SELECT * FROM relatebase_template.gen_nodes_hierarchy", C_SUPER, ERR_SILENT);
		q("SELECT COUNT(*) FROM _v_gen_nodes_hierarchy_nav");
		q("SELECT COUNT(*) FROM _v_pages_juliet");
	}
	if(!($n=q("SELECT Value FROM gen_tests WHERE VarGroup='Juliet:Setup' AND Var='ActiveEnhance'", O_VALUE))){
		//here we do all the things we need to - we make the assumption that none of these things have been done - so if any are partially there, the system will fail
		$a=q("SELECT DISTINCT Active FROM gen_nodes ORDER BY Active", O_COL);
		switch(true){
			case count($a)==0:
				//ok - no pages
			break;
			case count($a)==1 && (current($a)==1 || current($a)==0):
			case count($a)==2 && (current($a)==0 && end($a)==1):
				q("UPDATE gen_nodes SET Active = IF(Active=1, 8, 0)");
			break;
			case count($a)>2:
				//do nothing, this must have been configured already!!
			break;
		}
		q("ALTER TABLE gen_nodes CHANGE Active Active TINYINT(1) UNSIGNED NOT NULL DEFAULT 8");	
		q("INSERT INTO gen_tests SET VarGroup='Juliet:Setup', Var='ActiveEnhance', Value=1, CreateDate=NOW(), Creator='system'");
	}
	if(!($n=q("SELECT Value FROM gen_tests WHERE VarGroup='Juliet:Setup' AND Var='ComponentsTable'", O_VALUE))){
		$a=q("SHOW CREATE TABLE relatebase_template.gen_components", O_ROW, C_SUPER);
		q(str_replace('CREATE TABLE','CREATE TABLE IF NOT EXISTS',$a['Create Table']));
		$a=q("SHOW CREATE TABLE relatebase_template.gen_TemplatesComponents", O_ROW, C_SUPER);
		q(str_replace('CREATE TABLE','CREATE TABLE IF NOT EXISTS',$a['Create Table']));
		q("INSERT INTO gen_tests SET VarGroup='Juliet:Setup', Var='ComponentsTable', Value=1, CreateDate=NOW(), Creator='system'");
		mail($developerEmail, 'Notice in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err='added gen_components tables'),$fromHdrBugs);
	}
	if(!($n=q("SELECT Value FROM gen_tests WHERE VarGroup='Juliet:Setup' AND Var='BlockPositionNone'", O_VALUE))){
		$a=q("SHOW CREATE TABLE gen_templates_blocks", O_ROW);
		preg_match('/`Position` enum\(([^)]+)\)/i',$a['Create Table'],$s);
		if(!$s)exit('gen_templates_blocks.Position field is not value ENUM');
		if(!strstr($s[1],'\'none\'')){
			q("ALTER TABLE gen_templates_blocks CHANGE Position Position ENUM(".$s[1].",'none') COLLATE utf8_unicode_ci NOT NULL COMMENT 'CSS Position Values'");
		}
		q("INSERT INTO gen_tests SET VarGroup='Juliet:Setup', Var='BlockPositionNone', Value=1, CreateDate=NOW(), Creator='system'");
		mail($developerEmail, 'Notice in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err='added gen_components tables'),$fromHdrBugs);
	}
	$notice=ob_get_contents();
	if($notice){
		mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals('juliet template setup initiated, and probably table gen_tests was created'. $notice),$fromHdrBugs);
	}
}

ob_end_clean();
$activeLevel=8;
/*
for templateDefinedBlocks i have several goals:
	[2012-05-13: these are left to do; the array is now generated from gen_templates_blocks values]
	5. lose the idea of absolute vs. relative vs. inherit - commit to settings - but the key issue of the top nav is that we are "splitting up" divs somehow and what we'll really need to do is simply back-force a div to be relative if a child div is absolute
	6. a div has the following attributes
		a. its editability level.  some divs we want editable on content edit, others we want editable on layout/designer edit
		b. how flexible/dynamic it typically is
	
	from here we want to move the array into the database fully as then it can be a) shared b) installed
	I want to have the default settings of the template able to be returned to
*/

/* miscellaneous values */
if(!isset($suppressWrappers['mainWrapSub']))$suppressWrappers['mainWrapSub']=true;
$primaryBlockName='mainRegionCenterContent';
$mainRegionCenterPreContent=true;

/*Declared before the component*/
$templateName=str_replace('.php','',end(explode('/',$pJulietTemplate)));

if(file_exists($f=$_SERVER['DOCUMENT_ROOT'].'/site-local/'.$acct.'.'.$templateName.'.global.php')){
	$str=implode('',file($f));
	$str=trim($str);
	eval(' ?>'.$str.'<?php ');
}
if($pJBlocks=q("SELECT b.Name, b.*, a.Name AS TemplateName FROM gen_templates a, gen_templates_blocks b WHERE a.ID=b.Templates_ID AND a.Name='$templateName'",O_ARRAY_ASSOC)){
	foreach($pJBlocks as $n=>$v){
		if(!$Templates_ID)$Templates_ID=$v['Templates_ID'];
		if($a=array_key_deep($templateDefinedBlocks,$n,array('subKey'=>0)))$pJBlocks[$n]['settings']=$a;
		$pJBlocks[$n]['parentnodes']=pJ_parentnodes($v['ID']);
		eval( '$templateDefinedBlocks[\''.implode('\'][\'',$pJBlocks[$n]['parentnodes']).'\'][0]=array('.
			(trim($v['Content'])?'\'content\'=>$v[\'Content\'],':'').
			(trim($v['Parameters'])?'\'parameters\'=>$v[\'Parameters\'],':'').
			(trim($v['Position'])?'\'position\'=>$v[\'Position\'],':'').
			( true ?'\'editability\'=>ADMIN_MODE_DESIGNER,':'').
		');' );
		if(strstr($n,'topRegion')){
			//these will be evaluated later
			$pJTopBlocks[$n]=$v;
		}
	}
}else{
	exit('abnormal error on line '.__LINE__.' of '.end(explode('/',__FILE__)).', unable to locate array $pJBlocks');
}

foreach($consoleEmbeddedModules as $n=>$v){
	$gettable_parameters[$v['SKU']]=array();
	if(!empty($v['moduleAdminSettings']['gettable_parameters'])){
		foreach($v['moduleAdminSettings']['gettable_parameters'] as $o=>$w){
			$gettable_parameters[$v['SKU']][$o]=(is_array($w) ? $w[0] : $w);
		}
	}
	$settable_parameters[$v['SKU']]=array();
	if(!empty($v['moduleAdminSettings']['settable_parameters'])){
		foreach($v['moduleAdminSettings']['settable_parameters'] as $o=>$w){
			$settable_parameters[$v['SKU']][$o]=(is_array($w) ? $w[0] : $w);
		}
	}
}

$hasAdmin=false;
if($a=$_SESSION['cnx'][$acct]['accesses'])foreach($a as $v)if(preg_match('/^(admin|db admin)$/i',$v)){
	$hasAdmin=true;
	break;
}

//------------ code block 2094260 -----------
$pJComponentsRegisteredRefresh=true;
unset($_SESSION['pJ'],$pJ);
if(!isset($_SESSION['pJ']['componentsRegisteredSession']) || $pJComponentsRegisteredRefresh){
	$pJComponentsRegisteredRefresh=false;
	$_SESSION['pJ']['componentsRegisteredSession']=time();
	$pJ['componentsRegistered']=q("SELECT LCASE(c.Handle) AS handle, 0 AS compiled, tc.Settings, c.Location, c.ComponentFile, c.Description FROM gen_templates t, gen_TemplatesComponents tc, gen_components c WHERE t.ID=tc.Templates_ID AND tc.Components_ID=c.ID AND t.ID=$Templates_ID", O_ARRAY_ASSOC);
	#prn($pJ,1);
}
//--------------------------------------------
if($passnode){
	$gen_nodes=q("SELECT n.*, s.Settings FROM gen_nodes n LEFT JOIN gen_nodes_settings s ON n.ID=s.Nodes_ID WHERE n.ID=$passnode", O_ROW);
}else
//codeblock 2213411 - moved out so we can get the page node even if in a page group like products.php does
if($thispage=='index'){
	if($gen_nodes=q("SELECT n.*, s.Settings FROM gen_nodes n LEFT JOIN gen_nodes_settings s ON n.ID=s.Nodes_ID WHERE SystemName='{root_website_page}'", O_ROW)){
		//OK
	}else{
		$gen_nodes['ID']=q("INSERT INTO gen_nodes SET
		SystemName='{root_website_page}',
		Name='Home',
		Type='Object',
		Category='Website Page',
		CreateDate=NOW()/*,
		Creator='".($_SESSION['admin']['userName'] ? $_SESSION['admin']['userName'] : ($_SESSION['systemUserName'] ? $_SESSION['systemUserName'] : ($PHP_AUTH_USER ? $PHP_AUTH_USER : $acct)))."'*/", O_INSERTID);
		$gen_nodes['Name']='Home';
		q("INSERT INTO gen_nodes_settings SET Nodes_ID=".$gen_nodes['ID']);
	}
}else{
	$gen_nodes=q("SELECT n.*, s.Settings FROM gen_nodes n LEFT JOIN gen_nodes_settings s ON n.ID=s.Nodes_ID WHERE n.Type='Object' AND 
	REPLACE(REPLACE('$thispage','-',''),' ','')=
	REPLACE(REPLACE(n.Name,		'-',''),' ','') AND Active=$activeLevel", O_ROW);
}
if($gen_nodes){
	//get node id
	$thisnode=$gen_nodes['ID'];
	if($gen_nodes['Settings']){
		$Settings=unserialize(base64_decode($gen_nodes['Settings']));
	}
}
if($thisfolder){
	if(count($consoleEmbeddedModules))
	foreach($consoleEmbeddedModules as $n=>$v){
		$m=$v['moduleAdminSettings'];
		if($thisfolder==$m['handle'] || @in_array(strtolower($thisfolder),$m['handleAliases'])){
			//call the component which will declare the $$ regions for the template
			if(file_exists($JULIET_COMPONENT_ROOT.'/'.$acct.'.'.($m['componentPage'] ? $m['componentPage'] : $m['handle'].'.php'))){
				require($JULIET_COMPONENT_ROOT.'/'.$acct.'.'.($m['componentPage'] ? $m['componentPage'] : $m['handle'].'.php'));
			}else{
				require($JULIET_COMPONENT_ROOT.'/'.($m['componentPage'] ? $m['componentPage'] : $m['handle'].'.php'));
			}
			break;
		}
	}
}else{
	//codeblock 2213411 was here
}

if($Settings['ViewLoggedIn'] && !$_SESSION['cnx'][$MASTER_USERNAME]['identity']){
	header('Location: /cgi/usemod?src='.urlencode('/'.($thisfolder?$thisfolder.'/':'').($thissubfolder?$thissubfolder.'/':'').$thispage.($QUERY_STRING?'?'.$QUERY_STRING:'')));
}
if($a=trim($Settings['BlockSuppressionOverride'])){
	$a=explode(',',$a);
	foreach($a as $n=>$v){
		if(!trim($v)){
			unset($a[$n]);
			continue;
		}
		$a[$n]=strtolower($v);
	}
	$Settings['BlockSuppressionOverride']=$a;
}

if($pJInBlogMode){
	require($_SERVER['DOCUMENT_ROOT'].'/components-juliet/articles.php');
	if(rand(1,10)==5)mail($developerEmail, 'articles still has no control; Error file '.__FILE__.', line '.__LINE__,get_globals('articles still has no control; need ability to configure as with cgi, and there could be many articles and we need to be able to "hook into" this with gen_nodes - work this out'),$fromHdrBugs);
}else{
	//note use of goto to switch processing order
	if(!$pJNodeProcessingOrder)$pJNodeProcessingOrder='ComponentLocation';

	if($pJNodeProcessingOrder=='PageType')goto pagetype;

	componentlocation:
	
	if($gen_nodes['ComponentLocation']>''){
		if(in_array($acct, array('cpm161'))){ /* legacy for xxxpennington's cakesxxx and ACF */
			mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals('we have the new version of juliet template legacy-ing a method for cpm161 - fix them and remove this'),$fromHdrBugs);
			if(file_exists($JULIET_COMPONENT_ROOT.'/'.$acct.'.'.$gen_nodes['ComponentLocation'].'.php')){
				require($JULIET_COMPONENT_ROOT.'/'.$acct.'.'.$gen_nodes['ComponentLocation'].'.php');
			}else{
				require($JULIET_COMPONENT_ROOT.'/'.$gen_nodes['ComponentLocation'].'.php');
			}
		}else{
			ob_start();
			eval(' ?>'.$gen_nodes['ComponentLocation'].'<?php ');
			$err=ob_get_contents();
			ob_end_clean();
			if($err){			
				mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err),$fromHdrBugs);
			}
		}
	}

	if($pJNodeProcessingOrder=='PageType')goto endprocessingorder;
	pagetype:
	if($a=$gen_nodes['PageType']){
		$a=explode(':',$a);
		//2011-08-14: here we do something different - require the equivalent component AND usurp the thispage value
		if(!$passnode)$pJDerivedThispage=$a[1];
		if(file_exists($JULIET_COMPONENT_ROOT.'/'.$acct.'.'.$a[0].'.php')){
			require_once($JULIET_COMPONENT_ROOT.'/'.$acct.'.'.$a[0].'.php');
		}else{
			require_once($JULIET_COMPONENT_ROOT.'/'.$a[0].'.php');
		}
	}
	
	if($pJNodeProcessingOrder=='PageType')goto componentlocation;
	endprocessingorder:
}


//pJCSSIncludedFile
ob_start();
if(!($pJulietCSSCustomFile=file_exists($_SERVER['DOCUMENT_ROOT'].'/site-local/'.$acct.'.'.$templateName.'.css'))){
	//create from daughter css sheet
	$pJulietNewCSSCreated=copy($_SERVER['DOCUMENT_ROOT'].'/site-local/'.$templateName.'.css', $_SERVER['DOCUMENT_ROOT'].'/site-local/'.$acct.'.'.$templateName.'.css');
}
if(!$pJulietCSSCustomFile && !$pJulietNewCSSCreated){
	?><link href="/site-local/<?php echo $pJCSSIncludedFile=($templateName.'.css')?>" type="text/css" rel="stylesheet" /><?php 
}else{ 
	?><link href="/site-local/<?php echo $pJCSSIncludedFile=($acct.'.'.$templateName.'.css');?>" type="text/css" rel="stylesheet" /><?php 
}
$f=$_SERVER['DOCUMENT_ROOT'].'/site-local/'.$acct.'.'.$templateName.'.global.css';
if(file_exists($f)){
	echo "\n";
	?><link href="/site-local/<?php echo $acct.'.'.$templateName.'.global.css';?>" type="text/css" rel="stylesheet" /><?php
}
if($pJLocalCSSLinks)
foreach($pJLocalCSSLinks as $n=>$v){
	echo is_int($n) ? '' : "\n<!-- ".'CSS Link: '.$n." -->\n";
	?><link href="<?php echo $v;?>" type="text/css" rel="stylesheet" /><?php
}
$pJCSSLink=ob_get_contents();
ob_end_clean();

if(!$pJModalInclusion){ //----------------------- begin walrus -------------------------

if(!$thisfolder && ($thispage=='admin.php' || $thispage=='admin')){
	if($logout=='1'){
		unset($_SESSION['special'][$MASTER_DATABASE]['adminMode']);
		header('Location: '.$src);
		?>
		redirecting..
		<script language="javascript" type="text/javascript">
		window.location='<?php echo $src?>';
		</script><?php
		exit;
	}else if($UN==$MASTER_USERNAME && $PW==$MASTER_PASSWORD){
		$_SESSION['special'][$MASTER_DATABASE]['adminMode']=($_COOKIE['setAdminMode'] ? $_COOKIE['setAdminMode'] : 2);
		$location=($src ? $src : '/');
		header('Location: '.$location);
		?><script>window.location='<?php echo $location?>'</script><?php
		exit;
	}else if(strlen($UN.$PW)){
		$error=true;
	}
}else if(!$thisfolder && ($thispage=='products.php' || $thispage=='products')){
	if($adminMode && $_GET['Priority_y']){
		$dir=($Priority_y<9?1:-1);
		$thiscategory=q("SELECT ID FROM finan_items WHERE Function='".$Function."' AND Function!='' AND SubFunction='".$SubFunction."' AND SubFunction!='' ORDER BY Priority ASC", O_COL);
		$max=q("SELECT MAX(Priority) FROM finan_items WHERE Function='".$Function."' AND Function!='' AND SubFunction='".$SubFunction."' AND SubFunction!=''", O_VALUE);
	
		$current=q("SELECT Priority FROM finan_items WHERE ID=$ID", O_VALUE);
		if($dir==1){
			if($absolute){
				q("UPDATE finan_items SET Priority=Priority+1 WHERE Priority<=$current AND Function='".$Function."' AND Function!='' AND SubFunction='".$SubFunction."' AND SubFunction!=''");
				q("UPDATE finan_items SET Priority=1 WHERE ID=$ID");
			}else{
				q("UPDATE finan_items SET Priority=Priority+1 WHERE Priority+1=$current AND Function='".$Function."' AND Function!='' AND SubFunction='".$SubFunction."' AND SubFunction!=''");
				q("UPDATE finan_items SET Priority=$current-1 WHERE ID=$ID");
			}
		}else{
			if($absolute){
				q("UPDATE finan_items SET Priority=Priority-1 WHERE Priority>=$current AND Function='".$Function."' AND Function!='' AND SubFunction='".$SubFunction."' AND SubFunction!=''");
				q("UPDATE finan_items SET Priority=$max WHERE ID=$ID");
			}else{
				q("UPDATE finan_items SET Priority=Priority-1 WHERE Priority-1=$current AND Function='".$Function."' AND Function!='' AND SubFunction='".$SubFunction."' AND SubFunction!=''");
				q("UPDATE finan_items SET Priority=$current+1 WHERE ID=$ID");
			}
		}
		//re-index as needed
		$min=q("SELECT MIN(Priority) FROM finan_items WHERE Function='".$Function."' AND Function!='' AND SubFunction='".$SubFunction."' AND SubFunction!=''", O_VALUE);
		if($min==0){
			q("UPDATE finan_items SET Priority=Priority+1 WHERE Function='".$Function."' AND Function!='' AND SubFunction='".$SubFunction."' AND SubFunction!=''");
		}
		header('Location: products.php?Function='.urlencode(stripslashes($Function)).'&SubFunction='.urlencode(stripslashes($SubFunction)));
		exit;
	}
}else if(!$thisfolder && ($thispage=='search.php' || $thispage=='search')){
	if(!$q){
		header('Location: /');
		exit;
	}
	//this is the weighting of each part of the record proportionately
	$nodeRanks=array(
		'Name'=>3,
		'Description'=>1,
		'LongDescription'=>1,
		'Keywords'=>7,
		'Category'=>1,
		'SubCategory'=>1,
		'SKU'=>2
	);
	$nodeEvaluators=array(
		'Keywords'=>'search_precedence'
	);
	$q=trim(preg_replace('/\s+/',' ',$q));
	$qa=explode(' ',$q);
	if(!function_exists('search_precedence'))require($FUNCTION_ROOT.'/function_search_suite_v100.php');
	
	if($a=q("SELECT * FROM finan_items WHERE
		(
		Name LIKE '%$q%' OR 
		Description LIKE '%$q%' OR 
		LongDescription LIKE '%$q%' OR 
		/* note split to catch any word in keywords, ranking done later */
		Keywords LIKE '%".implode("%' OR Keywords LIKE '%",$qa)."%' OR 
		Category LIKE '%$q%' OR 
		SubCategory LIKE '%$q%' OR 
		SKU LIKE '%$q%'
		) AND Active=1", O_ARRAY)){
		$i=0;
		foreach($a as $v){
			/*
			note: need to consider the length of the search, rank the locations the item is found
			*/
			$relevance=array();//set to zero
			foreach($nodeRanks as $field=>$weight){
				if(!trim($v[$field]))continue;
				if($function = $nodeEvaluators[$field]){
					$relevance[$field] = $weight * $function($q, $v[$field]);
				}else if(strtolower(stripslashes($q)) == strtolower($v[$field])){
					//exact match
					$relevance[$field] = $weight * 1;
				}else if(stristr(strtolower($v[$field]), strtolower(stripslashes($q)))){
					//exact pattern in field
					$relevance[$field] = $weight * .75;
				}
			}
			//now build the array - handle duplicates.  The $crit array will be used to handle
			$i++;
			$searchResults[$i]=array_merge($v,array('relevance'=>array_sum($relevance), 'relevanceSummary'=>$relevance));
		}
		//resort the array
		$searchResults=subkey_sort($searchResults,'relevance', array(
			'sortType'=>'standard',
			'reindex'=>true,
			'sort'=>'desc',
		));
		//we handle range in post-eval of the return
		if(!$position)$position=1;
		if(!$batch)$batch=30;
		$qq=get_navstats(count($searchResults), $position, $batch);
		if(count($searchResults)){
			$_SESSION['special']['searchQuery']=$QUERY_STRING;
		}else{
			$_SESSION['special']['searchQuery']='reset=1';
		}
	}
}

//2012-05-06: buffer the document, but don't bother flushing unless an on-the-fly component needs to access the document <head>
ob_start('pJ_modify_document_callback'); //'pJ_modify_document_callback'
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php if(file_exists($_SERVER['DOCUMENT_ROOT'].'/favicon.ico')){ ?>
<link rel="shortcut icon" type="image/ico" href="/favicon.ico" />
<?php } ?>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo $headRegionTitle ? h($headRegionTitle) : metatags_i1('title');?></title>
<?php echo metatags_i1('meta');?>
<link href="/Library/cssreset01.css" type="text/css" rel="stylesheet" />
<?php
echo $pJCSSLink;
echo "\n";
?><style type="text/css">
<?php
if($pJLocalCSS)
foreach($pJLocalCSS as $n=>$v){
	?>/* ----------- Module CSS <?php echo $n;?> ------------ */<?php echo "\n";
	echo trim($v)."\n\n";
	unset($pJLocalCSS[$n]);
}
echo $pJLocalCSSPlaceholder='/* -- on-the-fly pJLocalCSS placeholder -- */'."\n";
if($Settings['CustomCSS']){
	?>/* ----------- Custom CSS per page ---------------- */<?php echo "\n";
	echo $Settings['CustomCSS'];
}
?>
</style>


<script language="javascript" type="text/javascript" src="/Library/js/jquery.js"></script>
<script src="/Library/js/global_04_i1.js" language="javascript" type="text/javascript"></script>
<script src="/Library/js/common_04_i1.js" language="javascript" type="text/javascript"></script>
<script src="/Library/js/forms_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/loader_04_i1.js" language="JavaScript" type="text/javascript"></script>
<?php if($pJulietBalanceColumns){ ?>
<script src="/Library/js/matching_columns_m_v100.js" language="JavaScript" type="text/javascript"></script>
<?php } ?>
<script language="JavaScript" type="text/javascript">
/* periwinkle coding */
var thispage='<?php echo $thispage?>';
var thisfolder='<?php echo $thisfolder?>';
<?php 
//2011-09-01 note the addition of thissubfolder which breaks apart thisfolder from now on
if($thissubfolder){ ?>var thissubfolder='<?php echo $thissubfolder;?>';<?php echo "\n"; } 
if($thisnode){ ?>var thisnode='<?php echo $thisnode;?>';<?php echo "\n"; } 
?>var browser='<?php echo $browser?>';
var ctime='<?php echo $ctime?>';
var PHPSESSID='<?php echo $PHPSESSID?>';
//for nav feature
var count='<?php echo $nullCount?>';
var ab='<?php echo $nullAbs?>';
CMSBEditorURL='cms3.11.php';
</script>
<?php
$f=$_SERVER['DOCUMENT_ROOT'].'/site-local/'.$acct.'.'.$templateName.'.global.js';
if(file_exists($f)){
	echo "\n";
	?><script language="javascript" type="text/javascript" src="/site-local/<?php echo $acct.'.'.$templateName.'.global.js';?>"></script><?php
}

//added 2011-09-21 for adding the cart region
if($invokeCartLayout){ ?><relatebaseheadarea /><?php }

?>
</head>
<?php ob_start();?>
<body>
<?php 
//set unique id for each page, plus classes
$out=ob_get_contents();
ob_end_clean();
$out=str_replace('<body>','<body id="'.$thispage.'">',$out);
if($gen_nodes['Class'] || $pJBodyClass){
	$str=' class="';
	$str.=$gen_nodes['Class'];
	if($gen_nodes['Class'] && $pJBodyClass)$str.=' ';
	if($pJBodyClass)$str.=implode(' ',$pJBodyClass);
	$str.='"';
	$out=str_replace('>',$str.'>',$out);
}
echo $out;
/* all "god-like" components for Juliet are in components-juliet and start with .settings */
if($adminMode)require($JULIET_COMPONENT_ROOT.'/.settings.toolbar.php');
?>
<div id="mainWrap" <?php pJCSS('mainWrap');?>><?php
//2012-04-16: this is somewhat hard-coded still
$pJCurrentContentRegion='mainWrap';
$pJEditability=$pJBlocks[$pJCurrentContentRegion]['settings']['editability'];


if(!$suppressWrappers['mainWrapSub']){ ?><div id="mainWrapSub" <?php pJCSS('mainWrapSub');?>><?php }


//---------------------------------------
/*
How do I get that this content is meant for the top region?
Maybe to it on a name basis? topRegion(Whatever) so "WHERE LIKE 'topRegion%'"
Loop through(for position)/output buffer gotten divs
Modify existing logic to be appropriate for database information.
*/




if($pJTopBlocks){ /* OR we can go with "I am in ADMIN_MODE_DESIGNER and let's just outlay the templateDefinedBlocks inside of mainWrap (still hard-coded)" */
	foreach($pJTopBlocks as $pJCurrentContentRegion=>$_bsr_v){

		//setting for level of editability
		$pJEditability=$pJBlocks[$pJCurrentContentRegion]['settings']['editability'];

		//pass on blank regions
		ob_start();
		if(isset($$pJCurrentContentRegion)){
			echo $$pJCurrentContentRegion;
		}else{
			if($_bsr_v['Name']!='topRegion'){
				?><div id="<?php echo $_bsr_v['Name']?>" <?php pJCSS($_bsr_v['Name']);?>><?php
			}
			$pJEditability=$pJBlocks[$pJCurrentContentRegion]['settings']['editability'];
            eval(' ?>'.$pJTopBlocks[$pJCurrentContentRegion]['Content'].'<?php ');
			if($_bsr_v['Name']!='topRegion'){
				?></div><?php
			}			
			$pJTopBlocks[$pJCurrentContentRegion]['Content']=ob_get_contents();
		}
		ob_end_clean();
	}
}

?><div id="topRegion"><?php
$pJEditability=$pJBlocks['topRegion']['settings']['editability'];
echo $pJTopBlocks['topRegion']['Content'];
/*

2011-07-18 this might not be the best place for it but it is here for now; and it is hardcoded

*/

$pJCurrentContentRegion='topRegion';

unset($str1,$str2);
//we declare the absolutely positioned elements 2nd
if(count($pJTopBlocks))
foreach($pJTopBlocks as $_bsr_n=>$_bsr_v){
	if($_bsr_n=='topRegion')continue;
	if($_bsr_v['Position']=='absolute'){
		$str2.=$_bsr_v['Content']."\n";
	}else{
		$str1.=$_bsr_v['Content']."\n";
	}
}
echo $str1.$str2;
?></div>
<div id="mainRegion" <?php pJCSS('mainRegion');?>>
	<?php
	$pJCurrentContentRegion='mainRegionIntro';
	if(pJ_suppress_block($pJCurrentContentRegion))goto mainRegionIntro_end;
	?>
	<div id="mainRegionIntro" <?php pJCSS($pJCurrentContentRegion);?>>
	<?php
	$pJEditability=$pJBlocks[$pJCurrentContentRegion]['settings']['editability'];
	
	if(isset($$pJCurrentContentRegion)){
		echo $$pJCurrentContentRegion;      
	}else if($pJBlocks[$pJCurrentContentRegion]['Content']){
		eval(' ?>'.$pJBlocks[$pJCurrentContentRegion]['Content'].'<?php ');
	}else{
		CMSB($pJCurrentContentRegion.'_1');
	} 
	?>
	</div><?php mainRegionIntro_end: //end block ?>
	<?php /*del 2013-07-16: pJWrap('mainRegionWide')*/?>
	<?php
	$pJCurrentContentRegion='mainRegionLeft';
	if(pJ_suppress_block($pJCurrentContentRegion))goto mainRegionLeft_end;
	?>
	<div id="mainRegionLeft" <?php pJCSS($pJCurrentContentRegion);?>>
		<?php
		$pJCurrentContentRegion='mainRegionLeftIntro';
		$pJEditability=$pJBlocks[$pJCurrentContentRegion]['settings']['editability'];
		?>
		<div id="mainRegionLeftIntro" <?php pJCSS($pJCurrentContentRegion);?>>
		<?php
		if(isset($$pJCurrentContentRegion)){
			echo $$pJCurrentContentRegion;      
		}else if($pJBlocks[$pJCurrentContentRegion]['Content']){
			eval(' ?>'.$pJBlocks[$pJCurrentContentRegion]['Content'].'<?php ');
		}else{
			CMSB($pJCurrentContentRegion.'_1');
		} 
		?>      
		</div>
		<div id="mainRegionLeftContent" <?php pJCSS('mainRegionLeftContent');?>>
		<?php
		$pJCurrentContentRegion='mainRegionLeftContent';
		$pJEditability=$pJBlocks[$pJCurrentContentRegion]['settings']['editability'];

		if(isset($$pJCurrentContentRegion)){
			echo $$pJCurrentContentRegion;      
		}else if($pJBlocks[$pJCurrentContentRegion]['Content']){
			eval(' ?>'.$pJBlocks[$pJCurrentContentRegion]['Content'].'<?php ');
		}else{			
			CMSB($pJCurrentContentRegion.'_1');
		} 
		?>      
		</div>
	</div>
	<?php mainRegionLeft_end: //end block?>
	<div id="mainRegionCenter" <?php pJCSS('mainRegionCenter');?>>
		<?php
		$pJCurrentContentRegion='mainRegionCenterIntro';
		if(pJ_suppress_block($pJCurrentContentRegion))goto mainRegionCenterIntro_end;
		$pJEditability=$pJBlocks[$pJCurrentContentRegion]['settings']['editability'];
		?>
		<div id="mainRegionCenterIntro" <?php pJCSS($pJCurrentContentRegion);?>>
		<?php
		if($invokeCartLayout){
			//nothing
		}else if(isset($$pJCurrentContentRegion)){
			echo $$pJCurrentContentRegion;      
		}else if($a=$pJBlocks[$pJCurrentContentRegion]['Content']){
			eval(' ?>'.$pJBlocks[$pJCurrentContentRegion]['Content'].'<?php ');
		}else{
			CMSB($pJCurrentContentRegion.'_1');
		} 
		?>      
		</div><?php mainRegionCenterIntro_end: //end block?>
		<div id="mainRegionCenterContent" <?php pJCSS('mainRegionCenterContent');?>>
			<?php

			//2013-07-23 relocate inset
			if($pJCenterContentInsetWide)ob_start();
			
			$pJCurrentContentRegion='mainRegionCenterContentInset';
			if(pJ_suppress_block($pJCurrentContentRegion))goto mainRegionCenterContentInset_end;
			$pJEditability=$pJBlocks[$pJCurrentContentRegion]['settings']['editability'];
			?>
			<div id="mainRegionCenterContentInset" <?php pJCSS($pJCurrentContentRegion);?>>
			<?php
			if($rtest==17)exit('test');
			if($invokeCartLayout){
				//nothing
			}else if(isset($$pJCurrentContentRegion)){
				echo $$pJCurrentContentRegion;      
			}else if($pJBlocks[$pJCurrentContentRegion]['Content']){
				eval(' ?>'.$pJBlocks[$pJCurrentContentRegion]['Content'].'<?php ');
			}else{
				if($test==17)echo '<!-- begin CMS -->';
				CMSB($pJCurrentContentRegion.'_1');
				if($test==17)echo '<!-- end CMS -->';
			} 
			?>      
			</div><?php mainRegionCenterContentInset_end: //end block?>
			<?php



			//2013-07-23 - buffer inset
			if($pJCenterContentInsetWide){
				$mainRegionCenterContentInset=ob_get_contents();
				ob_end_clean();
			}
			
			/* you always have to do this if you have an interspersed div that calls pJCSS.. */
			$pJCurrentContentRegion='mainRegionCenterContent';
			$pJEditability=$pJBlocks[$pJCurrentContentRegion]['settings']['editability'];

			if($invokeCartLayout){
				?><relatebasecartarea><?php
			}else if(isset($$pJCurrentContentRegion)){
#				if($test==17)exit('at 1');
				echo $$pJCurrentContentRegion;      
			}else if($pJBlocks[$pJCurrentContentRegion]['Content']){
				if($test==17)exit('at 2');
				eval(' ?>'.$pJBlocks[$pJCurrentContentRegion]['Content'].'<?php ');
			}else if($thisfolder=='' && ($thispage=='admin.php' || $thispage=='admin')){
				?><h1>Administrative Access</h1>
				<?php if($error){ ?>
				<div style="color:darkred;font-weight:bold;">Your username or password is incorrect</div>
				<?php } ?>
				<p>Enter your username and password:</p>
				<form name="form1" id="form1" method="post" action="admin.php">
					<input name="UN" type="text" id="UN" />
					<br />
					<input name="PW" type="password" id="PW" />
					<input type="hidden" name="src" value="<?php echo h(stripslashes($src));?>" id="src" />
					<br />
					<input type="submit" name="Submit" value="Sign In" />
				</form><?php
			}else{
					if($test==17)exit('asdf');
				CMSB($pJCurrentContentRegion.'_1');
			} 
			?>
		</div>
		<?php
		if($pJCenterContentInsetWide==1)echo $mainRegionCenterContentInset;
		?>
	</div>
	<?php
	if($pJCenterContentInsetWide==2)echo $mainRegionCenterContentInset;
	?>
	<div class="cb0"> </div>
</div>
<?php /*del 2013-07-16: pJWrap('mainRegionWide')*/?>
<?php if($pJBottomRegionWide)ob_start(); ?>
<div id="bottomRegion" <?php pJCSS('bottomRegion');?>>
	<div id="footer" <?php pJCSS('footer');?>><!-- now a div inside of bottomRegion -->
		<?php
		$pJCurrentContentRegion='footer';
		$pJEditability=$pJBlocks[$pJCurrentContentRegion]['settings']['editability'];

		if(isset($$pJCurrentContentRegion)){
			echo $$pJCurrentContentRegion;
		}else if($pJBlocks[$pJCurrentContentRegion]['Content']){
			eval(' ?>'.$pJBlocks[$pJCurrentContentRegion]['Content'].'<?php ');
		}else{
			if(file_exists($local=$JULIET_COMPONENT_ROOT.'/'.$acct.'.footer.php')){
				require($local);
			}else{
				require($JULIET_COMPONENT_ROOT.'/footer.php');
			}
		}
		?>
	</div>
</div>
<?php
if($pJBottomRegionWide){
	$_bottomRegion_=ob_get_contents();
	ob_end_clean();
}
if(!$suppressWrappers['mainWrapSub']){ ?></div><?php } ?>
<?php
//place bottomRegion between mainWrap and mainWrapSub
if($pJBottomRegionWide==1)echo $_bottomRegion_;
?>
</div><!-- end of mainWrap -->
<?php
//place bottomRegion entirely out of wraps
if($pJBottomRegionWide==2)echo $_bottomRegion_;
?>
<?php if($adminMode){ ?>
<div id="showTester" title="Javascript Tester" onClick="g('tester').style.display='block';">&nbsp;</div>
<div id="tester" >
	<a href="#" onClick="g('ctrlSection').style.display='block';return false;">Show Control Section</a><br />
	<textarea name="test" cols="65" rows="4" id="test">clear_form();</textarea><br />
	<input type="button" name="button" value="Test" onClick="jsEval(g('test').value);"><br />
	<textarea id="result" name="result" cols="65" rows="3" ></textarea>
</div>
<?php } ?>
<?php if(!$hideCtrlSection){ ?>
<div id="ctrlSection" style="display:none;">
	<iframe name="w1" src="/Library/js/blank.htm"></iframe>
	<iframe name="w2" src="<?php if($returnAction=='getDoc' && $document){
		//get the requested document
		echo '/index_01_exe.php?suppressPrintEnv=1&mode=getDoc&document='.$document;
	}else{
		echo '/Library/js/blank.htm';
	}?>"></iframe>
</div>
<?php } ?>
</body>
</html><?php

//2012-05-05 see above
ob_end_flush();


page_end();
}
//---------------------------------- end walrus -------------------------------
?>
