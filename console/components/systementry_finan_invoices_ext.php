<?php
//2012-12-19: add the extension record for invoice or cash sale
/*
we could also email invoices and respond to various settings for comm
*/
$extPresent=q("SELECT COUNT(*) FROM finan_invoices WHERE Headers_ID='$ID'", O_VALUE);
	
$sql=($extPresent?'UPDATE ':'INSERT INTO ').'finan_invoices SET Headers_ID='.$ID.', ';

if($ShippingAddress || $ShippingCity){
	//OK
}else{
	//we are going to for now consider shipping and billing address the same
	$a=(q("SELECT 
	c.PrimaryFirstName AS ShippingFirstName,
	c.PrimaryLastName AS ShippingLastName,
	c.CompanyName AS ShippingCompany,
	c.Address1 AS ShippingAddress,
	c.City AS ShippingCity,
	c.State AS ShippingState,
	c.Zip AS ShippingZip,
	c.Country AS ShippingCountry,
	c.Email AS ShippingEmail,
	c.Phone AS ShippingPhone,
	c.PrimaryFirstName AS BillingFirstName,
	c.PrimaryLastName AS BillingLastName,
	c.CompanyName AS BillingCompany,
	c.Address1 AS BillingAddress,
	c.City AS BillingCity,
	c.State AS BillingState,
	c.Zip AS BillingZip,
	c.Country AS BillingCountry,
	c.Email AS BillingEmail,
	c.Phone AS BillingPhone
	FROM finan_clients c WHERE c.ID='$Clients_ID'", O_ROW));
	foreach($a as $n=>$v)if(!trim($v))unset($a[$n]);
	extract($a);
}
$invoiceFields=q("EXPLAIN finan_invoices", O_ARRAY);
foreach($invoiceFields as $n=>$v){
	unset($invoiceFields[$n]);
	$invoiceFields[$v['Field']]=$v;
}
foreach(array_keys($invoiceFields) as $v){
	if(in_array($v,array('Headers_ID')))continue;
	if(isset($GLOBALS[$v]))$sql.=$v.'=\''.addslashes($GLOBALS[$v]).'\',';
}
$sql=rtrim($sql,',');
if($extPresent)$sql.=' WHERE Headers_ID='.$ID;
ob_start();
q($sql, ERR_ECHO);
$err=ob_get_contents();
ob_end_clean();
if($err)mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err),$fromHdrBugs);


?>