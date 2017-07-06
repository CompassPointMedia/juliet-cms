<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Please Confirm Your Email</title>
<style type="text/css">
body{
	margin:10px 18px;
	font-family:Arial, Helvetica, San-serif;
	font-size:13px;
}
</style>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php 
if($usemod['EmailLogo'] && !$nonlinkedTextOnly){ 
	?>
	<div id="imgHeader"><img src="<?php echo $usemod['EmailLogo'];?>" alt="<?php echo $adminCompany?>" /></div>
	<?php
}else if($usemod['EmailHeader']){  
	echo $usemod['EmailHeader']; 
}
?>
<div id="mainBody">
Dear <?php echo h(stripslashes($FirstName. ' '.$LastName));?>,
<br /><br />
You are receiving this email because you signed up at <?php echo $adminCompany?>.  In order complete the process, you must click on the link below. This must be done within <?php echo $usemod['EnrollmentAuthDuration']?> days:
<br /><br /><br />
<?php
$href= 'http://'.$_SERVER['HTTP_HOST'].'/cgi/login?sessionid='.$GLOBALS['PHPSESSID'].'&UN='.$UserName.'&EnrollmentAuthToken='.$EnrollmentAuthToken;
if($usemod['forceEnrollmentConfirmationRedirect'] && $usemod['enrollmentConfirmationRedirect']){
	$href.='&src='.urlencode($usemod['enrollmentConfirmationRedirect']);
}else if($src){
	//use src value passed in querystring -> post
	$href.='&src='.urlencode($src);
}else if($usemod['enrollmentConfirmationRedirect']){
	$href.='&src='.urlencode($usemod['enrollmentConfirmationRedirect']);
}
?>
<a href="<?php echo $href?>"><?php echo $href?></a>
<br /><br /><br />
This confirmation prevents malicious users using your email address.  If you did NOT actually apply, simply take no action and the application will not be completed.<br /><br />
Sincerely,<br />
<?php echo $adminCompany?> staff<br />
[002]
</div>
</body>
</html>
