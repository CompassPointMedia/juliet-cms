<?php
session_start();
$developerEmail='sam-git@samuelfullman.com';
$fromHdrBugs='From:bugreports@relatebase.com';


function get_globals($msg=''){
	ob_start();
	//snapshot of globals
	$a=$GLOBALS;
	//unset redundant nodes
	unset($a['HTTP_SERVER_VARS'], $a['HTTP_ENV_VARS'], $a['HTTP_GET_VARS'], $a['HTTP_COOKIE_VARS'], $a['HTTP_SESSION_VARS'], $a['HTTP_POST_FILES']);
	print_r($a);
	unset($a);
	$out=ob_get_contents();
	ob_end_clean();
	return ($msg ? "\nMessage:".$msg : '')."\n\n" . $out;
}
function iframe_shutdown(){
	/*
	2011-12-17 version 2.00
		* got output working finally - why did I wait so long for this?
	2007-03-21 @6:21AM 	version 1.01 
		* cleaned things up and started depending on external fctns like get_globals(); this version was used in jboyce.com
	*/
	global $qr, $qx, $iframe_shutdown_options, $assumeErrorState, $parentUnSubControl, $suppressNormalIframeShutdownJS, $developerEmail, $fromHdrBugs, $suppressMailOutput;
	if(!$suppressNormalIframeShutdownJS){
		?><script language="javascript" type="text/javascript">
		//notify the waiting parent of success, prevent timeout call of function
		window.parent.submitting=false;
		try{
			if(<?php echo $parentUnSubControl ? 'true' : 'false' ?>){
				eval('<?php echo $parentUnSubControl?>');
			}else{
				window.parent.document.getElementById('SubmitApplication').disabled=false;
				window.parent.document.getElementById('SubmitStatus1').innerHTML=' ';
			}
		}catch(e){ }
		</script><?php
	}
	if(!$assumeErrorState)return false; //this is the end of the script

	//for mailing
	if(!$suppressMailOutput){
		$out=ob_get_contents();
		ob_end_clean();
		if(strlen($out))echo "\n".'HTML output successfully intercepted';
	}

	//handle errors
	?><script>
	//for the end user - you can improve this rather scary-sounding message
	try{
		window.parent.g('ctrlSection').style.display='block';
	}catch(e){ }
	alert('We\'re sorry but there has been an abnormal error while submitting your information, and staff have been emailed.  Please try refreshing the page and entering your information again');
	</script><?php

	//we also mail that this has happened
	unset($mail);
	if($fl)$mail.="\n".'File: '.$fl;
	if($ln)$mail.="\n".'Line: '.$ln;

	$mail.="\n\n----------- Part 1: ENVIRONMENT VARIABLES -------------\n";
	$mail.=get_globals();
	
	//Page Output - normally we print out results after each SQL query for example
	if(strlen($out))$mail.="\n\n----------- Part 2: HTML OUTPUT -------------\n".$out;
	
	//send email notification
	$acct=end(explode('/',trim(preg_replace('/juliet|public_html|dev/i','',$_SERVER['DOCUMENT_ROOT']),'/')));
	mail($developerEmail,'Ab-shutdown: '.$acct, $mail, $fromHdrBugs);
	return true;
}

$assumeErrorState=true;
register_shutdown_function('iframe_shutdown');

ob_start();
echo 'here is some text';

wrong();


?>