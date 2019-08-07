<?php
/*
2013-06-26: see bottom for default settings on function flags
*/
if(!function_exists('q'))
require_once($FUNCTION_ROOT.'/function_q_v120.php');
require_once($FUNCTION_ROOT.'/function_q_tools_v100.php');
require_once($FUNCTION_ROOT.'/function_prn.php');
require_once($FUNCTION_ROOT.'/function_misc_functions_v001.php');
require_once($FUNCTION_ROOT.'/function_xml_read_tags_v134.php');
if(!function_exists('sql_insert_update_generic'))
require_once($FUNCTION_ROOT.'/function_sql_insert_update_generic_v111.php');
require_once($FUNCTION_ROOT.'/function_sql_autoinc_text_v232.php');
require_once($FUNCTION_ROOT.'/function_sql_query_parser_v100.php');
require_once($FUNCTION_ROOT.'/function_array_transpose.php');
require_once($FUNCTION_ROOT.'/function_array_merge_accurate_v100.php');
require_once($FUNCTION_ROOT.'/function_quasi_resource_generic_v201.php');
require_once($FUNCTION_ROOT.'/function_replace_form_elements_v100.php');
if(!function_exists('enhanced_mail'))
require_once($FUNCTION_ROOT.'/function_enhanced_mail_v211.php');
require_once($FUNCTION_ROOT.'/function_navigate_v141a.php');
require_once($FUNCTION_ROOT.'/function_callback_v101.php');
require_once($FUNCTION_ROOT.'/function_t_v111.php');
require_once($FUNCTION_ROOT.'/function_get_navstats_v110.php');
require_once($FUNCTION_ROOT.'/function_parse_query_v200.php');
require_once($FUNCTION_ROOT.'/function_relatebase_dataobjects_settings_v100.php');
require_once($FUNCTION_ROOT.'/function_set_priority_v110.php');
require_once($FUNCTION_ROOT.'/function_mysql_declare_field_attributes_rtcs_v100.php');
require_once($FUNCTION_ROOT.'/function_mysql_declare_table_rtcs_v200.php');
require_once($FUNCTION_ROOT.'/function_rb_vars_v120.php');
require_once($FUNCTION_ROOT.'/function_get_table_indexes_v101.php');
require_once($FUNCTION_ROOT.'/function_get_file_assets_v100.php');
require_once($FUNCTION_ROOT.'/function_get_contents_v100.php');
require_once($FUNCTION_ROOT.'/group_tree_functions_v100.php');
require_once($FUNCTION_ROOT.'/group_text_functions_v100.php');
require_once($FUNCTION_ROOT.'/function_array_alter_table_v100.php');
if(!function_exists('subkey_sort'))
require_once($FUNCTION_ROOT.'/function_array_subkey_sort_v300.php');
require_once($FUNCTION_ROOT.'/group_dataset_functions_v100.php');
require_once($FUNCTION_ROOT.'/group_colors_v110.php');
require_once($FUNCTION_ROOT.'/function_is_logical_v100.php');
require_once($FUNCTION_ROOT.'/function_attach_download_v100.php');
require_once($FUNCTION_ROOT.'/function_tabs_enhanced_v300.php');
if(!function_exists('CMSB'))
require_once($FUNCTION_ROOT.'/function_CMSB_v311.php');

if(!function_exists('broken_integrity')){
	function broken_integrity(){
		global $fl, $ln, $developerEmail, $fromHdrBugs;
		mail($developerEmail,'Broken DB integrity on file='.$fl.', line='.$ln,get_globals(),$fromHdrBugs);
	}
}
if(!function_exists('year_trans')){
	function year_trans($x, $thresh=35, $oneMil=true){
		/* year must be between [one Millennium] and 3000 if 4 digits */
		if(preg_match('/^[0-9]{2}$/',$x)) return ($x>=$thresh ? '19' : '20').$x;
		if(preg_match('/^[0-9]{3}$/',$x)){
			if($oneMil) return '';
			return $x;
		}
		if(preg_match('/^[0-9]{4}$/',$x)) return $x;
		//no criteria matches
		return '';
	}
}
if(!function_exists('month_trans')){
	function month_trans($x, $mode='int'){
		$months[1]=array('jan','january','01',1);
		$months[2]=array('feb','febuary','02',2,'febr');
		$months[3]=array('mar','march','03',3);
		$months[4]=array('apr','april','04',4);
		$months[5]=array('may','may','05',5);
		$months[6]=array('jun','june','06',6);
		$months[7]=array('jul','july','07',7);
		$months[8]=array('aug','august','08',8);
		$months[9]=array('sep','september','09',9,'sept');
		$months[10]=array('oct','october','10',10);
		$months[11]=array('nov','november','11',11,'novem');
		$months[12]=array('dec','december','12',12,'decem');
		if(!$x)return 0;
		$x=trim(strtolower(str_replace('.','',$x)));
		foreach($months as $key=>$month){
			if(in_array($x,$month)){
				switch(true){
					case $mode=='int': return $key;
					case $mode=='Short': return strtoupper(substr($month[0],0,1)).substr($month[0],-2);
					case $mode=='SHORT': return strtoupper($month[0]);
					case $mode=='short': return $month[0];
					case $mode=='Long': return strtoupper(substr($month[1],0,1)).substr($month[1],1-strlen($month[1]));
					case $mode=='LONG': return strtoupper($month[1]);
					case $mode=='long': return $month[1];
					case $mode=='zerofill': return $month[2];
					default: echo '<strong>unrecognized month translation mode: int, Short, short, SHORT, Long, LONG, long, zerofill</strong>';
				}
			}
		}
	}
}
if(!function_exists('n')){
	//outputs
	define('intifpossible','_1',false);
	define('_intifpossible',1,false);
	define('blankifzero','_2',false);
	define('_blankifzero',2,false);
	
	function n(){
		$knownConstants=array('_1','_2');
		foreach(func_get_args() as $v){
			if(in_array($v,$knownConstants)){
				$constants[]=(int) str_replace('_','',$v);
			}else if(is_numeric($v)){
				$outputs[]=$v;
			}
		}
		$out=$outputs[0];
		if(in_array(_intifpossible,$constants)){
			$out=preg_replace('/0+$/','',$out);
			$out=preg_replace('/\.$/','',$out);
		}
		if(in_array(_blankifzero,$constants)){
			if(!$out)$out='';
		}
		return $out;
	}
}

$qx['defCnxMethod']=C_MASTER;
$qx['useRemediation']=true;
$enhanced_mail['logmail']=true; 

?>