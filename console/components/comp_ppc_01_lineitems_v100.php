<?php
/*
todo:
2012-01-03: this coding should be able to handle cash sales and also checks.  difference with checks being lack of an item; there will be an account, description and extension
	* test a invoice where there is more than one payment applied
	* test a "final" payment that covers the amount exactly, a few pennies, below, and a few above - in rfm_payments.



2013-01-03
this is also extremely intricate.. a lot of things to consider
1. when we modify an invoice, suppose we remove one line and add another.  ANY CHANGES that include NET amount changes, including the deletion of a non-zero line item, or the addition of a non-zero line-item, will require the redistribution of the payment allocation
2. If the invoice amount goes less, it could cause the invoice to be over-paid.  PERHAPS we should at least warn or better prevent this from happening.

2012-12-12
this is a key piece of coding for invoices

*/
if(!function_exists('lib_proportion')){
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
}
if($a=q("SELECT ID FROM finan_items WHERE Accounts_ID IS NULL OR Accounts_ID=0", O_COL)){
	mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err='For this account, using this page, items were detected with no accounts_id and this was set to Misc. Income'),$fromHdrBugs);
	//correct this
	$getAccount=array(
		'var'=>'Accounts_ID',
		'typeName'=>'Income',
		'typeCategory'=>'Income',
		'name'=>'Misc. Income',
		'description'=>'Miscellaneous Income (added automatically)',	
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
	q("UPDATE finan_items SET Accounts_ID=$Accounts_ID WHERE Accounts_ID IS NULL OR Accounts_ID=0");
}
prn('----------------- start --------------');
unset($err);
$sub=(array_transpose($sub));
//preprocess - non-root transactions that originally were/are in this entry
$original=q("SELECT b.ID, b.Extension FROM finan_headers a, finan_transactions b WHERE a.ID='".$GLOBALS[$recordPKField[0]]."' AND a.ID=b.Headers_ID AND a.Accounts_ID!=b.Accounts_ID ORDER BY b.Idx", O_COL_ASSOC);
prn($qr);
prn('original transactions:');
prn($original);
//this represents all allocations that have been applied to this invoice, and the amounts
if($HeaderType=='Invoice')$applied=q("SELECT t.ID, t.Extension
FROM finan_transactions t JOIN finan_TransactionsTransactions tt ON t.ID=tt.ParentTransactions_ID WHERE tt.ChildTransactions_ID IN('".implode("','",array_keys($original))."') AND tt.Type='Payment'", O_COL_ASSOC);

$quantityChange=false;
foreach($sub as $n=>$v){
	// x - remove empties
	if(!trim(implode('',$v))){
		unset($sub[$n]);
		continue;  //ok just empty
	}
	if($v['ID']){
		//item won't be deleted but may be modified
		unset($original[$v['ID']]);
		
		//changing amount of existing entry
		if(round($original[$v['ID']]['Extension'],2)!=round($v['Extension'],2))$quantityChange=true;
	}else{
		//inserting a non-zero new entry
		if(round($v['Extension'],2)!=0.00)$quantityChange=true;
	}
	$updateTotal+=round($v['Extension'],2);
	//errors
	if(!$v['SKU']){
		$err['No SKU']++;
		continue;
	}
	if($v['Items_ID']){
		//OK
	}else{
		if(!($sub[$n]['Items_ID']=q("SELECT ID FROM finan_items WHERE SKU='".$v['SKU']."'", O_VALUE))){
			prn($qr);
			$err['Unrecognized SKU(s)']++;
		}
	}	
	if(
		round((float)preg_replace('/[$,]/','',$v['Extension']),2) 
		!= 
		round((float)preg_replace('/[$,]/','',$v['Quantity']) * (float)preg_replace('/[$,]/','',$v['UnitPrice']),2)
	)$err['Math incorrect']++;
}
if($err){
	foreach($err as $n=>$v)$errs[]=$n.': '.$v;
	error_alert('You have error(s) in your item entry as follows:\n'.implode('\n',$errs).'\nCorrect this and re-submit the record');
}
prn($sub);
if($HeaderType=='Invoice' && round(array_sum($applied),2)>$updateTotal){
	error_alert('This transaction has payment(s) applied to it.  It appears you have reduced the amount of this transaction below the amount of payment(s) applied to it.  Currently the system will not allow this');
}
if(count($original)){
	//these are going to be deleted. Are there reimbursements or etc. like timesheets that depend on these items?
	if(false && $a=q("SELECT * FROM finan_TransactionsTransactions tt WHERE ParentTransactions_ID IN(".implode(',',array_keys($original)).") OR ChildTransactions_ID IN(".implode(',',array_keys($original)).")", O_ARRAY))error_alert('Some of the line items you are deleting have dependent transactions and cannot be deleted');

	//------- at this point we begin affecting the database ----------
	q("DELETE FROM finan_transactions WHERE ID IN(".implode(',',array_keys($original)).")");
	prn($qr);
}

//now do the insertion/updating
$Idx=0;
foreach($sub as $n=>$v){
	//notice the extra O_VALUE query so that a lowercase user-entered sku like webhost will be converted to WEBHOST as in the items table
	$Idx++;
	if($v['ID']){
		q("UPDATE finan_transactions SET 
		Idx='$Idx',
		Items_ID='".$v['Items_ID']."', 
		SKU='".q("SELECT SKU FROM finan_items WHERE SKU='".$v['SKU']."'", O_VALUE)."', 
		".(isset($v['Name'])?"Name='".$v['Name']."',":'')." 
		".(
		strtolower($v['SKU'])!=q("SELECT LCASE(SKU) FROM finan_transactions WHERE ID='".$v['ID']."'", O_VALUE)
		?
		"Accounts_ID='".q("SELECT Accounts_ID FROM finan_items WHERE SKU='".$v['SKU']."'", O_VALUE)."',"
		:
		''
		)." 
		Description='".$v['Description']."', 
		Quantity='".preg_replace('/[$,]/','',$v['Quantity'])."', 
		UnitPrice='".preg_replace('/[$,]/','',$v['UnitPrice'])."', 
		Extension='".preg_replace('/[$,]/','',$v['Extension'])."', 
		EditDate=EditDate WHERE ID='".$v['ID']."'");
		$proportions[$v['ID']]=preg_replace('/[$,]/','',$v['Extension']);
	}else{
		if(round($v['Extension'],2)!=0.00){
			$quantityChange=true;
		}
		//do we need this for anything?
		$tid=q("INSERT INTO finan_transactions SET
		Idx=$Idx,
		Headers_ID='".$GLOBALS[$recordPKField[0]]."',
		Items_ID='".$v['Items_ID']."', 
		SKU='".q("SELECT SKU FROM finan_items WHERE SKU='".$v['SKU']."'", O_VALUE)."', 
		".(isset($v['Name'])?"Name='".$v['Name']."',":'')." 
		Accounts_ID='".q("SELECT Accounts_ID FROM finan_items WHERE SKU='".$v['SKU']."'", O_VALUE)."',
		Description='".$v['Description']."', 
		Quantity='".preg_replace('/[$,]/','',$v['Quantity'])."', 
		UnitPrice='".preg_replace('/[$,]/','',$v['UnitPrice'])."', 
		Extension='".preg_replace('/[$,]/','',$v['Extension'])."', 
		CreateDate=NOW(),
		Creator='".sun()."'", O_INSERTID);
		$proportions[$tid]=preg_replace('/[$,]/','',$v['Extension']);
	}
	prn($qr);
	$total+=$v['Extension'];
}
//adjust rho entry
if($mode==$updateMode){
	//note we don't touch Accounts_ID - done by root entry editing
	q("UPDATE finan_headers a, finan_transactions b SET b.Extension='".($total * -1)."', b.EditDate=b.EditDate WHERE a.ID='".$GLOBALS[$recordPKField[0]]."' AND a.ID=b.Headers_ID AND a.Accounts_ID=b.Accounts_ID");
}else{
	$rhoTransactions_ID=q("INSERT INTO finan_transactions SET
	Headers_ID='".$GLOBALS[$recordPKField[0]]."',
	Accounts_ID='$Accounts_ID',
	Extension='".($total * -1)."',
	CreateDate=NOW(),
	Creator='".sun()."'", O_INSERTID);
}
prn($qr);
if($HeaderType=='Invoice' && $applied && $quantityChange){
	q("DELETE FROM finan_TransactionsTransactions WHERE ParentTransactions_ID IN('".implode("','",array_keys($applied))."')");
	prn($qr);
	foreach($applied as $n=>$v){
		//we proportionally distribute to all updated/new transactions in this item
		$b=lib_proportion($proportions,$v,(isset($sofar)?$sofar:array()));
		foreach($b as $o=>$w){
			q("INSERT INTO finan_TransactionsTransactions SET ParentTransactions_ID=$n, ChildTransactions_ID=$o, AmountApplied=$w, Type='Payment'");
			prn($qr);
		}
		foreach($b as $n=>$v)$sofar[$n]+=$v;
	}
}
?>