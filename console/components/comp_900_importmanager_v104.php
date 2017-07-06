<?php
$suppressMailOutput=true;
/* 
documentation
-------------------
NOTE on ctrlFields: this only adds the columns if they are not present in the existing table, or not mentioned in the data source
you could use colMappings as a first line of update, and setValues as a second line, to populate the fields that are created.  If the target table already has fields by this name, or we are creating a new table and the data source has these fields, selecting any Add control fields option has no effect.



bugs:
-------------------
2013-03-09: 
	imported fruits into categories_1 table in cpm006.  why was the first record categories_id=null but secondaries were blank?
	the logic interlock between updateRows and hierarchy needs to be closed up; right now they are two but should always be one (hierarchy trumps)
	editdate was blank in the fruits table import; why was it not recognized as a timestamp?
2012-12-29: this piece of crap is screwed up.  I FEEL LIKE GIVING UP - but I will not, ever at any time.
	FIXED	gen_batches is not filling in Profiles_ID
	FIXED	gen_batches_entries has never worked
2012-12-26: not FILLING new columns dynamically created for imports to existing table - but they are being created
	- however it works to fill on the second pass if I synch with a key field...
2012-11-01: not getting clean batches_entries records (0 for key and errors but for importtable_amazon I I got the data in clean, i.e. extra columns)


todo:
-------------------
2013-03-08:
	* stats are inaccurate and incomplete after the fact, and I don't think I have any way to view import history
	* the help box is now getting in the way
	* still don't like the interlock between create-table and use-existing
	* load profile - should interlock against new with same name
		should auto-fill profile name and use variables "imported {Records} records"
		load profile fade-in - Times Used vs. Records - and see batch history
		also need to delete a batch or hide it at least
	* cancel button with are you sure + isEscapable=1 not 2
		
2012-06-05:
	* for updates, I would like to do the update, if affected rows then adjust timestamp and add editor - purest use of this feature
2012-05-27:
	
2012-05-25:
	* we really have two arrays, $tableFields and $fields - 1st = database fields and it has the query nodes as its structure; the 2nd could be synched with this AMAP
	* also the array $data could be folded into $fields I bet
	* standardize the error checking as it was with items, etc.
	* there is a concept that even if I am creating a new table, there can be a "translation" as if it were going into a known table.  That allows for the importFieldMap to be universal
	* i have cf's entered into the mix but the assignment of editdate value on an update is not really accurate


cool and ethereal but not needed any time soon
----------------------------------------------	
	* want to specify whether primary key field shouild also be AUTO_INCREMENT
	* special definition on fields - override default attributes - especially length if I know that for example a field is a longdesc but this import has no values
	* indexing and unique keys handled
	* "THINK" about currency, e.g. $50 would fail right now
	* translations such as United States => US, phone numbers to proper formats, California to CA etc
	* LOGICAL TRANSLATIONS
	* get data as a lookup in another table=>field
	* or create a lookup table from a field
		create lookup table with [x]extension / [ ]name of _____; change field name to Asdfs_ID; for lookup table have []description field (stock with this comment initially___________); ()no CF, ()timestamp only, ()timestamp + editor, ()full cf
	
* really the only way this will work will be with a preview table of some kind (but have it as an option)

2013-03-09
* added a hierarchy system for tables like chart of accoutns and items.  VERY CRUDE and has the following paramters:
	1. ideally you want records with name= Fruit, Fruit:Grapes, Fruit:Grapes:Concorde in order so that the records can be inserted with full field accuracy
	2. if we are "inHierarchy" i.e. in Fruit or Grapes for field value Fruit:Grapes:Concord, then only inserts will be done, and not updates
	3. with that in mind, if Grapes is missing, or out of order, it will get the description and column values of Concord
	4. you MUST set updateRows to checked, and specify the hierarchyNameField under "based on import column"
	5. that said, I do not know how this would behave if you natively put the primary key field (ID) in as well.  But this would normally only be data-in from QuickBooks
	6. you must declare hierarchyForeignKeyField=Accounts_ID for example; 
	7. I am assuming that if you want to create the table, the column e.g. Accounts_ID must be present even though it will be blank.  Note that this column is added to the recordset but may not be added to the table field list on CREATE TABLE
* make sure all tables have a primary key (ID) field; the average user is not going to include this	

2012-06-05:
	* now storing an import profile, so I can reload all the work to set up an import
2012-05-28:
	* added msyql::NOW() and mysql::MD5('$password') where password is a column in the data source
	* trimmed values due to sloppy users
	* added interpret as null feature
2012-05-24:
	* I am now able to import a compound-offset data merge at least from a csv file.  This could also work to double-offset merge two MySQL tables into one on a shared primary key.  THAT is something worth working for.
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

if(!function_exists('get_row')){
	function get_row($options=array()){
		/*
		2012-06-30 by Sam
		*/
		global $gr,$SQL, $SQLResult, $fp, $qr, $_i_900, $hierarchy,$_hierarchySet_,$table,$fieldMap,$fieldKeys,$colMappings,$tableFields;
		extract($options);
		if($fp){
			if($ignore_hierarchy)return fgetcsv($fp,100000);

			//------------ 2013-03-09: added hierarchy logic -------------			
			global $table,$hierarchy,$hierarchyNameField,$hierarchyForeignKeyField,$hierarchyAdditionalCondition,$_hierarchySet_,$_hierarchyPrevious_,$inHierarchy;

			$gr++;

			if(count($_hierarchySet_)){
				$inHierarchy=(count($_hierarchySet_)>1);
				#prn($r['location']="at $gr line ".__LINE__.($inHierarchy?'(in hierarchy)':''));
				global $r;
				//pass it as the 0th element - get rd and modify it and specify the parent if applies
				foreach($_hierarchySet_ as $v){
					$temp=$r[$fieldMap[strtolower($hierarchyNameField)]]=$v;
					break;
				}
				if(strlen($_hierarchyPrevious_)){
					if($n=$colMappings[strtolower($hierarchyNameField)]){
						$hnf=$tableFields[$n]['Field'];
					}else{
						$hnf=$hierarchyNameField;
					}
					$c=$hierarchyAdditionalCondition;
					if($id=q("/* for $temp */ SELECT ID FROM $table WHERE ".($c?$c:1)." AND $hnf='".addslashes($_hierarchyPrevious_)."'", O_VALUE)){
						$r[$fieldMap[strtolower($hierarchyForeignKeyField)]]=$id;
					}else{
						unset($r[$fieldMap[strtolower($hierarchyForeignKeyField)]]);
					}
				}else{
					unset($r[$fieldMap[strtolower($hierarchyForeignKeyField)]]);
				}
				//unset hierarchy set element
				foreach($_hierarchySet_ as $n=>$v){
					$_hierarchyPrevious_=$_hierarchySet_[$n];
					unset($_hierarchySet_[$n]);
					break;
				}
				//only on the last one
				if(!count($_hierarchySet_)){
					#prn('unset last at '.$gr);
					$_hierarchyPrevious_='';
				}
				return $r;
			}else{
				//fetch the row
				if(!($r=fgetcsv($fp,100000)))return false;
				
				if($hierarchy && strstr($r[$fieldMap[strtolower($hierarchyNameField)]],':')){
					$inHierarchy=true;
					$inHierarchy=true;

					//create hierarchy set
					$_hierarchySet_=explode(':',$r[$fieldMap[strtolower($hierarchyNameField)]]);
					foreach($_hierarchySet_ as $n=>$v)$_hierarchySet_[$n]=trim($v);

					//pass as 0th element as above
					$temp=$r[$fieldMap[strtolower($hierarchyNameField)]]=$_hierarchySet_[0];
					if(strlen($_hierarchyPrevious_)){
						if($n=$colMappings[strtolower($hierarchyNameField)]){
							$hnf=$tableFields[$n]['Field'];
						}else{
							$hnf=$hierarchyNameField;
						}
						$c=$hierarchyAdditionalCondition;
						if($id=q("/* for $temp */ SELECT ID FROM $table WHERE ".($c?$c:1)." AND $hierarchyNameField='".addslashes($_hierarchyPrevious_)."'", O_VALUE)){
							$r[$fieldMap[strtolower($hierarchyForeignKeyField)]]=$id;
						}else{
							unset($r[$fieldMap[strtolower($hierarchyForeignKeyField)]]);				
						}
					}else{
						unset($r[$fieldMap[strtolower($hierarchyForeignKeyField)]]);				
					}
					//unset hierarchy set element
					$_hierarchyPrevious_=$_hierarchySet_[0];
					unset($_hierarchySet_[0]);
					return $r;
				}else{
					$inHierarchy=false;
					#prn($r['location']="at $gr line ".__LINE__.($inHierarchy?'(in hierarchy)':''));
					//return the row
					return $r;
				}
			}
			//-------------------------			
		}else{
			return $SQLResult[$_i_900+1];
		}
	}
}

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
//WE *SHOULD* HAVE THIS: define('DATA_NULL',0);
define('DATA_UNDEFINED',1);
define('DATA_BOOL',2);
define('DATA_INT',3);
define('DATA_FLOAT',4);
define('DATA_DATE',5);
define('DATA_DATETIME',6);
define('DATA_STRING',7);
define('DATA_TEXT',8);
define('DATA_LONGTEXT',9);

if(!function_exists('dataintegrity')){
function dataintegrity($data){
	/* 2012-05-15: this function does not trim data; pass it that way if you want */
	$t=strtotime($data);
	global $interpretNull;
	$buffer=15; //i.e. a length of 240 (255-15) is text vs. char
	if($interpretNull)$data=preg_replace('/^null$/i','',$data);
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
		case strlen($data)<= 255 - $buffer:
			return DATA_STRING;
		case strlen($data)<65535 - $buffer:
			return DATA_TEXT;
		default:
			return DATA_LONGTEXT;
	}
}}
if(!function_exists('sql_attributes')){
function sql_attributes($field,$attributes,$options=array()){
	global $sql_attributes, $suppressPrimaryKeyInterpretation;
	$sql_attributes['current']=array();
	extract($options);
	if(!isset($notNull))$notNull=false;
	$null=($notNull && !$has_null ? ' NOT NULL':'');
	$nullYesNo=($notNull && !$has_null ? 'NO':'YES');
	extract($attributes);
	switch(true){
		case $integrity==DATA_BOOL:
			$sql_attributes['current']=array(
				'Field'=>$field,
				'Type'=>'TINYINT(1) UNSIGNED',
				'Null'=>$nullYesNo,
			);
			return 'TINYINT(1) UNSIGNED'.$null.' DEFAULT 0';
		case $integrity==DATA_INT:
			//the length will be longer than specified if mode=insert and field ends in _*ID
			if($mode=='insert' && preg_match('/_*id$/i',$field)){
				$length=max($length,11);
			}
			$sql_attributes['current']=array(
				'Field'=>$field,
				'Type'=>($length>9?'BIG':'').'INT('.$length.')',
				'Null'=>$nullYesNo,
			);
			$str=($length>9?'BIG':'').'INT('.$length.')'.$null;
			//if mode is insert, the field is unique and replete, and we have not already done this, it will be the primary key and auto_inc (otherwise it can/could be neither)
			if(!$sql_attributes['primary_key_declared'] && $mode=='insert' && $unique && $replete && !$suppressPrimaryKeyInterpretation){
				$sql_attributes['primary_key_declared']=true;
				$str.=' AUTO_INCREMENT PRIMARY KEY';
			}
			return $str;
		case $integrity==DATA_FLOAT:
			$sql_attributes['current']=array(
				'Field'=>$field,
				'Type'=>'FLOAT('.($length+1).','.($decimals?$decimals:'2').')',
				'Null'=>$nullYesNo,
			);
			return $sql_attributes['current']['Type'].$null;
		case $integrity==DATA_DATE:
			$sql_attributes['current']=array(
				'Field'=>$field,
				'Type'=>'DATE',
				'Null'=>$nullYesNo,
			);
			return 'DATE'.$null;
		case $integrity==DATA_DATETIME:
			$sql_attributes['current']=array(
				'Field'=>$field,
				'Type'=>'DATETIME',
				'Null'=>$nullYesNo,
			);
			return 'DATETIME'.$null;
		case !$integrity:
		case $integrity==DATA_STRING:
			$sql_attributes['current']=array(
				'Field'=>$field,
				'Type'=>'CHAR('.($length ? $length+2 : 20).')',
				'Null'=>$nullYesNo,
			);
			return 'CHAR('.($length ? $length+2 : 20).')'.$null.' DEFAULT \'\'';
		case $integrity==DATA_TEXT:
			$sql_attributes['current']=array(
				'Field'=>$field,
				'Type'=>'TEXT',
				'Null'=>$nullYesNo,
			);
			return 'TEXT'.$null;
		case $integrity==DATA_LONGTEXT:
			$sql_attributes['current']=array(
				'Field'=>$field,
				'Type'=>'LONGTEXT',
				'Null'=>$nullYesNo,
			);
			return 'LONGTEXT'.$null;
		default:
			return '{error:'.$integrity.'}';
		
	}
}}

if(!$Data)$Data='(Custom import)';
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
	'(Custom import)'=>array(
		'table'=>array(
			'name'=>'^[_a-z0-9]+$',
		),
		'fields'=>array(
			/* no fields defined */
		),
	),
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
	/* -------------------------- from juliet dev.popup.2012-06-02 ---------------------- */
	a.selected {
	  background-color:#1F75CC;
	  color:white;
	  z-index:100;
	}
	
	.messagepop {
	  background-color:#FFFFFF;
	  border:1px solid #999999;
	  cursor:default;
	  display:none;
	  margin-top: 15px;
	  position:absolute;
	  text-align:left;
	  z-index:50;
	  padding: 25px 25px 20px;
	}
	.messagepop p, .messagepop.div {
	  border-bottom: 1px solid #EFEFEF;
	  margin: 8px 0;
	  padding-bottom: 8px;
	}
	/* ---------------------------------------------------------------------------- */
	.highlighted{
		background-color:lightgreen;
		}

	.values{
		max-width:175px;
		}
	select.minimal{
		border-width:1px;
		}
	table.yat{
		border-collapse:collapse;
		}
	.yat td{
		border-bottom:1px dotted #ccc;
		padding:2px 4px 1px 2px;
		}
	.sectionA{
		background-color:cornsilk;
		border:1px solid #ccc;
		border-radius:10px;
		padding:10px;
		}
	</style>
	<script language="javascript" type="text/javascript">
	function setDocs(n){
		g('d1').src='importmanager.php?mode=importManager&submode=documentation&suppressCustomTitle=1&Data='+n;
		var ci=(n=='(Custom import)');
		g('PRE').disabled=ci;
		g('CustomFieldColumns').disabled=ci;
		g('f2').style.display=(ci?'block':'none');
	}
	function uploadFile(){
		g('form1').submit();
		g('uploadPending').style.display='block';
		//g('uploadFileButton').disabled=true;
		//register enabling if process nhf or fails
	}
	function toggle1(n){
		g('insertTableOptions').style.display=(n=='insertTable'?'block':'none');
		g('updateTableOptions').style.display=(n=='updateTable'?'block':'none');
	}
	var cfAlerted=false;
	function cfAlert(o){
		if(o.value && !cfAlerted){
			cfAlerted=true;
			alert('RelateBase has four "control fields": CreateDate, Creator, EditDate and Editor.  By default EditDate is the last time the record was modified in any way.  Creator and Editor are each 20 characters long maximum.  Both date fields\' format should be YYYY-MM-DD HH:MM:SS (time part is optional).  If your data source contains columns that match these fields, be sure and use the Column Mappings box to indicate this');
		}
	}
	function interlock091(o){
		g('importProfileDescription').disabled=(o.value=='');
		if(o.value=='')g('importProfileDescription').focus();
	}
	//profile popup box
	function deselect() {
		$(".pop").slideFadeToggle(function() {
			$("#loadProfile").removeClass("selected");
		});    
	}
	function step1(){
		if($(this).hasClass("selected")) {
			deselect();               
		} else {
			$(this).addClass("selected");
			$(".pop").slideFadeToggle(function() { });
		}
		return false;
	}
	$(function() {
		$("#loadProfile").live('click', step1);
		$(".close").live('click', function() {
			deselect();
			return false;
		});
	});
	$.fn.slideFadeToggle = function(easing, callback) {
		return this.animate({ opacity: 'toggle', height: 'toggle' }, "fast", easing, callback);
	};
	$(document).ready(function(e){
		$('#viewTable').click(function(e){
			if(!g('tableName').value){
				alert('select a table first');
			}else
			ow('importmanager.php?submode=viewTable&tableName='+g('tableName').value,'l2_table','550,700');
			return false;
		});
		$('#suppressPrimaryKeyInterpretation').click(function(e){
			g('PrimaryKeyColumn').disabled=g('suppressPrimaryKeyInterpretation').checked;
		});
	});

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
	//data conversion and error checking
	if($targetMergeField=='(same)')$targetMergeField=$arrowMergeField;
	if(!function_exists('reenable_submit')){
		function reenable_submit(){
			?><script language="javascript" type="text/javascript">
			window.parent.g('uploadPending').style.display='none';
			window.parent.g('uploadFileButton').disabled=false;
			</script><?php
		}
	}
	if($operation=='updateTable' && $updateRows && (!$arrowMergeField || !$targetMergeField )){
		reenable_submit();
		error_alert('You are choosing to UPDATE records with matching criteria.  You must specify the column in the import source, and the column in the target table, that you wish to compare');
	}
	if($hierarchy && (!$hierarchyNameField || !$hierarchyForeignKeyField))error_alert('You must specify the hierarchy name field and foreign key field to upload a hierarchical file');
	if($externalBatch){
		//pre-action, make sure tables are there
		ob_start(); q("SHOW CREATE TABLE gen_batches", ERR_ECHO); $err=ob_get_contents(); ob_end_clean();
		if($err){
			$sql=q("SHOW CREATE TABLE relatebase_template.gen_batches",O_ROW, C_SUPER);
			$sql=str_replace('`relatebase_template`.','',$sql['Create Table']);
			q($sql);
			$err='';		
		}else{
		}
		ob_start(); q("SHOW CREATE TABLE gen_batches_entries", ERR_ECHO); $err=ob_get_contents(); ob_end_clean();
		if($err){
			$sql=q("SHOW CREATE TABLE relatebase_template.gen_batches_entries",O_ROW, C_SUPER);
			$sql=str_replace('`relatebase_template`.','',$sql['Create Table']);
			q($sql);
			$err='';		
		}
	}
	
	//upload file
	$s_key=md5(time().rand(1,1000000));
	$_SESSION['special']['imports'][$s_key]['starttime']=time();
	$_POST_BUFFER=$_POST;
	if($SQL){
		$_SESSION['special']['imports'][$s_key]['file']='data source';
		$_SESSION['special']['imports'][$s_key]['filesize']='not given';
	}else{
		if(!is_uploaded_file($_FILES['uploadFile_1']['tmp_name'])){
			mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals($err='Error accessing uploaded file; operation not completed'),$fromHdrBugs);
			error_alert_2($err);
		}
		$_SESSION['special']['imports'][$s_key]['file']=$_FILES['uploadFile_1']['name'];
		$_SESSION['special']['imports'][$s_key]['filesize']=$_FILES['uploadFile_1']['size'];
	}
	$_SESSION['special']['imports'][$s_key]['data']=$_POST;
	reenable_submit();

	//universal data source coding
	if($SQL){
		if(trim($datasourceCnx))$datasourceCnx=explode(',',$datasourceCnx);
		ob_start();
		$SQLResult=q(stripslashes($SQL), $datasourceCnx, O_ARRAY_ZEROBASED, ERR_ECHO);
		$err=ob_get_contents();
		ob_end_clean();
		if($err){
			echo $err;
			error_alert('Error in sql query or connection');
		}
		if(!$SQLResult)error_alert('Your query does not return any records');
	}else{
		$fp=fopen($_FILES['uploadFile_1']['tmp_name'], 'r');
	}
	$_i_900=-1; #records idx
	if($Data=='(Custom import)'){
		/*
		$importProfile='insertProfile';
		$importProfileDescription='sample profile';
		*/
		if($importProfile && !strlen($importProfileDescription))error_alert('You are choosing to create or update an import profile; you must enter a quick description of this profile');

		//make excludeColumns an array
		if($excludeColumns=trim($excludeColumns)){
			$excludeColumns=explode(',',$excludeColumns);
			foreach($excludeColumns as $n=>$v){
				$v=trim($v);
				if(!$v){
					unset($excludeColumns[$n]);
					continue;
				}
				$excludeColumns[strtolower($v)]=$v;
				unset($excludeColumns[$n]);
			}
		}
		//translation table
		if($columnMappings=trim($columnMappings)){
			$columnMappings=preg_split('/[\n\r]+/',$columnMappings);
			foreach($columnMappings as $n=> $v){
				$v=trim($v);
				if(!$v || !preg_match('/^([_a-z0-9 ]+):([_a-z0-9 ]+)$/i',$v,$m)){
					unset($columnMappings[$n]);
					continue;
				}
				$colMappings[preg_replace('/[^_A-Za-z0-9]+/','',strtolower(trim($m[1])))]=preg_replace('/[^_A-Za-z0-9]+/','',strtolower(trim($m[2])));
				$importFieldMap[$Data]['fields'][preg_replace('/[^_A-Za-z0-9]+/','',strtolower(trim($m[2])))]['label'][]=strtolower(trim($m[1]));
			}
		}
		//set values
		if($setValues=trim($setValues)){
			$setValues=explode("\n",$setValues);
			foreach($setValues as $n=>$v){
				unset($setValues[$n]);
				$v=trim($v);
				if(!$v)continue;
				$a=explode(':',$v);
				$k=strtolower($a[0]);
				unset($a[0]);
				$val=trim(implode(':',$a));
				$setValues[$k]=$val;
			}
		}
		//control fields
		if(trim($ctrlFields)){
			$ctrlFields=explode(',',trim($ctrlFields));
			foreach($ctrlFields as $n=>$v){
				unset($ctrlFields[$n]);
				$ctrlFields[$v]='';
			}
		}
		
		//table name and tableFields
		if($operation=='updateTable'){
			$table=$tableName;
			ob_start();
			$tableFields=q("EXPLAIN $table", O_ARRAY);
			$err=ob_get_contents();
			ob_end_clean();
			if($err){
				prn($err);
				mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,
				get_globals($err),$fromHdrBugs);
				error_alert('error finding table '.$table. ', perhaps it has been deleted');
			}
			foreach($tableFields as $n=>$v){
				if($v['Key']=='PRI')$primaryKey=strtolower($v['Field']);
				//cf
				if($ctrlFields)
				foreach($ctrlFields as $o=>$w){
					if(preg_match('/(^|_)'.$o.'$/i',$v['Field'])){
						$ctrlFields[$o]=$v['Field'];
					}
				}
				$tableFields[strtolower($v['Field'])]=$v;
				unset($tableFields[$n]);
			}
			if($updateRows){
				if(!$tableFields[strtolower($targetMergeField)])error_alert('You checked "Update rows"; however the field "$targetMergeField" is not present in the table.  Please select the name of a unique-values field in the table');
				$e=$tableFields[strtolower($targetMergeField)];
				if($e['Key']!=='PRI' && $e['Key']!=='UNI'){
					$a=q("SELECT COUNT(*) AS f1, COUNT(DISTINCT $targetMergeField) AS f2 FROM $table WHERE $targetMergeField!='' AND $targetMergeField IS NOT NULL", O_ROW);
					if($a['f1']>0 && $a['f2']<$a['f1']){
						error_alert('Sorry, I cannot import this data.  You checked "Update rows" however the field ($targetMergeField) that you selected for comparison DOES NOT contain unique values.  You must resolve this before importing with Update rows selected');
					}else if(!$hierarchy){
						error_alert("I will attempt to update values by comparing field $arrowMergeField to $targetMergeField - however this field is NOT indexed as unique or a primary key in the table and you should do this to prevent future information loss",1);
					}
					
				}
			}
		}else{
			$newTableName=preg_replace('/[^_a-z0-9]+/i','',$newTableName);
			$table=($newTableName ? $newTableName : 'importtable_'.time());
			$tableFields=array();
		}
		//first recurse for data values and quality
		while($r=get_row(array('ignore_hierarchy'=>true))){ 
			$_i_900++;
			if($_i_900==0){
				foreach($r as $n=>$field){
					$field=preg_replace('/[^_A-Za-z0-9]/','',$field);
					//this will exclude this column from any operations afterward
					if($excludeColumns[strtolower($field)])$excluded++;

					$data[$n]['unique']=true;
					$data[$n]['replete']=true;
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
				if(count($fields) - $excluded < 1)error_alert('You do not have any importable fields (excluded='.$excluded.')');
				$f=$hierarchyForeignKeyField;
				if($f){
					if(!$fieldMap[strtolower($f)]){
						$n++;
						$fields[strtolower($f)]=$f;
						$fieldMap[strtolower($f)]=$n;
						$fieldKeys[$n]=$f;
					}
				}
				continue;
			}
			foreach($r as $n=>$v){
				$v=trim($v);
				$integrity=dataintegrity($v);
				if(strlen($v) && !($interpretNull && strtolower($v)=='null')){
					$data[$n]['present']++;
					$data[$n]['integrity']=max($data[$n]['integrity'], $integrity);
					if($data[$n]['integrity']==DATA_FLOAT){
						$data[$n]['decimals']=max($data[$n]['decimals'], strlen(end(explode('.',$v))));
					}
					if($interpretNull && strtolower($v)=='null')$data[$n]['has_null']=true;
				}
				if($data[$n]['unique']){
					if($values[$n][strtolower($v)] && !($interpretNull && strtolower($v)=='null'))unset($data[$n]['unique']);
					$values[$n][strtolower($v)]=1;
				}
				if($data[$n]['replete']){
					if(!trim(preg_replace('/^null$/i','',$v)))unset($data[$n]['replete']);
				}
				$data[$n]['length']=max($data[$n]['length'],strlen($v));
			}
		}
		$ctrlFieldsAttributes=array(
			'editdate'=>array(
				'create'=>'EditDate TIMESTAMP ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT \'Appended '.date('Y-m-d').' by import mgr.\'',
				'attributes'=>array(
					'Field'=>'EditDate',
					'Type'=>'TIMESTAMP',
					'Null'=>'YES',
				),
			),
			'editor'=>array(
				'create'=>'Editor CHAR(20) NULL COMMENT \'Appended '.date('Y-m-d').' by import mgr.\'',
				'attributes'=>array(
					'Field'=>'Editor',
					'Type'=>'CHAR(20)',
					'Null'=>'YES',
				),
			),
			'createdate'=>array(
				'create'=>'CreateDate DATETIME NULL COMMENT \'Appended '.date('Y-m-d').' by import mgr.\'',
				'attributes'=>array(
					'Field'=>'CreateDate',
					'Type'=>'DATETIME',
					'Null'=>'YES',
				),
			),
			'creator'=>array(
				'create'=>'Creator CHAR(20) NOT NULL DEFAULT \''.$MASTER_DATABASE.'\' COMMENT \'Appended '.date('Y-m-d').' by import mgr.\'',
				'attributes'=>array(
					'Field'=>'Creator',
					'Type'=>'CHAR(20)',
					'Null'=>'NOT NULL',
					'Default'=>$MASTER_DATABASE,
				),
			),
		);
		unset($sqls);
		$str='';
		if($operation=='updateTable'){
			//add the fields to the table which were not there before
			if($createColumnsDynamically){
				foreach($fields as $n=>$v){
					if($tableFields[$n])continue;
					if($excludeColumns[$n])continue;
					if($colMappings[$n])continue;
					$str.='ADD `'.$v.'`';
					$str.=' '.sql_attributes($v,$data[$fieldMap[$n]]);
					$str.=' COMMENT \''.($fieldComments[$n] ? str_replace('\'','\'\'',stripslashes($fieldComments[$n])) : 'Appended '.date('Y-m-d')).'\'';
					$str.=','."\n";
					$tableFields[strtolower($v)]=$sql_attributes['current'];
				}

				if($ctrlFields)
				foreach($ctrlFields as $n=>$v){
					if($v)continue;
					$str.='ADD '.$ctrlFieldsAttributes[$n]['create'];
					$str.=','."\n";
					$ctrlFields[$n]=current(explode(' ',$ctrlFieldsAttributes[$n]['create']));
					$tableFields[$n]=$ctrlFieldsAttributes[$n]['attributes'];
				}
				$str=rtrim($str,"\n,");
				if($str)$sqls[]='ALTER TABLE '.$table."\n".$str;
			}
		}else{
error_alert(' to '.__LINE__);

			if(q("SHOW TABLES LIKE '$table'", O_ROW))error_alert('The table '.$table.' already exists');
			$str='CREATE TABLE '.$table.'('."\n";
			foreach($fields as $n=>$v){
				if($excludeColumns[$n])continue;
				if($colMappings[$n])continue;
				//map out as if an existing table
				$tableFields[$n]['Field']=$v;
				$str.='`'.$v.'`';
				$str.=' '.sql_attributes($v,$data[$fieldMap[$n]],array('mode'=>'insert'));
				$str.=','."\n";
			}
			if($ctrlFields)
			foreach($ctrlFields as $n=>$v){
				if($v)continue;
				$str.=$ctrlFieldsAttributes[$n]['create'];
				$str.=','."\n";
				$ctrlFields[$n]=current(explode(' ',$ctrlFieldsAttributes[$n]['create']));
				$tableFields[$n]=$ctrlFieldsAttributes[$n]['attributes'];
			}
			$str=rtrim($str,"\n,");
			$str.=')';
			$str.='COMMENT=\''.($newTableComment ? str_replace('\'','\'\'',stripslashes($newTableComment)) : '').(strlen($newTableComment)<54 ? '['.date('Y-m-d').']' : '').'\'';
			$sqls[]=$str;
			
			//2013-03-09: make sure all tables have a primary key (ID) field; the average user is not going to include
			if($data)foreach($data as $n=>$v){
				if($v['unique'] && $v['replete'] && $v['present']>=7 && 
					$v['integrity']==DATA_INT && preg_match('/_*id$/i',$fieldMap[$n])){
					//this is primary
					$primaryField=$fieldKeys[$n];
					break;
				}
			}
			if(!$primaryField && !$doNotCreatePrimaryField){
				$sqls[]='ALTER TABLE `'.$table.'` ADD `ID` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST';
			}else if($primaryField){
				$sqls[]='ALTER TABLE `'.$table.'` ADD PRIMARY KEY(`'.$primaryField.'`)';
				$sqls[]='ALTER TABLE `'.$table.'` CHANGE `'.$primaryField.'` `'.$primaryField.'` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT';
			}
		}
		if(!empty($sqls)){
			ob_start();
			foreach($sqls as $sql)q($sql, ERR_ECHO);
			$err=ob_get_contents();
			ob_end_clean();
			if($err){
				prn($err);
				mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,
				get_globals($err),$fromHdrBugs);
				error_alert('error '.($operation=='updateTable'?'adding additional fields to':'creating').' table; unable to perform import');
			}
		}
		unset($sqls);
		//universal data source coding
		if($SQL){
			$SQLResult=q(stripslashes($SQL), $datasourceCnx, O_ARRAY_ZEROBASED);	
		}else{
			$str=implode('',file($_FILES['uploadFile_1']['tmp_name']));
			if(!strstr($str,"\r\n") && strstr($str,"\r")){
				$str=str_replace("\r","\r\n",$str);
				$fp=fopen($_FILES['uploadFile_1']['tmp_name'],'w');
				fwrite($fp,$str,strlen($str));
			}
			$fp=fopen($_FILES['uploadFile_1']['tmp_name'], 'r');
		}
		$_i_900=-1; #records idx
		while($r=get_row()){
			$_i_900++;
			if(!$_i_900)continue;
			if(
				/* 2013-03-08: dropped this as unnecessary; the remaining tuples cover it all
				$operation=='updateTable' && */
				($updateRows || $hierarchy) && 
				strlen($r[$fieldMap[strtolower($arrowMergeField)]]) && 
				q("SELECT COUNT(*) FROM $table WHERE ".$targetMergeField."='".addslashes($r[$fieldMap[strtolower($arrowMergeField)]])."'", O_VALUE)
			){
				//update
				//added 2013-03-09
				if($hierarchy && $inHierarchy){
					prn('{query not executed}');
					continue;
				}
				$action='update';
				$sql='UPDATE '.$table.' SET '."\n";
				foreach($r as $n=>$v){
					$thisfield=$fieldKeys[$n];
					if($excludeColumns[strtolower($thisfield)])continue;
					if($colMappings[strtolower($thisfield)])$thisfield=$colMappings[strtolower($thisfield)];
					
					if(!$tableFields[strtolower($thisfield)])continue;

					//we always trim input
					$v=trim($v);
					if(strtolower(substr($v,0,7))=='mysql::'){
						$formula='mysql';
						$v=end(explode('::',$v));
						if(preg_match_all('/\$([_a-zA-Z0-9]+)/',$v,$m)){
							for($i=0;$i<count($m[1]);$i++){
								$v=str_replace('$'.$m[1][$i], trim($r[$fieldMap[strtolower($m[1][$i])]]),$v);
							}
						}
					}else if($interpretNull && trim(strtolower($v))=='null' && $tableFields[strtolower($thisField)]['Null']=='YES'){
						$formula='null';
						$v='NULL';
					}else{
						$formula='';
					}
					if($fieldMap[strtolower($arrowMergeField)]==$n/*this is the comparison column used */)continue;
					if($data[$n]['integrity']==DATA_DATETIME || $data[$n]['integrity']==DATA_DATE){
						//KEEP date entries from failing
						if(strlen($v) && preg_match('/[^-0-9 :]/',$v))$v=date('Y-m-d H:i:s',strtotime($v));
					}
					if($doNoOverwrite && !strlen($v))continue;
					$sql.='`'.$thisfield.'`='.
						($formula || is_numeric($v)?'':'\'').
						($formula ? $v : addslashes($v)).
						($formula || is_numeric($v)?'':'\'').','."\n";
				}
				if(!empty($setValues)){
					foreach($setValues as $n=>$v){
						if(!$tableFields[$n])continue;
						//2013-03-08 now the same as field coding
						//we always trim input
						$v=trim($v);
						if(strtolower(substr($v,0,7))=='mysql::'){
							$formula='mysql';
							$v=end(explode('::',$v));
							if(preg_match_all('/\$([_a-zA-Z0-9]+)/',$v,$m)){
								for($i=0;$i<count($m[1]);$i++){
									$v=str_replace('$'.$m[1][$i], trim($r[$fieldMap[strtolower($m[1][$i])]]),$v);
								}
							}
						}else if($interpretNull && trim(strtolower($v))=='null'){
							$formula='null';
							$v='NULL';
						}else{
							$formula='';
						}
						if(false)
						if($data[$n]['integrity']==DATA_DATETIME || $data[$n]['integrity']==DATA_DATE){
							//KEEP date entries from failing
							if(strlen($v) && preg_match('/[^-0-9 :]/',$v))$v=date('Y-m-d H:i:s',strtotime($v));
						}
						$sql.='`'.$n.'`='.
							($formula || is_numeric($v)?'':'\'').
							($formula ? $v : addslashes($v)).
							($formula || is_numeric($v)?'':'\'').','."\n";
					}
				}
				$sql=rtrim($sql,','."\n");
				$sql.=" WHERE `$targetMergeField`='".addslashes($r[$fieldMap[strtolower($arrowMergeField)]])."'";
				prn($sql);
			}else{
				$action='insert';
				$sql='INSERT INTO '.$table.' SET '."\n";
				foreach($r as $n=>$v){
					$thisfield=$fieldKeys[$n];
					if($excludeColumns[strtolower($thisfield)])continue;
					if($colMappings[strtolower($thisfield)])$thisfield=$colMappings[strtolower($thisfield)];
					if(!$tableFields[strtolower($thisfield)])continue;

					//if present, set values does not update the value
					if(isset($setValues[strtolower($thisfield)]))unset($setValues[strtolower($thisfield)]);
					
					//we always trim input
					$v=trim($v);
					if(strtolower(substr($v,0,7))=='mysql::'){
						$formula='mysql';
						$v=end(explode('::',$v));
						if(preg_match_all('/\$([_a-zA-Z0-9]+)/',$v,$m)){
							for($i=0;$i<count($m[1]);$i++){
								$v=str_replace('$'.$m[1][$i], trim($r[$fieldMap[strtolower($m[1][$i])]]),$v);
							}
						}
					}else if($interpretNull && trim(strtolower($v))=='null'){
						$formula='null';
						$v='NULL';
					}else{
						$formula='';
					}
					if($data[$n]['integrity']==DATA_DATETIME || $data[$n]['integrity']==DATA_DATE){
						//KEEP date entries from failing
						if(strlen($v) && preg_match('/[^-0-9 :]/',$v))$v=date('Y-m-d H:i:s',strtotime($v));
					}
					$sql.='`'.$thisfield.'`='.
						($formula || is_numeric($v)?'':'\'').
						($formula ? $v : addslashes($v)).
						($formula || is_numeric($v)?'':'\'').','."\n";
				}
				if(!empty($setValues)){
					foreach($setValues as $n=>$v){
						if(!$tableFields[$n])continue;
						//2013-03-08 now the same as field coding
						//we always trim input
						$v=trim($v);
						if(strtolower(substr($v,0,7))=='mysql::'){
							$formula='mysql';
							$v=end(explode('::',$v));
							if(preg_match_all('/\$([_a-zA-Z0-9]+)/',$v,$m)){
								for($i=0;$i<count($m[1]);$i++){
									$v=str_replace('$'.$m[1][$i], trim($r[$fieldMap[strtolower($m[1][$i])]]),$v);
								}
							}
						}else if($interpretNull && trim(strtolower($v))=='null'){
							$formula='null';
							$v='NULL';
						}else{
							$formula='';
						}
						if(false)
						if($data[$n]['integrity']==DATA_DATETIME || $data[$n]['integrity']==DATA_DATE){
							//KEEP date entries from failing
							if(strlen($v) && preg_match('/[^-0-9 :]/',$v))$v=date('Y-m-d H:i:s',strtotime($v));
						}
						$sql.='`'.$n.'`='.
							($formula || is_numeric($v)?'':'\'').
							($formula ? $v : addslashes($v)).
							($formula || is_numeric($v)?'':'\'').','."\n";
					}
				}
				$sql=rtrim($sql,','."\n");
				prn($sql);
			}
			/*
			standardize error output
			*/
			if($externalBatch){
				if(!$Batches_ID)$Batches_ID=q("INSERT INTO gen_batches 
				SET StartTime=NOW(), Status='Running', 
				Source='".addslashes($_FILES['uploadFile_1']['name'])."', 
				Type='import', 
				SubType='custom', 
				Process='".str_replace('.php','',end(explode('/',__FILE__)))."', 
				Description='$batchDescription', 
				Notes='mode=uploadFile line ".__LINE__."', 
				CreateDate=NOW(), 
				Creator='".sun()."'", O_INSERTID, ERR_ECHO);
			}
			ob_start();
			$insertid=q($sql, ERR_ECHO, O_INSERTID);
			$err=ob_get_contents();
			ob_end_clean();
			if($err){
				$errors++;
				if($errors<3)mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err),$fromHdrBugs);
			}
			if($externalBatch){
				$entries++;
				if(($action=='insert' && $insertid) || ($action=='update' && $primaryKey))
				q("INSERT INTO gen_batches_entries SET Batches_ID=$Batches_ID, ObjectName='$table',Objects_ID='".($action=='insert' ? $insertid : $r[$fieldMap[$primaryKey]])."', Action='$action', Status='".($err?0:16 /*highest*/)."'", ERR_ECHO);
			}
		}
		if($externalBatch){
			q("UPDATE gen_batches SET StopTime=NOW(), Quantity='$entries', Errors='$errors', Status='Complete', EditDate=CreateDate WHERE ID='$Batches_ID'");
			prn($qr);
		}
		if($importProfile){
			unset($_POST['ImportProfiles_ID']);
			$VarValue=base64_encode(serialize(array(
				//this contains everything
				'_POST'=>stripslashes_deep($_POST),
				'_FILES'=>stripslashes_deep($_FILES),
				'timestamp'=>time(),
			)));
			if($importProfile=='insertProfile'){
				$ImportProfiles_ID=((int)q("SELECT MAX(CAST(VarKey AS UNSIGNED)) FROM bais_settings WHERE UserName='".sun()."' AND VarGroup='Import' AND VarNode='Custom'",O_VALUE)+1);
				q("INSERT INTO bais_settings SET 
				UserName='".sun()."',
				VarGroup='Import',
				VarNode='Custom',
				VarKey='$ImportProfiles_ID',
				VarValue='$VarValue'", O_INSERTID);
				prn($qr);
			}else{
				//updateProfile
				q("UPDATE bais_settings SET VarValue='$VarValue' WHERE UserName='".sun()."' AND VarGroup='Import' AND VarNode='Custom' AND VarKey='$ImportProfiles_ID'");
				prn($qr);
			}
			q("UPDATE gen_batches SET Profiles_ID='$ImportProfiles_ID' WHERE ID='$Batches_ID'");
			prn($qr);
		}
	}else{
		//2013-03-09, just a note that this section hasn't been touched or used in a LONG time
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
				$_SESSION['special']['imports'][$s_key]['errors'][$_i_900]=$exception;
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
					$ID=q("INSERT INTO finan_items SET ResourceToken='$ResourceToken', CreateDate=NOW(), Creator='".sun()."'", O_INSERTID);
				}
			}
			require($processingComponent);
			if($error_alert_2['errors']){
				$_SESSION['special']['imports'][$s_key]['errors'][$_i_900]=$error_alert_2['errors'];
			}
		}
	}
	//make sure the file contains required fields; map the fields out
	$_SESSION['special']['imports'][$s_key]['records']=$_i_900;
	$_SESSION['special']['imports'][$s_key]['endtime']=time();
	//make sure the file contains records

	//we assume we are in components folder and that the importmanager is one directory up
	if($file=$postImportApplication){
		$file='/console/'.$file;
		if(!strstr($file,'?'))$file.='?';
		if(substr($file,-1)!=='?')$file.='&';
		$file.='key='.$s_key.'&Batches_ID='.$Batches_ID;
	}else{
		$file=current(explode('/components/',__FILE__));
		$file=str_replace($_SERVER['DOCUMENT_ROOT'],'',$file);
		$file='/'.trim($file,'/').'/importmanager.php';
		$file.='?mode=finished&key='.$s_key;
	}
	?><script language="javascript" type="text/javascript">
	window.parent.location='<?php echo $file;?>';
	</script><?php
	prn($file.'?mode=finished&key='.$s_key);
	eOK();
}else if($submode=='viewTable'){
	//------------------------------ begin -----------------------------
	if(!$maxTextLength)$maxTextLength=75;
	ob_start();
	$a=q("EXPLAIN $tableName", O_ARRAY, ERR_ECHO);
	$err=ob_get_contents();
	ob_end_clean();
	if($err)prn($err,1);
	$getValues=q("SELECT COUNT(*) FROM $tableName", O_VALUE);
	?><h2><?php echo $tableName;?></h2>
	<p class="gray">
	Currently <?php echo $getValues?> records in this table
	</p>
	<table class="yat">
	<thead>
	<tr>
	<th>&nbsp;</th>
	<th>Field</th>
	<th>Type</th>
	<th>NULL</th>
	<th>Default</th>
	<?php if($getValues){ ?>
	<th>Values</th>
	<?php } ?>
	<th>Unique</th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach($a as $v){
		extract($v);

		$nulls=q("SELECT COUNT(*) FROM $tableName WHERE `$Field` IS NULL", O_VALUE);
		$blanks=q("SELECT COUNT(*) FROM $tableName WHERE `$Field`='' OR `$Field`='0000-00-00' OR `$Field`='00:00:00' OR `$Field`='0000-00-00 00:00:00'", O_VALUE);
		$w=q("SELECT `$Field`,COUNT(*) FROM $tableName WHERE `$Field` IS NOT NULL AND `$Field`!='' AND `$Field`!='0000-00-00' AND `$Field`!='00:00:00' AND `$Field`!='0000-00-00 00:00:00' GROUP BY `$Field` ORDER BY `$Field`", O_COL_ASSOC);
		$unique=((count($w)+$nulls+$blanks==$getValues) && ($blanks<2));
		
		if($Key=='UNI'){
			$uniqueType='Keyed';
		}else if($Key=='PRI'){
			$uniqueType='Primary';
		}else if($unique){
			$uniqueType='data';
		}else{
			$uniqueType='';
		}

		?><tr>
		<td>&nbsp;</td>
		<td><strong><?php echo $Field;?></strong></td>
		<td><?php 
		$str=str_replace('unsigned','+',$Type);
		if(preg_match('/\b(enum|set)\b/i',$Type,$m)){
			echo strtoupper($m[1]);
		}else{
			echo strtoupper($str);
		}
		?></td>
		<td><?php echo $Null=='YES'?'YES':''?></td>
		<td><?php
		$str=preg_replace('/0000-00-00|00:00:00/','',$Default);
		$str=str_replace('CURRENT_TIMESTAMP','Timestamp',$str);
		echo $str;
		?></td>
		<?php if($getValues){ ?>
		<td><?php
		/*
		if a text or longtext field, truncate
		*/
		?><select name="null" class="values minimal">
		<option value=""><?php
		$vs=array();
		$vs[]=($w?count($w).' value'.(count($w)>1?'s':''):'Empty');
		if($blanks)$vs[]=$blanks.' blank'.($blanks>1?'s':'');
		if($nulls)$vs[]=$nulls.' null'.($nulls>1?'s':'');
		echo implode('; ',$vs);
		?></option>
		<?php
		if($w)foreach($w as $val=>$count){
			$len=strlen($val);
			$val=strip_tags($val);
			$HTML=(strlen($val)<$len);
			?><option><?php echo ($HTML?'[HTML] ':'').h(strlen($val) ? substr($val,0,$maxTextLength).(strlen($val)>$maxTextLength?'...':'') : '(blank)').(!$unique ? ' ('.$count.')':'');?></option><?php
		}
		?>
		</select><?php
		?></td>
		<?php } ?>
		<td><?php 
		if( !($getValues==0 && $uniqueType=='data') )echo $uniqueType;
		?></td>
		<?php
		
		?></td>
		</tr><?php
	}
	?>
	</tbody>
	</table><?php
	eOK();
	//--------------------------------------------------
}
?>
<div id="importManager">
<?php
//populate import profile
if($ImportProfiles_ID && $a=q("SELECT VarValue FROM bais_settings WHERE UserName='".sun()."' AND VarGroup='Import' AND VarNode='Custom' AND VarKey='$ImportProfiles_ID'", O_VALUE)){
	$a=unserialize(base64_decode($a));
	unset($a['_POST']['mode'], $a['_POST']['submode']);
	extract($a['_POST']);
	$lastFile=$a['_FILES']['uploadFile_1'];
}else{
	unset($ImportProfiles_ID);
}

if($mode=='finished'){
	//report
	mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
	extract($_SESSION['special']['imports'][$s_key]);
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
	<h1>Import Manager</h1>
	<?php
	ob_start(); //-- buffer for tabs --
	if($profiles=q("SELECT VarKey, VarValue FROM bais_settings WHERE UserName='".sun()."' AND VarGroup='Import' AND VarNode='Custom'", O_COL_ASSOC)){
		?><div style="position:relative;"><h2 class="gray"><?php if($ImportProfiles_ID){ ?>In profile <?php echo $ImportProfiles_ID;?>:<?php } ?> <?php echo $importProfileDescription;?>&nbsp;&nbsp;<span style="font-size:13px;">[<a style="cursor:pointer;" id="loadProfile">Load Profile</a>] <?php
		if($ImportProfiles_ID){
			?>[<a href="importmanager.php?Data=(Custom+import)">Clear Profile</a>]<?php
		}
		?></span></h2>
		<div id="modalpopup" class="messagepop pop">
		<table class="yat">
		<thead>
		<tr>
		<th>&nbsp;</th>
		<th>Name</th>
		<th>Last Used</th>
		<th>Data Source</th>
		<th>Records</th>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach($profiles as $n=>$v){
			$v=unserialize(base64_decode($v));
			?><tr <?php echo $n==$ImportProfiles_ID?'class="highlighted"':''?>>
			<td>[<a href="importmanager.php?ImportProfiles_ID=<?php echo $n?>">load</a>]</td>
			<td><?php echo $v['_POST']['importProfileDescription'];?></td>
			<td><?php echo date('n/j/Y \a\t g:iA',$v['timestamp']);?></td>
			<td><?php echo $v['_FILES']['uploadFile_1']['name'];?></td>
			<td>&nbsp;</td>
			</tr><?php
		}
		?>
		</tbody>
		</table>
		
			<a class="close" href="/">Cancel</a>
		</div>
		</div><?php
	}
	?>
	<h3>Select the type of data to import </h3>
	<p><?php
	foreach($importFieldMap as $n=>$v){
		?><label><input name="Data" type="radio" value="<?php echo $n?>" <?php echo $Data==$n?'checked':''?> onchange="dChge(this);setDocs(this.value);" />
		<?php echo $n?></label>	<br />
		<?php
	}
	?>
	</p>
	<div class="sectionA">
	<h3>Select a CSV file </h3>
	<p><?php 
	if($lastFile){
		?><span id="lastFile" class="gray"><?php echo 'Last file uploaded was <strong>'.$lastFile['name'].'</strong>, '.round($lastFile['size']/1024,2).'K<br />';?></span><br /><?php
	}
	?>
	<input name="uploadFile_1" type="file" id="uploadFile_1"><br />
	<strong>..or Select a data Source</strong><br />
	
	SQL Query: <br />
	<textarea name="SQL" cols="45" rows="3" id="SQL" onchange="dChge(this);"><?php echo h($SQL);?></textarea>
	<br />
	Connection:
	<input name="datasourceCnx" type="text" id="datasourceCnx" value="<?php echo h($datasourceCnx);?>" onchange="dChge(this);" />
	<span class="gray">(optional: host,user,password,database)</span><br />
	</p>
	</div>
	<div id="f2" style="display:<?php echo $Data=='(Custom import)'?'block':'none';?>">
	Action to perform: 
	<label>
	<input name="operation" type="radio" value="updateTable" onchange="dChge(this);toggle1('updateTable');" <?php echo $operation=='updateTable'?'checked':''?> />
	Add to an existing table</label>
	&nbsp;&nbsp;&nbsp;
	<label><input name="operation" type="radio" value="insertTable" onchange="dChge(this);toggle1('insertTable');" <?php echo $operation=='insertTable' || !$operation?'checked':''?> /> Create a new table</label>
	<br />
	<div id="optionsWrapper">
	<div id="insertTableOptions" style="display:<?php echo $operation=='updateTable'?'none':'block';?>">
	Name of new table: 
	  <input name="newTableName" type="text" id="newTableName" value="<?php echo $newTableName;?>" size="25" maxlength="64" onchange="dChge(this);" />
	  <br />
	  (Optional) description/comment: 
	  <input name="newTableComment" type="text" id="newTableComment" value="<?php echo $newTableComment;?>" size="35" maxlength="64" onchange="dChge(this);" />
	</div>
	<div id="updateTableOptions" style="display:<?php echo $operation=='updateTable'?'block':'none';?>">
	<?php
	$t=q("SHOW TABLES IN $MASTER_DATABASE", O_ARRAY);
	$qx['useRemediation']=false;
	foreach($t as $n=>$v){
		$table=$v['Tables_in_'.$MASTER_DATABASE];
		ob_start();
		q("SHOW CREATE VIEW $table", ERR_ECHO);
		$err=ob_get_contents();
		ob_end_clean();
		if(!$err){
			continue;
		}
		ob_start();
		$c=q("SELECT COUNT(*) FROM $table", O_VALUE, ERR_ECHO);
		$err=ob_get_contents();
		ob_end_clean();
		if($err){
			continue;
		}
		$tables[strtolower($table)]['name']=$table;
		$tables[strtolower($table)]['count']=$c;
		ob_start();
		$create=q("SHOW CREATE TABLE $table", O_ROW, ERR_ECHO);
		$err=ob_get_contents();
		ob_end_clean();
		if($err){
			continue;
		}
		$create=$create['Create Table'];
		$create=explode('COMMENT=',$create);
		if($create[1]) $tables[strtolower($table)]['comment']=str_replace('by Samuel','',trim(trim($create[1]),'\''));
	}
	$tables=subkey_sort($tables,'name');
	?>
	Table to add records to: <select name="tableName" id="tableName" onchange="dChge(this);" style="width:400px;">
	<option value="">&lt;Select table..&gt;</option>
	<?php
	foreach($tables as $n=>$v){
		?><option <?php echo $v['count']?'':'class="gray"';?> value="<?php echo $v['name'];?>" <?php echo $tableName==$v['name']?'selected':''?>><?php echo $v['name'].($v['count']>0 ? '('.$v['count'].')' : '').'  '.$v['comment'];?></option><?php
	}
	?>
	</select>&nbsp;&nbsp;<a href="#" id="viewTable">View table..</a>
	<br />
	<input type="hidden" name="updateRows" value="0" />
	<label><input name="updateRows" type="checkbox" id="updateRows" value="1" onchange="dChge(this);" <?php echo $updateRows || !isset($updateRows) ? 'checked':''?> />
	<u>Update rows</u></label> based on import column:
<input name="arrowMergeField" type="text" id="arrowMergeField" value="<?php echo $arrowMergeField?>" size="6" onchange="dChge(this);" />
; compare  this to this field in the table: 
	 <input name="targetMergeField" type="text" id="targetMergeField" value="<?php echo $targetMergeField ? $targetMergeField : '(same)';?>" size="12" onfocus="if(this.value=='(same)'){this.value='';this.className='';}" class="<?php echo $targetMergeField?'':'gray';?>" onblur="if(this.value==''){this.value='(same)';this.className='gray';}" onchange="dChge(this);" tabindex="-1" />
	</div>


	<fieldset><legend><h3>Column Handling</h3></legend>
	<input type="hidden" name="createColumnsDynamically" value="0" />	
	<label><input name="createColumnsDynamically" type="checkbox" id="createColumnsDynamically" value="1" onchange="dChge(this);" <?php echo $createColumnsDynamically?'checked':''?> onclick="g('excludeColumns').disabled=!this.checked;" />
	Create new columns dynamically</label><br />
	<span class="gray">(If checked, any column in the import file that does not match the table will be created)</span>		
	<br />
	<br />

	
	Exclude these columns: <input name="excludeColumns" type="text" id="excludeColumns" value="<?php echo $excludeColumns;?>" size="35" maxlength="255" onchange="dChge(this);" <?php echo !$createColumnsDynamically?'disabled':''?> />
	<br />
	<br />
	Add control fields if not present: 
	<select name="ctrlFields" id="ctrlFields" onchange="dChge(this);cfAlert(this);">
	  <option value="">(none)</option>
	  <option value="editdate" <?php echo $ctrlFields=='editdate'?'selected':''?>>Timestamp (EditDate)</option>
	  <option value="editdate,editor" <?php echo $ctrlFields=='editdate,editor'?'selected':''?>>Timestamp and Editor</option>
	  <option value="editdate,editor,createdate,creator" <?php echo $ctrlFields=='editdate,editor,createdate,creator'?'selected':''?>>Timestamp, Editor, and CreateDate/Creator</option>
	  </select>
	<span class="gray">(users and times)</span><br />
	<br />
	Column mappings: <span class="gray">(each on a new line separated by a colon, as importcolumn1:targetfield1)</span><br />
	<textarea name="columnMappings" cols="45" rows="3" id="columnMappings"><?php echo h($columnMappings);?></textarea>
	<br />
	<br />
	Additional Import Values: <span class="gray">(each on a new line separated by a colon, as CreateDate:mysql::NOW() or simply CreateDate:2012-05-13)</span><br /> 
	<textarea name="setValues" cols="45" rows="3" id="setValues"><?php echo h($setValues);?></textarea>
	<br />
	Two important facts on additional values: 1) these are not used if the field is present or mapped out in the data source, 2) they must be present in the receiving table <br />
	<br />
	<input type="hidden" name="doNotOverwrite" value="0" />	
	<label><input name="doNotOverwrite" type="checkbox" id="doNotOverwrite" value="1" <?php echo !isset($doNotOverwrite) || $doNotOverwrite?'checked':''?> onchange="dChge(this);" />
When updating, do not overwrite non-blank values with blank values</label>
	<br />

	<br />
	<label>
	<input name="interpretNull" type="checkbox" id="interpretNull" value="1" onchange="dChge(this);" <?php echo $interpretNull || !isset($interpretNull)?'checked':''?> />
Interpret keyword &quot;NULL&quot; as a null value</label>
	<br />
	<br />
	<strong>Advanced</strong><br />
	<input type="hidden" name="hierarchy" value="0" />
	<label>
	<input name="hierarchy" type="checkbox" id="hierarchy" value="1" onchange="dChge(this);" <?php echo $hierarchy?'checked':''?> />
	Field hierarchy (internal) declared in column</label> named: 
	<input name="hierarchyNameField" type="text" id="hierarchyNameField" onchange="dChge(this);" value="<?php echo $hierarchyNameField;?>" size="7" />
	; foreign key field name: 
	<input name="hierarchyForeignKeyField" type="text" id="hierarchyForeignKeyField" onchange="dChge(this);" value="<?php echo $hierarchyForeignKeyField;?>" size="8" />
	<br />
	<input type="hidden" name="doNotCreatePrimaryField" value="0" />
	<label><input name="doNotCreatePrimaryField" type="checkbox" id="doNotCreatePrimaryField" value="1" <?php echo $doNotCreatePrimaryField?'checked':''?> onchange="dChge(this);" /> <u>DO NOT</u> create a primary key (ID) field for new tables</label>
	<br />
	<input name="suppressPrimaryKeyInterpretation" type="hidden" value="0" />
	<label><input type="checkbox" name="suppressPrimaryKeyInterpretation" id="suppressPrimaryKeyInterpretation" value="1" <?php echo $suppressPrimaryKeyInterpretation || !isset($suppressPrimaryKeyInterpretation) ? 'checked':''?> />
	<u>DO NOT</u> interpret a primary key from imported columns</label><br />
	..or, use the following column: 
	<input name="PrimaryKeyColumn" type="text" id="PrimaryKeyColumn" value="<?php echo $PrimaryKeyColumn?>" onchange="dChge(this);" <?php echo $suppressPrimaryKeyInterpretation || !isset($suppressPrimaryKeyInterpretation)?'disabled':''; ?> tabindex="-1" />
	<br />
	<br />
 
	
	Create custom fields with prefix: <input name="PRE" type="text" id="PRE" value="<?php echo $PRE?>" size="5" maxlength="4" onchange="dChge(this);" <?php echo $Data=='(Custom import)'?'disabled':''?> />
	<br />
	List custom fields in import file (separate by commas): <input name="CustomFieldColumns" type="text" id="CustomFieldColumns" value="<?php echo $CustomFieldColumns;?>" size="35" maxlength="255" onchange="dChge(this);" <?php echo $Data=='(Custom import)'?'disabled':''?> />
	</fieldset>
	<br />
	<br />
	Import profile options: 	
	<select name="importProfile" id="importProfile" onchange="dChge(this);interlock091(this);">
      <option value="">(none)</option>
      <option value="insertProfile">Store this import as a <?php echo $ImportProfiles_ID?'new ':''?>profile</option>
	  <?php if($ImportProfiles_ID){ ?>
	  <option value="updateProfile">Update this current profile</option>
	  <?php } ?>
    </select>
	<br />
	Import quick description: 
	<input name="importProfileDescription" type="text" id="importProfileDescription" value="<?php echo h($importProfileDescription);?>" size="35" maxlength="255" onchange="dChge(this);" /> 
	<em class="gray">(required)</em>
	<br />
	<br />
	<input type="hidden" name="externalBatch" value="0" />
	<label><input name="externalBatch" type="checkbox" id="externalBatch" value="1" onchange="dChge(this);" <?php echo !isset($externalBatch) || $externalBatch?'selected':''?> />
Create an batch reference for this import;</label> description of batch:
<input name="batchDescription" type="text" id="batchDescription" value="<?php echo h($batchDescription);?>" size="35" maxlength="255" onchange="dChge(this);" />
	<br />
	<br />
	After import, redirect to the following application page: 
	<input name="postImportApplication" type="text" id="postImportApplication" value="<?php echo h($postImportApplication);?>" size="35" onchange="dChge(this);" />
	<br />
	<span class="gray">(a value for Batches_ID=n will be appended to the query string)</span>
	</div>
	</div>
	
	  <br />
      <br />
      <input type="button" name="Button" id="uploadFileButton" value="Begin Import" onclick="uploadFile();" />
  &nbsp;
	  <span id="uploadPending" style="display:none;"><img src="/images/i/ani/ani-fb-orange.gif" width="16" height="11" /> </span>
	  
	<br />
	<input name="mode" type="hidden" id="mode" value="importManager" />
	<input name="submode" type="hidden" id="submode" value="uploadFile" />
	<input name="ImportProfiles_ID" type="hidden" id="ImportProfiles_ID" value="<?php echo $ImportProfiles_ID;?>" />
	<?php	
	get_contents_tabsection('form');
	?>
	<iframe name="d1" id="d1" src="importmanager.php?mode=importManager&submode=documentation&suppressCustomTitle=1&Data=<?php echo $Data?>" width="100%" height="<?php echo 400;?>" /></iframe>

	<?php
	get_contents_tabsection('specs');
	#if(minrole()<=ROLE_ADMIN)$adminMode=1;
	CMSB('importManagerHelp');
	get_contents_tabsection('help');
	
	tabs_enhanced(
		array(
			'form'=>array(
				'label'=>'Form'
			),
			'specs'=>array(
				'label'=>'Specifications'
			),
			'help'=>array(
				'label'=>'Help'
			),
		)
	);
}
?>
  </p>
</div>
	