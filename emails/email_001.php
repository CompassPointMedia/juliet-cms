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
You have recently applied at <?php echo $adminCompany?> and provided this email address (<?php echo $_POST['Email']?>).  Your application has NOT BEEN SUBMITTED YET; in order for you to verify your email address, you must click on this link (within <?php echo $usemod['EnrollmentAuthDuration']?> days):
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
This verifies you actually own the email address listed on the application.  If you did NOT actually enroll or sign up for an account at <?php echo $adminCompany?>, simply take no action and the application will not be completed.  In this case, please forward this email to <?php echo $adminCompany?> staff to report this activity.
<br /><br />
Sincerely,<br />
<?php echo $adminCompany?> staff<br />
<?php echo $usemod['siteDomain']?>
<br />
[<?php $a=explode('/',__FILE__); echo preg_replace('/\.php$/i','',$a[count($a)-1])?>]
</body>
</html>
