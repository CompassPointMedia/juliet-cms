<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Please Confirm Enrollment</title>
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
<?php if($usemod['EmailLogo'] && !$nonlinkedTextOnly){ ?>
<div id="imgHeader"><img src="<?php echo $usemod['EmailLogo'];?>" alt="<?php echo $adminCompany?>" /></div>
<?php }else if($usemod['EmailHeader']){  echo $usemod['EmailHeader']; } ?>
Dear <?php echo htmlentities($_POST['FirstName']. ' '.$_POST['LastName'])?>,
<br /><br />
You have recently applied to <?php echo $adminCompany?> and provided this email address (<?php echo $_POST['Email']?>).  Your account is NOT YET ACTIVE; in order for you to activate your enrollment, you must follow this link within <?php echo $usemod['EnrollmentAuthDuration']?> days:
<br /><br /><br />
<?php
$href= $usemod['usemodURLRoot'].'/login?sessionid='.$GLOBALS['PHPSESSID'].'&UN='.$UserName.'&EnrollmentAuthToken='.$EnrollmentAuthToken;
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
As soon as you reply, you will have access to our great <?php echo $usemod['wholesaleToken'] ? strtolower($usemod['memberWord']):''?> account features.
<br /><br /><br />
This prevents malicious users signing up under your email address and impersonating you.  If you did NOT actually enroll or sign up for an account at <?php echo $adminCompany?>, simply take no action and the enrollment will not be finalized.  You might wish to forward this email to <?php echo $adminCompany?> staff so that they are aware of this activity.
<br /><br />
Sincerely,<br />
<?php echo $adminCompany?> staff<br />
<?php echo $usemod['siteDomain']?>
[003]
</body>
</html>
