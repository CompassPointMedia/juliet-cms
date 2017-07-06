<?php
/*
2012-04-23:
	* I want to export vendors - uncomment and finish
	* want to export COA also
	* IIFExportComments - store a setting
iif file itself
	better comment than shopping cart?																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																												total cpa reimbursement: .. remove																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																												test import																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																	
	item type needs same types as quickbooks																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																					

2012-04-07: this is now coming into use in root functions in the console
IsPassedThrough - questions about this field
	* custom fields for customers - important to develop

*/
if($ObjectType){
	$a=explode(':',$ObjectType);
	$exportObject=$a[0];
	$exportType=$a[1];
}else{
	$exportObject='items';
	$exportType='iif';
	$ObjectType=$exportObject. ':'. $exportType;
}

if(!$step)$step=1;

if($mode=='exportData'){
	switch(true){
		case $ObjectType=='items:iif':
			if($datasetGroup=='all'){
				//no where clause
				$where = '1';
			}else if($datasetGroup=='some'){
				if(!trim($someWhere))error_alert('Enter a SQL "where" clause phrase such as "Category=\'Member\'"');
				$where = stripslashes($someWhere);
			}else if($datasetGroup=='unexported'){
				$where = 'i.ToBeExported=1';
			}
			if($includeOnlyUsed){
				
			}
			
			if($testQuantity){
				$records=q("SELECT COUNT(*) FROM finan_items i LEFT JOIN finan_transactions t ON t.Items_ID=i.ID WHERE i.ResourceType IS NOT NULL WHERE $where GROUP BY i.ID ORDER BY i.SKU", O_VALUE);
				error_alert( $records ? 'A total of '.$records.' need to be exported'.($asdf!='all' ? ' based on this criteria':'') : 'No records need to be exported' );
			}
			$Accounts_ID=str_replace('{RBADDNEW}','',$Accounts_ID);
			unset($options);
			$options['setAsExported']=($setAsExported ? true : false);
			if($blankAccount==0){
				$where='(i.Accounts_ID != 0 AND i.Accounts_ID IS NOT NULL)';
			}else if($blankAccount==1){
				
			}else if($blankAccount==2){
				if(!$Accounts_ID)error_alert('Select a chart of accounts item or create one');
				if($makePermanent){
					if(!q("SELECT a.ID
					FROM finan_accounts a, finan_accounts_types b WHERE a.Types_ID=b.ID AND a.ID=$Accounts_ID AND b.Name='Income'", O_VALUE))error_alert('Account not present or account type is not Income');
					q("UPDATE finan_items SET Accounts_ID=$Accounts_ID WHERE (Accounts_ID IS NULL OR Accounts_ID=0) AND ".$where);
				}else{
					//pass accounts_id to function
					$options['blankAccounts_ID']=$Accounts_ID;
				}
				
			}
			$suppressMailOutput=true;
			require($FUNCTION_ROOT.'/function_quickbooks_export_items_v410.php');
			
			quickbooks_export_items($where, $options);
			if(!$quickbooks_export_items['count'])error_alert('No items to export');
			$file='items_export_'.date('Y-m-d_\a\t_His_').'('.$quickbooks_export_items['count'].').iif';
			$assumeErrorState=false;
			$suppressNormalIframeShutdownJS=true;
			attach_download('', $quickbooks_export_items['records'], $file);
			exit;
		break;
		case $ObjectType=='transactions:iif':

			if(!function_exists('quickbooks_export_customer'))require($FUNCTION_ROOT.'/function_quickbooks_export_customer_v400.php');
			if(!function_exists('quickbooks_export_invoice'))require($FUNCTION_ROOT.'/function_quickbooks_export_invoice_v410.php');
			if(!function_exists('quickbooks_export_items'))require($FUNCTION_ROOT.'/function_quickbooks_export_items_v410.php');
			if(!function_exists('quickbooks_export_vendor'))require($FUNCTION_ROOT.'/function_quickbooks_export_vendor_v400.php');
	
			if(!isset($print))$print=false;
			$invoiceOptions['structureOverrides']=array(
				'TERMS'=>"'Net 15'",
				'SPL_ACCNT'=>"CONCAT('Total CPA Reimbursement:', IF(ff.ID>0,CONCAT(ff.Name,':'),''), f.Name)",
				'SPL_INVITEM'=>"CONCAT('Group Sales:Sales:',IF(ee.ID>0,CONCAT(ee.Name,':'),''), e.Name)"
			);
			$invoiceOptions['setAsExported']=$setAsExported;
			$invoiceOptions['print']=$toBePrinted;
			/*
			$customerOptions=array(
				'CUSTFLD1'=>'a.MembershipStart',
				'CUSTFLD2'=>'a.MembershipEnd',
				'CUSTFLD3'=>'a.MembershipLevel',
				'CUSTFLD4'=>'a.MembershipType',
				'CUSTFLD5'=>'',
				'CUSTFLD6'=>'c.UserName',
				'CUSTFLD7'=>'a.ExportTime'
			);
			*/
			$customerOptions['setAsExported']=true;

			/*
			$qbksFieldsOverrides['Items']['PREFVEND'] = array(
				'fh.VendorName', /* field name * /
				NULL, /* conversion specs * /
				array(  /* foreign key/hier specs * /
					LABEL_EXTERNAL_NONHIERARCHICAL,
					'gf_fosterhomes',
					'fh',
					'VendorName',
					'Vendors_ID',
					'ID'
				)
			);
			*/
			$itemOptions=array(
				'qbksFieldsOverrides'=>$qbksFieldsOverrides,
				'setAsExported'=>false,
				'debugQuery'=>true
			);
			$vendorOptions['debugQuery']=$developerEmail;
			if(quickbooks_export_invoice(q("SELECT ID FROM finan_headers WHERE ".($datasetGroup=='all' ? 1 : "ToBeExported=1"), O_COL), $invoiceOptions)){
				if(count($quickbooks_export_invoice['customers'])) quickbooks_export_customer($quickbooks_export_invoice['customers'], $customerOptions);
				$buffer=array();
				if(count($quickbooks_export_invoice['items'])){
					$buffer=$quickbooks_export_invoice['accounts'];
					quickbooks_export_items($quickbooks_export_invoice['items'], $itemOptions);
					/*
					if($quickbooks_export_items['vendors']){
						$vendorOptions['setAsExported']=false;
						$vendorOptions['completeQuery']="SELECT
						f.VendorName AS `NAME`,
						UNIX_TIMESTAMP(f.EditDate) AS `TIMESTAMP`,
						f.ID AS `REFNUM`,
						CONCAT(p.FirstName, IF(p.MiddleName,' ',''), p.MiddleName, ' ', p.LastName) AS `ADDR1`,
						f.Address AS `ADDR2`,
						CONCAT(f.City,', ',f.State,' ',f.Zip) AS `ADDR3`,
						CONCAT('Foster Parent:',oa_org2) AS `VTYPE`,
						CONCAT(p.FirstName, IF(p.MiddleName,' ',''), p.MiddleName, ' ', p.LastName) AS `CONT1`,
						f.Active AS `HIDDEN`,
						f.Phone AS `PHONE1`,
						f.WorkPhone AS `PHONE2`,
						f.Fax AS `FAXNUM`,
						f.PrintAs AS `PRINTAS`,
						f.FacilityNumber AS `NOTE`,
						CONCAT(un.un_firstname, ' ',un.un_lastname) AS `COMPANYNAME`,
						p.FirstName AS `FIRSTNAME`,
						p.MiddleName AS `MIDINIT`,
						p.LastName AS `LASTNAME`,
						p.Email AS `EMAIL`,
						1 AS ENDFIELDS
						FROM 
						gf_fosterhomes f 
						LEFT JOIN gf_FosterhomesParents fp ON f.ID=fp.Fosterhomes_ID AND fp.Position='Primary' AND fp.DateAssigned <= '$ReportDateFrom' AND (fp.DateReleased > '$ReportDateTo' OR !fp.DateReleased) 
						LEFT JOIN gf_parents p ON p.ID=fp.Parents_ID
						LEFT JOIN bais_universal un ON f.fh_stusername=un.un_username
						LEFT JOIN gf_OfficesStaff os ON os.os_stusername=f.fh_stusername
						LEFT JOIN bais_orgaliases oa ON os.os_unusername=oa_unusername
						WHERE f.ID IN(".implode(',',$quickbooks_export_items['vendors']).")";
						ob_start();
						print_r($vendorOptions['completeQuery']);
						$result=q($vendorOptions['completeQuery'], O_ARRAY);
						print_r($result);
						$out=ob_get_contents();
						ob_end_clean();
						mail($developerEmail,'query',$out,$fromHdrBugs);
						quickbooks_export_vendor('', $vendorOptions);
					}
					*/
				}
				if(count($buffer) || count($quickbooks_export_items['accounts'])){
					//merge the arrays from invoices and items, and get accounts
					//quickbooks_export_accounts(array_merge($buffer, $quickbooks_export_items['accounts']));
				}
	
				ob_start();
				echo $IIFExportComments;
				echo "!\n!\n!\t-----------------HEADER-----------------\n!\n!\n";
				$x=$quickbooks_export_invoice['header'];
				$x=str_replace('{_ExportCreateDate_}',date('m/d/y'),$x);
				$x=str_replace('{_ExportTimeStamp_}',time(),$x);
				$x=preg_replace('/Version [0-9]+\.0D/i','Version 8.0D',$x);
				echo $x;
				echo "!\n!\n!\tBEGIN CUSTOM NAME DICTIONARY\n!\n!\n";
				//export customers with name dictionary
				echo $quickbooks_export_customer['customnamedictionary'];
				if($x=$quickbooks_export_customer['records']){
					echo "!\n!\n!\t-----------------BEGIN CUSTOMER LIST-----------------\n!\n!\n";
					echo $quickbooks_export_customer['records'];
				}
				//export COA items
				if($quickbooks_export_accounts['records']){
					echo "!\n!\n!\t-----------------BEGIN CHART OF ACCOUNTS-----------------\n!\n!\n";
					echo $quickbooks_export_accounts['records'];
				}
				//export vendors
				if($quickbooks_export_vendor['records']){
					echo "!\n!\n!\t-----------------BEGIN VENDOR LIST-----------------\n!\n!\n";
					echo $quickbooks_export_vendor['records'];
				}
				//export items
				if($quickbooks_export_items['records']){
					echo "!\n!\n!\t-----------------BEGIN ITEM LIST-----------------\n!\n!\n";
					echo $quickbooks_export_items['records'];
				}
				//finally, export the transactions themselves
				echo "!\n!\n!\t-----------------BEGIN TRANSACTIONS-----------------\n!\n!\n";
				echo $quickbooks_export_invoice['records'];
				$out=ob_get_contents();
				ob_end_clean();
				
				$assumeErrorState=false;
				$suppressNormalIframeShutdownJS=true;
				$nameAs = ($fieldConfig['FILENAME']?$fieldConfig['FILENAME']:'invoices_'.date('Y-m-d_H:i:s').'.iif');
				header("Content-Type: text/plain");
				header("Content-Disposition: attachment; filename=$nameAs");
				echo $out;
			}else{
				error_alert('Unable to locate invoices!');
			}
		break;
		default:
	}
	error_alert('test');
}


ob_start();
for($_i_=1; $_i_<=1; $_i_++){ //------------- begin break loop ------------
?>
Select Export Type: 
<select name="ObjectType" id="ObjectType" onchange="window.location='exportmanager.php?ObjectType='+escape(this.value)">
  <option value="items:iif" <?php echo $ObjectType=='items:iif'?'selected':''?>>Items as IIF File</option>
  <option value="items:csv" disabled="disabled">Items as CSV File</option>
  <option value="items:xls" disabled="disabled">Items as Excel Sheet</option>
  <option value="customers:iif" disabled="disabled">Customers as IIF File</option>
  <option value="customers:csv" disabled="disabled">Customers as CSV File</option>
  <option value="transactions:iif" <?php echo $ObjectType=='transactions:iif'?'selected':''?>>Transactions in IIF Format</option>
</select>

<form name="form1" id="form1" action="index_01_exe.php" method="post" target="w2"><?php
if($exportObject=='items'){
	$suppressPrintEnv=1;
	
	if($step==1){
		?><h2>Export Items as .iif File</h2>
		<label>
		<input name="datasetGroup" type="radio" value="all" onchange="dChge(this);" <?php echo !isset($datasetGroup) || $datasetGroup=='all'?'checked':''?> />
		Export All</label><br>
		<label><input name="datasetGroup" type="radio" value="some" onchange="dChge(this);" <?php echo $datasetGroup=='some'?'checked':''?> />
		Export Some</label>
		<br />
		Define where clause (root table alias is &quot;i&quot;):<br />
		<textarea name="someWhere" cols="45" rows="3" id="someWhere" onchange="dChge(this);"><?php echo h($someWhere);?></textarea> 
		<br />
		<?php
		$raw=(q("SELECT COUNT(*) FROM finan_items WHERE ResourceType IS NOT NULL", O_VALUE) == ($unexported=q("SELECT COUNT(*) FROM finan_items WHERE ToBeExported=1 AND ResourceType IS NOT NULL", O_VALUE)));
		?>
		<label>
		<input name="datasetGroup" type="radio" value="unexported" onchange="dChge(this);" <?php echo $datasetGroup=='unexported'?'checked':''?> <?php echo $raw || !$unexported?'disabled':''?> />  
		Export only previously unexported items (total of <?php echo $unexported;?>)</label>
		<br /> 
		<!--
		<label> 
		<input name="includeOnlyUsed" type="checkbox" id="includeOnlyUsed" value="1" <?php echo !isset($includeOnlyUsed) || $includeOnlyUsed?'checked':''?> />
		Include only items which have been used in transactions</label>
		<br />
		-->
		<br />
  <label>
		<input name="setAsExported" type="checkbox" id="setAsExported" value="1" checked="checked" onchange="dChge(this)" />
  Mark these items as  exported</label>
		<br />

		<br />
		<h3>Account Required</h3>
		<p>All items must have a chart of accounts income account to be imported into QuickBooks.  If an item does not have a chart of accounts income item do the following:<br />
		<label><input name="blankAccount" type="radio" value="0" onchange="dChge(this);" /> 
		Do not export these items</label><br />
		<label><input name="blankAccount" type="radio" value="1" onchange="dChge(this);" checked="checked" />		
		Do nothing (you will need to add the income account yourself)</label><br />
		<label><input name="blankAccount" type="radio" value="2" onchange="dChge(this);" />
		Assign the following chart of accounts income account: </label>
		<select name="Accounts_ID" id="Accounts_ID" onchange="dChge(this);newOption(this, 'rfm_coa_focus.php', 'l1_coafocus', '600,500');" cbtable="finan_accounts">
		<?php
		if(!function_exists('coa'))require($FUNCTION_ROOT.'/function_coa_v100.php');
		unset($COAArray);
		coa('', 1, '', $subGroup='b.Name=\'Income\'');
		foreach($COAArray as $n=>$v){
			?><option value="<?php echo $v['ID'];?>" <?php echo $Accounts_ID==$v['ID']?'selected':''?> <?php echo $v['Level']>1?'style="padding-left:'.(($v['Level']-1)*15).'px;"':''?>><?php echo h($v['Name']);?></option><?php
		}
		?>
		<option value="{RBADDNEW}" style="background-color:thistle;">&lt; Add new.. &gt;</option>
		</select>
		<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<label><input name="makePermanent" type="checkbox" id="makePermanent" value="1" />
Update this change to exported items</label>
		  </p>
	    <?php
	}
}else if($exportObject=='transactions'){
	$suppressPrintEnv=1;
	$table=q("EXPLAIN finan_transactions", O_ARRAY);
	$hideTestQuantity=true;
	foreach($table as $n=>$v){
		$table[strtolower($v['Field'])]=$v;
		unset($table[$n]);
	}
	?>
  <div>
    <h2>Export Transactions as .iif File</h2>  
	Type:
	<select name="TransactionType" id="TransactionType" onchange="dChge(this);">
	<option value="combined" <?php echo $TransactionType=='combined'?'selected':''?>>Invoices and Cash Sales</option>
	<option value="invoices" <?php echo $TransactionType=='invoices'?'selected':''?> disabled="disabled">Invoices</option>
	<option value="sales" <?php echo $TransactionType=='sales'?'selected':''?> disabled="disabled">Cash Sales</option>
	<option value="payments" <?php echo $TransactionType=='payments'?'selected':''?> disabled="disabled">Payments</option>
	</select><br />
	<?php
	$raw=(($count=q("SELECT COUNT(*) FROM finan_headers WHERE ResourceType IS NOT NULL", O_VALUE)) == ($unexported=q("SELECT COUNT(*) FROM finan_headers WHERE ToBeExported=1 AND ResourceType IS NOT NULL", O_VALUE)));
	?>
	<label>
	<input name="datasetGroup" type="radio" value="all" onchange="dChge(this);" <?php echo $datasetGroup=='all' || $count==$unexported?'checked':''?> />
Export All</label>
	<br />
	<label><input name="datasetGroup" type="radio" value="unexported" onchange="dChge(this);" <?php echo $datasetGroup=='unexported' || $count!=$unexported ? 'checked' : ''?> <?php echo $raw || !$unexported?'disabled':''?> />
Export only previously unexported items (total of <?php echo $unexported;?>)</label>
	<br />
	<br />
	<label>
    <input name="setAsExported" type="checkbox" id="setAsExported" value="1" checked="checked" onchange="dChge(this)" />
Mark these transactions as  exported</label>
	<br />
	<label>
    <input name="toBePrinted" type="checkbox" id="setAsExported" value="1" checked="checked" onchange="dChge(this)" />
Mark these transactions as  to be printed</label>
	<br />
	<br />
	</div>
	<?php
	
}
?>
    <input name="mode" type="hidden" id="mode" value="exportData" />
<input name="exportObject" type="hidden" id="exportObject" value="<?php echo $exportObject;?>" />
<input name="exportType" type="hidden" id="exportType" value="<?php echo $exportType;?>" />
<input name="step" type="hidden" id="step" value="<?php echo $step;?>" />
<input name="testOnly" type="hidden" id="testOnly" />
<input name="suppressPrintEnv" type="hidden" id="suppressPrintEnv" value="<?php echo $suppressPrintEnv;?>" />
<br />
<?php if(!$hideTestQuantity){ ?>
<input name="Submit" type="submit" id="Submit" value="Test Quantity" onclick="g('testQuantity').value='1';" />
&nbsp;&nbsp;
<?php }?>
<input type="submit" name="Submit" value="Export" onclick="g('testQuantity').value='0';" />
&nbsp;&nbsp;
<input type="button" name="Submit2" value="Close" onclick="window.close();" />

</form><?php
$out=ob_get_contents();
ob_end_clean();
} //--------------------- end break loop ------------------
echo $out;
?>