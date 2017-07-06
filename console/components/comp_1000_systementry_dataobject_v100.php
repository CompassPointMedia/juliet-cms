<?php
/*
2013-09-16
* datasetIncludeFile - must be contained in components folder
* datasetDeleteHandler - can be a name of a function in above file

2013-04-07
	//2013-04-07 needed for parse_query
	$datasetTable=$systemTable['SystemName'];
	if(!$recordPKField && $datasetPrimaryKey)$recordPKField=$datasetPrimaryKey; - in dataobject() array in settings
	after dataset_component_v200a.php handled form fields, process the updates just after the charles code
	systementry.assets.php -> group_systementry.php in a_f

2013-01-16
	* mode=deleteObjects updated. 
		In the r_<?php ..?> id's for various HTML portion, the format is str_replace(:,::,value)
		When we pass multiple, let's take two fields values - abba:baby and thanks, John:Engineer
		Result would be id="r_abba::baby:thanks, John::Engineer"
		If a second record had field values of Austin, TX and San Marcos, TX result would be id="r_Austin, TX:San Marcos, TX"
			then deleteObjects would look like this:
				<Objects_ID=abba::baby:thanks,, John::Engineer,Austin,, TX:San Marcos,, TX>
			so, colons were already doubled, but commas are also doubled so we can pass multiple objects
2012-12-15
VERY IMPORTANT: this is a generic dataobject component.  This and the focus view constitute the flagship of being able to build an application with no hard-coding; eliminate all of the various dataobject component files in my current aps by configuring them here and then exporting the code or calling it; and harmonize between the list view and the focus view systems I have had for 10 years.

*/
function systementry_focus($record){
	global $object,$datasetFocusPage,$datasetFocusViewDims,$datasetFocusObject,$recordPKField;
	if(!$datasetFocusPage)$datasetFocusPage='systementry.php';
	if(!$datasetFocusViewDims)$datasetFocusViewDims='800,700';
	if(!$datasetFocusObject)$datasetFocusObject=$object.'_'.$recordPKField[0];
	$href=$datasetFocusPage.'?'.'_Profiles_ID_='.$GLOBALS['_Profiles_ID_'].'&'.$datasetFocusObject.'='.$record[$recordPKField[0]];
	?><a href="<?php echo $href?>" title="View details" onclick="return ow(this.href,'l1_<?php echo $GLOBALS['dataset'];?>','<?php echo $datasetFocusViewDims;?>');"><img src="/images/i/edit2.gif" alt="edit" /></a><?php
}
require_once($FUNCTION_ROOT.'/group_systementry.php');

//translate variables
$_Profiles_ID_=(is_numeric($_Profiles_ID_) ? $_Profiles_ID_ : $profile['ID']);

if($_Profiles_ID_ && !($systemProfile=q("SELECT * FROM system_profiles WHERE ID=$_Profiles_ID_", O_ROW))){
	exit('Unable to locate profile');
}else if($object){
	if(!$identifier)$identifier='default';
	if($systemTable=q("SELECT * FROM system_tables WHERE SystemName='$object'", O_ROW)){
		//OK
	}else{
		//do we have any call to register table settings for this identifier->object?
		#add query and settings here
		$Settings='';
		$a=q("EXPLAIN $object", O_ARRAY);
		unset($KeyField);
		foreach($a as $v)if($v['Key']=='PRI')$KeyField[]=$v['Field'];
		if(!$KeyField)exit('Cannot register table, it has no primary key');
		
		//by calling this script we are registering the table
		$n=q("INSERT INTO system_tables SET
		SystemName='$object',
		Name='$object',
		Settings='$Settings',
		KeyField='".implode(',',$KeyField)."',
		Description='Table registered by systementry',
		Type='table'",O_INSERTID);
		
		//after edgar
		$updatePKFieldValue[$object]=true;
		$systemTable=array(
			'ID'=>$n,
			'SystemName'=>$object,
			'Name'=>$object,
			'Settings'=>$Settings,
		);
	}
	if($systemProfile=q("SELECT * FROM system_profiles WHERE Tables_ID='".$systemTable['ID']."' AND Identifier='$identifier'", O_ROW)){
		$_Profiles_ID_=$systemProfile['ID'];
	}else{
		//do we have any call to register profile settings for this identifier->object?
		#add query and settings here
		$Settings='';

		//for now, we are not creating settings for this view - although theoretically at some point the rec. pymts window itself could be completely defined by settings, and this page (rfm_payments.php) would just be systementry.php called with the _Profiles_ID_
		$_Profiles_ID_=q("INSERT INTO system_profiles SET 
		Tables_ID='".$systemTable['ID']."',
		Identifier='$identifier',
		Type='Data View',
		Settings='$Settings',
		CreateDate=NOW(),
		Creator='".sun()."'", O_INSERTID);
		$systemProfile=array(
			'ID'=>$n,
			'Tables_ID'=>$systemTable['ID'],
			'Identifier'=>$identifier,
			'Settings'=>$Settings,
		);
	}
}
if(!($systemTable=q("SELECT * FROM system_tables WHERE ID='".$systemProfile['Tables_ID']."'", O_ROW))){
	mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err='Found system_profiles but not system_tables'),$fromHdrBugs);
	error_alert($err.', developer has been notified');
}
//2013-04-07 needed for parse_query
$datasetTable=$systemTable['SystemName'];

//get both settings arrays
$tableSettings=(strlen($systemTable['Settings'])>7 ? unserialize(base64_decode($systemTable['Settings'])) : array());
$profileSettings=(strlen($systemProfile['Settings'])>7 ? unserialize(base64_decode($systemProfile['Settings'])) : array());
if($profileSettings['dataobject']['datasetIncludeFile']){
	if(file_exists($COMPONENT_ROOT.'/'.$profileSettings['dataobject']['datasetIncludeFile'])){
		ob_start();
		//this can do no HTML output
		require($COMPONENT_ROOT.'/'.$profileSettings['dataobject']['datasetIncludeFile']);
		$condition=ob_get_contents();
		ob_end_clean();
		if($condition)$err='required file '.$profileSettings['dataobject']['datasetIncludeFile'].' produced an error or HTML output, see $condition variable emailed for more information';
	}else{
		$err='systementry allows parameter datasetIncludeFile, which has been declared ('.$profileSettings['dataobject']['datasetIncludeFile'].') however it is missing in the /console/component folder';
	}
	if($err){
		mail($developerEmail, 'Required file missing in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err."\n\n\n"),$fromHdrBugs);
		exit($err);
	}
}

	$object=$systemTable['SystemName']; #translate
	//---------- begin edgar -------------
	$objectFields=q("EXPLAIN $object", O_ARRAY);
	foreach($objectFields as $n=>$v){
		if($v['Key']=='PRI')$recordPKField[]=$v['Field'];
		//first non-numeric field = default sorter
		if(!$sorter && preg_match('/(char|varchar)/',$v['Type']) && !preg_match('/createdate|creator|editdate|editor/i',$v['Field']))$sorter=$v['Field'];
		if(preg_match('/resourcetype/i',$v['Field']) && $v['Null']=='YES')$quasiResourceTypeField=$v['Field'];
		if(preg_match('/resourcetoken/i',$v['Field']))$quasiResourceTokenField=$v['Field'];
		if(preg_match('/sessionkey/i',$v['Field']))$sessionKeyField=$v['Field'];
	
		if(preg_match('/creator/i',$v['Field']))$creatorField=$v['Field'];
		if(preg_match('/createdate/i',$v['Field']))$createDateField=$v['Field'];
		$objectFields[strtolower($v['Field'])]=$v;
		unset($objectFields[$n]);
	}
	//------------ end edgar -------------
@extract($profileSettings['dataobject']);
if(!$recordPKField){
	if($datasetPrimaryKey){
		$recordPKField=$datasetPrimaryKey;
	}else if($systemTable['KeyField']){
		$recordPKField=explode(',',$systemTable['KeyField']);
	}
	if(!$recordPKField)exit('Unable to determine primary key!');
}
if(($mode=='insertObjects' || $mode=='updateObjects' || $mode=='deleteObjects') &&
	$submode!=='updateDatasetFilters'){
	/*
	what I have at this point:
		tableSettings
		profileSettings
		object
		objectFields
		systemTable
		systemProfile
	*/
	if($mode=='deleteObjects'){
		$comma=md5(rand(1,1000000));
		$colon=md5(rand(1,1000000));
		$Objects_ID=str_replace(',,',$comma,$Objects_ID);
		$Objects_ID=explode(',',$Objects_ID);
		if($fctn=$profileSettings['dataobject']['datasetDeleteHandler']){
			$fctn($Objects_ID);
		}else{
			foreach($Objects_ID as $n=>$v){
				$v=str_replace('::',$colon,$v);
				$v=str_replace($comma,',',$v);
				$v=explode(':',$v);
				$str=array();
				foreach($recordPKField as $o=>$w){
					$str[]='`'.$w.'`=\''.str_replace($colon,':',$v[$o]).'\'';
				}
				$sql="DELETE FROM $object WHERE ".implode(' AND ',$str)." LIMIT 1";
				prn($sql);
				#continue;
				
				q($sql);
				prn($qr);
			}
		}
		?><script language="javascript" type="text/javascript">
		<?php foreach($Objects_ID as $v){ ?>try{ window.parent.g('r_<?php echo str_replace($comma,',',str_replace($colon,':',$v));?>').style.display='none'; }catch(e){ }</script><?php }?>
		</script><?php
		eOK();
	}

	//---------------- begin charles ----------------
	if(md5(stripslashes($profile['raw']))!==$profile['raw_hash']){
		if(!$profile['EditDate'])error_alert('variable profile.EditDate is not passed and is required');
		if(!$profile['ID'])error_alert('variable profile.ID is now required');
		if(strtotime(q("SELECT EditDate FROM system_profiles WHERE ID=".$profile['ID'], O_VALUE))>strtotime($profile['EditDate']))error_alert('Profile values have been updated after the current page was sent from the server.  Complete any update or insert operations if possible, and then refresh this page');
		$profile['raw']=trim(stripslashes($profile['raw']));
		if(substr($profile['raw'],0,5)!=='array'){
			$profile['raw_adjusted']='array('."\n".$profile['raw']."\n".')';
		}else{
			$profile['raw_adjusted']=$profile['raw'];
		}
		ob_start();
		eval('$str='.$profile['raw_adjusted'].';');
		$err=ob_get_contents();
		ob_end_clean();
		echo $err;
		if($err){
			mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err='Settings not updated; the array is not valid PHP expression!'),$fromHdrBugs);
			error_alert('your settings raw coding contains a parse error, see the code',1);
		}else{
			$str['_raw_']=$profile['raw'];
			$str=base64_encode(serialize($str));
			if($n=$profile['ID']){
				$e=date('Y-m-d H:i:s');
				q("UPDATE system_profiles SET
				Settings='$str',
				EditDate='$e'
				WHERE ID=$n");
				if(!$suppressPrintEnv){
					?><script language="javascript" type="text/javascript">
					try{ window.parent.g('profile[EditDate]').value='<?php echo $e;?>'; }
					catch(e){ }
					</script><?php
				}
			}else{
				$profile['ID']=q("INSERT INTO system_profiles SET 
				Tables_ID='".$systemTable['ID']."',
				Identifier='$identifier',
				Type='Data View',
				Name='Table Profile',
				Settings='$str',
				CreateDate=NOW(),
				Creator='".sun()."'", O_INSERTID);
			}
			prn($qr);
		}
		if($submode=='updateProfileSettingsOnly')eOK();
	}
	//---------------- end charles ----------------
	
	/*
	2013-04-08
	now update or insert individual rows
	I have done most of these hundreds of times; this query is found in data imports for sure and probably others.  Key action items are:
	1. format data in, esp. dates and numbers (i.e. $50.00 should go in a float field, 10A in a time or datetime field)
	2. allow for a 3rd query for _v_clients_contacts_join, we have no fields from finan_ClientsContacts but if we did we'd need the 3rd query.
		the rules are right now:
		a. view created will have fields which have a prefix by the table
		b. there must be some relationship between tables in the view SQL
		c. datasetSections = array( Clients=>array(table:finan_clients,primary_key:ID), Contacts=>.. )
		d. handle editdate
		e. handle new, and createdate and creator for that as well
		f. this involves pulling the REAL table, and at this point I may not need all the fields I have.  The only thing I really need is some mapping of the cognates to the table
	*/
	if(!empty($data)){
		$data=array_transpose($data);
		foreach($data as $key=>$set){
			if(!$set['_change_'])continue;

			//1 or more queries present
			$queries=array();

			foreach($set as $field=>$v){
				if(preg_match('/^_change_$/',$field))continue;
				$f=explode('_',$field);
				$cog=$f[0];
				if(count($f)>1 && $datasetSections[$cog]){
					unset($f[0]);
					$field=implode('_',$f);
					//partial query
					$queries[$datasetSections[$cog]['table']][$field]=$v;
				}else{
					$queries[$datasetTable][$field]=$v;
				}
			}
			foreach($queries as $table=>$set){
				$sql='UPDATE '.$table.' SET ';
				foreach($set as $field=>$v){
					$sql.=$field.'='.(is_numeric($v) || (strtolower($v)=='null' && $interpretNull) ? '':'"').trim($v).(is_numeric($v) || (strtolower($v)=='null' && $interpretNull) ? '':'"').', ';
				}
				$sql=rtrim($sql,', ')."\n".'WHERE ';
				if($datasetSections){
					foreach($datasetSections as $section=>$v){
						if($table==$v['table']){
							$sql.=($v['primary_key']?$v['primary_key']:'ID').'=';
							//finally, get the value
							$sql.=$data[$key][$section.'_'.($v['primary_key']?$v['primary_key']:'ID')];
							break;
						}
					}
				}else{
					$where=array();
					foreach($recordPKField as $v){
						$where[]=$v.'='.(is_numeric($set[$v]) || (strtolower($set[$v])=='null' && $interpretNull) ? '':'"').trim($set[$v]).(is_numeric($set[$v]) || (strtolower($set[$v])=='null' && $interpretNull) ? '':'"');
					}
					$sql.=implode(' AND ',$where);
				}
				$updates[]=$sql;
				ob_start();
				q($sql,ERR_ECHO);
				if($err=ob_get_contents())$errors[]=$err;
				ob_end_clean();
			}
		}
	}
	?><script language="javascript" type="text/javascript">
	
	</script><?php
	error_alert(
	(count($updates)>count($errors) ? count($updates).' update'.(count($updates)==1?'':'s').' completed, ':'').
	count($errors).' error'.(count($errors)==1?'':'s')
	);
}
if(!$profileSettings['dataobject']){
	//initial setup
	/*
	datasetAutoBuild:
	GOALS FOR 1.0.02A:
		test and make delete, add, and sort work
		test and have changing columns get stored as before for now
		better initial layout
		BUGS:
			h() and hl_bg() are not working - make them go to jquery, along with doubleclick
	1.0.01
		* dataset, datasetGroup, datasetComponent, datasetQuery, datasetQueryValidation
		* columns by analysis of $objectFields - system fields pretty much excluded
		* focus view goes to same profile using datasetFocusViewDeviceFunction=systementry_focus
		* if table has an "Active" field, then active controls working, otherwise they are hidden
	*/
	$datasetAutoBuild='1.0.01';
	$profileSettings['dataobject']=array(
		'datasetAutoBuild'=>$datasetAutoBuild,
		'dataset'=>preg_replace('/^[a-z0-9]+_/i','',$systemTable['SystemName']),
		'datasetGroup'=>preg_replace('/^[a-z0-9]+_/i','',$systemTable['SystemName']),
		'datasetComponent'=>preg_replace('/^[a-z0-9]+_/i','',$systemTable['SystemName']).'List',
		'datasetQuery'=>'SELECT * FROM '.$systemTable['SystemName'].' WHERE '.($quasiResourceTypeField ? $quasiResourceTypeField.' IS NOT NULL' : 1),
		'datasetQueryValidation'=>md5($MASTER_PASSWORD),
		'datasetFile'=>end(explode('/',__FILE__)),
		'datasetFocusViewDeviceFunction'=>'systementry_focus',
	);
	$str=trim($profileSettings['_raw_']);
	if(preg_match('/^array\(/i',$str)){
		$str=trim(preg_replace('/^array\(/i','',$str));
		$str=trim(preg_replace('/\),*\s*$/','',$str));
		//we just hope there's a comma on the previous node :)
	}
	$str.="\n";
	$str.='\'dataobject\'=>array(
	/* added by root_systementry_list.php at '.date('n/j/Y \a\t g:iA').' on initial call-up */
	\'datasetAutoBuild\'>\''.$datasetAutoBuild.'\',
	\'dataset\'=>\''.preg_replace('/^[a-z0-9]+_/i','',$systemTable['SystemName']).'\',
	\'datasetGroup\'=>\''.preg_replace('/^[a-z0-9]+_/i','',$systemTable['SystemName']).'\',
	\'datasetComponent\'=>\''.preg_replace('/^[a-z0-9]+_/i','',$systemTable['SystemName']).'List\',
	\'datasetQuery\'=>\'SELECT * FROM '.$systemTable['SystemName'].' WHERE '.($quasiResourceTypeField ? $quasiResourceTypeField.' IS NOT NULL' : 1).'\',
	\'datasetQueryValidation\'=>\''.md5($MASTER_PASSWORD).'\',
	\'datasetFile\'=>\''.end(explode('/',__FILE__)).'\',
	\'datasetFocusViewDeviceFunction\'=>\'systementry_focus\',
	\'columns\'=>array('."\n";
	//now we recurse the fields, and set default types on values
	#prn($objectFields);
	foreach($objectFields as $n=>$v){
		//omit the following fields
		if(preg_match('/_*resourcetype|_*resourcetoken|_*sessionkey|_*tobeexported|_*exporter|_*exporter|_*exporttime|_*tobeprinted|_*creator|_*editdate|_*editor/i',$v['Field']))continue;
		//omit non-string primary keys (except for compounds)
		if($v['Key']=='PRI' && count($recordPKField)==1 && !strstr($v['Type'],'char'))continue;
		
		//active
		if(preg_match('/_*active$/i',$v['Field']))$activeControl=true;
		
		$header=preg_replace('/([a-z0-9])([A-Z])/','$1 $2',$v['Field']);
		$profileSettings['dataobject']['columns'][$v['Field']]=array(
			'header'=>$header,
		);
		$str.="\t\t'".$v['Field']."'=>array(\n";
		//example of a parameter
		$str.="\t\t\t'header'=>'".$header."',\n";
		$str.="\t\t),\n";
	}
	$str.="\t),\n";//close the columns parent
	
	if($activeControl){
		$profileSettings['dataobject']['datasetActiveUsage']=true;
		$profileSettings['dataobject']['datasetActiveHideControl']=false;
		
		$str.="\t'datasetActiveUsage'=>true,\n";
		$str.="\t'datasetActiveHideControl'=>false,\n";
	}else{
		$profileSettings['dataobject']['datasetActiveUsage']=false;
		$profileSettings['dataobject']['datasetActiveHideControl']=true;
		
		$str.="\t'datasetActiveUsage'=>false,\n";
		$str.="\t'datasetActiveHideControl'=>true,\n";
	}
	$str.='),';//close the dataobject
	$profileSettings['_raw_']=$str;
	//update - and backup for now because we are munching some code
	mail($developerEmail, 'Backup in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals('backup coding for system profile settings: '."\n\n".q("SELECT Settings FROM system_profiles WHERE ID=$_Profiles_ID_", O_VALUE)),$fromHdrBugs);
	q("UPDATE system_profiles SET Settings='".base64_encode(serialize($profileSettings))."' WHERE ID=$_Profiles_ID_");
	extract($profileSettings['dataobject']);
}

/*
2012-12-16:
remember that this is a "list view"; the coding here is similar in structure and concept to the snippet I have used for years for the focus view.
NOTE then that many vars set in place by the dataset components could be modified to synch up with focus view.  Here are the focus view vars:
	insertMode|updateMode|deleteMode
	navObject (Items_ID)
	ids (O_COL)
	nullCount
	nullAbs
	mode: insert|update|{delete|
	Not that many actually, with the null.. vars being used for nav only
*/
$updateMode='updateObjects';
$insertMode='insertObjects';
$deleteMode='deleteObjects'; //note PLURAL for these values
$mode=$updateMode; //by default for now


if(!$refreshComponentOnly){
	?><style type="text/css">
	</style>
	<script language="javascript" type="text/javascript">
	$(document).ready(function(e){
		$('#updateProfileSettings').click(function(e){
			var buffer=g('submode').value;
			g('submode').value='updateProfileSettingsOnly';
			g('form1').submit();
			g('submode').value=buffer;
		});


		var select_profile_confirm='You have started editing these records and will lose your changes.  Continue?';
		//------------- stanley -------------
		var select_profile_initial=g('select_profile').selectedIndex;
		$('#select_profile').change(function(){
			if(detectChange && !confirm(select_profile_confirm)){
				g('select_profile').selectedIndex=select_profile_initial;
				return false;
			}
			var s=(window.location+'').split('?');
			s[1]=s[1].replace(/_Profiles_ID_=[0-9]*&*/,'');
			s[1]='_Profiles_ID_='+this.value+(s[1].length?'&'+s[1]:'');
			window.location=s[0]+'?'+s[1];
		});
		//------------- end stanley -------------

		$('.dsfield').change(function(){
			$(this).closest('.dsrow').find('._change_').val(1);
			detectChange=1;
		});

		//NOTE that focus view has subtable coding which needs to be in here for when list view is a spreadsheet
	});
	function dChgeReset(){
		$('._change_').val('');
		detectChange=0;
	}
	<?php if($n=$profileSettings['dataobject']['datasetTitle']){ ?>
	document.title='<?php echo str_replace('\'','\\\'',$n);?>';
	<?php } ?>
	</script><?php
}
?>
<form action="resources/bais_01_exe.php" method="post" enctype="multipart/form-data" name="form1" id="form1" target="w2">
<?php
ob_start(); //begin tabs
?>
<p class="gray">For now, copy and paste into Dreamweaver to edit.</p>
<a href="//relatebase.com/admin/base64_encode.php" title="this helps encode and decode this" onClick="return ow(this.href,'l1_base64','750,800');">Click here for the base64 encoder</a>
<br />

<input type="hidden" name="profile[ID]" id="profileID" value="<?php echo $systemProfile['ID'];?>" />
<input type="hidden" name="profile[EditDate]" id="profile[EditDate]" value="<?php echo $systemProfile['EditDate'];?>" />
<textarea cols="80" rows="25" name="profile[raw]" id="profileRaw" onChange="dChge(this);" class="tabby"><?php
ob_start();
if(strlen($profileSettings['_raw_']))echo $profileSettings['_raw_'];
$out=ob_get_contents();
$outMD5=md5($out);
ob_end_clean();
echo h($out);
?></textarea>
<input type="hidden" name="profile[raw_hash]" id="profileRawHash" value="<?php echo $outMD5?>" />
<br />
<input type="button" name="Button" value="Update Profile Settings" id="updateProfileSettings" />
<?php
get_contents_tabsection('settings');


		?><div class="fr">Current profile: <?php
		$filterTable=false;
		//----------------------- begin stanley ------------------
		?><select id="select_profile" class="minimal"><?php
		/*
		this selects all profiles for a given table ($object)
		*/
		if($a=q("SELECT p.ID, p.Identifier, t.SystemName, t.Name AS `Table`, p.Name, p.Description FROM system_tables t JOIN system_profiles p ON t.ID=p.Tables_ID WHERE p.Type='Data View' ".($filterTable?"AND t.ID='".$systemTable['ID']."'":''), O_ARRAY)){
			$i=0;
			foreach($a as $n=>$v){
				$i++;
				if(!$filterTable && $buffer!=$v['SystemName']){
					if($i>1)echo '</optgroup>';
					?><optgroup label="<?php echo h($v['Table']);?>"><?php
				}
				?><option value="<?php echo $v['ID']?>" <?php echo $_Profiles_ID_==$v['ID']?'selected':''?>><?php echo $v['Name']?h($v['Name'] . ' ('.$v['Identifier'].')'):$v['Identifier'];?></option><?php
			}
			if(!$filterTable){ ?></optgroup><?php }
		}
		?></select><?php
		//------------------------- end stanley ---------------------
		?></div>
		
<h2 class="nullTop">table: <?php echo $object;?></h2>
<h3><?php echo ($systemProfile['Category'] ? $systemProfile['Category'].' - ':'').$systemProfile['Name'];?></h3>
<?php if($str=$systemProfile['Description']){ ?><p class="gray"><?php echo $str;?></p><?php } ?>

<?php
ob_start();
$useStatusFilterOptions=false;
$outputStatusFilterOptions=false;
$filterGadgetHideCSS=true;
$filterGadgetHideJS=true;
$filterGadgetCSSInternal=true;
$filterGadgetJSInternal=true;
//$filterGadgetPassthroughFields=array('_Profiles_ID_'); - not used because _Profiles_ID_ is part of the dataobject

//most recent setting 2012-12-22
$filterGadgetSuppressForm=true;
require($MASTER_COMPONENT_ROOT.'/comp_01_filtergadget_v200a.php');
if(false){
	/* 2012-12-21: come back on these; priority would be:
	export CSV
	print full texts (just like print invoice) - make sure to have tbody and thead and etc. for multiple pages
	email as CSV
	email as PDF
	export as IIF
	upload records to this table
	export hierarchical view
	{future:configure export}
	*/
	?>
	<div class="frb">
		<a href="<?php echo $datasetFocusPage?>?cbFunction=refreshComponent&cbParam=fixed:<?php echo $datasetComponent?>" onclick="return <?php echo $datasetAddObjectJSFunction ? $datasetAddObjectJSFunction : 'add'.$dataset.'()'?>"><img src="/images/i/s/hlw-25x25-9EA9B4/plus.png" style="margin-top:7px;" />&nbsp;Add <?php echo strtolower($datasetWord);?></a>&nbsp;
	</div>
	<!-- options button -->
	<div class="frb">
		<a id="optionsItems" title="View Options" href="javascript:;" onclick="hidemenuie5(event,1);showmenuie5(event,1)" oncontextmenu="return false;"><img src="/images/i/options1.png" alt="Foster Items" width="32" height="32" /> Options</a>&nbsp;&nbsp;
	</div>
	<!-- reports button -->
	<div class="frb">
		<a id="reportsItems" title="View Foster Items Reports" href="javascript:;" onclick="hidemenuie5(event,1);showmenuie5(event,1)" oncontextmenu="return false;"><img src="/images/i/addr_26x27.gif" width="26" height="27" style="margin-top:5px;" /> Reports</a>
	</div>
	<!-- context menus -->
	<div id="childOptions" class="menuskin1" style="z-index:1000;" onMouseOver="hlght2(event)" onMouseOut="llght2(event)" onclick="executemenuie5(event)" precalculated="childoptionsPre()">
		<div id="cho1" default="1" style="font-weight:900;" class="menuitems" command="openItem()" status="Edit this item">
		Edit Item</div>
		<div id="cho2" class="menuitems" command="itemAction(event, 'delete');" status="Delete this item">
		Delete</div>
	</div>
	<div id="optionsItemsMenu" class="menuskin1" style="z-index:1000;width:225px;" onMouseOver="hlght2(event)" onMouseOut="llght2(event)" onclick="executemenuie5(event)" precalculated="optionsItems();">
		<div id="oh01" style="font-weight:900;" class="menuitems" command="addItem();" status="Add a new item">
		New Item</div>
	</div>
	<div id="reportsItemsMenu" class="menuskin1" style="z-index:1000;" onMouseOver="hlght2(event)" onMouseOut="llght2(event)" onclick="executemenuie5(event)">
		<div id="rc02" nowrap="nowrap" class="menuitems" command="item_report('CSV');" status="Complete Export of the Current Data">
		Export Data as CSV (Spreadsheet)</div>
	</div>
	<?php
}
//now save this for later output in the component
$datasetPreContent=ob_get_contents();
ob_end_clean();

require($_SERVER['DOCUMENT_ROOT'].'/components/dataset_generic_precoding_v200a.php');

if(!$refreshComponentOnly){
	?><style type="text/css">
	<?php
	echo $filterGadgetCSS;
	
	if(!$profileSettings['datasetColorHeader'])$profileSettings['datasetColorHeader']='674ea7';
	if(!$profileSettings['datasetColorRowAlt'])$profileSettings['datasetColorRowAlt']='d9d2e9';
	if(!$profileSettings['datasetColorSorted'])$profileSettings['datasetColorSorted']='wheat';
	dataset_complexDataCSS(array(
		'datasetColorHeader_'=>($profileSettings['datasetColorHeader']),
		'datasetColorRowAlt_'=>($profileSettings['datasetColorRowAlt']),
		'datasetColorSorted_'=>($profileSettings['datasetColorSorted']),
	));
	?>
	</style>
	<script language="javascript" type="text/javascript">
	<?php echo $filterGadgetJS;?>
	</script><?php
}
require($_SERVER['DOCUMENT_ROOT'].'/components/dataset_component_v200a.php');

get_contents_tabsection('form');

echo $helpString;

get_contents_tabsection('help');
tabs_enhanced(
	array(
		'form'=>array(
			'label'=>'Form'
		),
		'settings'=>array(
			'label'=>'Settings'
		),
		'help'=>array(
			'label'=>'Help',
		),
	), 
	array(
		'location'=>'bottom',
		'aColor'=>'royalblue',
		'brdColor'=>'#aaa',
	)
);

?>
<input name="mode" type="hidden" id="mode" value="<?php echo $mode;?>" />
<input name="submode" type="hidden" id="submode" />
<input name="_Profiles_ID_" type="hidden" id="_Profiles_ID_" value="<?php echo $_Profiles_ID_;?>" />
<input type="hidden" name="component" id="component" value="<?php echo $datasetComponent.':'.$datasetFile.':'.md5($datasetFile.$MASTER_PASSWORD);?>" />
<!-- these are set to their default -->
<input name="refreshComponentOnly" id="refreshComponentOnly" type="hidden" value="1" />
<input name="suppressPrintEnv" id="suppressPrintEnv" type="hidden" value="" />
</form>
