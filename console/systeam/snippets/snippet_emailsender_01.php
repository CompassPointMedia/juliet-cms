<?php
/*
2009-05-14: simple email sender component - uses enhanced_mail and best practice I have so far
*/
ob_start();
require($emailSource);			
$out=ob_get_contents();
ob_end_clean();
if(strtolower($systemEmail['content_disposition'])=='html'){
	$sent=enhanced_mail(
		$emailTo,
		($emailSubj ? $emailSubj : $systemEmail['subject']),
		$out,
		$emailFrom,
		'html',
		''
	);
}else{
	$sent=mail(
		$emailTo,
		($emailSubj ? $emailSubj : $systemEmail['subject']),
		$out,
		$emailFrom
	);
}
?>