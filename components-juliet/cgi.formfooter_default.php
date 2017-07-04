<?php
/*
2010-10-01
see the _usemod_formheader_default.php file

*/
if($CMSMessage){
	cgi_message_manager('formfooter',$CMSMessage);
}else{
	if($mode=='update'){
		if(!$updateAccountFormFooter)$updateAccountFormFooter='updateAccountFormFooter';
		if($msg=$message[$updateAccountFormFooter]){
			echo $msg;
		}
	}else{
		if(!$insertAccountFormFooter)$insertAccountFormFooter='insertAccountFormFooter';
		if($msg=$message[$insertAccountFormFooter]){
			echo $msg;
		}
	}
}
?>