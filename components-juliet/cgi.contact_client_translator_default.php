<?php
//2009-08-08:
$ResourceType=1;
$ResourceToken=substr(date('Y'),-1).date('mdHis').substr(rand(10000,99999),0,5);

//2007-09-04: this was to translate addr_contacts form into finan_clients (which has a poor structure to begin with, so be ready to modify this
if($mode=='insert'){
	$CompanyName=(isset($CompanyName) ? $CompanyName : (isset($Company) ? $Company : $FirstName . ' ' . $LastName)); //we keep this as it is
	$ClientName=sql_autoinc_text('finan_clients', 'ClientName', isset($ClientName) ? $ClientName : $CompanyName, $options=array(
		'returnLowerCase'=>false
	));
}

$PrimarySalutation=$Title;
$PrimaryFirstName=$FirstName;
$PrimaryMiddleName=$MiddleName;
$PrimaryLastName=$LastName;

//address
if(!isset($Address1))	$Address1=	$GLOBALS['HomeAddress'];
if(!isset($City)) 		$City=		$GLOBALS['HomeCity'];
if(!isset($State)) 		$State=		$GLOBALS['HomeState'];
if(!isset($Zip)) 		$Zip=		$GLOBALS['HomeZip'];
if(!isset($Country)) 	$Country=	$GLOBALS['HomeCountry'];
?>