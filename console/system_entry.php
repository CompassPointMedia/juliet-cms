<?php
/*
pulled over from AMS
*/ 
if($mode=='updateObject' || $mode=='insertObject' || $mode=='deleteObject'){
	//------------------------ codeblock 1015037 ----------------------------------------
	//a few security items
	unset($tableSettings,$profileSettings,$raw);
	
	//change these first vars and the queries for each instance
	if(!$object)error_alert('object variable is required');
	$objectFields=q("EXPLAIN $object", O_ARRAY);
	foreach($objectFields as $n=>$v){
		if($v['Key']=='PRI')$recordPKField[$v['Field']]=$v['Field'];
		//first non-numeric field = default sorter
		if(!$sorter && preg_match('/(char|varchar)/',$v['Type']))$sorter=$v['Field'];
		if(preg_match('/resourcetype/i',$v['Field']) && $v['NULL']=='YES')$quasiResourceTypeField=$v['Field'];
		if(preg_match('/resourcetoken/i',$v['Field']))$quasiResourceTokenField=$v['Field'];
		if(preg_match('/sessionkey/i',$v['Field']))$sessionKeyField=$v['Field'];
	
		if(preg_match('/creator/i',$v['Field']))$creatorField=$v['Field'];
		if(preg_match('/createdate/i',$v['Field']))$createDateField=$v['Field'];
	}
	
	if($system_tables=q("SELECT * FROM system_tables WHERE SystemName='$object'", O_ROW)){
		if(strlen($system_tables['Settings'])>7)$tableSettings=unserialize(base64_decode($system_tables['Settings']));
	}else{
		//by calling this script we are registering the table
		$system_tables['ID']=q("INSERT INTO system_tables SET
		SystemName='$object',
		Name='$object',
		KeyField='$recordPKField',
		Description='Table registered by system_entry',
		Type='table'",O_INSERTID);
	}
	end(explode('/',__FILE__))=='bais_01_exe.php' ? (!$identifier?error_alert('identifier variable is required'):'') : (!$identifier ? $identifier='default' : '');
	if($system_profiles=q("SELECT * FROM system_profiles WHERE Tables_ID='".$system_tables['ID']."' AND Identifier='$identifier'", O_ROW)){
		if(strlen($system_profiles['Settings'])>7){
			$profileSettings=unserialize(base64_decode($system_profiles['Settings']));
			/*
			profile "settings" are stored in a node called 'raw'
			*/
			$profileSettings['raw'];
		}
	}else{
		//no need to insert a profile at this time
	}
	
	if(count($recordPKField)<>1)exit('table has a compound or missing primary key');
	//------------------------ end codeblock 1015037 ----------------------------------------
		
	/*
	identify date fields and number fields which will lose information (IL)
	
	*/
	//system error checking
	if(!$recordPKField){
		mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err='variable recordPKField is not present'),$fromHdrBugs);
		error_alert($err.', developer has been notified');
	}
	if($mode=='deleteObject'){
		error_alert('delete mode not developed');
	}
	if($mode==$insertMode && !$$recordPKField){
		unset($$recordPKField);
	}
	//note if quasi resource is present we always update
	$sql=sql_insert_update_generic($MASTER_DATABASE,$object,($quasiResourceTypeField ? $insertMode : $mode));
	prn($sql);
	ob_start();
	$n=q($sql,$mode==$insertMode?O_INSERTID:O_AFFECTEDROWS, ERR_ECHO);
	$err=ob_get_contents();
	ob_end_clean();
	if($err){
		prn($err);
		error_alert('Error in query');
	}
	if($mode==$insertMode){
		$GLOBALS[$recordPKField]=$n;
	}else{
		$affected_rows=$n;
	}
	if($cbPresent){
		$cbValue=$$recordPKField;
		if($n=$tableSettings['label']){
			$cbLabel=q("SELECT IF(TRIM($n)!='',TRIM($n),$recordPKField) FROM $object WHERE $recordPKField='".$recordPKField."'", O_VALUE);
		}else{
			foreach(($relations=sql_table_relationships()) as $n=>$v){
				if(strtolower($v['table'])==strtolower($object)){
					$cbLabel=q("SELECT ".$v['label']." FROM $object WHERE $recordPKField='".$$recordPKField."'", O_VALUE);
					break;
				}
			}
		}
		callback(array("useTryCatch"=>false));
	}
	if(preg_match('/insert|navig|kill/',$navMode)){
		$navigateCount=$count+($mode==$insertMode?1:0);
		$navigate=true;
	}
	
	//last step
	if(md5(stripslashes($profile['raw']))!==$profile['raw_hash']){
		//this piggybacks on top of the array if present
		ob_start();
		eval('$profileSettings[\'raw\']='.stripslashes($profile['raw']).';');
		$err=ob_get_contents();
		ob_end_clean();
		if($err){
			mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err='settings not updated; array is not valid php expression!'),$fromHdrBugs);
		}else{
			if($n=$profile['ID']){
	
				q("UPDATE system_profiles SET
				Settings='".base64_encode(serialize($profileSettings))."'
				WHERE ID=$n");
			}else{
				$profile['ID']=q("INSERT INTO system_profiles SET 
				Tables_ID='".$system_tables['ID']."',
				Identifier='$identifier',
				Type='data view',
				Name='data view',
				Settings='".base64_encode(serialize($profileSettings))."',
				CreateDate=NOW(),
				Creator='".sun()."'", O_INSERTID);
			}
			prn($qr);
		}
	}
	goto bypass;
}


//identify this script/GUI
$localSys['scriptID']='gen_access1';
$localSys['scriptVersion']='4.0';
$localSys['componentID']='main';
$localSys['pageLevel']=1;

//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');
//------------------------ Navbuttons head coding v1.50 -----------------------------

//------------------------ codeblock 1015037 ----------------------------------------
//a few security items
unset($tableSettings,$profileSettings,$raw);

//change these first vars and the queries for each instance
if(!$object)error_alert('object variable is required');
$objectFields=q("EXPLAIN $object", O_ARRAY);
foreach($objectFields as $n=>$v){
	if($v['Key']=='PRI')$recordPKField[$v['Field']]=$v['Field'];
	//first non-numeric field = default sorter
	if(!$sorter && preg_match('/(char|varchar)/',$v['Type']))$sorter=$v['Field'];
	if(preg_match('/resourcetype/i',$v['Field']) && $v['NULL']=='YES')$quasiResourceTypeField=$v['Field'];
	if(preg_match('/resourcetoken/i',$v['Field']))$quasiResourceTokenField=$v['Field'];
	if(preg_match('/sessionkey/i',$v['Field']))$sessionKeyField=$v['Field'];

	if(preg_match('/creator/i',$v['Field']))$creatorField=$v['Field'];
	if(preg_match('/createdate/i',$v['Field']))$createDateField=$v['Field'];
}

if($system_tables=q("SELECT * FROM system_tables WHERE SystemName='$object'", O_ROW)){
	if(strlen($system_tables['Settings'])>7)$tableSettings=unserialize(base64_decode($system_tables['Settings']));
}else{
	//by calling this script we are registering the table
	$system_tables['ID']=q("INSERT INTO system_tables SET
	SystemName='$object',
	Name='$object',
	KeyField='$recordPKField',
	Description='Table registered by system_entry',
	Type='table'",O_INSERTID);
}
end(explode('/',__FILE__))=='bais_01_exe.php' ? (!$identifier?error_alert('identifier variable is required'):'') : (!$identifier ? $identifier='default' : '');
if($system_profiles=q("SELECT * FROM system_profiles WHERE Tables_ID='".$system_tables['ID']."' AND Identifier='$identifier'", O_ROW)){
	if(strlen($system_profiles['Settings'])>7){
		$profileSettings=unserialize(base64_decode($system_profiles['Settings']));
		/*
		profile "settings" are stored in a node called 'raw'
		*/
		$profileSettings['raw'];
	}
}else{
	//no need to insert a profile at this time
}

if(count($recordPKField)<>1)exit('table has a compound or missing primary key');
//------------------------ end codeblock 1015037 ----------------------------------------

$recordPKField=current($recordPKField); //normally ID
if(!$sorter)$sorter=$recordPKField;

$navObject=$object.'_'.$recordPKField;
$updateMode='updateObject';
$insertMode='insertObject';
$deleteMode='deleteObject';
$insertType=1; //1=Save&New and Save&Close; 2 = Save and Save&New
#set these to 'disabled' if desired
$saveInitiallyDisabled='';
$saveAndNewInitiallyDisabled='';
$saveAndCloseInitiallyDisabled='';
//v1.4 change - some information about the coding
$navVer='1.50';
//v1.3 change - declare this function if you need to add parameters to the query string
$navQueryFunction='system_entry_nav()';
//v1.3 change - deny transiting from Next to New mode (shutting off ability to insert)
$denyNextToNew=false;
//declare the query to get the idSet or subset, ordered by desired sort order - note that if you're using quasi resources, then be sure and filter them out.
$ids=q("SELECT $recordPKField FROM $object WHERE 1 ".($quasiResourceField?"AND $quasiResourceField IS NOT NULL":'')." ORDER BY $sorter",O_COL);

$nullCount=count($ids);
$j=0;
if($nullCount){
	foreach($ids as $v){
		$j++; //starting value=1
		if($j==$abs+$nav || (isset($$navObject) && $$navObject==$v)){
			$nullAbs=$j;
			//get actual primary key if passage by abs+nav
			if(!$$navObject) $$navObject=$v;
			break;
		}
	}
}else{
	$nullAbs=1;
}
//note the coding to on ResourceToken - this will allow a submitted page to come up again if the user Refreshes the browser
if(
	strlen($$navObject) || 
	($quasiResourceTypeField && $ResourceToken && $$navObject=q("SELECT $recordPKField FROM $object WHERE $quasiResourceTokenField='$ResourceToken' AND $quasiResourceTypeField IS NOT NULL", O_VALUE))
	){
	//get the record for the object
	if($a=q("SELECT * FROM $object WHERE $recordPKField='".$$navObject."'",O_ROW)){
		$mode=$updateMode;
		@extract($a);
	}else{
		//object may have been deleted by another user, least perplexing approach is to present insert mode
		$mode=$insertMode;
		unset($$navObject);
		$nullAbs=$nullCount+1;
	}
}else{
	$mode=$insertMode;
	if($quasiResourceTypeField){
		//handle this
		$$navObject=quasi_resource_generic(
			$MASTER_DATABASE, 
			$object, 
			$ResourceToken, 
			$quasiResourceTypeField, 
			$sessionKeyField,
			$quasiResourceTokenField, 
			$recordPKField, 
			$creatorField, 
			$createDateField
		);
	}
	$nullAbs=$nullCount+1; //where we actually are right then
}
//--------------------------- end coding --------------------------------
gmicrotime('afterhead');
function form_field_presenter($options=array()){
	/*
	created 2012-10-10 for faster prototyping of forms, the objective being
		spit out equivalent php coding
		create the actual form field as HTML output if preferred
		set certain fields as the best possible type based on first, common sense presumptions and then an overlay
		eventually, have settings for a form which are entirely set by another form for the settings :)
	the common sense logic for presenting a form is as follows:
		* the primary key field should be a hidden field
		* tinyint fields should be checkboxes
		* enum or set fields.. s/e
		* char fields => imput[type=text]
		* text fields => textarea
		* cognates_id - look in database for tables with that last cognate - + bonus the ability to add new
			(do this even if multiple matches and notify this is a probationary field status) - how do we resolve this on-the-fly
		* all checkboxes have a zero-value in front of them
	the logic layer on top is as follows
		* for this view, we can skip these fields
		* we need a different table:label for a relational field
		* we need distinct values
		* we need optgroups by some condition
		* this should be a dropdown of lookup values
		* fields should be grouped into either a tabset or a control of a dropdown
		
		
	other parameters
		the view is set up or not set up
		if there are notices, warnings or even worse, errors, then this should appear
		(if there is no name of the view then the name = default)
		
	
	*/
	@extract($options);
	if(!$object)global $object;
	$cognate=strtolower(current(explode('_',$object)));
	if(!$object)exit('object variable is required');
	if(!$fields)global $objectFields;
	if($objectFields){
		$fields=$objectFields;
	}else{
		$fields=q("EXPLAIN $object", O_ARRAY);
	}
	if(!$recordPKField){
		foreach($fields as $n=>$v){
			if($v['Key']=='PRI')$recordPKField[$v['Field']]=$v['Field'];
			//first non-numeric field = default sorter
			if(!$sorter && preg_match('/(char|varchar)/',$v['Type']))$sorter=$v['Field'];
			if(preg_match('/resourcetype|resourcetoken/i',$v['Field']) && $v['NULL']=='YES')$quasiResourceField=$v['Field'];
		}
		if(count($recordPKField)<>1)exit('table has a compound or missing primary key');
		$recordPKField=current($recordPKField); //normally ID
		if(!$sorter)$sorter=$recordPKField;
	}
	global $mode;
	$mid=(strstr($mode,'update')?'um':'im');

	if(!$usedAttributes)$usedAttributes=array('id','class','style','onclick','onchange','value','rows','cols','cbtable');
	$relations=sql_table_relationships();
	?>
	<h2>table: <?php echo $object;?></h2>
	<table>
	<?php
	foreach($fields as $v){
		extract($v);
		$field=strtolower($Field);
		$flags		=$columns[$field]['flags'];
		$attributes	=$columns[$field]['attributes'];
		$calledType	=strtolower($flags['type']);
		if($calledType=='none')continue;
		$subtype='';
		/* 
		my concerns are about data type and esp. integer, float, or date types
		
		*/
		
		//get "natural type" of field based on database
		if($Key=='PRI'){
			$naturalType= 'hidden';
		}else if($n=$relations[$field]){
			$naturalType='select';
			$subtype='relation';
			
			if($r=$flags['relation']){
				//OK
				
			}
			
			//artificially set some attributes
			$attributes['cbtable']=($r['table']?$r['table']:$n['table']);
			$wparam='object='.($r['table']?$r['table']:$n['table']);
			$wname='l2_'.substr(md5($r['table']?$r['table']:$n['table']),0,6);
			$wsize=($r['wsize']?$r['wsize']:'700,700'); //for now until we get into settings better
			$attributes['onchange']='>>newOption(this, \'system_entry.php\', \''.$wname.'\', \''.$wsize.'\',\''.$wparam.'\');';
			
			
			//echo 'relation ('.$n['table'].':'.$n['label'].')';
		}else if(preg_match('/(createdate|creator|editdate|editor)/i',$Type,$m)){
			$naturalType='none';
			$m=$m[1];
		}else if(preg_match('/tinyint/',$Type)){
			$naturalType='checkbox';
		}else if(preg_match('/(char|varchar)/',$Type,$m)){
			$m=$m[1];
			$naturalType='input';
		}else if(preg_match('/text/',$Type)){
			$naturalType='textarea';
		}else if(preg_match('/(enum|set)/',$Type,$m)){
			$subtype=$m[1];
			$naturalType='select';
		}else{
			$naturalType='input';
		}
		
		//get called type and typeSrc from options
		if($calledType){
			$type=$calledType;
			$typeSrc='called';
		}else{
			$type=$naturalType;
			$typeSrc='natural';
		}
		if($typeSrc=='natural' && preg_match('/\b(createdate|creator|editdate|editor)$/',$field))continue;
		
		//if($field=='manufacturers_id')prn($attributes,1);

		//build a collection _attrib_ which is the attributes adjusted for current mode (insert|update)
		$possibleAttributes=array();
		$_attrib_=array();
		if(count($attributes))
		foreach($attributes as $o=>$w)$possibleAttributes[]=strtolower(current(explode(':',$o)));
		if(count($possibleAttributes))
		foreach($possibleAttributes as $w)$_attrib_[$w]=(isset($attributes[$w.':'.$mid]) ? $attributes[$w.':'.$mid] : $attributes[$w]);
		$output=array();
		
		/* special cases:
			* need to be able to handle field1, field2 for radios
			*/
		foreach($usedAttributes as $handle){
			//calculate default value
			unset($default);
			/* do calculations here on does this field need this attribute */
			switch($handle){
				case 'id':
					$default=$Field;
				break;
				case 'class':
				break;
				case 'style':
				break;
				case 'onclick':
				break;
				case 'onchange':
					$default='dChge(this);';
				break;
				case 'value':
					if(isset($GLOBALS[$Field])){
						$default=$GLOBALS[$Field];
					}else if($mode==$insertMode){
						$default=$Default;
					}
				break;
				case 'rows':
					if($type=='textarea')$default=3;
				break;
				case 'cols':
					if($type=='textarea')$default=45;
				break;
			}
			if(isset($_attrib_[$handle])){
				$str=$_attrib_[$handle];
				if(strlen($str)){
					if(substr($str,0,2)=='<<'){
						#before
						$output[$handle]=substr($str,2,strlen($str)-2).($default?' '. $default:'');
					}else if(substr($str,0,2)=='>>'){
						#after
						$output[$handle]=($default?$default.' ':'').substr($str,2,strlen($str)-2);
					}else{
						#replace
						$output[$handle]=$str;
					}
				}else{
					#delete (do not carry)
				}
			}else if(isset($default)){
				$output[$handle]=$default;
			}
		}

		//now convert the value as needed esp. date values
		if(!$flags['do_not_convert_value']){
		
		}
		
		if(strlen($output['value']))$output['value']=h($output['value']);
		
		$name=(
			$attributes['field_name'] ? 
			$attributes['field_name'] :
			($pre=$flags['array_wrapper']?$pre.'[':'') . $Field . ($flags['array_wrapper']?']':'') . ($flags['build_array']?'[]':'')
		);

		ob_start();
		switch($type){
			case 'input':
				?><input type="text" name="<?php echo $name;?>" <?php foreach($output as $o=>$w)echo $o.'="'.$w.'" ';?> /><?php
			break;
			case 'hidden':
				?><input type="hidden" name="<?php echo $name;?>" <?php foreach($output as $o=>$w)echo $o.'="'.$w.'" ';?> /><?php
			break;
			case 'textarea':
				$value=$output['value'];
				unset($output['value']);
				?><textarea name="<?php echo $name;?>" <?php foreach($output as $o=>$w)echo $o.'="'.$w.'" ';?>><?php echo $value;?></textarea><?php
			break;
			case 'select':
				//this is the big one
				$hasBlank=false;
				unset($_opt_);
				$value=$output['value'];
				unset($output['value']);
				if($subtype=='enum' || $subtype=='set'){
					$opt=rtrim(substr($Type,strlen($subtype)+2),')');
					$opt=substr($opt,0,strlen($opt)-1);
					$opt=explode('\',\'',$opt);
					//convert values
					foreach($opt as $o=>$w){
						if(!strlen($w))$hasBlank=true;
						$_opt_[$w]=h($w);
					}
					ksort($_opt_);
				}else if($subtype=='relation'){
					$_opt_=q("SELECT ".end(explode('_',$field)).", ".($relations[$field]['label'] ? $relations[$field]['label'] : end(explode('_',$field)))." FROM ".$relations[$field]['table']." ORDER BY ".($relations[$field]['label'] ? $relations[$field]['label'] : end(explode('_',$field))), O_COL_ASSOC);
				}else{
					$opt='';
				}
				?><select name="<?php echo $name;?>" <?php foreach($output as $o=>$w)echo $o.'="'.$w.'" ';?>><?php 
				if($mid=='im'){ /* good default behaviour but not a hard rule */
					?><option value="">&lt;Select..&gt;</option><?php 
				} 
				if(!empty($_opt_))
				foreach($_opt_ as $o=>$w){
					?><option value="<?php echo $o;?>" <?php echo $o==$value?'selected':''?>><?php echo $w;?></option><?php
				}
				if($subtype=='relation'){
					?><option value="{RBADDNEW}" style="background-color:thistle;">&lt;Add new entry..&gt;</option><?php
				}
				?>
				</select><?php
			break;
			case 'checkbox':
				?><input type="hidden" name="<?php echo $name;?>" value="0" /><input type="checkbox" name="<?php echo $name;?>" <?php foreach($output as $o=>$w)if($o!=='value')echo $o.'="'.$w.'" ';?><?php echo ' value="1"';?> <?php echo ($mode==$insertMode && $Default=='1') || ($output['value']=='1')?'checked':'';?> /><?php
			break;
			case 'radio':
			
			break;
			default:
				continue;
		}
		$out=ob_get_contents();
		ob_end_clean();

		if($type=='hidden'){
			$hiddenFields[$Field]=$out;
			continue;
		}

		?><tr>
		<td><?php echo preg_replace('/([a-z])([A-Z])/','$1 $2',$Field);?></td>
		<td><?php
		echo $out;
		?></td></tr><?php
	}
	?></table><?php
	if(!empty($hiddenFields))echo implode("\n",$hiddenFields);
}

$PageTitle='System Data Entry';
$suppressForm=false;

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/reports_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo dynamic_title($PageTitle.' - '.$AcctCompanyName);?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" href="/Library/css/cssreset01.css" type="text/css" />
<link rel="stylesheet" href="/site-local/gf5_simple.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/data_04_i1.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<style type="text/css">
</style>

<script language="JavaScript" type="text/javascript" src="/Library/js/jquery.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/jquery.bbq.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/global_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/common_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/forms_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/loader_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/dataobjects_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/site-local/local.js"></script>
<script language="JavaScript" type="text/javascript">
/* periwinkle coding */
var thispage='<?php echo $thispage?>';
var thisfolder='<?php echo $thisfolder?>';
var browser='<?php echo $browser?>';
var ctime='<?php echo $ctime?>';
var PHPSESSID='<?php echo $PHPSESSID?>';
//for nav feature
var count='<?php echo $nullCount?>';
var ab='<?php echo $nullAbs?>';

var isEscapable=2;

AddOnkeypressCommand('PropKeyPress(e)'); //if not declared already
function system_entry_nav(){
	var a,s='';
	(a=$.deparam.querystring());
	if(a.object)s+='&object='+a.object;
	if(a.identifier)s+='&identifier='+a.identifier;
	return s;
}
</script>


<!-- InstanceEndEditable -->
</head>

<body id="report">
<?php if(!$suppressForm){ ?>
<form action="resources/bais_01_exe.php" method="post" enctype="multipart/form-data" name="form1" target="w2" id="form1" onSubmit="return beginSubmit();">
<?php }?>
<div id="header"><!-- InstanceBeginEditable name="top_nav" -->

<div id="btns150" class="fr"><?php
ob_start();
?>
<input id="Previous" type="button" name="Submit" value="Previous" class="navButton_A" onClick="focus_nav(-1, '<?php echo ($mode==$insertMode?'insert':'update')?>', <?php echo $mode==$insertMode?1:0?>, 0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $nullAbs<=1?'disabled':''?> >
<?php
//Handle display of all buttons besides the Previous button
if($mode==$insertMode){
	if($insertType==2 /** advanced mode **/){
		//save
		?><input id="Save" type="button" name="Submit" value="Save" class="navButton_A" onClick="focus_nav(0,'insert',1,2<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveInitiallyDisabled?>><?php
	}
	//save and new - common to both modes
	?><input id="SaveAndNew" type="button" name="Submit" value="Save &amp; New" class="navButton_A" onClick="focus_nav(0,'insert', 1,1<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveAndNewInitiallyDisabled?>><?php
	if($insertType==1 /** basic mode **/){
		//save and close
		?><input id="SaveAndClose" type="button" name="Submit" value="Save &amp; Close" class="navButton_A" onClick="focus_nav(0,'insert', 1,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveAndCloseInitiallyDisabled?>><?php
	}
	?><input id="CancelInsert" type="button" name="Submit" value="Cancel" class="navButton_A" onClick="focus_nav_cxl('insert');"><?php
}else{
	//OK, and appropriate [next] button
	?><input id="OK" type="button" name="Submit" value="OK" class="navButton_A" onClick="focus_nav(0,'<?php echo $mode==$insertMode?'insert':'update'?>',0,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);">
	<input id="Next" type="button" name="Submit" value="Next" class="navButton_A" onClick="focus_nav(1,'<?php echo $mode==$insertMode?'insert':'update'?>',0,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $nullAbs>$nullCount || ($denyNextToNew && $nullAbs==$nullCount) ?'disabled':''?>><?php
}
$navbuttons=ob_get_contents();
ob_end_clean();
//2009-09-10 - change button names, set default as =submit, hide unused buttons
if(!$addRecordText)$addRecordText='Add Record';
if(!isset($navbuttonDefaultLogic))$navbuttonDefaultLogic=true;
if($navbuttonDefaultLogic){
	$navbuttonSetDefault=($mode==$insertMode?'SaveAndNew':'OK');
	if($cbSelect){
		$navbuttonOverrideLabel['SaveAndClose']=$addRecordText;
		$navbuttonHide=array(
			'Previous'=>true,
			'Save'=>true,
			'SaveAndNew'=>true,
			'Next'=>true,
			'OK'=>true
		);
	}
}
$navbuttonLabels=array(
	'Previous'		=>'Previous',
    'Save'			=>'Save',
    'SaveAndNew'	=>'Save &amp; New',
    'SaveAndClose'	=>'Save &amp; Close',
    'CancelInsert'	=>'Cancel',
    'OK'			=>'OK',
    'Next'			=>'Next'
);
foreach($navbuttonLabels as $n=>$v){
	if($navbuttonOverrideLabel[$n])
	$navbuttons=str_replace(
		'id="'.$n.'" type="button" name="Submit" value="'.$v.'"', 
		'id="'.$n.'" type="button" name="Submit" value="'.h($navbuttonOverrideLabel[$n]).'"', 
		$navbuttons
	);
	if($navbuttonHide[$n])
	$navbuttons=str_replace(
		'id="'.$n.'" type="button"',
		'id="'.$n.'" type="button" style="display:none;"',
		$navbuttons
	);
}
if($navbuttonSetDefault)$navbuttons=str_replace(
	'<input id="'.$navbuttonSetDefault.'" type="button"', 
	'<input id="'.$navbuttonSetDefault.'" type="submit"', 
	$navbuttons
);
echo $navbuttons;

// *note that we could go back to the same page the 'New Record' click appeared on, but there's major issues programmatically on whether it would shift because of the placement of the new record.
// *note that the primary key field is now included here to save time
?>
<input name="navVer" type="hidden" id="navVer" value="<?php echo $navVer?>" />
<?php if($ResourceToken){ ?>
<input name="ResourceToken" type="hidden" id="ResourceToken" value="<?php echo $ResourceToken?>" />
<?php } if($navQueryFunction){ ?>
<input type="hidden" name="navQueryFunction" id="navQueryFunction" value="<?php echo h($navQueryFunction);?>" />
<?php } ?>
<input name="object" type="hidden" id="object" value="<?php echo $object?>" />
<input name="navObject" type="hidden" id="navObject" value="<?php echo $navObject?>" />
<input name="nav" type="hidden" id="nav" />
<input name="navMode" type="hidden" id="navMode" value="" />
<input name="count" type="hidden" id="count" value="<?php echo $nullCount?>" />
<input name="abs" type="hidden" id="abs" value="<?php echo $nullAbs?>" />
<input name="insertMode" type="hidden" id="insertMode" value="<?php echo $insertMode?>" />
<input name="updateMode" type="hidden" id="updateMode" value="<?php echo $updateMode?>" />
<input name="deleteMode" type="hidden" id="deleteMode" value="<?php echo $deleteMode?>" />
<input name="mode" type="hidden" id="mode" value="<?php echo $mode?>" />
<input name="submode" type="hidden" id="submode" value="" />
<input name="componentID" type="hidden" id="componentID" value="<?php echo $localSys['componentID']?>" />
<input name="recordPKField" type="hidden" id="recordPKField" value="<?php echo $recordPKField?>" />
<input name="identifier" type="hidden" id="identifier" value="<?php echo $identifier?>" />
<?php
if(count($_REQUEST)){
	foreach($_REQUEST as $n=>$v){
		if(substr($n,0,2)=='cb'){
			if(!$setCBPresent){
				$setCBPresent=true;
				?><!-- callback fields automatically generated --><?php
				echo "\n";
				?><input name="cbPresent" id="cbPresent" value="1" type="hidden" /><?php
				echo "\n";
			}
			if(is_array($v)){
				foreach($v as $o=>$w){
					echo "\t\t";
					?><input name="<?php echo $n?>[<?php echo is_numeric($o)? '': $o?>]" id="<?php echo $n?>[<?php echo is_numeric($o)? '': $o?>]" type="hidden" value="<?php echo stripslashes($w)?>" /><?php
					echo "\n";
				}
			}else{
				echo "\t\t";
				?><input name="<?php echo $n?>" id="<?php echo $n?>" type="hidden" value="<?php echo stripslashes($v)?>" /><?php
				echo "\n";
			}
		}
	}
}
?><!-- end navbuttons 1.43 --></div>

<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->

<?php
ob_start();
//--------- buffer for settings ---------


$defaultSettings=array(
	'collection'=>array(
		'array_wrapper'=>'', /* this=data would change Category into data[Category] for example */
	),
	'columns'=>array(
		'sample_column'=>array(
			'attributes'=>array(
				/*
				example attributes: id, class, style, onclick (>> means put at end of default attributes, << means put before, and neither simply means replace or create the attribute)
				using class:im => somevalue means that this only applies on insertMode, same deal with class:um
				
				'value'=>'test',
				
				*/
			),
			'flags'=>array(
				'array_wrapper'=>'', /* if blank, it's still SET so this would remove any array_wrapper from general collection specs'*/
				'build_array'=>false, /* if true a [] will be added on the end */
				'type'=>'', /* values = NONE|text|textarea|select|radio|checkbox - will override any other logic, and NONE (case-insensitive) will simply skip the field */
				/*
				big areas
				*/
				'relation'=>array(), /* name-value pairs vs. what relations *might* come up with e.g. table=>finan_accounts_types, label=>Name */
				'distinct'=>'', /* true|false initially.  This is an interlocking attribute [1] */
				'counter'=>'', /* adds a character counter */
				'do_not_convert_value'=>false, /* if !true then will convert e.g. date value 2012-10-11 to 10/11/2012 for output */
				/*
				etc., etc., etc.
				*/
			),
		),
	),
);
/*
[1] not developed; the issue is priority in processing
*/

?>
<p class="gray">For now, copy and paste into Dreamweaver to edit.</p>
<a href="//relatebase.com/admin/base64_encode.php" title="this helps encode and decode this" onClick="return ow(this.href,'l1_base64','750,800');">Click here for the base64 encoder</a>
<br />

<input type="hidden" name="profile[ID]" id="profileID" value="<?php echo $system_profiles['ID'];?>" />
<textarea cols="65" rows="25" name="profile[raw]" id="profileRaw" onChange="dChge(this);"><?php
ob_start();
if(!empty($profileSettings['raw'])){
	print_r($profileSettings['raw']);
}else{
	var_export($defaultSettings);
}
$out=ob_get_contents();
$outMD5=md5($out);
ob_end_clean();
echo h($out);
?></textarea>
<input type="hidden" name="profile[raw_hash]" id="profileRawHash" value="<?php echo $outMD5?>" />
<?php
get_contents_tabsection('settings');

//---------- buffer for form ----------

form_field_presenter($profileSettings);

get_contents_tabsection('form');

tabs_enhanced(
	array(
		'form'=>array(
			'label'=>'Form'
		),
		'settings'=>array(
			'label'=>'Settings'
		),
	), 
	array(
		'location'=>'bottom',
		'aColor'=>'royalblue',
		'brdColor'=>'#aaa',
	)
);

?>

<!-- InstanceEndEditable --></div>
<div id="footer"><!-- InstanceBeginEditable name="footer" -->
&nbsp;&nbsp;
<!-- InstanceEndEditable --></div>
<?php if(!$suppressForm){ ?>
</form>
<?php }?>
<?php if(!$hideCtrlSection){ ?>
<div id="showTester" title="Javascript Tester" onClick="g('tester').style.display='block';">&nbsp;</div>
<div id="tester" >
	<a href="#" onClick="g('ctrlSection').style.display='block';return false;">Show Control Section</a><br />
	<textarea name="test" cols="65" rows="4" id="test">clear_form();</textarea><br />
	<input type="button" name="button" value="Test" onClick="jsEval(g('test').value);"><br />
	<textarea id="result" name="result" cols="65" rows="3" ></textarea>
</div>
<div id="ctrlSection" style="display:<?php echo $testModeC ? 'block':'none'?>">
	<iframe name="w1" src="/Library/blank.htm"></iframe>
	<iframe name="w2" src="/Library/blank.htm"></iframe>
	<iframe name="w3" src="/Library/blank.htm"></iframe>
	<iframe name="w4" src="/Library/blank.htm"></iframe>
</div>
<?php } ?>
</body>
<!-- InstanceEnd --></html><?php page_end();
//skip the page output
bypass:
?>