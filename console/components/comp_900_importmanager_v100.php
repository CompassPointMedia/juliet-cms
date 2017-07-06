<?php
/* 
todo:
-------------------
* for field, COMMENT 'Empty on initial import' ..or, '3 unique values on import'
* want to be able to specify which field is the primary key
* want to specify whether primary key field shouild also be AUTO_INCREMENT
* want to add control fields 
* add source as independent field including BATCH IMPORT of the data the table was created with
* want to name the table
* need a comment on the table/description
* special definition on fields - override default attributes - especially length if I know that for example a field is a longdesc but this import has no values
* indexing and unique keys handled
* "THINK" about currency, e.g. $50 would fail right now
* translations such as United States => US, phone numbers to proper formats, California to CA etc
* LOGICAL TRANSLATIONS
* get data as a lookup in another table=>field
* or create a lookup table from a field
* really the only way this will work will be with a preview table of some kind (but have it as an option)

Then, import data into an existing table
* map fields
* add fields as needed
* create a schema table with all these new mappings


2012-05-15:
	* now able to create a table from scratch via a CSV file with the following features developed:
		1. concept of "integrity" with boolean being highest (actually blank even higher) to longtext being lowest, see define DATA_BOOL below and function dataintegrity()
		2. for this opertation I am precursing the data before entry
	
2011-12-17
	* for the importFieldMap array, we should be able to read the table and create this array similarly
	* we should also pre-read whether this is an update or a insert based on a unique-field criteria (SKU for items)
2011-11-14
	This script was first devoloped for cpm051 for import of FC, FP and Staff with the additional ability to show specifications for imports and etc..  Reference this file in order to see more examples of $importFieldMap and use of import_translator 

*/

if(!function_exists('error_alert_2')){
function error_alert_2($x,$options=array()){
	global $assumeErrorState,$error_alert_2;
	if(is_array($options)){
		extract($options);
	}else{
		$continue=$options;
	}
	/*
	parameters stroable in $error_alert_2 global
	------------------------------------------
	storeErrorAlert
	errors=array()
	
	*/
	if(strlen($error_alert_2['storeErrorAlert']) && $error_alert_2['storeErrorAlert']==md5($GLOBALS['MASTER_PASSWORD'])){
		$error_alert_2['errors'][]=$x;
	}else{
		?><script language="javascript" type="text/javascript">
		alert('<?php echo $x?>');
		<?php if($focusField){ ?>
		window.parent.g('<?php echo $focusField?>').focus();
		window.parent.g('<?php echo $focusField?>').select();
		<?php } ?>
		</script><?php
		if(!$continue){
			$assumeErrorState=false;
			exit;
		}
	}
}
}
define('DATA_UNDEFINED',1);
define('DATA_BOOL',2);
define('DATA_INT',3);
define('DATA_FLOAT',4);
define('DATA_DATE',5);
define('DATA_DATETIME',6);
define('DATA_STRING',7);
define('DATA_TEXT',8);
define('DATA_LONGTEXT',9);
if(!function_exists('datalevel')){
function dataintegrity($data){
	/* 2012-05-15: this function does not trim data; pass it that way if you want */
	$t=strtotime($data);
	switch(true){
		case !strlen($data):
			return DATA_UNDEFINED;
		case is_logical($data):
			return DATA_BOOL;
		case preg_match('/^-*[0-9]+$/',$data) && strlen($data)<21:
			return DATA_INT;
		case preg_match('/^-*[0-9]+\.[0-9]+$/',$data):
			return DATA_FLOAT;
		case $t!==false && $t/86400==floor($t/86400):
			return DATA_DATE;
		case $t!==false:
			return DATA_DATETIME;
		case strlen($data)<257:
			return DATA_STRING;
		case strlen($data)<65537:
			return DATA_TEXT;
		default:
			return DATA_LONGTEXT;
	}
}
}
if(!function_exists('sql_attributes')){
function sql_attributes($attributes){
	extract($attributes);
	switch(true){
		case $integrity==DATA_BOOL:
			return 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0';
		case $integrity==DATA_INT:
			return ($length>9?'BIG':'').'INT('.$length.') NOT NULL DEFAULT 0';
		case $integrity==DATA_FLOAT:
			return 'FLOAT('.($length+1).','.($decimals?$decimals:'2').') NOT NULL';
		case $integrity==DATA_DATE:
			return 'DATE NOT NULL';
		case $integrity==DATA_DATETIME:
			return 'DATETIME NOT NULL';
		case !$integrity:
		case $integrity==DATA_STRING:
			return 'CHAR('.($length ? $length+2 : 20).') NOT NULL DEFAULT \'\'';
		case $integrity==DATA_TEXT:
			return 'TEXT NOT NULL';
		case $integrity==DATA_LONGTEXT:
			return 'LONGTEXT NOT NULL';
		default:
			return '{error:'.$integrity.'}';
		
	}
}
}

if(!$Data)$Data='Item';
$processingComponent=$_SERVER['DOCUMENT_ROOT'].'/console/resources/bais_01_exe.php';
function import_translator($p){
	global $n,$v,$public_cnx,$Data;
	switch($p){
		case 'manufacturer':
			//there is no error here - mfr is simply created w/o prejudice
			if(!trim($v))return '';
			if(is_numeric($v)){
				return $v;
			}else{
				if($ID=q("SELECT ID FROM finan_manufacturers WHERE Name='".addslashes($v)."'", O_VALUE)){
					return $ID;
				}else{
					return q("INSERT INTO finan_manufacturers SET Name='".addslashes($v)."', CreateDate=NOW(), Creator='".sun()."'", O_INSERTID);
				}
			}
		break;
		case 'purchaseprice':
		case 'unitprice':
		case 'unitprice2':
		case 'wholesaleprice':
			$v=preg_replace('/[^-.0-9]/','',$v);
			return $v;
		break;
	}
	global $exception,$importFieldMap,$data,$fieldIndexes;
	if($importFieldMap[$Data]['fields'][$fieldIndexes[$n]]['required']) $exception[]=$e;
}
$importFieldMap=array(
	'Item'=>array(
		'table'=>array(
			'name'=>'finan_items',
		),
		'fields'=>array(
			'SKU'=>array(
				'required'=>true,
				'label'=>array('Part Number','SKU','P/N'),
				'notes'=>'<strong>Must be unique</strong>!  Duplicate part numbers will not be imported.  Recommended characters are A-Z, 0-9, and a dash(-) character ONLY',
			),
			'Name'=>array(
				'required'=>true,
				'label'=>array('Name','Product Name'),
			),
			'Description'=>array(
				'required'=>false,
				'label'=>'Description',
			),
			'LongDescription'=>array(
				'required'=>false,
				'label'=>'Long Description',
			),
			'Category'=>array(
			
			),
			'SubCategory'=>array(
			
			),
			'UnitPrice'=>array(
				'required'=>false,
				'label'=>array('Price','Unit Price'),
				'translate'=>'import_translator(\'unitprice\')',
			),
			'UnitPrice2'=>array(
				'required'=>false,
				'label'=>array('List Price','Unit Price 2'),
				'notes'=>'This price is the "Suggested Retail Price" and is typically higher than the price you sell this part for.  If you do not use this convention, this field should not be imported.',
				'translate'=>'import_translator(\'unitprice2\')',
			),
			'WholesalePrice'=>array(
				'required'=>false,
				'label'=>'Wholesale Price',
				'notes'=>'This is the typical price you sell this part for to a reseller',
				'translate'=>'import_translator(\'wholesaleprice\')',
			),
			'PurchasePrice'=>array(
				'required'=>false,
				'label'=>'Purchase Price',
				'notes'=>'This is the price you typically purchase the product for; this is not a required field but is helpful if you purchase the product from another company and wish to get Cost of Goods Sold data',
				'translate'=>'import_translator(\'purchaseprice\')',
			),
			'Manufacturers_ID'=>array(
				'required'=>false,
				'label'=>'Manufacturer',
				'notes'=>'Manufacturer should be present in the system <strong>first</strong> before importing parts; either full name or short name must match exactly.  Otherwise, the manufacturer will be added with no address or other information',
				'translate'=>'import_translator(\'manufacturer\')',
				'value_list'=>q("SELECT ID, Name FROM finan_manufacturers ORDER BY Name", O_COL_ASSOC),
			),
			'{controlfields}'=>array(
				'label'=>'Control Fields',
				'description'=>'These provide data about who created or edited the record, and when.  Creator and Editor fields are 20 characters in length max',
			),
			'Creator'=>array(
				'required'=>false,
				'label'=>'Creator',
			),
			'CreateDate'=>array(
				'required'=>false,
				'label'=>array('Create Date','Created'),
			),
			'Editor'=>array(
				'required'=>false,
				'label'=>'Editor',
			),
			'EditDate'=>array(
				'required'=>false,
				'label'=>array('Edit Date','Edited'),
			),
			'Active'=>array(
				'required'=>false,
				'label'=>array('Active'),
				'notes'=>'Whether the part is currently active (showing).  Inactive items do not show on the website',
			),
		),
	),
	'(Custom import)'=>array(
		'table'=>array(
			'name'=>'^[_a-z0-9]+$',
		),
		'fields'=>array(
			/* no fields defined */
		),
	),
);

if($Data!=='(Custom import)' && !$fields){
	$fields=q("EXPLAIN ".$importFieldMap[$Data]['table']['name'], O_ARRAY);
	foreach($fields as $o=>$w){
		$fields[strtolower($w['Field'])]=$w;
		unset($fields[$o]);
	}
}
if(!$refreshComponentOnly){
	?><style type="text/css">
	.importParams{
		border-collapse:collapse;
		}
	.importParams td{
		border:1px solid #ccc;
		padding:4px 7px 1px 3px;
		}
	.importParams th{
		padding:4px 7px 1px 3px;
		}
	</style>
	<script language="javascript" type="text/javascript">
	function setDocs(n){
		g('d1').src='importmanager.php?mode=importManager&submode=documentation&suppressCustomTitle=1&Data='+n;
	}
	function uploadFile(){
		g('form1').submit();
		g('uploadPending').style.display='block';
		g('uploadFileButton').disabled=true;
		//register enabling if process nhf or fails
	}
	</script><?php
}
if($submode=='documentation'){
	if(false){ ?><div style="display:xnone;"><?php } 
	if($Data=='Item'){
		?>
		<h1>Item Import Rules</h1>
		<p>Please contact Sam Fullman at 512-754-7927 for assistance or to add additional import objects into the system</p>
		<?php
	}else if($Data=='(Custom import)'){
		?>
		<h1>Create Table From Import</h1>
		<?php
	}
	if($Data=='(Custom import)'){
		?>
		<p>A table will be created from the CSV file you upload.  The first row must represent field names; only a-z, 0-9, and the underscore character will be used for the field name.  Duplicate field names will be appended with _2, _3 etc. to be unique.</p>
		<p>The import considers data to be in 8 levels of integrity: </p>
		<p><span class="gray">0) (all blank columns fit in here)</span><br />
		1) boolean values, specifically 1 or 0, or T or F, or true or false, etc.  These will be converted to a 1 or 0 as long as all data is either of these values, or blank, or null<br />
		2) integer values, specifically both positive and negative values.<br />
		  3) float values, specifically numbers with a decimal portion<br />
		  4) date values, specifically values that will pass the PHP strtotime() test with a UNIX timestamp AND which have no time portion (i.e. 00:00:00).  These will be converted into Date fields<br />
		  5) date time values, specifically values that have both date and time values<br />
		  6) string values under 255 characters<br />
		  7) string values under 64K<br />
		  8) long string (longtext) values
</p>
		<p> 
		  <?php
	}else{
		?>
	  </p>
		<p>Import files must be in CSV format.</p>
		<table class="importParams">
		<thead>
		<tr>
		<th>Field Name</th>
		<th nowrap="nowrap">OK Import <br />Field Names</th>
		<th>Required</th>
		<th>Max Chars</th>
		<th>Data Type</th>
		<th>Notes</th>
		</tr>
		</thead><tbody><?php
		foreach($importFieldMap[$Data]['fields'] as $n=>$v){
	
			if(preg_match('/^\{([^{}]+)\}/i',$n,$m)){
				?><tr><td colspan="100%"><h3><?php echo $v['label']?$v['label']:$m[1];?></h3><?php
				if($n=$v['description']){
					?><p class="gray"><?php echo $n;?></p><?php
				}
				?></td></tr><?php
				continue;
			}
	
			?><tr>
			<td style="color:darkgreen;"><?php echo is_array($v['label']) ? $v['label'][0] : ($v['label'] ? $v['label'] : $n);?></td>
			<td><?php echo is_array($v['label']) ? implode(', ',$v['label']) : '&nbsp;';?></td>
			<td class="tac"><?php echo $v['required'] ? 'yes' : '<span class="gray">no</span>';?></td>
			<td><?php 
			$dt='';
			if($v['length']){
				echo $v['length'];
			}else{
				$x=$fields[strtolower($n)]['Type'];
				if(preg_match('/date|time|year/i',$x)){
					$dt='<span class="gray">(date/time)</span>';
				}else if($x=='text'){
					echo '64Kb';
					$dt='text';
				}else if($x=='longtext'){
					echo '<em>no limit</em>';
					$dt='text';
				}else if(preg_match('/([a-z]+)\(([,0-9]+)\)/i',$x,$m)){
					echo $m[2];
					$dt=str_replace('var','',$m[1]);
				}
			}
			?></td>
			<td><?php echo $v['datatype'] ? $v['datatype'] : $dt; ?></td>
			<td><?php echo $v['notes'] ? $v['notes'] : '&nbsp;'; 
			if(count($v['value_list'])){
				?><br />
				Current Values: <select name="null">
				<?php
				foreach($v['value_list'] as $o=>$w){
					?><option value="<?php echo $o?>"><?php echo h($w);?></option><?php
				}?>
				</select><?php
			}
			?></td>
			</tr><?php
		}
		?></tbody></table><?php
	}
	if(false){ ?></div><?php }
	$assumeErrorState=false;
	exit;
}else if($submode=='uploadFile'){
	//upload file
	$key=md5(time().rand(1,1000000));
	$_SESSION['special']['imports'][$key]['starttime']=time();
	$_POST_BUFFER=$_POST;
	if(!is_uploaded_file($_FILES['uploadFile_1']['tmp_name'])){
		mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals($err='Error accessing uploaded file; operation not completed'),$fromHdrBugs);
		error_alert_2($err);
	}
	$_SESSION['special']['imports'][$key]['file']=$_FILES['uploadFile_1']['name'];
	$_SESSION['special']['imports'][$key]['filesize']=$_FILES['uploadFile_1']['size'];
	$_SESSION['special']['imports'][$key]['data']=$_POST;
	
	?><script language="javascript" type="text/javascript">
	window.parent.g('uploadFileButton').disabled=false;
	</script><?php
	
	$fp=fopen($_FILES['uploadFile_1']['tmp_name'], 'r');
	$_i_900=-1; #records idx

	if($Data=='(Custom import)'){
		//first recurse for data values and quality
		while($r=fgetcsv($fp,100000)){
			$_i_900++;
			if($_i_900==0){
				foreach($r as $n=>$field){
					$data[$n]['unique']=true;
					$field=preg_replace('/[^_A-Za-z0-9]/','',$field);
					if($fields[strtolower($field)]){
						$dupes[strtolower($field)]++;
						$key=strtolower($field).'_'.($dupes[strtolower($field)]+1);
						$Key=$field.'_'.($dupes[strtolower($field)]+1);
						$fields[$key]=$Key;
					}else{
						$key=strtolower($field);
						$Key=$field;
						$fields[$key]=$Key;
					}
					$fieldMap[$key]=$n;
					$fieldKeys[$n]=$Key;
				}
				continue;
			}
			foreach($r as $n=>$v){
				$v=trim($v);
				$integrity=dataintegrity($v);
				if(strlen($v)){
					$data[$n]['present']++;
					$data[$n]['integrity']=max($data[$n]['integrity'], $integrity);
					if($data[$n]['integrity']==DATA_FLOAT){
						$data[$n]['decimals']=max($data[$n]['decimals'], strlen(end(explode('.',$v))));
					}
				}
				if($data[$n]['unique']){
					if($values[$n][strtolower($v)])unset($data[$n]['unique']);
					$values[$n][strtolower($v)]=1;
				}
				$data[$n]['length']=max($data[$n]['length'],strlen($v));
			}
		}
		$tableName=preg_replace('/[^_a-z0-9]+/i','',$tableName);
		$table=($tableName ? $tableName : 'importtable_'.time());
		$sql='CREATE TABLE '.$table.'('."\n";
		foreach($fields as $n=>$v){
			$sql.='`'.$v.'`';
			$sql.=' '.sql_attributes($data[$fieldMap[$n]]);
			$sql.=','."\n";
		}
		$sql=rtrim($sql,"\n,");
		$sql.=')';
		ob_start();
		q($sql, ERR_ECHO);
		$err=ob_get_contents();
		ob_end_clean();
		if($err){
			prn($err);
			mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err),$fromHdrBugs);
			error_alert('error creating table; unable to perform import');
		}	
		
		$fp=fopen($_FILES['uploadFile_1']['tmp_name'], 'r');
		$_i_900=-1; #records idx
		while($r=fgetcsv($fp,100000)){
			$_i_900++;
			if(!$_i_900)continue;
			$sql='INSERT INTO '.$table.' SET '."\n";
			foreach($r as $n=>$v){
				$sql.='`'.$fieldKeys[$n].'`='.(is_numeric($v)?'':'\'').addslashes($v).(is_numeric($v)?'':'\'').',';
			}
			prn($sql);
			q(rtrim($sql,','));
			
		}

	}else{
		while($r=fgetcsv($fp,10000)){
			$_i_900++;
			if($_i_900===0){
	
				if($PRE && $CustomFieldColumns){
					$CustomFieldColumns=explode(',',$CustomFieldColumns);
					foreach($CustomFieldColumns as $v){
						$v=trim($v);
						if(!$v)continue;
						$newField=$PRE.'_'.preg_replace('/[^a-z0-9]/i','',$v);
						if(!$fields[strtolower($newField)]){
							$updateFields=true;
							q("ALTER TABLE ".$importFieldMap[$Data]['table']['name']." ADD `$newField` CHAR(255) NOT NULL");
						}
						if(!$importFieldMap[$Data]['fields'][$newField])$importFieldMap[$Data]['fields'][$newField]=array('label'=>$v);
					}
				}
				if($updateFields){
					$fields=q("EXPLAIN ".$importFieldMap[$Data]['table']['name'], O_ARRAY);
					foreach($fields as $o=>$w){
						$fields[strtolower($w['Field'])]=$w;
						unset($fields[$o]);
					}
				}
	
				foreach($r as $n=>$v){
					$rawFieldIndexes[$n]=preg_replace('/[^a-z0-9_]/i','',$v);
					foreach($importFieldMap[$Data]['fields'] as $o=>$w){
						unset($comp);
						is_array($w['label']) ?  '' : $w['label']=array($w['label'] ? $w['label'] : $o);
						foreach($w['label'] as $x){
							if(strtolower(preg_replace('/[^a-zA-Z0-9_]/','',$x))==strtolower(preg_replace('/[^a-zA-Z0-9_]/','',$v))){
								define($o,$n);
								$fieldIndexes[$n]=$o;
							}
						}
					}
				}
				//make sure all required fields are present
				$requiredFieldsMissing=array();
				foreach($importFieldMap[$Data]['fields'] as $n=>$v){
					if($v['required'] && !in_array($n,$fieldIndexes)){
						$requiredFieldsMissing[]=(is_array($v['label']) ? $v['label'][0] : $v['label']);
					}
				}
				if(!empty($requiredFieldsMissing))error_alert_2('The following fields are not present: '.implode(', ',$requiredFieldsMissing));
				continue;
			}
			//goal
			
			//------------ ok this is kind of a pre-processing section -----------------
			unset($tpost);
			unset($exception);
			foreach($r as $n=>$v){
				//handle translation and normlazation
				if($fctn=$importFieldMap[$Data]['fields'][$fieldIndexes[$n]]['translate']){
					eval('$v='.$fctn.';');
				}
	
				//now we have a translated/normalized variable; handle requirements
				
				//set the quasi post array
				if(strstr($fieldIndexes[$n],'[')){
					eval('$tpost["'.str_replace('[','"][',$fieldIndexes[$n]).'=addslashes($v);');
				}else if(is_array($v)){
					eval('$tpost[\''.$fieldIndexes[$n].'\']=$v;');
				}else if($fieldIndexes[$n]){
					eval('$tpost[\''.$fieldIndexes[$n].'\']=addslashes($v);');
				}else{
					//this column is not on the field indexes
					if($f=$fields[strtolower($rawFieldIndexes[$n])]){
						eval('$tpost[\''.$f['Field'].'\']=addslashes($v);');
						prn('$tpost[\''.$f['Field'].'\']=addslashes($v);');
					}else{
						//2012-03-11: we don't do this for security I think
					}
				}
			}
			#$assumeErrorState=false;
			#prn($tpost,1);
			if($exception){
				$_SESSION['special']['imports'][$key]['errors'][$_i_900]=$exception;
				continue;
			}
			//-----------------------------------------------------------------
			
			//handle implicit field (2011-09-18: what did I mean by this?)
			
		
			//the goal; let the code block handle it as it normally does..
			$_POST=$tpost;
			extract($_POST);
			/*
			prn('--------------post---------------');
			prn($_POST);
			
			initiate lazy error checking - **NOTE THIS WILL REQUIRE LEVELS* and we need to more implement DIRONAL ec on the code block
			
			*/
		
			//prepare the variables/environment for submission
			
			$error_alert_2['storeErrorAlert']=md5($GLOBALS['MASTER_PASSWORD']);
			unset($error_alert_2['errors']);
		
			//other variables that may be needed
			$lazyErrorChecking=true;
			$suppressNavigate=true;
			$navigateExit=false;
			
		
			//mode-specific coding
			if($Data=='Item'){
				$insertMode='insertItem';
				$updateMode='updateItem';
	
				if($ID=q("SELECT * FROM finan_items WHERE SKU='".addslashes($SKU)."'", O_VALUE)){
					$mode=$updateMode;
				}else{
					$mode=$insertMode;
					$ResourceToken=substr(date('YmdHis'),3).rand(10000,99999);
					$ID=q("INSERT INTO finan_items SET ResourceToken='$ResourceToken', CreateDate=NOW(), Creator='system'", O_INSERTID);
				}
			}
			require($processingComponent);
			if($error_alert_2['errors']){
				$_SESSION['special']['imports'][$key]['errors'][$_i_900]=$error_alert_2['errors'];
			}
		}
	}

	
	//make sure the file contains required fields; map the fields out
	$_SESSION['special']['imports'][$key]['records']=$_i_900;
	$_SESSION['special']['imports'][$key]['endtime']=time();
	//make sure the file contains records
	?><script language="javascript" type="text/javascript">
	window.parent.location='/console/importmanager.php?mode=finished&key=<?php echo $key?>';
	</script><?php
	$assumeErrorState=false;
	exit;
}
?>
<div id="importManager">
<?php
if($mode=='finished'){
	//report
	mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
	extract($_SESSION['special']['imports'][$key]);
	$Data=$data['Data'];
	?>
	<h1>Import Completed</h1>
	<div class="fr">
	Import Started: <?php echo date('n/j/Y \a\t g:iA',$starttime);?><br />
	Import Completed: <?php echo date('n/j/Y \a\t g:iA',$endtime);?><br />
	
	</div>
	Import type: <span class="varHl"><?php echo $Data;?></span><br />
	File name: <span class="varHl"><?php echo $file;?></span><br />
	File size: <span class="varHl"><?php echo round($filesize/1024,2).'kb';?></span><br />
	Total records: <span class="varHl"><?php echo $records;?></span><br />
	Successful imports: <span class="varHl"><?php echo $records - count($errors);?></span><br />
	Errors: <span class="varHl"><?php echo count($errors) ? count($errors) : '<em>none</em>';?></span><br />
	<?php
	if($errors){
		?><table class="importParams"><?php
		foreach($errors as $i=>$v){
			?><tr>
			<td nowrap="nowrap">Record <?php echo $i;?></td>
			<td>
			<?php echo implode('<br />',$v);?>
			</td>
			</tr><?php
		}
		?></table><?php
	}
	?>
	<div class="fr">
	  <input type="button" name="Button" value="Print" onclick="window.print();" />
	  &nbsp;&nbsp;
	  <input type="button" name="Submit2" value="Close" onclick="window.close();" />
	  &nbsp;&nbsp;
	  <input type="button" name="Submit2" value="New Import" onclick="window.location='importmanager.php?Data=<?php echo $Data;?>';" />
	</div>
	<?php	
}else{
	?>
	<h1>Import Manger</h1>
	<h3>Select the type of data to import </h3>
	<p>
	<?php
	foreach($importFieldMap as $n=>$v){
		?>
		<label><input name="Data" type="radio" value="<?php echo $n?>" <?php echo $Data==$n?'checked':''?> onchange="dChge(this);setDocs(this.value);" />
		<?php echo $n?></label>	<br />
	
		<?php
	}
	?>
  </p>
	<iframe name="d1" id="d1" src="importmanager.php?mode=importManager&submode=documentation&suppressCustomTitle=1&Data=<?php echo $Data?>" width="100%" height="<?php echo 400;?>" /></iframe>
	<h3>  Select a CSV file </h3>
	<p>
	  <input name="uploadFile_1" type="file" id="uploadFile_1">
	  <br />
	  Create custom fields with prefix: 
	  <input name="PRE" type="text" id="PRE" value="<?php echo $PRE?>" size="5" maxlength="4" onchange="dChge(this);" />
	  <!--
	  <label title="Lazy error checking relaxes most of the requirements for a complete foster home or foster child record">
	  <input name="lazyErrorChecking" type="checkbox" id="lazyErrorChecking" onchange="dChge(this);" value="1" checked="checked" />
      Use lazy error checking</label>
	  <br />
	  -->
      <br />
      List custom fields in import file (separate by commas):
<input name="CustomFieldColumns" type="text" id="CustomFieldColumns" value="<?php echo $CustomFieldColumns;?>" size="35" maxlength="255" onchange="dChge(this);" />
      <br />
      <br />
      <input type="button" name="Button" id="uploadFileButton" value="Begin Import" onclick="uploadFile();" />
  &nbsp;
	  <span id="uploadPending" style="display:none;"><img src="/images/i/ani/ani-fb-orange.gif" width="16" height="11" /> </span>
	  
	  <br />
	  <input name="mode" type="hidden" id="mode" value="importManager" />
	  <input name="submode" type="hidden" id="submode" value="uploadFile" />
	  <br />
	  <br />
	  <br /> 
	  <?php	
}
?>
  </p>
</div>