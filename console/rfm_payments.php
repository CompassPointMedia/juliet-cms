<?php 
/*
2012-12-30: pulling over receive payments from GLF into console for RFM.  The primary difference is that payment lines are being allocated to various line items of the invoice.  This was not done with GLF; all invoices had only one line item.
* integrating as much as possible with systementry, we are going to create a specific profile for payments (headers) NAMED payments

*/
$f=str_replace('.php','.assets.php',__FILE__);
if(file_exists($f))require($f);
$f=($REDIRECT_URL ? $REDIRECT_URL : $SCRIPT_FILENAME);
if(end(explode('/',$f))==end(explode('/',__FILE__))){
	//identify this script/GUI
	$localSys['scriptGroup']='';
	$localSys['scriptID']='generic';
	$localSys['scriptVersion']='1.0';
	$localSys['pageType']='Properties Window';
	
	
//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');
}

//functions that are developed for various views - copied from systementry.php
function lib_nextHeaderNumber(){
	$a=q("SELECT HeaderNumber FROM finan_headers", O_COL);
	if(!$a)return 1;
	foreach($a as $n=>$v)$a[$n]=preg_replace('/[^-0-9]*/','',$v);
	foreach($a as $n=>$v)$a[$n]=(int) end(explode('-',$v));
	return max($a)+1;
}
function systementry_parse_vars($n){
	preg_match_all('/\{\{([_0-9a-zA-Z]+)\}\}/',$n,$m);
	for($i=0;$i<count($m[1]);$i++){
		if($m[1][$i]=='MASTER_PASSWORD' || $m[1][$i]=='SUPER_MASTER_PASSWORD')continue;
		$n=str_replace('{{'.$m[1][$i].'}}',$GLOBALS[$m[1][$i]],$n);
	}
	return $n;
}
function lib_calculate($f,$a){
	//2012-12-25
	if($f=='total'){
		return array_sum($a);
	}else if($f=='average'){
		return array_sum($a)/count($a);
	}else if($f=='count'){
		return count($a);
	}else if($f=='median'){
		sort($a);
		return $a[floor(count($a)/2)];
	}
}
function lib_proportion($totals,$apply,$applied=array()){
	$total=array_sum($totals);
	foreach($totals as $n=>$v){
		$i++;
		//first pass
		if($i<count($totals)){
			$out[$n]=round($apply * ($v/$total),2);
			$running+=$out[$n];
		}else{
			$out[$n]=round($apply-$running,2);//adjust last line item to penny
		}
	}
	if(empty($applied))return $out;
	//now see if any apply portions + out portions bust totals
	$i=0;
	foreach($totals as $n=>$v){
		$i++;
		if($i<count($totals)){
			if(round(abs($applied[$n] + $out[$n]),2) > round(abs($v),2)){
				$d=round((abs($applied[$n] + $out[$n]) - abs($v)) * ($v<1 ? -1 : 1),2);
				$adjust+=$d;
				$out[$n]-=$d;
			}
		}else{
			//evaluate "last drop" - if we were passed good data we will not have any discrpancies
			$out[$n]+=$d;
		}
	}
	return $out;
}

//------------------------ codeblock 1230052 ----------------------------------------
//a few security items
unset($tableSettings,$profileSettings,$raw);
//2012-12-12 do this for now, as this is passed and conflicting; any old systems like callback() use this?
unset($recordPKField);

//2012-12-30[modified] a little translation here
$object='finan_headers';
$identifier='payments-standard'; //2012-12-30 only one for now
if($systemProfile=q("SELECT p.*, t.Settings AS TableSettings, t.Name AS TableName FROM system_profiles p JOIN system_tables t ON p.Tables_ID=t.ID WHERE t.SystemName='$object' AND p.Identifier='payments-standard'", O_ROW)){
	$systemTable=array(
		'ID'=>$systemProfile['Tables_ID'],
		'Name'=>$systemProfile['TableName'],
		'Settings'=>$systemProfile['TableSettings'],
	);
	$_Profiles_ID_=$systemProfile['ID'];
	//manage variables
	if(strlen($systemTable['Settings'])>7)$tableSettings=unserialize(base64_decode($systemTable['Settings']));
	if(strlen($systemProfile['Settings'])>7)$profileSettings=unserialize(base64_decode($systemProfile['Settings']));
}else{
	if($systemTable=q("SELECT * FROM system_tables WHERE SystemName='$object'", O_ROW)){
		//manage variables
		if(strlen($systemTable['Settings'])>7)$tableSettings=unserialize(base64_decode($systemTable['Settings']));
	}else{
		$n=q("INSERT INTO system_tables SET
		SystemName='$object',
		Name='$object',
		KeyField='ID',
		Description='Table registered by rfm_payments',
		Type='table'",O_INSERTID);
		$systemTable=array(
			'ID'=>$n,
			'Name'=>$object,
			'SystemName'=>$object,
			'KeyField'=>'ID',
		);
	}
	if($systemProfile=q("SELECT * FROM system_profiles WHERE Tables_ID='".$systemTable['ID']."' AND Identifier='$identifier'", O_ROW)){
		//manage variables, _Profiles_ID_ is a pillow variable
		$_Profiles_ID_=$systemProfile['ID'];
	}else{
		//for now, we are not creating settings for this view - although theoretically at some point the rec. pymts window itself could be completely defined by settings, and this page (rfm_payments.php) would just be systementry.php called with the _Profiles_ID_
		$_Profiles_ID_=q("INSERT INTO system_profiles SET 
		Tables_ID='".$systemTable['ID']."',
		Identifier='$identifier',
		Type='Data View',
		Name='Receive Payments Standard View',
		Settings='',
		CreateDate=NOW(),
		Creator='".sun()."'", O_INSERTID);
		$systemProfile=array(
			'ID'=>$n,
			'Identifier'=>$identifier,
		);
	}
}
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
}
//------------ end edgar -------------

if(count($recordPKField)<>1)exit('table has a compound or missing primary key');
//------------------------ end codeblock 1230052 ----------------------------------------



if($mode=='updatePayment' || $mode=='insertPayment' || $mode=='deletePayment'){
	if(false){
		//---------------- begin charles ----------------
		if(md5(stripslashes($profile['raw']))!==$profile['raw_hash']){
			//if(!$profile['EditDate'])error_alert('variable profile.EditDate is not passed and is required');
			//if(!$profile['ID'])error_alert('variable profile.ID is now required');
			//if(strtotime(q("SELECT EditDate FROM system_profiles WHERE ID='".$profile['ID']."'", O_VALUE))>strtotime($profile['EditDate']))error_alert('Profile values have been updated after the current page was sent from the server.  Complete any update or insert operations if possible, and then refresh this page');
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
					WHERE ID='$n'");
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
		if($f=$profileSettings['submodes'][$submode]){
			if(file_exists($COMPONENT_ROOT.'/'.$f)){
				require($COMPONENT_ROOT.'/'.$f);
			}else{
				mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err='unused mode called'),$fromHdrBugs);
				error_alert('unable to locate submode component '.$f);
			}
		}	
		if(($s=$profileSettings['sub_table']) && $s['active']){
			if(!$s['post_processing_component'])error_alert('You have specified a sub table for this form, however there is no post processing component specified in the profile settings under sub_table.  Click the settings tab at the bottom and update this');
			if(!file_exists($COMPONENT_ROOT.'/'.$s['post_processing_component']))error_alert('The post processing component you specified ('.$s['post_processing_component'].') does not exist');
			require($COMPONENT_ROOT.'/'.$s['post_processing_component']);
		}
			
		/*
		identify date fields and number fields which will lose information (IL)
		
		*/
		//system error checking
		if(!$recordPKField[0]){
			mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err='variable recordPKField is not present'),$fromHdrBugs);
			error_alert($err.', developer has been notified');
		}
		if($mode=='deletePayment'){
			error_alert('delete mode not developed');
		}
		if($mode==$insertMode && !$$recordPKField[0]){
			unset($GLOBALS[$recordPKField[0]]);
		}
		foreach($GLOBALS as $n=>$v){
			if(!is_string($v))continue;
			if(strtolower($v)=='null'){
				$GLOBALS[$n]='PHP:NULL';
			}else if(strtolower($v)=='\null'){
				$GLOBALS[$n]=str_replace('\\','',$v);
			}
		}
		//note if quasi resource is present we always update
		if($quasiResourceTypeField)$GLOBALS[$quasiResourceTypeField]='1';
		$sql=sql_insert_update_generic($MASTER_DATABASE,$object,($quasiResourceTypeField ? $updateMode : $mode));
		prn($sql);
		ob_start();
		$n=q($sql,$mode==$insertMode?O_INSERTID:O_AFFECTEDROWS, ERR_ECHO);
		$err=ob_get_contents();
		ob_end_clean();
		if($err){
			prn($err);
			mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err),$fromHdrBugs);
			error_alert('Error in query, developer has been notified');
		}
		if($mode==$insertMode){
			foreach($objectFields as $key=>$v){
				if(strtolower($v['Field'])==strtolower($recordPKField[0])){
					$autoinc=($v['Extra']=='auto_increment'?1:0);
					break;
				}
			}
			if($autoinc){
				$GLOBALS[$recordPKField[0]]=$n;
			}else{
				//it is the value that was passed on the form
			}
		}else{
			$affected_rows=$n;
		}
		if($f=$profileSettings['post_processing_component_end']){
			if(file_exists($COMPONENT_ROOT.'/'.$f)){
				require($COMPONENT_ROOT.'/'.$f);
			}else{
				mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err='specified end processing component '.$f.' not present'),$fromHdrBugs);
				error_alert($err,1);
			}
		}
		if($mode==$insertMode && $navMode=='insert' && $quasiResourceTypeField){
			//2012-12-23: added (surprised I missed this) insert record and then update id and resourcetoken
			$newResourceToken=substr(date('YmdHis').rand('10000','99999'),3,16);
			$newID=quasi_resource_generic(
				$MASTER_DATABASE, 
				$object, 
				$newResourceToken, 
				$quasiResourceTypeField, 
				$sessionKeyField,
				$quasiResourceTokenField, 
				$recordPKField[0], 
				$creatorField, 
				$createDateField
			);
			?><script language="javascript" type="text/javascript">
			window.parent.sets['<?php echo $recordPKField[0];?>']='<?php echo $newID;?>';
			try{
			window.parent.g('<?php echo $quasiResourceTokenField;?>').value='<?php echo $newResourceToken;?>';
			}catch(e){}
			try{
			window.parent.<?php echo $quasiResourceTokenField;?>='<?php echo $newResourceToken;?>';
			}catch(e){}
			</script><?php
		}
		if($cbPresent){
			prn($recordPKField);
			$cbValue=$GLOBALS[$recordPKField[0]];
			if($n=$tableSettings['label']){
				$cbLabel=q("SELECT IF(TRIM('$n')!='',TRIM('$n'),".$recordPKField[0].") FROM $object WHERE ".$recordPKField[0]."='".$GLOBALS[$recordPKField[0]]."'", O_VALUE);
			}else{
				foreach(($relations=sql_table_relationships()) as $n=>$v){
					if(strtolower($v['table'])==strtolower($object)){
						$cbLabel=q("SELECT ".$v['label']." FROM $object WHERE ".$recordPKField[0]."='".$GLOBALS[$recordPKField[0]]."'", O_VALUE);
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
		goto bypass;
	}
	//-------------------- begin wesley ---------------------
	/*
	2012-12-31 copied and modified from GLF application
	2013-01-01 extensive updates on logic to get this truly down to the distribution line; also handling NEGATIVE LINE ITEMS on the invoice
	*/
	//clean up the data
	$Amount=str_replace(',','',$Amount);
	$Total=str_replace(',','',$Total);
	$Unallocated=str_replace(',','',$Unallocated);

	//basic fields present
	//values must be numeric and must be above zero
	//values must sum to the Total field
	if(!$Clients_ID)error_alert('Select a client first');
	if(!$HeaderNumber) error_alert('There is no check/confirmation number present');
	if(!strtotime($HeaderDate)) error_alert('Invalid Date for the check.');
	if(!preg_match('/^[0-9]*\.?[0-9]{0,2}$/',$Amount))
	error_alert('You have entered an incorrect amount in the Amount field (top of form)');
	foreach($ApplyTo as $n=>$v){
		$ApplyTo[$n]=str_replace(',','',$v);
		if(!trim($v) || trim($v)==0){
			unset($ApplyTo[$n]);
			continue;
		}
		if(preg_match('/-/',$v))$errors['negative']=true;
		if(!preg_match('/^[0-9]*\.?[0-9]{0,2}$/',$v)){
			$errors['badnumber']++;
		}
	}
	if(strlen($Unallocated) && !preg_match('/^[0-9]*\.?[0-9]{0,2}$/',$Unallocated))
	error_alert('You have a value in the unapplied funds field that is not a valid number');

	if($n=$errors['badnumber']){
		error_alert('You entered '.$n.' number value'.($n>1?'s':'').' that '.($n>1?'are':'is').' not valid');
	}else if($errors['negative']){
		error_alert('You cannot apply a negative amount to an invoice');
	}
	if(round($Total,2) + round($Unallocated,2)!=round($Amount,2))
	error_alert('Amount of check (top of form) and total applied (bottom of form) do not match');
	if(round($Total,2)!=round(array_sum($ApplyTo),2))
	error_alert('Abnormal error, the total field at the bottom does not equal the amount(s) you are applying');
	if(empty($ApplyTo))
	error_alert('You must enter at least one non-negative number value');


	if($mode==$insertMode){
		//------------------------- begin codeblock 3004373 ------------------------------
		//now get amounts applied to invoices!
		$applied=q("SELECT h.ID AS Headers_ID, h.Accounts_ID, h.HeaderNumber, h.AmountAppliedTo, h.OriginalTotal FROM _v_x_finan_headers_master h WHERE h.ID IN(".implode(',',array_keys($ApplyTo)).")", O_ARRAY_ASSOC);
		foreach($applied as $v){
			//no value can exceed the originaltotal less applied amounts [and for update mode, add back in the allocation of this payment]
			if($v['OriginalTotal'] - $v['AmountAppliedTo'] < $ApplyTo[$v['Headers_ID']])$errors['overpay'][]=$v['HeaderNumber'];
		}
		prn($applied);
		if($a=$errors['overpay'])error_alert('You have specified overpayment amounts for invoice(s) '.str_replace('/,([^]+)$/',' and $1',implode(', ',$a)));

		//here we go pardner		
		q("UPDATE finan_headers SET
		HeaderType='Payment',
		HeaderDate='".date('Y-m-d',strtotime($HeaderDate))."',
		HeaderStatus='Current',
		HeaderNumber='$HeaderNumber',
		ResourceType=1,
		ResourceToken='$ResourceToken',
		SessionKey='$SessionKey',
		Clients_ID='$Clients_ID',
		".(strlen($Contacts_ID)?"Contacts_ID=$Contacts_ID,":'')."
		".(strlen($Classes_ID)?"Classes_ID=$Classes_ID,":'')."
		Accounts_ID=$Accounts_ID,
		Notes='$Notes',
		CreateDate=NOW(),
		Creator='".sun()."'
		WHERE ID=$ID");
		prn($qr);
		
		//insert payment entry, probably will never do an update here
		if($e=q("SELECT Headers_ID FROM finan_payments WHERE Headers_ID=$ID", O_VALUE)){
			q("UPDATE finan_payments SET Types_ID='$Types_ID' WHERE Headers_ID=$ID");
		}else{
			q("INSERT INTO finan_payments SET
			Headers_ID='$ID',
			Types_ID='$Types_ID'");
		}
		prn($qr);

		//so now we have good amounts and we can apply the payments
		foreach($ApplyTo as $IID=>$amount){
			$TID=q("INSERT INTO finan_transactions SET
			Headers_ID=$ID,
			Accounts_ID=".$applied[$IID]['Accounts_ID'].",
			".(strlen($Classes_ID)?"Classes_ID=$Classes_ID,":'')."
			Extension='".number_format($amount,2)."',
			CreateDate=NOW(),
			Creator='".sun()."'",O_INSERTID);
			prn($qr);
			
			//now, distribute the amount among the transactions evenly
			#old - failed to exclude root transaction
			#$a=q("SELECT ID, Extension FROM finan_transactions WHERE Headers_ID=$IID", O_COL_ASSOC);

			$a=q("SELECT t.ID, t.Extension FROM finan_headers h JOIN finan_transactions t ON h.ID=t.Headers_ID WHERE h.ID=$IID AND h.Accounts_ID!=t.Accounts_ID", O_COL_ASSOC);

			$b=lib_proportion($a,$amount);
			foreach($b as $n=>$v){
				q("INSERT INTO finan_TransactionsTransactions SET
				ParentTransactions_ID=$TID,
				ChildTransactions_ID=$n,
				Type='Payment',
				AmountApplied='$v'");
				prn($qr);
			}
		}
		//insert rho transaction
		q("INSERT INTO finan_transactions SET Headers_ID=$ID,
		Accounts_ID=$Accounts_ID,
		Extension='-".$Amount."',
		CreateDate=NOW(),
		Creator='".sun()."'", O_INSERTID);
		prn($qr);

		if($Unallocated>0){
			$getAccount=array(
				'var'=>'UnallocatedFundsAccounts_ID',
				'typeName'=>'Other Current Asset',
				'typeCategory'=>'Asset',
				'name'=>'Unallocated',
				'description'=>'Portion of a payment not applied to any invoice',			
			);
			//-------------- begin dickens ------------
			$var=$getAccount['var'];
			if(!$$var){
				if(!($_Types_ID_=q("SELECT ID FROM finan_accounts_types WHERE Name='".$getAccount['typeName']."' AND Category='".$getAccount['typeCategory']."'", O_VALUE))){
					$_Types_ID_=q("INSERT INTO finan_accounts_types SET Name='".$getAccount['typeName']."', Category='".$getAccount['typeCategory']."', CreateDate=NOW(), Creator='".sun()."'", O_INSERTID);
				}
				if(!($$var=q("SELECT ID FROM finan_accounts WHERE Accounts_ID IS NULL AND Name='".$getAccount['name']."' AND Types_ID=$_Types_ID_", O_VALUE))){
					$$var=q("INSERT INTO finan_accounts SET Name='".$getAccount['name']."', Description='".$getAccount['description']."', Notes='".($getAccount['notes'] ? addslashes($getAccount['notes']) : "Added automatically by ".end(explode('/',__FILE__)))."', Types_ID=$Types_ID, CreateDate=NOW(), Creator='".sun()."'", O_INSERTID);
				}
			}
			//-------------- end dickens ------------

			//ente an offsetting unallocated allocation..
			q("INSERT INTO finan_transactions SET
			Headers_ID=$ID,
			Accounts_ID=$UnallocatedFundsAccounts_ID,
			Extension='".number_format($Unallocated,2)."',
			CreateDate=NOW(),
			Creator='".sun()."'");
			prn($qr);
		}
		//------------------------- end codeblock 3004373 ------------------------------
	}else{
		mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err='unused mode called'),$fromHdrBugs);
		error_alert('update of payments not yet developed');
		//original info
		if(!($o=q("SELECT h.OriginalTotal
		FROM _v_x_finan_headers_master h WHERE Headers_ID=$Headers_ID",O_ROW)))error_alert('asdf');
		
		//we cannot update the AMOUNT of a payment that has been deposited
		if($o['OriginalTotal']==array_sum($amounts) && $deposited=q("SELECT SUM(tt.AmountApplied), t2.Headers_ID FROM finan_transactions t JOIN finan_TransactionsTransactions tt ON t.ID=tt.ChildTransactions_ID JOIN finan_transactions t2 ON tt.ParentTransactions_ID=t2.ID WHERE t.Headers_ID=$Headers_ID AND tt.Type='Deposit' GROUP BY t.Headers_ID", O_ROW)){
			?><script language="javascript" type="text/javascript">
			if(confirm('You cannot modify a payment that has been deposited.  You must first delete the deposit from the bank account.  Would you like to open and view the deposit right now?')){
				window.open('asdf');
			}
			</script><?php
			eOK;
		}
		
		//asdf - move this above updateMode
		//no portion of a payment may be over-applied to an invoice		
		foreach($amounts as $Headers_ID=>$amount){
			if($amount > $OriginalTotal - $AmountAppliedTo)$err['overallocation'][]=$HeaderNumber;
		}
		if($a=$err['overallocation'])error_alert('You have entered amounts that would overpay the following invoices: '.preg_replace('/, ([^,]+)/',', and $1',implode(', ',$a)));
		
		
		
		
		
		q("UPDATE finan_headers
		SET
		HeaderNumber='$HeaderNumber',
		HeaderDate='".t($DateCredited)."',
		Notes='$Notes',
		EditDate=NOW(),
		Editor='".sun()."' WHERE ID=$ID");
		prn($qr);
		
		//insert payment entry
		q("UPDATE finan_payments SET
		Types_ID='$Types_ID' WHERE Headers_ID=$ID");
		prn($qr);
		
		//update root transaction
		q("UPDATE finan_headers h, finan_transactions t SET
		t.Extension='".$Total."',
		t.EditDate=NOW(),
		t.Editor='".sun()."' WHERE h.ID=t.Headers_ID AND t.Headers_ID=$ID AND h.Accounts_ID=t.Accounts_ID");
		prn($qr);

		foreach($ApplyTo as $n=>$v)if(!$v)unset($ApplyTo[$n]);
		$loop=$ApplyTo; //this is the desired application, Invoices -> amount
		//uset unallocated invoices
		if($AppliedTo=q("SELECT t.Headers_ID, tt.AmountApplied
			FROM finan_transactions t, finan_TransactionsTransactions tt, finan_transactions t2
			WHERE t.ID=tt.ChildTransactions_ID AND tt.ParentTransactions_ID=t2.ID AND t2.Headers_ID=$ID", O_COL_ASSOC)){
			foreach($AppliedTo as $n=>$v)$loop[$n]=$v;
		}
		
		//now recurse the combined set
		foreach($loop as $n=>$v){
			if(isset($ApplyTo[$n]) && isset($AppliedTo[$n])){
				//common to both - skip if the amount hasn't changed
				if($ApplyTo[$n]==$AppliedTo[$n])continue;
				if($ApplyTo[$n]==0){
					q("DELETE tt.* FROM
					finan_transactions t, finan_TransactionsTransactions tt, finan_transactions t2
					WHERE t.ID=tt.ParentTransactions_ID AND tt.ChildTransactions_ID=t2.ID AND
					t.Headers_ID=$ID AND
					t2.Headers_ID=$n");
					mail($developerEmail, 'Notice in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($notice='this was added 2012-09-17; previously TransTrans was zeroing out rows when invoice payments were being re-applied; now we are DELETING zero-value entries so that the invoice doesn\'t interlock on a zero-allocation'),$fromHdrBugs);
				}else{
					//allocate as necessary; simplified based on a one-line transaction
					q("UPDATE 
					finan_transactions t, finan_TransactionsTransactions tt, finan_transactions t2
					SET 
					t.Extension=-".$ApplyTo[$n].",
					AmountApplied='".$ApplyTo[$n]."'
					WHERE t.ID=tt.ParentTransactions_ID AND tt.ChildTransactions_ID=t2.ID AND
					t.Headers_ID=$ID AND
					t2.Headers_ID=$n");
				}
				prn($qr);
			}else if(isset($ApplyTo[$n]) && !isset($AppliedTo[$n])){
				//enter new transaction and allocation
				$Transactions_ID=q("INSERT INTO finan_transactions SET
				Headers_ID=$ID,
				Accounts_ID=$InvoiceAccounts_ID,
				Name='Rental Locating Payment',
				Description='Payment ".($Types_ID==1?'Check #':'Cash')." $HeaderNumber',
				Extension='-".$v."',
				CreateDate=NOW(),
				Creator='".sun()."'", O_INSERTID);
				prn($qr);
				q("INSERT INTO finan_TransactionsTransactions SET ParentTransactions_ID=$Transactions_ID, ChildTransactions_ID='".q("SELECT t.ID FROM finan_transactions t, finan_headers h WHERE h.ID=t.Headers_ID AND h.ID=$n AND h.Accounts_ID!=t.Accounts_ID", O_VALUE)."', AmountApplied='$v'");
				prn($qr);
			}else if(!isset($ApplyTo[$n]) && isset($AppliedTo[$n])){
				//delete this transaction and allocation
				q("DELETE t.*, tt.* FROM
				finan_transactions t, finan_TransactionsTransactions tt, finan_transactions t2
				WHERE t.ID=tt.ParentTransactions_ID AND tt.ChildTransactions_ID=t2.ID AND
				t.Headers_ID=$ID AND t2.Headers_ID=$n");
				prn($qr);
			}
		}
	}
	error_alert('look');
	$navigate=true;
	$navigateCount=$count+($mode==$insertMode?1:0);
	//-------------------- end wesley ---------------------
	goto bypass;
}

//------------------------ Navbuttons head coding v1.50 -----------------------------
//object=finan_headers (a payment has a header as any other transaction)
if(!$sorter)$sorter='ID';
$navObject=$object.'_ID';
$updateMode='updatePayment';
$insertMode='insertPayment';
$deleteMode='deletePayment';
$insertType=1; //1=Save&New and Save&Close; 2 = Save and Save&New
#set these to 'disabled' if desired
$saveInitiallyDisabled='';
$saveAndNewInitiallyDisabled='';
$saveAndCloseInitiallyDisabled='';
//v1.4 change - some information about the coding
$navVer='1.50';
//v1.3 change - declare this function if you need to add parameters to the query string
$navQueryFunction='rfm_payments_nav()';
//v1.3 change - deny transiting from Next to New mode (shutting off ability to insert)
$denyNextToNew=false;
//declare the query to get the idSet or subset, ordered by desired sort order - note that if you're using quasi resources, then be sure and filter them out.
$ids=q("SELECT ID FROM $object WHERE HeaderType='Payment' ".($quasiResourceTypeField?"AND $quasiResourceTypeField IS NOT NULL":'')." ORDER BY $sorter",O_COL);

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
if(strlen($$navObject)){
	if($a=q("SELECT a.*, b.Types_ID FROM finan_headers a, finan_payments b WHERE a.ID=b.Headers_ID AND a.ID='".$$navObject."'",O_ROW)){
		$mode=$updateMode;
		@extract($a);
		//now get calculated values
		$Amounts=
		q("SELECT
		t2.Headers_ID, SUM(t.Extension)
		FROM
		finan_transactions t,
		finan_TransactionsTransactions tt,
		finan_transactions t2
		WHERE
		t.ID=tt.ParentTransactions_ID AND
		tt.ChildTransactions_ID=t2.ID AND
		t.Headers_ID='".$$navObject."'
		GROUP BY t2.Headers_ID", O_COL_ASSOC);
	}else{
		//object may have been deleted by another user, least perplexing approach is to present insert mode
		$mode=$insertMode;
		unset($$object);
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
			$recordPKField[0], 
			$creatorField, 
			$createDateField
		);
		//added 2012-12-12: for the hidden field
		$GLOBALS[$recordPKField[0]]=$$navObject;
	}
	$nullAbs=$nullCount+1; //where we actually are right then
}
//--------------------------- end coding --------------------------------
gmicrotime('afterhead');

$PageTitle='Receive Payments';
$suppressForm=false;

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/properties_04_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo dynamic_title($PageTitle.' - '.$AcctCompanyName);?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" href="/console/rbrfm_admin.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<link href="/Library/ckeditor_3.4/_samples/sample.css" rel="stylesheet" type="text/css" />
<style type="text/css">
textarea.tabby:focus{
	border-style:dotted;
	}
.comment{
	cursor:pointer;
	border-bottom:1px dashed #666;
	}
#workSpace{
	clear:both;
	border:1px solid #ccc;
	padding:5px 10px;
	margin:7px 0px;
	}
</style>

<script language="JavaScript" type="text/javascript" src="/Library/js/jquery.js"></script>
<script language="javascript" type="text/javascript" src="/Library/js/jquery.tabby.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/jquery.bbq.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/jq/numeric.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/global_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/common_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/forms_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/loader_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/dataobjects_04_i1.js"></script>
<script type="text/javascript" src="/Library/ckeditor_3.4/ckeditor.js"></script>
<script language="JavaScript" type="text/javascript">
/* periwinkle coding 2.1 */
var thispage='<?php echo $thispage?>';
var thisfolder='<?php echo $thisfolder?>';
var ctime='<?php echo $ctime?>';
var PHPSESSID='<?php echo $PHPSESSID?>';
//for nav feature
var count='<?php echo $nullCount?>';
var ab='<?php echo $nullAbs?>';
var isEscapable=1;
AddOnkeypressCommand('PropKeyPress(e)'); //if not declared already

var Headers_ID='<?php echo $ID?>';
var HeaderStatus='<?php echo $HeaderStatus;?>';
var datasetObject='payment';

function rfm_payments_nav(){
	alert('not developed, see rfm_payments_nav()');
	return false;
}
</script>
<!-- InstanceEndEditable -->
</head>

<body id="properties">
<form id="form1" name="form1" target="w2" method="post" action="/console/resources/bais_01_exe.php" onSubmit="return beginSubmit();" enctype="multipart/form-data">
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
<input name="_Profiles_ID_" type="hidden" id="_Profiles_ID_" value="<?php echo $_Profiles_ID_;?>" />
<input name="ID" type="hidden" id="ID" value="<?php echo $ID;?>" />
<input name="recordPKField" type="hidden" id="recordPKField" value="<?php echo $recordPKField[0]?>" />
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
ob_start(); //--------- begin tabs ---------

require('components/comp_70_payments_v200.php');

get_contents_tabsection('form'); //---------------------------
?>
<p class="gray">For now, copy and paste into Dreamweaver to edit.</p>
<a href="//relatebase.com/admin/base64_encode.php" title="this helps encode and decode this" onClick="return ow(this.href,'l1_base64','750,800');">Click here for the base64 encoder</a>
<br />

<input type="hidden" name="profile[ID]" id="profileID" value="<?php echo $systemProfile['ID'];?>" />
<input type="hidden" name="profile[EditDate]" id="profileID" value="<?php echo $systemProfile['EditDate'];?>" />
<textarea cols="80" rows="25" name="profile[raw]" id="profileRaw" onChange="dChge(this);" class="tabby"><?php
ob_start();
if(strlen($profileSettings['_raw_'])){
	echo $profileSettings['_raw_'];
}else{
	echo $defaultProfileSettingsString;
}
$out=ob_get_contents();
$outMD5=md5($out);
ob_end_clean();
echo h($out);
?></textarea>
<input type="hidden" name="profile[raw_hash]" id="profileRawHash" value="<?php echo $outMD5?>" />
<br />
<input type="button" name="Button" value="Update Profile Settings" id="updateProfileSettings" />
<?php
get_contents_tabsection('settings'); //---------------------------

echo $helpString;

get_contents_tabsection('help'); //---------------------------
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

<!-- InstanceEndEditable --></div>
<div id="footer"><!-- InstanceBeginEditable name="footer" -->
&nbsp;
<!-- InstanceEndEditable --></div>
</form>
<?php if(!$hideCtrlSection){ ?>
<div id="showTester" title="Javascript Tester" onClick="g('tester').style.display='block';">&nbsp;</div>
<div id="tester" >
	<a href="#" onClick="g('ctrlSection').style.display='block';return false;">Show Control Section</a><br />
	<textarea name="test" cols="65" rows="4" id="test">clear_form();</textarea><br />
	<input type="button" name="button" value="Test" onClick="jsEval(g('test').value);"><br />
	<textarea id="result" name="result" cols="65" rows="3" ></textarea>
</div>
<div id="ctrlSection" style="display:<?php echo $testModeC ? 'block':'none'?>">
	<iframe name="w1" src="/Library/js/blank.htm"></iframe>
	<iframe name="w2" src="/Library/js/blank.htm"></iframe>
	<iframe name="w3" src="/Library/js/blank.htm"></iframe>
	<iframe name="w4" src="/Library/js/blank.htm"></iframe>
</div>
<?php } ?>
</body>
<!-- InstanceEnd --></html><?php
page_end();
//skip the page output
bypass:
?>