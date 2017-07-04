<?php
/*
i1=first basic error checking usage
*/
$ec=$usemod['ec'];
if(isset($Title) && !trim($Title))error_alert('Please include your title');
if(isset($Company) && !trim($Company))error_alert('Please include a company name');
if(!$FirstName || !$LastName)error_alert('Please include a first and last name');
if(!$ec['EmailOptional'] && !valid_email($Email))error_alert('Your email address is not valid');

if($mode=='insert' && !$usemod['autoGeneratePassword']){
	if(!trim($Password) || $Password!==$nullPassword) error_alert('Password not present or passwords do not match');
	if(strlen($Password)<3)	error_alert('Password must be at least 3 characters long');
}
if(!$ec['BusAddressOptional'] && isset($BusAddress)){
	if(!$BusAddress || !$BusCity || !$BusState || !$BusZip) error_alert('Please include a complete business address');
}
if(!$ec['HomeAddressOptional'] && isset($HomeAddress)){
	if(!$HomeAddress || !$HomeCity || !$HomeState || !$HomeZip) error_alert('Please include a complete address');
}
if(!$ec['HomePhoneOptional'] && isset($HomePhone)){
	if(!$HomePhone) error_alert('Please Include a Home Phone Number');
}
if(!$ec['HomeMobileOptional'] && isset($HomeMobile)){
	if(!$HomeMobile) error_alert('Please Include a Home Mobile Phone Number');
}
if(!$ec['BusPhoneOptional'] && isset($BusPhone)){
	if(!$BusPhone) error_alert('Please Include a Business Phone Number');
} 
if($usemod['wholesaleToken'] && isset($WholesaleNumber) && (!trim($WholesaleNumber) || !trim($WholesaleState))){
	error_alert('Please include your resale number and state or province (comments are optional)');
}

//terms of use
if($mode=='insert' && $usemod['requireTermsOfUseAcceptance'] && !$TOU_OK){
	error_alert($usemod['TermsOfUseErrorMsg']);
}else{
	//somewhat secure translation
	$_POST[$usemod['TermsOfUseField']]=$usemod['TermsOfUseVersionOrID'];
}
?>